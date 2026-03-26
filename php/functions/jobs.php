<?php

/**
 * php/functions/jobs.php
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
    try {
        // Required fields
        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');

        // Optional fields
        $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $location    = trim($_POST['location'] ?? '');
        $job_type    = trim($_POST['job_type'] ?? 'Full Time');
        $salary_min  = !empty($_POST['salary_min']) ? (float)$_POST['salary_min'] : null;
        $salary_max  = !empty($_POST['salary_max']) ? (float)$_POST['salary_max'] : null;
        $experience_level = trim($_POST['experience_level'] ?? 'Not specified');
        $required_qualification = trim($_POST['required_qualification'] ?? 'Not specified');
        $years_experience_min = !empty($_POST['years_experience_min']) ? (int)$_POST['years_experience_min'] : null;
        $years_experience_max = !empty($_POST['years_experience_max']) ? (int)$_POST['years_experience_max'] : null;
        $deadline    = !empty($_POST['deadline']) ? $_POST['deadline'] : null;
        $featured    = isset($_POST['featured']) ? 1 : 0;

        if (empty($title) || empty($description)) {
            $_SESSION['error'] = 'Title and description are required.';
            header('Location: ' . BASE_URL . '/post-a-job');
            exit;
        }

        // Validate category_id exists if provided
        if ($category_id !== null) {
            $cat_chk = $db->prepare("SELECT category_id FROM job_categories WHERE category_id = ?");
            $cat_chk->execute([$category_id]);
            if (!$cat_chk->fetch()) {
                $_SESSION['error'] = 'Invalid category selected.';
                header('Location: ' . BASE_URL . '/post-a-job');
                exit;
            }
        }

        if ($action === 'create') {
            $db->prepare("INSERT INTO jobs (employer_id, title, category_id, location, job_type, salary_min, salary_max,
                                            experience_level, required_qualification, years_experience_min, years_experience_max,
                                            description, deadline, featured)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
                ->execute([
                    $employer_id,
                    $title,
                    $category_id,
                    $location,
                    $job_type,
                    $salary_min,
                    $salary_max,
                    $experience_level,
                    $required_qualification,
                    $years_experience_min,
                    $years_experience_max,
                    $description,
                    $deadline,
                    $featured
                ]);
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
            $db->prepare("UPDATE jobs SET title=?, category_id=?, location=?, job_type=?, salary_min=?, salary_max=?,
                                          experience_level=?, required_qualification=?, years_experience_min=?, years_experience_max=?,
                                          description=?, deadline=?, featured=? WHERE job_id=?")
                ->execute([
                    $title,
                    $category_id,
                    $location,
                    $job_type,
                    $salary_min,
                    $salary_max,
                    $experience_level,
                    $required_qualification,
                    $years_experience_min,
                    $years_experience_max,
                    $description,
                    $deadline,
                    $featured,
                    $job_id
                ]);
            $_SESSION['success'] = 'Job updated.';
        }
    } catch (Exception $e) {
        error_log("Error in job create/edit: " . $e->getMessage());
        $_SESSION['error'] = 'An error occurred: ' . $e->getMessage();
        header('Location: ' . BASE_URL . '/post-a-job');
        exit;
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
} elseif ($action === 'delete') {
    $job_id = (int)($_POST['job_id'] ?? 0);
    $chk = $db->prepare("SELECT employer_id FROM jobs WHERE job_id = ?");
    $chk->execute([$job_id]);
    $job = $chk->fetch();
    if (!$job || (int)$job['employer_id'] !== $employer_id) {
        http_response_code(403);
        exit;
    }
    
    // Delete the job from the database
    $db->prepare("DELETE FROM jobs WHERE job_id=?")->execute([$job_id]);
    $_SESSION['success'] = 'Job successfully deleted.';
}

header('Location: ' . BASE_URL . '/employer/jobs');
exit;
