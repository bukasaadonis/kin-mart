<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Services\SimplePdf;

final class ReportController extends Controller {
    public function index():void{
        $from=$_GET['from']??date('Y-m-01');$to=$_GET['to']??date('Y-m-d');$type=$_GET['type']??'sales';
        [$headers,$rows,$summary]=$this->data($type,$from,$to);
        $this->view('reports/index',compact('from','to','type','headers','rows','summary')+['title'=>'Rapports']);
    }
    public function csv():never{$this->download('csv');}
    public function xls():never{$this->download('xls');}
    public function pdf():never{$this->download('pdf');}

    private function download(string $format):never{
        $from=$_GET['from']??date('Y-m-01');$to=$_GET['to']??date('Y-m-d');$type=$_GET['type']??'sales';
        [$headers,$rows]=$this->data($type,$from,$to);$filename='kinmart_'.$type.'_'.date('Ymd');
        if($format==='csv'){header('Content-Type: text/csv; charset=utf-8');header('Content-Disposition: attachment; filename="'.$filename.'.csv"');$o=fopen('php://output','w');fputcsv($o,$headers,';');foreach($rows as $row)fputcsv($o,array_values($row),';');fclose($o);exit;}
        if($format==='xls'){header('Content-Type: application/vnd.ms-excel; charset=utf-8');header('Content-Disposition: attachment; filename="'.$filename.'.xls"');echo '<meta charset="utf-8"><table border="1"><tr>';foreach($headers as $h)echo'<th>'.e($h).'</th>';echo'</tr>';foreach($rows as $row){echo'<tr>';foreach($row as $v)echo'<td>'.e($v).'</td>';echo'</tr>';}echo'</table>';exit;}
        $lines=["Période: $from au $to",implode(' | ',$headers)];foreach($rows as $row)$lines[]=implode(' | ',array_map(fn($v)=>(string)$v,$row));SimplePdf::output('KIN-MART - Rapport '.strtoupper($type),$lines,$filename.'.pdf');
    }

    private function data(string $type,string $from,string $to):array{
        $db=Database::connection();$summary=[];
        switch($type){
            case 'stock':
                $headers=['Code','Produit','Catégorie','Stock','Réservé','Disponible','Min','Valeur achat'];
                $rows=$db->query("SELECT p.internal_code,p.name,c.name category,s.quantity,s.reserved_quantity,(s.quantity-s.reserved_quantity) available,p.min_stock,ROUND(s.quantity*p.purchase_price,2) stock_value FROM products p JOIN categories c ON c.id=p.category_id JOIN stocks s ON s.product_id=p.id WHERE p.status='ACTIVE' ORDER BY p.name")->fetchAll();break;
            case 'low_stock':
                $headers=['Code','Produit','Disponible','Seuil min','Stock max'];
                $rows=$db->query("SELECT p.internal_code,p.name,(s.quantity-s.reserved_quantity) available,p.min_stock,p.max_stock FROM products p JOIN stocks s ON s.product_id=p.id WHERE p.status='ACTIVE' AND (s.quantity-s.reserved_quantity)<=p.min_stock ORDER BY available")->fetchAll();break;
            case 'expiry':
                $headers=['Code','Produit','Expiration','Stock','Jours restants'];
                $rows=$db->query("SELECT p.internal_code,p.name,p.expiration_date,s.quantity,DATEDIFF(p.expiration_date,CURDATE()) days_left FROM products p JOIN stocks s ON s.product_id=p.id WHERE p.expiration_date IS NOT NULL ORDER BY p.expiration_date")->fetchAll();break;
            case 'customers':
                $headers=['Code','Client','Téléphone','Segment','Total achats','Commandes','Points','Niveau'];
                $rows=$db->query("SELECT c.code,CONCAT(c.first_name,' ',c.last_name) customer,c.phone,c.segment,c.total_spent,c.order_count,COALESCE(l.points_balance,0) points,COALESCE(l.tier,'STANDARD') tier FROM customers c LEFT JOIN loyalty_accounts l ON l.customer_id=c.id ORDER BY c.total_spent DESC")->fetchAll();break;
            case 'orders':
                $headers=['N°','Date','Client','Type','Statut','Sous-total','Remise','Livraison','Total'];
                $st=$db->prepare("SELECT o.order_no,o.created_at,CONCAT(c.first_name,' ',c.last_name) customer,o.fulfillment,o.status,o.subtotal,o.discount,o.delivery_fee,o.total FROM orders o JOIN customers c ON c.id=o.customer_id WHERE DATE(o.created_at) BETWEEN ? AND ? ORDER BY o.created_at DESC");$st->execute([$from,$to]);$rows=$st->fetchAll();break;
            case 'purchases':
                $headers=['N°','Date','Fournisseur','Statut','Total','Réception'];
                $st=$db->prepare("SELECT po.order_no,po.created_at,s.company_name,po.status,po.total,po.received_at FROM purchase_orders po JOIN suppliers s ON s.id=po.supplier_id WHERE DATE(po.created_at) BETWEEN ? AND ? ORDER BY po.created_at DESC");$st->execute([$from,$to]);$rows=$st->fetchAll();break;
            case 'returns':
                $headers=['N° retour','Date','Vente','Motif','Montant','Utilisateur'];
                $st=$db->prepare("SELECT r.return_no,r.created_at,s.sale_no,r.reason,r.amount,u.full_name FROM `returns` r JOIN sales s ON s.id=r.sale_id JOIN users u ON u.id=r.user_id WHERE DATE(r.created_at) BETWEEN ? AND ? ORDER BY r.created_at DESC");$st->execute([$from,$to]);$rows=$st->fetchAll();break;
            case 'inventories':
                $headers=['N° inventaire','Date','Statut','Utilisateur','Validé le','Nb lignes','Écart absolu'];
                $st=$db->prepare("SELECT i.inventory_no,i.created_at,i.status,u.full_name,i.validated_at,COUNT(ii.id) lines_count,COALESCE(SUM(ABS(ii.difference_qty)),0) total_gap FROM inventories i JOIN users u ON u.id=i.user_id LEFT JOIN inventory_items ii ON ii.inventory_id=i.id WHERE DATE(i.created_at) BETWEEN ? AND ? GROUP BY i.id ORDER BY i.created_at DESC");$st->execute([$from,$to]);$rows=$st->fetchAll();break;
            case 'loyalty':
                $headers=['Code','Client','Points','Niveau','Total achats','Fréquence'];
                $rows=$db->query("SELECT c.code,CONCAT(c.first_name,' ',c.last_name) customer,COALESCE(l.points_balance,0) points,COALESCE(l.tier,'STANDARD') tier,c.total_spent,c.order_count FROM customers c LEFT JOIN loyalty_accounts l ON l.customer_id=c.id ORDER BY points DESC")->fetchAll();break;
            default:
                $type='sales';$headers=['N°','Date','Caissier','Client','Sous-total','Remise','Total','Coût','Marge','Statut'];
                $st=$db->prepare("SELECT s.sale_no,s.created_at,u.full_name cashier,COALESCE(CONCAT(c.first_name,' ',c.last_name),'Passage') customer,s.subtotal,s.discount,s.total,s.cost_total,ROUND(s.total-s.cost_total,2) margin,s.status FROM sales s JOIN users u ON u.id=s.user_id LEFT JOIN customers c ON c.id=s.customer_id WHERE DATE(s.created_at) BETWEEN ? AND ? ORDER BY s.created_at DESC");$st->execute([$from,$to]);$rows=$st->fetchAll();
                $summary=['ca'=>array_sum(array_map(fn($r)=>(float)$r['total'],$rows)),'cost'=>array_sum(array_map(fn($r)=>(float)$r['cost_total'],$rows))];break;
        }
        return[$headers,$rows,$summary];
    }
}
