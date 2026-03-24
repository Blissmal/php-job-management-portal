<?php
/**
 * php/function/save_job.php
 * Toggle saved job for a seeker.
 */
require_once __DIR__ . '/../config/connection.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SESSION['role'] !== 'seeker') { http_response_code(403); exit; }

$job_id    = (int)($_POST['job_id'] ?? 0);
$seeker_id = (int)$_SESSION['user_id'];
$db        = getDB();

$stmt = $db->prepare("SELECT save_id FROM saved_jobs WHERE seeker_id=? AND job_id=?");
$stmt->execute([$seeker_id, $job_id]);

if ($stmt->fetch()) {
    $db->prepare("DELETE FROM saved_jobs WHERE seeker_id=? AND job_id=?")->execute([$seeker_id, $job_id]);
    $_SESSION['success'] = 'Job removed from saved list.';
} else {
    try {
        $db->prepare("INSERT INTO saved_jobs (seeker_id, job_id) VALUES (?, ?)")->execute([$seeker_id, $job_id]);
        $_SESSION['success'] = 'Job saved!';
    } catch (PDOException $e) {  }
}

$redirect = $_POST['redirect'] ?? (BASE_URL . '/jobs');
header('Location: ' . $redirect); exit;
