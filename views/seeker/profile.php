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
$db = getDB();

// Fetch existing profile data if any
$stmt = $db->prepare("SELECT * FROM job_seeker_profiles WHERE user_id = ?");
$stmt->execute([$uid]);
$profile = $stmt->fetch();

$error = $_SESSION['error'] ?? null;
$success = $_SESSION['success'] ?? null;
unset($_SESSION['error']);
unset($_SESSION['success']);
?>

<!-- Hero Banner -->
<section class="relative w-full overflow-hidden" style="height:280px;">
    <div class="absolute inset-0" style="background: linear-gradient(135deg, #8b91dd 0%, #10195d 70%, #10195d 100%); opacity:0.92;"></div>
    <div class="absolute inset-0 flex flex-col items-center justify-end pb-10 z-10">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-3 tracking-tight">Complete Your Profile</h1>
        <p class="text-gray-300 text-sm">
            <span class="text-white">Set up your job seeker profile to get started</span>
        </p>
    </div>
</section>

<!-- Profile Form -->
<section class="flex items-start justify-center py-20">
    <div class="w-full max-w-2xl mx-4 relative z-20">
        <div class="bg-white rounded-2xl">

            <!-- Card Header -->
            <div class="px-8 pt-8 pb-6 border-b border-slate-100">
                <h2 class="text-xl font-bold text-slate-800 tracking-tight">Job Seeker Profile</h2>
                <p class="text-sm text-slate-500 mt-1">This information helps employers find you (all fields required)</p>
            </div>

            <!-- Card Body -->
            <div class="px-8 py-6">
                <form method="POST" action="../../php/function/profile.php" enctype="multipart/form-data" class="space-y-5">

                    <!-- Full Name -->
                    <div>
                        <label for="full_name" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="full_name"
                            name="full_name"
                            placeholder="Your full name"
                            value="<?php echo htmlspecialchars($profile['full_name'] ?? ''); ?>"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-sm placeholder-slate-400
                     focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent focus:bg-white
                     transition duration-150">
                    </div>

                    <!-- Headline -->
                    <div>
                        <label for="headline" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Professional Headline <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="headline"
                            name="headline"
                            placeholder="e.g., Full Stack Developer | React & Node.js"
                            value="<?php echo htmlspecialchars($profile['headline'] ?? ''); ?>"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-sm placeholder-slate-400
                     focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent focus:bg-white
                     transition duration-150">
                    </div>

                    <!-- Location -->
                    <div>
                        <label for="location" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Location <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="location"
                            name="location"
                            placeholder="City, Country"
                            value="<?php echo htmlspecialchars($profile['location'] ?? ''); ?>"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-sm placeholder-slate-400
                     focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent focus:bg-white
                     transition duration-150">
                    </div>

                    <!-- Skills -->
                    <div>
                        <label for="skills" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Skills <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            id="skills"
                            name="skills"
                            placeholder="e.g., JavaScript, React, Node.js, MySQL, Docker (comma separated)"
                            required
                            rows="3"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-sm placeholder-slate-400
                     focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent focus:bg-white
                     transition duration-150"><?php echo htmlspecialchars($profile['skills'] ?? ''); ?></textarea>
                    </div>

                    <!-- Bio -->
                    <div>
                        <label for="bio" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            About You <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            id="bio"
                            name="bio"
                            placeholder="Tell employers about yourself, your experience, and career goals"
                            required
                            rows="4"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-sm placeholder-slate-400
                     focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent focus:bg-white
                     transition duration-150"><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></textarea>
                    </div>

                    <!-- Resume Upload -->
                    <div>
                        <label for="resume" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Upload Resume <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="file"
                                id="resume"
                                name="resume"
                                accept=".pdf,.doc,.docx"
                                <?php echo empty($profile['resume_path']) ? 'required' : ''; ?>
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-sm
                       focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent
                       transition duration-150 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                        <p class="text-xs text-slate-500 mt-1.5">PDF, DOC, or DOCX only. Max 10 MB.</p>
                        <?php if ($profile && $profile['resume_path']): ?>
                            <p class="text-xs text-green-600 mt-2">Resume uploaded: <a href="<?php echo BASE_URL . '/' . htmlspecialchars($profile['resume_path']); ?>" class="underline" target="_blank">View</a></p>
                        <?php endif; ?>
                    </div>

                    <!-- Submit -->
                    <div class="pt-1">
                        <button
                            type="submit"
                            class="w-full py-2.5 px-6 rounded-xl text-sm font-semibold text-white transition duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-400"
                            style="background-color:#198754;"
                            onmouseover="this.style.backgroundColor='#157347'"
                            onmouseout="this.style.backgroundColor='#198754'">
                            Complete Profile
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</section>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>

<?php if ($error): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Profile Update Failed',
            text: '<?php echo addslashes($error); ?>',
            confirmButtonColor: '#198754',
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
            title: 'Profile Complete!',
            text: '<?php echo addslashes($success); ?>',
            confirmButtonColor: '#198754',
            confirmButtonText: 'Go to Dashboard',
            background: '#fff',
            customClass: {
                popup: 'rounded-2xl',
                title: 'text-lg font-semibold',
                confirmButton: 'px-6 py-2 text-sm'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '<?php echo BASE_URL . '/dashboard'; ?>';
            }
        });
    </script>
<?php endif; ?>
