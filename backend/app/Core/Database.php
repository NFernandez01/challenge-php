<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $_ENV['DB_HOST'],
                $_ENV['DB_PORT'] ?? '3306',
                $_ENV['DB_NAME']
            );

            self::$instance = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], [
                // Los errores de SQL lanzan PDOException en vez de fallar en silencio
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                // fetch()/fetchAll() devuelven arrays asociativos por defecto
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // Prepared statements reales de MySQL (no emulados por PDO)
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }

        return self::$instance;
    }
}
