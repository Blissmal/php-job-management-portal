<?php include_once '../partials/header.php'; ?>
<?php
require_once __DIR__ . '/../../php/config/connection.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Check if user is logged in and is an employer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header('Location: /login');
    exit;
}

$uid = (int)$_SESSION['user_id'];
$db = getDB();

// Fetch existing profile data if any
$stmt = $db->prepare("SELECT * FROM employer_profiles WHERE user_id = ?");
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
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-3 tracking-tight">Complete Your Company Profile</h1>
        <p class="text-gray-300 text-sm">
            <span class="text-white">Set up your employer profile to start posting jobs</span>
        </p>
    </div>
</section>

<!-- Profile Form -->
<section class="flex items-start justify-center py-20">
    <div class="w-full max-w-2xl mx-4 relative z-20">
        <div class="bg-white rounded-2xl">

            <!-- Card Header -->
            <div class="px-8 pt-8 pb-6 border-b border-slate-100">
                <h2 class="text-xl font-bold text-slate-800 tracking-tight">Employer Profile</h2>
                <p class="text-sm text-slate-500 mt-1">Help job seekers learn about your company (all fields required)</p>
            </div>

            <!-- Card Body -->
            <div class="px-8 py-6">
                <form method="POST" action="../../php/function/profile.php" class="space-y-5">

                    <!-- Company Name -->
                    <div>
                        <label for="company_name" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Company Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="company_name"
                            name="company_name"
                            placeholder="Your company name"
                            value="<?php echo htmlspecialchars($profile['company_name'] ?? ''); ?>"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-sm placeholder-slate-400
                     focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent focus:bg-white
                     transition duration-150">
                    </div>

                    <!-- Industry -->
                    <div>
                        <label for="industry" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Industry <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="industry"
                            name="industry"
                            placeholder="e.g., Technology, Finance, Healthcare"
                            value="<?php echo htmlspecialchars($profile['industry'] ?? ''); ?>"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-sm placeholder-slate-400
                     focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent focus:bg-white
                     transition duration-150">
                    </div>

                    <!-- Website -->
                    <div>
                        <label for="website" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Company Website <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="url"
                            id="website"
                            name="website"
                            placeholder="https://www.yourcompany.com"
                            value="<?php echo htmlspecialchars($profile['website'] ?? ''); ?>"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-sm placeholder-slate-400
                     focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent focus:bg-white
                     transition duration-150">
                    </div>

                    <!-- Company Description -->
                    <div>
                        <label for="description" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            About Your Company <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            id="description"
                            name="description"
                            placeholder="Tell job seekers about your company, mission, values, and culture"
                            required
                            rows="5"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-sm placeholder-slate-400
                     focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent focus:bg-white
                     transition duration-150"><?php echo htmlspecialchars($profile['description'] ?? ''); ?></textarea>
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

<?php include_once '../partials/footer.php'; ?>

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
