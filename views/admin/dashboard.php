<?php
require_once __DIR__ . '/../../php/config/connection.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login');
    exit;
}

$uid = (int)$_SESSION['user_id'];
$db = getDB();

// Fetch System Stats
$totalUsers = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalSeekers = $db->query("SELECT COUNT(*) FROM users WHERE role = 'seeker'")->fetchColumn();
$totalEmployers = $db->query("SELECT COUNT(*) FROM users WHERE role = 'employer'")->fetchColumn();
$totalJobs = $db->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
$totalApplications = $db->query("SELECT COUNT(*) FROM applications")->fetchColumn();

// Fetch Recent Users
$stmt = $db->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
$recentUsers = $stmt->fetchAll();

// Fetch Recent Jobs
$stmt = $db->query("
    SELECT j.*, e.company_name
    FROM jobs j
    JOIN employer_profiles e ON j.employer_id = e.user_id
    ORDER BY j.created_at DESC
    LIMIT 5
");
$recentJobs = $stmt->fetchAll();

include_once __DIR__ . '/../partials/header.php';
?>

<!-- Hero Banner -->
<section class="relative w-full overflow-hidden" style="height:280px;">
    <div class="absolute inset-0" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 70%, #020617 100%); opacity:0.95;"></div>
    <div class="absolute inset-0 flex flex-col items-center justify-end z-10 pb-10">
        <h1 class="text-2xl md:text-3xl font-bold text-white mb-2 tracking-tight text-center">Admin Control Panel 🛡️</h1>
        <p class="text-slate-300 text-sm md:text-base text-center max-w-2xl px-4">System-wide overview and management of users, jobs, and applications.</p>
    </div>
</section>

<!-- Main content area -->
<div class="max-w-7xl w-full mx-auto px-6 py-12 flex flex-col lg:flex-row gap-10 items-start">

    <!-- Sidebar -->
    <?php include_once __DIR__ . '/../partials/sidebar.php'; ?>

    <!-- Page Content -->
    <main class="flex-1 min-w-0 w-full">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
            <!-- Total Users -->
            <div class="bg-white rounded-lg border border-slate-200 p-5 hover:shadow-md transition-shadow">
                <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Users</p>
                <div class="flex items-end justify-between mt-2">
                    <p class="text-2xl font-bold text-slate-900"><?php echo $totalUsers; ?></p>
                    <div class="bg-slate-100 rounded p-1.5">
                        <i data-lucide="users" class="w-4 h-4 text-slate-600"></i>
                    </div>
                </div>
            </div>

            <!-- Seekers -->
            <div class="bg-white rounded-lg border border-slate-200 p-5 hover:shadow-md transition-shadow">
                <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Seekers</p>
                <div class="flex items-end justify-between mt-2">
                    <p class="text-2xl font-bold text-blue-600"><?php echo $totalSeekers; ?></p>
                    <div class="bg-blue-50 rounded p-1.5">
                        <i data-lucide="user" class="w-4 h-4 text-blue-600"></i>
                    </div>
                </div>
            </div>

            <!-- Employers -->
            <div class="bg-white rounded-lg border border-slate-200 p-5 hover:shadow-md transition-shadow">
                <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Employers</p>
                <div class="flex items-end justify-between mt-2">
                    <p class="text-2xl font-bold text-indigo-600"><?php echo $totalEmployers; ?></p>
                    <div class="bg-indigo-50 rounded p-1.5">
                        <i data-lucide="building" class="w-4 h-4 text-indigo-600"></i>
                    </div>
                </div>
            </div>

            <!-- Jobs -->
            <div class="bg-white rounded-lg border border-slate-200 p-5 hover:shadow-md transition-shadow">
                <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Jobs</p>
                <div class="flex items-end justify-between mt-2">
                    <p class="text-2xl font-bold text-green-600"><?php echo $totalJobs; ?></p>
                    <div class="bg-green-50 rounded p-1.5">
                        <i data-lucide="briefcase" class="w-4 h-4 text-green-600"></i>
                    </div>
                </div>
            </div>

            <!-- Apps -->
            <div class="bg-white rounded-lg border border-slate-200 p-5 hover:shadow-md transition-shadow">
                <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Applications</p>
                <div class="flex items-end justify-between mt-2">
                    <p class="text-2xl font-bold text-[#fb236a]"><?php echo $totalApplications; ?></p>
                    <div class="bg-pink-50 rounded p-1.5">
                        <i data-lucide="file-text" class="w-4 h-4 text-[#fb236a]"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
            <!-- Recent Users -->
            <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wide flex items-center gap-2">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                        Recent Registrations
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-slate-700">Email</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-700">Role</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-700">Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentUsers as $user): ?>
                                <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-3 font-medium text-slate-900"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td class="px-6 py-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase <?php echo $user['role'] === 'employer' ? 'bg-indigo-50 text-indigo-700' : ($user['role'] === 'admin' ? 'bg-slate-900 text-white' : 'bg-blue-50 text-blue-700'); ?>">
                                            <?php echo $user['role']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-slate-500"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Jobs -->
            <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wide flex items-center gap-2">
                        <i data-lucide="briefcase" class="w-4 h-4"></i>
                        Newest Jobs
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-slate-700">Job Title</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-700">Company</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-700">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentJobs as $job): ?>
                                <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-3 font-medium text-slate-900"><?php echo htmlspecialchars($job['title']); ?></td>
                                    <td class="px-6 py-3 text-slate-700"><?php echo htmlspecialchars($job['company_name']); ?></td>
                                    <td class="px-6 py-3 text-slate-500"><?php echo date('M d, Y', strtotime($job['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- System Actions -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 flex flex-col items-center text-center">
                <div class="bg-white rounded-full p-2 border border-slate-200 mb-3">
                    <i data-lucide="settings" class="w-5 h-5 text-slate-600"></i>
                </div>
                <h3 class="text-xs font-bold text-slate-900 uppercase">System Config</h3>
                <p class="text-[11px] text-slate-500 mt-1">Manage global site settings and parameters.</p>
                <button class="mt-3 text-[11px] font-bold text-indigo-600 hover:underline">Manage</button>
            </div>
            <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 flex flex-col items-center text-center">
                <div class="bg-white rounded-full p-2 border border-slate-200 mb-3">
                    <i data-lucide="shield-check" class="w-5 h-5 text-slate-600"></i>
                </div>
                <h3 class="text-xs font-bold text-slate-900 uppercase">Security Log</h3>
                <p class="text-[11px] text-slate-500 mt-1">Review authentication and access logs.</p>
                <button class="mt-3 text-[11px] font-bold text-indigo-600 hover:underline">View Logs</button>
            </div>
            <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 flex flex-col items-center text-center">
                <div class="bg-white rounded-full p-2 border border-slate-200 mb-3">
                    <i data-lucide="mail" class="w-5 h-5 text-slate-600"></i>
                </div>
                <h3 class="text-xs font-bold text-slate-900 uppercase">Email Queue</h3>
                <p class="text-[11px] text-slate-500 mt-1">Monitor outgoing system notifications.</p>
                <button class="mt-3 text-[11px] font-bold text-indigo-600 hover:underline">Monitor</button>
            </div>
            <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 flex flex-col items-center text-center">
                <div class="bg-white rounded-full p-2 border border-slate-200 mb-3">
                    <i data-lucide="database" class="w-5 h-5 text-slate-600"></i>
                </div>
                <h3 class="text-xs font-bold text-slate-900 uppercase">Maintenance</h3>
                <p class="text-[11px] text-slate-500 mt-1">Database optimization and cleanup tools.</p>
                <button class="mt-3 text-[11px] font-bold text-indigo-600 hover:underline">Optimize</button>
            </div>
        </div>
    </main>
</div>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>
