<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$connectionPath = __DIR__ . '/../../php/config/connection.php';
if (file_exists($connectionPath)) {
  require_once $connectionPath;
}

// Fetch user name for nav if logged in
$navUserId   = $_SESSION['user_id'] ?? null;
$navUserRole = $_SESSION['role'] ?? null;
$navUserName = $_SESSION['email'] ?? null;

// Navigation links
$navLinks = [
  ['label' => 'Home', 'href' => '/'],
  ['label' => 'Jobs', 'href' => '/jobs'],
  ['label' => 'Categories', 'href' => '/categories'],
  ['label' => 'Employers', 'href' => '/employers'],
];

$iconClass = 'w-3 h-3 inline-block mr-1 align-middle';

// Auth links – now with separate icon and text for responsive hiding
$authLinks = [];

// Show "Post a Job" for employers and non-logged-in users
if (!$navUserId || $navUserRole === 'employer') {
  $authLinks[] = [
    'icon'    => 'plus',
    'text'    => 'Post A job',
    'href'    => '/post-a-job',
    'variant' => 'primary',
  ];
}

// Show logout if logged in
if ($navUserId) {
  $authLinks[] = [
    'icon'    => 'log-out',
    'text'    => 'Sign out',
    'href'    => '/logout',
    'variant' => 'ghost',
  ];
} else {
  // Show register and login if not logged in
  $authLinks[] = [
    'icon'    => 'key-round',
    'text'    => 'Register',
    'href'    => '/register',
    'variant' => 'ghost',
  ];
  $authLinks[] = [
    'icon'    => 'log-in',
    'text'    => 'Login',
    'href'    => '/login',
    'variant' => 'ghost',
  ];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mboka Kenya</title>

  <!-- Fonts: site fonts + footer Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Quicksand:wght@300..700&family=Varela+Round&display=swap" rel="stylesheet">

  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

  <!-- SweetAlert2 CDN -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


  <style type="text/tailwindcss">
    /* ─── Site theme ─────────────────────────────────────────── */
    @theme {
      --font-montserrat:   "Montserrat",   ui-sans-serif;
      --font-quicksand:    "Quicksand",    ui-sans-serif;
      --font-varela-round: "Varela Round", ui-sans-serif;
      --font-inter:        "Inter",        ui-sans-serif;
      --font-sans: var(--font-montserrat), var(--font-quicksand), var(--font-varela-round);

      /* ─── Footer colour tokens ──────────────────────────────── */
      --color-footer-bg:     #1a2332;
      --color-footer-dark:   #141d2b;
      --color-footer-border: #243044;
      --color-footer-text:   #8a9bb5;
      --color-footer-link:   #7a8eac;
      --color-ck-pink:       #fb236a;
    }

    /* ─── Global layout ──────────────────────────────────────── */

    /* ─── Navbar drawer transitions ──────────────────────────── */
    .drawer {
      transition: transform 0.3s ease-in-out;
      transform: translateX(-100%);
    }
    .drawer.open {
      transform: translateX(0);
    }
    .backdrop {
      transition: opacity 0.3s ease;
      opacity: 0;
      pointer-events: none;
    }
    .backdrop.open {
      opacity: 1;
      pointer-events: auto;
    }

    /* ─── Footer ─────────────────────────────────────────────── */

    /* Textured dark background with subtle radial gradients */
    .footer-bg-texture {
      background-color: #1a2332;
      background-image:
        radial-gradient(ellipse at 20% 50%, rgba(232,20,77,0.04) 0%, transparent 60%),
        radial-gradient(ellipse at 80% 20%, rgba(30,60,100,0.15) 0%, transparent 50%);
    }

    /* Slightly darker strip for the copyright bar */
    .footer-bottom-bg {
      background-color: #131c29;
    }

    /* Pink › arrow before every nav link */
    .footer-nav-arrow::before {
      content: '›';
      margin-right: 8px;
      color: #fb236a;
      font-size: 1.1rem;
      font-weight: 700;
      line-height: 1;
    }

    /* Newsletter input */
    .footer-email-input::placeholder {
      color: #5a6e8a;
    }
    .footer-email-input:focus {
      outline: none;
      border-color: #fb236a;
      box-shadow: 0 0 0 2px rgba(232,20,77,0.15);
    }
  </style>
</head>

<body class="font-varela-round h-full min-h-screen flex flex-col">

  <!-- Navbar -->
  <header class=" flex items-center justify-center max-w-7xl">


    <nav id="siteNav" class="flex items-center justify-center fixed top-0 left-0 right-0 w-full h-16 transition-all duration-300 ease-in-out z-50 bg-transparent text-white ">
      <div class="max-w-7xl w-full px-4 flex items-center justify-between h-full">

        <!-- Left: Hamburger (visible on mobile) -->
        <button id="menuToggle" class="md:hidden text-current focus:outline-none" aria-label="Menu">
          <i data-lucide="menu" class="w-6 h-6"></i>
        </button>

        <!-- Logo (centered on mobile, left on larger screens) -->
        <a href="<?php echo $navUserId ? '/dashboard' : '/'; ?>" class="nav-logo font-bold text-2xl transition-colors flex items-center justify-center gap-2  left-1/2 transform max-md:-translate-x-1/2 md:static md:transform-none">
          <i data-lucide="briefcase" class="w-8 h-8 text-[#fb236a]" aria-hidden="true"></i>
          <span class="hidden md:inline">MBOKA KENYA</span>
          <span class="md:hidden">MK</span>
        </a>

        <!-- Center Navigation Links (hidden on mobile, shown in drawer) -->
        <div class="hidden md:flex items-center gap-8">
          <?php foreach ($navLinks as $link): ?>
            <a href="<?php echo $link['href']; ?>" class="nav-link font-medium text-sm transition-colors">
              <?php echo $link['label']; ?>
            </a>
          <?php endforeach; ?>
        </div>

        <!-- Right side: Actions -->
        <div class="flex items-center gap-1">
          <?php if ($navUserId): ?>
            <a href="<?php echo $navUserRole === 'seeker' ? '/seeker/dashboard' : ($navUserRole === 'admin' ? '/admin/dashboard' : '/dashboard'); ?>" class="nav-action flex items-center gap-1">
              <i data-lucide="user-round" class="w-5 h-5" aria-hidden="true"></i>
              <span class="hidden md:inline">Dahboard</span>
            </a>
          <?php endif; ?>

          <?php foreach ($authLinks as $link): ?>
            <?php
            $isPrimary = $link['variant'] === 'primary';
            $classes = 'flex items-center gap-1 p-2 rounded-md font-medium text-sm transition-all ';
            $classes .= $isPrimary ? 'bg-[#fb236a] text-white hover:bg-[#fb236a]/80 px-4' : '';
            ?>
            <a href="<?php echo $link['href']; ?>" class="nav-action <?php echo $isPrimary ? 'primary-action' : ''; ?> <?php echo $classes; ?>">
              <i data-lucide="<?php echo $link['icon']; ?>" class="w-5 h-5" aria-hidden="true"></i>
              <span class="hidden md:inline"><?php echo $link['text']; ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </nav>
  </header>

  <!-- Backdrop -->
  <div id="drawerBackdrop" class="backdrop fixed inset-0 bg-black/50 z-40"></div>

  <!-- Drawer Sidebar -->
  <div id="drawer" class="drawer fixed top-0 left-0 h-full w-64 bg-white shadow-lg z-50 p-4 overflow-y-auto">
    <div class="flex justify-between items-center mb-6">
      <span class="font-bold text-gray-800">Menu</span>
      <button id="closeDrawer" class="text-gray-600 hover:text-gray-900">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>
    <?php if ($navUserId): ?>
      <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-200">
        <div class="w-10 h-10 bg-pink-500 rounded-full flex items-center justify-center text-white font-semibold">
          <?php echo strtoupper(substr($navUserName, 0, 1)); ?>
        </div>
        <div>
          <div class="font-medium text-gray-800"><?php echo htmlspecialchars($navUserName); ?></div>
          <div class="text-sm text-gray-500"><?php echo ucfirst(str_replace('_', ' ', $navUserRole)); ?></div>
        </div>
      </div>
    <?php endif; ?>
    <ul class="space-y-3">
      <?php foreach ($navLinks as $link): ?>
        <li>
          <a href="<?php echo $link['href']; ?>" class="drawer-link block py-2 text-gray-700 hover:text-[#fb236a] transition-colors">
            <?php echo $link['label']; ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>



  <script>
    $(document).ready(function() {
      var $nav = $('#siteNav');
      var $logo = $nav.find('.nav-logo');
      var $navLinks = $nav.find('.nav-link');
      var $userTexts = $nav.find('.nav-username, .nav-role');
      var $actionLinks = $nav.find('.nav-action:not(.primary-action)');

      function updateNavOnScroll() {
        if ($(window).scrollTop() > 100) {
          $nav.addClass('bg-white shadow-[0_2px_5px_rgba(32,32,32,.1)]').removeClass('bg-transparent text-white').addClass('text-gray-700');
          $logo.addClass('text-gray-900').removeClass('text-white');
          $navLinks.addClass('text-gray-700 hover:text-gray-900').removeClass('text-white hover:text-white/90');
          $userTexts.addClass('text-gray-900').removeClass('text-white');
          $actionLinks.addClass('text-gray-700 hover:text-gray-900').removeClass('text-white hover:text-white/90');
        } else {
          $nav.removeClass('bg-white shadow-[0_2px_5px_rgba(32,32,32,.1)]').addClass('bg-transparent text-white').removeClass('text-gray-700');
          $logo.removeClass('text-gray-900').addClass('text-white');
          $navLinks.removeClass('text-gray-700 hover:text-gray-900').addClass('text-white hover:text-white/90');
          $userTexts.removeClass('text-gray-900').addClass('text-white');
          $actionLinks.removeClass('text-gray-700 hover:text-gray-900').addClass('text-white hover:text-white/90');
        }
      }

      $(window).on('scroll', updateNavOnScroll);
      updateNavOnScroll();

      // Drawer
      function openDrawer() {
        $('#drawer').addClass('open');
        $('#drawerBackdrop').addClass('open');
        $('body').css('overflow', 'hidden');
      }

      function closeDrawer() {
        $('#drawer').removeClass('open');
        $('#drawerBackdrop').removeClass('open');
        $('body').css('overflow', '');
      }

      $('#menuToggle').on('click', openDrawer);
      $('#closeDrawer').on('click', closeDrawer);
      $('#drawerBackdrop').on('click', closeDrawer);
      $('.drawer-link').on('click', closeDrawer);

      lucide.createIcons();
    });
  </script>
