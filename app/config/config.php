<?php
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/KinMart/index.php'));
$baseUrl = $scriptDir === '/' ? '' : rtrim($scriptDir, '/');

return [
    'app' => [
        'name' => 'KIN-MART',
        'subtitle' => 'Super Marché',
        'base_url' => $baseUrl,
        'timezone' => 'Africa/Kinshasa',
        'debug' => true,
        'currency' => 'USD',
    ],
    'db' => [
        'host' => getenv('KINMART_DB_HOST') ?: '127.0.0.1',
        'port' => getenv('KINMART_DB_PORT') ?: '3306',
        'name' => getenv('KINMART_DB_NAME') ?: 'kinmart',
        'user' => getenv('KINMART_DB_USER') ?: 'root',
        'pass' => getenv('KINMART_DB_PASS') !== false ? getenv('KINMART_DB_PASS') : '',
        'charset' => 'utf8mb4',
    ],
    'security' => [
        'max_login_attempts' => 5,
        'lock_minutes' => 15,
    ],
];
