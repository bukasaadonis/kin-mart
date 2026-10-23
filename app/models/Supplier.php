<?php
namespace App\Models;
use App\Core\Model;
final class Supplier extends Model {
 public function allSuppliers():array{return $this->all("SELECT s.*,COALESCE((SELECT SUM(po.total) FROM purchase_orders po WHERE po.supplier_id=s.id AND po.status<>'ANNULE'),0) total_purchases FROM suppliers s ORDER BY s.company_name");}
 public function find(int $id):?array{return $this->one("SELECT * FROM suppliers WHERE id=?",[$id]);}
 public function create(array $d):int{$st=$this->db->prepare("INSERT INTO suppliers(company_name,contact_name,phone,email,address,lead_time_days,debt,status) VALUES(?,?,?,?,?,?,?,'ACTIVE')");$st->execute([$d['company_name'],$d['contact_name']?:null,$d['phone']?:null,$d['email']?:null,$d['address']?:null,$d['lead_time_days'],$d['debt']]);return(int)$this->db->lastInsertId();}
 public function update(int $id,array $d):void{$this->exec("UPDATE suppliers SET company_name=?,contact_name=?,phone=?,email=?,address=?,lead_time_days=?,debt=?,status=? WHERE id=?",[$d['company_name'],$d['contact_name']?:null,$d['phone']?:null,$d['email']?:null,$d['address']?:null,$d['lead_time_days'],$d['debt'],$d['status'],$id]);}
}
