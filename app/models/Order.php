<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

final class Order extends Model {
    public function list(?int $customerId=null): array {
        $p=[]; $w='1';
        if($customerId){$w='o.customer_id=?';$p=[$customerId];}
        return $this->all("SELECT o.*,CONCAT(c.first_name,' ',c.last_name) customer,c.phone customer_phone,u.full_name delivery_name
            FROM orders o JOIN customers c ON c.id=o.customer_id
            LEFT JOIN users u ON u.id=o.assigned_delivery_user_id
            WHERE $w ORDER BY o.created_at DESC",$p);
    }

    public function find(int $id): ?array {
        $o=$this->one("SELECT o.*,CONCAT(c.first_name,' ',c.last_name) customer,c.email,c.phone customer_phone FROM orders o JOIN customers c ON c.id=o.customer_id WHERE o.id=?",[$id]);
        if(!$o)return null;
        $o['items']=$this->all("SELECT oi.*,p.name,p.internal_code FROM order_items oi JOIN products p ON p.id=oi.product_id WHERE oi.order_id=?",[$id]);
        return $o;
    }

    public function create(int $customerId,array $cart,array $d): int {
        return Database::transaction(function($db)use($customerId,$cart,$d){
            if(!$cart)throw new \RuntimeException('Panier vide.');
            $subtotal=0.0;$items=[];
            foreach($cart as $pid=>$qty){
                $st=$db->prepare("SELECT p.id,p.name,p.sale_price,p.category_id,s.quantity,s.reserved_quantity,
                    COALESCE((SELECT CASE
                      WHEN pr.type='PERCENT' THEN GREATEST(0,p.sale_price-(p.sale_price*pr.value/100))
                      WHEN pr.type='FIXED' THEN GREATEST(0,p.sale_price-pr.value)
                      ELSE p.sale_price END
                    FROM promotions pr LEFT JOIN promotion_products pp ON pp.promotion_id=pr.id
                    WHERE pr.status='ACTIVE' AND NOW() BETWEEN pr.start_at AND pr.end_at
                    AND (pp.product_id=p.id OR pr.category_id=p.category_id)
                    ORDER BY pr.value DESC LIMIT 1),p.sale_price) effective_price
                    FROM products p JOIN stocks s ON s.product_id=p.id WHERE p.id=? AND p.status='ACTIVE' FOR UPDATE");
                $st->execute([(int)$pid]);$p=$st->fetch();$qty=(float)$qty;
                if(!$p||$qty<=0||$qty>((float)$p['quantity']-(float)$p['reserved_quantity']))throw new \RuntimeException('Stock indisponible pour '.($p['name']??'un produit'));
                $price=(float)$p['effective_price'];$subtotal+=$price*$qty;$items[]=['p'=>$p,'qty'=>$qty,'price'=>$price];
            }

            $discount=0.0;$couponId=null;$couponCode=trim((string)($d['coupon_code']??''));
            if($couponCode!==''){
                $q=$db->prepare("SELECT * FROM coupons WHERE code=? AND status='ACTIVE' AND NOW() BETWEEN start_at AND end_at AND (usage_limit IS NULL OR used_count<usage_limit) FOR UPDATE");
                $q->execute([$couponCode]);$coupon=$q->fetch();
                if(!$coupon)throw new \RuntimeException('Coupon invalide ou expiré.');
                if($subtotal<(float)$coupon['min_cart'])throw new \RuntimeException('Le montant minimum du coupon n’est pas atteint.');
                $discount=$coupon['type']==='PERCENT'?$subtotal*((float)$coupon['value']/100):(float)$coupon['value'];
                $discount=min($subtotal,max(0,$discount));$couponId=(int)$coupon['id'];
            }

            $fee=$d['fulfillment']==='LIVRAISON'?(float)$d['delivery_fee']:0.0;
            $total=max(0,$subtotal-$discount)+$fee;
            $no='CMD-'.date('Ymd-His').'-'.random_int(10,99);
            $db->prepare("INSERT INTO orders(order_no,customer_id,delivery_zone_id,status,fulfillment,subtotal,discount,delivery_fee,total,commune,quartier,avenue,number,reference_text,phone,instructions)
                VALUES(?,?,?,'NOUVELLE',?,?,?,?,?,?,?,?,?,?,?,?)")
                ->execute([$no,$customerId,$d['delivery_zone_id']??null,$d['fulfillment'],$subtotal,$discount,$fee,$total,$d['commune']?:null,$d['quartier']?:null,$d['avenue']?:null,$d['number']?:null,$d['reference_text']?:null,$d['phone']?:null,$d['instructions']?:null]);
            $id=(int)$db->lastInsertId();
            foreach($items as $x){$p=$x['p'];$q=$x['qty'];$price=$x['price'];$db->prepare("INSERT INTO order_items(order_id,product_id,quantity,unit_price,subtotal) VALUES(?,?,?,?,?)")->execute([$id,$p['id'],$q,$price,$price*$q]);$db->prepare("UPDATE stocks SET reserved_quantity=reserved_quantity+? WHERE product_id=?")->execute([$q,$p['id']]);}
            if($couponId){$db->prepare("INSERT INTO coupon_usage(coupon_id,customer_id,order_id) VALUES(?,?,?)")->execute([$couponId,$customerId,$id]);$db->prepare("UPDATE coupons SET used_count=used_count+1 WHERE id=?")->execute([$couponId]);}
            $db->prepare("INSERT INTO deliveries(order_id,status) VALUES(?,'A_AFFECTER')")->execute([$id]);
            $db->prepare("INSERT INTO notifications(role_name,type,title,message,link) VALUES('ADMIN','NEW_ORDER','Nouvelle commande',?,?)")->execute(['Commande '.$no.' reçue.','orders/'.$id]);
            return$id;
        });
    }

    public function updateStatus(int $id,string $status,int $userId):void {
        Database::transaction(function($db)use($id,$status,$userId){
            $st=$db->prepare("SELECT * FROM orders WHERE id=? FOR UPDATE");$st->execute([$id]);$o=$st->fetch();
            if(!$o)throw new \RuntimeException('Commande introuvable.');
            $old=$o['status'];if($old===$status)return;

            if($status==='ANNULEE'&&$old!=='LIVREE'){
                $it=$db->prepare("SELECT * FROM order_items WHERE order_id=?");$it->execute([$id]);
                foreach($it->fetchAll() as $row)$db->prepare("UPDATE stocks SET reserved_quantity=GREATEST(0,reserved_quantity-?) WHERE product_id=?")->execute([$row['quantity'],$row['product_id']]);
                $cu=$db->prepare("SELECT coupon_id FROM coupon_usage WHERE order_id=?");$cu->execute([$id]);$cid=$cu->fetchColumn();if($cid){$db->prepare("UPDATE coupons SET used_count=GREATEST(0,used_count-1) WHERE id=?")->execute([$cid]);$db->prepare("DELETE FROM coupon_usage WHERE order_id=?")->execute([$id]);}
            } elseif($status==='LIVREE'&&$old!=='LIVREE') {
                $it=$db->prepare("SELECT oi.*,p.purchase_price,p.min_stock FROM order_items oi JOIN products p ON p.id=oi.product_id WHERE oi.order_id=?");$it->execute([$id]);$cost=0.0;
                foreach($it->fetchAll() as $row){
                    $ss=$db->prepare("SELECT quantity,reserved_quantity FROM stocks WHERE product_id=? FOR UPDATE");$ss->execute([$row['product_id']]);$s=$ss->fetch();$before=(float)$s['quantity'];$after=$before-(float)$row['quantity'];
                    if($after<0)throw new \RuntimeException('Stock insuffisant au moment de la livraison.');
                    $db->prepare("UPDATE stocks SET quantity=?,reserved_quantity=GREATEST(0,reserved_quantity-?) WHERE product_id=?")->execute([$after,$row['quantity'],$row['product_id']]);
                    $db->prepare("INSERT INTO stock_movements(product_id,type,quantity,stock_before,stock_after,user_id,reason,reference_no) VALUES(?,'VENTE',?,?,?,?,?,?)")->execute([$row['product_id'],$row['quantity'],$before,$after,$userId,'Commande livrée',$o['order_no']]);
                    $cost+=(float)$row['purchase_price']*(float)$row['quantity'];
                    if(($after-(float)$s['reserved_quantity'])<=(float)$row['min_stock'])$db->prepare("INSERT INTO notifications(role_name,type,title,message,link) VALUES('STOCK_MANAGER','LOW_STOCK','Stock faible',?,?)")->execute(['Produit #'.$row['product_id'].' au seuil de stock.','stock']);
                }
                $saleNo='WEB-'.date('Ymd-His').'-'.random_int(10,99);
                $db->prepare("INSERT INTO sales(sale_no,customer_id,user_id,subtotal,discount,total,cost_total) VALUES(?,?,?,?,?,?,?)")->execute([$saleNo,$o['customer_id'],$userId,$o['subtotal'],$o['discount'],$o['total'],$cost]);
                $sid=(int)$db->lastInsertId();
                $oi=$db->prepare("SELECT * FROM order_items WHERE order_id=?");$oi->execute([$id]);
                foreach($oi->fetchAll() as $row){$pp=$db->prepare("SELECT purchase_price FROM products WHERE id=?");$pp->execute([$row['product_id']]);$buy=(float)$pp->fetch()['purchase_price'];$db->prepare("INSERT INTO sale_items(sale_id,product_id,quantity,unit_price,purchase_price,subtotal) VALUES(?,?,?,?,?,?)")->execute([$sid,$row['product_id'],$row['quantity'],$row['unit_price'],$buy,$row['subtotal']]);}
                $db->prepare("INSERT INTO sale_payments(sale_id,method,currency,amount,exchange_rate,reference_no) VALUES(?,'ESPECES','USD',?,1,?)")->execute([$sid,$o['total'],$o['order_no']]);
                $pointsRate=(float)($db->query("SELECT COALESCE((SELECT value FROM settings WHERE `key`='loyalty_points_per_usd'),'1')")->fetchColumn()?:1);$points=(int)floor((float)$o['total']*$pointsRate);
                $db->prepare("UPDATE customers SET total_spent=total_spent+?,order_count=order_count+1 WHERE id=?")->execute([$o['total'],$o['customer_id']]);
                $db->prepare("INSERT IGNORE INTO loyalty_accounts(customer_id) VALUES(?)")->execute([$o['customer_id']]);
                $db->prepare("UPDATE loyalty_accounts SET points_balance=points_balance+? WHERE customer_id=?")->execute([$points,$o['customer_id']]);
                $db->prepare("INSERT INTO loyalty_transactions(loyalty_account_id,type,points,reference_no,notes) SELECT id,'GAIN',?,?,? FROM loyalty_accounts WHERE customer_id=?")->execute([$points,$saleNo,'Points commande livrée',$o['customer_id']]);$db->prepare("UPDATE loyalty_accounts la JOIN customers c ON c.id=la.customer_id SET la.tier=CASE WHEN c.total_spent>=1000 THEN 'PLATINUM' WHEN c.total_spent>=500 THEN 'GOLD' WHEN c.total_spent>=200 THEN 'SILVER' ELSE 'STANDARD' END WHERE la.customer_id=?")->execute([$o['customer_id']]);
                $db->prepare("UPDATE deliveries SET status='LIVREE',delivered_at=NOW() WHERE order_id=?")->execute([$id]);
            }
            if($status==='EN_LIVRAISON')$db->prepare("UPDATE deliveries SET status='EN_COURS' WHERE order_id=?")->execute([$id]);
            $db->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$status,$id]);
        });
    }

    public function assignDelivery(int $id,int $deliveryUserId):void {
        $this->exec("UPDATE orders SET assigned_delivery_user_id=? WHERE id=?",[$deliveryUserId,$id]);
        $this->exec("UPDATE deliveries SET delivery_user_id=?,status='A_RECUPERER',assigned_at=NOW() WHERE order_id=?",[$deliveryUserId,$id]);
    }
}
