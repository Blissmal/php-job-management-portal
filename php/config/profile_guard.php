<?php

/**
 * php/config/profile_guard.php
 * Middleware to check if user has completed their profile
 * Should be included in pages that require profile completion
 */

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/connection.php';

function isProfileComplete()
{
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        return false;
    }

    $uid = (int)$_SESSION['user_id'];
    $role = $_SESSION['role'];
    $db = getDB();

    if ($role === 'seeker') {
        $stmt = $db->prepare(
            "SELECT profile_id FROM job_seeker_profiles
             WHERE user_id = ? AND full_name IS NOT NULL AND full_name != ''
             AND headline IS NOT NULL AND headline != ''
             AND skills IS NOT NULL AND skills != ''
             AND location IS NOT NULL AND location != ''
             AND bio IS NOT NULL AND bio != ''
             AND resume_path IS NOT NULL AND resume_path != ''"
        );
        $stmt->execute([$uid]);
        return (bool)$stmt->fetch();
    } elseif ($role === 'employer') {
        $stmt = $db->prepare(
            "SELECT profile_id FROM employer_profiles
             WHERE user_id = ? AND company_name IS NOT NULL AND company_name != ''
             AND industry IS NOT NULL AND industry != ''
             AND website IS NOT NULL AND website != ''
             AND description IS NOT NULL AND description != ''"
        );
        $stmt->execute([$uid]);
        return (bool)$stmt->fetch();
    }

    return false;
}

function requireProfileComplete()
{
    if ($_SESSION['role'] === 'admin') {
        return;
    }

    if (!isProfileComplete()) {
        $role = $_SESSION['role'] ?? 'seeker';
        $_SESSION['profile_incomplete'] = true;
        if ($role === 'seeker') {
            header('Location: ' . BASE_URL . '/seeker/profile');
        } else {
            header('Location: ' . BASE_URL . '/employer/profile');
        }
        exit;
    }
}
