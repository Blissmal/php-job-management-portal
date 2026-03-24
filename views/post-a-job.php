<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$connectionPath = __DIR__ . '/../../php/config/connection.php';
if (file_exists($connectionPath)) require_once $connectionPath;

$navUserName = $_SESSION['currentUserName'] ?? null;
$navUserRole = $_SESSION['currentUserRole'] ?? null;
$navUserId   = $_SESSION['currentUser']     ?? null;
$isLoggedIn  = isset($_SESSION['currentUser']);

include_once 'partials/header.php';
?>

<!-- Hero Banner -->
<section class="relative w-full overflow-hidden" style="height:280px;">
    <div class="absolute inset-0" style="background: linear-gradient(135deg, #8b91dd 0%, #10195d 70%, #10195d 100%); opacity:0.92;"></div>
    <div class="absolute inset-0 flex flex-col items-center justify-end pb-10 z-10">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-3 tracking-tight">Post a Job</h1>
        <p class="text-gray-300 text-sm">
            <a href="/" class="underline underline-offset-2 hover:text-white transition-colors">Home</a>
            <span class="mx-2 opacity-50">—</span>
            <span class="text-white">Post a Job</span>
        </p>
    </div>
</section>

<main class="min-h-screen pb-20">
    <div class="max-w-4xl mx-auto px-4 pt-10">

        <?php if (!$isLoggedIn): ?>
            <!-- ── Not Logged In Banner ── -->
            <div class="bg-indigo-50 border border-indigo-200 rounded-xl px-5 py-4 mb-8 flex items-center justify-between gap-4 flex-wrap">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-indigo-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 8v4m0 4h.01" />
                    </svg>
                    <p class="text-sm text-indigo-700">
                        <span class="font-semibold">Have an account?</span>
                        Sign in to post a job and manage your listings from your employer dashboard.
                    </p>
                </div>
                <a href="/login" class="shrink-0 text-sm font-semibold text-white px-5 py-2 rounded-lg transition-colors duration-150"
                    style="background-color:#198754;" onmouseover="this.style.backgroundColor='##D5225A'" onmouseout="this.style.backgroundColor='#198754'">
                    Sign In
                </a>
            </div>
        <?php endif; ?>

        <form method="POST" action="/php/function/post-job.php" enctype="multipart/form-data" id="jobForm">

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
                        <input class="form-input rounded-md" type="text" id="title" name="title" placeholder="e.g. Senior Software Engineer" required>
                    </div>

                    <!-- Location + Remote -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label" for="location">Location <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
                            <input class="form-input rounded-md" type="text" id="location" name="location" placeholder='e.g. "Nairobi"'>
                            <p class="text-xs text-slate-400 mt-1">Leave blank if location is not important</p>
                        </div>
                        <div class="flex flex-col justify-center">
                            <label class="form-label">Remote Position</label>
                            <label class="flex items-center gap-2.5 cursor-pointer mt-1">
                                <input type="checkbox" name="remote" id="remote" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-400">
                                <span class="text-sm text-slate-600">This is a remote position</span>
                            </label>
                        </div>
                    </div>

                    <!-- Job Type + Salary + Career Level -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="form-label" for="type">Job Type <span class="text-red-500">*</span></label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="">Select type…</option>
                                <option value="Full Time">Full Time</option>
                                <option value="Part Time">Part Time</option>
                                <option value="Contract">Contract</option>
                                <option value="Internship">Internship</option>
                                <option value="Freelance">Freelance</option>
                                <option value="Temporary">Temporary</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="salary">Job Salary <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
                            <input class="form-input rounded-md" type="text" id="salary" name="salary" placeholder="e.g. KES 80,000">
                        </div>
                        <div>
                            <label class="form-label" for="career_level">Career Level <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
                            <select class="form-select" id="career_level" name="career_level">
                                <option value="">Choose level…</option>
                                <option>Entry Level</option>
                                <option>Junior</option>
                                <option>Mid Level</option>
                                <option>Senior</option>
                                <option>Lead, Manager, Senior</option>
                                <option>Director</option>
                                <option>Executive</option>
                            </select>
                        </div>
                    </div>

                    <!-- Experience + Industry + Qualification -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="form-label" for="experience">Experience <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
                            <select class="form-select" id="experience" name="experience">
                                <option value="">Choose…</option>
                                <option>No Experience</option>
                                <option>Less than 1 Year</option>
                                <option>1-2 Years</option>
                                <option>3-5 Years</option>
                                <option>5-10 Years</option>
                                <option>10+ Years</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="industry">Industry <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
                            <select class="form-select" id="industry" name="industry">
                                <option value="">Choose…</option>
                                <option>Technology & IT</option>
                                <option>Education & Training</option>
                                <option>Finance & Banking</option>
                                <option>Healthcare</option>
                                <option>Engineering</option>
                                <option>Media & Communications</option>
                                <option>Retail & Consumer Goods</option>
                                <option>Government & NGO</option>
                                <option>Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="qualification">Qualification <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
                            <select class="form-select" id="qualification" name="qualification">
                                <option value="">Choose…</option>
                                <option>Certificate</option>
                                <option>Diploma</option>
                                <option>Bachelor Degree</option>
                                <option>Master Degree</option>
                                <option>PhD / Doctorate</option>
                                <option>No Requirement</option>
                            </select>
                        </div>
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="form-label" for="category">Job Category <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
                        <select class="form-select" id="category" name="category">
                            <option value="">Choose a category…</option>
                            <option>Software Development</option>
                            <option>Design & Creative</option>
                            <option>Data & Analytics</option>
                            <option>Marketing & Sales</option>
                            <option>Customer Support</option>
                            <option>Human Resources</option>
                            <option>Finance & Accounting</option>
                            <option>Legal</option>
                            <option>Operations</option>
                            <option>Education</option>
                            <option>Healthcare</option>
                            <option>Other</option>
                        </select>
                    </div>

                    <!-- Deadline -->
                    <div>
                        <label class="form-label" for="deadline">Application Deadline <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
                        <input class="form-input rounded-md" type="date" id="deadline" name="deadline">
                    </div>

                    <!-- Application Email / URL -->
                    <div>
                        <label class="form-label" for="apply_link">Application Email or URL <span class="text-red-500">*</span></label>
                        <input class="form-input rounded-md" type="text" id="apply_link" name="apply_link" placeholder="Enter an email address or application link" required>
                    </div>

                    <!-- Description (Markdown Editor) -->
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
                            required></textarea>

                        <!-- Preview Panel (hidden by default) -->
                        <div id="mdPreview"
                            class="hidden w-full min-h-[200px] border border-indigo-200 rounded-xl bg-indigo-50/40 px-6 py-5 mt-2 prose prose-sm max-w-none text-slate-700">
                        </div>
                    </div>

                </div>
            </div>

            <!-- ════════════════════════════════════════════ -->
            <!--  SECTION 2: Company Details                  -->
            <!-- ════════════════════════════════════════════ -->
            <div class="bg-white mb-6">
                <div class="px-7 py-5 border-b border-slate-100 flex items-center gap-3">
                    <span class="w-7 h-7 rounded-full bg-indigo-600 text-white text-xs font-bold flex items-center justify-center">2</span>
                    <h2 class="text-base font-bold text-slate-800 tracking-tight">Company Details</h2>
                </div>
                <div class="px-7 py-6 space-y-5">

                    <!-- Company Name -->
                    <div>
                        <label class="form-label" for="company_name">Company Name <span class="text-red-500">*</span></label>
                        <input class="form-input rounded-md" type="text" id="company_name" name="company_name"
                            placeholder="Enter the name of the company"
                            value="<?php echo htmlspecialchars($navUserName ?? ''); ?>"
                            required>
                    </div>

                    <!-- Website + Tagline -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label" for="company_website">Website <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
                            <input class="form-input rounded-md" type="url" id="company_website" name="company_website" placeholder="https://yourcompany.com">
                        </div>
                        <div>
                            <label class="form-label" for="company_tagline">Tagline <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
                            <input class="form-input rounded-md" type="text" id="company_tagline" name="company_tagline" placeholder="Briefly describe your company">
                        </div>
                    </div>

                    <!-- Logo -->
                    <div>
                        <label class="form-label" for="company_logo">Company Logo <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
                        <div class="flex items-center gap-4">
                            <label for="company_logo"
                                class="cursor-pointer flex items-center gap-2 px-4 py-2.5 rounded-xl border-2 border-dashed border-slate-200 bg-slate-50
                       text-sm text-slate-500 hover:border-indigo-300 hover:bg-indigo-50 transition duration-150">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                    <polyline points="17 8 12 3 7 8" />
                                    <line x1="12" y1="3" x2="12" y2="15" />
                                </svg>
                                <span id="logoLabel">Choose file…</span>
                            </label>
                            <input type="file" id="company_logo" name="company_logo" accept="image/*" class="sr-only"
                                onchange="document.getElementById('logoLabel').textContent = this.files[0]?.name || 'Choose file…'">
                            <span class="text-xs text-slate-400">Max 2 MB · PNG, JPG, SVG</span>
                        </div>
                    </div>

                    <!-- Email + Phone -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label" for="company_email">Contact Email <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
                            <input class="form-input rounded-md" type="email" id="company_email" name="company_email"
                                placeholder="contact@company.com"
                                value="<?php echo htmlspecialchars($_SESSION['currentUserEmail'] ?? ''); ?>">
                        </div>
                        <div>
                            <label class="form-label" for="company_phone">Phone <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
                            <input class="form-input rounded-md" type="tel" id="company_phone" name="company_phone" placeholder="+254 700 000 000">
                        </div>
                    </div>

                </div>
            </div>

            <!-- ── Action Buttons ── -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" id="draftBtn"
                    class="px-6 py-2.5 rounded-xl text-sm font-semibold border-2 border-slate-300 text-slate-600 hover:border-slate-400 bg-white transition duration-150">
                    Save Draft
                </button>
                <button type="submit"
                    class="px-8 py-2.5 rounded-xl text-sm font-semibold text-white transition duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#d5225a]"
                    style="background-color:#198754;"
                    onmouseover="this.style.backgroundColor='#d5225bc2'"
                    onmouseout="this.style.backgroundColor='#D5225A'">
                    Post Job
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
        margin-bottom: 0.4rem;
        color: #1e293b;
    }

    .prose h3 {
        font-size: 0.95rem;
        font-weight: 700;
        margin-top: 0.8rem;
        margin-bottom: 0.3rem;
        color: #1e293b;
    }

    .prose p {
        margin-bottom: 0.6rem;
    }

    .prose ul {
        list-style: disc;
        padding-left: 1.4rem;
        margin-bottom: 0.6rem;
    }

    .prose ol {
        list-style: decimal;
        padding-left: 1.4rem;
        margin-bottom: 0.6rem;
    }

    .prose li {
        margin-bottom: 0.2rem;
    }

    .prose strong {
        font-weight: 700;
    }

    .prose em {
        font-style: italic;
    }
</style>

<!-- ── Markdown Formatter + Preview ── -->
<script>
    const textarea = document.getElementById('description');
    const previewEl = document.getElementById('mdPreview');
    const previewBtn = document.getElementById('previewBtn');
    let inPreview = false;

    function fmt(type) {
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const sel = textarea.value.substring(start, end);
        let insert = '';
        const maps = {
            bold: `**${sel || 'bold text'}**`,
            italic: `_${sel || 'italic text'}_`,
            h2: `\n## ${sel || 'Heading'}`,
            h3: `\n### ${sel || 'Heading'}`,
            ul: `\n- ${sel || 'List item'}`,
            ol: `\n1. ${sel || 'List item'}`,
        };
        insert = maps[type] || sel;
        textarea.setRangeText(insert, start, end, 'end');
        textarea.focus();
    }

    // Minimal markdown → HTML parser (no external lib needed)
    function parseMarkdown(md) {
        return md
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;') // escape
            .replace(/^### (.+)$/gm, '<h3>$1</h3>')
            .replace(/^## (.+)$/gm, '<h2>$1</h2>')
            .replace(/^# (.+)$/gm, '<h2>$1</h2>')
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/_(.+?)_/g, '<em>$1</em>')
            .replace(/^\d+\. (.+)$/gm, '<li class="ol-item">$1</li>')
            .replace(/^[-*] (.+)$/gm, '<li class="ul-item">$1</li>')
            .replace(/(<li class="ol-item">.*<\/li>\n?)+/g, m => `<ol>${m.replace(/ class="ol-item"/g,'')}</ol>`)
            .replace(/(<li class="ul-item">.*<\/li>\n?)+/g, m => `<ul>${m.replace(/ class="ul-item"/g,'')}</ul>`)
            .replace(/\n{2,}/g, '</p><p>')
            .replace(/^(?!<[huo]|<p)(.+)$/gm, '<p>$1</p>')
            .replace(/<p><\/p>/g, '');
    }

    function togglePreview() {
        inPreview = !inPreview;
        if (inPreview) {
            previewEl.innerHTML = parseMarkdown(textarea.value) || '<span class="text-slate-400 text-sm italic">Nothing to preview yet…</span>';
            previewEl.classList.remove('hidden');
            textarea.classList.add('hidden');
            previewBtn.textContent = 'Edit';
        } else {
            previewEl.classList.add('hidden');
            textarea.classList.remove('hidden');
            previewBtn.textContent = 'Preview';
        }
    }

    // Save draft (just serializes to sessionStorage for now)
    document.getElementById('draftBtn').addEventListener('click', () => {
        const data = {};
        new FormData(document.getElementById('jobForm')).forEach((v, k) => data[k] = v);
        sessionStorage.setItem('jobDraft', JSON.stringify(data));
        const btn = document.getElementById('draftBtn');
        btn.textContent = '✓ Draft Saved';
        btn.classList.add('border-[#d5225bc2]', 'text-[#D5225A]');
        setTimeout(() => {
            btn.textContent = 'Save Draft';
            btn.classList.remove('border-[#d5225bc2]', 'text-[#D5225A]');
        }, 2000);
    });

    // Restore draft on load
    window.addEventListener('DOMContentLoaded', () => {
        const saved = sessionStorage.getItem('jobDraft');
        if (!saved) return;
        const data = JSON.parse(saved);
        Object.entries(data).forEach(([k, v]) => {
            const el = document.querySelector(`[name="${k}"]`);
            if (el && el.type !== 'file') el.value = v;
        });
    });
</script>

<?php include_once 'partials/footer.php'; ?>
