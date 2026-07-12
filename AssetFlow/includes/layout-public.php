<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

function render_public_header(string $title, string $active = ''): void
{
    $user = current_user();
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= sanitize($title) ?> | AssetFlow</title>
  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body class="public-site">
  <nav class="public-navbar">
    <a href="index.php" class="nav-brand">
      <span class="logo">AF</span>
      <div>
        <strong>AssetFlow</strong>
        <small>Employee Portal</small>
      </div>
    </a>
    <button class="nav-toggle" id="navToggle" type="button" aria-label="Menu">☰</button>
    <div class="public-nav-links" id="navLinks">
      <a href="index.php" class="<?= $active === 'home' ? 'active' : '' ?>">Home</a>
      <a href="index.php#features" class="<?= $active === 'features' ? 'active' : '' ?>">Features</a>
      <?php if ($user): ?>
        <?php if (is_admin_role($user['role'])): ?>
        <a href="admin/dashboard.php" class="btn nav-btn primary">Admin Panel</a>
        <?php else: ?>
        <a href="user/dashboard.php" class="btn nav-btn primary">My Dashboard</a>
        <?php endif; ?>
        <button type="button" class="btn nav-btn ghost" id="publicLogout">Logout</button>
      <?php else: ?>
        <a href="login.php" class="<?= $active === 'login' ? 'active' : '' ?>">Login</a>
        <a href="signup.php" class="btn nav-btn signup-btn <?= $active === 'signup' ? 'active' : '' ?>">Sign Up</a>
      <?php endif; ?>
    </div>
  </nav>
    <?php
}

function render_public_footer(): void
{
    ?>
  <footer class="public-footer">
    <div class="footer-inner">
      <div>
        <strong>AssetFlow</strong>
        <p>Enterprise Asset & Resource Management for employees and teams.</p>
      </div>
      <div class="footer-links">
        <a href="index.php">Home</a>
        <a href="login.php">Employee Login</a>
        <a href="signup.php">Create Account</a>
      </div>
    </div>
    <small>© <?= date('Y') ?> AssetFlow · Hackathon 2026</small>
  </footer>
  <script>window.ASSETFLOW_API = 'api/index.php?route=';</script>
  <script src="assets/js/api.js"></script>
  <script src="assets/js/public.js"></script>
</body>
</html>
    <?php
}
