<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$connectionPath = __DIR__ . '/../../php/config/connection.php';
if (file_exists($connectionPath)) require_once $connectionPath;

// Get job ID from route
$job_id = (int)($_ROUTE['id'] ?? 0);
if ($job_id <= 0) {
    header('Location: /employer/jobs');
    exit;
}

// Get current employer
$employer_id = $_SESSION['user_id'] ?? null;
if (!$employer_id) {
    header('Location: /login');
    exit;
}

// Fetch job details
$job = null;
$categories = [];
try {
    $db = getDB();

    // Fetch categories
    $stmt = $db->query("SELECT category_id, category_name FROM job_categories ORDER BY category_name ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch job
    $stmt = $db->prepare("SELECT * FROM jobs WHERE job_id = ? AND employer_id = ?");
    $stmt->execute([$job_id, $employer_id]);
    $job = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$job) {
        header('Location: /employer/jobs');
        exit;
    }
} catch (Exception $e) {
    error_log("Error fetching job: " . $e->getMessage());
    header('Location: /employer/jobs');
    exit;
}

$navUserName = $_SESSION['name'] ?? null;
$navUserRole = $_SESSION['role'] ?? null;
$navUserId   = $_SESSION['user_id']     ?? null;
$isLoggedIn  = isset($_SESSION['user_id']);

// Get error/success messages
$error = $_SESSION['error'] ?? null;
$success = $_SESSION['success'] ?? null;
unset($_SESSION['error'], $_SESSION['success']);

include_once 'partials/header.php';
?>

<!-- Hero Banner -->
<section class="relative w-full overflow-hidden" style="height:280px;">
    <div class="absolute inset-0" style="background: linear-gradient(135deg, #8b91dd 0%, #10195d 70%, #10195d 100%); opacity:0.92;"></div>
    <div class="absolute inset-0 flex flex-col items-center justify-end pb-10 z-10">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-3 tracking-tight">Edit Job</h1>
        <p class="text-gray-300 text-sm">
            <a href="/employer/jobs" class="underline underline-offset-2 hover:text-white transition-colors">Jobs</a>
            <span class="mx-2 opacity-50">—</span>
            <span class="text-white">Edit Job</span>
        </p>
    </div>
</section>

<main class="min-h-screen pb-20">
    <div class="max-w-4xl mx-auto px-4 pt-10">

        <?php if ($error): ?>
            <div id="errorAlert" class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4 flex items-center gap-3">
                <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 shrink-0"></i>
                <p class="text-sm text-red-800"><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div id="successAlert" class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4 flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5 text-green-600 shrink-0"></i>
                <p class="text-sm text-green-800"><?php echo htmlspecialchars($success); ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="../../php/functions/jobs.php" enctype="multipart/form-data" id="jobForm">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="job_id" value="<?php echo (int)$job['job_id']; ?>">

            <!-- ════════════════════════════════════════════ -->
            <!--  SECTION 1: Job Details                      -->
            <!-- ════════════════════════════════════════════ -->
            <div class="bg-white overflow-hidden mb-6">
                <div class="px-7 py-5 border-b border-slate-100 flex items-center gap-3">
                    <span class="w-7 h-7 rounded-full bg-indigo-600 text-white text-xs font-bold flex items-center justify-center">1</span>
                    <h2 class="text-base font-bold text-slate-800 tracking-tight">Job Details</h2>
                </div>
                <div class="px-7 py-6 space-y-5">

                    <!-- Job Title -->
                    <div>
                        <label class="form-label" for="title">Job Title <span class="text-red-500">*</span></label>
                        <input class="form-input rounded-md" type="text" id="title" name="title" placeholder="e.g. Senior Software Engineer"
                            value="<?php echo htmlspecialchars($job['title']); ?>" required>
                    </div>

                    <!-- Location + Remote -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label" for="location">Location <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
                            <input class="form-input rounded-md" type="text" id="location" name="location" placeholder='e.g. "Nairobi"'
                                value="<?php echo htmlspecialchars($job['location'] ?? ''); ?>">
                            <p class="text-xs text-slate-400 mt-1">Leave blank if location is not important</p>
                        </div>
                        <div class="flex flex-col justify-center">
                            <label class="form-label">Remote Position</label>
                            <label class="flex items-center gap-2.5 cursor-pointer mt-1">
                                <input type="checkbox" name="remote" id="remote" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-400"
                                    <?php echo (($job['location'] ?? '') === 'Remote') ? 'checked' : ''; ?>>
                                <span class="text-sm text-slate-600">This is a remote position</span>
                            </label>
                        </div>
                    </div>

                    <!-- Job Type + Career Level + Experience -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="form-label" for="job_type">Job Type <span class="text-red-500">*</span></label>
                            <select class="form-select" id="job_type" name="job_type" required>
                                <option value="">Select type…</option>
                                <option value="Full Time" <?php echo ($job['job_type'] === 'Full Time') ? 'selected' : ''; ?>>Full Time</option>
                                <option value="Part Time" <?php echo ($job['job_type'] === 'Part Time') ? 'selected' : ''; ?>>Part Time</option>
                                <option value="Contract" <?php echo ($job['job_type'] === 'Contract') ? 'selected' : ''; ?>>Contract</option>
                                <option value="Internship" <?php echo ($job['job_type'] === 'Internship') ? 'selected' : ''; ?>>Internship</option>
                                <option value="Freelance" <?php echo ($job['job_type'] === 'Freelance') ? 'selected' : ''; ?>>Freelance</option>
                                <option value="Temporary" <?php echo ($job['job_type'] === 'Temporary') ? 'selected' : ''; ?>>Temporary</option>
                                <option value="Remote" <?php echo ($job['job_type'] === 'Remote') ? 'selected' : ''; ?>>Remote</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="experience_level">Career Level <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
                            <select class="form-select" id="experience_level" name="experience_level">
                                <option value="Not specified" <?php echo ($job['experience_level'] === 'Not specified') ? 'selected' : ''; ?>>Not specified</option>
                                <option value="Entry Level" <?php echo ($job['experience_level'] === 'Entry Level') ? 'selected' : ''; ?>>Entry Level</option>
                                <option value="Mid Level" <?php echo ($job['experience_level'] === 'Mid Level') ? 'selected' : ''; ?>>Mid Level</option>
                                <option value="Senior" <?php echo ($job['experience_level'] === 'Senior') ? 'selected' : ''; ?>>Senior</option>
                                <option value="Lead" <?php echo ($job['experience_level'] === 'Lead') ? 'selected' : ''; ?>>Lead</option>
                                <option value="Manager" <?php echo ($job['experience_level'] === 'Manager') ? 'selected' : ''; ?>>Manager</option>
                                <option value="Executive" <?php echo ($job['experience_level'] === 'Executive') ? 'selected' : ''; ?>>Executive</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="required_qualification">Qualification <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
                            <select class="form-select" id="required_qualification" name="required_qualification">
                                <option value="Not specified" <?php echo ($job['required_qualification'] === 'Not specified') ? 'selected' : ''; ?>>Not specified</option>
                                <option value="High School" <?php echo ($job['required_qualification'] === 'High School') ? 'selected' : ''; ?>>High School</option>
                                <option value="Diploma" <?php echo ($job['required_qualification'] === 'Diploma') ? 'selected' : ''; ?>>Diploma</option>
                                <option value="Bachelor Degree" <?php echo ($job['required_qualification'] === 'Bachelor Degree') ? 'selected' : ''; ?>>Bachelor Degree</option>
                                <option value="Master Degree" <?php echo ($job['required_qualification'] === 'Master Degree') ? 'selected' : ''; ?>>Master Degree</option>
                                <option value="PhD" <?php echo ($job['required_qualification'] === 'PhD') ? 'selected' : ''; ?>>PhD</option>
                                <option value="Certification" <?php echo ($job['required_qualification'] === 'Certification') ? 'selected' : ''; ?>>Certification</option>
                            </select>
                        </div>
                    </div>

                    <!-- Salary Min + Salary Max + Years of Experience -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="form-label" for="salary_min">Minimum Salary <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
                            <input class="form-input rounded-md" type="number" id="salary_min" name="salary_min" placeholder="e.g. 50000" min="0" step="1000"
                                value="<?php echo htmlspecialchars($job['salary_min'] ?? ''); ?>">
                        </div>
                        <div>
                            <label class="form-label" for="salary_max">Maximum Salary <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
                            <input class="form-input rounded-md" type="number" id="salary_max" name="salary_max" placeholder="e.g. 150000" min="0" step="1000"
                                value="<?php echo htmlspecialchars($job['salary_max'] ?? ''); ?>">
                        </div>
                        <div>
                            <label class="form-label">Years of Experience <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
                            <div class="flex gap-2">
                                <input class="form-input rounded-md" type="number" id="years_experience_min" name="years_experience_min" placeholder="Min" min="0" step="1" max="60"
                                    value="<?php echo htmlspecialchars($job['years_experience_min'] ?? ''); ?>">
                                <input class="form-input rounded-md" type="number" id="years_experience_max" name="years_experience_max" placeholder="Max" min="0" step="1" max="60"
                                    value="<?php echo htmlspecialchars($job['years_experience_max'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="form-label" for="category_id">Job Category <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="">Choose a category…</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo (int)$category['category_id']; ?>"
                                    <?php echo ($job['category_id'] == $category['category_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Deadline -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label" for="deadline">Application Deadline <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
                            <input class="form-input rounded-md" type="date" id="deadline" name="deadline"
                                value="<?php echo htmlspecialchars($job['deadline'] ?? ''); ?>">
                        </div>
                        <div class="flex flex-col justify-center">
                            <label class="form-label">Featured Job</label>
                            <label class="flex items-center gap-2.5 cursor-pointer mt-1">
                                <input type="checkbox" name="featured" id="featured" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-400"
                                    <?php echo ($job['featured'] == 1) ? 'checked' : ''; ?>>
                                <span class="text-sm text-slate-600">Promote this job (appears in featured section)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="form-label">Job Description <span class="text-red-500">*</span></label>
                        <p class="text-xs text-slate-400 mb-2">Use the toolbar to format — supports <strong>bold</strong>, <em>italic</em>, lists, and headings.</p>

                        <!-- Toolbar -->
                        <div class="editor-toolbar flex flex-wrap items-center gap-1 border border-slate-200 border-b-0 rounded-t-xl bg-slate-50 px-3 py-2">
                            <button type="button" onclick="fmt('bold')" title="Bold" class="tool-btn font-bold">B</button>
                            <button type="button" onclick="fmt('italic')" title="Italic" class="tool-btn italic">I</button>
                            <button type="button" onclick="fmt('h2')" title="Heading 2" class="tool-btn text-xs font-bold">H2</button>
                            <button type="button" onclick="fmt('h3')" title="Heading 3" class="tool-btn text-xs font-bold">H3</button>
                            <div class="w-px h-5 bg-slate-200 mx-1"></div>
                            <button type="button" onclick="fmt('ul')" title="Bullet list" class="tool-btn">• List</button>
                            <button type="button" onclick="fmt('ol')" title="Numbered list" class="tool-btn">1. List</button>
                            <div class="w-px h-5 bg-slate-200 mx-1"></div>
                            <button type="button" onclick="togglePreview()" id="previewBtn" class="tool-btn text-indigo-600 font-semibold">Preview</button>
                        </div>

                        <!-- Textarea -->
                        <textarea
                            id="description"
                            name="description"
                            rows="12"
                            placeholder="Describe the role, responsibilities, requirements…"
                            class="w-full px-4 py-3 border border-slate-200 rounded-b-xl bg-white text-sm text-slate-800 placeholder-slate-400
                     focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent
                     resize-y font-mono leading-relaxed transition duration-150"
                            required><?php echo htmlspecialchars($job['description']); ?></textarea>

                        <!-- Preview Panel (hidden by default) -->
                        <div id="mdPreview"
                            class="hidden w-full min-h-[200px] border border-indigo-200 rounded-xl bg-indigo-50/40 px-6 py-5 mt-2 prose prose-sm max-w-none text-slate-700">
                        </div>
                    </div>

                </div>
            </div>
            <!-- ── Action Buttons ── -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="/employer/jobs"
                    class="px-6 py-2.5 rounded-xl text-sm font-semibold border-2 border-slate-300 text-slate-600 hover:border-slate-400 bg-white transition duration-150">
                    Cancel
                </a>
                <button type="submit"
                    class="px-8 py-2.5 rounded-xl text-sm font-semibold text-white transition duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#d5225a]"
                    style="background-color:#198754;"
                    onmouseover="this.style.backgroundColor='#d5225bc2'"
                    onmouseout="this.style.backgroundColor='#D5225A'">
                    Update Job
                </button>
            </div>

        </form>
    </div>
</main>

<!-- ── Shared Input Styles ── -->
<style>
    .form-label {
        display: block;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.375rem;
    }

    .form-input,
    .form-select {
        width: 100%;
        padding: 0.625rem 1rem;
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
        background-color: #f8fafc;
        font-size: 0.875rem;
        color: #1e293b;
        transition: all 0.15s ease;
        appearance: none;
    }

    .form-select {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 1rem;
        padding-right: 2.5rem;
    }

    .form-input:focus,
    .form-select:focus {
        outline: none;
        border-color: transparent;
        box-shadow: 0 0 0 2px #818cf8;
        background-color: #fff;
    }

    .form-input::placeholder {
        color: #94a3b8;
    }

    /* Toolbar buttons */
    .tool-btn {
        padding: 0.25rem 0.625rem;
        border-radius: 0.4rem;
        font-size: 0.8rem;
        color: #475569;
        background: transparent;
        border: 1px solid transparent;
        cursor: pointer;
        transition: all 0.12s;
        line-height: 1.6;
    }

    .tool-btn:hover {
        background: #e0e7ff;
        color: #4f46e5;
        border-color: #c7d2fe;
    }

    /* Prose preview */
    .prose h2 {
        font-size: 1.1rem;
        font-weight: 700;
        margin-top: 1rem;
        margin-bottom: 0.5rem;
    }

    .prose h3 {
        font-size: 1rem;
        font-weight: 600;
        margin-top: 0.8rem;
        margin-bottom: 0.4rem;
    }

    .prose ul,
    .prose ol {
        padding-left: 1.5rem;
        margin-bottom: 0.5rem;
    }

    .prose strong {
        font-weight: 700;
    }

    .prose em {
        font-style: italic;
    }
</style>

<script>
    /**
     * Markdown editor toolbar functions
     */
    function fmt(type) {
        const textarea = document.getElementById('description');
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;
        const selected = text.substring(start, end);
        let newText = '';

        if (type === 'bold') {
            newText = `**${selected || 'bold text'}**`;
        } else if (type === 'italic') {
            newText = `*${selected || 'italic text'}*`;
        } else if (type === 'h2') {
            newText = `## ${selected || 'Heading 2'}\n`;
        } else if (type === 'h3') {
            newText = `### ${selected || 'Heading 3'}\n`;
        } else if (type === 'ul') {
            newText = `• ${selected || 'List item'}\n`;
        } else if (type === 'ol') {
            newText = `1. ${selected || 'List item'}\n`;
        }

        textarea.value = text.substring(0, start) + newText + text.substring(end);
        textarea.focus();
        updatePreview();
    }

    function togglePreview() {
        const preview = document.getElementById('mdPreview');
        const btn = document.getElementById('previewBtn');
        if (preview.classList.contains('hidden')) {
            preview.classList.remove('hidden');
            btn.textContent = 'Hide Preview';
            updatePreview();
        } else {
            preview.classList.add('hidden');
            btn.textContent = 'Preview';
        }
    }

    function updatePreview() {
        const textarea = document.getElementById('description');
        const preview = document.getElementById('mdPreview');
        const text = textarea.value;

        // Simple markdown to HTML conversion (very basic)
        let html = text
            .replace(/^### (.*?)$/gm, '<h3>$1</h3>')
            .replace(/^## (.*?)$/gm, '<h2>$1</h2>')
            .replace(/^\* (.*?)$/gm, '<li>$1</li>')
            .replace(/^1\. (.*?)$/gm, '<li>$1</li>')
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/\n/g, '<br>');

        preview.innerHTML = html;
    }

    /**
     * Form submission validation and SweetAlert integration
     */
    const form = document.getElementById('jobForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const title = document.getElementById('title')?.value?.trim() || '';
            const description = document.getElementById('description')?.value?.trim() || '';
            const jobType = document.getElementById('job_type')?.value?.trim() || '';

            if (!title) {
                showError('Job title is required');
                return;
            }
            if (!description) {
                showError('Job description is required');
                return;
            }
            if (!jobType) {
                showError('Job type is required');
                return;
            }
            if (description.length < 20) {
                showError('Job description must be at least 20 characters long');
                return;
            }

            this.submit();
        });
    }

    function showError(message) {
        console.error('Job Form Error:', message);

        // Log to server for debugging
        fetch('/api/log', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                type: 'job_form_error',
                message: message,
                url: window.location.href,
                timestamp: new Date().toISOString()
            })
        }).catch(() => {}); // Silently ignore logging errors

        // Show SweetAlert error
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: message,
                confirmButtonColor: '#d5225a',
                confirmButtonText: 'OK'
            });
        } else {
            alert('❌ ' + message);
        }
    }

    // Check if error alert should trigger SweetAlert
    // Wait for SweetAlert to load if not already available
    const waitForSweetAlert = setInterval(() => {
        if (typeof Swal === 'undefined') return; // Still waiting
        clearInterval(waitForSweetAlert);

        const errorAlert = document.getElementById('errorAlert');
        if (errorAlert) {
            const errorParagraph = errorAlert.querySelector('p');
            if (errorParagraph && errorParagraph.textContent) {
                const errorMsg = errorParagraph.textContent || 'An error occurred';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMsg,
                    confirmButtonColor: '#d5225a',
                    confirmButtonText: 'Go Back to Jobs',
                    allowOutsideClick: false
                }).then(result => {
                    if (result.isConfirmed) {
                        window.location.href = '/employer/jobs';
                    }
                });
                return; // Exit after showing error
            }
        }

        // Show success message with redirect
        const successAlert = document.getElementById('successAlert');
        if (successAlert) {
            const successParagraph = successAlert.querySelector('p');
            if (successParagraph && successParagraph.textContent) {
                const successMsg = successParagraph.textContent || 'Success!';
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: successMsg,
                    confirmButtonColor: '#2b9a66',
                    confirmButtonText: 'View Your Jobs',
                    allowOutsideClick: false
                }).then(result => {
                    if (result.isConfirmed) {
                        window.location.href = '/employer/jobs';
                    }
                });
            }
        }
    }, 100);
</script>

<?php include_once 'partials/footer.php'; ?>
