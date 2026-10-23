<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Models\User;
use App\Services\AuditService;
final class UserController extends Controller {
 public function index():void{$m=new User();$this->view('users/index',['users'=>$m->allUsers(),'roles'=>$m->roles(),'title'=>'Utilisateurs & rôles']);}
 public function store():void{$d=['role_id'=>(int)$_POST['role_id'],'customer_id'=>null,'username'=>trim($_POST['username']??''),'email'=>trim($_POST['email']??''),'password'=>(string)($_POST['password']??''),'full_name'=>trim($_POST['full_name']??''),'phone'=>trim($_POST['phone']??'')];if($d['username']===''||$d['full_name']===''||strlen($d['password'])<8){flash('error','Données invalides. Mot de passe minimum 8 caractères.');redirect('users');}try{$id=(new User())->create($d);AuditService::log('CREATE','USER',null,['id'=>$id,'username'=>$d['username']]);flash('success','Utilisateur créé.');}catch(\Throwable $e){flash('error',$e->getMessage());}redirect('users');}
 public function status(string $id):void{$status=in_array($_POST['status']??'',['ACTIVE','INACTIVE','LOCKED'],true)?$_POST['status']:'INACTIVE';(new User())->setStatus((int)$id,$status);AuditService::log('STATUS','USER',['id'=>$id],['status'=>$status]);flash('success','Statut modifié.');redirect('users');}
}
