<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\User;
use App\Services\AuditService;

final class AuthController extends Controller {
    public function loginForm(): void {
        if (auth_user()) redirect(($_SESSION['user']['role'] ?? '')==='CLIENT' ? 'account' : 'admin');
        $this->view('auth/login',['title'=>'Connexion'], 'auth');
    }
    public function login(): void {
        $login=trim($_POST['login']??''); $password=(string)($_POST['password']??'');
        $_SESSION['_old']=['login'=>$login];
        if($login===''||$password===''){flash('error','Identifiant et mot de passe obligatoires.');redirect('login');}
        $db=Database::connection();$max=(int)config('security.max_login_attempts',5);$mins=(int)config('security.lock_minutes',15);
        $st=$db->prepare("SELECT COUNT(*) c FROM login_attempts WHERE login=? AND success=0 AND attempted_at>=DATE_SUB(NOW(),INTERVAL $mins MINUTE)");$st->execute([$login]);
        if((int)$st->fetch()['c'] >= $max){flash('error',"Trop de tentatives. Réessayez dans $mins minutes.");redirect('login');}
        $user=(new User())->findByLogin($login);$ok=$user&&$user['status']==='ACTIVE'&&password_verify($password,$user['password_hash']);
        $db->prepare("INSERT INTO login_attempts(login,ip_address,success) VALUES(?,?,?)")->execute([$login,$_SERVER['REMOTE_ADDR']??'local',$ok?1:0]);
        if(!$ok){flash('error','Identifiants invalides ou compte désactivé.');redirect('login');}
        session_regenerate_id(true);
        $_SESSION['user']=['id'=>(int)$user['id'],'username'=>$user['username'],'full_name'=>$user['full_name'],'role'=>$user['role'],'role_label'=>$user['role_label'],'customer_id'=>$user['customer_id']?(int)$user['customer_id']:null];
        $db->prepare("UPDATE users SET last_login_at=NOW() WHERE id=?")->execute([$user['id']]);
        AuditService::log('LOGIN','AUTH',null,['username'=>$user['username']]); unset($_SESSION['_old']);
        if((int)$user['force_password_change']===1) redirect('change-password');
        redirect($user['role']==='CLIENT'?'account':($user['role']==='DELIVERY'?'deliveries':'admin'));
    }
    public function logout(): void { AuditService::log('LOGOUT','AUTH'); $_SESSION=[]; session_regenerate_id(true); flash('success','Déconnexion réussie.'); redirect('login'); }
    public function registerForm(): void { $this->view('auth/register',['title'=>'Créer un compte client'],'auth'); }
    public function register(): void {
        $d=['first_name'=>trim($_POST['first_name']??''),'last_name'=>trim($_POST['last_name']??''),'phone'=>trim($_POST['phone']??''),'email'=>trim($_POST['email']??''),'password'=>(string)($_POST['password']??'')];
        if($d['first_name']===''||$d['last_name']===''||$d['email']===''||strlen($d['password'])<8){flash('error','Complétez les champs. Mot de passe: au moins 8 caractères.');redirect('register');}
        try{Database::transaction(function($db)use($d){$code='CLI-'.date('ymd').'-'.random_int(100,999);$db->prepare("INSERT INTO customers(code,first_name,last_name,phone,email) VALUES(?,?,?,?,?)")->execute([$code,$d['first_name'],$d['last_name'],$d['phone']?:null,$d['email']]);$cid=(int)$db->lastInsertId();$db->prepare("INSERT INTO loyalty_accounts(customer_id) VALUES(?)")->execute([$cid]);$rid=(int)$db->query("SELECT id FROM roles WHERE name='CLIENT'")->fetchColumn();$username=strtolower(preg_replace('/[^a-z0-9]+/i','',$d['first_name'].$d['last_name'])).random_int(10,99);$db->prepare("INSERT INTO users(role_id,customer_id,username,email,password_hash,full_name,status) VALUES(?,?,?,?,?,?,'ACTIVE')")->execute([$rid,$cid,$username,$d['email'],password_hash($d['password'],PASSWORD_DEFAULT),$d['first_name'].' '.$d['last_name']]);});flash('success','Compte créé. Vous pouvez vous connecter.');redirect('login');}catch(\Throwable $e){flash('error','Impossible de créer le compte: '.$e->getMessage());redirect('register');}
    }
    public function changePasswordForm(): void { $this->view('auth/change_password',['title'=>'Changer le mot de passe'],'admin'); }
    public function changePassword(): void { $p=(string)($_POST['password']??'');$c=(string)($_POST['confirm']??'');if(strlen($p)<8||$p!==$c){flash('error','Les mots de passe doivent correspondre et contenir au moins 8 caractères.');redirect('change-password');}(new User())->updatePassword((int)$_SESSION['user']['id'],password_hash($p,PASSWORD_DEFAULT));flash('success','Mot de passe modifié.');redirect('admin'); }
}
