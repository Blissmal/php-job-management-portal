<?php

/**
 * php/function/apply.php
 * Both cover letter (min 50 chars) and resume are required.
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
    header('Location: ' . BASE_URL . '/job-detail?id=' . $job_id);
    exit;
}
$cover = htmlspecialchars($cover);

// Resume is required
if (empty($_FILES['resume']['name'])) {
    $_SESSION['error'] = 'A resume file is required to apply.';
    header('Location: ' . BASE_URL . '/job-detail?id=' . $job_id);
    exit;
}

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
    header('Location: ' . BASE_URL . '/job-detail?id=' . $job_id);
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

$db = getDB();
try {
    $db->prepare(
        "INSERT INTO applications (job_id, seeker_id, resume_snapshot_path, cover_letter)
         VALUES (?, ?, ?, ?)"
    )->execute([$job_id, $seeker_id, $resume_path, $cover]);
    $_SESSION['success'] = 'Application submitted successfully!';
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        if (file_exists($dest)) {
            unlink($dest);
        }
        $_SESSION['error'] = 'You have already applied for this job.';
    } else {
        if (file_exists($dest)) {
            unlink($dest);
        }
        throw $e;
    }
}

header('Location: ' . BASE_URL . '/dashboard');
exit;
