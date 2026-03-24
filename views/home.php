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
            <form id="searchForm" action="/jobs" method="POST" class="flex flex-col md:flex-row gap-6 w-full px-8 md:px-20 max-w-5xl">
                <!-- Keyword Input -->
                <div class="basis-0 grow max-w-full relative flex items-center bg-white px-2 gap-3 rounded-md ">
                    <input
                        type="text"
                        name="keyword"
                        id="keyword"
                        placeholder="Job title, keywords..."
                        class="w-full  px-6 py-3 text-gray-800 focus:outline-none focus:ring-none "
                        required />
                    <i data-lucide="keyboard" class="w-8 h-8 text-[#fb236a]"></i>
                </div>

                <!-- City Input -->
                <div class="flex-none w-auto max-w-none relative flex items-center bg-white px-2 gap-3 rounded-md">
                    <input
                        type="text"
                        name="city"
                        id="city"
                        placeholder="City, province or region"
                        class="w-full px-6 py-3  text-gray-800 focus:outline-none focus:ring-none "
                        required />
                    <i data-lucide="map-pin" class="w-8 h-8 text-[#fb236a]"></i>
                </div>

                <!-- Search Button -->
                <div class="flex-none w-auto max-w-none relative flex items-center ">

                    <button
                        type="submit"
                        class="p-3 bg-[#fb236a] hover:bg-[#fb236a]/80 text-white font-semibold rounded-md transition-colors duration-200 flex items-center justify-center gap-2 max-md:w-full">
                        <span class="block md:hidden">Search</span>
                        <i data-lucide="search" class="w-8 h-8 "></i>
                    </button>
                </div>
            </form>
        </div>
        <div class=" absolute left-1/2 top-full -translate-x-1/2 -translate-y-1/2 rounded-full z-[2]">
            <a href="#jh-scroll-here" title="Scroll Button" class="group text-white w-[130px] h-[130px] border-2 border-white flex items-center justify-center rounded-full transition-all duration-300 ease-in-out hover:border-[#fb236a]">
                <span class="bg-white text-[#fb236a] p-5 rounded-full shadow-lg transition-colors duration-300 group-hover:bg-[#fb236a] group-hover:text-white">
                    <i data-lucide="arrow-down"></i>
                </span>
            </a>
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

            <!-- Heading -->
            <div class="text-center my-12">
                <h2 class="text-3xl font-bold text-gray-800 mb-2" style="font-family:'Montserrat',sans-serif;">
                    Popular Categories
                </h2>
                <p class="text-gray-500 text-sm">37 jobs live - 10 added today.</p>
            </div>

            <!-- Grid -->
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
    <section class="p-[87px_0_113px] text-center relative w-screen mb-[70px]  after:absolute after:inset-0 after:bg-[#232a79] after:opacity-80 after:content-[''] after:z-0" style="background-image: url( ../assets/images/parallax1-pixelate.jpg );">
        <div class="max-w-6xl mx-auto ">
            <div class="relative z-2 ">
                <div class="mb-[41px] text-center">
                    <h3 class="text-4xl text-semibold text-white">Employers: Find the Right Tech Talent</h3> <span class="text-xl text-gray-300 mt-2 inline-block ">Hiring the right tech talent is tough, but we're here to help. </span>
                </div>
                <div class="action"> <a class=" font-quicksand inline-block font-bold text-center whitespace-nowrap align-middle select-none border-2 border-white transition-colors duration-150 ease-in-out px-[36px] py-[15px] text-[16px] leading-none rounded-lg text-white" href="/post-a-job/">Post a Job</a></div>
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
                <p class="text-gray-500 text-sm">Leading Employers already using Coding Kenya.</p>
            </div>

            <!-- Results count -->
            <div class="flex items-center gap-3 mb-4">
                <p class="text-sm font-semibold text-gray-800">
                    Showing <?php echo $showing_from; ?> - <?php echo $showing_to; ?> of <?php echo number_format($total_jobs); ?> jobs
                </p>
                <a href="/jobs" class="text-xs text-gray-500 border border-gray-300 rounded px-2 py-0.5 hover:border-gray-500 transition-colors">
                    reset
                </a>
            </div>

            <!-- Job list -->

            <?php foreach ($jobs as $job): ?>
                <a href="/jobs/<?php echo urlencode(strtolower(str_replace(' ', '-', $job['title']))); ?>"
                    class="group flex items-center gap-4 px-6 py-7 transition-all duration-200
                  hover:shadow-[0_4px_20px_rgba(0,0,0,0.08)] hover:z-10 hover:bg-gray-50/60">

                    <!-- Logo -->
                    <div class="shrink-0 w-16 h-16 rounded overflow-hidden border border-gray-100 flex items-center justify-center text-lg font-bold"
                        style="background-color: <?php echo $job['logo_bg']; ?>; color: <?php echo $job['logo_color']; ?>;">
                        <?php echo $job['logo']; ?>
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


            <!-- Pagination hint -->
            <div class="flex justify-center gap-2 mt-8">
                <?php foreach (range(1, 5) as $p): ?>
                    <a href="/jobs?page=<?php echo $p; ?>"
                        class="w-8 h-8 flex items-center justify-center text-xs rounded border
                  <?php echo $p === 2 ? 'bg-[#fb236a] text-white border-[#fb236a]' : 'border-gray-300 text-gray-600 hover:border-[#fb236a] hover:text-[#fb236a]'; ?>
                  transition-colors duration-200">
                        <?php echo $p; ?>
                    </a>
                <?php endforeach; ?>
                <a href="/jobs?page=6"
                    class="w-8 h-8 flex items-center justify-center text-xs rounded border border-gray-300 text-gray-600
                hover:border-[#fb236a] hover:text-[#fb236a] transition-colors duration-200">
                    <i data-lucide="chevron-right" class="w-3 h-3"></i>
                </a>
            </div>

        </div>
    </section>


    <?php
    $companies = [
        ['name' => 'Cellulant',       'initial' => 'C', 'bg' => '#fff',    'color' => '#c0392b'],
        ['name' => 'Equity Bank',     'initial' => 'E', 'bg' => '#fff',    'color' => '#922b21'],
        ['name' => 'Google',          'initial' => 'G', 'bg' => '#fff',    'color' => '#4285F4'],
        ['name' => 'County Govt',     'initial' => 'K', 'bg' => '#1a5276', 'color' => '#fff'],
        ['name' => 'Konza Technopolis', 'initial' => 'K', 'bg' => '#fff',   'color' => '#1e8449'],
    ];
    ?>

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
                <?php foreach ($companies as $company): ?>
                    <div class="group w-44 h-28 bg-white rounded-xl border border-gray-100
                    flex items-center justify-center
                    shadow-sm hover:shadow-md transition-shadow duration-200">
                        <!-- Placeholder: swap the div below for a real <img> tag when logos are available -->
                        <div class="flex flex-col items-center gap-1">
                            <span class="w-12 h-12 rounded-full flex items-center justify-center text-xl font-bold"
                                style="background-color:<?php echo $company['bg']; ?>; color:<?php echo $company['color']; ?>; border: 1.5px solid #e5e7eb;">
                                <?php echo $company['initial']; ?>
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
        const city = document.getElementById('city').value.trim();

        // Validation: prevent blank submissions
        if (!keyword || !city) {
            e.preventDefault();
            alert('Please fill in both the job title/keywords and city fields.');
            return false;
        }
    });
</script>

<?php include_once 'partials/footer.php'; ?>
