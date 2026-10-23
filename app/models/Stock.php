<?php
namespace App\Models;
use App\Core\Model;
use App\Core\Database;
final class Stock extends Model {
 public function movements(int $limit=200):array{return $this->all("SELECT sm.*,p.name product,p.internal_code,u.full_name user_name FROM stock_movements sm JOIN products p ON p.id=sm.product_id LEFT JOIN users u ON u.id=sm.user_id ORDER BY sm.created_at DESC LIMIT $limit");}
 public function adjust(int $productId,float $quantity,string $type,string $reason,int $userId):void{Database::transaction(function($db)use($productId,$quantity,$type,$reason,$userId){$st=$db->prepare("SELECT quantity FROM stocks WHERE product_id=? FOR UPDATE");$st->execute([$productId]);$row=$st->fetch();if(!$row)throw new \RuntimeException('Stock introuvable');$before=(float)$row['quantity'];$delta=in_array($type,['ENTREE','RETOUR_CLIENT','AJUSTEMENT'],true)?$quantity:-$quantity;$after=$before+$delta;if($after<0)throw new \RuntimeException('Stock insuffisant');$db->prepare("UPDATE stocks SET quantity=? WHERE product_id=?")->execute([$after,$productId]);$db->prepare("INSERT INTO stock_movements(product_id,type,quantity,stock_before,stock_after,user_id,reason,reference_no) VALUES(?,?,?,?,?,?,?,?)")->execute([$productId,$type,$quantity,$before,$after,$userId,$reason,'MAN-'.date('YmdHis')]);});}
}
