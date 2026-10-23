<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Product;
use App\Models\Stock;
use App\Services\AuditService;
final class StockController extends Controller {
 public function index():void{$p=new Product();$this->view('stock/index',['low'=>$p->lowStock(),'expiring'=>$p->expiring(30),'movements'=>(new Stock())->movements(60),'title'=>'Stocks']);}
 public function movement():void{$pid=(int)($_POST['product_id']??0);$qty=(float)($_POST['quantity']??0);$type=$_POST['type']??'ENTREE';$allowed=['ENTREE','RETOUR_CLIENT','CASSE','PERTE','EXPIRATION','AJUSTEMENT','RETOUR_FOURNISSEUR'];if(!$pid||$qty<=0||!in_array($type,$allowed,true)){flash('error','Mouvement invalide.');redirect('stock');}try{(new Stock())->adjust($pid,$qty,$type,trim($_POST['reason']??'Mouvement manuel'),(int)$_SESSION['user']['id']);AuditService::log('MOVEMENT','STOCK',null,$_POST);flash('success','Stock mis à jour.');}catch(\Throwable $e){flash('error',$e->getMessage());}redirect('stock');}
 public function inventoryForm():void{$items=(new Product())->paginate('',null,1,500)['items'];$this->view('stock/inventory',['items'=>$items,'title'=>'Inventaire physique']);}
 public function inventoryStore():void{$phys=$_POST['physical']??[];$db=Database::connection();try{\App\Core\Database::transaction(function($db)use($phys){$no='INV-'.date('Ymd-His');$db->prepare("INSERT INTO inventories(inventory_no,status,user_id) VALUES(?,'OUVERT',?)")->execute([$no,$_SESSION['user']['id']]);$inv=(int)$db->lastInsertId();foreach($phys as $pid=>$val){if($val==='')continue;$st=$db->prepare("SELECT quantity FROM stocks WHERE product_id=? FOR UPDATE");$st->execute([(int)$pid]);$row=$st->fetch();if(!$row)continue;$sys=(float)$row['quantity'];$phy=(float)$val;$diff=$phy-$sys;$db->prepare("INSERT INTO inventory_items(inventory_id,product_id,system_qty,physical_qty,difference_qty) VALUES(?,?,?,?,?)")->execute([$inv,$pid,$sys,$phy,$diff]);if(abs($diff)>0.0001){$db->prepare("UPDATE stocks SET quantity=? WHERE product_id=?")->execute([$phy,$pid]);$db->prepare("INSERT INTO stock_movements(product_id,type,quantity,stock_before,stock_after,user_id,reason,reference_no) VALUES(?,'AJUSTEMENT',?,?,?,?,?,?)")->execute([$pid,abs($diff),$sys,$phy,$_SESSION['user']['id'],'Inventaire physique',$no]);}}$db->prepare("UPDATE inventories SET status='VALIDE',validated_at=NOW() WHERE id=?")->execute([$inv]);});flash('success','Inventaire validé et écarts ajustés.');}catch(\Throwable $e){flash('error',$e->getMessage());}redirect('stock');}
}
