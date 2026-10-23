<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Models\Promotion;
use App\Models\Product;
use App\Services\AuditService;
final class PromotionController extends Controller {
 public function index():void{$db=\App\Core\Database::connection();$coupons=$db->query("SELECT * FROM coupons ORDER BY id DESC")->fetchAll();$this->view('promotions/index',['promotions'=>(new Promotion())->list(),'coupons'=>$coupons,'categories'=>(new Product())->categories(),'title'=>'Promotions & coupons']);}
 public function store():void{$d=['name'=>trim($_POST['name']??''),'type'=>$_POST['type']??'PERCENT','value'=>(float)($_POST['value']??0),'category_id'=>(int)($_POST['category_id']??0),'start_at'=>str_replace('T',' ',$_POST['start_at']??date('Y-m-d H:i:s')),'end_at'=>str_replace('T',' ',$_POST['end_at']??date('Y-m-d H:i:s')),'min_cart'=>(float)($_POST['min_cart']??0)];$id=(new Promotion())->create($d);AuditService::log('CREATE','PROMOTION',null,$d);flash('success','Promotion créée.');redirect('promotions');}

 public function storeCoupon():void{$db=\App\Core\Database::connection();$code=strtoupper(trim($_POST['code']??''));$type=$_POST['type']??'PERCENT';$value=(float)($_POST['value']??0);$min=(float)($_POST['min_cart']??0);$limit=($_POST['usage_limit']??'')!==''?(int)$_POST['usage_limit']:null;$start=str_replace('T',' ',$_POST['start_at']??date('Y-m-d H:i:s'));$end=str_replace('T',' ',$_POST['end_at']??date('Y-m-d H:i:s'));if($code===''||$value<=0){flash('error','Coupon invalide.');redirect('promotions');}try{$st=$db->prepare("INSERT INTO coupons(code,type,value,min_cart,usage_limit,start_at,end_at,status) VALUES(?,?,?,?,?,?,?,'ACTIVE')");$st->execute([$code,$type,$value,$min,$limit,$start,$end]);AuditService::log('CREATE','COUPON',null,['code'=>$code]);flash('success','Coupon créé.');}catch(\Throwable $e){flash('error',$e->getMessage());}redirect('promotions');}
}
