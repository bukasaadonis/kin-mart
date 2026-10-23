<?php
namespace App\Core;

use PDO;

abstract class Model {
    protected PDO $db;
    public function __construct() { $this->db = Database::connection(); }
    protected function all(string $sql, array $params = []): array {
        $st = $this->db->prepare($sql); $st->execute($params); return $st->fetchAll();
    }
    protected function one(string $sql, array $params = []): ?array {
        $st = $this->db->prepare($sql); $st->execute($params); $r=$st->fetch(); return $r ?: null;
    }
    protected function exec(string $sql, array $params = []): bool {
        $st = $this->db->prepare($sql); return $st->execute($params);
    }
}
