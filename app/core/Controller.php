<?php
namespace App\Core;

abstract class Controller {
    protected function view(string $view, array $data = [], string|false $layout = 'admin'): void {
        extract($data, EXTR_SKIP);
        $viewFile = dirname(__DIR__) . '/views/' . $view . '.php';
        if (!is_file($viewFile)) throw new \RuntimeException("Vue introuvable: $view");
        if ($layout === false) { require $viewFile; return; }
        $contentView = $viewFile;
        require dirname(__DIR__) . '/views/layouts/' . $layout . '.php';
    }
    protected function json(array $data, int $status = 200): never {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
