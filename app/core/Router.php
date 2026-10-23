<?php
namespace App\Core;

use App\Middlewares\AuthMiddleware;
use App\Middlewares\RoleMiddleware;

final class Router {
    private array $routes = [];

    public function get(string $path, array $handler, array $middleware = []): void { $this->add('GET', $path, $handler, $middleware); }
    public function post(string $path, array $handler, array $middleware = []): void { $this->add('POST', $path, $handler, $middleware); }
    private function add(string $method, string $path, array $handler, array $middleware): void {
        $path = trim($path, '/');
        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $path);
        $this->routes[] = compact('method','path','regex','handler','middleware');
    }

    public function dispatch(string $method, string $url): void {
        $path = trim((string)parse_url($url, PHP_URL_PATH), '/');
        foreach ($this->routes as $route) {
            if ($route['method'] !== strtoupper($method)) continue;
            if (!preg_match('#^' . $route['regex'] . '$#', $path, $m)) continue;
            foreach ($route['middleware'] as $mw) {
                if ($mw === 'auth') AuthMiddleware::handle();
                elseif (str_starts_with($mw, 'role:')) RoleMiddleware::handle(explode(',', substr($mw, 5)));
            }
            if ($method === 'POST') Csrf::enforce();
            [$class, $action] = $route['handler'];
            $controller = new $class();
            $args=[]; foreach ($m as $k=>$v) if (!is_int($k)) $args[]=$v;
            $controller->$action(...$args);
            return;
        }
        http_response_code(404);
        require dirname(__DIR__) . '/views/errors/404.php';
    }
}
