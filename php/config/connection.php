<?php

/**
 * Provides a singleton PDO connection using environment variables.
 */
function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $host     = getenv('DB_HOST') ?: 'localhost';
        $dbname   = getenv('DB_NAME') ?: 'authentication_system';
        $username = getenv('DB_USER') ?: 'root';
        $password = getenv('DB_PASS') ?: 'root';

        $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";

        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }

    return $pdo;
}
