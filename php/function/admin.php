<?php

/**
 * php/function/admin.php
 * Admin actions: activate/deactivate users, delete users.
 * Accepts POST requests with 'action' activate/deactivate/delete and 'user_id' all from the form.
 * Only accessible to users with 'admin' role.
 * 
 */
require_once __DIR__ . '/../config/connection.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit;
}

$action  = $_POST['action'] ?? '';
$user_id = (int)($_POST['user_id'] ?? 0);
$db      = getDB();

if ($action === 'activate') {
    $db->prepare("UPDATE users SET status='active' WHERE user_id=?")->execute([$user_id]);
    $_SESSION['success'] = 'User activated.';
} elseif ($action === 'deactivate') {
    $db->prepare("UPDATE users SET status='inactive' WHERE user_id=?")->execute([$user_id]);
    $_SESSION['success'] = 'User deactivated.';
} elseif ($action === 'delete') {
    $db->prepare("DELETE FROM users WHERE user_id=?")->execute([$user_id]);
    $_SESSION['success'] = 'User deleted.';
}

header('Location: ' . BASE_URL . '/admin');
exit;
