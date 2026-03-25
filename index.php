<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', rtrim((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'], '/'));
}

session_start();
require_once 'php/config/profile_guard.php';

// ─── Middleware ────────────────────────────────────────────────────────────────

function requireAuth(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }
}

function requireGuest(): void
{
    if (!empty($_SESSION['user_id'])) {
        header('Location: /dashboard');
        exit;
    }
}

function requireRole(string ...$roles): void
{
    requireAuth();
    if (!in_array($_SESSION['role'] ?? '', $roles, true)) {
        http_response_code(403);
        include_once 'views/403.php';
        exit;
    }
}

function requireSeeker(): void
{
    requireRole('seeker');
}

function requireEmployer(): void
{
    requireRole('employer');
}

function requireSeekerWithProfile(): void
{
    requireSeeker();
    requireProfileComplete();
}

function requireEmployerWithProfile(): void
{
    requireEmployer();
    requireProfileComplete();
}

// ─── Route definitions ─────────────────────────────────────────────────────────
//
//  Each route is:  [ 'METHOD', '/path', 'views/file.php', callable|null ]
//  Use null for no middleware.
//  Paths support one named segment parameter:  /jobs/:id
//

$routes = [
    ['GET',  '/',               'views/home.php',           null],
    ['GET',  '/jobs',           'views/jobs.php',           null],
    ['GET',  '/categories',           'views/categories.php',           null],
    ['GET',  '/jobs/:id',       'views/job-single.php',     null],
    ['GET',  '/employers',      'views/employers.php',      null],
    ['GET',  '/post-a-job',     'views/post-a-job.php',     'requireEmployerWithProfile'],
    ['POST', '/post-a-job',     'php/functions/store-job.php', 'requireEmployerWithProfile'],
    ['GET',  '/login',          'views/login.php',          'requireGuest'],
    ['POST', '/login',          'php/function/login.php',  'requireGuest'],
    ['GET',  '/register',       'views/register.php',       'requireGuest'],
    ['POST', '/register',       'php/functions/register.php', 'requireGuest'],
    ['GET',  '/seeker/dashboard',      'views/seeker/dashboard.php',      'requireSeekerWithProfile'],
    ['GET',  '/seeker/saved-jobs',      'views/seeker/saved-jobs.php',      'requireSeekerWithProfile'],
    ['GET',  '/seeker/applications',      'views/seeker/applications.php',      'requireSeekerWithProfile'],
    ['GET',  '/seeker/profile',      'views/seeker/profile.php',      'requireSeeker'],
    ['GET',  '/employer/dashboard',      'views/employer/dashboard.php',      'requireEmployerWithProfile'],
    ['GET',  '/employer/profile',      'views/employer/profile.php',      'requireEmployer'],
    ['GET',  '/admin/profile',      'views/admin/profile.php',      'requireAuth'],
    ['GET',  '/logout',         'php/functions/logout.php', 'requireAuth'],
];

// ─── Dispatcher ────────────────────────────────────────────────────────────────

$method  = $_SERVER['REQUEST_METHOD'];
$uri     = strtok($_SERVER['REQUEST_URI'], '?'); // strip query string
$uri     = rtrim($uri, '/') ?: '/';              // normalise trailing slash; keep root as "/"

$params  = [];   // will hold named route params like :id
$matched = false;

foreach ($routes as [$routeMethod, $routePath, $view, $middleware]) {

    // Convert :param segments into a named capture regex
    $pattern = preg_replace('#/:([^/]+)#', '/(?P<$1>[^/]+)', $routePath);
    $pattern = '#^' . $pattern . '$#';

    if ($method !== $routeMethod || !preg_match($pattern, $uri, $matches)) {
        continue;
    }

    // Extract only named captures (the :param values)
    $params = array_filter(
        $matches,
        fn($key) => is_string($key),
        ARRAY_FILTER_USE_KEY
    );

    // Run middleware if defined
    if ($middleware !== null) {
        $middleware();
    }

    // Expose params to the view as $_ROUTE
    $_ROUTE = $params;

    include_once $view;
    $matched = true;
    break;
}

if (!$matched) {
    // Check if the path exists but method is wrong (405 vs 404)
    $pathExists = array_filter($routes, fn($r) => $r[1] === $uri);
    if ($pathExists) {
        http_response_code(405);
        include_once 'views/405.php';
    } else {
        http_response_code(404);
        include_once 'views/404.php';
    }
}
