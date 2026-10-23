<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\AuditService;
final class PurchaseController extends Controller {
 public function index():void{$this->view('purchases/index',['orders'=>(new Purchase())->list(),'title'=>'Approvisionnements']);}
 public function createForm():void{$this->view('purchases/form',['suppliers'=>(new Supplier())->allSuppliers(),'products'=>(new Product())->paginate('',null,1,500)['items'],'title'=>'Nouveau bon de commande']);}
 public function store():void{$pids=$_POST['product_id']??[];$qtys=$_POST['qty']??[];$prices=$_POST['price']??[];$items=[];foreach($pids as $i=>$pid){$q=(float)($qtys[$i]??0);$pr=(float)($prices[$i]??0);if((int)$pid>0&&$q>0&&$pr>=0)$items[]=['product_id'=>(int)$pid,'qty'=>$q,'price'=>$pr];}if(!$items){flash('error','Ajoutez au moins une ligne.');redirect('purchases/create');}try{$id=(new Purchase())->create((int)$_POST['supplier_id'],$items,(int)$_SESSION['user']['id'],trim($_POST['notes']??''));AuditService::log('CREATE','PURCHASE',null,['id'=>$id]);flash('success','Bon de commande créé.');redirect('purchases');}catch(\Throwable $e){flash('error',$e->getMessage());redirect('purchases/create');}}
 public function receive(string $id):void{try{(new Purchase())->receive((int)$id,(int)$_SESSION['user']['id']);AuditService::log('RECEIVE','PURCHASE',['id'=>$id]);flash('success','Réception enregistrée, stock augmenté.');}catch(\Throwable $e){flash('error',$e->getMessage());}redirect('purchases');}
}
