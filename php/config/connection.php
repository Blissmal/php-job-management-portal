<?php
// Define base URL
if (!defined('BASE_URL')) {
    define('BASE_URL', rtrim((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'], '/'));
}

/**
 * Minimal .env loader
 */
function loadEnv(string $path)
{
    if (!file_exists($path)) {
        error_log("ENV file not found: $path");
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue; // skip empty lines and comments
        }

        if (strpos($line, '=') === false) {
            continue; // skip malformed lines
        }

        list($key, $value) = explode('=', $line, 2);

        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'"); // remove whitespace and quotes

        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;

        // DEBUG: log each loaded variable
        error_log("LOADED ENV: $key=$value");
    }
}

// Load .env from current directory
loadEnv(__DIR__ . '/../../.env');

/**
 * Returns a singleton PDO connection using environment variables.
 */
function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $host     = getenv('DB_HOST') ?: 'localhost';
        $port     = getenv('DB_PORT') ?: '3306';
        $dbname   = getenv('DB_NAME') ?: 'authentication_system';
        $username = getenv('DB_USER') ?: 'root';
        $password = getenv('DB_PASS') ?: 'root';

        // DEBUG LOG (remove in production)
        error_log("DB_HOST=" . var_export($host, true));
        error_log("DB_PORT=" . var_export($port, true));
        error_log("DB_NAME=" . var_export($dbname, true));
        error_log("DB_USER=" . var_export($username, true));
        error_log("DB_PASS=" . var_export($password, true));

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

        try {
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            error_log("PDO connection failed: " . $e->getMessage());
            throw $e;
        }
    }

    return $pdo;
}

// Example usage (for development, remove in production)
try {
    $db = getDB();
    error_log("Database connection successful");
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
}

