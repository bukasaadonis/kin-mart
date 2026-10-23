<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

final class Sale extends Model {
    public function createSale(
        array $cart,
        ?int $customerId,
        array $payment,
        int $userId,
        ?int $cashSessionId = null,
        float $manualDiscount = 0,
        int $pointsUse = 0
    ): array {
        return Database::transaction(function ($db) use ($cart, $customerId, $payment, $userId, $cashSessionId, $manualDiscount, $pointsUse) {
            if (!$cart) throw new \RuntimeException('Le panier est vide.');

            $subtotal = 0.0;
            $cost = 0.0;
            $resolved = [];
            foreach ($cart as $pid => $qty) {
                $st = $db->prepare("SELECT p.id,p.name,p.sale_price,p.purchase_price,p.min_stock,p.category_id,
                    s.quantity,s.reserved_quantity,
                    COALESCE((SELECT CASE
                        WHEN pr.type='PERCENT' THEN GREATEST(0,p.sale_price-(p.sale_price*pr.value/100))
                        WHEN pr.type='FIXED' THEN GREATEST(0,p.sale_price-pr.value)
                        ELSE p.sale_price END
                    FROM promotions pr
                    LEFT JOIN promotion_products pp ON pp.promotion_id=pr.id
                    WHERE pr.status='ACTIVE' AND NOW() BETWEEN pr.start_at AND pr.end_at
                    AND (pp.product_id=p.id OR pr.category_id=p.category_id)
                    ORDER BY pr.value DESC LIMIT 1),p.sale_price) effective_price
                    FROM products p JOIN stocks s ON s.product_id=p.id
                    WHERE p.id=? AND p.status='ACTIVE' FOR UPDATE");
                $st->execute([(int)$pid]);
                $p = $st->fetch();
                if (!$p) throw new \RuntimeException('Produit introuvable.');
                $qty = (float)$qty;
                $available = (float)$p['quantity'] - (float)$p['reserved_quantity'];
                if ($qty <= 0 || $qty > $available) throw new \RuntimeException('Stock insuffisant pour ' . $p['name']);
                $price = (float)$p['effective_price'];
                $subtotal += $price * $qty;
                $cost += (float)$p['purchase_price'] * $qty;
                $resolved[] = ['p' => $p, 'qty' => $qty, 'price' => $price];
            }

            $manualDiscount = max(0, min($manualDiscount, $subtotal));
            $loyaltyDiscount = 0.0;
            $pointsConsumed = 0;
            $loyaltyAccountId = null;

            if ($customerId && $pointsUse > 0) {
                $acc = $db->prepare("SELECT id,points_balance FROM loyalty_accounts WHERE customer_id=? FOR UPDATE");
                $acc->execute([$customerId]);
                $a = $acc->fetch();
                if ($a) {
                    $loyaltyAccountId = (int)$a['id'];
                    $threshold = (int)($db->query("SELECT COALESCE((SELECT value FROM settings WHERE `key`='loyalty_points_per_reward'),'100')")->fetchColumn() ?: 100);
                    $reward = (float)($db->query("SELECT COALESCE((SELECT value FROM settings WHERE `key`='loyalty_reward_usd'),'1')")->fetchColumn() ?: 1);
                    $usable = min(max(0, $pointsUse), (int)$a['points_balance']);
                    $groups = $threshold > 0 ? intdiv($usable, $threshold) : 0;
                    $pointsConsumed = $groups * $threshold;
                    $loyaltyDiscount = min($subtotal - $manualDiscount, $groups * $reward);
                    if ($pointsConsumed > 0) {
                        $db->prepare("UPDATE loyalty_accounts SET points_balance=points_balance-? WHERE id=?")->execute([$pointsConsumed, $loyaltyAccountId]);
                    }
                }
            }

            $discount = min($subtotal, $manualDiscount + $loyaltyDiscount);
            $total = max(0, $subtotal - $discount);
            $saleNo = 'VTE-' . date('Ymd-His') . '-' . random_int(10, 99);

            $db->prepare("INSERT INTO sales(sale_no,customer_id,cash_session_id,user_id,subtotal,discount,total,cost_total)
                VALUES(?,?,?,?,?,?,?,?)")
                ->execute([$saleNo, $customerId, $cashSessionId, $userId, $subtotal, $discount, $total, $cost]);
            $saleId = (int)$db->lastInsertId();

            foreach ($resolved as $x) {
                $p = $x['p']; $qty = $x['qty']; $price = $x['price'];
                $line = $price * $qty;
                $db->prepare("INSERT INTO sale_items(sale_id,product_id,quantity,unit_price,purchase_price,subtotal)
                    VALUES(?,?,?,?,?,?)")
                    ->execute([$saleId, $p['id'], $qty, $price, $p['purchase_price'], $line]);
                $before = (float)$p['quantity'];
                $after = $before - $qty;
                $db->prepare("UPDATE stocks SET quantity=? WHERE product_id=?")->execute([$after, $p['id']]);
                $db->prepare("INSERT INTO stock_movements(product_id,type,quantity,stock_before,stock_after,user_id,reason,reference_no)
                    VALUES(?,'VENTE',?,?,?,?,?,?)")
                    ->execute([$p['id'], $qty, $before, $after, $userId, 'Vente POS', $saleNo]);
                if (($after - (float)$p['reserved_quantity']) <= (float)$p['min_stock']) {
                    $db->prepare("INSERT INTO notifications(role_name,type,title,message,link)
                        VALUES('STOCK_MANAGER','LOW_STOCK','Stock faible',?,?)")
                        ->execute([$p['name'] . ' est au seuil de stock.', 'stock']);
                }
            }

            $db->prepare("INSERT INTO sale_payments(sale_id,method,currency,amount,exchange_rate,reference_no,received_amount,change_amount)
                VALUES(?,?,?,?,?,?,?,?)")
                ->execute([$saleId, $payment['method'], $payment['currency'], $payment['amount'], $payment['exchange_rate'], $payment['reference_no'] ?: null, $payment['received_amount'], $payment['change_amount']]);

            $pointsEarned = 0;
            if ($customerId) {
                $pointsRate = (float)($db->query("SELECT COALESCE((SELECT value FROM settings WHERE `key`='loyalty_points_per_usd'),'1')")->fetchColumn() ?: 1);
                $pointsEarned = (int)floor($total * $pointsRate);
                $db->prepare("UPDATE customers SET total_spent=total_spent+?,order_count=order_count+1 WHERE id=?")->execute([$total, $customerId]);
                $db->prepare("INSERT IGNORE INTO loyalty_accounts(customer_id) VALUES(?)")->execute([$customerId]);
                if (!$loyaltyAccountId) {
                    $q=$db->prepare("SELECT id FROM loyalty_accounts WHERE customer_id=?");$q->execute([$customerId]);$loyaltyAccountId=(int)$q->fetchColumn();
                }
                if ($pointsConsumed > 0) {
                    $db->prepare("INSERT INTO loyalty_transactions(loyalty_account_id,type,points,reference_no,notes)
                        VALUES(?,'UTILISATION',?,?,?)")
                        ->execute([$loyaltyAccountId, -$pointsConsumed, $saleNo, 'Réduction fidélité']);
                }
                if ($pointsEarned > 0) {
                    $db->prepare("UPDATE loyalty_accounts SET points_balance=points_balance+? WHERE id=?")->execute([$pointsEarned, $loyaltyAccountId]);
                    $db->prepare("INSERT INTO loyalty_transactions(loyalty_account_id,type,points,reference_no,notes)
                        VALUES(?,'GAIN',?,?,?)")
                        ->execute([$loyaltyAccountId, $pointsEarned, $saleNo, 'Points de vente']);
                }
                $db->prepare("UPDATE loyalty_accounts la JOIN customers c ON c.id=la.customer_id SET la.tier=CASE WHEN c.total_spent>=1000 THEN 'PLATINUM' WHEN c.total_spent>=500 THEN 'GOLD' WHEN c.total_spent>=200 THEN 'SILVER' ELSE 'STANDARD' END WHERE la.customer_id=?")->execute([$customerId]);
            }

            return [
                'id'=>$saleId,'sale_no'=>$saleNo,'subtotal'=>$subtotal,'discount'=>$discount,'total'=>$total,
                'points_used'=>$pointsConsumed,'points_earned'=>$pointsEarned
            ];
        });
    }

    public function find(int $id): ?array {
        $sale=$this->one("SELECT s.*,u.full_name cashier,CONCAT(c.first_name,' ',c.last_name) customer FROM sales s JOIN users u ON u.id=s.user_id LEFT JOIN customers c ON c.id=s.customer_id WHERE s.id=?",[$id]);
        if(!$sale)return null;
        $sale['items']=$this->all("SELECT si.*,p.name,p.internal_code FROM sale_items si JOIN products p ON p.id=si.product_id WHERE si.sale_id=?",[$id]);
        $sale['payments']=$this->all("SELECT * FROM sale_payments WHERE sale_id=?",[$id]);
        return $sale;
    }

    public function list(string $from,string $to):array {
        return $this->all("SELECT s.*,u.full_name cashier,CONCAT(c.first_name,' ',c.last_name) customer FROM sales s JOIN users u ON u.id=s.user_id LEFT JOIN customers c ON c.id=s.customer_id WHERE DATE(s.created_at) BETWEEN ? AND ? ORDER BY s.created_at DESC",[$from,$to]);
    }

    public function cancel(int $id,int $userId,string $reason):void {
        Database::transaction(function($db)use($id,$userId,$reason){
            $st=$db->prepare("SELECT * FROM sales WHERE id=? FOR UPDATE");$st->execute([$id]);$sale=$st->fetch();
            if(!$sale||$sale['status']!=='COMPLETED')throw new \RuntimeException('Vente non annulable.');
            $items=$db->prepare("SELECT * FROM sale_items WHERE sale_id=?");$items->execute([$id]);
            foreach($items->fetchAll() as $it){
                $s=$db->prepare("SELECT quantity FROM stocks WHERE product_id=? FOR UPDATE");$s->execute([$it['product_id']]);
                $before=(float)$s->fetch()['quantity'];$after=$before+(float)$it['quantity'];
                $db->prepare("UPDATE stocks SET quantity=? WHERE product_id=?")->execute([$after,$it['product_id']]);
                $db->prepare("INSERT INTO stock_movements(product_id,type,quantity,stock_before,stock_after,user_id,reason,reference_no) VALUES(?,'RETOUR_CLIENT',?,?,?,?,?,?)")
                    ->execute([$it['product_id'],$it['quantity'],$before,$after,$userId,$reason,$sale['sale_no']]);
            }
            $db->prepare("UPDATE sales SET status='CANCELLED' WHERE id=?")->execute([$id]);
        });
    }

    public function returnsForSale(int $saleId):array {
        return $this->all("SELECT r.*,u.full_name FROM `returns` r JOIN users u ON u.id=r.user_id WHERE r.sale_id=? ORDER BY r.created_at DESC",[$saleId]);
    }

    public function returnItem(int $saleId,int $saleItemId,float $qty,int $userId,string $reason):void {
        Database::transaction(function($db)use($saleId,$saleItemId,$qty,$userId,$reason){
            if($qty<=0)throw new \RuntimeException('Quantité invalide.');
            $st=$db->prepare("SELECT si.*,s.sale_no,s.status FROM sale_items si JOIN sales s ON s.id=si.sale_id WHERE si.id=? AND si.sale_id=? FOR UPDATE");
            $st->execute([$saleItemId,$saleId]);$it=$st->fetch();
            if(!$it||!in_array($it['status'],['COMPLETED','REFUNDED'],true))throw new \RuntimeException('Ligne de vente non retournable.');
            $q=$db->prepare("SELECT COALESCE(SUM(ri.quantity),0) q FROM return_items ri JOIN `returns` r ON r.id=ri.return_id WHERE ri.sale_item_id=? AND r.status='APPROVED'");$q->execute([$saleItemId]);$already=(float)$q->fetch()['q'];
            if($already+$qty>(float)$it['quantity'])throw new \RuntimeException('La quantité retournée dépasse la quantité vendue.');
            $amount=$qty*(float)$it['unit_price'];$no='RET-'.date('Ymd-His').'-'.random_int(10,99);
            $db->prepare("INSERT INTO `returns`(return_no,sale_id,user_id,amount,reason,status) VALUES(?,?,?,?,?,'APPROVED')")->execute([$no,$saleId,$userId,$amount,$reason]);
            $rid=(int)$db->lastInsertId();
            $db->prepare("INSERT INTO return_items(return_id,sale_item_id,product_id,quantity,amount) VALUES(?,?,?,?,?)")->execute([$rid,$saleItemId,$it['product_id'],$qty,$amount]);
            $ss=$db->prepare("SELECT quantity FROM stocks WHERE product_id=? FOR UPDATE");$ss->execute([$it['product_id']]);$before=(float)$ss->fetch()['quantity'];$after=$before+$qty;
            $db->prepare("UPDATE stocks SET quantity=? WHERE product_id=?")->execute([$after,$it['product_id']]);
            $db->prepare("INSERT INTO stock_movements(product_id,type,quantity,stock_before,stock_after,user_id,reason,reference_no) VALUES(?,'RETOUR_CLIENT',?,?,?,?,?,?)")->execute([$it['product_id'],$qty,$before,$after,$userId,$reason,$no]);
            $all=$db->prepare("SELECT COALESCE(SUM(amount),0) a FROM `returns` WHERE sale_id=? AND status='APPROVED'");$all->execute([$saleId]);$returned=(float)$all->fetch()['a'];
            $sale=$db->prepare("SELECT total FROM sales WHERE id=?");$sale->execute([$saleId]);$sr=$sale->fetch();if($returned>=(float)$sr['total']-0.001)$db->prepare("UPDATE sales SET status='REFUNDED' WHERE id=?")->execute([$saleId]);
        });
    }
}
