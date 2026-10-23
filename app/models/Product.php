<?php
namespace App\Models;

use App\Core\Model;

final class Product extends Model {
    public function paginate(string $q='', ?int $category=null, int $page=1, int $perPage=20): array {
        $where="p.status<>'ARCHIVED'"; $params=[];
        if($q!==''){ $where.=" AND (p.name LIKE ? OR p.internal_code LIKE ? OR p.barcode LIKE ?)"; $like="%$q%"; $params=[$like,$like,$like]; }
        if($category){$where.=" AND p.category_id=?";$params[]=$category;}
        $c=$this->db->prepare("SELECT COUNT(*) c FROM products p WHERE $where");$c->execute($params);$total=(int)$c->fetch()['c'];
        $offset=max(0,($page-1)*$perPage);
        $sql="SELECT p.*,c.name category,b.name brand,u.symbol unit,s.quantity,s.reserved_quantity,(s.quantity-s.reserved_quantity) available_qty,sp.company_name supplier FROM products p JOIN categories c ON c.id=p.category_id JOIN units u ON u.id=p.unit_id LEFT JOIN brands b ON b.id=p.brand_id LEFT JOIN suppliers sp ON sp.id=p.supplier_id LEFT JOIN stocks s ON s.product_id=p.id WHERE $where ORDER BY p.created_at DESC LIMIT $perPage OFFSET $offset";
        $st=$this->db->prepare($sql);$st->execute($params);
        return ['items'=>$st->fetchAll(),'total'=>$total,'page'=>$page,'pages'=>max(1,(int)ceil($total/$perPage))];
    }
    public function find(int $id): ?array { return $this->one("SELECT p.*,c.name category,b.name brand,u.symbol unit,COALESCE(s.quantity,0) quantity,COALESCE(s.reserved_quantity,0) reserved_quantity,sp.company_name supplier,COALESCE((SELECT CASE WHEN pr.type='PERCENT' THEN GREATEST(0,p.sale_price-(p.sale_price*pr.value/100)) WHEN pr.type='FIXED' THEN GREATEST(0,p.sale_price-pr.value) ELSE p.sale_price END FROM promotions pr LEFT JOIN promotion_products pp ON pp.promotion_id=pr.id WHERE pr.status='ACTIVE' AND NOW() BETWEEN pr.start_at AND pr.end_at AND (pp.product_id=p.id OR pr.category_id=p.category_id) ORDER BY pr.value DESC LIMIT 1),p.sale_price) effective_price FROM products p JOIN categories c ON c.id=p.category_id JOIN units u ON u.id=p.unit_id LEFT JOIN brands b ON b.id=p.brand_id LEFT JOIN suppliers sp ON sp.id=p.supplier_id LEFT JOIN stocks s ON s.product_id=p.id WHERE p.id=?",[$id]); }
    public function findByBarcodeOrCode(string $code): ?array { return $this->one("SELECT p.*,COALESCE(s.quantity,0) quantity,COALESCE(s.reserved_quantity,0) reserved_quantity,(COALESCE(s.quantity,0)-COALESCE(s.reserved_quantity,0)) available_qty,COALESCE((SELECT CASE WHEN pr.type='PERCENT' THEN GREATEST(0,p.sale_price-(p.sale_price*pr.value/100)) WHEN pr.type='FIXED' THEN GREATEST(0,p.sale_price-pr.value) ELSE p.sale_price END FROM promotions pr LEFT JOIN promotion_products pp ON pp.promotion_id=pr.id WHERE pr.status='ACTIVE' AND NOW() BETWEEN pr.start_at AND pr.end_at AND (pp.product_id=p.id OR pr.category_id=p.category_id) ORDER BY pr.value DESC LIMIT 1),p.sale_price) effective_price FROM products p LEFT JOIN stocks s ON s.product_id=p.id WHERE p.status='ACTIVE' AND (p.barcode=? OR p.internal_code=?)",[$code,$code]); }
    public function categories(): array { return $this->all("SELECT * FROM categories WHERE status='ACTIVE' ORDER BY name"); }
    public function brands(): array { return $this->all("SELECT * FROM brands WHERE status='ACTIVE' ORDER BY name"); }
    public function units(): array { return $this->all("SELECT * FROM units ORDER BY name"); }
    public function suppliers(): array { return $this->all("SELECT * FROM suppliers WHERE status='ACTIVE' ORDER BY company_name"); }
    public function create(array $d): int {
        $st=$this->db->prepare("INSERT INTO products(internal_code,barcode,name,description,category_id,subcategory_id,brand_id,supplier_id,unit_id,purchase_price,sale_price,tax_rate,min_stock,max_stock,location,manufacturing_date,expiration_date,image,status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $st->execute([$d['internal_code'],$d['barcode']?:null,$d['name'],$d['description']?:null,$d['category_id'],$d['subcategory_id']?:null,$d['brand_id']?:null,$d['supplier_id']?:null,$d['unit_id'],$d['purchase_price'],$d['sale_price'],$d['tax_rate'],$d['min_stock'],$d['max_stock'],$d['location']?:null,$d['manufacturing_date']?:null,$d['expiration_date']?:null,$d['image']?:null,$d['status']]);
        $id=(int)$this->db->lastInsertId();
        $this->exec("INSERT INTO stocks(product_id,quantity,reserved_quantity) VALUES(?,0,0)",[$id]);
        return $id;
    }
    public function update(int $id,array $d): void {
        $sql="UPDATE products SET internal_code=?,barcode=?,name=?,description=?,category_id=?,brand_id=?,supplier_id=?,unit_id=?,purchase_price=?,sale_price=?,tax_rate=?,min_stock=?,max_stock=?,location=?,manufacturing_date=?,expiration_date=?,image=COALESCE(?,image),status=? WHERE id=?";
        $this->exec($sql,[$d['internal_code'],$d['barcode']?:null,$d['name'],$d['description']?:null,$d['category_id'],$d['brand_id']?:null,$d['supplier_id']?:null,$d['unit_id'],$d['purchase_price'],$d['sale_price'],$d['tax_rate'],$d['min_stock'],$d['max_stock'],$d['location']?:null,$d['manufacturing_date']?:null,$d['expiration_date']?:null,$d['image']?:null,$d['status'],$id]);
    }
    public function archive(int $id): void { $this->exec("UPDATE products SET status='ARCHIVED' WHERE id=?",[$id]); }
    public function lowStock(int $limit=50): array { return $this->all("SELECT p.id,p.internal_code,p.name,p.min_stock,p.max_stock,s.quantity,s.reserved_quantity,(s.quantity-s.reserved_quantity) available_qty FROM products p JOIN stocks s ON s.product_id=p.id WHERE p.status='ACTIVE' AND (s.quantity-s.reserved_quantity)<=p.min_stock ORDER BY available_qty ASC LIMIT $limit"); }
    public function expiring(int $days=30): array { return $this->all("SELECT p.*,s.quantity FROM products p LEFT JOIN stocks s ON s.product_id=p.id WHERE p.expiration_date IS NOT NULL AND p.expiration_date<=DATE_ADD(CURDATE(),INTERVAL ? DAY) AND p.status='ACTIVE' ORDER BY p.expiration_date",[$days]); }
    public function publicList(string $q='', ?int $category=null, int $limit=100): array {
        $where="p.status='ACTIVE' AND (s.quantity-s.reserved_quantity)>0";$params=[];
        if($q!==''){$where.=" AND p.name LIKE ?";$params[]="%$q%";}
        if($category){$where.=" AND p.category_id=?";$params[]=$category;}
        $sql="SELECT p.*,c.name category,(s.quantity-s.reserved_quantity) available_qty,COALESCE((SELECT CASE WHEN pr.type='PERCENT' THEN GREATEST(0,p.sale_price-(p.sale_price*pr.value/100)) WHEN pr.type='FIXED' THEN GREATEST(0,p.sale_price-pr.value) ELSE p.sale_price END FROM promotions pr LEFT JOIN promotion_products pp ON pp.promotion_id=pr.id WHERE pr.status='ACTIVE' AND NOW() BETWEEN pr.start_at AND pr.end_at AND (pp.product_id=p.id OR pr.category_id=p.category_id) ORDER BY pr.value DESC LIMIT 1),p.sale_price) effective_price FROM products p JOIN categories c ON c.id=p.category_id JOIN stocks s ON s.product_id=p.id WHERE $where ORDER BY p.created_at DESC LIMIT $limit";
        $st=$this->db->prepare($sql);$st->execute($params);return $st->fetchAll();
    }
}
