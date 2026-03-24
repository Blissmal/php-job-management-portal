<?php
session_start();

// ─── Middleware ────────────────────────────────────────────────────────────────

function requireAuth(): void {
    if (empty($_SESSION['currentUser'])) {
        header('Location: /login');
        exit;
    }
}

function requireGuest(): void {
    if (!empty($_SESSION['currentUser'])) {
        header('Location: /dashboard');
        exit;
    }
}

function requireRole(string ...$roles): void {
    requireAuth();
    if (!in_array($_SESSION['currentUserRole'] ?? '', $roles, true)) {
        http_response_code(403);
        include_once 'views/403.php';
        exit;
    }
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
    ['GET',  '/post-a-job',     'views/post-a-job.php',     null],
    ['POST', '/post-a-job',     'php/functions/store-job.php', 'requireAuth'],
    ['GET',  '/login',          'views/login.php',          'requireGuest'],
    ['POST', '/login',          'php/functions/login.php',  'requireGuest'],
    ['GET',  '/register',       'views/register.php',       'requireGuest'],
    ['POST', '/register',       'php/functions/register.php', 'requireGuest'],
    ['GET',  '/dashboard',      'views/dashboard.php',      'requireAuth'],
    ['GET',  '/logout',         'php/functions/logout.php', 'requireAuth'],
    ['GET',  '/index',          'views/login.php',          null],
    ['GET',  '/index.php',      'views/login.php',          null],
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
