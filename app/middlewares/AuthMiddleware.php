<?php
namespace App\Middlewares;
final class AuthMiddleware {
    public static function handle(): void {
        if (empty($_SESSION['user'])) { flash('error','Veuillez vous connecter.'); redirect('login'); }
    }
}
