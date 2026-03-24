<?php

/**
 * php/function/download_resume.php
 * Streams resume files to authorised users only.
 * Employers can download resumes for jobs they own.
 * Seekers can download their own application snapshot or profile resume.
 */
require_once __DIR__ . '/../config/connection.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

$app_id = (int)($_GET['app_id'] ?? 0);
$db     = getDB();
$row    = null;

if ($_SESSION['role'] === 'employer') {
    // Employer may only download resumes attached to their own job listings
    $stmt = $db->prepare(
        "SELECT a.resume_snapshot_path
         FROM applications a
         JOIN jobs j ON j.job_id = a.job_id
         WHERE a.app_id = ? AND j.employer_id = ?"
    );
    $stmt->execute([$app_id, (int)$_SESSION['user_id']]);
    $row = $stmt->fetch();
} elseif ($_SESSION['role'] === 'seeker') {
    // Seeker may download their own application snapshot
    $stmt = $db->prepare(
        "SELECT resume_snapshot_path
         FROM applications
         WHERE app_id = ? AND seeker_id = ?"
    );
    $stmt->execute([$app_id, (int)$_SESSION['user_id']]);
    $row = $stmt->fetch();

    if (!$row) {
        // Fall back to their profile resume
        $stmt2 = $db->prepare(
            "SELECT resume_path AS resume_snapshot_path
             FROM job_seeker_profiles
             WHERE user_id = ?"
        );
        $stmt2->execute([(int)$_SESSION['user_id']]);
        $row = $stmt2->fetch();
    }
}

if (empty($row['resume_snapshot_path'])) {
    http_response_code(404);
    echo 'File not found.';
    exit;
}

$path = __DIR__ . '/../../' . $row['resume_snapshot_path'];

if (!file_exists($path)) {
    http_response_code(404);
    echo 'File not found.';
    exit;
}

$ext      = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$mime     = mime_content_type($path);


$uid      = (int)$_SESSION['user_id'];
$filename = 'resume_' . $uid . '.' . $ext;

header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($path));
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
readfile($path);
exit;
