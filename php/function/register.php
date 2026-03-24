<?php

/**
 * php/function/register.php
 * Unified registration handler for both job seekers and employers.
 */
require_once __DIR__ . '/../config/connection.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/register');
    exit;
}

// Get common fields
$email = trim($_POST['email'] ?? '');
$pass = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';
$role = trim($_POST['role'] ?? '');

// Normalize role to match ENUM values in database
if ($role === 'job_seeker') {
    $role = 'seeker';
}

// Validation
$errors = [];
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email is required.';
}
if (strlen($pass) < 8) {
    $errors[] = 'Password must be at least 8 characters.';
}
if ($pass !== $confirm) {
    $errors[] = 'Passwords do not match.';
}
if (!in_array($role, ['seeker', 'employer'])) {
    $errors[] = 'Invalid account type selected.';
}

if ($errors) {
    $_SESSION['error'] = implode(' ', $errors);
    header('Location: ' . BASE_URL . '/register');
    exit;
}

$db = getDB();

// Check email uniqueness
$stmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    $_SESSION['error'] = 'An account with this email already exists.';
    header('Location: ' . BASE_URL . '/register');
    exit;
}

$hash = password_hash($pass, PASSWORD_BCRYPT);

try {
    $db->beginTransaction();

    // Insert base user record
    $db->prepare("INSERT INTO users (email, password_hash, role) VALUES (?, ?, ?)")
        ->execute([$email, $hash, $role]);
    $uid = $db->lastInsertId();

    $db->commit();
} catch (Exception $e) {
    $db->rollBack();
    error_log('Registration error: ' . $e->getMessage());
    $_SESSION['error'] = 'Registration failed. Please try again.';
    header('Location: ' . BASE_URL . '/register');
    exit;
}

// Auto-login
session_regenerate_id(true);
$_SESSION['user_id'] = $uid;
$_SESSION['role'] = $role;
$_SESSION['status'] = 'active';
$_SESSION['email'] = $email;
$_SESSION['success'] = 'Account created successfully! Now complete your profile.';

// Redirect to appropriate profile page
$profileRedirect = ($role === 'seeker') ? '/seeker/profile' : '/employer/profile';
header('Location: ' . BASE_URL . $profileRedirect);
exit;
