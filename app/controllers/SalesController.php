<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Models\Sale;
use App\Services\AuditService;
final class SalesController extends Controller {
 public function index():void{$from=$_GET['from']??date('Y-m-01');$to=$_GET['to']??date('Y-m-d');$this->view('sales/index',['sales'=>(new Sale())->list($from,$to),'from'=>$from,'to'=>$to,'title'=>'Ventes']);}
 public function show(string $id):void{$m=new Sale();$sale=$m->find((int)$id);if(!$sale)redirect('sales');$this->view('sales/show',['sale'=>$sale,'returns'=>$m->returnsForSale((int)$id),'title'=>'Vente '.$sale['sale_no']]);}
 public function cancel(string $id):void{try{(new Sale())->cancel((int)$id,(int)$_SESSION['user']['id'],trim($_POST['reason']??'Annulation autorisée'));AuditService::log('CANCEL','SALE',['id'=>$id],['reason'=>$_POST['reason']??'']);flash('success','Vente annulée et stock restauré.');}catch(\Throwable $e){flash('error',$e->getMessage());}redirect('sales/'.$id);}
 public function returnItem(string $id):void{try{(new Sale())->returnItem((int)$id,(int)$_POST['sale_item_id'],(float)$_POST['quantity'],(int)$_SESSION['user']['id'],trim($_POST['reason']??'Retour client'));AuditService::log('RETURN','SALE',['id'=>$id],$_POST);flash('success','Retour enregistré et stock réintégré.');}catch(\Throwable $e){flash('error',$e->getMessage());}redirect('sales/'.$id);}
}
