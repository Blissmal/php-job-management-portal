<?php
require_once __DIR__ . '/../../php/config/connection.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Check if user is logged in and is a seeker
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seeker') {
    header('Location: /login');
    exit;
}

$uid = (int)$_SESSION['user_id'];
$db  = getDB();

// Fetch application stats
$stmt = $db->prepare("SELECT status, COUNT(*) as count FROM applications WHERE seeker_id = ? GROUP BY status");
$stmt->execute([$uid]);
$stats = [];
while ($row = $stmt->fetch()) {
    $stats[$row['status']] = $row['count'];
}
$pendingCount  = $stats['pending']  ?? 0;
$acceptedCount = $stats['accepted'] ?? 0;
$rejectedCount = $stats['rejected'] ?? 0;
$totalApplications = $pendingCount + $acceptedCount + $rejectedCount;

// Filters
$statusFilter = $_GET['status'] ?? 'all';
$search       = trim($_GET['search'] ?? '');

// Pagination
$perPage     = 10;
$currentPage = max(1, (int)($_GET['page'] ?? 1));
$offset      = ($currentPage - 1) * $perPage;

// Build query
$whereClauses = ["ja.seeker_id = ?"];
$params       = [$uid];

if ($statusFilter !== 'all') {
    $whereClauses[] = "ja.status = ?";
    $params[]       = $statusFilter;
}
if ($search !== '') {
    $whereClauses[] = "(j.title LIKE ? OR ep.company_name LIKE ?)";
    $params[]       = "%$search%";
    $params[]       = "%$search%";
}

$whereSQL = implode(' AND ', $whereClauses);

// Total count for pagination
$countStmt = $db->prepare("
    SELECT COUNT(*) FROM applications ja
    JOIN jobs j          ON ja.job_id     = j.job_id
    LEFT JOIN employer_profiles ep ON j.employer_id = ep.user_id
    WHERE $whereSQL
");
$countStmt->execute($params);
$totalFiltered = (int)$countStmt->fetchColumn();
$totalPages    = max(1, (int)ceil($totalFiltered / $perPage));

// Fetch paginated applications
$params[] = $perPage;
$params[] = $offset;

$stmt = $db->prepare("
    SELECT ja.app_id, j.job_id, j.title, j.location, j.salary_min, j.salary_max,
           COALESCE(ep.company_name, 'Unknown Company') AS company_name, jc.category_name, jc.icon_path,
           ja.status, ja.applied_at, ja.cover_letter
    FROM applications ja
    JOIN jobs j                 ON ja.job_id     = j.job_id
    LEFT JOIN employer_profiles ep   ON j.employer_id = ep.user_id
    LEFT JOIN job_categories jc ON j.category_id = jc.category_id
    WHERE $whereSQL
    ORDER BY ja.applied_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute($params);
$applications = $stmt->fetchAll();

include_once __DIR__ . '/../partials/header.php';
?>

<!-- Hero Banner -->
<section class="relative w-full overflow-hidden" style="height:280px;">
    <div class="absolute inset-0"
        style="background:linear-gradient(135deg,#8b91dd 0%,#10195d 70%,#10195d 100%);opacity:.92;"></div>
    <div class="absolute inset-0 flex flex-col items-center justify-center z-10">
        <h1 class="text-2xl md:text-3xl font-bold text-white mb-2 tracking-tight text-center">My Applications</h1>
        <p class="text-gray-200 text-sm md:text-base text-center max-w-2xl px-4">
            Track and manage all your job applications in one place
        </p>
    </div>
</section>

<!-- Main layout -->
<div class="max-w-7xl w-full mx-auto px-6 py-12 flex gap-10 items-start">

    <!-- Sidebar -->
    <div class="sticky top-20 self-start">
        <?php include_once __DIR__ . '/../partials/sidebar.php'; ?>
    </div>

    <!-- Page Content -->
    <main class="flex-1 min-w-0">

        <!-- Stats Row -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <a href="?status=all"
                class="bg-white rounded-lg border <?php echo $statusFilter === 'all' ? 'border-indigo-400 ring-2 ring-indigo-100' : 'border-slate-200'; ?> p-5 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-slate-600 text-sm font-medium">Total</p>
                        <p class="text-2xl font-bold text-slate-900 mt-1"><?php echo $totalApplications; ?></p>
                    </div>
                    <div class="bg-indigo-50 rounded-lg p-2">
                        <i data-lucide="file-text" class="w-5 h-5 text-indigo-600"></i>
                    </div>
                </div>
            </a>

            <a href="?status=pending"
                class="bg-white rounded-lg border <?php echo $statusFilter === 'pending' ? 'border-yellow-400 ring-2 ring-yellow-100' : 'border-slate-200'; ?> p-5 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-slate-600 text-sm font-medium">Pending</p>
                        <p class="text-2xl font-bold text-yellow-600 mt-1"><?php echo $pendingCount; ?></p>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-2">
                        <i data-lucide="clock" class="w-5 h-5 text-yellow-600"></i>
                    </div>
                </div>
            </a>

            <a href="?status=accepted"
                class="bg-white rounded-lg border <?php echo $statusFilter === 'accepted' ? 'border-green-400 ring-2 ring-green-100' : 'border-slate-200'; ?> p-5 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-slate-600 text-sm font-medium">Accepted</p>
                        <p class="text-2xl font-bold text-green-600 mt-1"><?php echo $acceptedCount; ?></p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-2">
                        <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                    </div>
                </div>
            </a>

            <a href="?status=rejected"
                class="bg-white rounded-lg border <?php echo $statusFilter === 'rejected' ? 'border-red-400 ring-2 ring-red-100' : 'border-slate-200'; ?> p-5 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-slate-600 text-sm font-medium">Rejected</p>
                        <p class="text-2xl font-bold text-red-600 mt-1"><?php echo $rejectedCount; ?></p>
                    </div>
                    <div class="bg-red-50 rounded-lg p-2">
                        <i data-lucide="x-circle" class="w-5 h-5 text-red-600"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- Search & Filter Bar -->
        <div class="bg-white rounded-lg border border-slate-200 px-5 py-4 mb-6 flex flex-col sm:flex-row gap-3 items-center">
            <form method="GET" class="flex flex-col sm:flex-row gap-3 w-full">
                <!-- Preserve active status filter -->
                <?php if ($statusFilter !== 'all'): ?>
                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($statusFilter); ?>">
                <?php endif; ?>

                <!-- Search -->
                <div class="relative flex-1">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                    <input
                        type="text"
                        name="search"
                        value="<?php echo htmlspecialchars($search); ?>"
                        placeholder="Search by job title or company…"
                        class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-sm
                               placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400
                               focus:border-transparent focus:bg-white transition duration-150">
                </div>

                <!-- Status dropdown -->
                <select name="status"
                    onchange="this.form.submit()"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-700 text-sm
                           focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent
                           focus:bg-white transition duration-150">
                    <option value="all" <?php echo $statusFilter === 'all'      ? 'selected' : ''; ?>>All Statuses</option>
                    <option value="pending" <?php echo $statusFilter === 'pending'  ? 'selected' : ''; ?>>Pending</option>
                    <option value="accepted" <?php echo $statusFilter === 'accepted' ? 'selected' : ''; ?>>Accepted</option>
                    <option value="rejected" <?php echo $statusFilter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>

                <button type="submit"
                    class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition duration-150
                           focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-400"
                    style="background-color:#8b91dd;"
                    onmouseover="this.style.backgroundColor='#7a7fd4'"
                    onmouseout="this.style.backgroundColor='#8b91dd'">
                    Search
                </button>

                <?php if ($search !== '' || $statusFilter !== 'all'): ?>
                    <a href="?"
                        class="px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-600 bg-slate-100
                              hover:bg-slate-200 transition duration-150 whitespace-nowrap text-center">
                        Clear
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Results count -->
        <p class="text-sm text-slate-500 mb-4">
            Showing <span class="font-medium text-slate-700"><?php echo count($applications); ?></span>
            of <span class="font-medium text-slate-700"><?php echo $totalFiltered; ?></span>
            application<?php echo $totalFiltered !== 1 ? 's' : ''; ?>
            <?php if ($statusFilter !== 'all'): ?>
                &mdash; <span class="font-medium"><?php echo ucfirst($statusFilter); ?></span>
            <?php endif; ?>
        </p>

        <!-- Applications List -->
        <?php if (count($applications) > 0): ?>
            <div class="space-y-4">
                <?php foreach ($applications as $app):
                    $salaryRange = '';
                    if ($app['salary_min'] && $app['salary_max']) {
                        $salaryRange = 'KES ' . number_format($app['salary_min']) . ' – ' . number_format($app['salary_max']);
                    }

                    $statusConfig = match ($app['status']) {
                        'pending'  => ['badge' => 'bg-yellow-50 text-yellow-700 border border-yellow-200', 'icon' => 'clock',        'label' => 'Pending'],
                        'accepted' => ['badge' => 'bg-green-50  text-green-700  border border-green-200',  'icon' => 'check-circle', 'label' => 'Accepted'],
                        'rejected' => ['badge' => 'bg-red-50    text-red-700    border border-red-200',    'icon' => 'x-circle',     'label' => 'Rejected'],
                        default    => ['badge' => 'bg-slate-50  text-slate-700  border border-slate-200',  'icon' => 'help-circle',  'label' => ucfirst($app['status'])],
                    };
                ?>
                    <div class="bg-white rounded-lg border border-slate-200 p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-start justify-between gap-4">

                            <!-- Left: job info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-3 flex-wrap mb-1">
                                    <h3 class="text-base font-semibold text-slate-900">
                                        <a href="/jobs/<?php echo $app['job_id']; ?>"
                                            class="hover:text-indigo-600 transition-colors">
                                            <?php echo htmlspecialchars($app['title']); ?>
                                        </a>
                                    </h3>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium <?php echo $statusConfig['badge']; ?>">
                                        <i data-lucide="<?php echo $statusConfig['icon']; ?>" class="w-3.5 h-3.5"></i>
                                        <?php echo $statusConfig['label']; ?>
                                    </span>
                                </div>

                                <p class="text-sm text-slate-600 mb-3">
                                    <?php echo htmlspecialchars($app['company_name']); ?>
                                </p>

                                <!-- Meta chips -->
                                <div class="flex flex-wrap gap-3 mb-3">
                                    <?php if ($app['location']): ?>
                                        <div class="flex items-center gap-1 text-xs text-slate-500">
                                            <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                                            <?php echo htmlspecialchars($app['location']); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($app['category_name']): ?>
                                        <div class="flex items-center gap-1 text-xs text-slate-500">
                                            <?php if ($app['icon_path']): ?>
                                                <img src="<?php echo htmlspecialchars($app['icon_path']); ?>" alt="" class="w-3.5 h-3.5">
                                            <?php else: ?>
                                                <i data-lucide="briefcase" class="w-3.5 h-3.5"></i>
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($app['category_name']); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($salaryRange): ?>
                                        <div class="flex items-center gap-1 text-xs text-green-600 font-medium">
                                            <i data-lucide="banknote" class="w-3.5 h-3.5"></i>
                                            <?php echo $salaryRange; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($app['cover_letter'])): ?>
                                    <p class="text-xs text-slate-500 line-clamp-2 italic">
                                        "<?php echo htmlspecialchars(substr($app['cover_letter'], 0, 160)); ?>…"
                                    </p>
                                <?php endif; ?>

                                <p class="text-xs text-slate-400 mt-2">
                                    Applied <?php echo date('M d, Y', strtotime($app['applied_at'])); ?>
                                </p>
                            </div>

                            <!-- Right: actions -->
                            <div class="flex flex-col gap-2 flex-shrink-0">
                                <a href="/jobs/<?php echo $app['job_id']; ?>"
                                    class="px-4 py-2 text-white text-sm font-medium rounded-lg
                                          hover:opacity-90 transition-colors text-center whitespace-nowrap"
                                    style="background-color:#8b91dd;">
                                    View Job
                                </a>
                                <?php if ($app['status'] === 'pending'): ?>
                                    <button
                                        onclick="withdrawApplication(<?php echo $app['app_id']; ?>)"
                                        class="px-4 py-2 bg-red-50 text-red-600 text-sm font-medium rounded-lg
                                               hover:bg-red-100 transition-colors whitespace-nowrap">
                                        Withdraw
                                    </button>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="flex justify-center items-center gap-2 mt-8">
                    <?php
                    $queryBase = http_build_query(array_filter([
                        'status' => $statusFilter !== 'all' ? $statusFilter : null,
                        'search' => $search ?: null,
                    ]));
                    $queryBase = $queryBase ? '&' . $queryBase : '';
                    ?>

                    <!-- Prev -->
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=<?php echo $currentPage - 1 . $queryBase; ?>"
                            class="px-3 py-2 rounded-lg border border-slate-200 text-slate-600 text-sm
                                  hover:bg-slate-50 transition-colors flex items-center gap-1">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i> Prev
                        </a>
                    <?php endif; ?>

                    <!-- Page numbers -->
                    <?php for ($p = max(1, $currentPage - 2); $p <= min($totalPages, $currentPage + 2); $p++): ?>
                        <a href="?page=<?php echo $p . $queryBase; ?>"
                            class="px-3.5 py-2 rounded-lg border text-sm font-medium transition-colors
                                  <?php echo $p === $currentPage
                                        ? 'border-indigo-400 text-white'
                                        : 'border-slate-200 text-slate-600 hover:bg-slate-50'; ?>"
                            <?php if ($p === $currentPage): ?>
                            style="background-color:#8b91dd;"
                            <?php endif; ?>>
                            <?php echo $p; ?>
                        </a>
                    <?php endfor; ?>

                    <!-- Next -->
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?php echo $currentPage + 1 . $queryBase; ?>"
                            class="px-3 py-2 rounded-lg border border-slate-200 text-slate-600 text-sm
                                  hover:bg-slate-50 transition-colors flex items-center gap-1">
                            Next <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- Empty State -->
            <div class="bg-white rounded-lg border border-slate-200 p-12 text-center">
                <i data-lucide="inbox" class="w-16 h-16 text-slate-300 mx-auto mb-4"></i>
                <?php if ($search !== '' || $statusFilter !== 'all'): ?>
                    <h3 class="text-lg font-semibold text-slate-900 mb-2">No Applications Found</h3>
                    <p class="text-slate-600 mb-6">No applications match your current filters. Try adjusting your search.</p>
                    <a href="?"
                        class="inline-block px-6 py-2 text-white text-sm font-medium rounded-lg hover:opacity-90 transition-colors"
                        style="background-color:#8b91dd;">
                        Clear Filters
                    </a>
                <?php else: ?>
                    <h3 class="text-lg font-semibold text-slate-900 mb-2">No Applications Yet</h3>
                    <p class="text-slate-600 mb-6">You haven't applied to any jobs yet. Start exploring opportunities!</p>
                    <a href="/jobs"
                        class="inline-block px-6 py-2 text-white text-sm font-medium rounded-lg hover:opacity-90 transition-colors"
                        style="background-color:#8b91dd;">
                        Browse Jobs
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </main>
</div><!-- end flex container -->

<?php include_once __DIR__ . '/../partials/footer.php'; ?>

<script>
    function withdrawApplication(appId) {
        Swal.fire({
            icon: 'warning',
            title: 'Withdraw Application?',
            text: 'This will permanently remove your application for this job.',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#8b91dd',
            confirmButtonText: 'Yes, Withdraw',
            cancelButtonText: 'Cancel',
            background: '#fff',
            customClass: {
                popup: 'rounded-2xl',
                title: 'text-lg font-semibold',
                confirmButton: 'px-6 py-2 text-sm',
                cancelButton: 'px-6 py-2 text-sm'
            }
        }).then((result) => {
            if (!result.isConfirmed) return;

            fetch('<?php echo BASE_URL; ?>/php/functions/withdraw_application.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'app_id=' + appId
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Withdrawn!',
                            text: 'Your application has been withdrawn.',
                            confirmButtonColor: '#8b91dd',
                            confirmButtonText: 'OK',
                            background: '#fff',
                            customClass: {
                                popup: 'rounded-2xl',
                                title: 'text-lg font-semibold',
                                confirmButton: 'px-6 py-2 text-sm'
                            }
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to withdraw application.',
                            confirmButtonColor: '#8b91dd',
                            confirmButtonText: 'OK',
                            background: '#fff',
                            customClass: {
                                popup: 'rounded-2xl',
                                title: 'text-lg font-semibold',
                                confirmButton: 'px-6 py-2 text-sm'
                            }
                        });
                    }
                })
                .catch(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An unexpected error occurred.',
                        confirmButtonColor: '#8b91dd',
                        confirmButtonText: 'OK',
                        background: '#fff',
                        customClass: {
                            popup: 'rounded-2xl',
                            title: 'text-lg font-semibold',
                            confirmButton: 'px-6 py-2 text-sm'
                        }
                    });
                });
        });
    }

    if (typeof lucide !== 'undefined') lucide.createIcons();
</script>