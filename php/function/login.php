<?php
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../config/profile_guard.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

$email = trim($_POST['email'] ?? '');
$pass  = $_POST['password'] ?? '';

if (empty($email) || empty($pass)) {
    $_SESSION['authError'] = 'Email and password are required.';
    header('Location: ' . BASE_URL . '/login');
    exit;
}

$db   = getDB();
$stmt = $db->prepare("SELECT user_id, password_hash, role, status FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($pass, $user['password_hash'])) {
    $_SESSION['authError'] = 'Invalid email or password.';
    header('Location: ' . BASE_URL . '/login');
    exit;
}

if ($user['status'] !== 'active') {
    $_SESSION['authError'] = 'Your account has been deactivated. Please contact the administrator.';
    header('Location: ' . BASE_URL . '/login');
    exit;
}

// Regenerate session ID to prevent fixation
session_regenerate_id(true);

$_SESSION['user_id'] = $user['user_id'];
$_SESSION['role']    = $user['role'];
$_SESSION['status']  = $user['status'];
$_SESSION['email']   = $email;

// Check if profile is complete
if (!isProfileComplete()) {
    // Redirect to appropriate profile page
    $profileRedirect = ($user['role'] === 'seeker') ? '/seeker/profile' : '/employer/profile';
    header('Location: ' . BASE_URL . $profileRedirect);
    exit;
}

// Redirect to appropriate dashboard
$dashboardRedirect = match ($user['role']) {
    'seeker' => '/seeker/dashboard',
    'employer' => '/employer/dashboard',
    'admin' => '/admin/dashboard',
    default => '/dashboard'
};

header('Location: ' . BASE_URL . $dashboardRedirect);
exit;
