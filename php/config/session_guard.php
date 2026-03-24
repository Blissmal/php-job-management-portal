<?php

/**
 * php/config/session_guard.php
 * Include at the top of any protected view.
 * Usage:  
 * require_role('seeker');
 * require_role(['employer','admin']); supports multiple roles.
 * 403 Forbidden if not logged in or role mismatch. Inactive accounts are blocked and logged out.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_role(string|array $allowed): void
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
    $roles = is_array($allowed) ? $allowed : [$allowed];
    if (!in_array($_SESSION['role'], $roles, true)) {
        http_response_code(403);
        include __DIR__ . '/../../views/403.php';
        exit;
    }
    // Block inactive accounts
    if (($_SESSION['status'] ?? 'active') !== 'active') {
        session_destroy();
        header('Location: ' . BASE_URL . '/login?error=deactivated');
        exit;
    }
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function current_role(): string
{
    return $_SESSION['role'] ?? '';
}
