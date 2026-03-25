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

if ($job_id <= 0) {
    http_response_code(404);
    include_once 'views/404.php';
    exit;
}

try {
    $db = getDB();

    // Fetch job details
    $stmt = $db->prepare("
        SELECT
            j.job_id,
            j.title,
            j.job_type AS type,
            j.location,
            j.description,
            j.salary_min,
            j.salary_max,
            ep.company_name

            FROM jobs j
            JOIN employer_profiles ep ON j.employer_id = ep.user_id
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

    $job = [
        'job_id'    => $row['job_id'],
        'title'     => $row['title'],
        'type'      => $row['type'],
        'location'  => $row['location'],
        'salary'    => ($row['salary_min'] && $row['salary_max'])
            ? 'KES ' . number_format($row['salary_min']) . ' – ' . number_format($row['salary_max'])
            : null,
        'company'   => $row['company_name'],
    ];

    // Check if user has already applied
    $seeker_id = $_SESSION['user_id'] ?? null;
    $already_applied = false;
    $current_resume = null;

    if ($seeker_id) {
        // Check if already applied
        $checkStmt = $db->prepare("
            SELECT 1 FROM applications
            WHERE job_id = ? AND seeker_id = ?
        ");
        $checkStmt->execute([$job_id, $seeker_id]);
        $already_applied = (bool)$checkStmt->fetch();

        // Fetch current resume from profile
        $resumeStmt = $db->prepare("
            SELECT resume_path FROM job_seeker_profiles
            WHERE user_id = ?
        ");
        $resumeStmt->execute([$seeker_id]);
        $profileData = $resumeStmt->fetch(PDO::FETCH_ASSOC);
        $current_resume = $profileData['resume_path'] ?? null;
    }
} catch (Exception $e) {
    http_response_code(500);
    include_once 'views/404.php';
    exit;
}
?>
<?php include_once 'partials/header.php'; ?>

<main class="w-full">
    <!-- Hero Banner -->
    <section class="relative w-full overflow-hidden" style="height:280px;">
        <div class="absolute inset-0" style="background: linear-gradient(135deg, #8b91dd 0%, #10195d 70%, #10195d 100%); opacity:0.92;"></div>
        <div class="absolute inset-0 flex flex-col items-center justify-end pb-10 z-10 px-4">
            <h1 class="text-2xl md:text-4xl font-bold text-white mb-3 tracking-tight text-center">
                Apply for: <?php echo htmlspecialchars($job['title']); ?>
            </h1>
            <p class="text-gray-300 text-sm">
                <span class="flex items-center gap-1.5">
                    <?php echo htmlspecialchars($job['company']); ?>
                </span>
            </p>
        </div>
    </section>

    <div class="min-h-screen py-12 px-4">
        <div class="max-w-2xl mx-auto">

            <!-- Job Summary Card -->
            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-8 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($job['title']); ?></h2>
                        <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($job['company']); ?></p>
                        <div class="flex flex-wrap gap-3 mt-3">
                            <span class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 bg-gray-100 text-gray-700 rounded-full">
                                <i data-lucide="briefcase" class="w-3.5 h-3.5"></i>
                                <?php echo htmlspecialchars($job['type']); ?>
                            </span>
                            <?php if ($job['location']): ?>
                                <span class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 bg-gray-100 text-gray-700 rounded-full">
                                    <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                                    <?php echo htmlspecialchars($job['location']); ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($job['salary']): ?>
                                <span class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 bg-gray-100 text-gray-700 rounded-full">
                                    <i data-lucide="banknote" class="w-3.5 h-3.5"></i>
                                    <?php echo htmlspecialchars($job['salary']); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <a href="/jobs/<?php echo (int)$job['job_id']; ?>"
                        class="self-start sm:self-center px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        View Details
                    </a>
                </div>
            </div>

            <?php if ($already_applied): ?>
                <!-- Already Applied Message -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-8">
                    <div class="flex gap-4">
                        <i data-lucide="alert-circle" class="w-6 h-6 text-yellow-600 shrink-0 mt-0.5"></i>
                        <div>
                            <h3 class="font-semibold text-yellow-900 mb-1">Already Applied</h3>
                            <p class="text-sm text-yellow-800">You have already submitted an application for this position. You can only submit one application per job.</p>
                            <a href="/seeker/applications" class="inline-block mt-3 text-sm font-medium text-yellow-700 hover:text-yellow-900">
                                View your applications →
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Application Form -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <!-- Form Header -->
                    <div class="px-8 py-6 border-b border-gray-200 bg-gray-50">
                        <h2 class="text-lg font-bold text-gray-900">Submit Your Application</h2>
                        <p class="text-sm text-gray-600 mt-1">Share your resume and tell the employer why you're a great fit for this role.</p>
                    </div>

                    <!-- Form Body -->
                    <form method="POST" action="php/functions/apply.php" enctype="multipart/form-data" class="px-8 py-8 space-y-6">
                        <input type="hidden" name="job_id" value="<?php echo (int)$job['job_id']; ?>">

                        <!-- Resume Upload -->
                        <div>
                            <label for="resume" class="block text-sm font-semibold text-gray-900 mb-2">
                                Resume <span class="text-red-500">*</span>
                            </label>

                            <?php if ($current_resume): ?>
                                <!-- Current Resume Section -->
                                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                                    <div class="flex items-start gap-3">
                                        <i data-lucide="check-circle" class="w-5 h-5 text-green-600 shrink-0 mt-0.5"></i>
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-green-900">Current Resume on File</p>
                                            <p class="text-xs text-green-700 mt-1">
                                                <?php echo htmlspecialchars(basename($current_resume)); ?>
                                            </p>
                                            <p class="text-xs text-green-600 mt-2">We'll use this resume for your application unless you upload a new one.</p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <p class="text-xs text-gray-500 mb-3">
                                <?php echo $current_resume ? 'Upload a new resume to replace your current one (optional), or leave blank to use your current resume.' : 'Upload your resume in PDF, DOC, or DOCX format. Max size: 10 MB.'; ?>
                            </p>
                            <div class="relative">
                                <input
                                    type="file"
                                    id="resume"
                                    name="resume"
                                    accept=".pdf,.doc,.docx"
                                    <?php echo !$current_resume ? 'required' : ''; ?>
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                    aria-label="Upload resume">
                                <div class="px-6 py-8 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer text-center">
                                    <i data-lucide="upload-cloud" class="w-8 h-8 text-gray-400 mx-auto mb-2"></i>
                                    <p class="text-sm font-medium text-gray-700">
                                        <span class="text-[#fb236a] font-semibold">Click to upload</span> or drag and drop
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">PDF, DOC or DOCX (up to 10 MB)</p>
                                </div>
                            </div>
                            <p id="fileName" class="text-xs text-gray-600 mt-2 hidden">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5 inline text-green-600 mr-1"></i>
                                <span id="fileNameText"></span>
                            </p>
                        </div>

                        <!-- Cover Letter -->
                        <div>
                            <label for="cover_letter" class="block text-sm font-semibold text-gray-900 mb-2">
                                Cover Letter <span class="text-red-500">*</span>
                            </label>
                            <p class="text-xs text-gray-500 mb-3">
                                Tell the employer why you're interested in this role and what makes you a great fit. Minimum 50 characters.
                            </p>
                            <textarea
                                id="cover_letter"
                                name="cover_letter"
                                rows="8"
                                placeholder="Dear hiring manager,&#10;&#10;I am writing to express my strong interest in the {{POSITION}} role at {{COMPANY}}...&#10;&#10;Best regards,&#10;[Your Name]"
                                required
                                minlength="50"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#fb236a] focus:border-transparent resize-none text-sm text-gray-900 placeholder-gray-400"></textarea>
                            <div class="flex items-center justify-between mt-2">
                                <p class="text-xs text-gray-500">
                                    Minimum 50 characters required
                                </p>
                                <p class="text-xs text-gray-600">
                                    <span id="charCount">0</span> / 50 characters
                                </p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-gray-200">
                            <a href="/jobs/<?php echo (int)$job['job_id']; ?>"
                                class="flex-1 text-center px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                                Cancel
                            </a>
                            <button
                                type="submit"
                                class="flex-1 px-6 py-3 bg-[#fb236a] hover:bg-[#e01060] text-white font-semibold rounded-lg shadow transition-colors duration-200 flex items-center justify-center gap-2">
                                <i data-lucide="send" class="w-4 h-4"></i>
                                Submit Application
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Help Section -->
                <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <h3 class="text-sm font-semibold text-blue-900 mb-3 flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4"></i>
                        Tips for a Strong Application
                    </h3>
                    <ul class="space-y-2 text-sm text-blue-800">
                        <li class="flex gap-2">
                            <span class="shrink-0">•</span>
                            <span><strong>Personalize your cover letter:</strong> Address it to the hiring manager and mention specific details about the role.</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="shrink-0">•</span>
                            <span><strong>Highlight relevant experience:</strong> Focus on skills and achievements that match the job requirements.</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="shrink-0">•</span>
                            <span><strong>Keep it concise:</strong> Use clear, professional language and proper formatting.</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="shrink-0">•</span>
                            <span><strong>Proofread carefully:</strong> Check for spelling and grammar errors before submitting.</span>
                        </li>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Back to Job -->
            <div class="mt-8 text-center">
                <a href="/jobs/<?php echo (int)$job['job_id']; ?>" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-[#2b9a66] transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Back to job posting
                </a>
            </div>
        </div>
    </div>
</main>

<script>
    // File name display
    const resumeInput = document.getElementById('resume');
    const fileNameDisplay = document.getElementById('fileName');
    const fileNameText = document.getElementById('fileNameText');

    resumeInput?.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            fileNameText.textContent = this.files[0].name;
            fileNameDisplay.classList.remove('hidden');
        } else {
            fileNameDisplay.classList.add('hidden');
        }
    });

    // Character counter for cover letter
    const coverLetterInput = document.getElementById('cover_letter');
    const charCount = document.getElementById('charCount');

    coverLetterInput?.addEventListener('input', function() {
        charCount.textContent = this.value.length;
    });
</script>

<?php include_once 'partials/footer.php'; ?>
