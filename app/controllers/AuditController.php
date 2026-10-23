<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Core\Database;
final class AuditController extends Controller {
 public function index():void{$logs=Database::connection()->query("SELECT a.*,u.full_name FROM audit_logs a LEFT JOIN users u ON u.id=a.user_id ORDER BY a.created_at DESC LIMIT 500")->fetchAll();$this->view('audit/index',['logs'=>$logs,'title'=>'Journal d’audit']);}
}
