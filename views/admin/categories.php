<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../php/config/connection.php';

try {
    $db = getDB();

    // Fetch all categories with job counts
    $stmt = $db->query("SELECT c.category_id, c.category_name, c.description, c.created_at, COUNT(j.job_id) as job_count FROM job_categories c LEFT JOIN jobs j ON c.category_id = j.category_id GROUP BY c.category_id ORDER BY c.category_name ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error fetching categories: " . $e->getMessage());
    $categories = [];
}

$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

include_once 'views/partials/header.php';
?>

<main class="w-full">
    <!-- Hero Banner -->
    <section class="relative w-full overflow-hidden" style="height:220px;">
        <div class="absolute inset-0" style="background: linear-gradient(135deg, #8b91dd 0%, #10195d 70%, #10195d 100%); opacity:0.92;"></div>
        <div class="absolute inset-0 flex flex-col items-center justify-end pb-10 z-10 px-4">
            <h1 class="text-2xl md:text-3xl font-bold text-white mb-2 tracking-tight">Job Categories</h1>
            <p class="text-gray-300 text-sm">Manage job categories across the platform</p>
        </div>
    </section>

    <div class="min-h-screen py-12 px-4 bg-gray-50">
        <div class="max-w-5xl mx-auto">

            <!-- Success/Error Messages -->
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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Add New Category Form (Sidebar) -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow border border-gray-200 sticky top-20">
                        <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
                            <h2 class="text-lg font-bold text-gray-900">Add Category</h2>
                        </div>
                        <form method="POST" action="/admin/categories" class="px-6 py-6 space-y-4">
                            <input type="hidden" name="action" value="create">

                            <div>
                                <label class="block text-sm font-semibold text-gray-900 mb-2">
                                    Category Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="category_name" required placeholder="e.g. Frontend Development"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#fb236a] focus:border-transparent text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-900 mb-2">
                                    Description <span class="text-gray-400 font-normal text-xs">(optional)</span>
                                </label>
                                <textarea name="description" rows="3" placeholder="Brief description of this job category..."
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#fb236a] focus:border-transparent text-sm resize-none"></textarea>
                            </div>

                            <button type="submit"
                                class="w-full px-4 py-2.5 bg-[#fb236a] hover:bg-[#e01060] text-white font-semibold rounded-lg transition-colors flex items-center justify-center gap-2">
                                <i data-lucide="plus" class="w-4 h-4"></i>
                                Add Category
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Categories List -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
                        <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
                            <h2 class="text-lg font-bold text-gray-900">All Categories (<?php echo count($categories); ?>)</h2>
                        </div>

                        <?php if (empty($categories)): ?>
                            <div class="px-6 py-12 text-center">
                                <i data-lucide="inbox" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                                <p class="text-gray-500">No categories yet. Create one to get started.</p>
                            </div>
                        <?php else: ?>
                            <div class="divide-y divide-gray-200">
                                <?php foreach ($categories as $category): ?>
                                    <div class="px-6 py-5 hover:bg-gray-50 transition-colors">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="flex-1 min-w-0">
                                                <h3 class="text-sm font-bold text-gray-900">
                                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                                </h3>
                                                <?php if ($category['description']): ?>
                                                    <p class="text-xs text-gray-600 mt-1 line-clamp-2">
                                                        <?php echo htmlspecialchars($category['description']); ?>
                                                    </p>
                                                <?php endif; ?>
                                                <div class="flex gap-3 mt-2 text-xs text-gray-500">
                                                    <span>
                                                        <i data-lucide="briefcase" class="w-3.5 h-3.5 inline mr-1 align-text-bottom"></i>
                                                        <?php echo (int)$category['job_count']; ?> job<?php echo $category['job_count'] !== 1 ? 's' : ''; ?>
                                                    </span>
                                                    <span>
                                                        <i data-lucide="calendar" class="w-3.5 h-3.5 inline mr-1 align-text-bottom"></i>
                                                        <?php echo date('M d, Y', strtotime($category['created_at'])); ?>
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-2 shrink-0">
                                                <button type="button"
                                                    onclick="openEditModal(<?php echo (int)$category['category_id']; ?>, '<?php echo htmlspecialchars(addslashes($category['category_name'])); ?>', '<?php echo htmlspecialchars(addslashes($category['description'])); ?>')"
                                                    class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors" title="Edit">
                                                    <i data-lucide="edit-2" class="w-4 h-4"></i>
                                                </button>

                                                <?php if ((int)$category['job_count'] === 0): ?>
                                                    <form method="POST" action="/admin/categories" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="category_id" value="<?php echo (int)$category['category_id']; ?>">
                                                        <button type="submit" class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors" title="Delete">
                                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <button type="button" disabled class="p-2 text-gray-300 cursor-not-allowed" title="Cannot delete - has jobs">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full">
            <div class="px-6 py-5 border-b border-gray-200 flex items-center justify-between bg-gray-50">
                <h2 class="text-lg font-bold text-gray-900">Edit Category</h2>
                <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form method="POST" action="/admin/categories" class="px-6 py-6 space-y-4">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="category_id" id="editCategoryId">

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">
                        Category Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="category_name" id="editCategoryName" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#fb236a] focus:border-transparent text-sm">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">
                        Description <span class="text-gray-400 font-normal text-xs">(optional)</span>
                    </label>
                    <textarea name="description" id="editCategoryDesc" rows="3"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#fb236a] focus:border-transparent text-sm resize-none"></textarea>
                </div>

                <div class="flex gap-3 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeEditModal()"
                        class="flex-1 px-4 py-2.5 text-gray-700 bg-gray-100 hover:bg-gray-200 font-medium rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2.5 bg-[#fb236a] hover:bg-[#e01060] text-white font-medium rounded-lg transition-colors">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
    function openEditModal(id, name, desc) {
        document.getElementById('editCategoryId').value = id;
        document.getElementById('editCategoryName').value = name;
        document.getElementById('editCategoryDesc').value = desc;
        document.getElementById('editModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    // Close modal when clicking outside
    document.getElementById('editModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeEditModal();
        }
    });
</script>

<?php include_once 'views/partials/footer.php'; ?>
