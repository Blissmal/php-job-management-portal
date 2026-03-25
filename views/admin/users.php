<?php
require_once __DIR__ . '/../../php/config/connection.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login');
    exit;
}

$db = getDB();
$stmt = $db->query("
    SELECT u.user_id, u.email, u.status, u.created_at, p.full_name, p.headline
    FROM users u
    LEFT JOIN job_seeker_profiles p ON u.user_id = p.user_id
    WHERE u.role = 'seeker'
    ORDER BY u.created_at DESC
");
$seekers = $stmt->fetchAll();

include_once __DIR__ . '/../partials/header.php';
?>

<section class="relative w-full overflow-hidden" style="height:280px;">
    <div class="absolute inset-0" style="background: #1e293b; opacity:0.95;"></div>
    <div class="absolute inset-0 flex items-center justify-center z-10">
        <h1 class="text-xl font-bold text-white tracking-tight">Manage Job Seekers</h1>
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
                            <th class="px-6 py-3 font-semibold text-slate-700">Name / Email</th>
                            <th class="px-6 py-3 font-semibold text-slate-700">Headline</th>
                            <th class="px-6 py-3 font-semibold text-slate-700">Status</th>
                            <th class="px-6 py-3 font-semibold text-slate-700">Joined</th>
                            <th class="px-6 py-3 font-semibold text-slate-700 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($seekers as $s): ?>
                            <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-900"><?php echo htmlspecialchars($s['full_name'] ?? 'N/A'); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo htmlspecialchars($s['email']); ?></div>
                                </td>
                                <td class="px-6 py-4 text-slate-600 italic"><?php echo htmlspecialchars($s['headline'] ?? '-'); ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase <?php echo $s['status'] === 'active' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'; ?>">
                                        <?php echo $s['status']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-500"><?php echo date('M d, Y', strtotime($s['created_at'])); ?></td>
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
