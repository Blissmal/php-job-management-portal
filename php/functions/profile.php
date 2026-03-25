<?php

/**
 * php/functions/profile.php
 * Handles seeker profile update + resume upload, and employer profile update.
 * Seeker resumes are stored in: uploads/resumes/profiles/[user_id]/
 * Old profile resumes are deleted when a new one is uploaded.
 */
require_once __DIR__ . '/../config/connection.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/dashboard');
    exit;
}

$uid  = (int)$_SESSION['user_id'];
$role = $_SESSION['role'];
$db   = getDB();

if ($role === 'seeker') {
    $full_name = trim($_POST['full_name'] ?? '');
    $headline  = trim($_POST['headline'] ?? '');
    $skills    = trim($_POST['skills'] ?? '');
    $location  = trim($_POST['location'] ?? '');
    $bio       = trim($_POST['bio'] ?? '');

    // Validate all required fields
    $errors = [];
    if (empty($full_name)) $errors[] = 'Full name is required.';
    if (empty($headline)) $errors[] = 'Professional headline is required.';
    if (empty($skills)) $errors[] = 'Skills are required.';
    if (empty($location)) $errors[] = 'Location is required.';
    if (empty($bio)) $errors[] = 'Bio is required.';

    if ($errors) {
        $_SESSION['error'] = implode(' ', $errors);
        header('Location: ' . BASE_URL . '/seeker/profile');
        exit;
    }

    // Handle resume upload
    $resume_path = null;
    if (!empty($_FILES['resume']['name'])) {
        $allowed_mime = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($_FILES['resume']['tmp_name']);
        $size  = $_FILES['resume']['size'];

        if (!in_array($mime, $allowed_mime) || $size > 10 * 1024 * 1024) {
            $_SESSION['error'] = 'Invalid file. PDF/DOC/DOCX only, max 10 MB.';
            header('Location: ' . BASE_URL . '/seeker/profile');
            exit;
        }

        // Delete the existing profile resume before saving the new one
        $stmt = $db->prepare("SELECT resume_path FROM job_seeker_profiles WHERE user_id = ?");
        $stmt->execute([$uid]);
        $oldPath = $stmt->fetchColumn();
        if ($oldPath) {
            $oldFile = __DIR__ . '/../../' . $oldPath;
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        // Build per-user directory: uploads/resumes/profiles/[user_id]/
        $userDir = __DIR__ . '/../../uploads/resumes/profiles/' . $uid;
        if (!is_dir($userDir)) {
            mkdir($userDir, 0755, true);
        }

        $ext         = pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION);
        $name        = uniqid('resume_') . '.' . $ext;
        $dest        = $userDir . '/' . $name;
        $resume_path = 'uploads/resumes/profiles/' . $uid . '/' . $name;

        move_uploaded_file($_FILES['resume']['tmp_name'], $dest);

        $db->prepare(
            "INSERT INTO job_seeker_profiles (user_id, full_name, headline, skills, location, bio, resume_path)
             VALUES (?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
             full_name = ?, headline = ?, skills = ?, location = ?, bio = ?, resume_path = ?"
        )->execute([$uid, $full_name, $headline, $skills, $location, $bio, $resume_path, $full_name, $headline, $skills, $location, $bio, $resume_path]);
    } else {
        // Check if resume already exists
        $stmt = $db->prepare("SELECT resume_path FROM job_seeker_profiles WHERE user_id = ?");
        $stmt->execute([$uid]);
        $existing = $stmt->fetch();

        if (!$existing || !$existing['resume_path']) {
            $_SESSION['error'] = 'Resume is required.';
            header('Location: ' . BASE_URL . '/seeker/profile');
            exit;
        }

        $db->prepare(
            "INSERT INTO job_seeker_profiles (user_id, full_name, headline, skills, location, bio)
             VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
             full_name = ?, headline = ?, skills = ?, location = ?, bio = ?"
        )->execute([$uid, $full_name, $headline, $skills, $location, $bio, $full_name, $headline, $skills, $location, $bio]);
    }
} elseif ($role === 'employer') {
    $company_name = trim($_POST['company_name'] ?? '');
    $industry     = trim($_POST['industry'] ?? '');
    $website      = trim($_POST['website'] ?? '');
    $description  = trim($_POST['description'] ?? '');

    // Validate all required fields
    $errors = [];
    if (empty($company_name)) $errors[] = 'Company name is required.';
    if (empty($industry)) $errors[] = 'Industry is required.';
    if (empty($website)) $errors[] = 'Website is required.';
    if (empty($description)) $errors[] = 'Description is required.';

    if ($errors) {
        $_SESSION['error'] = implode(' ', $errors);
        header('Location: ' . BASE_URL . '/employer/profile');
        exit;
    }

    $db->prepare(
        "INSERT INTO employer_profiles (user_id, company_name, industry, website, description)
         VALUES (?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
         company_name = ?, industry = ?, website = ?, description = ?"
    )->execute([$uid, $company_name, $industry, $website, $description, $company_name, $industry, $website, $description]);
}

$_SESSION['success'] = 'Profile completed! Redirecting to dashboard...';

// Redirect based on role
$redirect = ($role === 'seeker') ? '/seeker/profile' : '/employer/profile';
header('Location: ' . BASE_URL . $redirect);
exit;
