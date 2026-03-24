<?php
require_once __DIR__ . '/../config/connection.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/register'); exit;
}

$full_name = trim($_POST['full_name'] ?? '');
$email     = trim($_POST['email'] ?? '');
$phone     = trim($_POST['phone'] ?? '');
$pass      = $_POST['password'] ?? '';
$confirm   = $_POST['confirm_password'] ?? '';

// Validation
$errors = [];
if (empty($full_name)) $errors[] = 'Full name is required.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
if (strlen($pass) < 8) $errors[] = 'Password must be at least 8 characters.';
if ($pass !== $confirm) $errors[] = 'Passwords do not match.';

if ($errors) {
    $_SESSION['error'] = implode(' ', $errors);
    header('Location: ' . BASE_URL . '/register'); exit;
}

$db = getDB();

// Check email uniqueness
$stmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    $_SESSION['error'] = 'An account with this email already exists.';
    header('Location: ' . BASE_URL . '/register'); exit;
}

$hash = password_hash($pass, PASSWORD_BCRYPT);

try {
    $db->beginTransaction();
    $db->prepare("INSERT INTO users (email, password_hash, role) VALUES (?, ?, 'seeker')")
       ->execute([$email, $hash]);
    $uid = $db->lastInsertId();
    $db->prepare("INSERT INTO job_seeker_profiles (user_id, full_name) VALUES (?, ?)")
       ->execute([$uid, $full_name]);
    $db->commit();
} catch (Exception $e) {
    $db->rollBack();
    $_SESSION['error'] = 'Registration failed. Please try again.';
    header('Location: ' . BASE_URL . '/register'); exit;
}

// Auto-login
session_regenerate_id(true);
$_SESSION['user_id'] = $uid;
$_SESSION['role']    = 'seeker';
$_SESSION['status']  = 'active';
$_SESSION['email']   = $email;
$_SESSION['success'] = 'Account created! Welcome to KYU Job Portal.';
header('Location: ' . BASE_URL . '/dashboard'); exit;
