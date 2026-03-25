<?php
require_once __DIR__ . '/../../php/config/connection.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Check if user is logged in and is an employer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header('Location: /login');
    exit;
}

$uid = (int)$_SESSION['user_id'];
$db = getDB();

// Fetch company profile data
$stmt = $db->prepare("SELECT company_name, logo_path FROM employer_profiles WHERE user_id = ?");
$stmt->execute([$uid]);
$profile = $stmt->fetch();
$companyName = $profile['company_name'] ?? 'Employer';

// Determine greeting based on time
$hour = date('H');
if ($hour >= 5 && $hour < 12) {
    $greeting = "Good morning";
} elseif ($hour >= 12 && $hour < 17) {
    $greeting = "Good afternoon";
} else {
    $greeting = "Good evening";
}

// Fetch Job Stats
$stmt = $db->prepare("SELECT COUNT(*) FROM jobs WHERE employer_id = ?");
$stmt->execute([$uid]);
$totalJobs = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM jobs WHERE employer_id = ? AND status = 'open'");
$stmt->execute([$uid]);
$activeJobs = $stmt->fetchColumn();

// Fetch Application Stats
$stmt = $db->prepare("
    SELECT COUNT(*)
    FROM applications a
    JOIN jobs j ON a.job_id = j.job_id
    WHERE j.employer_id = ?
");
$stmt->execute([$uid]);
$totalApplications = $stmt->fetchColumn();

$stmt = $db->prepare("
    SELECT COUNT(*)
    FROM applications a
    JOIN jobs j ON a.job_id = j.job_id
    WHERE j.employer_id = ? AND a.status = 'Pending'
");
$stmt->execute([$uid]);
$pendingApplications = $stmt->fetchColumn();

// Fetch Recent Job Postings
$stmt = $db->prepare("
    SELECT j.*,
           (SELECT COUNT(*) FROM applications WHERE job_id = j.job_id) as app_count
    FROM jobs j
    WHERE j.employer_id = ?
    ORDER BY j.created_at DESC
    LIMIT 5
");
$stmt->execute([$uid]);
$recentJobs = $stmt->fetchAll();

// Fetch Recent Applications
$stmt = $db->prepare("
    SELECT a.*, j.title as job_title, s.full_name as seeker_name
    FROM applications a
    JOIN jobs j ON a.job_id = j.job_id
    JOIN job_seeker_profiles s ON a.seeker_id = s.user_id
    WHERE j.employer_id = ?
    ORDER BY a.applied_at DESC
    LIMIT 5
");
$stmt->execute([$uid]);
$recentApplications = $stmt->fetchAll();

include_once __DIR__ . '/../partials/header.php';
?>

<!-- Hero Banner -->
<section class="relative w-full overflow-hidden" style="height:280px;">
    <div class="absolute inset-0" style="background: linear-gradient(135deg, #8b91dd 0%, #10195d 70%, #10195d 100%); opacity:0.92;"></div>
    <div class="absolute inset-0 flex flex-col items-center justify-end z-10 pb-10">
        <h1 class="text-2xl md:text-3xl font-bold text-white mb-2 tracking-tight text-center"><?php echo $greeting; ?>, <?php echo htmlspecialchars($companyName); ?>! 👋</h1>
        <p class="text-gray-100 text-sm md:text-base text-center max-w-2xl px-4">Manage your job postings and find the best talent for your team.</p>
    </div>
</section>

<!-- Main content area -->
<div class="max-w-7xl w-full mx-auto px-6 py-12 flex flex-col lg:flex-row gap-10 items-start">

    <!-- Sidebar -->
    <?php include_once __DIR__ . '/../partials/sidebar.php'; ?>

    <!-- Page Content -->
    <main class="flex-1 min-w-0 w-full">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Total Jobs -->
            <div class="bg-white rounded-lg border border-slate-200 p-5 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-slate-600 text-sm font-medium">Total Jobs</p>
                        <p class="text-2xl font-bold text-slate-900 mt-2"><?php echo $totalJobs; ?></p>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-2">
                        <i data-lucide="briefcase" class="w-5 h-5 text-blue-600"></i>
                    </div>
                </div>
            </div>

            <!-- Active Jobs -->
            <div class="bg-white rounded-lg border border-slate-200 p-5 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-slate-600 text-sm font-medium">Active Jobs</p>
                        <p class="text-2xl font-bold text-green-600 mt-2"><?php echo $activeJobs; ?></p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-2">
                        <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                    </div>
                </div>
            </div>

            <!-- Total Applications -->
            <div class="bg-white rounded-lg border border-slate-200 p-5 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-slate-600 text-sm font-medium">Total Applications</p>
                        <p class="text-2xl font-bold text-slate-900 mt-2"><?php echo $totalApplications; ?></p>
                    </div>
                    <div class="bg-indigo-50 rounded-lg p-2">
                        <i data-lucide="users" class="w-5 h-5 text-indigo-600"></i>
                    </div>
                </div>
            </div>

            <!-- Pending Applications -->
            <div class="bg-white rounded-lg border border-slate-200 p-5 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-slate-600 text-sm font-medium">Pending Apps</p>
                        <p class="text-2xl font-bold text-yellow-600 mt-2"><?php echo $pendingApplications; ?></p>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-2">
                        <i data-lucide="clock" class="w-5 h-5 text-yellow-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
            <!-- Recent Job Postings -->
            <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-slate-900 flex items-center gap-2">
                        <i data-lucide="list" class="w-5 h-5 text-slate-600"></i>
                        Recent Job Postings
                    </h2>
                    <a href="/post-a-job" class="text-xs bg-[#fb236a] text-white px-3 py-1 rounded hover:bg-[#fb236a]/90 transition-colors">Post New</a>
                </div>

                <?php if (count($recentJobs) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-700">Job Title</th>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-700">Apps</th>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-700">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentJobs as $job):
                                    $statusColor = $job['status'] === 'open' ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-700';
                                ?>
                                    <tr class="border-b border-slate-200 hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-slate-900"><?php echo htmlspecialchars($job['title']); ?></div>
                                            <div class="text-xs text-slate-500"><?php echo date('M d, Y', strtotime($job['created_at'])); ?></div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center gap-1 text-slate-700">
                                                <i data-lucide="user" class="w-3.5 h-3.5"></i>
                                                <?php echo $job['app_count']; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusColor; ?>">
                                                <?php echo ucfirst($job['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
                        <a href="/employer/jobs" class="text-indigo-600 text-sm font-medium hover:underline inline-flex items-center gap-1">
                            Manage all jobs
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="px-6 py-12 text-center">
                        <i data-lucide="plus-circle" class="w-12 h-12 text-slate-300 mx-auto mb-4"></i>
                        <p class="text-slate-600">No jobs posted yet.</p>
                        <a href="/post-a-job" class="inline-block mt-4 text-[#fb236a] font-medium hover:underline">Post your first job</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Recent Applications -->
            <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h2 class="text-lg font-semibold text-slate-900 flex items-center gap-2">
                        <i data-lucide="user-plus" class="w-5 h-5 text-slate-600"></i>
                        Recent Applications
                    </h2>
                </div>

                <?php if (count($recentApplications) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-700">Applicant</th>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-700">Job</th>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-700">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentApplications as $app):
                                    $statusColor = match ($app['status']) {
                                        'Pending' => 'bg-yellow-50 text-yellow-700',
                                        'Reviewed' => 'bg-blue-50 text-blue-700',
                                        'Shortlisted' => 'bg-purple-50 text-purple-700',
                                        'Hired' => 'bg-green-50 text-green-700',
                                        'Rejected' => 'bg-red-50 text-red-700',
                                        default => 'bg-slate-50 text-slate-700'
                                    };
                                ?>
                                    <tr class="border-b border-slate-200 hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-slate-900"><?php echo htmlspecialchars($app['seeker_name']); ?></div>
                                            <div class="text-xs text-slate-500"><?php echo date('M d, Y', strtotime($app['applied_at'])); ?></div>
                                        </td>
                                        <td class="px-6 py-4 text-slate-700 max-w-[150px] truncate"><?php echo htmlspecialchars($app['job_title']); ?></td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusColor; ?>">
                                                <?php echo $app['status']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
                        <a href="/employer/applications" class="text-indigo-600 text-sm font-medium hover:underline inline-flex items-center gap-1">
                            View all applications
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="px-6 py-12 text-center">
                        <i data-lucide="inbox" class="w-12 h-12 text-slate-300 mx-auto mb-4"></i>
                        <p class="text-slate-600">No applications received yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-8">
            <a href="/post-a-job" class="bg-white border border-slate-200 rounded-lg p-6 hover:shadow-md hover:border-[#fb236a]/30 transition-all group">
                <div class="bg-pink-50 rounded-lg p-3 w-fit mb-4 group-hover:bg-[#fb236a] transition-colors">
                    <i data-lucide="plus" class="w-6 h-6 text-[#fb236a] group-hover:text-white"></i>
                </div>
                <h3 class="font-semibold text-slate-900">Post a Job</h3>
                <p class="text-sm text-slate-600 mt-1">Create a new job listing to find talent.</p>
            </a>

            <a href="/employer/profile" class="bg-white border border-slate-200 rounded-lg p-6 hover:shadow-md hover:border-blue-200 transition-all group">
                <div class="bg-blue-50 rounded-lg p-3 w-fit mb-4 group-hover:bg-blue-600 transition-colors">
                    <i data-lucide="building" class="w-6 h-6 text-blue-600 group-hover:text-white"></i>
                </div>
                <h3 class="font-semibold text-slate-900">Company Profile</h3>
                <p class="text-sm text-slate-600 mt-1">Update your company details and logo.</p>
            </a>

            <a href="/employer/jobs" class="bg-white border border-slate-200 rounded-lg p-6 hover:shadow-md hover:border-indigo-200 transition-all group">
                <div class="bg-indigo-50 rounded-lg p-3 w-fit mb-4 group-hover:bg-indigo-600 transition-colors">
                    <i data-lucide="briefcase" class="w-6 h-6 text-indigo-600 group-hover:text-white"></i>
                </div>
                <h3 class="font-semibold text-slate-900">Manage Jobs</h3>
                <p class="text-sm text-slate-600 mt-1">Edit, close, or reopen your job postings.</p>
            </a>
        </div>
    </main>

</div><!-- end flex container -->

<?php include_once __DIR__ . '/../partials/footer.php'; ?>
