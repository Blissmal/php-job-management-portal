<?php

/**
 * php/function/profile.php
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
            header('Location: ' . BASE_URL . '/profile');
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
            "UPDATE job_seeker_profiles
             SET full_name = ?, headline = ?, skills = ?, location = ?, bio = ?, resume_path = ?
             WHERE user_id = ?"
        )->execute([$full_name, $headline, $skills, $location, $bio, $resume_path, $uid]);

    } else {
        // No new resume uploaded — leave existing resume_path untouched
        $db->prepare(
            "UPDATE job_seeker_profiles
             SET full_name = ?, headline = ?, skills = ?, location = ?, bio = ?
             WHERE user_id = ?"
        )->execute([$full_name, $headline, $skills, $location, $bio, $uid]);
    }

} elseif ($role === 'employer') {
    $company_name = trim($_POST['company_name'] ?? '');
    $industry     = trim($_POST['industry'] ?? '');
    $website      = trim($_POST['website'] ?? '');
    $description  = trim($_POST['description'] ?? '');

    $db->prepare(
        "UPDATE employer_profiles
         SET company_name = ?, industry = ?, website = ?, description = ?
         WHERE user_id = ?"
    )->execute([$company_name, $industry, $website, $description, $uid]);
}

$_SESSION['success'] = 'Profile updated.';
header('Location: ' . BASE_URL . '/profile');
exit;