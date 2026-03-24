<?php include_once 'partials/header.php'; ?>

<main class="w-full">
    <section class="relative w-full overflow-hidden" style="height:280px;">
        <div class="absolute inset-0" style="background: linear-gradient(135deg, #8b91dd 0%, #10195d 70%, #10195d 100%); opacity:0.92;"></div>
        <div class="absolute inset-0 flex flex-col items-center justify-end pb-10 z-10">
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-3 tracking-tight">Categories</h1>
            <p class="text-gray-300 text-sm">
                <a href="/" class="underline underline-offset-2 hover:text-white transition-colors">Home</a>
                <span class="mx-2 opacity-50">—</span>
                <span class="text-white">Categories</span>
            </p>
        </div>
    </section>

    <?php
    // ── Live Data ─────────────────────────────────────────────────────────────────
    require_once 'php/config/connection.php';

    try {
        $db = getDB();

        // All categories with open job counts, ordered by count desc
        $stmt = $db->query("
            SELECT jc.category_id,
                   jc.category_name            AS name,
                   COALESCE(jc.icon_path, 'briefcase') AS icon,
                   COUNT(CASE WHEN j.status = 'open' THEN 1 END) AS count
            FROM job_categories jc
            LEFT JOIN jobs j ON j.category_id = jc.category_id
            GROUP BY jc.category_id, jc.category_name, jc.icon_path
            ORDER BY count DESC
        ");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $total_open = (int) $db->query("SELECT COUNT(*) FROM jobs WHERE status = 'open'")->fetchColumn();

    } catch (Exception $e) {
        $categories = [];
        $total_open = 0;
    }
    ?>

    <!-- ═══════════════════════════════════════════════════════════════
     SECTION – All Categories
════════════════════════════════════════════════════════════════ -->
    <section class="bg-white py-16 px-4">
        <div class="max-w-6xl mx-auto">

            <!-- Page header -->
            <div class="text-center my-12">
                <h2 class="text-3xl font-bold text-gray-800 mb-2" style="font-family:'Montserrat',sans-serif;">
                    Browse by Category
                </h2>
                <p class="text-gray-500 text-sm">
                    <?php echo number_format($total_open); ?> open position<?php echo $total_open !== 1 ? 's' : ''; ?> across <?php echo count($categories); ?> categories.
                </p>
            </div>

            <?php if (empty($categories)): ?>
                <div class="text-center py-20">
                    <i data-lucide="folder-open" class="w-12 h-12 text-gray-300 mx-auto mb-4"></i>
                    <p class="text-gray-400">No categories found.</p>
                </div>
            <?php else: ?>
                <!-- Grid -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-0 border border-gray-200">
                    <?php foreach ($categories as $index => $cat):
                        $isLeft   = $index % 4 === 0;
                        $isTop    = $index < 4;
                    ?>
                        <a href="/jobs?category=<?php echo urlencode($cat['name']); ?>"
                            class="group flex flex-col items-center justify-center text-center aspect-square p-8
                              transition-all duration-200 hover:shadow-lg hover:z-10
                              <?php echo !$isTop  ? 'border-t' : ''; ?>
                              <?php echo !$isLeft ? 'border-l' : ''; ?>
                              border-gray-200">

                            <i data-lucide="<?php echo htmlspecialchars($cat['icon']); ?>"
                                class="w-10 h-10 text-[#858bbe] mb-4 transition-transform duration-200 group-hover:scale-110"
                                stroke-width="1.5"></i>

                            <h3 class="text-sm font-bold text-gray-800 mb-1"><?php echo htmlspecialchars($cat['name']); ?></h3>
                            <p class="text-xs text-[#fb236a] font-medium">
                                <?php echo (int)$cat['count']; ?> open position<?php echo (int)$cat['count'] !== 1 ? 's' : ''; ?>
                            </p>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </section>

</main>

<?php include_once 'partials/footer.php'; ?>