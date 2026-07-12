<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

function render_header(string $title, array $user): void
{
    $current = basename($_SERVER['PHP_SELF']);
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= sanitize($title) ?> | AssetFlow</title>
  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>
  <nav class="navbar">
    <div class="nav-brand">
      <span class="logo">AF</span>
      <div>
        <strong>AssetFlow</strong>
        <small>Enterprise Asset Management</small>
      </div>
    </div>
    <button class="nav-toggle" id="navToggle" aria-label="Toggle menu">☰</button>
    <div class="nav-links" id="navLinks">
      <a href="dashboard.php" class="<?= $current === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
      <?php if ($user['role'] === 'admin'): ?>
      <a href="organization.php" class="<?= $current === 'organization.php' ? 'active' : '' ?>">Organization</a>
      <?php endif; ?>
      <a href="assets.php" class="<?= $current === 'assets.php' ? 'active' : '' ?>">Assets</a>
      <a href="allocation.php" class="<?= $current === 'allocation.php' ? 'active' : '' ?>">Allocation</a>
      <a href="booking.php" class="<?= $current === 'booking.php' ? 'active' : '' ?>">Booking</a>
      <a href="maintenance.php" class="<?= $current === 'maintenance.php' ? 'active' : '' ?>">Maintenance</a>
      <?php if (in_array($user['role'], ['admin', 'asset_manager'], true)): ?>
      <a href="audit.php" class="<?= $current === 'audit.php' ? 'active' : '' ?>">Audit</a>
      <?php endif; ?>
      <a href="reports.php" class="<?= $current === 'reports.php' ? 'active' : '' ?>">Reports</a>
      <a href="notifications.php" class="<?= $current === 'notifications.php' ? 'active' : '' ?>">Notifications</a>
    </div>
    <div class="nav-user">
      <span><?= sanitize($user['name']) ?> · <?= sanitize(role_label($user['role'])) ?></span>
      <button class="btn ghost" id="logoutBtn" type="button">Logout</button>
    </div>
  </nav>
  <main class="page">
    <header class="page-header">
      <h1><?= sanitize($title) ?></h1>
    </header>
    <?php
}

function render_footer(): void
{
    ?>
  </main>
  <script src="assets/js/api.js"></script>
  <script src="assets/js/app.js"></script>
</body>
</html>
    <?php
}
