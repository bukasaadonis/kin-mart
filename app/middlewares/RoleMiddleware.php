<?php
namespace App\Middlewares;
final class RoleMiddleware {
    public static function handle(array $roles): void {
        if (empty($_SESSION['user'])) redirect('login');
        if (!in_array($_SESSION['user']['role'] ?? '', $roles, true)) {
            http_response_code(403);
            require dirname(__DIR__) . '/views/errors/403.php';
            exit;
        }
    }
}
