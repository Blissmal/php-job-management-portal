<?php
require_once 'php/config/connection.php';

$id = $_ROUTE['id'] ?? '';
if (!ctype_digit($id)) {
    http_response_code(404);
    exit('Invalid job ID');
}
$job_id = (int)$id;

// Get current user info for edit button visibility
$current_user_id   = $_SESSION['user_id'] ?? null;
$current_user_role = $_SESSION['role']    ?? null;
$is_employer       = $current_user_role === 'employer';
$is_owner          = false;

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
            j.employer_id,
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

    // Check if current user is the owner of this job
    $is_owner = ($is_employer && $current_user_id === (int)$row['employer_id']);

    // Check if job is saved (for seekers)
    $is_saved = false;
    if ($current_user_id && $current_user_role === 'seeker') {
        $checkSaved = $db->prepare("SELECT save_id FROM saved_jobs WHERE seeker_id = ? AND job_id = ?");
        $checkSaved->execute([$current_user_id, $job_id]);
        $is_saved = (bool)$checkSaved->fetch();
    }

    // Check if seeker has already applied to this job
    $existing_application = null;
    if ($current_user_id && $current_user_role === 'seeker') {
        $checkApp = $db->prepare("SELECT app_id, status, applied_at FROM applications WHERE seeker_id = ? AND job_id = ?");
        $checkApp->execute([$current_user_id, $job_id]);
        $existing_application = $checkApp->fetch(PDO::FETCH_ASSOC) ?: null;
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
        'career_level'  => $row['experience']      ?? 'Not specified',
        'industry'      => $row['industry']         ?? ($row['category_name'] ?? 'Not specified'),
        'experience'    => $row['experience_range'] ?? 'Not specified',
        'qualification' => $row['qualification']    ?? 'Not specified',
        'description'   => $row['description']      ?? '',
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
        ['icon' => 'building-2',     'label' => 'Industry',      'value' => $job['industry']],
        ['icon' => 'sliders',        'label' => 'Experience',    'value' => $job['experience']],
        ['icon' => 'graduation-cap', 'label' => 'Qualification', 'value' => $job['qualification']],
    ];
    if ($job['salary']) {
        $overview_items[] = ['icon' => 'banknote', 'label' => 'Salary', 'value' => $job['salary']];
    }
} catch (Exception $e) {
    http_response_code(500);
    include_once 'views/404.php';
    exit;
}

function renderMarkdown(string $text): string
{
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

    $text = preg_replace('/^### (.+)$/m',  '<h3>$1</h3>', $text);
    $text = preg_replace('/^## (.+)$/m',   '<h2>$1</h2>', $text);
    $text = preg_replace('/^# (.+)$/m',    '<h2>$1</h2>', $text);

    $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
    $text = preg_replace('/_(.+?)_/',       '<em>$1</em>',         $text);

    $text = preg_replace('/^\d+\. (.+)$/m', '<li class="__ol">$1</li>', $text);
    $text = preg_replace('/^[-*] (.+)$/m',  '<li class="__ul">$1</li>', $text);

    $text = preg_replace_callback(
        '/(<li class="__ol">.*?<\/li>\n?)+/s',
        fn($m) => '<ol>' . str_replace(' class="__ol"', '', $m[0]) . '</ol>',
        $text
    );
    $text = preg_replace_callback(
        '/(<li class="__ul">.*?<\/li>\n?)+/s',
        fn($m) => '<ul>' . str_replace(' class="__ul"', '', $m[0]) . '</ul>',
        $text
    );

    $lines  = explode("\n", $text);
    $output = '';
    $buffer = '';

    foreach ($lines as $line) {
        $trimmed = trim($line);

        if (preg_match('/^<(h[123456]|ul|ol|li)[\s>]/', $trimmed) || $trimmed === '') {
            if ($buffer !== '') {
                $output .= '<p>' . trim($buffer) . '</p>' . "\n";
                $buffer  = '';
            }
            if ($trimmed !== '') $output .= $trimmed . "\n";
        } else {
            $buffer .= ($buffer ? ' ' : '') . $trimmed;
        }
    }

    if ($buffer !== '') {
        $output .= '<p>' . trim($buffer) . '</p>';
    }

    return $output;
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
                        <div class="job-description text-sm text-gray-700 leading-relaxed mb-6">
                            <?= renderMarkdown($job['description']) ?>
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

                <!-- ── CTA Block ──────────────────────────────────────────── -->

                <?php if ($is_owner): ?>
                    <!-- Employer who owns this job → Edit -->
                    <a href="/employer/jobs/<?php echo (int)$job['job_id']; ?>/edit"
                        class="w-full block text-center bg-[#2b9a66] hover:bg-[#1e7047] text-white font-semibold text-base
                               py-4 rounded-lg shadow transition-colors duration-200 flex items-center justify-center gap-2">
                        <i data-lucide="edit" class="w-5 h-5"></i>
                        Edit Job
                    </a>

                <?php elseif ($current_user_role === 'employer'): ?>
                    <!-- Another employer → blocked -->
                    <div class="w-full bg-gray-100 border border-gray-200 text-gray-500 text-sm font-medium
                                py-4 px-4 rounded-lg flex items-center justify-center gap-2">
                        <i data-lucide="ban" class="w-4 h-4"></i>
                        Employers cannot apply for jobs
                    </div>

                <?php elseif ($current_user_role === 'seeker' && $existing_application): ?>
                    <!-- Seeker already applied → status banner + optional withdraw -->
                    <?php
                        $appStatus   = $existing_application['status'];
                        $appliedDate = date('M j, Y', strtotime($existing_application['applied_at']));
                        $statusConfig = [
                            'Pending'     => ['bg' => 'bg-amber-50',  'border' => 'border-amber-300',  'text' => 'text-amber-700',  'icon' => 'clock',        'label' => 'Application Pending'],
                            'Reviewed'    => ['bg' => 'bg-blue-50',   'border' => 'border-blue-300',   'text' => 'text-blue-700',   'icon' => 'eye',          'label' => 'Under Review'],
                            'Shortlisted' => ['bg' => 'bg-indigo-50', 'border' => 'border-indigo-300', 'text' => 'text-indigo-700', 'icon' => 'star',         'label' => 'Shortlisted!'],
                            'Hired'       => ['bg' => 'bg-green-50',  'border' => 'border-green-300',  'text' => 'text-green-700',  'icon' => 'check-circle', 'label' => "You're Hired!"],
                            'Rejected'    => ['bg' => 'bg-red-50',    'border' => 'border-red-300',    'text' => 'text-red-700',    'icon' => 'x-circle',     'label' => 'Not Selected'],
                        ];
                        $sc = $statusConfig[$appStatus] ?? $statusConfig['Pending'];
                    ?>
                    <div class="w-full <?php echo $sc['bg']; ?> border <?php echo $sc['border']; ?> rounded-lg px-4 py-4">
                        <div class="flex items-center gap-2 <?php echo $sc['text']; ?> font-semibold text-sm mb-1">
                            <i data-lucide="<?php echo $sc['icon']; ?>" class="w-4 h-4 shrink-0"></i>
                            <?php echo $sc['label']; ?>
                        </div>
                        <p class="text-xs text-gray-500">Applied on <?php echo $appliedDate; ?></p>

                        <?php if ($appStatus === 'Pending'): ?>
                            <!-- Hidden form — submitted programmatically after SweetAlert confirm -->
                            <form id="withdraw-form" method="POST" action="/php/functions/withdraw.php">
                                <input type="hidden" name="app_id"   value="<?php echo (int)$existing_application['app_id']; ?>">
                                <input type="hidden" name="redirect" value="/jobs/<?php echo (int)$job['job_id']; ?>">
                            </form>
                            <button
                                type="button"
                                onclick="confirmWithdraw()"
                                class="mt-3 w-full border border-red-300 text-red-600 hover:bg-red-50 text-xs font-semibold
                                       py-2 px-3 rounded-md transition-colors duration-200 flex items-center justify-center gap-1.5">
                                <i data-lucide="undo-2" class="w-3.5 h-3.5"></i>
                                Withdraw Application
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Save job still available even after applying -->
                    <form method="POST" action="/save-job">
                        <input type="hidden" name="job_id"   value="<?php echo (int)$job['job_id']; ?>">
                        <input type="hidden" name="redirect" value="/jobs/<?php echo (int)$job['job_id']; ?>">
                        <button type="submit"
                            class="w-full px-4 py-3 border-2 font-semibold text-sm rounded-lg transition-colors duration-200
                                   flex items-center justify-center gap-2
                                   <?php echo $is_saved ? 'border-[#fb236a] text-[#fb236a] bg-pink-50' : 'border-gray-300 text-gray-700 hover:border-[#fb236a] hover:text-[#fb236a]'; ?>">
                            <i data-lucide="bookmark" class="w-4 h-4"></i>
                            <?php echo $is_saved ? 'Saved' : 'Save Job'; ?>
                        </button>
                    </form>

                <?php elseif ($current_user_role === 'seeker'): ?>
                    <!-- Seeker not yet applied -->
                    <div class="flex flex-col gap-3">
                        <a href="/jobs/<?php echo (int)$job['job_id']; ?>/apply"
                            class="w-full block text-center bg-[#fb236a] hover:bg-[#e01060] text-white font-semibold text-base
                                   py-4 rounded-lg shadow transition-colors duration-200">
                            Apply for this job
                        </a>
                        <form method="POST" action="/save-job">
                            <input type="hidden" name="job_id"   value="<?php echo (int)$job['job_id']; ?>">
                            <input type="hidden" name="redirect" value="/jobs/<?php echo (int)$job['job_id']; ?>">
                            <button type="submit"
                                class="w-full px-4 py-3 border-2 font-semibold text-sm rounded-lg transition-colors duration-200
                                       flex items-center justify-center gap-2
                                       <?php echo $is_saved ? 'border-[#fb236a] text-[#fb236a] bg-pink-50' : 'border-gray-300 text-gray-700 hover:border-[#fb236a] hover:text-[#fb236a]'; ?>">
                                <i data-lucide="bookmark" class="w-4 h-4"></i>
                                <?php echo $is_saved ? 'Saved' : 'Save Job'; ?>
                            </button>
                        </form>
                    </div>

                <?php else: ?>
                    <!-- Guest / not logged in -->
                    <div class="flex flex-col gap-3">
                        <a href="/login?redirect=<?php echo urlencode('/jobs/' . (int)$job['job_id']); ?>"
                            class="w-full block text-center bg-[#fb236a] hover:bg-[#e01060] text-white font-semibold text-base
                                   py-4 rounded-lg shadow transition-colors duration-200">
                            Login to Apply
                        </a>
                        <a href="/register"
                            class="w-full block text-center border-2 border-gray-300 text-gray-700
                                   hover:border-[#fb236a] hover:text-[#fb236a] font-semibold text-sm
                                   py-3 rounded-lg transition-colors duration-200">
                            Create an Account
                        </a>
                    </div>
                <?php endif; ?>

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

<style>
    .job-description h2 {
        font-size: 1rem;
        font-weight: 700;
        color: #1e293b;
        margin-top: 1.25rem;
        margin-bottom: 0.4rem;
    }

    .job-description h3 {
        font-size: 0.9rem;
        font-weight: 700;
        color: #1e293b;
        margin-top: 1rem;
        margin-bottom: 0.3rem;
    }

    .job-description p {
        margin-bottom: 0.6rem;
        color: #4b5563;
    }

    .job-description ul {
        list-style: disc;
        padding-left: 1.4rem;
        margin-bottom: 0.75rem;
    }

    .job-description ol {
        list-style: decimal;
        padding-left: 1.4rem;
        margin-bottom: 0.75rem;
    }

    .job-description li {
        margin-bottom: 0.25rem;
        color: #4b5563;
    }

    .job-description strong {
        font-weight: 700;
        color: #1e293b;
    }

    .job-description em {
        font-style: italic;
    }
</style>

<?php if ($current_user_role === 'seeker' && $existing_application && $existing_application['status'] === 'Pending'): ?>
<script>
    function confirmWithdraw() {
        Swal.fire({
            icon: 'warning',
            title: 'Withdraw Application?',
            text: 'This will permanently remove your application for this job.',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#8b91dd',
            confirmButtonText: 'Yes, Withdraw',
            cancelButtonText: 'Cancel',
            background: '#fff',
            customClass: {
                popup: 'rounded-2xl',
                title: 'text-lg font-semibold',
                confirmButton: 'px-6 py-2 text-sm',
                cancelButton: 'px-6 py-2 text-sm'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('withdraw-form').submit();
            }
        });
    }
</script>
<?php endif; ?>

<?php include_once 'partials/footer.php'; ?>