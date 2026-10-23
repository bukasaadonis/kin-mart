<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Order;
final class DeliveryController extends Controller {
 public function index():void{$db=Database::connection();$uid=(int)$_SESSION['user']['id'];$st=$db->prepare("SELECT o.*,CONCAT(c.first_name,' ',c.last_name) customer FROM orders o JOIN customers c ON c.id=o.customer_id WHERE o.assigned_delivery_user_id=? AND o.status IN('PRETE','EN_LIVRAISON') ORDER BY o.created_at");$st->execute([$uid]);$this->view('deliveries/index',['orders'=>$st->fetchAll(),'title'=>'Mes livraisons']);}
 public function status(string $id):void{$status=$_POST['status']??'';if(!in_array($status,['EN_LIVRAISON','LIVREE'],true)){flash('error','Statut invalide');redirect('deliveries');}try{(new Order())->updateStatus((int)$id,$status,(int)$_SESSION['user']['id']);flash('success','Livraison mise à jour.');}catch(\Throwable $e){flash('error',$e->getMessage());}redirect('deliveries');}
}
