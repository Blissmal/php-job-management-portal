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

// Fetch all jobs for this employer
$stmt = $db->prepare("
    SELECT j.*,
           (SELECT COUNT(*) FROM applications WHERE job_id = j.job_id) as app_count
    FROM jobs j
    WHERE j.employer_id = ?
    ORDER BY j.created_at DESC
");
$stmt->execute([$uid]);
$allJobs = $stmt->fetchAll();

include_once __DIR__ . '/../partials/header.php';
?>

<!-- Hero Banner -->
<section class="relative w-full overflow-hidden" style="height:280px;">
    <div class="absolute inset-0" style="background: linear-gradient(135deg, #8b91dd 0%, #10195d 70%, #10195d 100%); opacity:0.92;"></div>
    <div class="absolute inset-0 flex flex-col items-center justify-end z-10 pb-8">
        <h1 class="text-2xl font-bold text-white mb-2 tracking-tight text-center">Manage Your Jobs</h1>
        <p class="text-gray-100 text-sm text-center">View and manage all your posted job listings.</p>
    </div>
</section>

<div class="max-w-7xl w-full mx-auto px-6 py-12 flex flex-col lg:flex-row gap-10 items-start">
    <!-- Sidebar -->
    <?php include_once __DIR__ . '/../partials/sidebar.php'; ?>

    <!-- Page Content -->
    <main class="flex-1 min-w-0 w-full">
        <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-sm">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-slate-900">Your Postings</h2>
                <a href="/post-a-job" class="bg-[#fb236a] text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-[#fb236a]/90 transition-colors">Post New Job</a>
            </div>

            <?php if (count($allJobs) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-slate-700">Job Details</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-700 text-center">Applications</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-700 text-center">Status</th>
                                <th class="px-6 py-3 text-right font-semibold text-slate-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allJobs as $job):
                                $statusColor = $job['status'] === 'open' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-800';
                            ?>
                                <tr class="border-b border-slate-200 hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-slate-900"><?php echo htmlspecialchars($job['title']); ?></div>
                                        <div class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                                            <i data-lucide="map-pin" class="w-3 h-3"></i>
                                            <?php echo htmlspecialchars($job['location']); ?> • Posted <?php echo date('M d, Y', strtotime($job['created_at'])); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="/employer/applications?job_id=<?php echo $job['job_id']; ?>" class="inline-flex flex-col items-center group">
                                            <span class="text-lg font-bold text-slate-900 group-hover:text-[#fb236a]"><?php echo $job['app_count']; ?></span>
                                            <span class="text-[10px] uppercase font-semibold text-slate-500">View Apps</span>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusColor; ?>">
                                            <?php echo ucfirst($job['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <form action="/php/functions/jobs.php" method="POST" class="inline">
                                                <input type="hidden" name="job_id" value="<?php echo $job['job_id']; ?>">
                                                <input type="hidden" name="action" value="<?php echo $job['status'] === 'open' ? 'close' : 'reopen'; ?>">
                                                <button type="submit" class="p-1.5 rounded border border-slate-200 text-slate-600 hover:bg-slate-50" title="<?php echo $job['status'] === 'open' ? 'Close Posting' : 'Reopen Posting'; ?>">
                                                    <i data-lucide="<?php echo $job['status'] === 'open' ? 'lock' : 'unlock'; ?>" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                            <a href="/jobs/<?php echo $job['job_id']; ?>" class="p-1.5 rounded border border-slate-200 text-slate-600 hover:bg-slate-50" title="View Listing">
                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="px-6 py-12 text-center">
                    <i data-lucide="briefcase" class="w-12 h-12 text-slate-300 mx-auto mb-4"></i>
                    <p class="text-slate-600 font-medium">No jobs posted yet.</p>
                    <a href="/post-a-job" class="text-[#fb236a] hover:underline mt-2 inline-block">Post your first job</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>
