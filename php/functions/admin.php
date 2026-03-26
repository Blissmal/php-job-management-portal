<?php
/**
 * php/functions/admin_operations.php
 */
require_once __DIR__ . '/../config/connection.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Make sure the user is actually an admin before processing
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /admin/admins');
    exit;
}

$action = $_POST['action'] ?? '';
$db = getDB();

// Determine where to redirect back to
$redirect_url = $_SERVER['HTTP_REFERER'] ?? '/admin/admins';

if ($action === 'create_admin') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Basic validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Invalid email format.';
        header('Location: ' . $redirect_url);
        exit;
    }

    if (strlen($password) < 8) {
        $_SESSION['error'] = 'Password must be at least 8 characters long.';
        header('Location: ' . $redirect_url);
        exit;
    }

    // Check if email already exists
    $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = 'A user with this email already exists.';
        header('Location: ' . $redirect_url);
        exit;
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);

    try {
        // Insert new admin
        $stmt = $db->prepare("INSERT INTO users (email, password_hash, role, status) VALUES (?, ?, 'admin', 'active')");
        $stmt->execute([$email, $hash]);
        
        $_SESSION['success'] = 'New admin user created successfully.';
    } catch (Exception $e) {
        error_log('Admin creation error: ' . $e->getMessage());
        $_SESSION['error'] = 'Failed to create admin due to a system error.';
    }

    header('Location: ' . $redirect_url);
    exit;
} 
elseif ($action === 'revoke_admin') {
    $target_id = $_POST['target_user_id'] ?? 0;

    // Prevent revoking oneself
    if ((int)$target_id === (int)$_SESSION['user_id']) {
        $_SESSION['error'] = 'You cannot revoke your own admin access.';
        header('Location: ' . $redirect_url);
        exit;
    }

    try {
        // Update user status to inactive
        $stmt = $db->prepare("UPDATE users SET status = 'inactive' WHERE user_id = ? AND role = 'admin'");
        $stmt->execute([$target_id]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['success'] = 'Admin access revoked successfully.';
        } else {
            $_SESSION['error'] = 'Admin not found or already inactive.';
        }
    } catch (Exception $e) {
        error_log('Admin revoke error: ' . $e->getMessage());
        $_SESSION['error'] = 'Failed to revoke admin due to a system error.';
    }

    header('Location: ' . $redirect_url);
    exit;
} 
else {
    $_SESSION['error'] = 'Invalid action requested.';
    header('Location: ' . $redirect_url);
    exit;
}