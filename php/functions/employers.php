<?php
require_once __DIR__ . '/../../php/config/connection.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Authentication and Role Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login');
    exit;
}

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $target_user_id = (int)($_POST['user_id'] ?? 0);

    if ($action === 'delete' && $target_user_id > 0) {
        try {
            $delStmt = $db->prepare("DELETE FROM users WHERE user_id = ? AND role = 'employer'");
            $delStmt->execute([$target_user_id]);
            $_SESSION['success'] = "Employer account and associated profile deleted.";
        } catch (Exception $e) {
            error_log("Error deleting employer: " . $e->getMessage());
            $_SESSION['error'] = "Failed to delete employer. Please remove associated job postings first if cascade is not set.";
        }
        header('Location: /admin/employers');
        exit;
    } 
    elseif ($action === 'update' && $target_user_id > 0) {
        $status = $_POST['status'] ?? 'active';
        try {
            $updStmt = $db->prepare("UPDATE users SET status = ? WHERE user_id = ? AND role = 'employer'");
            $updStmt->execute([$status, $target_user_id]);
            $_SESSION['success'] = "Employer status updated successfully.";
        } catch (Exception $e) {
            error_log("Error updating employer: " . $e->getMessage());
            $_SESSION['error'] = "Failed to update employer status.";
        }
        header('Location: /admin/employers');
        exit;
    }
}


$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

include_once __DIR__ . '/../partials/header.php';
?>