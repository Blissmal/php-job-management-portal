<?php include_once 'partials/header.php'; ?>
<?php
if (isset($_SESSION["currentUser"])) {
  header('Location: /dashboard');
  exit;
}
$authError = $_SESSION['authError'] ?? null;
unset($_SESSION['authError']);
?>

<!-- Hero Banner -->
<section class="relative w-full overflow-hidden" style="height:280px;">
  <div class="absolute inset-0" style="background: linear-gradient(135deg, #8b91dd 0%, #10195d 70%, #10195d 100%); opacity:0.92;"></div>
  <div class="absolute inset-0 flex flex-col items-center justify-end pb-10 z-10">
    <h1 class="text-3xl md:text-4xl font-bold text-white mb-3 tracking-tight">Register</h1>
    <p class="text-gray-300 text-sm">
      <a href="/" class="underline underline-offset-2 hover:text-white transition-colors">Home</a>
      <span class="mx-2 opacity-50">—</span>
      <span class="text-white">Register</span>
    </p>
  </div>
</section>

<!-- Register Form -->
<section class=" flex items-start justify-center">
  <div class="w-full max-w-md mx-4 relative z-20">
    <div class="bg-white rounded-2xl ">

      <!-- Card Header -->
      <div class="px-8 pt-8 pb-6 border-b border-slate-100">
        <h2 class="text-xl font-bold text-slate-800 tracking-tight">Create an Account</h2>
        <p class="text-sm text-slate-500 mt-1">Fill in the details below to get started</p>
      </div>

      <!-- Card Body -->
      <div class="px-8 py-6">

        <?php if ($authError): ?>
          <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-5 text-sm">
            <span class="mt-0.5 text-base">⚠</span>
            <span><?php echo htmlspecialchars($authError); ?></span>
          </div>
        <?php endif; ?>

        <form method="POST" action="/php/function/register.php" id="regForm" class="space-y-5">

          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">
              Email Address <span class="text-red-500">*</span>
            </label>
            <input
              type="email"
              id="email"
              name="email"
              placeholder="you@example.com"
              value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
              required
              class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-sm placeholder-slate-400
                     focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent focus:bg-white
                     transition duration-150">
          </div>

          <!-- Account Type -->
          <div>
            <label for="role" class="block text-sm font-semibold text-slate-700 mb-1.5">
              Account Type <span class="text-red-500">*</span>
            </label>
            <div class="grid grid-cols-2 gap-3" id="roleSelector">

              <label class="role-option cursor-pointer">
                <input type="radio" name="role" value="job_seeker" class="sr-only"
                  <?php echo (($_POST['role'] ?? 'job_seeker') === 'job_seeker') ? 'checked' : ''; ?>>
                <div class="role-card flex flex-col items-center gap-2 px-4 py-3.5 rounded-xl border-2 border-slate-200 bg-slate-50 transition duration-150 hover:border-indigo-300">
                  <i data-lucide="search"></i>
                  <span class="text-sm font-semibold text-slate-700">Job Seeker</span>
                  <span class="text-xs text-slate-400 text-center leading-tight">Find an opportunity</span>
                </div>
              </label>

              <label class="role-option cursor-pointer">
                <input type="radio" name="role" value="employer" class="sr-only"
                  <?php echo (($_POST['role'] ?? '') === 'employer') ? 'checked' : ''; ?>>
                <div class="role-card flex flex-col items-center gap-2 px-4 py-3.5 rounded-xl border-2 border-slate-200 bg-slate-50 transition duration-150 hover:border-indigo-300">
                  <i data-lucide="building-2"></i>
                  <span class="text-sm font-semibold text-slate-700">Employer</span>
                  <span class="text-xs text-slate-400 text-center leading-tight">Hire the best talent</span>
                </div>
              </label>

            </div>
          </div>

          <!-- Password -->
          <div>
            <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">
              Password <span class="text-red-500">*</span>
            </label>
            <input
              type="password"
              id="password"
              name="password"
              placeholder="Create a strong password"
              required
              class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-sm placeholder-slate-400
                     focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent focus:bg-white
                     transition duration-150">
            <!-- Password requirements -->
            <div class="mt-2.5 grid grid-cols-2 gap-2 text-xs" id="pwReqs">

              <div class="pw-req flex items-center gap-2 text-slate-400" id="req-len">
                <i data-lucide="x" class="icon-x w-4 h-4"></i>
                <i data-lucide="check" class="icon-check w-4 h-4 hidden text-green-500"></i>
                <span>8+ characters</span>
              </div>

              <div class="pw-req flex items-center gap-2 text-slate-400" id="req-up">
                <i data-lucide="x" class="icon-x w-4 h-4"></i>
                <i data-lucide="check" class="icon-check w-4 h-4 hidden text-green-500"></i>
                <span>Uppercase letter</span>
              </div>

              <div class="pw-req flex items-center gap-2 text-slate-400" id="req-num">
                <i data-lucide="x" class="icon-x w-4 h-4"></i>
                <i data-lucide="check" class="icon-check w-4 h-4 hidden text-green-500"></i>
                <span>One number</span>
              </div>

              <div class="pw-req flex items-center gap-2 text-slate-400" id="req-sp">
                <i data-lucide="x" class="icon-x w-4 h-4"></i>
                <i data-lucide="check" class="icon-check w-4 h-4 hidden text-green-500"></i>
                <span>Special character</span>
              </div>

            </div>
          </div>

          <!-- Confirm Password -->
          <div>
            <label for="confirm_password" class="block text-sm font-semibold text-slate-700 mb-1.5">
              Confirm Password <span class="text-red-500">*</span>
            </label>
            <input
              type="password"
              id="confirm_password"
              name="confirm_password"
              placeholder="Re-enter your password"
              required
              class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-sm placeholder-slate-400
                     focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent focus:bg-white
                     transition duration-150">
            <p class="text-xs mt-1.5 text-red-500 hidden" id="matchError">Passwords do not match</p>
          </div>

          <!-- Submit -->
          <div class="pt-1">
            <button
              type="submit"
              id="submitBtn"
              disabled
              class="w-full py-2.5 px-6 rounded-xl text-sm font-semibold text-white transition duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-400 disabled:opacity-50 disabled:cursor-not-allowed"
              style="background-color:#198754;"
              onmouseover="if(!this.disabled) this.style.backgroundColor='#157347'"
              onmouseout="this.style.backgroundColor='#198754'">
              Create Account
            </button>
          </div>

        </form>
      </div>

      <!-- Card Footer -->
      <div class="px-8 pb-7 text-center">
        <p class="text-sm text-slate-500">
          Already have an account?
          <a href="/login" class="text-indigo-600 font-semibold hover:text-indigo-800 ml-1 transition-colors">Sign In Now</a>
        </p>
      </div>

    </div>
  </div>
</section>

<?php include_once 'partials/footer.php'; ?>

<style>
  /* Role card active state */
  .role-option input[type="radio"]:checked+.role-card {
    border-color: #6366f1;
    background-color: #eef2ff;
  }

  .role-option input[type="radio"]:checked+.role-card span.text-slate-700 {
    color: #4338ca;
  }

  /* Password requirement met state */
  .pw-req.met {
    color: #16a34a;
  }

  .pw-req.met .req-icon {
    background-color: #16a34a;
    border-color: #16a34a;
    color: white;
  }

  .pw-req.met .req-icon::before {
    content: '✓';
  }

  .pw-req:not(.met) .req-icon::before {
    content: '✕';
  }
</style>

<script>
  const passwordInput = document.getElementById('password');
  const confirmPasswordInput = document.getElementById('confirm_password');
  const submitButton = document.getElementById('submitBtn');
  const matchError = document.getElementById('matchError');

  const requirements = {
    len: /^.{8,}$/,
    up: /[A-Z]/,
    num: /[0-9]/,
    sp: /[!@#$%^&*]/
  };

  function checkPasswordRequirements() {
    let allMet = true;
    for (const [key, regex] of Object.entries(requirements)) {
      const el = document.getElementById('req-' + key);
      if (regex.test(passwordInput.value)) {
        el.classList.add('met');
      } else {
        el.classList.remove('met');
        allMet = false;
      }
    }

    const matches = passwordInput.value === confirmPasswordInput.value && confirmPasswordInput.value !== '';
    if (confirmPasswordInput.value !== '') {
      matchError.classList.toggle('hidden', matches);
    }

    submitButton.disabled = !(allMet && matches);
  }

  passwordInput.addEventListener('input', checkPasswordRequirements);
  confirmPasswordInput.addEventListener('input', checkPasswordRequirements);

  // Role card keyboard support
  document.querySelectorAll('.role-option').forEach(label => {
    label.addEventListener('keydown', e => {
      if (e.key === ' ' || e.key === 'Enter') {
        label.querySelector('input').checked = true;
      }
    });
  });
</script>
