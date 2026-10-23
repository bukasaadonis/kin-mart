<?php
namespace App\Models;

use App\Core\Model;

final class User extends Model {
    public function findByLogin(string $login): ?array {
        return $this->one("SELECT u.*, r.name role, r.label role_label FROM users u JOIN roles r ON r.id=u.role_id WHERE (u.username=? OR u.email=?) LIMIT 1", [$login,$login]);
    }
    public function allUsers(): array { return $this->all("SELECT u.*,r.name role,r.label role_label FROM users u JOIN roles r ON r.id=u.role_id ORDER BY u.created_at DESC"); }
    public function roles(): array { return $this->all("SELECT * FROM roles ORDER BY id"); }
    public function create(array $d): int {
        $st=$this->db->prepare("INSERT INTO users(role_id,customer_id,username,email,password_hash,full_name,phone,status) VALUES(?,?,?,?,?,?,?,'ACTIVE')");
        $st->execute([$d['role_id'],$d['customer_id']?:null,$d['username'],$d['email']?:null,password_hash($d['password'],PASSWORD_DEFAULT),$d['full_name'],$d['phone']?:null]);
        return (int)$this->db->lastInsertId();
    }
    public function updatePassword(int $id,string $hash): void { $this->exec("UPDATE users SET password_hash=?,force_password_change=0 WHERE id=?",[$hash,$id]); }
    public function setStatus(int $id,string $status): void { $this->exec("UPDATE users SET status=? WHERE id=?",[$status,$id]); }
}
