<?php
// ── Live Data ─────────────────────────────────────────────────────────────────
// Must run BEFORE header (which starts the session and loads connection)
require_once 'php/config/connection.php';

$id = $_ROUTE['id'] ?? '';
if (!ctype_digit($id)) {
    http_response_code(404);
    exit('Invalid job ID');
}
$job_id = (int)$id;
error_log($job_id);
error_log($_ROUTE['id']);

if ($job_id <= 0) {
    http_response_code(404);
    include_once 'views/404.php';
    exit;
}

try {
    $db = getDB();


    $stmt = $db->prepare("
        SELECT
            j.job_id,
            j.title,
            j.job_type        AS type,
            j.location,
            j.description,
            j.salary_min,
            j.salary_max,
            j.featured,
            j.experience_level AS experience,
            j.required_qualification AS qualification,
            j.years_experience_min,
            j.years_experience_max,

                CASE
                    WHEN j.years_experience_min IS NOT NULL AND j.years_experience_max IS NOT NULL
                THEN CONCAT(j.years_experience_min, ' - ', j.years_experience_max, ' years')
                    WHEN j.years_experience_min IS NOT NULL
                THEN CONCAT(j.years_experience_min, '+ years')
                ELSE 'Not specified'
                END AS experience_range,

            j.status,
            j.created_at,

            DATE_FORMAT(j.deadline, '%M %e, %Y') AS deadline,

                CASE
                    WHEN j.created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                THEN 'just now'
                    WHEN j.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                THEN CONCAT(HOUR(TIMEDIFF(NOW(), j.created_at)), ' hours ago')
                    WHEN j.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                THEN CONCAT(DATEDIFF(NOW(), j.created_at), ' days ago')
                ELSE DATE_FORMAT(j.created_at, '%M %e, %Y')
                END AS posted,

            ep.company_name,
            ep.website,
            ep.industry,
            ep.description AS employer_bio,

            jc.category_name

            FROM jobs j
            JOIN employer_profiles ep ON j.employer_id = ep.user_id
            LEFT JOIN job_categories jc ON j.category_id = jc.category_id
            WHERE j.job_id = ?
            AND j.status = 'open'
    ");
    $stmt->execute([$job_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        http_response_code(404);
        include_once 'views/404.php';
        exit;
    }

    // ── Map DB row to template-friendly shape ─────────────────────────────────
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
    $logoStyle = $palettes[abs(crc32($row['company_name'])) % count($palettes)];

    $job = [
        'job_id'        => $row['job_id'],
        'title'         => $row['title'],
        'type'          => $row['type'],
        'location'      => $row['location'],
        'posted'        => $row['posted'],
        'deadline'      => $row['deadline'] ?? 'Open until filled',
        'career_level'  => $row['experience']  ?? 'Not specified',
        'industry'      => $row['industry']       ?? ($row['category_name'] ?? 'Not specified'),
        'experience'    => $row['experience_range']     ?? 'Not specified',
        'qualification' => $row['qualification']  ?? 'Not specified',
        'description'   => $row['description']    ?? '',
        'salary'        => ($row['salary_min'] && $row['salary_max'])
            ? 'KES ' . number_format($row['salary_min']) . ' – ' . number_format($row['salary_max'])
            : null,
        'employer' => [
            'name'       => $row['company_name'],
            'tagline'    => $row['employer_bio'] ? mb_substr(strip_tags($row['employer_bio']), 0, 80) . '…' : '',
            'website'    => $row['website'] ?? '#',
            'logo'       => strtoupper(substr($row['company_name'], 0, 2)),
            'logo_bg'    => $logoStyle['bg'],
            'logo_color' => $logoStyle['color'],
        ],
    ];

    $overview_items = [
        ['icon' => 'bar-chart-2',    'label' => 'Career Level',  'value' => $job['career_level']],
        ['icon' => 'building-2',     'label' => 'Industry',       'value' => $job['industry']],
        ['icon' => 'sliders',        'label' => 'Experience',     'value' => $job['experience']],
        ['icon' => 'graduation-cap', 'label' => 'Qualification',  'value' => $job['qualification']],
    ];
    if ($job['salary']) {
        $overview_items[] = ['icon' => 'banknote', 'label' => 'Salary', 'value' => $job['salary']];
    }
} catch (Exception $e) {
    http_response_code(500);
    include_once 'views/404.php';
    exit;
}
?>
<?php include_once 'partials/header.php'; ?>

<main class="w-full">
    <section class="relative w-full overflow-hidden" style="height:280px;">
        <div class="absolute inset-0" style="background: linear-gradient(135deg, #8b91dd 0%, #10195d 70%, #10195d 100%); opacity:0.92;"></div>
        <div class="absolute inset-0 flex flex-col items-center justify-end pb-10 z-10 px-4">
            <h1 class="text-2xl md:text-4xl font-bold text-white mb-3 tracking-tight text-center">
                <?php echo htmlspecialchars($job['title']); ?>
            </h1>
            <p class="text-gray-300 text-sm flex flex-wrap items-center gap-3 justify-center">
                <span class="border border-white py-1 px-4 rounded-full text-sm"><?php echo htmlspecialchars($job['type']); ?></span>
                <span class="flex items-center gap-1.5">
                    <i data-lucide="map-pin" class="h-4 w-4"></i>
                    <?php echo htmlspecialchars($job['location']); ?>
                </span>
                <span class="flex items-center gap-1.5">
                    <i data-lucide="calendar" class="h-4 w-4"></i>
                    <?php echo htmlspecialchars($job['posted']); ?>
                </span>
            </p>
        </div>
    </section>


    <div class="min-h-screen py-8 px-4">
        <div class="max-w-6xl mx-auto flex flex-col lg:flex-row gap-6 items-start">

            <!-- ── Left: Job Content ─────────────────────────────────────── -->
            <div class="flex-1 min-w-0">

                <!-- Employer card -->
                <div class="bg-white border-b border-gray-200 p-6 mb-6 flex items-start gap-5">
                    <div class="shrink-0 w-20 h-20 rounded-lg border border-gray-100 flex items-center justify-center
                        text-base font-bold shadow-sm"
                        style="background-color:<?php echo $job['employer']['logo_bg']; ?>; color:<?php echo $job['employer']['logo_color']; ?>;">
                        <?php echo htmlspecialchars($job['employer']['logo']); ?>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900"><?php echo htmlspecialchars($job['employer']['name']); ?></h2>
                        <?php if ($job['employer']['tagline']): ?>
                            <p class="text-sm text-gray-500 mt-0.5"><?php echo htmlspecialchars($job['employer']['tagline']); ?></p>
                        <?php endif; ?>
                        <?php if ($job['employer']['website'] && $job['employer']['website'] !== '#'): ?>
                            <a href="<?php echo htmlspecialchars($job['employer']['website']); ?>" target="_blank" rel="noopener noreferrer"
                                class="flex items-center gap-1.5 mt-2 text-sm text-[#2b9a66] hover:underline">
                                <i data-lucide="link" class="w-3.5 h-3.5"></i>
                                <?php echo htmlspecialchars($job['employer']['website']); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Job description -->
                <div class="bg-white p-8">

                    <h2 class="text-xl font-bold text-gray-900 mb-4">Job Description</h2>

                    <?php if ($job['description']): ?>
                        <!-- Render description — strip_tags guards against raw HTML stored in DB -->
                        <div class="text-sm text-gray-600 leading-relaxed space-y-3 mb-6">
                            <?php
                            // If description is plain text, preserve line breaks as paragraphs
                            $paragraphs = array_filter(array_map('trim', explode("\n", $job['description'])));
                            foreach ($paragraphs as $para): ?>
                                <p><?php echo htmlspecialchars($para); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-sm text-gray-400 italic mb-6">No description available for this role.</p>
                    <?php endif; ?>

                    <!-- Back to jobs -->
                    <div class="mt-8 pt-6 border-t border-gray-100">
                        <a href="/jobs" class="flex items-center gap-2 text-sm text-gray-500 hover:text-[#2b9a66] transition-colors">
                            <i data-lucide="arrow-left" class="w-4 h-4"></i>
                            Back to all jobs
                        </a>
                    </div>
                </div>
            </div>

            <!-- ── Right: Sidebar ───────────────────────────────────────── -->
            <div class="w-full lg:w-72 shrink-0 lg:sticky lg:top-24 flex flex-col gap-5">

                <!-- Apply button -->
                <a href="/jobs/<?php echo (int)$job['job_id']; ?>/apply"
                    class="w-full block text-center bg-[#fb236a] hover:bg-[#e01060] text-white font-semibold text-base
                py-4 rounded-lg shadow transition-colors duration-200">
                    Apply for this job
                </a>

                <!-- Job Overview -->
                <div class="bg-white px-6 py-5 rounded-lg border border-gray-100 shadow-sm">
                    <h3 class="text-base font-bold text-gray-900 mb-4">Job Overview</h3>
                    <div class="flex flex-col divide-y divide-gray-100">
                        <?php foreach ($overview_items as $item): ?>
                            <div class="flex items-start gap-3.5 py-4 first:pt-0 last:pb-0">
                                <i data-lucide="<?php echo $item['icon']; ?>"
                                    class="w-6 h-6 text-[#2b9a66] shrink-0 mt-0.5" stroke-width="1.75"></i>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($item['label']); ?></p>
                                    <p class="text-xs text-gray-500 mt-0.5 leading-snug"><?php echo htmlspecialchars($item['value']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Job Location -->
                <div class="bg-white px-6 py-5 rounded-lg border border-gray-100 shadow-sm">
                    <h3 class="text-base font-bold text-gray-900 mb-4">Job Location</h3>
                    <!-- Map placeholder — swap for a Google Maps iframe with the location -->
                    <div class="w-full h-40 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400">
                        <div class="flex flex-col items-center gap-2 text-xs">
                            <i data-lucide="map" class="w-8 h-8 text-gray-300"></i>
                            <span><?php echo htmlspecialchars($job['location']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Deadline -->
                <div class="bg-white px-6 py-4 rounded-lg border border-gray-100 shadow-sm flex items-center gap-3">
                    <i data-lucide="calendar-clock" class="w-5 h-5 text-[#2b9a66] shrink-0"></i>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">Application Deadline</p>
                        <p class="text-sm font-semibold text-gray-800 mt-0.5"><?php echo htmlspecialchars($job['deadline']); ?></p>
                    </div>
                </div>

            </div>
        </div>
    </div>

</main>

<?php include_once 'partials/footer.php'; ?>
