<?php include_once 'partials/header.php'; ?>
<?php
if (!isset($_SESSION["currentUser"])) {
  header('Location: /login');
  exit;
}

function getCurrentUser($id)
{
  global $pdo;
  $s = $pdo->prepare("SELECT id,email,full_name,phone,role,is_active,city,country,profile_completion_percentage,created_at,last_login FROM users WHERE id=?");
  $s->execute([$id]);
  return $s->fetch(PDO::FETCH_ASSOC);
}

$user = getCurrentUser($_SESSION["currentUser"]);
if (!$user) {
  session_destroy();
  header('Location: /login');
  exit;
}

$firstName  = explode(' ', $user['full_name'])[0];
$completion = (int)($user['profile_completion_percentage'] ?? 20);
$memberSince = date('M Y', strtotime($user['created_at']));
$roleLabel  = ucfirst(str_replace('_', ' ', $user['role']));
?>

<section class="dashboard-page">
  <div class="dashboard-wrap">

    <!-- Welcome banner -->
    <div class="welcome-banner">
      <div class="welcome-text">
        <h1>Welcome back, <?php echo htmlspecialchars($firstName); ?>!</h1>
        <p>You are signed in to your SecureAuth dashboard.</p>
      </div>
      <div class="active-badge">
        <span class="pulse-dot"></span>
        Active session
      </div>
    </div>
  </div>
</section>

<?php include_once 'partials/footer.php'; ?>