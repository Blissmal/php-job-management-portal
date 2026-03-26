<?php

/**
 * php/functions/apply.php
 * Cover letter required (min 50 chars). Resume can be uploaded or use existing resume from profile.
 * Resumes are stored in: uploads/resumes/applications/[job_id]/[seeker_id]/
 */
require_once __DIR__ . '/../config/connection.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SESSION['role'] !== 'seeker') {
    http_response_code(403);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/jobs');
    exit;
}

$job_id    = (int)($_POST['job_id'] ?? 0);
$seeker_id = (int)$_SESSION['user_id'];
$cover     = trim($_POST['cover_letter'] ?? '');

// Cover letter required, min 50 chars
if (strlen($cover) < 50) {
    $_SESSION['error'] = 'Cover letter is required and must be at least 50 characters.';
    header('Location: ' . BASE_URL . '/jobs/' . $job_id . '/apply');
    exit;
}
$cover = htmlspecialchars($cover);

$db = getDB();
$resume_path = null;

// Check if a new resume file was uploaded
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
        header('Location: ' . BASE_URL . '/jobs/' . $job_id . '/apply');
        exit;
    }

    // uploads/resumes/applications/[job_id]/[seeker_id]/
    $snapDir = __DIR__ . '/../../uploads/resumes/applications/' . $job_id . '/' . $seeker_id;
    if (!is_dir($snapDir)) {
        mkdir($snapDir, 0755, true);
    }

    $ext         = pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION);
    $name        = uniqid('cv_') . '.' . $ext;
    $dest        = $snapDir . '/' . $name;
    $resume_path = 'uploads/resumes/applications/' . $job_id . '/' . $seeker_id . '/' . $name;

    move_uploaded_file($_FILES['resume']['tmp_name'], $dest);
} else {
    // Use existing resume from profile
    try {
        $stmt = $db->prepare("SELECT resume_path FROM job_seeker_profiles WHERE user_id = ?");
        $stmt->execute([$seeker_id]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($profile && $profile['resume_path']) {
            $resume_path = $profile['resume_path'];
        } else {
            $_SESSION['error'] = 'No resume found. Please upload a resume to apply.';
            header('Location: ' . BASE_URL . '/jobs/' . $job_id . '/apply');
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'An error occurred while fetching your resume.';
        header('Location: ' . BASE_URL . '/jobs/' . $job_id . '/apply');
        exit;
    }
}

$db = getDB();
try {
    $db->prepare(
        "INSERT INTO applications (job_id, seeker_id, resume_snapshot_path, cover_letter)
         VALUES (?, ?, ?, ?)"
    )->execute([$job_id, $seeker_id, $resume_path, $cover]);

    header('Location: ' . BASE_URL . '/seeker/applications?applied=1');
    exit;
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        if (!empty($dest) && file_exists($dest)) unlink($dest);
        $_SESSION['error'] = 'You have already applied for this job.';
    } else {
        if (!empty($dest) && file_exists($dest)) unlink($dest);
        throw $e;
    }
}

header('Location: ' . BASE_URL . '/jobs/' . $job_id . '/apply');
exit;
