<?php
namespace App\Services;

use App\Core\Database;

final class AuditService {
    public static function log(string $action, string $module, mixed $old = null, mixed $new = null): void {
        try {
            $db = Database::connection();
            $st = $db->prepare("INSERT INTO audit_logs(user_id,action,module,old_data,new_data,ip_address,created_at) VALUES(?,?,?,?,?,?,NOW())");
            $st->execute([
                $_SESSION['user']['id'] ?? null,
                $action, $module,
                $old === null ? null : json_encode($old, JSON_UNESCAPED_UNICODE),
                $new === null ? null : json_encode($new, JSON_UNESCAPED_UNICODE),
                $_SERVER['REMOTE_ADDR'] ?? 'local'
            ]);
        } catch (\Throwable $e) { /* l'audit ne doit pas bloquer l'opération métier */ }
    }
}
