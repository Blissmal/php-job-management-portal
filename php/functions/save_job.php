<?php

/**
 * php/functions/save-job.php
 */

require_once __DIR__ . '/../config/connection.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

// Must be a seeker — employers and admins cannot save jobs
if ($_SESSION['role'] !== 'seeker') {
    http_response_code(403);
    exit;
}

$seeker_id = (int)$_SESSION['user_id'];
$job_id    = (int)($_POST['job_id'] ?? 0);
$redirect  = $_POST['redirect'] ?? '/jobs';

if ($job_id <= 0) {
    $_SESSION['error'] = 'Invalid job ID.';
    header('Location: ' . $redirect);
    exit;
}

try {
    $db = getDB();

    // Verify job exists
    $checkJob = $db->prepare("SELECT job_id FROM jobs WHERE job_id = ?");
    $checkJob->execute([$job_id]);
    if (!$checkJob->fetch()) {
        $_SESSION['error'] = 'Job not found.';
        header('Location: ' . $redirect);
        exit;
    }

    // Toggle saved state
    $checkSaved = $db->prepare("SELECT save_id FROM saved_jobs WHERE seeker_id = ? AND job_id = ?");
    $checkSaved->execute([$seeker_id, $job_id]);

    if ($checkSaved->fetch()) {
        $db->prepare("DELETE FROM saved_jobs WHERE seeker_id = ? AND job_id = ?")
            ->execute([$seeker_id, $job_id]);
        $_SESSION['success'] = 'Job removed from saved jobs.';
    } else {
        $db->prepare("INSERT INTO saved_jobs (seeker_id, job_id) VALUES (?, ?)")
            ->execute([$seeker_id, $job_id]);
        $_SESSION['success'] = 'Job saved successfully!';
    }

} catch (Exception $e) {
    error_log("Save job error: " . $e->getMessage());
    $_SESSION['error'] = 'Something went wrong. Please try again.';
}

header('Location: ' . $redirect);
exit;