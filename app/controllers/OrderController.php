<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Order;
use App\Services\AuditService;
final class OrderController extends Controller {
 public function index():void{$this->view('orders/index',['orders'=>(new Order())->list(),'title'=>'Commandes']);}
 public function show(string $id):void{$o=(new Order())->find((int)$id);if(!$o)redirect('orders');$deliveryUsers=Database::connection()->query("SELECT u.id,u.full_name FROM users u JOIN roles r ON r.id=u.role_id WHERE r.name='DELIVERY' AND u.status='ACTIVE'")->fetchAll();$this->view('orders/show',['order'=>$o,'deliveryUsers'=>$deliveryUsers,'title'=>'Commande '.$o['order_no']]);}
 public function status(string $id):void{$status=$_POST['status']??'';$allowed=['NOUVELLE','CONFIRMEE','EN_PREPARATION','PRETE','EN_LIVRAISON','LIVREE','ANNULEE'];if(!in_array($status,$allowed,true)){flash('error','Statut invalide.');redirect('orders/'.$id);}try{(new Order())->updateStatus((int)$id,$status,(int)$_SESSION['user']['id']);AuditService::log('STATUS','ORDER',['id'=>$id],['status'=>$status]);flash('success','Statut mis à jour.');}catch(\Throwable $e){flash('error',$e->getMessage());}redirect('orders/'.$id);}
 public function assign(string $id):void{$uid=(int)$_POST['delivery_user_id'];(new Order())->assignDelivery((int)$id,$uid);flash('success','Livreur affecté.');redirect('orders/'.$id);}
}
