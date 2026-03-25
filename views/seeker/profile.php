<?php
require_once __DIR__ . '/../../php/config/connection.php';
include_once __DIR__ . '/../partials/header.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Check if user is logged in and is a seeker
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seeker') {
    header('Location: /login');
    exit;
}

$uid = (int)$_SESSION['user_id'];
$db  = getDB();

// Fetch existing profile data if any
$stmt = $db->prepare("SELECT * FROM job_seeker_profiles WHERE user_id = ?");
$stmt->execute([$uid]);
$profile = $stmt->fetch();

$error   = $_SESSION['error']   ?? null;
$success = $_SESSION['success'] ?? null;
$profile_incomplete = $_SESSION['profile_incomplete'] ?? null;
unset($_SESSION['error'], $_SESSION['success'], $_SESSION['profile_incomplete']);
?>

<!-- ── Hero Banner — full width, never interrupted ── -->
<section class="relative w-full overflow-hidden" style="height:280px;">
    <div class="absolute inset-0"
        style="background:linear-gradient(135deg,#8b91dd 0%,#10195d 70%,#10195d 100%);opacity:.92;"></div>
    <div class="absolute inset-0 flex flex-col items-center justify-end pb-10 z-10">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-3 tracking-tight">Complete Your Profile</h1>
        <p class="text-gray-300 text-sm">
            <span class="text-white">Set up your job seeker profile to get started</span>
        </p>
    </div>
</section>

<!-- ── Two-column layout: sticky sidebar + main content ── -->
<div class="max-w-7xl w-full mx-auto px-6 py-12 flex gap-10 items-start">

    <!-- Sidebar — sticky, self-start so it doesn't stretch -->
    <div class="sticky top-20 self-start w-52 flex-shrink-0">
        <?php include_once __DIR__ . '/../partials/sidebar.php'; ?>
    </div>

    <!-- Main content -->
    <main class="flex-1 min-w-0">

        <!-- Page title row -->
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-slate-800">My Profile</h2>
            <p class="text-sm text-slate-500 mt-1">
                This information helps employers find you &mdash; all fields are required.
            </p>
        </div>

        <div class="bg-white">

            <!-- Card Header -->
            <div class="px-8 pt-7 pb-5 border-b border-slate-100">
                <h3 class="text-base font-semibold text-slate-700">Job Seeker Profile</h3>
            </div>

            <!-- Card Body -->
            <div class="px-8 py-6">
                <form method="POST"
                    action="/seeker/profile"
                    enctype="multipart/form-data"
                    class="space-y-5">

                    <!-- Full Name -->
                    <div>
                        <label for="full_name"
                            class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="full_name" name="full_name"
                            placeholder="Your full name"
                            value="<?php echo htmlspecialchars($profile['full_name'] ?? ''); ?>"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50
                                      text-slate-800 text-sm placeholder-slate-400
                                      focus:outline-none focus:ring-2 focus:ring-indigo-400
                                      focus:border-transparent focus:bg-white transition duration-150">
                    </div>

                    <!-- Professional Headline -->
                    <div>
                        <label for="headline"
                            class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Professional Headline <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="headline" name="headline"
                            placeholder="e.g., Full Stack Developer | React & Node.js"
                            value="<?php echo htmlspecialchars($profile['headline'] ?? ''); ?>"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50
                                      text-slate-800 text-sm placeholder-slate-400
                                      focus:outline-none focus:ring-2 focus:ring-indigo-400
                                      focus:border-transparent focus:bg-white transition duration-150">
                    </div>

                    <!-- Location -->
                    <div>
                        <label for="location"
                            class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Location <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="location" name="location"
                            placeholder="City, Country"
                            value="<?php echo htmlspecialchars($profile['location'] ?? ''); ?>"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50
                                      text-slate-800 text-sm placeholder-slate-400
                                      focus:outline-none focus:ring-2 focus:ring-indigo-400
                                      focus:border-transparent focus:bg-white transition duration-150">
                    </div>

                    <!-- Skills -->
                    <div>
                        <label for="skills"
                            class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Skills <span class="text-red-500">*</span>
                        </label>
                        <textarea id="skills" name="skills" rows="3" required
                            placeholder="e.g., JavaScript, React, Node.js, MySQL, Docker (comma separated)"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50
                                         text-slate-800 text-sm placeholder-slate-400
                                         focus:outline-none focus:ring-2 focus:ring-indigo-400
                                         focus:border-transparent focus:bg-white transition duration-150"><?php echo htmlspecialchars($profile['skills'] ?? ''); ?></textarea>
                    </div>

                    <!-- Bio -->
                    <div>
                        <label for="bio"
                            class="block text-sm font-semibold text-slate-700 mb-1.5">
                            About You <span class="text-red-500">*</span>
                        </label>
                        <textarea id="bio" name="bio" rows="4" required
                            placeholder="Tell employers about yourself, your experience, and career goals"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50
                                         text-slate-800 text-sm placeholder-slate-400
                                         focus:outline-none focus:ring-2 focus:ring-indigo-400
                                         focus:border-transparent focus:bg-white transition duration-150"><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></textarea>
                    </div>

                    <!-- Resume Upload -->
                    <div>
                        <label for="resume"
                            class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Upload Resume <span class="text-red-500">*</span>
                        </label>
                        <input type="file" id="resume" name="resume"
                            accept=".pdf,.doc,.docx"
                            <?php echo empty($profile['resume_path']) ? 'required' : ''; ?>
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50
                                      text-slate-800 text-sm focus:outline-none focus:ring-2
                                      focus:ring-indigo-400 focus:border-transparent transition duration-150
                                      file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0
                                      file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700
                                      hover:file:bg-indigo-100">
                        <p class="text-xs text-slate-500 mt-1.5">PDF, DOC, or DOCX only. Max 10 MB.</p>
                        <?php if (!empty($profile['resume_path'])): ?>
                            <p class="text-xs text-green-600 mt-2">
                                Resume on file:
                                <a href="<?php echo BASE_URL . '/' . htmlspecialchars($profile['resume_path']); ?>"
                                    class="underline font-medium" target="_blank">View current resume</a>
                                &mdash; uploading a new file will replace it.
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Submit -->
                    <div class="pt-2">
                        <button type="submit"
                            class="w-full py-2.5 px-6 rounded-xl text-sm font-semibold text-white
                                       transition duration-150 focus:outline-none focus:ring-2
                                       focus:ring-offset-2 focus:ring-indigo-400"
                            style="background-color:#8b91dd;"
                            onmouseover="this.style.backgroundColor='#7a7fd4'"
                            onmouseout="this.style.backgroundColor='#8b91dd'">
                            Save Profile
                        </button>
                    </div>

                </form>
            </div><!-- /card body -->

        </div><!-- /card -->
    </main>

</div><!-- /flex container -->

<?php include_once __DIR__ . '/../partials/footer.php'; ?>

<?php if ($profile_incomplete): ?>
    <script>
        Swal.fire({
            icon: 'info',
            title: 'Profile Incomplete',
            text: 'Please complete your profile details and upload a resume before accessing the dashboard.',
            confirmButtonColor: '#8b91dd',
            confirmButtonText: 'Got it',
            background: '#fff',
            customClass: {
                popup: 'rounded-2xl',
                title: 'text-lg font-semibold',
                confirmButton: 'px-6 py-2 text-sm'
            }
        });
    </script>
<?php endif; ?>

<?php if ($error): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Profile Update Failed',
            text: '<?php echo addslashes($error); ?>',
            confirmButtonColor: '#8b91dd',
            confirmButtonText: 'Try Again',
            background: '#fff',
            customClass: {
                popup: 'rounded-2xl',
                title: 'text-lg font-semibold',
                confirmButton: 'px-6 py-2 text-sm'
            }
        });
    </script>
<?php endif; ?>

<?php if ($success): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Profile Saved!',
            text: '<?php echo addslashes($success); ?>',
            confirmButtonColor: '#8b91dd',
            confirmButtonText: 'Continue',
            background: '#fff',
            customClass: {
                popup: 'rounded-2xl',
                title: 'text-lg font-semibold',
                confirmButton: 'px-6 py-2 text-sm'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '<?php echo BASE_URL . '/seeker/dashboard'; ?>';
            }
        });
    </script>
<?php endif; ?>
