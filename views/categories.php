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
    // ── Mock Data ──────────────────────────────────────────────────────────────────

    $categories = [
        ['icon' => 'user-tie',          'name' => 'Account Manager',      'count' => 2],
        ['icon' => 'bot',               'name' => 'Artificial Intelligence', 'count' => 35],
        ['icon' => 'briefcase',         'name' => 'Attaché',               'count' => 1],
        ['icon' => 'cpu',               'name' => 'Automation',            'count' => 47],
        ['icon' => 'laptop',            'name' => 'Backend Engineer',      'count' => 102],
        ['icon' => 'database',          'name' => 'Big Data',              'count' => 14],
        ['icon' => 'network',           'name' => 'Blockchain Developer',  'count' => 15],
        ['icon' => 'bar-chart-2',       'name' => 'Business Analysis',     'count' => 7],
    ];

    $jobs = [
        [
            'title'    => 'Software Engineering Intern',
            'company'  => 'Zeraki',
            'location' => 'Nairobi',
            'type'     => 'Internship',
            'posted'   => '11 hours ago',
            'featured' => true,
            'logo'     => 'Z',
            'logo_bg'  => '#e8f4fd',
            'logo_color' => '#1a6fb5',
        ],
        [
            'title'    => 'Support Engineer – Financial Services IT',
            'company'  => 'Safaricom PLC',
            'location' => 'Nairobi',
            'type'     => 'Full Time',
            'posted'   => '1 week ago',
            'featured' => true,
            'logo'     => 'S',
            'logo_bg'  => '#1db954',
            'logo_color' => '#fff',
        ],
        [
            'title'    => 'Information Security Analyst',
            'company'  => 'Geminia Insurance',
            'location' => 'Nairobi',
            'type'     => 'Full Time',
            'posted'   => '1 week ago',
            'featured' => true,
            'logo'     => 'G',
            'logo_bg'  => '#f0f4ff',
            'logo_color' => '#3b5bdb',
        ],
        [
            'title'    => 'Spatial Data Application Developer',
            'company'  => 'United Nations Environment Programme',
            'location' => 'Nairobi',
            'type'     => 'Full Time',
            'posted'   => '1 week ago',
            'featured' => true,
            'logo'     => 'U',
            'logo_bg'  => '#e8f0fe',
            'logo_color' => '#1a73e8',
        ],
        [
            'title'    => 'DevOps Engineer',
            'company'  => 'Twiga Foods',
            'location' => 'Nairobi',
            'type'     => 'Full Time',
            'posted'   => '2 days ago',
            'featured' => false,
            'logo'     => 'T',
            'logo_bg'  => '#fff3e0',
            'logo_color' => '#e65100',
        ],
        [
            'title'    => 'Mobile Developer (Flutter)',
            'company'  => 'NCBA Bank',
            'location' => 'Nairobi',
            'type'     => 'Contract',
            'posted'   => '3 days ago',
            'featured' => false,
            'logo'     => 'N',
            'logo_bg'  => '#e8f5e9',
            'logo_color' => '#2e7d32',
        ],
    ];

    $total_jobs   = 1966;
    $showing_from = 7;
    $showing_to   = 12;

    // Badge colour map
    function typeBadgeClass(string $type): string
    {
        return match ($type) {
            'Internship' => 'border-[#5433b2] text-[#5433b2]',
            'Contract'   => 'border-[#c05621] text-[#c05621]',
            default      => 'border-[#176f45] text-[#176f45]',
        };
    }
    ?>

    <!-- ═══════════════════════════════════════════════════════════════
     SECTION 1 – Popular Categories
════════════════════════════════════════════════════════════════ -->
    <section class="bg-white py-16 px-4">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-0 border border-gray-200">
            <?php foreach ($categories as $cat):
            $index = array_search($cat, $categories);
            $total = count($categories);
            $isTop = $index < 2;
            $isLeft = $index % 4 === 0;
            $isRight = $index % 4 === 3;
            $isBottom = $index >= $total - 4;
            ?>
            <a href="/jobs?category=<?php echo urlencode($cat['name']); ?>"
            class="group flex flex-col items-center justify-center text-center aspect-square p-8
              transition-all duration-200 hover:shadow-lg hover:z-10
              <?php echo !$isTop ? 'border-t' : ''; ?> <?php echo !$isLeft ? 'border-l' : ''; ?>
              <?php echo !$isRight ? 'border-r' : ''; ?> <?php echo !$isBottom ? 'border-b' : ''; ?> border-gray-200">

            <i data-lucide="<?php echo $cat['icon']; ?>"
                class="w-10 h-10 text-[#858bbe] mb-4 transition-transform duration-200 group-hover:scale-110"
                stroke-width="1.5"></i>

            <h3 class="text-sm font-bold text-gray-800 mb-1"><?php echo htmlspecialchars($cat['name']); ?></h3>
            <p class="text-xs text-[#fb236a] font-medium">
                <?php echo $cat['count']; ?> open position<?php echo $cat['count'] !== 1 ? 's' : ''; ?>
            </p>
            </a>
            <?php endforeach; ?>
            </div>

        </div>
    </section>

</main>

<?php include_once 'partials/footer.php'; ?>
