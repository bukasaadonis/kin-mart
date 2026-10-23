<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Product;
final class DashboardController extends Controller {
 public function index():void{$db=Database::connection();$one=fn($sql)=>(float)$db->query($sql)->fetchColumn();$stats=[
 'today'=>$one("SELECT COALESCE(SUM(total),0) FROM sales WHERE status='COMPLETED' AND DATE(created_at)=CURDATE()"),
 'week'=>$one("SELECT COALESCE(SUM(total),0) FROM sales WHERE status='COMPLETED' AND YEARWEEK(created_at,1)=YEARWEEK(CURDATE(),1)"),
 'month'=>$one("SELECT COALESCE(SUM(total),0) FROM sales WHERE status='COMPLETED' AND YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE())"),
 'year'=>$one("SELECT COALESCE(SUM(total),0) FROM sales WHERE status='COMPLETED' AND YEAR(created_at)=YEAR(CURDATE())"),
 'profit'=>$one("SELECT COALESCE(SUM(total-cost_total),0) FROM sales WHERE status='COMPLETED' AND YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE())"),
 'sales'=>(int)$one("SELECT COUNT(*) FROM sales WHERE status='COMPLETED' AND DATE(created_at)=CURDATE()"),
 'orders'=>(int)$one("SELECT COUNT(*) FROM orders WHERE status NOT IN('LIVREE','ANNULEE')"),
 'clients'=>(int)$one("SELECT COUNT(*) FROM customers WHERE status='ACTIVE'"),
 'products'=>(int)$one("SELECT COUNT(*) FROM products WHERE status='ACTIVE'"),
 'stock_value'=>$one("SELECT COALESCE(SUM(s.quantity*p.purchase_price),0) FROM stocks s JOIN products p ON p.id=s.product_id WHERE p.status='ACTIVE'"),
 'low'=>(int)$one("SELECT COUNT(*) FROM stocks s JOIN products p ON p.id=s.product_id WHERE p.status='ACTIVE' AND (s.quantity-s.reserved_quantity)<=p.min_stock"),
 'out'=>(int)$one("SELECT COUNT(*) FROM stocks s JOIN products p ON p.id=s.product_id WHERE p.status='ACTIVE' AND (s.quantity-s.reserved_quantity)<=0"),
 'expiring'=>(int)$one("SELECT COUNT(*) FROM products WHERE status='ACTIVE' AND expiration_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(),INTERVAL 30 DAY)"),
 ];
 $daily=$db->query("SELECT DATE_FORMAT(d.dt,'%d/%m') label,COALESCE(SUM(s.total),0) value FROM (SELECT CURDATE() dt UNION SELECT DATE_SUB(CURDATE(),INTERVAL 1 DAY) UNION SELECT DATE_SUB(CURDATE(),INTERVAL 2 DAY) UNION SELECT DATE_SUB(CURDATE(),INTERVAL 3 DAY) UNION SELECT DATE_SUB(CURDATE(),INTERVAL 4 DAY) UNION SELECT DATE_SUB(CURDATE(),INTERVAL 5 DAY) UNION SELECT DATE_SUB(CURDATE(),INTERVAL 6 DAY)) d LEFT JOIN sales s ON DATE(s.created_at)=d.dt AND s.status='COMPLETED' GROUP BY d.dt ORDER BY d.dt")->fetchAll();
 $top=$db->query("SELECT p.name,SUM(si.quantity) qty,SUM(si.subtotal) revenue FROM sale_items si JOIN sales s ON s.id=si.sale_id JOIN products p ON p.id=si.product_id WHERE s.status='COMPLETED' GROUP BY p.id,p.name ORDER BY qty DESC LIMIT 5")->fetchAll();
 $payments=$db->query("SELECT method,SUM(amount) value FROM sale_payments GROUP BY method ORDER BY value DESC")->fetchAll();
 $notifs=$db->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 8")->fetchAll();
 $this->view('dashboard/index',compact('stats','daily','top','payments','notifs')+['title'=>'Tableau de bord']);}
}
