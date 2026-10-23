<?php
namespace App\Models;
use App\Core\Model;
final class Customer extends Model {
 public function allCustomers(string $q=''):array{$p=[];$w='1';if($q!==''){$w='(c.first_name LIKE ? OR c.last_name LIKE ? OR c.phone LIKE ? OR c.code LIKE ?)';$like="%$q%";$p=[$like,$like,$like,$like];}return $this->all("SELECT c.*,COALESCE(l.points_balance,0) points,COALESCE(l.tier,'STANDARD') tier FROM customers c LEFT JOIN loyalty_accounts l ON l.customer_id=c.id WHERE $w ORDER BY c.created_at DESC",$p);}
 public function find(int $id):?array{return $this->one("SELECT c.*,COALESCE(l.points_balance,0) points,COALESCE(l.tier,'STANDARD') tier FROM customers c LEFT JOIN loyalty_accounts l ON l.customer_id=c.id WHERE c.id=?",[$id]);}
 public function create(array $d):int{$code='CLI-'.date('ymd').'-'.random_int(100,999);$st=$this->db->prepare("INSERT INTO customers(code,first_name,last_name,gender,phone,email,address) VALUES(?,?,?,?,?,?,?)");$st->execute([$code,$d['first_name'],$d['last_name'],$d['gender']?:null,$d['phone']?:null,$d['email']?:null,$d['address']?:null]);$id=(int)$this->db->lastInsertId();$this->exec("INSERT INTO loyalty_accounts(customer_id) VALUES(?)",[$id]);return$id;}
 public function update(int $id,array $d):void{$this->exec("UPDATE customers SET first_name=?,last_name=?,gender=?,phone=?,email=?,address=?,status=? WHERE id=?",[$d['first_name'],$d['last_name'],$d['gender']?:null,$d['phone']?:null,$d['email']?:null,$d['address']?:null,$d['status'],$id]);}
 public function loyaltyHistory(int $id):array{return $this->all("SELECT lt.* FROM loyalty_transactions lt JOIN loyalty_accounts la ON la.id=lt.loyalty_account_id WHERE la.customer_id=? ORDER BY lt.created_at DESC LIMIT 100",[$id]);}
}
