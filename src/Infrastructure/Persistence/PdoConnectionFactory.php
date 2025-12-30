<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Persistence;

use PDO;

class PdoConnectionFactory
{
    public static function create(): PDO
    {
        $host = getenv('DB_HOST') ?: 'mysql-local';
        $port = getenv('DB_PORT') ?: '3306';
        $database = getenv('DB_DATABASE') ?: 'ceer';
        $username = getenv('DB_USERNAME') ?: 'ceer';
        $password = getenv('DB_PASSWORD') ?: 'ceer';

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

        return new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
}
