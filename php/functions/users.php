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
            // Delete user. Ensure your DB uses ON DELETE CASCADE for related profile/application tables!
            $delStmt = $db->prepare("DELETE FROM users WHERE user_id = ? AND role = 'seeker'");
            $delStmt->execute([$target_user_id]);
            $_SESSION['success'] = "User successfully deleted.";
        } catch (Exception $e) {
            error_log("Error deleting user: " . $e->getMessage());
            $_SESSION['error'] = "Failed to delete user. They may have dependent records.";
        }
        header('Location: /admin/seekers');
        exit;
    } 
    elseif ($action === 'update' && $target_user_id > 0) {
        $status = $_POST['status'] ?? 'active';
        try {
            // Update the user's status
            $updStmt = $db->prepare("UPDATE users SET status = ? WHERE user_id = ? AND role = 'seeker'");
            $updStmt->execute([$status, $target_user_id]);
            $_SESSION['success'] = "User status updated successfully.";
        } catch (Exception $e) {
            error_log("Error updating user: " . $e->getMessage());
            $_SESSION['error'] = "Failed to update user status.";
        }
        header('Location: /admin/users');
        exit;
    }
}


$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

?>