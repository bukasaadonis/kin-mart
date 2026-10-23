<?php
declare(strict_types=1);

use App\Core\Router;
use App\Core\Session;

require __DIR__ . '/app/helpers/functions.php';

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) return;
    $relative = substr($class, strlen($prefix));
    $parts = explode('\\', $relative);
    if ($parts) $parts[0] = strtolower($parts[0]);
    $file = __DIR__ . '/app/' . implode('/', $parts) . '.php';
    if (is_file($file)) require $file;
});

$GLOBALS['config'] = require __DIR__ . '/app/config/config.php';
Session::start();

try {
    $router = new Router();
    require __DIR__ . '/app/config/routes.php';
    $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_GET['url'] ?? '');
} catch (Throwable $e) {
    http_response_code(500);
    $debug = (bool) config('app.debug', false);
    $message = $debug ? $e->getMessage() : 'Une erreur interne est survenue.';
    $details = $debug ? $e->getTraceAsString() : '';
    require __DIR__ . '/app/views/errors/500.php';
}
