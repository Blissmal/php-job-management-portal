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
    $db = getDB();

    $active_specialisms = $_GET['specialism'] ?? [];
    $active_sizes       = $_GET['size']       ?? [];
    $keyword            = $_GET['q']          ?? '';
    $location           = $_GET['location']   ?? '';

    // ── Sidebar data from DB ──────────────────────────────────────────────────

    // "Specialism" = industry values with counts
    $specialisms = $db->query(
        "SELECT ep.industry AS label, COUNT(*) AS count
         FROM employer_profiles ep
         JOIN users u ON u.user_id = ep.user_id
         WHERE u.status = 'active' AND ep.industry IS NOT NULL AND ep.industry != ''
         GROUP BY ep.industry
         ORDER BY count DESC, ep.industry ASC"
    )->fetchAll();

    // "Team Size" = team_size values with counts, in canonical order
    $teamSizeOrder = ['1-10','11-50','51-200','201-500','501-1000','1001-5000','5001-10000','10000+'];
    $rawSizes = $db->query(
        "SELECT ep.team_size AS label, COUNT(*) AS count
         FROM employer_profiles ep
         JOIN users u ON u.user_id = ep.user_id
         WHERE u.status = 'active' AND ep.team_size IS NOT NULL
         GROUP BY ep.team_size"
    )->fetchAll(PDO::FETCH_KEY_PAIR);

    $team_sizes = [];
    foreach ($teamSizeOrder as $sz) {
        if (isset($rawSizes[$sz])) {
            $team_sizes[] = ['label' => $sz, 'count' => $rawSizes[$sz]];
        }
    }

    // ── Employer listings from DB ─────────────────────────────────────────────
    $conditions = ["u.status = 'active'", "u.role = 'employer'"];
    $params     = [];

    if ($keyword !== '') {
        $conditions[] = "(ep.company_name LIKE ? OR ep.description LIKE ?)";
        $params[] = "%$keyword%";
        $params[] = "%$keyword%";
    }
    if ($location !== '') {
        $conditions[] = "ep.location LIKE ?";
        $params[] = "%$location%";
    }
    if ($active_specialisms) {
        $active_specialisms = (array)$active_specialisms;
        $ph = implode(',', array_fill(0, count($active_specialisms), '?'));
        $conditions[] = "ep.industry IN ($ph)";
        $params = array_merge($params, array_values($active_specialisms));
    }
    if ($active_sizes) {
        $active_sizes = (array)$active_sizes;
        $ph = implode(',', array_fill(0, count($active_sizes), '?'));
        $conditions[] = "ep.team_size IN ($ph)";
        $params = array_merge($params, array_values($active_sizes));
    }

    $where = 'WHERE ' . implode(' AND ', $conditions);

    $empStmt = $db->prepare(
        "SELECT ep.user_id, ep.company_name, ep.industry AS specialism,
                ep.location, ep.team_size, ep.website, ep.logo_path, ep.description,
                (SELECT COUNT(*) FROM jobs j
                 WHERE j.employer_id = ep.user_id AND j.status = 'open') AS open_jobs
         FROM employer_profiles ep
         JOIN users u ON u.user_id = ep.user_id
         $where
         ORDER BY open_jobs DESC, ep.company_name ASC"
    );
    $empStmt->execute($params);
    $employers = $empStmt->fetchAll();

    $specialism_colors = [
        'Software'        => 'text-[#fb236a]',
        'Mobile'          => 'text-[#fb236a]',
        'Technology'      => 'text-[#2b9a66]',
        'EdTech'          => 'text-[#2b9a66]',
        'Telecommunications' => 'text-[#fb236a]',
        'Banking & Finance'  => 'text-[#2b9a66]',
    ];

    function logoInitials(string $name): string {
        $words = explode(' ', trim($name));
        return mb_strtoupper(
            mb_substr($words[0], 0, 1) . (isset($words[1]) ? mb_substr($words[1], 0, 1) : '')
        );
    }
    ?>

    <div class="min-h-screen py-8 px-4">
        <div class="max-w-6xl mx-auto flex gap-6 items-start">

            <!-- ── Sidebar ──────────────────────────────────────────────── -->
            <aside class="w-56 shrink-0 sticky top-24 flex flex-col gap-4">

                <!-- Keyword search -->
                <form method="GET" action="/employers">
                    <div class="bg-white rounded-lg border border-gray-200 px-4 py-3 flex items-center gap-2 shadow-sm">
                        <input
                            type="text" name="q"
                            placeholder="Company title or keywords"
                            value="<?php echo htmlspecialchars($keyword); ?>"
                            class="flex-1 text-sm text-gray-600 placeholder-gray-400 focus:outline-none bg-transparent min-w-0">
                        <i data-lucide="search" class="w-4 h-4 text-[#fb236a] shrink-0"></i>
                    </div>

                    <!-- Location -->
                    <div class="bg-white rounded-lg border border-gray-200 px-4 py-3 flex items-center gap-2 shadow-sm mt-4">
                        <input
                            type="text" name="location"
                            placeholder="All Locations"
                            value="<?php echo htmlspecialchars($location); ?>"
                            class="flex-1 text-sm text-gray-600 placeholder-gray-400 focus:outline-none bg-transparent min-w-0">
                        <i data-lucide="map-pin" class="w-4 h-4 text-[#fb236a] shrink-0"></i>
                    </div>

                    <!-- Specialism (= industry from DB) -->
                    <?php if ($specialisms): ?>
                    <div class="bg-white rounded-lg border border-gray-200 px-4 py-4 shadow-sm mt-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wide">Specialism</h3>
                            <i data-lucide="minus" class="w-3.5 h-3.5 text-gray-400"></i>
                        </div>
                        <div class="flex flex-col gap-2.5">
                            <?php foreach ($specialisms as $s):
                                $checked = in_array($s['label'], (array)$active_specialisms, true);
                            ?>
                                <label class="flex items-center gap-2.5 cursor-pointer group">
                                    <input type="checkbox" name="specialism[]" value="<?php echo htmlspecialchars($s['label']); ?>"
                                        <?php echo $checked ? 'checked' : ''; ?>
                                        class="w-3.5 h-3.5 rounded border-gray-300 cursor-pointer shrink-0">
                                    <span class="text-xs text-gray-600 group-hover:text-gray-900 flex-1">
                                        <?php echo htmlspecialchars($s['label']); ?>
                                    </span>
                                    <span class="text-[10px] text-gray-400">(<?php echo $s['count']; ?>)</span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Team Size (= team_size from DB) -->
                    <?php if ($team_sizes): ?>
                    <div class="bg-white rounded-lg border border-gray-200 px-4 py-4 shadow-sm mt-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wide">Team Size</h3>
                            <i data-lucide="minus" class="w-3.5 h-3.5 text-gray-400"></i>
                        </div>
                        <div class="flex flex-col gap-2.5">
                            <?php foreach ($team_sizes as $ts):
                                $checked = in_array($ts['label'], (array)$active_sizes, true);
                            ?>
                                <label class="flex items-center gap-2.5 cursor-pointer group">
                                    <input type="checkbox" name="size[]" value="<?php echo htmlspecialchars($ts['label']); ?>"
                                        <?php echo $checked ? 'checked' : ''; ?>
                                        class="w-3.5 h-3.5 rounded border-gray-300 cursor-pointer shrink-0">
                                    <span class="text-xs text-gray-600 group-hover:text-gray-900 flex-1">
                                        <?php echo htmlspecialchars($ts['label']); ?>
                                    </span>
                                    <span class="text-[10px] text-gray-400">(<?php echo $ts['count']; ?>)</span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <button type="submit" class="hidden"></button>
                </form>

            </aside>

            <!-- ── Employer listings ─────────────────────────────────────── -->
            <div class="flex-1 min-w-0">
                <div class="flex flex-col divide-y divide-gray-200 bg-white overflow-hidden">
                    <?php foreach ($employers as $emp): ?>
                        <a href="/employers/<?php echo urlencode(strtolower(str_replace(' ', '-', $emp['company_name']))); ?>"
                            class="group flex items-start gap-5 px-6 py-6 hover:bg-gray-50/70 transition-colors duration-150">

                            <!-- Logo -->
                            <div class="shrink-0 w-16 h-16 rounded-lg border border-gray-100 flex items-center justify-center
                                text-sm font-bold overflow-hidden shadow-sm bg-[#e8f4fd] text-[#1a6fb5]">
                                <?php if ($emp['logo_path']): ?>
                                    <img src="/<?php echo htmlspecialchars($emp['logo_path']); ?>" alt="" class="w-full h-full object-contain p-1">
                                <?php else: ?>
                                    <?php echo logoInitials($emp['company_name']); ?>
                                <?php endif; ?>
                            </div>

                            <!-- Body -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-4 flex-wrap">
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900 group-hover:text-[#2b9a66] transition-colors leading-snug">
                                            <?php echo htmlspecialchars($emp['company_name']); ?>
                                        </h3>
                                        <?php if ($emp['specialism']): ?>
                                            <p class="text-xs font-medium mt-0.5 <?php echo $specialism_colors[$emp['specialism']] ?? 'text-gray-500'; ?>">
                                                <?php echo htmlspecialchars($emp['specialism']); ?>
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
                                    <span class="text-sm font-medium shrink-0 text-[#2b9a66]">
                                        <?php echo $emp['open_jobs']; ?> Open Position<?php echo $emp['open_jobs'] != 1 ? 's' : ''; ?>
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 leading-relaxed mt-2 line-clamp-2">
                                    <?php echo htmlspecialchars($emp['description'] ?? ''); ?>
                                </p>
                            </div>

                        </a>
                    <?php endforeach; ?>

                    <?php if (empty($employers)): ?>
                        <div class="px-6 py-16 text-center text-gray-400 text-sm">
                            No employers found.
                        </div>
                    <?php endif; ?>
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