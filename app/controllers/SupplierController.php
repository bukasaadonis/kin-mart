<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Models\Supplier;
use App\Services\AuditService;
final class SupplierController extends Controller {
 private Supplier $m;public function __construct(){$this->m=new Supplier();}
 public function index():void{$this->view('suppliers/index',['suppliers'=>$this->m->allSuppliers(),'title'=>'Fournisseurs']);}
 public function store():void{$d=$this->data();if($d['company_name']===''){flash('error','Nom entreprise obligatoire.');redirect('suppliers');}$id=$this->m->create($d);AuditService::log('CREATE','SUPPLIER',null,$d);flash('success','Fournisseur ajouté.');redirect('suppliers');}
 public function update(string $id):void{$d=$this->data();$this->m->update((int)$id,$d);AuditService::log('UPDATE','SUPPLIER',['id'=>$id],$d);flash('success','Fournisseur modifié.');redirect('suppliers');}
 private function data():array{return['company_name'=>trim($_POST['company_name']??''),'contact_name'=>trim($_POST['contact_name']??''),'phone'=>trim($_POST['phone']??''),'email'=>trim($_POST['email']??''),'address'=>trim($_POST['address']??''),'lead_time_days'=>max(0,(int)($_POST['lead_time_days']??3)),'debt'=>(float)($_POST['debt']??0),'status'=>$_POST['status']??'ACTIVE'];}
}
