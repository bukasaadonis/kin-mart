<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Core\Database;
final class SearchController extends Controller {
 public function index():void{$q=trim($_GET['q']??'');$db=Database::connection();$products=$customers=$orders=$sales=[];if($q!==''){$like="%$q%";$st=$db->prepare("SELECT id,internal_code,barcode,name,sale_price FROM products WHERE status<>'ARCHIVED' AND (name LIKE ? OR internal_code LIKE ? OR barcode LIKE ?) LIMIT 20");$st->execute([$like,$like,$like]);$products=$st->fetchAll();$st=$db->prepare("SELECT id,code,first_name,last_name,phone FROM customers WHERE first_name LIKE ? OR last_name LIKE ? OR code LIKE ? OR phone LIKE ? LIMIT 20");$st->execute([$like,$like,$like,$like]);$customers=$st->fetchAll();$st=$db->prepare("SELECT id,order_no,status,total FROM orders WHERE order_no LIKE ? LIMIT 20");$st->execute([$like]);$orders=$st->fetchAll();$st=$db->prepare("SELECT id,sale_no,status,total FROM sales WHERE sale_no LIKE ? LIMIT 20");$st->execute([$like]);$sales=$st->fetchAll();}$this->view('search/index',compact('q','products','customers','orders','sales')+['title'=>'Recherche globale']);}
}
