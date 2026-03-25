<?php
require_once __DIR__ . '/../../php/config/connection.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login');
    exit;
}

$db = getDB();
$stmt = $db->query("
    SELECT a.*, j.title as job_title, s.full_name as seeker_name, e.company_name
    FROM applications a
    JOIN jobs j ON a.job_id = j.job_id
    JOIN job_seeker_profiles s ON a.seeker_id = s.user_id
    JOIN employer_profiles e ON j.employer_id = e.user_id
    ORDER BY a.applied_at DESC
");
$applications = $stmt->fetchAll();

include_once __DIR__ . '/../partials/header.php';
?>

<section class="relative w-full overflow-hidden" style="height:120px;">
    <div class="absolute inset-0" style="background: #1e293b; opacity:0.95;"></div>
    <div class="absolute inset-0 flex items-center justify-center z-10">
        <h1 class="text-xl font-bold text-white tracking-tight">System Applications Monitor</h1>
    </div>
</section>

<div class="max-w-7xl w-full mx-auto px-6 py-12 flex flex-col lg:flex-row gap-10">
    <?php include_once __DIR__ . '/../partials/sidebar.php'; ?>
    <main class="flex-1">
        <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3 font-semibold text-slate-700">Candidate</th>
                            <th class="px-6 py-3 font-semibold text-slate-700">Job / Company</th>
                            <th class="px-6 py-3 font-semibold text-slate-700">Applied At</th>
                            <th class="px-6 py-3 font-semibold text-slate-700">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $app): 
                            $statusColor = match ($app['status']) {
                                'Pending' => 'bg-yellow-50 text-yellow-700',
                                'Hired' => 'bg-green-50 text-green-700',
                                'Rejected' => 'bg-red-50 text-red-700',
                                default => 'bg-blue-50 text-blue-700'
                            };
                        ?>
                            <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 font-medium text-slate-900"><?php echo htmlspecialchars($app['seeker_name']); ?></td>
                                <td class="px-6 py-4">
                                    <div class="text-slate-800"><?php echo htmlspecialchars($app['job_title']); ?></div>
                                    <div class="text-[10px] text-slate-500 uppercase"><?php echo htmlspecialchars($app['company_name']); ?></div>
                                </td>
                                <td class="px-6 py-4 text-slate-500"><?php echo date('M d, Y', strtotime($app['applied_at'])); ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase <?php echo $statusColor; ?>">
                                        <?php echo $app['status']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>
