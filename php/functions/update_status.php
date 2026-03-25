<?php

/**
 * php/functions/update_status.php
 * Employer updates an application's status.
 */
require_once __DIR__ . '/../config/connection.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SESSION['role'] !== 'employer') {
    http_response_code(403);
    exit;
}

$app_id    = (int)($_POST['app_id'] ?? 0);
$new_status = $_POST['status'] ?? '';
$allowed    = ['Pending', 'Reviewed', 'Shortlisted', 'Rejected', 'Hired'];

if (!in_array($new_status, $allowed)) {
    $_SESSION['error'] = 'Invalid status.';
    header('Location: ' . BASE_URL . '/dashboard');
    exit;
}

$db = getDB();
// Verify employer owns the job for this application
$stmt = $db->prepare(
    "SELECT a.app_id FROM applications a
     JOIN jobs j ON j.job_id = a.job_id
     WHERE a.app_id = ? AND j.employer_id = ?"
);
$stmt->execute([$app_id, (int)$_SESSION['user_id']]);
if (!$stmt->fetch()) {
    http_response_code(403);
    exit;
}

$db->prepare("UPDATE applications SET status=? WHERE app_id=?")->execute([$new_status, $app_id]);
$_SESSION['success'] = 'Status updated to ' . $new_status . '.';
header('Location: ' . BASE_URL . '/application-detail?id=' . $app_id);
exit;
