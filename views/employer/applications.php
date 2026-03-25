<?php
require_once __DIR__ . '/../../php/config/connection.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Check if user is logged in and is an employer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header('Location: /login');
    exit;
}

$uid = (int)$_SESSION['user_id'];
$job_id = isset($_GET['job_id']) ? (int)$_GET['job_id'] : null;
$db = getDB();

// Fetch applications for this employer
$query = "
    SELECT a.*, j.title as job_title, s.full_name as seeker_name, s.user_id as seeker_uid
    FROM applications a
    JOIN jobs j ON a.job_id = j.job_id
    JOIN job_seeker_profiles s ON a.seeker_id = s.user_id
    WHERE j.employer_id = ?
";
$params = [$uid];

if ($job_id) {
    $query .= " AND j.job_id = ?";
    $params[] = $job_id;
}

$query .= " ORDER BY a.applied_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$applications = $stmt->fetchAll();

// Fetch employer's jobs for filtering
$stmt = $db->prepare("SELECT job_id, title FROM jobs WHERE employer_id = ? ORDER BY title ASC");
$stmt->execute([$uid]);
$employerJobs = $stmt->fetchAll();

include_once __DIR__ . '/../partials/header.php';
?>

<!-- Hero Banner -->
<section class="relative w-full overflow-hidden" style="height:280px;">
    <div class="absolute inset-0" style="background: linear-gradient(135deg, #8b91dd 0%, #10195d 70%, #10195d 100%); opacity:0.92;"></div>
    <div class="absolute inset-0 flex flex-col items-center justify-end z-10 pb-8">
        <h1 class="text-2xl font-bold text-white mb-2 tracking-tight text-center">Applications Received</h1>
        <p class="text-gray-100 text-sm text-center">Review and manage candidates who applied for your jobs.</p>
    </div>
</section>

<div class="max-w-7xl w-full mx-auto px-6 py-12 flex flex-col lg:flex-row gap-10 items-start">
    <!-- Sidebar -->
    <?php include_once __DIR__ . '/../partials/sidebar.php'; ?>

    <!-- Page Content -->
    <main class="flex-1 min-w-0 w-full">
        <!-- Filter Bar -->
        <div class="mb-6 bg-white p-4 rounded-lg border border-slate-200 shadow-sm flex flex-wrap items-center gap-4">
            <span class="text-sm font-semibold text-slate-700">Filter by Job:</span>
            <form action="" method="GET" class="flex gap-2">
                <select name="job_id" class="text-sm border border-slate-300 rounded-md px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-[#fb236a]/20">
                    <option value="">All Jobs</option>
                    <?php foreach ($employerJobs as $job): ?>
                        <option value="<?php echo $job['job_id']; ?>" <?php echo $job_id === (int)$job['job_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($job['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="bg-slate-100 text-slate-700 px-4 py-1.5 rounded-md text-sm font-medium hover:bg-slate-200 transition-colors">Apply</button>
            </form>
            <?php if ($job_id): ?>
                <a href="/employer/applications" class="text-xs text-[#fb236a] hover:underline">Clear Filter</a>
            <?php endif; ?>
        </div>

        <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-sm">
            <?php if (count($applications) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-slate-700">Applicant</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-700">Job Title</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-700">Applied Date</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-700">Status</th>
                                <th class="px-6 py-3 text-right font-semibold text-slate-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $app):
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
                                        <div class="text-[11px] text-slate-500 uppercase font-bold tracking-wider mt-1">ID: #<?php echo $app['app_id']; ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-700"><?php echo htmlspecialchars($app['job_title']); ?></td>
                                    <td class="px-6 py-4 text-slate-600"><?php echo date('M d, Y', strtotime($app['applied_at'])); ?></td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusColor; ?>">
                                            <?php echo $app['status']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <form action="/php/functions/update_status.php" method="POST" class="inline">
                                                <input type="hidden" name="app_id" value="<?php echo $app['app_id']; ?>">
                                                <select name="status" onchange="this.form.submit()" class="text-xs border border-slate-200 rounded px-1.5 py-1 bg-white focus:outline-none">
                                                    <?php
                                                    $statuses = ['Pending', 'Reviewed', 'Shortlisted', 'Hired', 'Rejected'];
                                                    foreach ($statuses as $st): ?>
                                                        <option value="<?php echo $st; ?>" <?php echo $app['status'] === $st ? 'selected' : ''; ?>><?php echo $st; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </form>
                                            <a href="/application-detail?id=<?php echo $app['app_id']; ?>" class="p-1.5 rounded border border-slate-200 text-slate-600 hover:bg-slate-50" title="View Details">
                                                <i data-lucide="external-link" class="w-4 h-4"></i>
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
                    <i data-lucide="inbox" class="w-12 h-12 text-slate-300 mx-auto mb-4"></i>
                    <p class="text-slate-600">No applications found.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>
