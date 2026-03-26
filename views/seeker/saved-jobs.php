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

// Fetch saved jobs
$stmt = $db->prepare("
    SELECT sj.save_id, j.job_id, j.title, j.description, j.salary_min, j.salary_max,
           COALESCE(ep.company_name, 'Unknown Company') AS company_name, jc.category_name, jc.icon_path, j.location, j.created_at, sj.saved_at
    FROM saved_jobs sj
    JOIN jobs j ON sj.job_id = j.job_id
    LEFT JOIN employer_profiles ep ON j.employer_id = ep.user_id
    LEFT JOIN job_categories jc ON j.category_id = jc.category_id
    WHERE sj.seeker_id = ?
    ORDER BY sj.saved_at DESC
");
$stmt->execute([$uid]);
$savedJobs = $stmt->fetchAll();

include_once __DIR__ . '/../partials/header.php';
?>

<!-- Hero Banner -->
<section class="relative w-full overflow-hidden" style="height:280px;">
    <div class="absolute inset-0" style="background: linear-gradient(135deg, #8b91dd 0%, #10195d 70%, #10195d 100%); opacity:0.92;"></div>
    <div class="absolute inset-0 flex flex-col items-center justify-center z-10">
        <h1 class="text-2xl md:text-3xl font-bold text-white mb-2 tracking-tight text-center">Saved Jobs</h1>
        <p class="text-gray-200 text-sm md:text-base text-center max-w-2xl px-4">Your collection of interesting job opportunities</p>
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
        <!-- Header with count -->
        <div class="mb-6">
            <h2 class="text-xl font-bold text-slate-900">Saved Jobs</h2>
            <p class="text-slate-600 text-sm mt-1"><?php echo count($savedJobs); ?> job<?php echo count($savedJobs) !== 1 ? 's' : ''; ?> saved</p>
        </div>

        <?php if (count($savedJobs) > 0): ?>
            <!-- Jobs Grid -->
            <div class="space-y-4">
                <?php foreach ($savedJobs as $job):
                    $salaryRange = '';
                    if ($job['salary_min'] && $job['salary_max']) {
                        $salaryRange = 'KES ' . number_format($job['salary_min']) . ' - ' . number_format($job['salary_max']);
                    }
                ?>
                    <div class="bg-white rounded-lg border border-slate-200 p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <!-- Job Title and Company -->
                                <div class="mb-3">
                                    <h3 class="text-lg font-semibold text-slate-900 line-clamp-2">
                                        <a href="/jobs/<?php echo $job['job_id']; ?>" class="hover:text-indigo-600 transition-colors">
                                            <?php echo htmlspecialchars($job['title']); ?>
                                        </a>
                                    </h3>
                                    <p class="text-sm text-slate-600 mt-1">
                                        <a href="#" class="hover:text-indigo-600">
                                            <?php echo htmlspecialchars($job['company_name']); ?>
                                        </a>
                                    </p>
                                </div>

                                <!-- Description -->
                                <p class="text-sm text-slate-600 line-clamp-2 mb-3">
                                    <?php echo htmlspecialchars(substr($job['description'], 0, 200)); ?>...
                                </p>

                                <!-- Meta Info -->
                                <div class="flex flex-wrap gap-4 mb-4">
                                    <?php if ($job['category_name']): ?>
                                        <div class="flex items-center gap-1 text-sm text-slate-600">
                                            <?php if ($job['icon_path']): ?>
                                                <img src="<?php echo htmlspecialchars($job['icon_path']); ?>" alt="" class="w-4 h-4">
                                            <?php else: ?>
                                                <i data-lucide="briefcase" class="w-4 h-4"></i>
                                            <?php endif; ?>
                                            <span><?php echo htmlspecialchars($job['category_name']); ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($job['location']): ?>
                                        <div class="flex items-center gap-1 text-sm text-slate-600">
                                            <i data-lucide="map-pin" class="w-4 h-4"></i>
                                            <span><?php echo htmlspecialchars($job['location']); ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($salaryRange): ?>
                                        <div class="flex items-center gap-1 text-sm text-green-600 font-medium">
                                            <i data-lucide="dollar-sign" class="w-4 h-4"></i>
                                            <span><?php echo $salaryRange; ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Saved Date -->
                                <p class="text-xs text-slate-500">
                                    Saved on <?php echo date('M d, Y', strtotime($job['saved_at'])); ?>
                                </p>
                            </div>

                            <!-- Actions -->
                            <div class="flex flex-col gap-2 flex-shrink-0">
                                <a
                                    href="/jobs/<?php echo $job['job_id']; ?>"
                                    class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors text-center whitespace-nowrap">
                                    View Details
                                </a>
                                <button
                                    onclick="removeSavedJob(<?php echo $job['save_id']; ?>)"
                                    class="px-4 py-2 bg-red-50 text-red-600 text-sm font-medium rounded-lg hover:bg-red-100 transition-colors whitespace-nowrap">
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="bg-white rounded-lg border border-slate-200 p-12 text-center">
                <i data-lucide="bookmark" class="w-16 h-16 text-slate-300 mx-auto mb-4"></i>
                <h3 class="text-lg font-semibold text-slate-900 mb-2">No Saved Jobs Yet</h3>
                <p class="text-slate-600 mb-6">Start exploring and saving jobs that interest you to keep them in one place.</p>
                <a
                    href="/jobs"
                    class="inline-block px-6 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                    Browse Jobs
                </a>
            </div>
        <?php endif; ?>
    </main>

</div><!-- end flex container -->

<?php include_once __DIR__ . '/../partials/footer.php'; ?>

<script>
    function removeSavedJob(savedId) {
        if (!confirm('Are you sure you want to remove this saved job?')) {
            return;
        }

        fetch('<?php echo BASE_URL; ?>/php/functions/remove_saved_job.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'save_id=' + savedId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Removed!',
                        text: 'Job removed from your saved jobs',
                        confirmButtonColor: '#8b91dd',
                        confirmButtonText: 'OK',
                        background: '#fff',
                        customClass: {
                            popup: 'rounded-2xl',
                            title: 'text-lg font-semibold',
                            confirmButton: 'px-6 py-2 text-sm'
                        }
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to remove job',
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
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while removing the job',
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
    }

    // Initialize lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>