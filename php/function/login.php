<?php
require_once __DIR__ . '/../config/connection.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

$email = trim($_POST['email'] ?? '');
$pass  = $_POST['password'] ?? '';

if (empty($email) || empty($pass)) {
    $_SESSION['error'] = 'Email and password are required.';
    header('Location: ' . BASE_URL . '/login');
    exit;
}

$db   = getDB();
$stmt = $db->prepare("SELECT user_id, password_hash, role, status FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($pass, $user['password_hash'])) {
    $_SESSION['error'] = 'Invalid email or password.';
    header('Location: ' . BASE_URL . '/login');
    exit;
}

if ($user['status'] !== 'active') {
    $_SESSION['error'] = 'Your account has been deactivated. Contact admin.';
    header('Location: ' . BASE_URL . '/login');
    exit;
}

// Regenerate session ID to prevent fixation
session_regenerate_id(true);

$_SESSION['user_id'] = $user['user_id'];
$_SESSION['role']    = $user['role'];
$_SESSION['status']  = $user['status'];
$_SESSION['email']   = $email;

header('Location: ' . BASE_URL . '/dashboard');
exit;
