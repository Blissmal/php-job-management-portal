<?php include_once 'partials/header.php'; ?>

<main class="w-full">
    <section class="relative h-screen max-h-[70dvh] w-full max-w-full after:absolute after:inset-0 after:bg-linear-[45deg,#8b91dd_0%,#10195d_71%,#10195d_100%] after:opacity-80 after:content-[''] after:z-0 pt-16">
        <div class="relative z-10 w-full max-w-7xl mx-auto px-4 flex flex-col items-center justify-center h-full">
            <!-- Hero Content -->
            <div class="text-center mb-12">
                <h1 class="text-3xl md:text-5xl font-bold text-white mb-4">
                    Connecting Employers with top tier talents
                </h1>
                <p class="text-xl text-gray-200">
                    Find Jobs, Employment & Career Opportunities
                </p>
            </div>

            <!-- Search Form -->
            <form id="searchForm" action="/jobs" method="GET" class="flex flex-col md:flex-row gap-6 w-full px-8 md:px-20 max-w-5xl">
                <!-- Keyword Input -->
                <div class="basis-0 grow max-w-full relative flex items-center bg-white px-2 gap-3 rounded-md">
                    <input
                        type="text"
                        name="keyword"
                        id="keyword"
                        placeholder="Job title, keywords..."
                        class="w-full px-6 py-3 text-gray-800 focus:outline-none focus:ring-none" />
                    <i data-lucide="keyboard" class="w-8 h-8 text-[#fb236a]"></i>
                </div>

                <!-- City Input -->
                <div class="flex-none w-auto max-w-none relative flex items-center bg-white px-2 gap-3 rounded-md">
                    <input
                        type="text"
                        name="location"
                        id="city"
                        placeholder="City, province or region"
                        class="w-full px-6 py-3 text-gray-800 focus:outline-none focus:ring-none" />
                    <i data-lucide="map-pin" class="w-8 h-8 text-[#fb236a]"></i>
                </div>

                <!-- Search Button -->
                <div class="flex-none w-auto max-w-none relative flex items-center">
                    <button
                        type="submit"
                        class="p-3 bg-[#fb236a] hover:bg-[#fb236a]/80 text-white font-semibold rounded-md transition-colors duration-200 flex items-center justify-center gap-2 max-md:w-full">
                        <span class="block md:hidden">Search</span>
                        <i data-lucide="search" class="w-8 h-8"></i>
                    </button>
                </div>
            </form>
        </div>
        <div class="absolute left-1/2 top-full -translate-x-1/2 -translate-y-1/2 rounded-full z-[2]">
            <a href="#jh-scroll-here" title="Scroll Button" class="group text-white w-[130px] h-[130px] border-2 border-white flex items-center justify-center rounded-full transition-all duration-300 ease-in-out hover:border-[#fb236a]">
                <span class="bg-white text-[#fb236a] p-5 rounded-full shadow-lg transition-colors duration-300 group-hover:bg-[#fb236a] group-hover:text-white">
                    <i data-lucide="arrow-down"></i>
                </span>
            </a>
        </div>
    </section>

    <?php
    // ── Live Data ─────────────────────────────────────────────────────────────────
    require_once 'php/config/connection.php';

    try {
        $db = getDB();

        // ── Categories: top 8 by open job count ─────────────────────────────────
        $cat_stmt = $db->query("
            SELECT jc.category_name AS name,
                   COALESCE(jc.icon_path, 'briefcase') AS icon,
                   COUNT(CASE WHEN j.status = 'open' THEN 1 END) AS count
            FROM job_categories jc
            LEFT JOIN jobs j ON j.category_id = jc.category_id
            GROUP BY jc.category_id, jc.category_name, jc.icon_path
            ORDER BY count DESC
            LIMIT 8
        ");
        $categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

        // ── Featured jobs: latest 6 open jobs ────────────────────────────────────
        $job_stmt = $db->query("
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
            WHERE j.status = 'open'
            ORDER BY j.featured DESC, j.created_at DESC
            LIMIT 6
        ");
        $jobs = $job_stmt->fetchAll(PDO::FETCH_ASSOC);

        // ── Stats ─────────────────────────────────────────────────────────────────
        $total_jobs   = (int) $db->query("SELECT COUNT(*) FROM jobs WHERE status = 'open'")->fetchColumn();
        $total_today  = (int) $db->query("SELECT COUNT(*) FROM jobs WHERE status = 'open' AND DATE(created_at) = CURDATE()")->fetchColumn();

        // Pagination display info
        $showing_from = min(7, $total_jobs);
        $showing_to   = min(12, $total_jobs);

        // ── Companies: top 5 employers by open job count ─────────────────────────
        $co_stmt = $db->query("
            SELECT ep.company_name                          AS name,
                   UPPER(SUBSTR(ep.company_name, 1, 2))    AS initial,
                   COUNT(CASE WHEN j.status='open' THEN 1 END) AS open_jobs
            FROM employer_profiles ep
            JOIN users u ON ep.user_id = u.user_id
            LEFT JOIN jobs j ON j.employer_id = ep.user_id
            WHERE u.status = 'active'
            GROUP BY ep.profile_id, ep.company_name
            ORDER BY open_jobs DESC
            LIMIT 5
        ");
        $companies = $co_stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        // Graceful fallback — empty arrays so page still renders
        $categories  = [];
        $jobs        = [];
        $companies   = [];
        $total_jobs  = 0;
        $total_today = 0;
        $showing_from = 0;
        $showing_to   = 0;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────────

    // Generate a deterministic logo background + text colour from a company name
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
        $index = abs(crc32($name)) % count($palettes);
        return $palettes[$index];
    }

    function typeBadgeClass(string $type): string
    {
        return match ($type) {
            'Internship' => 'border-[#5433b2] text-[#5433b2]',
            'Contract'   => 'border-[#c05621] text-[#c05621]',
            'Remote'     => 'border-[#6d28d9] text-[#6d28d9]',
            'Temporary'  => 'border-[#c05621] text-[#c05621]',
            'Freelance'  => 'border-[#b45309] text-[#b45309]',
            'Part Time'  => 'border-[#0369a1] text-[#0369a1]',
            default      => 'border-[#176f45] text-[#176f45]',
        };
    }
    ?>

    <!-- ═══════════════════════════════════════════════════════════════
     SECTION 1 – Popular Categories
════════════════════════════════════════════════════════════════ -->
    <section id="jh-scroll-here" class="bg-white py-16 px-4">
        <div class="max-w-6xl mx-auto">

            <!-- Heading -->
            <div class="text-center my-12">
                <h2 class="text-3xl font-bold text-gray-800 mb-2" style="font-family:'Montserrat',sans-serif;">
                    Popular Categories
                </h2>
                <p class="text-gray-500 text-sm">
                    <?php echo number_format($total_jobs); ?> jobs live
                    <?php if ($total_today > 0): ?> - <?php echo $total_today; ?> added today<?php endif; ?>.
                </p>
            </div>

            <?php if (empty($categories)): ?>
                <p class="text-center text-gray-400 py-10">No categories found.</p>
            <?php else: ?>
                <!-- Grid -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-0 border border-gray-200">
                    <?php foreach ($categories as $index => $cat):
                        $total = count($categories);
                        $isTop    = $index < 4;
                        $isLeft   = $index % 4 === 0;
                        $isRight  = $index % 4 === 3;
                        $isBottom = $index >= $total - 4;
                    ?>
                        <a href="/jobs?category=<?php echo urlencode($cat['name']); ?>"
                            class="group flex flex-col items-center justify-center text-center aspect-square p-8
                              transition-all duration-200 hover:shadow-lg hover:z-10
                              <?php echo !$isTop   ? 'border-t' : ''; ?>
                              <?php echo !$isLeft  ? 'border-l' : ''; ?>
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

            <!-- CTA -->
            <div class="text-center mt-12">
                <a href="/categories"
                    class="inline-block border border-[#fb236a] text-[#fb236a] text-sm font-medium px-8 py-3 rounded
                hover:bg-[#fb236a] hover:text-white transition-all duration-200">
                    Browse All Categories
                </a>
            </div>

        </div>
    </section>



    <!-- ═══════════════════════════════════════════════════════════════
     SECTION 2 – CTA Banner
════════════════════════════════════════════════════════════════ -->
    <section class="p-[87px_0_113px] text-center relative w-screen mb-[70px] after:absolute after:inset-0 after:bg-[#232a79] after:opacity-80 after:content-[''] after:z-0" style="background-image: url(../assets/images/parallax1-pixelate.jpg);">
        <div class="max-w-6xl mx-auto">
            <div class="relative z-2">
                <div class="mb-[41px] text-center">
                    <h3 class="text-4xl text-semibold text-white">Employers: Find the Right Tech Talent</h3>
                    <span class="text-xl text-gray-300 mt-2 inline-block">Hiring the right tech talent is tough, but we're here to help.</span>
                </div>
                <div class="action">
                    <a class="font-quicksand inline-block font-bold text-center whitespace-nowrap align-middle select-none border-2 border-white transition-colors duration-150 ease-in-out px-[36px] py-[15px] text-[16px] leading-none rounded-lg text-white hover:bg-white hover:text-[#232a79]" href="/post-a-job">Post a Job</a>
                </div>
            </div>
        </div>
    </section>


    <!-- ═══════════════════════════════════════════════════════════════
     SECTION 3 – Featured Jobs
════════════════════════════════════════════════════════════════ -->
    <section class="bg-white py-16 px-4">
        <div class="max-w-6xl mx-auto">

            <!-- Heading -->
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold text-gray-800 mb-2" style="font-family:'Montserrat',sans-serif;">
                    Featured Jobs
                </h2>
                <p class="text-gray-500 text-sm">Leading Employers already using Mboka Kenya.</p>
            </div>

            <!-- Results count -->
            <div class="flex items-center gap-3 mb-4">
                <p class="text-sm font-semibold text-gray-800">
                    Showing <?php echo $showing_from; ?> – <?php echo $showing_to; ?> of <?php echo number_format($total_jobs); ?> jobs
                </p>
                <a href="/jobs" class="text-xs text-gray-500 border border-gray-300 rounded px-2 py-0.5 hover:border-gray-500 transition-colors">
                    view all
                </a>
            </div>

            <?php if (empty($jobs)): ?>
                <p class="text-center text-gray-400 py-10">No jobs available at the moment. Check back soon!</p>
            <?php else: ?>
                <!-- Job list -->
                <?php foreach ($jobs as $job):
                    $logoStyle = companyLogoStyle($job['company']);
                    $initial   = strtoupper(substr($job['company'], 0, 2));
                ?>
                    <a href="/jobs/<?php echo (int)$job['job_id']; ?>"
                        class="group flex items-center gap-4 px-6 py-7 transition-all duration-200
                      hover:shadow-[0_4px_20px_rgba(0,0,0,0.08)] hover:z-10 hover:bg-gray-50/60 border-b border-gray-100">

                        <!-- Logo -->
                        <div class="shrink-0 w-16 h-16 rounded overflow-hidden border border-gray-100 flex items-center justify-center text-lg font-bold"
                            style="background-color:<?php echo $logoStyle['bg']; ?>; color:<?php echo $logoStyle['color']; ?>;">
                            <?php echo htmlspecialchars($initial); ?>
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 flex-wrap">
                                <h3 class="text-sm font-semibold text-[#fb236a] group-hover:underline truncate">
                                    <?php echo htmlspecialchars($job['title']); ?>
                                </h3>
                                <?php if ($job['featured']): ?>
                                    <i data-lucide="star" class="w-4 h-4 text-[#232a79] shrink-0" fill="#232a79"></i>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs text-[#fb236a] font-medium my-1">
                                <?php echo htmlspecialchars($job['company']); ?>
                            </p>
                            <div class="flex items-center gap-1 mt-1.5 text-xs text-gray-500">
                                <i data-lucide="map-pin" class="w-3 h-3 shrink-0"></i>
                                <span><?php echo htmlspecialchars($job['location']); ?></span>
                            </div>
                        </div>

                        <!-- Badge + time -->
                        <div class="shrink-0 text-right flex flex-col items-end gap-2">
                            <span class="border text-xs font-medium px-3 py-1 rounded <?php echo typeBadgeClass($job['type']); ?>">
                                <?php echo htmlspecialchars($job['type']); ?>
                            </span>
                            <span class="text-xs text-gray-400"><?php echo htmlspecialchars($job['posted']); ?></span>
                        </div>

                    </a>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Pagination hint -->
            <div class="flex justify-center gap-2 mt-8">
                <?php foreach (range(1, min(5, (int)ceil($total_jobs / 10))) as $p): ?>
                    <a href="/jobs?page=<?php echo $p; ?>"
                        class="w-8 h-8 flex items-center justify-center text-xs rounded border
                  <?php echo $p === 1 ? 'bg-[#fb236a] text-white border-[#fb236a]' : 'border-gray-300 text-gray-600 hover:border-[#fb236a] hover:text-[#fb236a]'; ?>
                  transition-colors duration-200">
                        <?php echo $p; ?>
                    </a>
                <?php endforeach; ?>
                <?php if ($total_jobs > 50): ?>
                    <a href="/jobs?page=6"
                        class="w-8 h-8 flex items-center justify-center text-xs rounded border border-gray-300 text-gray-600
                    hover:border-[#fb236a] hover:text-[#fb236a] transition-colors duration-200">
                        <i data-lucide="chevron-right" class="w-3 h-3"></i>
                    </a>
                <?php endif; ?>
            </div>

        </div>
    </section>


    <!-- ═══════════════════════════════════════════════════════════════
     SECTION – Companies We've Helped
════════════════════════════════════════════════════════════════ -->
    <section class="py-20 px-4">
        <div class="max-w-5xl mx-auto">

            <!-- Heading -->
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-800 mb-2" style="font-family:'Montserrat',sans-serif;">
                    Companies We've Helped
                </h2>
                <p class="text-gray-500 text-sm">Some of the companies we've worked with to help recruit excellent talent.</p>
            </div>

            <!-- Logo cards -->
            <div class="flex flex-wrap items-center justify-center gap-5">
                <?php foreach ($companies as $company):
                    $style = companyLogoStyle($company['name']);
                ?>
                    <div class="group w-44 h-28 bg-white rounded-xl border border-gray-100
                    flex items-center justify-center
                    shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="flex flex-col items-center gap-1">
                            <span class="w-12 h-12 rounded-full flex items-center justify-center text-xl font-bold"
                                style="background-color:<?php echo $style['bg']; ?>; color:<?php echo $style['color']; ?>; border: 1.5px solid #e5e7eb;">
                                <?php echo htmlspecialchars($company['initial']); ?>
                            </span>
                            <span class="text-[11px] text-gray-500 font-medium text-center leading-tight px-2">
                                <?php echo htmlspecialchars($company['name']); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>
    </section>


    <!-- ═══════════════════════════════════════════════════════════════
     SECTION – Got a Question?
════════════════════════════════════════════════════════════════ -->
    <section class="bg-[#232a79] py-14 px-4 mt-4">
        <div class="max-w-5xl mx-auto text-center">
            <h2 class="text-2xl font-bold text-white mb-2" style="font-family:'Montserrat',sans-serif;">
                Got a question?
            </h2>
            <p class="text-white/80 text-sm">
                We're here to help. Send us an email at
                <a href="mailto:hello@mbokakenya.com"
                    class="text-white underline underline-offset-2 hover:text-white/90 transition-colors">
                    hello@mbokakenya.com
                </a>
            </p>
        </div>
    </section>
</main>

<script>
    document.getElementById('searchForm').addEventListener('submit', function(e) {
        const keyword = document.getElementById('keyword').value.trim();
        const city    = document.getElementById('city').value.trim();
        if (!keyword && !city) {
            e.preventDefault();
            alert('Please fill in at least one search field.');
        }
    });
</script>

<?php include_once 'partials/footer.php'; ?>