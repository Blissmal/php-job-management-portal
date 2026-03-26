<?php
require_once __DIR__ . '/../../php/config/connection.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Authentication and Role Check
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

// Handle Flash Messages
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

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

        <?php if ($success): ?>
            <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4 flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5 text-green-600 shrink-0"></i>
                <p class="text-sm text-green-800"><?php echo htmlspecialchars($success); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4 flex items-center gap-3">
                <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 shrink-0"></i>
                <p class="text-sm text-red-800"><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

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
                        <?php if (empty($seekers)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500">No job seekers found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($seekers as $s): ?>
                                <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-slate-900"><?php echo htmlspecialchars($s['full_name'] ?? 'N/A'); ?></div>
                                        <div class="text-xs text-slate-500"><?php echo htmlspecialchars($s['email']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 italic"><?php echo htmlspecialchars($s['headline'] ?? '-'); ?></td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase <?php echo $s['status'] === 'active' ? 'bg-green-50 text-green-700' : ($s['status'] === 'banned' ? 'bg-red-50 text-red-700' : 'bg-yellow-50 text-yellow-700'); ?>">
                                            <?php echo htmlspecialchars($s['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-500"><?php echo date('M d, Y', strtotime($s['created_at'])); ?></td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-3">
                                            <button type="button" 
                                                onclick="openEditModal(<?php echo (int)$s['user_id']; ?>, '<?php echo htmlspecialchars(addslashes($s['email'])); ?>', '<?php echo htmlspecialchars(addslashes($s['status'])); ?>')" 
                                                class="text-indigo-600 hover:text-indigo-800 transition-colors" title="Edit Status">
                                                <i data-lucide="edit-2" class="w-4 h-4"></i>
                                            </button>

                                            <form method="POST" action="/admin/seekers" class="inline" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="user_id" value="<?php echo (int)$s['user_id']; ?>">
                                                <button type="submit" class="text-red-500 hover:text-red-700 transition-colors" title="Delete User">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-lg max-w-md w-full overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200 flex items-center justify-between bg-slate-50">
            <h2 class="text-lg font-bold text-slate-900">Edit User Status</h2>
            <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form method="POST" action="/admin/seekers" class="px-6 py-6 space-y-4">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="user_id" id="editUserId">

            <div>
                <label class="block text-sm font-semibold text-slate-900 mb-2">Account Email</label>
                <input type="text" id="editUserEmail" readonly 
                    class="w-full px-4 py-2.5 border border-slate-200 rounded-lg bg-slate-50 text-slate-500 cursor-not-allowed focus:outline-none text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-900 mb-2">Account Status</label>
                <select name="status" id="editUserStatus" required
                    class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="banned">Banned</option>
                </select>
            </div>

            <div class="flex gap-3 pt-4 mt-2">
                <button type="button" onclick="closeEditModal()"
                    class="flex-1 px-4 py-2.5 text-slate-700 bg-slate-100 hover:bg-slate-200 font-medium rounded-lg transition-colors">
                    Cancel
                </button>
                <button type="submit"
                    class="flex-1 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, email, status) {
        document.getElementById('editUserId').value = id;
        document.getElementById('editUserEmail').value = email;
        document.getElementById('editUserStatus').value = status;
        document.getElementById('editModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    // Close modal when clicking on the dark backdrop
    document.getElementById('editModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeEditModal();
        }
    });
</script>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>