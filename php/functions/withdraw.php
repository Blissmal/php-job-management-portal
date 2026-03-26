<?php

/**
 * php/functions/withdraw.php
 * Accepts an optional `redirect` field; falls back to /dashboard.
 */
require_once __DIR__ . '/../config/connection.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Auth: seekers only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seeker') {
    http_response_code(403);
    header('Location: ' . BASE_URL . '/login');
    exit;
}

// POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/dashboard');
    exit;
}

$app_id    = (int)($_POST['app_id']  ?? 0);
$seeker_id = (int)$_SESSION['user_id'];

// Sanitise the redirect: must start with / and contain no protocol (no open redirect)
$raw_redirect = trim($_POST['redirect'] ?? '');
$redirect     = (str_starts_with($raw_redirect, '/') && !str_starts_with($raw_redirect, '//'))
    ? $raw_redirect
    : '/dashboard';

if ($app_id <= 0) {
    $_SESSION['error'] = 'Invalid application.';
    header('Location: ' . BASE_URL . $redirect);
    exit;
}

$db = getDB();

// Verify ownership and fetch current status
$stmt = $db->prepare(
    "SELECT app_id, status FROM applications WHERE app_id = ? AND seeker_id = ?"
);
$stmt->execute([$app_id, $seeker_id]);
$app = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$app) {
    $_SESSION['error'] = 'Application not found.';
    header('Location: ' . BASE_URL . $redirect);
    exit;
}

if ($app['status'] !== 'Pending') {
    $_SESSION['error'] = 'Only Pending applications can be withdrawn. This one has already been ' . $app['status'] . '.';
    header('Location: ' . BASE_URL . $redirect);
    exit;
}

$db->prepare("DELETE FROM applications WHERE app_id = ?")->execute([$app_id]);

$_SESSION['success'] = 'Application withdrawn successfully.';
header('Location: ' . BASE_URL . $redirect);
exit;