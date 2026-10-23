<?php
namespace App\Models;
use App\Core\Model;
final class Promotion extends Model {
 public function list():array{return $this->all("SELECT p.*,c.name category FROM promotions p LEFT JOIN categories c ON c.id=p.category_id ORDER BY p.created_at DESC");}
 public function create(array $d):int{$st=$this->db->prepare("INSERT INTO promotions(name,type,value,category_id,start_at,end_at,min_cart,status) VALUES(?,?,?,?,?,?,?,'ACTIVE')");$st->execute([$d['name'],$d['type'],$d['value'],$d['category_id']?:null,$d['start_at'],$d['end_at'],$d['min_cart']]);return(int)$this->db->lastInsertId();}
}
