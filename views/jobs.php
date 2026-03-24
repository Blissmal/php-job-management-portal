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
    // ── Mock Data ──────────────────────────────────────────────────────────────────

    $job_types = [
        ['label' => 'Freelance',  'count' => 7],
        ['label' => 'Full Time',  'count' => 1617],
        ['label' => 'Internship', 'count' => 52],
        ['label' => 'Part Time',  'count' => 2],
        ['label' => 'Remote',     'count' => 110],
        ['label' => 'Temporary',  'count' => 25],
    ];

    $date_filters = [
        ['label' => 'Last Hour',    'value' => '1h'],
        ['label' => 'Last 24 Hours', 'value' => '24h'],
        ['label' => 'Last 7 Days',  'value' => '7d'],
        ['label' => 'Last 14 Days', 'value' => '14d'],
        ['label' => 'Last 30 Days', 'value' => '30d'],
        ['label' => 'All',          'value' => 'all', 'default' => true],
    ];

    $jobs = [
        ['title' => 'Tutorial Fellow – Information Technology', 'company' => 'Zetech University', 'location' => 'Nairobi', 'type' => 'Full Time', 'posted' => '5 hours ago', 'featured' => true, 'logo' => 'ZU', 'logo_bg' => '#1a2a6c', 'logo_color' => '#fff'],
        ['title' => 'Webmaster – Computer Science', 'company' => 'Zetech University', 'location' => 'Nairobi', 'type' => 'Full Time', 'posted' => '8 hours ago', 'featured' => true, 'logo' => 'ZU', 'logo_bg' => '#1a2a6c', 'logo_color' => '#fff'],
        ['title' => 'LLM Trainer – Agent Function call', 'company' => 'Turing', 'location' => 'Nairobi/Remote', 'type' => 'Temporary', 'posted' => '8 hours ago', 'featured' => true, 'logo' => 'T', 'logo_bg' => '#111', 'logo_color' => '#fff'],
        ['title' => 'Software Engineering Intern', 'company' => 'Zeraki', 'location' => 'Nairobi', 'type' => 'Internship', 'posted' => '23 hours ago', 'featured' => true, 'logo' => 'Z', 'logo_bg' => '#e8f4fd', 'logo_color' => '#1a6fb5'],
        ['title' => 'Support Engineer – Financial Services IT', 'company' => 'Safaricom PLC', 'location' => 'Nairobi', 'type' => 'Full Time', 'posted' => '1 week ago', 'featured' => true, 'logo' => 'S', 'logo_bg' => '#1db954', 'logo_color' => '#fff'],
        ['title' => 'Information Security Analyst', 'company' => 'Geminia Insurance', 'location' => 'Nairobi', 'type' => 'Full Time', 'posted' => '1 week ago', 'featured' => true, 'logo' => 'G', 'logo_bg' => '#f0f4ff', 'logo_color' => '#3b5bdb'],
        ['title' => 'Spatial Data Application Developer', 'company' => 'United Nations Environment Programme', 'location' => 'Nairobi', 'type' => 'Full Time', 'posted' => '1 week ago', 'featured' => true, 'logo' => 'U', 'logo_bg' => '#e8f0fe', 'logo_color' => '#1a73e8'],
        ['title' => 'Digital Systems Analyst', 'company' => 'Triccare', 'location' => 'Nairobi', 'type' => 'Full Time', 'posted' => '2 weeks ago', 'featured' => true, 'logo' => 'Tc', 'logo_bg' => '#e3f2fd', 'logo_color' => '#0d47a1'],
        ['title' => 'Junior IT Officer', 'company' => 'AA Growers', 'location' => 'Timau', 'type' => 'Full Time', 'posted' => '2 weeks ago', 'featured' => true, 'logo' => 'AA', 'logo_bg' => '#e8f5e9', 'logo_color' => '#2e7d32'],
        ['title' => 'Digital Consultant', 'company' => 'World Vision Kenya (WVK)', 'location' => 'Nairobi', 'type' => 'Full Time', 'posted' => '2 weeks ago', 'featured' => true, 'logo' => 'WV', 'logo_bg' => '#e65100', 'logo_color' => '#fff'],
    ];

    $total_jobs   = 1966;
    $current_page = (int)($_GET['page'] ?? 1);
    $per_page     = 10;
    $total_pages  = (int)ceil($total_jobs / $per_page);

    // Active filters from GET
    $active_types = $_GET['type'] ?? [];
    $active_date  = $_GET['date'] ?? 'all';

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

    // Pagination helper: build page URL preserving existing filters
    function pageUrl(int $p): string
    {
        $params = $_GET;
        $params['page'] = $p;
        return '/jobs?' . http_build_query($params);
    }
    ?>

    <!-- Jobs body -->
    <div class=" min-h-screen py-8 px-4">
        <div class="max-w-6xl mx-auto flex gap-6 items-start">

            <!-- ── Sidebar ──────────────────────────────────────────────── -->
            <aside class="w-56 shrink-0 sticky top-24 flex flex-col gap-4">

                <!-- Location -->
                <div class="bg-white rounded-lg border border-gray-200 px-4 py-3 flex items-center gap-2 shadow-sm">
                    <input
                        type="text"
                        name="location"
                        placeholder="All Locations"
                        value="<?php echo htmlspecialchars($_GET['location'] ?? ''); ?>"
                        class="flex-1 text-sm text-gray-600 placeholder-gray-400 focus:outline-none bg-transparent">
                    <i data-lucide="map-pin" class="w-4 h-4 text-[#fb236a] shrink-0"></i>
                </div>
                <!-- Keyboard -->
                <div class="bg-white rounded-lg border border-gray-200 px-4 py-3 flex items-center gap-2 shadow-sm">
                    <input
                        type="text"
                        name="keyword"
                        id="keyword"
                        placeholder="Job title, keywords..."
                        value="<?php echo htmlspecialchars($_GET['location'] ?? ''); ?>"
                        class="flex-1 text-sm text-gray-600 placeholder-gray-400 focus:outline-none bg-transparent">
                    <i data-lucide="keyboard" class="w-4 h-4 text-[#fb236a] shrink-0"></i>
                </div>


                <!-- Date Posted -->
                <div class="bg-white rounded-lg border border-gray-200 px-4 py-4 shadow-sm">
                    <div class="flex flex-col gap-2.5">
                        <?php foreach ($date_filters as $df):
                            $checked = ($active_date === $df['value']) || (isset($df['default']) && $active_date === 'all');
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
                            $colors = [
                                'Freelance'  => '#ef4444',
                                'Full Time'  => '#3b82f6',
                                'Internship' => '#10b981',
                                'Part Time'  => '#8b5cf6',
                                'Remote'     => '#06b6d4',
                                'Temporary'  => '#f59e0b',
                            ];
                            $color = $colors[$jt['label']] ?? '#6b7280';
                        ?>
                            <label class="flex items-center gap-2.5 cursor-pointer group">
                                <input
                                    type="checkbox"
                                    name="type[]"
                                    value="<?php echo $jt['label']; ?>"
                                    <?php echo $isChecked ? 'checked' : ''; ?>
                                    style="accent-color: <?php echo $color; ?>"
                                    class="w-3.5 h-3.5 rounded cursor-pointer shrink-0">
                                <span class="text-xs text-gray-600 group-hover:text-gray-900 transition-colors flex-1">
                                    <?php echo $jt['label']; ?>
                                </span>
                                <span class="text-[10px] text-gray-400">(<?php echo number_format($jt['count']); ?>)</span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

            </aside>

            <!-- ── Main Content ─────────────────────────────────────────── -->
            <div class="flex-1 min-w-0">

                <!-- Results count -->
                <p class="text-sm text-gray-600 mb-4">
                    Showing <strong><?php echo (($current_page - 1) * $per_page) + 1; ?></strong>
                    –
                    <strong><?php echo min($current_page * $per_page, $total_jobs); ?></strong>
                    of <strong><?php echo number_format($total_jobs); ?></strong> jobs
                </p>

                <!-- Job list -->
                <div class="flex flex-col gap-3">
                    <?php foreach ($jobs as $job): ?>
                        <a href="/jobs/<?php echo urlencode(strtolower(str_replace([' ', '–'], ['-', '-'], $job['title']))); ?>"
                            class="group bg-white rounded-lg border border-gray-100 px-5 py-4 flex items-center gap-4
                    shadow-sm hover:shadow-md transition-shadow duration-200">

                            <!-- Logo -->
                            <div class="shrink-0 w-14 h-14 rounded-lg border border-gray-100 flex items-center justify-center
                        text-sm font-bold overflow-hidden"
                                style="background-color:<?php echo $job['logo_bg']; ?>; color:<?php echo $job['logo_color']; ?>;">
                                <?php echo $job['logo']; ?>
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
                <?php
                $window  = 2; // pages around current
                $pages   = [];
                $pages[] = 1;
                for ($i = max(2, $current_page - $window); $i <= min($total_pages - 1, $current_page + $window); $i++) {
                    $pages[] = $i;
                }
                $pages[] = $total_pages;
                $pages = array_unique($pages);
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

                    <?php
                    $prev = null;
                    foreach ($pages as $p):
                        if ($prev !== null && $p - $prev > 1): ?>
                            <span class="w-8 h-8 flex items-center justify-center text-gray-400 text-xs">…</span>
                        <?php endif; ?>

                        <a href="<?php echo pageUrl($p); ?>"
                            class="w-8 h-8 flex items-center justify-center rounded border text-xs font-medium transition-colors
                      <?php echo $p === $current_page
                            ? 'bg-[#fb236a] text-white border-[#fb236a]'
                            : 'border-gray-300 text-gray-600 hover:border-[#fb236a] hover:text-[#fb236a]'; ?>">
                            <?php echo $p; ?>
                        </a>
                    <?php
                        $prev = $p;
                    endforeach;
                    ?>

                    <!-- Next -->
                    <?php if ($current_page < $total_pages): ?>
                        <a href="<?php echo pageUrl($current_page + 1); ?>"
                            class="w-8 h-8 flex items-center justify-center rounded border border-gray-300 text-gray-500
                    hover:border-[#fb236a] hover:text-[#fb236a] transition-colors text-xs">
                            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                        </a>
                    <?php endif; ?>

                </div>

            </div><!-- /.flex-1 -->
        </div><!-- /.max-w-6xl -->
    </div>
</main>

<?php include_once 'partials/footer.php'; ?>
