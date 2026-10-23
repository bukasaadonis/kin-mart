<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Models\Analytics;
use App\Models\Setting;
final class AnalyticsController extends Controller {
 public function index():void{$a=new Analytics();$d=(int)(new Setting())->get('dormant_days',30);$this->view('analytics/index',['coverage'=>$a->coverage(30),'stars'=>$a->stars(),'dormant'=>$a->dormant($d),'segments'=>$a->segments(),'title'=>'KIN-MART Analytics']);}
}
