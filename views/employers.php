<?php include_once 'partials/header.php'; ?>

<main class="w-full">
    <section class="relative w-full overflow-hidden" style="height:280px;">
        <div class="absolute inset-0" style="background: linear-gradient(135deg, #8b91dd 0%, #10195d 70%, #10195d 100%); opacity:0.92;"></div>
        <div class="absolute inset-0 flex flex-col items-center justify-end pb-10 z-10">
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-3 tracking-tight">Employers</h1>
            <p class="text-gray-300 text-sm">
                <a href="/" class="underline underline-offset-2 hover:text-white transition-colors">Home</a>
                <span class="mx-2 opacity-50">—</span>
                <span class="text-white">Employers</span>
            </p>
        </div>
    </section>
    <?php
    // ── Mock Data ──────────────────────────────────────────────────────────────────

    $specialisms = [
        ['label' => 'Mobile',     'count' => 1],
        ['label' => 'Software',   'count' => 1],
        ['label' => 'Technology', 'count' => 2],
    ];

    $team_sizes = [
        ['label' => '1001-5000',   'count' => 3],
        ['label' => '401-500',     'count' => 1],
        ['label' => '5001-10,000', 'count' => 7],
    ];

    $employers = [
        [
            'name'        => 'Mind Cloud Tribe',
            'specialism'  => 'Software',
            'location'    => null,
            'description' => 'Mind Cloud Tribe is a unique platform of tutorials (videos, tools & tool-builders) to help you build your business plan as well as a marketplace....',
            'open_jobs'   => 0,
            'logo'        => 'MC',
            'logo_bg'     => '#e0f2fe',
            'logo_color'  => '#0369a1',
        ],
        [
            'name'        => 'Telkom Kenya',
            'specialism'  => null,
            'location'    => 'Nairobi, Kenya',
            'description' => 'Telkom Kenya is an integrated telecommunications provider in Kenya. It was previously a part of the Kenya Posts and Telecommunications Corporation (KPTC) which was....',
            'open_jobs'   => 0,
            'logo'        => 'TK',
            'logo_bg'     => '#0ea5e9',
            'logo_color'  => '#fff',
        ],
        [
            'name'        => 'Equity Bank',
            'specialism'  => null,
            'location'    => 'Nairobi, Kenya',
            'description' => 'Equity Bank Kenya Limited, is a financial services provider headquartered in Nairobi, Kenya. It is licensed as a commercial bank by the Central Bank....',
            'open_jobs'   => 15,
            'logo'        => 'EQ',
            'logo_bg'     => '#fff3e0',
            'logo_color'  => '#922b21',
        ],
        [
            'name'        => 'Safaricom Kenya',
            'specialism'  => 'Mobile',
            'location'    => 'Nairobi, Kenya',
            'description' => 'Safaricom PLC is a Kenyan mobile network operator headquartered at Safaricom House in Nairobi, Kenya. It is the largest telecommunications provider in Kenya, and one....',
            'open_jobs'   => 0,
            'logo'        => 'SF',
            'logo_bg'     => '#e8f5e9',
            'logo_color'  => '#1db954',
        ],
        [
            'name'        => 'Microsoft Kenya',
            'specialism'  => 'Technology',
            'location'    => 'Nairobi, Kenya',
            'description' => 'At Microsoft, our mission is to empower every person and every organization on the planet to achieve more. Our mission is grounded in both....',
            'open_jobs'   => 0,
            'logo'        => 'MS',
            'logo_bg'     => '#e8f0fe',
            'logo_color'  => '#0078d4',
        ],
        [
            'name'        => 'Nokia',
            'specialism'  => 'Technology',
            'location'    => 'Nairobi, Kenya',
            'description' => 'Nokia Corporation is a Finnish multinational telecommunications, information technology, and consumer electronics company, founded in 1865. Nokia\'s headquarters are in Espoo, Finland.',
            'open_jobs'   => 3,
            'logo'        => 'NOK',
            'logo_bg'     => '#003cba',
            'logo_color'  => '#fff',
        ],
        [
            'name'        => 'Google Kenya',
            'specialism'  => 'Technology',
            'location'    => 'Nairobi, Kenya',
            'description' => 'Google, LLC is an American multinational technology company that specializes in Internet-related services and products, which include online advertising technologies, a search engine, cloud....',
            'open_jobs'   => 0,
            'logo'        => 'G',
            'logo_bg'     => '#fff',
            'logo_color'  => '#4285F4',
        ],
        [
            'name'        => 'Cellulant Kenya',
            'specialism'  => null,
            'location'    => 'Nairobi, Kenya',
            'description' => 'Cellulant is a leading multinational payments company in Africa on a mission to digitise payments for Africa\'s largest economies. The company operates a one-stop....',
            'open_jobs'   => 0,
            'logo'        => 'CL',
            'logo_bg'     => '#f5f5f5',
            'logo_color'  => '#c0392b',
        ],
        [
            'name'        => 'Konza Technopolis',
            'specialism'  => 'Technology',
            'location'    => 'Nairobi, Kenya',
            'description' => 'Konza Technopolis, previously called Konza Technology City, is a large technology hub planned by the Government of Kenya to be built 64 km south of....',
            'open_jobs'   => 0,
            'logo'        => 'KT',
            'logo_bg'     => '#e8f5e9',
            'logo_color'  => '#1e8449',
        ],
        [
            'name'        => 'Kenya Ports Authority',
            'specialism'  => null,
            'location'    => 'Nairobi, Kenya',
            'description' => 'The Kenya Ports Authority\'s mandate is to maintain, operate, improve and regulate all sea and inland waterway ports in Kenya. Other ports include Lamu,....',
            'open_jobs'   => 0,
            'logo'        => 'KPA',
            'logo_bg'     => '#1a3a6c',
            'logo_color'  => '#fff',
        ],
    ];

    $active_specialisms = $_GET['specialism'] ?? [];
    $active_sizes       = $_GET['size']       ?? [];
    $keyword            = $_GET['q']          ?? '';
    $location           = $_GET['location']   ?? '';

    $specialism_colors = [
        'Software'   => 'text-[#fb236a]',
        'Mobile'     => 'text-[#fb236a]',
        'Technology' => 'text-[#2b9a66]',
    ];
    ?>

    <div class="min-h-screen py-8 px-4">
        <div class="max-w-6xl mx-auto flex gap-6 items-start">

            <!-- ── Sidebar ──────────────────────────────────────────────── -->
            <aside class="w-56 shrink-0 sticky top-24 flex flex-col gap-4">

                <!-- Keyword search -->
                <div class="bg-white rounded-lg border border-gray-200 px-4 py-3 flex items-center gap-2 shadow-sm">
                    <input
                        type="text" name="q"
                        placeholder="Company title or keywords"
                        value="<?php echo htmlspecialchars($keyword); ?>"
                        class="flex-1 text-sm text-gray-600 placeholder-gray-400 focus:outline-none bg-transparent min-w-0">
                    <i data-lucide="search" class="w-4 h-4 text-[#fb236a] shrink-0"></i>
                </div>

                <!-- Location -->
                <div class="bg-white rounded-lg border border-gray-200 px-4 py-3 flex items-center gap-2 shadow-sm">
                    <input
                        type="text" name="location"
                        placeholder="All Locations"
                        value="<?php echo htmlspecialchars($location); ?>"
                        class="flex-1 text-sm text-gray-600 placeholder-gray-400 focus:outline-none bg-transparent min-w-0">
                    <i data-lucide="map-pin" class="w-4 h-4 text-[#fb236a] shrink-0"></i>
                </div>

                <!-- Specialism -->
                <div class="bg-white rounded-lg border border-gray-200 px-4 py-4 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wide">Specialism</h3>
                        <i data-lucide="minus" class="w-3.5 h-3.5 text-gray-400"></i>
                    </div>
                    <div class="flex flex-col gap-2.5">
                        <?php foreach ($specialisms as $s):
                            $checked = in_array($s['label'], $active_specialisms, true);
                        ?>
                            <label class="flex items-center gap-2.5 cursor-pointer group">
                                <input type="checkbox" name="specialism[]" value="<?php echo $s['label']; ?>"
                                    <?php echo $checked ? 'checked' : ''; ?>
                                    class="w-3.5 h-3.5 rounded border-gray-300 cursor-pointer shrink-0">
                                <span class="text-xs text-gray-600 group-hover:text-gray-900 flex-1">
                                    <?php echo $s['label']; ?>
                                </span>
                                <span class="text-[10px] text-gray-400">(<?php echo $s['count']; ?>)</span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Team Size -->
                <div class="bg-white rounded-lg border border-gray-200 px-4 py-4 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wide">Team Size</h3>
                        <i data-lucide="minus" class="w-3.5 h-3.5 text-gray-400"></i>
                    </div>
                    <div class="flex flex-col gap-2.5">
                        <?php foreach ($team_sizes as $ts):
                            $checked = in_array($ts['label'], $active_sizes, true);
                        ?>
                            <label class="flex items-center gap-2.5 cursor-pointer group">
                                <input type="checkbox" name="size[]" value="<?php echo $ts['label']; ?>"
                                    <?php echo $checked ? 'checked' : ''; ?>
                                    class="w-3.5 h-3.5 rounded border-gray-300 cursor-pointer shrink-0">
                                <span class="text-xs text-gray-600 group-hover:text-gray-900 flex-1">
                                    <?php echo $ts['label']; ?>
                                </span>
                                <span class="text-[10px] text-gray-400">(<?php echo $ts['count']; ?>)</span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

            </aside>

            <!-- ── Employer listings ─────────────────────────────────────── -->
            <div class="flex-1 min-w-0">
                <div class="flex flex-col divide-y divide-gray-200 bg-white   overflow-hidden">
                    <?php foreach ($employers as $emp): ?>
                        <a href="/employers/<?php echo urlencode(strtolower(str_replace(' ', '-', $emp['name']))); ?>"
                            class="group flex items-start gap-5 px-6 py-6 hover:bg-gray-50/70 transition-colors duration-150">

                            <!-- Logo -->
                            <div class="shrink-0 w-16 h-16 rounded-lg border border-gray-100 flex items-center justify-center
                        text-sm font-bold overflow-hidden shadow-sm"
                                style="background-color:<?php echo $emp['logo_bg']; ?>; color:<?php echo $emp['logo_color']; ?>;">
                                <?php echo $emp['logo']; ?>
                            </div>

                            <!-- Body -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-4 flex-wrap">
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900 group-hover:text-[#2b9a66] transition-colors leading-snug">
                                            <?php echo htmlspecialchars($emp['name']); ?>
                                        </h3>
                                        <?php if ($emp['specialism']): ?>
                                            <p class="text-xs font-medium mt-0.5 <?php echo $specialism_colors[$emp['specialism']] ?? 'text-gray-500'; ?>">
                                                <?php echo $emp['specialism']; ?>
                                            </p>
                                        <?php endif; ?>
                                        <?php if ($emp['location']): ?>
                                            <div class="flex items-center gap-1 mt-1 text-xs text-gray-400">
                                                <i data-lucide="map-pin" class="w-3 h-3 shrink-0"></i>
                                                <span><?php echo htmlspecialchars($emp['location']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <!-- Open positions -->
                                    <span class="text-sm font-medium shrink-0 <?php echo $emp['open_jobs'] > 0 ? 'text-[#2b9a66]' : 'text-[#2b9a66]'; ?>">
                                        <?php echo $emp['open_jobs']; ?> Open Position<?php echo $emp['open_jobs'] !== 1 ? 's' : ''; ?>
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 leading-relaxed mt-2 line-clamp-2">
                                    <?php echo htmlspecialchars($emp['description']); ?>
                                </p>
                            </div>

                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <div class="flex items-center justify-center gap-1.5 mt-8">
                    <?php foreach ([1, 2] as $p): ?>
                        <a href="/employers?page=<?php echo $p; ?>"
                            class="w-8 h-8 flex items-center justify-center rounded border text-xs font-medium transition-colors
                    <?php echo $p === 1
                            ? 'bg-[#fb236a] text-white border-[#fb236a]'
                            : 'border-gray-300 text-gray-600 hover:border-[#fb236a] hover:text-[#fb236a]'; ?>">
                            <?php echo $p; ?>
                        </a>
                    <?php endforeach; ?>
                    <a href="/employers?page=3"
                        class="w-8 h-8 flex items-center justify-center rounded border border-gray-300 text-gray-500
                  hover:border-[#fb236a] hover:text-[#fb236a] transition-colors text-xs">
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>

            </div>
        </div>
    </div>
</main>

<?php include_once 'partials/footer.php'; ?>
