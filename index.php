<?php

/**
 * URL Router
 * Entry point for views and action handlers (form submissions).
 * Maps request URLs to handler files and allowed HTTP methods.
 */

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';


$script = dirname($_SERVER['SCRIPT_NAME']);
$script = ($script === '/' || $script === '\\') ? '' : rtrim($script, '/\\');

define('BASE_URL', $scheme . '://' . $host . $script);

require_once __DIR__ . '/php/config/connection.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$request_uri  = $_SERVER['REQUEST_URI'] ?? '/';
$script_dir   = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$request_url  = substr($request_uri, strlen($script_dir));
$request_url  = strtok($request_url, '?');
$request_url  = '/' . ltrim($request_url, '/');


$routes = [
    '/login-post'             => ['php/function/login.php',                ['POST']],
    '/logout'                 => ['php/function/logout.php',                 ['GET', 'POST']],
    '/register-post'          => ['php/function/register_seeker.php',      ['POST']],
    '/register-employer-post' => ['php/function/register_employer.php',    ['POST']],
    '/profile-update'         => ['php/function/profile.php',              ['POST']],
    '/jobs-action'            => ['php/function/jobs.php',                 ['POST']],
    '/apply-post'             => ['php/function/apply.php',                ['POST']],
    '/update-status'          => ['php/function/update_status.php',        ['POST']],
    '/save-job-toggle'        => ['php/function/save_job.php',             ['POST']],
    '/withdraw'               => ['php/function/withdraw.php',             ['POST']],
    '/download-resume'        => ['php/function/download_resume.php',      ['GET']],
    '/admin-action'           => ['php/function/admin.php',                ['POST']],
    // Add view routes here
];

if (array_key_exists($request_url, $routes)) {
    [$file, $methods] = $routes[$request_url];

    if (!in_array($_SERVER['REQUEST_METHOD'], $methods, true)) {
        http_response_code(405);
        echo 'Method Not Allowed';
        exit;
    }

    $full_path = __DIR__ . '/' . $file;
    if (file_exists($full_path)) {
        include $full_path;
    } else {
        http_response_code(500);
        echo 'Route file missing: ' . htmlspecialchars($file);
    }
} else {
    http_response_code(404);
    include __DIR__ . '/views/404.php';
}
