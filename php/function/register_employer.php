<?php
/**
 * php/function/register_employer.php
 * Open employer registration — no Employer ID / registry validation.
 * Employers self-register with company details directly.
 */
require_once __DIR__ . '/../config/connection.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/register-employer'); exit;
}

$company_name = trim($_POST['company_name'] ?? '');
$industry     = trim($_POST['industry'] ?? '');
$website      = trim($_POST['website'] ?? '');
$email        = trim($_POST['email'] ?? '');
$pass         = $_POST['password'] ?? '';
$confirm      = $_POST['confirm_password'] ?? '';

$errors = [];
if (empty($company_name)) $errors[] = 'Company name is required.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
if (strlen($pass) < 8) $errors[] = 'Password must be at least 8 characters.';
if ($pass !== $confirm) $errors[] = 'Passwords do not match.';

if ($errors) {
    $_SESSION['error'] = implode(' ', $errors);
    header('Location: ' . BASE_URL . '/register-employer'); exit;
}

$db = getDB();
$stmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    $_SESSION['error'] = 'An account with this email already exists.';
    header('Location: ' . BASE_URL . '/register-employer'); exit;
}

$hash = password_hash($pass, PASSWORD_BCRYPT);

try {
    $db->beginTransaction();
    $db->prepare("INSERT INTO users (email, password_hash, role) VALUES (?, ?, 'employer')")
       ->execute([$email, $hash]);
    $uid = $db->lastInsertId();
    $db->prepare("INSERT INTO employer_profiles (user_id, company_name, industry, website) VALUES (?, ?, ?, ?)")
       ->execute([$uid, $company_name, $industry, $website]);
    $db->commit();
} catch (Exception $e) {
    $db->rollBack();
    $_SESSION['error'] = 'Registration failed. Please try again.';
    header('Location: ' . BASE_URL . '/register-employer'); exit;
}

session_regenerate_id(true);
$_SESSION['user_id'] = $uid;
$_SESSION['role']    = 'employer';
$_SESSION['status']  = 'active';
$_SESSION['email']   = $email;
$_SESSION['success'] = 'Employer account created! Start posting jobs.';
header('Location: ' . BASE_URL . '/dashboard'); exit;
