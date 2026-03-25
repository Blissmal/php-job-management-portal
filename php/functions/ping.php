<?php

/**
 * php/functions/ping.php
 * Health check endpoint that keeps the server and database awake
 * Returns JSON response with server status
 */

header('Content-Type: application/json');

try {
    // Start performance timer
    $startTime = microtime(true);

    // Check database connection
    require_once __DIR__ . '/../config/connection.php';
    $db = getDB();

    // Execute a simple query to keep database alive
    $stmt = $db->prepare("SELECT COUNT(*) as total_jobs FROM jobs");
    $stmt->execute();
    $jobsCount = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get application count
    $appStmt = $db->prepare("SELECT COUNT(*) as total_applications FROM applications");
    $appStmt->execute();
    $applicationsCount = $appStmt->fetch(PDO::FETCH_ASSOC);

    // Get user count
    $userStmt = $db->prepare("SELECT COUNT(*) as total_users FROM users");
    $userStmt->execute();
    $usersCount = $userStmt->fetch(PDO::FETCH_ASSOC);

    // Calculate response time
    $endTime = microtime(true);
    $responseTime = round(($endTime - $startTime) * 1000, 2); // milliseconds

    http_response_code(200);
    echo json_encode([
        'status' => 'ok',
        'message' => 'Server and database are healthy',
        'timestamp' => date('Y-m-d H:i:s'),
        'database' => [
            'connection' => 'healthy',
            'jobs_count' => (int)$jobsCount['total_jobs'],
            'applications_count' => (int)$applicationsCount['total_applications'],
            'users_count' => (int)$usersCount['total_users']
        ],
        'performance' => [
            'response_time_ms' => $responseTime,
            'php_version' => phpversion()
        ],
        'uptime' => [
            'pinged_at' => time(),
            'server_timezone' => date_default_timezone_get()
        ]
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(503);
    echo json_encode([
        'status' => 'error',
        'message' => 'Service unavailable',
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
    exit;
}
