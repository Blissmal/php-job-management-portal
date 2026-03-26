<?php

/**
 * php/functions/categories.php
 * Handles CRUD operations for job categories.
 * Only accessible to users with 'admin' role.
 */
require_once __DIR__ . '/../config/connection.php';
if (session_status() === PHP_SESSION_NONE) session_start();


// 403 Page shown on unauthorized user (Only admin user is allowed)
if ($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit();
}

$action = $_POST['action'] ?? '';
$db = getDB();

// conditional processing based on the value of action passed from the input in the form
if ($action === 'create') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $_SESSION['error'] = 'Invalid request method.';
        header('Location: ' . BASE_URL . '/admin/categories');
        exit;
    }

    $category_name = trim($_POST['category_name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($category_name)) {
        $_SESSION['error'] = 'Category name is required.';
        header('Location: ' . BASE_URL . '/admin/categories');
        exit;
    }

    try {
        $db->prepare("INSERT INTO job_categories (category_name, description) VALUES (?, ?)")
            ->execute([$category_name, $description]);
        $_SESSION['success'] = "Category '{$category_name}' created successfully.";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $_SESSION['error'] = "Category '{$category_name}' already exists.";
        } else {
            $_SESSION['error'] = 'Error creating category. Please try again.';
            error_log("Database error: " . $e->getMessage());
        }
    }
} elseif ($action === 'edit') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $_SESSION['error'] = 'Invalid request method.';
        header('Location: ' . BASE_URL . '/admin/categories');
        exit;
    }

    $category_id = (int)($_POST['category_id'] ?? 0);
    $category_name = trim($_POST['category_name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($category_id <= 0 || empty($category_name)) {
        $_SESSION['error'] = 'Invalid category or name.';
        header('Location: ' . BASE_URL . '/admin/categories');
        exit;
    }

    try {
        $db->prepare("UPDATE job_categories SET category_name = ?, description = ? WHERE category_id = ?")
            ->execute([$category_name, $description, $category_id]);
        $_SESSION['success'] = "Category updated successfully.";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $_SESSION['error'] = "A category with this name already exists.";
        } else {
            $_SESSION['error'] = 'Error updating category. Please try again.';
            error_log("Database error: " . $e->getMessage());
        }
    }
} elseif ($action === 'delete') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $_SESSION['error'] = 'Invalid request method.';
        header('Location: ' . BASE_URL . '/admin/categories');
        exit;
    }

    $category_id = (int)($_POST['category_id'] ?? 0);

    if ($category_id <= 0) {
        $_SESSION['error'] = 'Invalid category.';
        header('Location: ' . BASE_URL . '/admin/categories');
        exit;
    }

    try {
        // Check if any jobs use this category
        $check = $db->prepare("SELECT COUNT(*) as count FROM jobs WHERE category_id = ?");
        $check->execute([$category_id]);
        $result = $check->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] > 0) {
            $_SESSION['error'] = "Cannot delete category. It has {$result['count']} associated job(s).";
        } else {
            $db->prepare("DELETE FROM job_categories WHERE category_id = ?")
                ->execute([$category_id]);
            $_SESSION['success'] = "Category deleted successfully.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error deleting category. Please try again.';
        error_log("Database error: " . $e->getMessage());
    }
}

header('Location: ' . BASE_URL . '/admin/categories');
exit;
