<?php
namespace App\Core;

final class Session {
    public static function start(): void {
        if (session_status() === PHP_SESSION_ACTIVE) return;
        date_default_timezone_set((string)config('app.timezone', 'Africa/Kinshasa'));
        session_name('KINMARTSESSID');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'httponly' => true,
            'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'samesite' => 'Lax',
        ]);
        session_start();
        if (empty($_SESSION['_initiated'])) {
            session_regenerate_id(true);
            $_SESSION['_initiated'] = time();
        }
    }
}
