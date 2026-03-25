<?php

/**
 * php/functions/save-job.php
 * Handles saving/unsaving jobs for job seekers
 */

require_once __DIR__ . '/../config/connection.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Determine if AJAX request or form submission
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    } else {
        http_response_code(405);
    }
    exit;
}

$seeker_id = $_SESSION['user_id'] ?? null;
if (!$seeker_id || $_SESSION['role'] !== 'seeker') {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    } else {
        http_response_code(403);
    }
    exit;
}

$job_id = (int)($_POST['job_id'] ?? 0);
if ($job_id <= 0) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid job ID']);
    } else {
        $_SESSION['error'] = 'Invalid job ID';
        header('Location: /jobs');
    }
    exit;
}

try {
    $db = getDB();

    // Check if job exists
    $checkJob = $db->prepare("SELECT job_id FROM jobs WHERE job_id = ?");
    $checkJob->execute([$job_id]);
    if (!$checkJob->fetch()) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Job not found']);
        } else {
            $_SESSION['error'] = 'Job not found';
            header('Location: /jobs');
        }
        exit;
    }

    // Check if already saved
    $checkSaved = $db->prepare("SELECT save_id FROM saved_jobs WHERE seeker_id = ? AND job_id = ?");
    $checkSaved->execute([$seeker_id, $job_id]);
    $isSaved = (bool)$checkSaved->fetch();

    if ($isSaved) {
        // Remove from saved
        $db->prepare("DELETE FROM saved_jobs WHERE seeker_id = ? AND job_id = ?")
            ->execute([$seeker_id, $job_id]);
        $message = 'Job removed from saved jobs';
    } else {
        // Add to saved
        $db->prepare("INSERT INTO saved_jobs (seeker_id, job_id) VALUES (?, ?)")
            ->execute([$seeker_id, $job_id]);
        $message = 'Job saved successfully!';
    }

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        $_SESSION['success'] = $message;
        header('Location: /jobs/' . $job_id);
    }
    exit;
} catch (Exception $e) {
    error_log("Save job error: " . $e->getMessage());
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error processing request']);
    } else {
        $_SESSION['error'] = 'Error processing request';
        header('Location: /jobs');
    }
    exit;
}
