<?php

/**
 * php/function/jobs.php
 * Accepts POST requests with 'action' create/edit/close/reopen and relevant job data.
 * Only accessible to users with 'employer' role.
 */
require_once __DIR__ . '/../config/connection.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/dashboard');
    exit;
}
if ($_SESSION['role'] !== 'employer') {
    http_response_code(403);
    exit;
}

$action      = $_POST['action'] ?? '';
$employer_id = (int)$_SESSION['user_id'];
$db          = getDB();

if ($action === 'create' || $action === 'edit') {
    $title       = trim($_POST['title'] ?? '');
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $location    = trim($_POST['location'] ?? '');
    $salary_min  = !empty($_POST['salary_min']) ? (float)$_POST['salary_min'] : null;
    $salary_max  = !empty($_POST['salary_max']) ? (float)$_POST['salary_max'] : null;
    $description = trim($_POST['description'] ?? '');
    $deadline    = !empty($_POST['deadline']) ? $_POST['deadline'] : null;

    if (empty($title) || empty($description)) {
        $_SESSION['error'] = 'Title and description are required.';
        header('Location: ' . BASE_URL . '/job-create');
        exit;
    }

    // Validate category_id exists if provided
    if ($category_id !== null) {
        $cat_chk = $db->prepare("SELECT category_id FROM job_categories WHERE category_id = ?");
        $cat_chk->execute([$category_id]);
        if (!$cat_chk->fetch()) {
            $_SESSION['error'] = 'Invalid category selected.';
            header('Location: ' . BASE_URL . '/job-create');
            exit;
        }
    }

    if ($action === 'create') {
        $db->prepare("INSERT INTO jobs (employer_id, title, category_id, location, salary_min, salary_max, description, deadline)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([$employer_id, $title, $category_id, $location, $salary_min, $salary_max, $description, $deadline]);
        $_SESSION['success'] = 'Job posted successfully.';
    } else {
        $job_id = (int)($_POST['job_id'] ?? 0);
        // Verify ownership
        $chk = $db->prepare("SELECT employer_id FROM jobs WHERE job_id = ?");
        $chk->execute([$job_id]);
        $job = $chk->fetch();
        if (!$job || (int)$job['employer_id'] !== $employer_id) {
            http_response_code(403);
            exit;
        }
        $db->prepare("UPDATE jobs SET title=?, category_id=?, location=?, salary_min=?, salary_max=?, description=?, deadline=? WHERE job_id=?")
            ->execute([$title, $category_id, $location, $salary_min, $salary_max, $description, $deadline, $job_id]);
        $_SESSION['success'] = 'Job updated.';
    }
} elseif ($action === 'close') {
    $job_id = (int)($_POST['job_id'] ?? 0);
    $chk = $db->prepare("SELECT employer_id FROM jobs WHERE job_id = ?");
    $chk->execute([$job_id]);
    $job = $chk->fetch();
    if (!$job || (int)$job['employer_id'] !== $employer_id) {
        http_response_code(403);
        exit;
    }
    $db->prepare("UPDATE jobs SET status='closed' WHERE job_id=?")->execute([$job_id]);
    $_SESSION['success'] = 'Job closed.';
} elseif ($action === 'reopen') {
    $job_id = (int)($_POST['job_id'] ?? 0);
    $chk = $db->prepare("SELECT employer_id FROM jobs WHERE job_id = ?");
    $chk->execute([$job_id]);
    $job = $chk->fetch();
    if (!$job || (int)$job['employer_id'] !== $employer_id) {
        http_response_code(403);
        exit;
    }
    $db->prepare("UPDATE jobs SET status='open' WHERE job_id=?")->execute([$job_id]);
    $_SESSION['success'] = 'Job reopened.';
}

header('Location: ' . BASE_URL . '/dashboard');
exit;
