<?php
require_once __DIR__ . '/../../php/config/connection.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Check if user is logged in and is a seeker
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seeker') {
    header('Location: /login');
    exit;
}

$uid = (int)$_SESSION['user_id'];
$db = getDB();
$email = $_SESSION['email'] ?? '';
$userName = explode('@', $email)[0];

// Fetch profile data
$stmt = $db->prepare("SELECT full_name FROM job_seeker_profiles WHERE user_id = ?");
$stmt->execute([$uid]);
$profile = $stmt->fetch();
$displayName = $profile['full_name'] ?? ucfirst($userName);

// Determine greeting based on time
$hour = date('H');
if ($hour >= 5 && $hour < 12) {
    $greeting = "Good morning";
} elseif ($hour >= 12 && $hour < 17) {
    $greeting = "Good afternoon";
} else {
    $greeting = "Good evening";
}

// Fetch application stats
$stmt = $db->prepare("SELECT status, COUNT(*) as count FROM applications WHERE seeker_id = ? GROUP BY status");
$stmt->execute([$uid]);
$stats = [];
while ($row = $stmt->fetch()) {
    $stats[$row['status']] = $row['count'];
}

$pendingCount = $stats['pending'] ?? 0;
$acceptedCount = $stats['accepted'] ?? 0;
$rejectedCount = $stats['rejected'] ?? 0;
$totalApplications = $pendingCount + $acceptedCount + $rejectedCount;

// Fetch recent applications
$stmt = $db->prepare("
    SELECT ja.app_id, j.title, c.company_name, ja.status, ja.applied_at
    FROM applications ja
    JOIN jobs j ON ja.job_id = j.job_id
    JOIN employer_profiles c ON j.employer_id = c.profile_id
    WHERE ja.seeker_id = ?
    ORDER BY ja.applied_at DESC
    LIMIT 5
");
$stmt->execute([$uid]);
$recentApplications = $stmt->fetchAll();

// Fetch saved jobs count
$stmt = $db->prepare("SELECT COUNT(*) as count FROM saved_jobs WHERE seeker_id = ?");
$stmt->execute([$uid]);
$savedJobsCount = $stmt->fetch()['count'] ?? 0;

include_once __DIR__ . '/../partials/header.php';
?>

<!-- Hero Banner -->
<section class="relative w-full overflow-hidden" style="height:280px;">
    <div class="absolute inset-0" style="background: linear-gradient(135deg, #8b91dd 0%, #10195d 70%, #10195d 100%); opacity:0.92;"></div>
    <div class="absolute inset-0 flex flex-col items-center justify-end z-10 pb-10">
        <h1 class="text-2xl md:text-3xl font-bold text-white mb-2 tracking-tight text-center"><?php echo $greeting; ?>, <?php echo htmlspecialchars($displayName); ?>! 👋</h1>
        <p class="text-gray-200 text-sm md:text-base text-center max-w-2xl px-4">Ready to take the next step in your career? Explore amazing opportunities waiting for you.</p>
    </div>
</section>

<!-- Main content area: sidebar + page content side by side -->
<div class="max-w-7xl w-full mx-auto px-6 py-12 flex gap-10 items-start">

    <!-- Sidebar (inline, sticky) -->
    <div class="sticky top-20 self-start">
        <?php include_once __DIR__ . '/../partials/sidebar.php'; ?>
    </div>

    <!-- Page Content -->
    <main class="flex-1 min-w-0">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Total Applications -->
            <div class="bg-white rounded-lg border border-slate-200 p-5 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-slate-600 text-sm font-medium">Total Applications</p>
                        <p class="text-2xl font-bold text-slate-900 mt-2"><?php echo $totalApplications; ?></p>
                    </div>
                    <div class="bg-indigo-50 rounded-lg p-2">
                        <i data-lucide="file-text" class="w-5 h-5 text-indigo-600"></i>
                    </div>
                </div>
            </div>

            <!-- Pending Applications -->
            <div class="bg-white rounded-lg border border-slate-200 p-5 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-slate-600 text-sm font-medium">Pending</p>
                        <p class="text-2xl font-bold text-slate-900 mt-2"><?php echo $pendingCount; ?></p>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-2">
                        <i data-lucide="clock" class="w-5 h-5 text-yellow-600"></i>
                    </div>
                </div>
            </div>

            <!-- Accepted Applications -->
            <div class="bg-white rounded-lg border border-slate-200 p-5 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-slate-600 text-sm font-medium">Accepted</p>
                        <p class="text-2xl font-bold text-green-600 mt-2"><?php echo $acceptedCount; ?></p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-2">
                        <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                    </div>
                </div>
            </div>

            <!-- Rejected Applications -->
            <div class="bg-white rounded-lg border border-slate-200 p-5 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-slate-600 text-sm font-medium">Rejected</p>
                        <p class="text-2xl font-bold text-red-600 mt-2"><?php echo $rejectedCount; ?></p>
                    </div>
                    <div class="bg-red-50 rounded-lg p-2">
                        <i data-lucide="x-circle" class="w-5 h-5 text-red-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Applications Section -->
        <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                <h2 class="text-lg font-semibold text-slate-900 flex items-center gap-2">
                    <i data-lucide="history" class="w-5 h-5 text-slate-600"></i>
                    Recent Applications
                </h2>
            </div>

            <?php if (count($recentApplications) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-slate-700">Job Title</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-700">Company</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-700">Status</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-700">Applied Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentApplications as $app):
                                $statusColor = match ($app['status']) {
                                    'pending' => 'bg-yellow-50 text-yellow-700',
                                    'accepted' => 'bg-green-50 text-green-700',
                                    'rejected' => 'bg-red-50 text-red-700',
                                    default => 'bg-slate-50 text-slate-700'
                                };
                                $statusIcon = match ($app['status']) {
                                    'pending' => 'clock',
                                    'accepted' => 'check-circle',
                                    'rejected' => 'x-circle',
                                    default => 'help-circle'
                                };
                            ?>
                                <tr class="border-b border-slate-200 hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 font-medium text-slate-900"><?php echo htmlspecialchars($app['title']); ?></td>
                                    <td class="px-6 py-4 text-slate-700"><?php echo htmlspecialchars($app['company_name']); ?></td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium <?php echo $statusColor; ?>">
                                            <i data-lucide="<?php echo $statusIcon; ?>" class="w-3.5 h-3.5"></i>
                                            <?php echo ucfirst($app['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600"><?php echo date('M d, Y', strtotime($app['applied_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
                    <a href="/seeker/applications" class="text-indigo-600 text-sm font-medium hover:underline inline-flex items-center gap-1">
                        View all applications
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            <?php else: ?>
                <div class="px-6 py-12 text-center">
                    <i data-lucide="inbox" class="w-12 h-12 text-slate-300 mx-auto mb-4"></i>
                    <p class="text-slate-600 text-base">No applications yet.</p>
                    <p class="text-slate-500 text-sm mt-1">Start applying to jobs to see them here!</p>
                    <a href="/jobs" class="inline-block mt-4 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                        Browse Jobs
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-8">
            <!-- Update Profile -->
            <a href="/seeker/profile" class="bg-indigo-50 border border-indigo-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                <div class="flex items-start gap-4">
                    <div class="bg-indigo-600 rounded-lg p-3">
                        <i data-lucide="user-check" class="w-6 h-6 text-white"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-slate-900">Update Your Profile</h3>
                        <p class="text-sm text-slate-600 mt-1">Keep your profile fresh and relevant to attract more employers</p>
                    </div>
                    <i data-lucide="arrow-right" class="w-5 h-5 text-indigo-600 flex-shrink-0"></i>
                </div>
            </a>

            <!-- Browse Jobs -->
            <a href="/jobs" class="bg-blue-50 border border-blue-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                <div class="flex items-start gap-4">
                    <div class="bg-blue-600 rounded-lg p-3">
                        <i data-lucide="search" class="w-6 h-6 text-white"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-slate-900">Explore Jobs</h3>
                        <p class="text-sm text-slate-600 mt-1">Discover new job opportunities that match your skills</p>
                    </div>
                    <i data-lucide="arrow-right" class="w-5 h-5 text-blue-600 flex-shrink-0"></i>
                </div>
            </a>
        </div>
    </main>

</div><!-- end flex container -->

<?php include_once __DIR__ . '/../partials/footer.php'; ?>
