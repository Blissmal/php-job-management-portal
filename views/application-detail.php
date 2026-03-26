<?php
require_once __DIR__ . '/../php/config/connection.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Auth guard - employer only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header('Location: /login');
    exit;
}

$uid = (int) $_SESSION['user_id'];
$app_id = (int) ($_GET['id'] ?? 0);
$db = getDB();

// Fetch application with all related data
$stmt = $db->prepare("
    SELECT 
        a.app_id,
        a.job_id,
        a.seeker_id,
        a.status,
        a.applied_at,
        a.cover_letter,
        a.resume_snapshot_path,
        j.title as job_title,
        j.location as job_location,
        j.job_type,
        j.salary_min,
        j.salary_max,
        j.description as job_description,
        j.created_at as job_posted_at,
        s.full_name,
        s.headline,
        s.skills,
        s.location as seeker_location,
        s.bio,
        s.resume_path,
        u.email as seeker_email,
        e.company_name
    FROM applications a
    JOIN jobs j ON a.job_id = j.job_id
    JOIN job_seeker_profiles s ON a.seeker_id = s.user_id
    JOIN users u ON a.seeker_id = u.user_id
    JOIN users eu ON j.employer_id = eu.user_id
    JOIN employer_profiles e ON eu.user_id = e.user_id
    WHERE a.app_id = ? AND j.employer_id = ?
");
$stmt->execute([$app_id, $uid]);
$app = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$app) {
    $_SESSION['error'] = 'Application not found or access denied.';
    header('Location: /employer/applications');
    exit;
}

// Fetch all applications for this job (for comparison stats)
$stmt = $db->prepare("
    SELECT status, COUNT(*) as count 
    FROM applications 
    WHERE job_id = ? 
    GROUP BY status
");
$stmt->execute([$app['job_id']]);
$statusStats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Fetch other applications from this seeker (to show if they've applied elsewhere)
$stmt = $db->prepare("
    SELECT a.app_id, j.title, j.job_id, a.status, a.applied_at
    FROM applications a
    JOIN jobs j ON a.job_id = j.job_id
    WHERE a.seeker_id = ? AND j.employer_id = ? AND a.app_id != ?
    ORDER BY a.applied_at DESC
    LIMIT 5
");
$stmt->execute([$app['seeker_id'], $uid, $app_id]);
$otherApplications = $stmt->fetchAll();

// Count total applications from this seeker across all employers
$stmt = $db->prepare("
    SELECT COUNT(*) as total 
    FROM applications 
    WHERE seeker_id = ?
");
$stmt->execute([$app['seeker_id']]);
$totalApplications = $stmt->fetchColumn();

// Status change handler
$statusMessage = null;
if (isset($_SESSION['success'])) {
    $statusMessage = $_SESSION['success'];
    unset($_SESSION['success']);
}
$errorMessage = null;
if (isset($_SESSION['error'])) {
    $errorMessage = $_SESSION['error'];
    unset($_SESSION['error']);
}

include_once 'partials/header.php';
?>

<!-- Hero Banner -->
<section class="relative w-full overflow-hidden" style="height:280px;">
    <div class="absolute inset-0" style="background: linear-gradient(135deg, #8b91dd 0%, #10195d 70%, #10195d 100%); opacity:0.92;"></div>
    <div class="absolute inset-0 flex flex-col items-center justify-end pb-10 z-10">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-3 tracking-tight">Application Review</h1>
        <p class="text-gray-300 text-sm">
            <a href="/" class="underline underline-offset-2 hover:text-white transition-colors">Home</a>
            <span class="mx-2 opacity-50">—</span>
            <a href="/employer/applications" class="underline underline-offset-2 hover:text-white transition-colors">Applications</a>
            <span class="mx-2 opacity-50">—</span>
            <span class="text-white">Application #<?= $app_id ?></span>
        </p>
    </div>
</section>

<main class="min-h-screen pb-20 bg-slate-50">
    <div class="max-w-6xl mx-auto px-4 py-10">

        <!-- Status Alert -->
        <?php if ($errorMessage): ?>
            <div class="bg-red-50 border border-red-200 rounded-xl px-5 py-4 mb-6 flex items-center gap-3">
                <p class="text-sm text-red-700 font-medium"><?= htmlspecialchars($errorMessage) ?></p>
            </div>
        <?php endif; ?>

        <!-- Status Alert -->
        <?php if ($statusMessage): ?>
            <div class="bg-green-50 border border-green-200 rounded-xl px-5 py-4 mb-6 flex items-center gap-3">
                <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <p class="text-sm text-green-700 font-medium"><?= $statusMessage ?></p>
            </div>
        <?php endif; ?>

        <!-- Back Button -->
        <a href="/employer/applications" class="text-sm text-slate-500 hover:text-slate-700 transition-colors flex items-center gap-1.5 mb-6">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Applications
        </a>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Left Column: Candidate Profile & Application Details -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Candidate Overview Card -->
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                    <div style="background: linear-gradient(135deg, #8b91dd 0%, #10195d 70%);" class="h-24"></div>
                    <div class="px-6 pb-6">
                        <!-- Profile Section -->
                        <div class="flex items-start gap-4 -mt-12 mb-6 relative">
                            <div style="background: linear-gradient(135deg, #8b91dd 0%, #10195d 70%, #10195d 100%);" class="w-24 h-24 rounded-xl border-4 border-white flex items-center justify-center text-4xl font-bold text-white">
                                <?= strtoupper(substr($app['full_name'], 0, 1)) ?>
                            </div>
                            <div class="flex-1 pt-4">
                                <h1 class="text-2xl font-bold text-slate-800"><?= htmlspecialchars($app['full_name']) ?></h1>
                                <p class="text-sm text-slate-500 mt-1"><?= htmlspecialchars($app['seeker_email']) ?></p>
                                <?php if ($app['headline']): ?>
                                    <p class="text-sm text-slate-600 mt-2 font-medium"><?= htmlspecialchars($app['headline']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Info Grid -->
                        <div class="grid grid-cols-2 gap-4 mb-6 py-6 border-t border-b border-slate-200">
                            <div>
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Applied For</p>
                                <p class="text-sm font-semibold text-slate-800 mt-1"><?= htmlspecialchars($app['job_title']) ?></p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Applied Date</p>
                                <p class="text-sm font-semibold text-slate-800 mt-1"><?= date('M d, Y', strtotime($app['applied_at'])) ?></p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Location</p>
                                <p class="text-sm font-semibold text-slate-800 mt-1"><?= htmlspecialchars($app['seeker_location'] ?? 'Not specified') ?></p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Applications</p>
                                <p class="text-sm font-semibold text-slate-800 mt-1"><?= $totalApplications ?> role<?= $totalApplications !== 1 ? 's' : '' ?></p>
                            </div>
                        </div>

                        <!-- Bio -->
                        <?php if ($app['bio']): ?>
                            <div class="mb-6">
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">About</p>
                                <p class="text-sm text-slate-700 leading-relaxed"><?= htmlspecialchars($app['bio']) ?></p>
                            </div>
                        <?php endif; ?>

                        <!-- Skills -->
                        <?php if ($app['skills']): ?>
                            <div class="mb-6">
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Skills</p>
                                <div class="flex flex-wrap gap-2">
                                    <?php foreach (array_filter(array_map('trim', explode(',', $app['skills']))) as $skill): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700">
                                            <?= htmlspecialchars($skill) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Resume Download -->
                        <?php if ($app['resume_path']): ?>
                            <div class="pt-6 border-t border-slate-200">
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Resume</p>
                                <a href="/php/functions/download_resume.php?app_id=<?= $app['app_id'] ?>"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors text-sm font-medium">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Download Resume
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Cover Letter Card -->
                <?php if ($app['cover_letter']): ?>
                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                        <div class="px-6 py-5 border-b border-slate-200 bg-slate-50">
                            <h3 class="font-bold text-slate-800">Cover Letter</h3>
                        </div>
                        <div class="px-6 py-6">
                            <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-wrap"><?= htmlspecialchars($app['cover_letter']) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Other Applications from This Seeker -->
                <?php if (!empty($otherApplications)): ?>
                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                        <div class="px-6 py-5 border-b border-slate-200 bg-slate-50">
                            <h3 class="font-bold text-slate-800">Other Applications by <?= htmlspecialchars($app['full_name']) ?></h3>
                        </div>
                        <div class="divide-y divide-slate-200">
                            <?php foreach ($otherApplications as $other): ?>
                                <a href="/application-detail?id=<?= $other['app_id'] ?>" class="px-6 py-4 flex items-center justify-between hover:bg-slate-50 transition-colors group">
                                    <div>
                                        <p class="font-medium text-slate-800 group-hover:text-indigo-600 transition-colors"><?= htmlspecialchars($other['title']) ?></p>
                                        <p class="text-xs text-slate-500 mt-1"><?= date('M d, Y', strtotime($other['applied_at'])) ?></p>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= match ($other['status']) {
                                                                                                                                'Pending' => 'bg-yellow-50 text-yellow-700',
                                                                                                                                'Reviewed' => 'bg-blue-50 text-blue-700',
                                                                                                                                'Shortlisted' => 'bg-purple-50 text-purple-700',
                                                                                                                                'Hired' => 'bg-green-50 text-green-700',
                                                                                                                                'Rejected' => 'bg-red-50 text-red-700',
                                                                                                                                default => 'bg-slate-50 text-slate-700'
                                                                                                                            } ?>">
                                        <?= $other['status'] ?>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

            <!-- Right Column: Status & Analysis -->
            <div class="space-y-6">

                <!-- Status Management Card -->
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                    <div class="px-6 py-5 border-b border-slate-200 bg-slate-50">
                        <h3 class="font-bold text-slate-800">Application Status</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <!-- Current Status Badge -->
                        <div>
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Current Status</p>
                            <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-semibold <?= match ($app['status']) {
                                                                                                                    'Pending' => 'bg-yellow-50 text-yellow-700',
                                                                                                                    'Reviewed' => 'bg-blue-50 text-blue-700',
                                                                                                                    'Shortlisted' => 'bg-purple-50 text-purple-700',
                                                                                                                    'Hired' => 'bg-green-50 text-green-700',
                                                                                                                    'Rejected' => 'bg-red-50 text-red-700',
                                                                                                                    default => 'bg-slate-50 text-slate-700'
                                                                                                                } ?>">
                                <?= $app['status'] ?>
                            </span>
                        </div>

                        <!-- Status Update Form -->
                        <form method="POST" action="/php/functions/application-post.php" class="space-y-3">
                            <input type="hidden" name="app_id" value="<?= $app_id ?>">
                            <div>
                                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-2">Change Status</label>
                                <select name="new_status" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    <option value="">Select new status...</option>
                                    <?php foreach (['Pending', 'Reviewed', 'Shortlisted', 'Hired', 'Rejected'] as $status): ?>
                                        <option value="<?= $status ?>" <?= $app['status'] === $status ? 'disabled' : '' ?>>
                                            <?= $status ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="w-full px-4 py-2.5 rounded-lg text-sm font-semibold text-white transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                style="background-color:#D5225A;"
                                onmouseover="this.style.backgroundColor='#b81c4a'"
                                onmouseout="this.style.backgroundColor='#D5225A'">
                                Update Status
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Job Info Card -->
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                    <div class="px-6 py-5 border-b border-slate-200 bg-slate-50">
                        <h3 class="font-bold text-slate-800">Job Position</h3>
                    </div>
                    <div class="px-6 py-6 space-y-4">
                        <div>
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Title</p>
                            <p class="text-sm font-semibold text-slate-800"><?= htmlspecialchars($app['job_title']) ?></p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Job Type</p>
                            <p class="text-sm font-semibold text-slate-800"><?= htmlspecialchars($app['job_type']) ?></p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Location</p>
                            <p class="text-sm font-semibold text-slate-800"><?= htmlspecialchars($app['job_location'] ?? 'Not specified') ?></p>
                        </div>
                        <?php if ($app['salary_min'] && $app['salary_max']): ?>
                            <div>
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Salary Range</p>
                                <p class="text-sm font-semibold text-slate-800">
                                    <?= number_format($app['salary_min']) ?> - <?= number_format($app['salary_max']) ?>
                                </p>
                            </div>
                        <?php endif; ?>
                        <a href="/jobs/<?= $app['job_id'] ?>" class="block mt-4 px-4 py-2 rounded-lg text-center text-sm font-semibold text-indigo-600 hover:bg-indigo-50 transition-colors border border-indigo-200">
                            View Job Posting
                        </a>
                    </div>
                </div>

                <!-- Application Statistics Card -->
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                    <div class="px-6 py-5 border-b border-slate-200 bg-slate-50">
                        <h3 class="font-bold text-slate-800">Job Applications Summary</h3>
                    </div>
                    <div class="px-6 py-6 space-y-3">
                        <?php $totalApps = array_sum($statusStats); ?>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <p class="text-sm font-medium text-slate-700">Total Applications</p>
                                <p class="text-lg font-bold text-slate-800"><?= $totalApps ?></p>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-2"></div>
                        </div>

                        <!-- Status breakdown -->
                        <?php foreach (['Pending' => 'yellow', 'Reviewed' => 'blue', 'Shortlisted' => 'purple', 'Hired' => 'green', 'Rejected' => 'red'] as $status => $color): ?>
                            <?php $count = $statusStats[$status] ?? 0;
                            $percentage = $totalApps > 0 ? ($count / $totalApps) * 100 : 0; ?>
                            <div class="pt-2">
                                <div class="flex items-center justify-between mb-1">
                                    <p class="text-xs font-medium text-slate-600"><?= $status ?></p>
                                    <p class="text-xs font-bold text-slate-700"><?= $count ?> (<?= number_format($percentage, 0) ?>%)</p>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-<?= $color ?>-500 h-full transition-all" style="width: <?= $percentage ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Quick Actions Card -->
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                    <div class="px-6 py-5 border-b border-slate-200 bg-slate-50">
                        <h3 class="font-bold text-slate-800">Quick Actions</h3>
                    </div>
                    <div class="p-6 space-y-4">

                        <!-- Transition Email Section -->
                        <div>
                            <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-2">
                                Transition Message
                            </label>
                            <select id="transitionMessageSelect"
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent mb-3">
                                <option value="">Select a message template...</option>
                                <optgroup label="Pending">
                                    <option
                                        data-subject="We've received your application — <?= htmlspecialchars($app['job_title']) ?>"
                                        data-body="Dear <?= htmlspecialchars($app['full_name']) ?>,<?php echo "\n\n"; ?>Thank you for applying for the <?= htmlspecialchars($app['job_title']) ?> position at <?= htmlspecialchars($app['company_name']) ?>. We have received your application and it is currently under review.<?php echo "\n\n"; ?>We will be in touch once we have had a chance to review all applications.<?php echo "\n\n"; ?>Best regards,<?php echo "\n"; ?><?= htmlspecialchars($app['company_name']) ?> Hiring Team">
                                        Application Received — Under Review
                                    </option>
                                </optgroup>
                                <optgroup label="Reviewed">
                                    <option
                                        data-subject="Your application has been reviewed — <?= htmlspecialchars($app['job_title']) ?>"
                                        data-body="Dear <?= htmlspecialchars($app['full_name']) ?>,<?php echo "\n\n"; ?>We wanted to let you know that we have reviewed your application for the <?= htmlspecialchars($app['job_title']) ?> role at <?= htmlspecialchars($app['company_name']) ?>. Your profile has been noted and we are currently evaluating all candidates.<?php echo "\n\n"; ?>We appreciate your interest and patience.<?php echo "\n\n"; ?>Best regards,<?php echo "\n"; ?><?= htmlspecialchars($app['company_name']) ?> Hiring Team">
                                        Application Reviewed
                                    </option>
                                </optgroup>
                                <optgroup label="Shortlisted">
                                    <option
                                        data-subject="Great news! You've been shortlisted — <?= htmlspecialchars($app['job_title']) ?>"
                                        data-body="Dear <?= htmlspecialchars($app['full_name']) ?>,<?php echo "\n\n"; ?>We are pleased to inform you that you have been shortlisted for the <?= htmlspecialchars($app['job_title']) ?> position at <?= htmlspecialchars($app['company_name']) ?>. Your skills and experience stood out among our applicants.<?php echo "\n\n"; ?>We will be reaching out shortly to schedule the next steps in our selection process.<?php echo "\n\n"; ?>Best regards,<?php echo "\n"; ?><?= htmlspecialchars($app['company_name']) ?> Hiring Team">
                                        Shortlisted — Next Steps Coming
                                    </option>
                                    <option
                                        data-subject="Interview Invitation — <?= htmlspecialchars($app['job_title']) ?>"
                                        data-body="Dear <?= htmlspecialchars($app['full_name']) ?>,<?php echo "\n\n"; ?>Congratulations! After reviewing your application for the <?= htmlspecialchars($app['job_title']) ?> role, we would like to invite you for an interview at <?= htmlspecialchars($app['company_name']) ?>.<?php echo "\n\n"; ?>Please reply to this email with your availability over the next few days so we can schedule a convenient time.<?php echo "\n\n"; ?>We look forward to speaking with you!<?php echo "\n\n"; ?>Best regards,<?php echo "\n"; ?><?= htmlspecialchars($app['company_name']) ?> Hiring Team">
                                        Shortlisted — Interview Invitation
                                    </option>
                                </optgroup>
                                <optgroup label="Hired">
                                    <option
                                        data-subject="Offer of Employment — <?= htmlspecialchars($app['job_title']) ?>"
                                        data-body="Dear <?= htmlspecialchars($app['full_name']) ?>,<?php echo "\n\n"; ?>We are delighted to offer you the position of <?= htmlspecialchars($app['job_title']) ?> at <?= htmlspecialchars($app['company_name']) ?>. After careful consideration, we believe you are an excellent fit for our team.<?php echo "\n\n"; ?>Please reply to this email to confirm your acceptance, and we will send over the formal offer letter and onboarding details shortly.<?php echo "\n\n"; ?>Welcome to the team!<?php echo "\n\n"; ?>Best regards,<?php echo "\n"; ?><?= htmlspecialchars($app['company_name']) ?> Hiring Team">
                                        Job Offer — Congratulations
                                    </option>
                                </optgroup>
                                <optgroup label="Rejected">
                                    <option
                                        data-subject="Your application update — <?= htmlspecialchars($app['job_title']) ?>"
                                        data-body="Dear <?= htmlspecialchars($app['full_name']) ?>,<?php echo "\n\n"; ?>Thank you for your interest in the <?= htmlspecialchars($app['job_title']) ?> position at <?= htmlspecialchars($app['company_name']) ?> and for the time you invested in your application.<?php echo "\n\n"; ?>After careful review, we regret to inform you that we will not be moving forward with your application at this time. This was a difficult decision as we received many strong applications.<?php echo "\n\n"; ?>We wish you the very best in your job search and future endeavours.<?php echo "\n\n"; ?>Kind regards,<?php echo "\n"; ?><?= htmlspecialchars($app['company_name']) ?> Hiring Team">
                                        Application Unsuccessful
                                    </option>
                                    <option
                                        data-subject="Update on your application — <?= htmlspecialchars($app['job_title']) ?>"
                                        data-body="Dear <?= htmlspecialchars($app['full_name']) ?>,<?php echo "\n\n"; ?>Thank you for applying to <?= htmlspecialchars($app['company_name']) ?> for the <?= htmlspecialchars($app['job_title']) ?> role. We appreciated the opportunity to learn more about your background.<?php echo "\n\n"; ?>At this time, we have decided to move forward with other candidates whose experience more closely aligns with our current needs. We encourage you to keep an eye on our future openings.<?php echo "\n\n"; ?>We wish you success in your search.<?php echo "\n\n"; ?>Kind regards,<?php echo "\n"; ?><?= htmlspecialchars($app['company_name']) ?> Hiring Team">
                                        Not the Right Fit — Encouragement
                                    </option>
                                </optgroup>
                            </select>

                            <!-- Message Preview -->
                            <div id="messagePreview" class="hidden mb-3 p-3 bg-slate-50 border border-slate-200 rounded-lg">
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Subject</p>
                                <p id="previewSubject" class="text-xs text-slate-700 font-medium mb-2"></p>
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Message</p>
                                <p id="previewBody" class="text-xs text-slate-600 leading-relaxed whitespace-pre-wrap max-h-28 overflow-y-auto"></p>
                            </div>

                            <button id="sendEmailBtn"
                                onclick="openMailto()"
                                disabled
                                class="w-full px-4 py-2.5 rounded-lg text-sm font-semibold transition-colors border-2 text-slate-400 border-slate-200 bg-slate-50 cursor-not-allowed"
                                data-email="<?= htmlspecialchars($app['seeker_email']) ?>">
                                <span class="flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    Send Email
                                </span>
                            </button>
                        </div>

                        <a href="/employer/applications" class="block w-full px-4 py-2.5 rounded-lg text-center text-sm font-semibold text-slate-600 border-2 border-slate-200 hover:bg-slate-50 transition-colors">
                            Back to List
                        </a>
                    </div>
                </div>

                <script>
                    const select = document.getElementById('transitionMessageSelect');
                    const btn = document.getElementById('sendEmailBtn');
                    const preview = document.getElementById('messagePreview');
                    const previewSubject = document.getElementById('previewSubject');
                    const previewBody = document.getElementById('previewBody');

                    select.addEventListener('change', function () {
                        const opt = this.options[this.selectedIndex];
                        const subject = opt.getAttribute('data-subject');
                        const body = opt.getAttribute('data-body');

                        if (subject && body) {
                            previewSubject.textContent = subject;
                            previewBody.textContent = body;
                            preview.classList.remove('hidden');

                            btn.disabled = false;
                            btn.classList.remove('text-slate-400', 'border-slate-200', 'bg-slate-50', 'cursor-not-allowed');
                            btn.classList.add('text-indigo-600', 'border-indigo-200', 'hover:bg-indigo-50');
                        } else {
                            preview.classList.add('hidden');
                            btn.disabled = true;
                            btn.classList.add('text-slate-400', 'border-slate-200', 'bg-slate-50', 'cursor-not-allowed');
                            btn.classList.remove('text-indigo-600', 'border-indigo-200', 'hover:bg-indigo-50');
                        }
                    });

                    function openMailto() {
                        const opt = select.options[select.selectedIndex];
                        const email = btn.getAttribute('data-email');
                        const subject = encodeURIComponent(opt.getAttribute('data-subject') || '');
                        const body = encodeURIComponent(opt.getAttribute('data-body') || '');
                        window.location.href = `mailto:${email}?subject=${subject}&body=${body}`;
                    }
                </script>

            </div>

        </div>

    </div>
</main>

<style>
    /* Gradient color support for progress bars */
    .bg-yellow-500 {
        background-color: #eab308;
    }

    .bg-blue-500 {
        background-color: #3b82f6;
    }

    .bg-purple-500 {
        background-color: #a855f7;
    }

    .bg-green-500 {
        background-color: #22c55e;
    }

    .bg-red-500 {
        background-color: #ef4444;
    }
</style>

<?php include_once 'partials/footer.php'; ?>