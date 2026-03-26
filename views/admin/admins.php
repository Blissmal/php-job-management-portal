<?php
require_once __DIR__ . '/../../php/config/connection.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login');
    exit;
}

$db = getDB();
$stmt = $db->query("
    SELECT user_id, email, status, created_at
    FROM users
    WHERE role = 'admin'
    ORDER BY created_at DESC
");
$admins = $stmt->fetchAll();

include_once __DIR__ . '/../partials/header.php';
?>

<section class="relative w-full overflow-hidden" style="height:280px;">
    <div class="absolute inset-0" style="background: #1e293b; opacity:0.95;"></div>
    <div class="absolute inset-0 flex items-center justify-center z-10">
        <h1 class="text-xl font-bold text-white tracking-tight">System Administrators</h1>
    </div>
</section>

<div class="max-w-7xl w-full mx-auto px-6 py-12 flex flex-col lg:flex-row gap-10">
    <?php include_once __DIR__ . '/../partials/sidebar.php'; ?>
    <main class="flex-1">
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="mb-4 p-3 bg-green-50 text-green-700 border border-green-200 rounded">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-4 p-3 bg-red-50 text-red-700 border border-red-200 rounded">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-sm">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Platform Admins</h2>
                <button onclick="toggleModal('addAdminModal')" class="bg-slate-900 text-white px-3 py-1.5 rounded text-xs font-bold hover:bg-slate-800 transition-colors">Add Admin</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3 font-semibold text-slate-700">Admin Email</th>
                            <th class="px-6 py-3 font-semibold text-slate-700">Status</th>
                            <th class="px-6 py-3 font-semibold text-slate-700">Created At</th>
                            <th class="px-6 py-3 font-semibold text-slate-700 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admins as $a): ?>
                            <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-900"><?php echo htmlspecialchars($a['email']); ?></div>
                                    <?php if ((int)$a['user_id'] === (int)$_SESSION['user_id']): ?>
                                        <span class="text-[10px] bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded font-bold uppercase">You</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase <?php echo $a['status'] === 'active' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'; ?>">
                                        <?php echo $a['status']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-500"><?php echo date('M d, Y', strtotime($a['created_at'])); ?></td>
                                <td class="px-6 py-4 text-right">
                                    <?php if ((int)$a['user_id'] !== (int)$_SESSION['user_id']): ?>
                                        <?php if ($a['status'] === 'active'): ?>
                                            <form action="/admin/add-admin" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to revoke this admin?');">
                                                <input type="hidden" name="action" value="revoke_admin">
                                                <input type="hidden" name="target_user_id" value="<?php echo $a['user_id']; ?>">
                                                <button type="submit" class="text-red-600 hover:underline">Revoke</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-slate-400 italic text-xs">Revoked</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-slate-400 italic text-xs">Primary</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<div id="addAdminModal" class="fixed inset-0 z-50 hidden bg-slate-900 bg-opacity-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
        <h3 class="text-lg font-bold text-slate-900 mb-4">Create New Admin</h3>
        
        <form action="/admin/add-admin" method="POST">
            <input type="hidden" name="action" value="create_admin">
            
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                <input type="email" id="email" name="email" required class="w-full px-3 py-2 border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-slate-900">
            </div>
            
            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                <input type="password" id="password" name="password" required minlength="8" class="w-full px-3 py-2 border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-slate-900">
            </div>
            
            <div class="flex justify-end gap-3">
                <button type="button" onclick="toggleModal('addAdminModal')" class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">Cancel</button>
                <button type="submit" class="bg-slate-900 text-white px-4 py-2 rounded text-sm font-bold hover:bg-slate-800 transition-colors">Create Admin</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleModal(modalID) {
        document.getElementById(modalID).classList.toggle("hidden");
    }
</script>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>