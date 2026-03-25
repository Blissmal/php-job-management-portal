<?php include_once 'partials/header.php'; ?>

<main class="w-full">
    <section class="relative w-full overflow-hidden" style="height:280px;">
        <div class="absolute inset-0" style="background: linear-gradient(135deg, #8b91dd 0%, #10195d 70%, #10195d 100%); opacity:0.92;"></div>
        <div class="absolute inset-0 flex flex-col items-center justify-end pb-10 z-10">
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-3 tracking-tight">Jobs</h1>
            <p class="text-gray-300 text-sm">
                <a href="/" class="underline underline-offset-2 hover:text-white transition-colors">Home</a>
                <span class="mx-2 opacity-50">—</span>
                <span class="text-white">Jobs</span>
            </p>
        </div>
    </section>

    <?php
    // ── Live Data ─────────────────────────────────────────────────────────────────
    require_once 'php/config/connection.php';

    // ── Read & sanitise filters from GET ──────────────────────────────────────────
    $keyword      = trim($_GET['keyword']  ?? '');
    $location     = trim($_GET['location'] ?? '');
    $active_types = isset($_GET['type']) && is_array($_GET['type']) ? $_GET['type'] : [];
    $active_date  = $_GET['date']  ?? 'all';
    $category_id  = (int)($_GET['category'] ?? 0);
    $current_page = max(1, (int)($_GET['page'] ?? 1));
    $per_page     = 10;

    // Date filter map
    $date_filters = [
        ['label' => 'Last Hour',     'value' => '1h'],
        ['label' => 'Last 24 Hours', 'value' => '24h'],
        ['label' => 'Last 7 Days',   'value' => '7d'],
        ['label' => 'Last 14 Days',  'value' => '14d'],
        ['label' => 'Last 30 Days',  'value' => '30d'],
        ['label' => 'All',           'value' => 'all', 'default' => true],
    ];
    $date_interval_map = [
        '1h'  => '1 HOUR',
        '24h' => '24 HOUR',
        '7d'  => '7 DAY',
        '14d' => '14 DAY',
        '30d' => '30 DAY',
    ];

    try {
        $db = getDB();

        // ── Build WHERE dynamically ───────────────────────────────────────────────
        $where  = ["j.status = 'open'"];
        $params = [];

        if ($keyword !== '') {
            $where[]  = "(j.title LIKE ? OR j.description LIKE ? OR ep.company_name LIKE ?)";
            $kw       = '%' . $keyword . '%';
            array_push($params, $kw, $kw, $kw);
        }
        if ($location !== '') {
            $where[]  = "j.location LIKE ?";
            $params[] = '%' . $location . '%';
        }
        if (!empty($active_types)) {
            $ph      = implode(',', array_fill(0, count($active_types), '?'));
            $where[] = "j.job_type IN ($ph)";
            array_push($params, ...$active_types);
        }
        if ($category_id > 0) {
            $where[]  = "j.category_id = ?";
            $params[] = $category_id;
        }
        if (isset($date_interval_map[$active_date])) {
            $interval = $date_interval_map[$active_date];
            $where[]  = "j.created_at >= DATE_SUB(NOW(), INTERVAL $interval)";
        }

        $where_sql = implode(' AND ', $where);

        // ── Total count ───────────────────────────────────────────────────────────
        $count_stmt = $db->prepare("
            SELECT COUNT(*)
            FROM jobs j
            JOIN employer_profiles ep ON j.employer_id = ep.user_id
            WHERE $where_sql
        ");
        $count_stmt->execute($params);
        $total_jobs  = (int) $count_stmt->fetchColumn();
        $total_pages = max(1, (int) ceil($total_jobs / $per_page));
        $offset      = ($current_page - 1) * $per_page;

        // ── Fetch paginated jobs ──────────────────────────────────────────────────
        $stmt = $db->prepare("
            SELECT j.job_id,
                   j.title,
                   j.job_type    AS type,
                   j.location,
                   j.featured,
                   ep.company_name AS company,
                   CASE
                       WHEN j.created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                           THEN 'just now'
                       WHEN j.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                           THEN CONCAT(HOUR(TIMEDIFF(NOW(), j.created_at)), ' hours ago')
                       WHEN j.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                           THEN CONCAT(DATEDIFF(NOW(), j.created_at), ' days ago')
                       ELSE DATE_FORMAT(j.created_at, '%M %e')
                   END AS posted
            FROM jobs j
            JOIN employer_profiles ep ON j.employer_id = ep.user_id
            WHERE $where_sql
            ORDER BY j.featured DESC, j.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute(array_merge($params, [$per_page, $offset]));
        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ── Job type counts for sidebar (from all open jobs, not filtered) ────────
        $type_stmt = $db->query("
            SELECT job_type AS label, COUNT(*) AS count
            FROM jobs
            WHERE status = 'open'
            GROUP BY job_type
            ORDER BY count DESC
        ");
        $job_types = $type_stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        $jobs        = [];
        $job_types   = [];
        $total_jobs  = 0;
        $total_pages = 1;
        $offset      = 0;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────────
    function companyLogoStyle(string $name): array
    {
        $palettes = [
            ['bg' => '#e8f4fd', 'color' => '#1a6fb5'],
            ['bg' => '#e8f5e9', 'color' => '#2e7d32'],
            ['bg' => '#f0f4ff', 'color' => '#3b5bdb'],
            ['bg' => '#fff3e0', 'color' => '#e65100'],
            ['bg' => '#e8f0fe', 'color' => '#1a73e8'],
            ['bg' => '#fce4ec', 'color' => '#c62828'],
            ['bg' => '#ede7f6', 'color' => '#4527a0'],
            ['bg' => '#e0f7fa', 'color' => '#00695c'],
        ];
        return $palettes[abs(crc32($name)) % count($palettes)];
    }

    function typeBadgeClass(string $type): string
    {
        return match ($type) {
            'Internship' => 'border-[#2b9a66] text-[#2b9a66]',
            'Temporary'  => 'border-[#c05621] text-[#c05621]',
            'Remote'     => 'border-[#6d28d9] text-[#6d28d9]',
            'Freelance'  => 'border-[#b45309] text-[#b45309]',
            'Part Time'  => 'border-[#0369a1] text-[#0369a1]',
            default      => 'border-[#2b7a78] text-[#2b7a78]',
        };
    }

    function typeDotColor(string $type): string
    {
        return match ($type) {
            'Freelance'  => '#ef4444',
            'Full Time'  => '#3b82f6',
            'Internship' => '#10b981',
            'Part Time'  => '#8b5cf6',
            'Remote'     => '#06b6d4',
            'Temporary'  => '#f59e0b',
            default      => '#6b7280',
        };
    }

    // Pagination helper: preserves all active filters
    function pageUrl(int $p): string
    {
        $params         = $_GET;
        $params['page'] = $p;
        return '/jobs?' . http_build_query($params);
    }
    ?>

    <!-- Jobs body -->
    <div class="min-h-screen py-8 px-4">
        <div class="max-w-6xl mx-auto flex gap-6 items-start">

            <!-- ── Sidebar ──────────────────────────────────────────────── -->
            <form id="filterForm" method="GET" action="/jobs">
                <aside class="w-56 shrink-0 sticky top-24 flex flex-col gap-4">

                    <!-- Location -->
                    <div class="bg-white rounded-lg border border-gray-200 px-4 py-3 flex items-center gap-2 shadow-sm">
                        <input
                            type="text"
                            name="location"
                            placeholder="All Locations"
                            value="<?php echo htmlspecialchars($location); ?>"
                            class="flex-1 text-sm text-gray-600 placeholder-gray-400 focus:outline-none bg-transparent">
                        <i data-lucide="map-pin" class="w-4 h-4 text-[#fb236a] shrink-0"></i>
                    </div>

                    <!-- Keyword -->
                    <div class="bg-white rounded-lg border border-gray-200 px-4 py-3 flex items-center gap-2 shadow-sm">
                        <input
                            type="text"
                            name="keyword"
                            placeholder="Job title, keywords..."
                            value="<?php echo htmlspecialchars($keyword); ?>"
                            class="flex-1 text-sm text-gray-600 placeholder-gray-400 focus:outline-none bg-transparent">
                        <i data-lucide="keyboard" class="w-4 h-4 text-[#fb236a] shrink-0"></i>
                    </div>

                    <!-- Date Posted -->
                    <div class="bg-white rounded-lg border border-gray-200 px-4 py-4 shadow-sm">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wide">Date Posted</h3>
                        </div>
                        <div class="flex flex-col gap-2.5">
                            <?php foreach ($date_filters as $df):
                                $checked = $active_date === $df['value'];
                            ?>
                                <label class="flex items-center gap-2.5 cursor-pointer group">
                                    <input
                                        type="radio"
                                        name="date"
                                        value="<?php echo $df['value']; ?>"
                                        <?php echo $checked ? 'checked' : ''; ?>
                                        class="appearance-none w-4 h-4 rounded-full border-2 border-gray-300 checked:border-[#fb236a] checked:bg-[#fb236a] transition-colors cursor-pointer shrink-0">
                                    <span class="text-xs text-gray-600 group-hover:text-gray-900 transition-colors">
                                        <?php echo $df['label']; ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Job Type -->
                    <div class="bg-white rounded-lg border border-gray-200 px-4 py-4 shadow-sm">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wide">Job Type</h3>
                            <i data-lucide="minus" class="w-3.5 h-3.5 text-gray-400"></i>
                        </div>
                        <div class="flex flex-col gap-2.5">
                            <?php foreach ($job_types as $jt):
                                $isChecked = in_array($jt['label'], $active_types, true);
                                $dotColor  = typeDotColor($jt['label']);
                            ?>
                                <label class="flex items-center gap-2.5 cursor-pointer group">
                                    <input
                                        type="checkbox"
                                        name="type[]"
                                        value="<?php echo htmlspecialchars($jt['label']); ?>"
                                        <?php echo $isChecked ? 'checked' : ''; ?>
                                        style="accent-color: <?php echo $dotColor; ?>"
                                        class="w-3.5 h-3.5 rounded cursor-pointer shrink-0">
                                    <span class="text-xs text-gray-600 group-hover:text-gray-900 transition-colors flex-1">
                                        <?php echo htmlspecialchars($jt['label']); ?>
                                    </span>
                                    <span class="text-[10px] text-gray-400">(<?php echo number_format((int)$jt['count']); ?>)</span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Apply Filters button -->
                    <button type="submit"
                        class="w-full py-2.5 bg-[#fb236a] hover:bg-[#e01060] text-white text-sm font-semibold rounded-lg transition-colors duration-200">
                        Apply Filters
                    </button>

                    <?php if (!empty($keyword) || !empty($location) || !empty($active_types) || $active_date !== 'all' || $category_id > 0): ?>
                        <a href="/jobs" class="text-center text-xs text-gray-500 hover:text-[#fb236a] transition-colors">
                            Clear all filters
                        </a>
                    <?php endif; ?>

                </aside>
            </form>

            <!-- ── Main Content ─────────────────────────────────────────── -->
            <div class="flex-1 min-w-0">

                <!-- Results count -->
                <p class="text-sm text-gray-600 mb-4">
                    <?php if ($total_jobs === 0): ?>
                        No jobs found matching your filters.
                    <?php else: ?>
                        Showing <strong><?php echo number_format(($current_page - 1) * $per_page + 1); ?></strong>
                        –
                        <strong><?php echo number_format(min($current_page * $per_page, $total_jobs)); ?></strong>
                        of <strong><?php echo number_format($total_jobs); ?></strong> jobs
                    <?php endif; ?>
                </p>

                <!-- Job list -->
                <?php if (empty($jobs)): ?>
                    <div class="bg-white rounded-lg border border-gray-100 px-8 py-16 text-center">
                        <i data-lucide="search-x" class="w-12 h-12 text-gray-300 mx-auto mb-4"></i>
                        <p class="text-gray-500 font-medium mb-2">No jobs found</p>
                        <p class="text-sm text-gray-400">Try adjusting your search or filters</p>
                        <a href="/jobs" class="mt-4 inline-block text-sm text-[#fb236a] hover:underline">Clear filters</a>
                    </div>
                <?php else: ?>
                    <div class="flex flex-col gap-3">
                        <?php foreach ($jobs as $job):
                            $logoStyle = companyLogoStyle($job['company']);
                            $initial   = strtoupper(substr($job['company'], 0, 2));
                        ?>
                            <a href="/jobs/<?php echo (int)$job['job_id']; ?>"
                                class="group bg-white rounded-lg border border-gray-100 px-5 py-4 flex items-center gap-4
                        shadow-sm hover:shadow-md transition-shadow duration-200">

                                <!-- Logo -->
                                <div class="shrink-0 w-14 h-14 rounded-lg border border-gray-100 flex items-center justify-center
                            text-sm font-bold overflow-hidden"
                                    style="background-color:<?php echo $logoStyle['bg']; ?>; color:<?php echo $logoStyle['color']; ?>;">
                                    <?php echo htmlspecialchars($initial); ?>
                                </div>

                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="text-sm font-semibold text-[#2b9a66] group-hover:underline leading-snug">
                                            <?php echo htmlspecialchars($job['title']); ?>
                                        </h3>
                                        <?php if ($job['featured']): ?>
                                            <i data-lucide="star" class="w-3.5 h-3.5 text-[#2b9a66] shrink-0" fill="#2b9a66"></i>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-xs text-[#2b9a66] font-medium mt-0.5">
                                        <?php echo htmlspecialchars($job['company']); ?>
                                    </p>
                                    <div class="flex items-center gap-1 mt-1 text-xs text-gray-400">
                                        <i data-lucide="map-pin" class="w-3 h-3 shrink-0"></i>
                                        <span><?php echo htmlspecialchars($job['location']); ?></span>
                                    </div>
                                </div>

                                <!-- Badge + time -->
                                <div class="shrink-0 flex flex-col items-end gap-2 text-right">
                                    <span class="border text-[11px] font-medium px-3 py-1 rounded <?php echo typeBadgeClass($job['type']); ?>">
                                        <?php echo htmlspecialchars($job['type']); ?>
                                    </span>
                                    <span class="text-[11px] text-gray-400"><?php echo htmlspecialchars($job['posted']); ?></span>
                                </div>

                            </a>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1):
                        $window  = 2;
                        $pages   = [1];
                        for ($i = max(2, $current_page - $window); $i <= min($total_pages - 1, $current_page + $window); $i++) {
                            $pages[] = $i;
                        }
                        $pages[] = $total_pages;
                        $pages   = array_unique($pages);
                    ?>
                        <div class="flex flex-wrap items-center justify-center gap-1.5 mt-8">

                            <!-- Prev -->
                            <?php if ($current_page > 1): ?>
                                <a href="<?php echo pageUrl($current_page - 1); ?>"
                                    class="w-8 h-8 flex items-center justify-center rounded border border-gray-300 text-gray-500
                            hover:border-[#fb236a] hover:text-[#fb236a] transition-colors text-xs">
                                    <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                                </a>
                            <?php endif; ?>

                            <?php $prev = null; foreach ($pages as $p): ?>
                                <?php if ($prev !== null && $p - $prev > 1): ?>
                                    <span class="w-8 h-8 flex items-center justify-center text-gray-400 text-xs">…</span>
                                <?php endif; ?>
                                <a href="<?php echo pageUrl($p); ?>"
                                    class="w-8 h-8 flex items-center justify-center rounded border text-xs font-medium transition-colors
                              <?php echo $p === $current_page
                                    ? 'bg-[#fb236a] text-white border-[#fb236a]'
                                    : 'border-gray-300 text-gray-600 hover:border-[#fb236a] hover:text-[#fb236a]'; ?>">
                                    <?php echo $p; ?>
                                </a>
                            <?php $prev = $p; endforeach; ?>

                            <!-- Next -->
                            <?php if ($current_page < $total_pages): ?>
                                <a href="<?php echo pageUrl($current_page + 1); ?>"
                                    class="w-8 h-8 flex items-center justify-center rounded border border-gray-300 text-gray-500
                            hover:border-[#fb236a] hover:text-[#fb236a] transition-colors text-xs">
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                </a>
                            <?php endif; ?>

                        </div>
                    <?php endif; ?>

                <?php endif; ?>

            </div><!-- /.flex-1 -->
        </div><!-- /.max-w-6xl -->
    </div>
</main>

<?php include_once 'partials/footer.php'; ?>