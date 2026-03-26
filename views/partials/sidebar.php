<?php

/**
 * Sidebar component for authenticated seeker and employer pages
 * Include with: <?php include_once __DIR__ . '/../partials/sidebar.php'; ?>
 *
 * USAGE IN PAGE LAYOUT:
 * Wrap your page content like this (after header & banner):
 *
 * <div class="max-w-6xl mx-auto px-4 py-12 flex gap-10">
 *   <?php include_once __DIR__ . '/../partials/sidebar.php'; ?>
 *   <main class="flex-1 min-w-0"> ... your page content ... </main>
 * </div>
 */

if (session_status() === PHP_SESSION_NONE) session_start();

$userRole = $_SESSION['role'] ?? null;
$userId   = $_SESSION['user_id'] ?? null;

// Fetch saved jobs count for seeker badge
$savedJobsCount = 0;
if ($userRole === 'seeker' && $userId) {
  require_once __DIR__ . '/../../php/config/connection.php';
  $db = getDB();
  $stmt = $db->prepare("SELECT COUNT(*) as count FROM saved_jobs WHERE seeker_id = ?");
  $stmt->execute([$userId]);
  $savedJobsCount = $stmt->fetch()['count'] ?? 0;
}

$sidebarMenu = [];

if ($userRole === 'seeker') {
  $sidebarMenu = [
    ['icon' => 'layout-dashboard', 'label' => 'Dashboard',    'href' => '/seeker/dashboard', 'badge' => null],
    ['icon' => 'briefcase',        'label' => 'Saved Jobs',   'href' => '/seeker/saved-jobs', 'badge' => $savedJobsCount > 0 ? ($savedJobsCount > 9 ? '9+' : $savedJobsCount) : null],
    ['icon' => 'file-text',        'label' => 'Applications', 'href' => '/seeker/applications', 'badge' => null],
    ['icon' => 'user',             'label' => 'My Profile',   'href' => '/seeker/profile', 'badge' => null],
  ];
} elseif ($userRole === 'employer') {
  $sidebarMenu = [
    ['icon' => 'layout-dashboard', 'label' => 'Dashboard',       'href' => '/employer/dashboard', 'badge' => null],
    ['icon' => 'briefcase',        'label' => 'Posted Jobs',      'href' => '/employer/jobs', 'badge' => null],
    ['icon' => 'users',            'label' => 'Applications',     'href' => '/employer/applications', 'badge' => null],
    ['icon' => 'user',             'label' => 'Company Profile',   'href' => '/employer/profile', 'badge' => null],
  ];
} elseif ($userRole === 'admin') {
  $sidebarMenu = [
    ['icon' => 'layout-dashboard', 'label' => 'Dashboard',  'href' => '/admin/dashboard', 'badge' => null],
    ['icon' => 'users',            'label' => 'Users',      'href' => '/admin/users', 'badge' => null],
    ['icon' => 'shield',           'label' => 'Admins',     'href' => '/admin/admins', 'badge' => null],
    ['icon' => 'building',         'label' => 'Employers',  'href' => '/admin/employers', 'badge' => null],
    ['icon' => 'briefcase',        'label' => 'All Jobs',   'href' => '/admin/jobs', 'badge' => null],
    ['icon' => 'file-text',        'label' => 'Apps',       'href' => '/admin/applications', 'badge' => null],
    ['icon'=> 'bookmark',        'label'=> 'Categories',  'href' => '/admin/categories', 'badge' => null],
  ];
}

$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>

<!-- ===== Desktop Sidebar (inline, sticky positioning) ===== -->
<aside class="hidden lg:block w-56 flex-shrink-0">
  <nav class="sticky top-20 space-y-1">
    <?php foreach ($sidebarMenu as $item):
      $isActive = $currentPath === $item['href'];
    ?>
      <a
        href="<?php echo htmlspecialchars($item['href']); ?>"
        class="flex items-center gap-3 px-2 py-2.5 rounded-md transition-colors duration-150
               <?php echo $isActive
                  ? 'text-indigo-600 font-semibold'
                  : 'text-slate-500 hover:text-indigo-600'; ?>">
        <i data-lucide="<?php echo htmlspecialchars($item['icon']); ?>"
          class="w-5 h-5 flex-shrink-0
                  <?php echo $isActive ? 'text-indigo-600' : 'text-slate-400'; ?>"></i>
        <span class="text-sm flex-1"><?php echo htmlspecialchars($item['label']); ?></span>
        <?php if ($item['badge']): ?>
          <span class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full"><?php echo $item['badge']; ?></span>
        <?php endif; ?>
      </a>
    <?php endforeach; ?>

    <!-- Divider -->
    <div class="pt-2 pb-1">
      <hr class="border-slate-200">
    </div>

    <!-- Sign Out -->
    <a
      href="/logout"
      class="flex items-center gap-3 px-2 py-2.5 rounded-md text-slate-500 hover:text-indigo-600 transition-colors duration-150">
      <i data-lucide="log-out" class="w-5 h-5 flex-shrink-0 text-slate-400"></i>
      <span class="text-sm">Log out</span>
    </a>
  </nav>
</aside>

<!-- ===== Mobile Sidebar (Drawer) ===== -->
<div id="mobileSidebarBackdrop"
  class="fixed inset-0 bg-black/40 z-40 hidden lg:hidden"
  onclick="toggleMobileSidebar()">
</div>

<div id="mobileSidebar"
  class="fixed left-0 top-16 h-[calc(100vh-64px)] w-60 bg-white border-r border-slate-200 z-50
            transform -translate-x-full transition-transform duration-300 lg:hidden overflow-y-auto shadow-lg">
  <nav class="px-4 py-6 space-y-1">
    <?php foreach ($sidebarMenu as $item):
      $isActive = $currentPath === $item['href'];
    ?>
      <a
        href="<?php echo htmlspecialchars($item['href']); ?>"
        class="flex items-center gap-3 px-2 py-2.5 rounded-md transition-colors duration-150
               <?php echo $isActive
                  ? 'text-indigo-600 font-semibold'
                  : 'text-slate-500 hover:text-indigo-600'; ?>">
        <i data-lucide="<?php echo htmlspecialchars($item['icon']); ?>"
          class="w-5 h-5 flex-shrink-0
                  <?php echo $isActive ? 'text-indigo-600' : 'text-slate-400'; ?>"></i>
        <span class="text-sm flex-1"><?php echo htmlspecialchars($item['label']); ?></span>
        <?php if ($item['badge']): ?>
          <span class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full"><?php echo $item['badge']; ?></span>
        <?php endif; ?>
      </a>
    <?php endforeach; ?>

    <div class="pt-2 pb-1">
      <hr class="border-slate-200">
    </div>

    <a
      href="/logout"
      class="flex items-center gap-3 px-2 py-2.5 rounded-md text-slate-500 hover:text-slate-800 transition-colors duration-150">
      <i data-lucide="log-out" class="w-5 h-5 flex-shrink-0 text-slate-400"></i>
      <span class="text-sm">Log out</span>
    </a>
  </nav>
</div>

<script>
  window.toggleMobileSidebar = function() {
    const sidebar = document.getElementById('mobileSidebar');
    const backdrop = document.getElementById('mobileSidebarBackdrop');
    sidebar.classList.toggle('-translate-x-full');
    backdrop.classList.toggle('hidden');
  };
</script>
