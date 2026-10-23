<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Core\Database;
final class NotificationController extends Controller {
 public function index():void{$db=Database::connection();$uid=(int)$_SESSION['user']['id'];$role=(string)$_SESSION['user']['role'];$st=$db->prepare("SELECT * FROM notifications WHERE user_id=? OR role_name=? OR role_name='ADMIN' AND ? IN ('SUPER_ADMIN','ADMIN') ORDER BY created_at DESC LIMIT 200");$st->execute([$uid,$role,$role]);$this->view('notifications/index',['notifications'=>$st->fetchAll(),'title'=>'Notifications']);}
 public function read(string $id):void{$db=Database::connection();$db->prepare("UPDATE notifications SET is_read=1 WHERE id=?")->execute([(int)$id]);redirect('notifications');}
 public function readAll():void{$db=Database::connection();$uid=(int)$_SESSION['user']['id'];$role=(string)$_SESSION['user']['role'];$st=$db->prepare("UPDATE notifications SET is_read=1 WHERE user_id=? OR role_name=? OR (role_name='ADMIN' AND ? IN ('SUPER_ADMIN','ADMIN'))");$st->execute([$uid,$role,$role]);flash('success','Notifications marquées comme lues.');redirect('notifications');}
}
