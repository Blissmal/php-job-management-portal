<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$connectionPath = __DIR__ . '/../../php/config/connection.php';
if (file_exists($connectionPath)) require_once $connectionPath;

// Fetch categories from database
$categories = [];
try {
    $db = getDB();
    $stmt = $db->query("SELECT category_id, category_name FROM job_categories ORDER BY category_name ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error fetching categories: " . $e->getMessage());
}


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

        <form method="POST" action="/post-a-job" enctype="multipart/form-data" id="jobForm">
            <input type="hidden" name="action" value="create">

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

                    <!-- Job Type + Career Level + Experience -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="form-label" for="job_type">Job Type <span class="text-red-500">*</span></label>
                            <select class="form-select" id="job_type" name="job_type" required>
                                <option value="">Select type…</option>
                                <option value="Full Time">Full Time</option>
                                <option value="Part Time">Part Time</option>
                                <option value="Contract">Contract</option>
                                <option value="Internship">Internship</option>
                                <option value="Freelance">Freelance</option>
                                <option value="Temporary">Temporary</option>
                                <option value="Remote">Remote</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="experience_level">Career Level <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
                            <select class="form-select" id="experience_level" name="experience_level">
                                <option value="Not specified">Not specified</option>
                                <option value="Entry Level">Entry Level</option>
                                <option value="Mid Level">Mid Level</option>
                                <option value="Senior">Senior</option>
                                <option value="Lead">Lead</option>
                                <option value="Manager">Manager</option>
                                <option value="Executive">Executive</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="required_qualification">Qualification <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
                            <select class="form-select" id="required_qualification" name="required_qualification">
                                <option value="Not specified">Not specified</option>
                                <option value="High School">High School</option>
                                <option value="Diploma">Diploma</option>
                                <option value="Bachelor Degree">Bachelor Degree</option>
                                <option value="Master Degree">Master Degree</option>
                                <option value="PhD">PhD</option>
                                <option value="Certification">Certification</option>
                            </select>
                        </div>
                    </div>

                    <!-- Salary Min + Salary Max + Years of Experience -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="form-label" for="salary_min">Minimum Salary <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
                            <input class="form-input rounded-md" type="number" id="salary_min" name="salary_min" placeholder="e.g. 50000" min="0" step="1000">
                        </div>
                        <div>
                            <label class="form-label" for="salary_max">Maximum Salary <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
                            <input class="form-input rounded-md" type="number" id="salary_max" name="salary_max" placeholder="e.g. 150000" min="0" step="1000">
                        </div>
                        <div>
                            <label class="form-label">Years of Experience <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
                            <div class="flex gap-2">
                                <input class="form-input rounded-md" type="number" id="years_experience_min" name="years_experience_min" placeholder="Min" min="0" step="1" max="60">
                                <input class="form-input rounded-md" type="number" id="years_experience_max" name="years_experience_max" placeholder="Max" min="0" step="1" max="60">
                            </div>
                        </div>
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="form-label" for="category_id">Job Category <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="">Choose a category…</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo (int)$category['category_id']; ?>">
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Deadline -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label" for="deadline">Application Deadline <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
                            <input class="form-input rounded-md" type="date" id="deadline" name="deadline">
                        </div>
                        <div class="flex flex-col justify-center">
                            <label class="form-label">Featured Job</label>
                            <label class="flex items-center gap-2.5 cursor-pointer mt-1">
                                <input type="checkbox" name="featured" id="featured" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-400">
                                <span class="text-sm text-slate-600">Promote this job (appears in featured section)</span>
                            </label>
                        </div>
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

/* ── Toolbar formatter ── */
function fmt(type) {
    textarea.focus();
    const s = textarea.selectionStart, e = textarea.selectionEnd;
    const sel = textarea.value.substring(s, e);
    const cur = textarea.value;

    const maps = {
        bold:   { wrap: ['**', '**'], def: 'bold text' },
        italic: { wrap: ['_', '_'],   def: 'italic text' },
        h2:     { prefix: '## ',      def: 'Heading',   newline: true },
        h3:     { prefix: '### ',     def: 'Heading',   newline: true },
        ul:     { prefix: '- ',       def: 'List item', newline: true },
        ol:     { prefix: '1. ',      def: 'List item', newline: true },
    };

    const m = maps[type];
    let insert, newStart, newEnd;

    if (m.wrap) {
        const text = sel || m.def;
        insert   = m.wrap[0] + text + m.wrap[1];
        newStart = s + m.wrap[0].length;
        newEnd   = newStart + text.length;
    } else {
        const needsNewline = m.newline && s > 0 && cur[s - 1] !== '\n';
        const prefix = (needsNewline ? '\n' : '') + m.prefix;
        const text   = sel || m.def;
        insert   = prefix + text;
        newStart = s + prefix.length;
        newEnd   = newStart + text.length;
    }

    textarea.setRangeText(insert, s, e, 'end');
    textarea.selectionStart = newStart;
    textarea.selectionEnd   = newEnd;
}

/* ── Markdown parser (line-by-line, no double-escaping) ── */
function esc(s) {
    return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function inline(s) {
    return esc(s)
        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
        .replace(/__(.+?)__/g,     '<strong>$1</strong>')
        .replace(/_(.+?)_/g,       '<em>$1</em>')
        .replace(/\*(.+?)\*/g,     '<em>$1</em>');
}

function parseMarkdown(raw) {
    const lines = raw.split('\n');
    const out = [];
    let i = 0;

    while (i < lines.length) {
        const line = lines[i];

        // Headings
        if (/^### /.test(line)) { out.push('<h3>' + esc(line.slice(4)) + '</h3>'); i++; continue; }
        if (/^## /.test(line))  { out.push('<h2>' + esc(line.slice(3)) + '</h2>'); i++; continue; }
        if (/^# /.test(line))   { out.push('<h2>' + esc(line.slice(2)) + '</h2>'); i++; continue; }

        // Unordered list — collect consecutive items
        if (/^[-*] /.test(line)) {
            const items = [];
            while (i < lines.length && /^[-*] /.test(lines[i])) {
                items.push('<li>' + inline(lines[i].slice(2)) + '</li>');
                i++;
            }
            out.push('<ul>' + items.join('') + '</ul>');
            continue;
        }

        // Ordered list — collect consecutive items
        if (/^\d+\. /.test(line)) {
            const items = [];
            while (i < lines.length && /^\d+\. /.test(lines[i])) {
                items.push('<li>' + inline(lines[i].replace(/^\d+\. /, '')) + '</li>');
                i++;
            }
            out.push('<ol>' + items.join('') + '</ol>');
            continue;
        }

        // Blank line — skip
        if (line.trim() === '') { i++; continue; }

        // Plain paragraph
        out.push('<p>' + inline(line) + '</p>');
        i++;
    }

    return out.join('\n') || '<span style="color:#94a3b8;font-style:italic;font-size:13px;">Nothing to preview yet…</span>';
}

/* ── Toggle preview / edit ── */
function togglePreview() {
    inPreview = !inPreview;
    if (inPreview) {
        previewEl.innerHTML = parseMarkdown(textarea.value);
        previewEl.classList.remove('hidden');
        textarea.classList.add('hidden');
        previewBtn.textContent = 'Edit';
    } else {
        previewEl.classList.add('hidden');
        textarea.classList.remove('hidden');
        previewBtn.textContent = 'Preview';
    }
}

/* ── Save draft ── */
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

/* ── Restore draft on load ── */
window.addEventListener('DOMContentLoaded', () => {
    const saved = sessionStorage.getItem('jobDraft');
    if (!saved) return;
    const data = JSON.parse(saved);
    Object.entries(data).forEach(([k, v]) => {
        const el = document.querySelector(`[name="${k}"]`);
        if (el && el.type !== 'file') el.value = v;
    });
});

/* ── SweetAlert for flash messages ── */
const waitForSweetAlert = setInterval(() => {
    if (typeof Swal === 'undefined') return;
    clearInterval(waitForSweetAlert);

    const errorAlert = document.getElementById('errorAlert');
    if (errorAlert) {
        const msg = errorAlert.querySelector('p')?.textContent || 'An error occurred';
        Swal.fire({ icon: 'error', title: 'Error', text: msg, confirmButtonColor: '#d5225a', confirmButtonText: 'Go Back to Jobs', allowOutsideClick: false })
            .then(r => { if (r.isConfirmed) window.location.href = '/jobs'; });
        return;
    }

    const successAlert = document.getElementById('successAlert');
    if (successAlert) {
        const msg = successAlert.querySelector('p')?.textContent || 'Success!';
        Swal.fire({ icon: 'success', title: 'Success!', text: msg, confirmButtonColor: '#2b9a66', confirmButtonText: 'View Your Job', allowOutsideClick: false })
            .then(r => { if (r.isConfirmed) window.location.href = '/employer/jobs'; });
    }
}, 100);
</script>

<?php include_once 'partials/footer.php'; ?>
