<?php
require_once __DIR__ . '/../../php/config/connection.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login');
    exit;
}

$db = getDB();
$stmt = $db->query("
    SELECT u.user_id, u.email, u.status, u.created_at, e.company_name, e.industry
    FROM users u
    LEFT JOIN employer_profiles e ON u.user_id = e.user_id
    WHERE u.role = 'employer'
    ORDER BY u.created_at DESC
");
$employers = $stmt->fetchAll();

include_once __DIR__ . '/../partials/header.php';
?>

<section class="relative w-full overflow-hidden" style="height:280px;">
    <div class="absolute inset-0" style="background: #1e293b; opacity:0.95;"></div>
    <div class="absolute inset-0 flex items-center justify-center z-10">
        <h1 class="text-xl font-bold text-white tracking-tight">Manage Employers</h1>
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
                            <th class="px-6 py-3 font-semibold text-slate-700">Company / Email</th>
                            <th class="px-6 py-3 font-semibold text-slate-700">Industry</th>
                            <th class="px-6 py-3 font-semibold text-slate-700 text-center">Jobs</th>
                            <th class="px-6 py-3 font-semibold text-slate-700">Joined</th>
                            <th class="px-6 py-3 font-semibold text-slate-700 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employers as $e):
                            $jobStmt = $db->prepare("SELECT COUNT(*) FROM jobs WHERE employer_id = ?");
                            $jobStmt->execute([$e['user_id']]);
                            $jobCount = $jobStmt->fetchColumn();
                        ?>
                            <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-900"><?php echo htmlspecialchars($e['company_name'] ?? 'N/A'); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo htmlspecialchars($e['email']); ?></div>
                                </td>
                                <td class="px-6 py-4 text-slate-600 italic"><?php echo htmlspecialchars($e['industry'] ?? '-'); ?></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="font-bold text-slate-900"><?php echo $jobCount; ?></span>
                                </td>
                                <td class="px-6 py-4 text-slate-500"><?php echo date('M d, Y', strtotime($e['created_at'])); ?></td>
                                <td class="px-6 py-4 text-right">
                                    <button class="text-indigo-600 hover:underline">Edit</button>
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
