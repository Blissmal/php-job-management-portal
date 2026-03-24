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
    <h1 class="text-3xl md:text-4xl font-bold text-white mb-3 tracking-tight">Sign In</h1>
    <p class="text-gray-300 text-sm">
      <a href="/" class="underline underline-offset-2 hover:text-white transition-colors">Home</a>
      <span class="mx-2 opacity-50">—</span>
      <span class="text-white">Login</span>
    </p>
  </div>
</section>

<!-- Login Form -->
<section class="flex items-start justify-center py-20">
  <div class="w-full max-w-md mx-4relative z-20">
    <div class="bg-white">

      <!-- Card Header -->
      <div class="px-8 pt-8 pb-6 border-b border-slate-100">
        <h2 class="text-xl font-bold text-slate-800 tracking-tight">Welcome Back</h2>
        <p class="text-sm text-slate-500 mt-1">Enter your credentials to access your account</p>
      </div>

      <!-- Card Body -->
      <div class="px-8 py-6">

        <?php if ($authError): ?>
          <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-5 text-sm">
            <span class="mt-0.5 text-base">⚠</span>
            <span><?php echo htmlspecialchars($authError); ?></span>
          </div>
        <?php endif; ?>

        <form method="POST" action="/php/function/login.php" class="space-y-5">

          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">
              Email Address
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

          <!-- Password -->
          <div>
            <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">
              Password
            </label>
            <input
              type="password"
              id="password"
              name="password"
              placeholder="Enter your password"
              autocomplete="current-password"
              required
              class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-sm placeholder-slate-400
                     focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent focus:bg-white
                     transition duration-150">
          </div>

          <!-- Forgot Password -->
          <div class="flex justify-end -mt-2">
            <a href="#" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium transition-colors">
              Forgot your password?
            </a>
          </div>

          <!-- Submit -->
          <div class="pt-1">
            <button
              type="submit"
              class="w-full py-2.5 px-6 rounded-xl text-sm font-semibold text-white transition duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-400"
              style="background-color:#198754;"
              onmouseover="this.style.backgroundColor='#157347'"
              onmouseout="this.style.backgroundColor='#198754'">
              Sign In to Account
            </button>
          </div>

        </form>
      </div>

      <!-- Card Footer -->
      <div class="px-8 pb-7 text-center">
        <p class="text-sm text-slate-500">
          Don't have an account?
          <a href="/register" class="text-indigo-600 font-semibold hover:text-indigo-800 ml-1 transition-colors">Sign Up Now</a>
        </p>
      </div>

    </div>
  </div>
</section>

<?php include_once 'partials/footer.php'; ?>
