<?php
namespace App\Services;
use App\Core\Database;
final class RecommendationService {
 public static function forCustomer(int $customerId,int $limit=8):array{
  $db=Database::connection();
  $sql="SELECT p.id,p.name,p.sale_price,p.image,c.name category,(s.quantity-s.reserved_quantity) available_qty,
      COUNT(si.id) affinity
    FROM products p JOIN categories c ON c.id=p.category_id JOIN stocks s ON s.product_id=p.id
    LEFT JOIN sale_items si ON si.product_id=p.id
    LEFT JOIN sales sa ON sa.id=si.sale_id AND sa.customer_id=? AND sa.status='COMPLETED'
    WHERE p.status='ACTIVE' AND (s.quantity-s.reserved_quantity)>0
      AND (p.category_id IN (SELECT DISTINCT p2.category_id FROM sale_items si2 JOIN sales sa2 ON sa2.id=si2.sale_id JOIN products p2 ON p2.id=si2.product_id WHERE sa2.customer_id=? AND sa2.status='COMPLETED')
           OR NOT EXISTS(SELECT 1 FROM sales sx WHERE sx.customer_id=? AND sx.status='COMPLETED'))
    GROUP BY p.id,p.name,p.sale_price,p.image,c.name,s.quantity,s.reserved_quantity
    ORDER BY affinity DESC,p.created_at DESC LIMIT ".max(1,(int)$limit);
  $st=$db->prepare($sql);$st->execute([$customerId,$customerId,$customerId]);return$st->fetchAll();
 }
}
