<?php
require_once __DIR__ . '/../config/connection.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Auth guard - employer only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header('Location: /login');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_status'], $_POST['app_id'])) {
    $db = getDB();
    $uid = (int) $_SESSION['user_id'];
    $app_id = (int) $_POST['app_id'];
    $newStatus = trim($_POST['new_status']);
    
    $validStatuses = ['Pending', 'Reviewed', 'Shortlisted', 'Hired', 'Rejected'];
    
    if (in_array($newStatus, $validStatuses)) {
        // Update the status, ensuring the employer owns the job this application is for
        $stmt = $db->prepare("
            UPDATE applications 
            SET status = ? 
            WHERE app_id = ? AND job_id IN (SELECT job_id FROM jobs WHERE employer_id = ?)
        ");
        
        if ($stmt->execute([$newStatus, $app_id, $uid]) && $stmt->rowCount() > 0) {
            $_SESSION['success'] = "Status updated to: <strong>" . htmlspecialchars($newStatus) . "</strong>";
        } else {
            $_SESSION['error'] = "Failed to update status or permission denied.";
        }
    } else {
        $_SESSION['error'] = "Invalid status selected.";
    }
    
    // Redirect back to the application detail page
    header("Location: /application-detail?id=" . $app_id);
    exit;
}

// Fallback redirect if accessed directly without POST data
header('Location: /employer/applications');
exit;