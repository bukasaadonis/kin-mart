<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Models\Customer;
use App\Services\AuditService;
final class CustomerController extends Controller {
 private Customer $m;public function __construct(){$this->m=new Customer();}
 public function index():void{$q=trim($_GET['q']??'');$this->view('customers/index',['customers'=>$this->m->allCustomers($q),'q'=>$q,'title'=>'Clients & fidélité']);}
 public function store():void{$d=$this->data();if($d['first_name']===''||$d['last_name']===''){flash('error','Nom et prénom obligatoires.');redirect('customers');}$id=$this->m->create($d);AuditService::log('CREATE','CUSTOMER',null,$d);flash('success','Client créé.');redirect('customers/'.$id);}
 public function show(string $id):void{$c=$this->m->find((int)$id);if(!$c){redirect('customers');}$this->view('customers/show',['customer'=>$c,'history'=>$this->m->loyaltyHistory((int)$id),'title'=>'Fiche client']);}
 public function update(string $id):void{$d=$this->data()+['status'=>$_POST['status']??'ACTIVE'];$this->m->update((int)$id,$d);AuditService::log('UPDATE','CUSTOMER',['id'=>$id],$d);flash('success','Client mis à jour.');redirect('customers/'.$id);}
 private function data():array{return['first_name'=>trim($_POST['first_name']??''),'last_name'=>trim($_POST['last_name']??''),'gender'=>trim($_POST['gender']??''),'phone'=>trim($_POST['phone']??''),'email'=>trim($_POST['email']??''),'address'=>trim($_POST['address']??'')];}
}
