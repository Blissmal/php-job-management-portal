<?php
require_once __DIR__ . '/../../php/config/connection.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login');
    exit;
}

$db = getDB();
$stmt = $db->query("
    SELECT j.*, e.company_name, c.category_name
    FROM jobs j
    LEFT JOIN employer_profiles e ON j.employer_id = e.user_id
    LEFT JOIN job_categories c ON j.category_id = c.category_id
    ORDER BY j.created_at DESC
");
$jobs = $stmt->fetchAll();

include_once __DIR__ . '/../partials/header.php';
?>

<section class="relative w-full overflow-hidden" style="height:280px;">
    <div class="absolute inset-0" style="background: #1e293b; opacity:0.95;"></div>
    <div class="absolute inset-0 flex items-center justify-center z-10">
        <h1 class="text-xl font-bold text-white tracking-tight">System-wide Job Listings</h1>
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
                            <th class="px-6 py-3 font-semibold text-slate-700">Job Title</th>
                            <th class="px-6 py-3 font-semibold text-slate-700">Employer</th>
                            <th class="px-6 py-3 font-semibold text-slate-700">Category</th>
                            <th class="px-6 py-3 font-semibold text-slate-700">Status</th>
                            <!-- <th class="px-6 py-3 font-semibold text-slate-700 text-right">Actions</th> -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jobs as $j): ?>
                            <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-900"><?php echo htmlspecialchars($j['title']); ?></div>
                                    <div class="text-[10px] text-slate-500 uppercase"><?php echo htmlspecialchars($j['location']); ?></div>
                                </td>
                                <td class="px-6 py-4 text-slate-600"><?php echo htmlspecialchars($j['company_name'] ?? 'Unknown'); ?></td>
                                <td class="px-6 py-4">
                                    <span class="text-xs bg-slate-100 px-2 py-0.5 rounded text-slate-700"><?php echo htmlspecialchars($j['category_name'] ?? 'Other'); ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase <?php echo $j['status'] === 'open' ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-700'; ?>">
                                        <?php echo $j['status']; ?>
                                    </span>
                                </td>
                                <!-- <td class="px-6 py-4 text-right">
                                    <button class="text-red-600 hover:underline">Remove</button>
                                </td> -->
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>
