<?php

/**
 * php/functions/withdraw.php
 * Allows a seeker to withdraw a Pending application only.
 * Once Reviewed/Shortlisted/Hired/Rejected the employer has acted — withdrawal is blocked.
 */
require_once __DIR__ . '/../config/connection.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SESSION['role'] !== 'seeker') {
    http_response_code(403);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/dashboard');
    exit;
}

$app_id    = (int)($_POST['app_id'] ?? 0);
$seeker_id = (int)$_SESSION['user_id'];
$db        = getDB();

// Verify ownership and that status is still Pending
$stmt = $db->prepare(
    "SELECT app_id, status FROM applications WHERE app_id = ? AND seeker_id = ?"
);
$stmt->execute([$app_id, $seeker_id]);
$app = $stmt->fetch();

if (!$app) {
    $_SESSION['error'] = 'Application not found.';
    header('Location: ' . BASE_URL . '/dashboard');
    exit;
}

if ($app['status'] !== 'Pending') {
    $_SESSION['error'] = 'You can only withdraw a Pending application. This one has already been ' . $app['status'] . '.';
    header('Location: ' . BASE_URL . '/dashboard');
    exit;
}

$db->prepare("DELETE FROM applications WHERE app_id = ?")->execute([$app_id]);
$_SESSION['success'] = 'Application withdrawn successfully.';
header('Location: ' . BASE_URL . '/dashboard');
exit;
