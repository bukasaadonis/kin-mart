<?php
namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

final class Database {
    private static ?PDO $pdo = null;

    public static function connection(): PDO {
        if (self::$pdo instanceof PDO) return self::$pdo;
        $db = config('db');
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $db['host'], $db['port'], $db['name'], $db['charset']);
        try {
            self::$pdo = new PDO($dsn, $db['user'], $db['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            self::$pdo->exec("SET time_zone = '+01:00'");
            return self::$pdo;
        } catch (PDOException $e) {
            throw new RuntimeException('Connexion à MySQL impossible. Vérifiez XAMPP, le port MySQL et app/config/config.php. Détail: ' . $e->getMessage());
        }
    }

    public static function transaction(callable $callback): mixed {
        $pdo = self::connection();
        try {
            if (!$pdo->inTransaction()) $pdo->beginTransaction();
            $result = $callback($pdo);
            if ($pdo->inTransaction()) $pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }
}
