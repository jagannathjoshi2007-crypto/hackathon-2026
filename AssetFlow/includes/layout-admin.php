<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

function render_admin_header(string $title, array $user): void
{
    $current = basename($_SERVER['PHP_SELF']);
    $base = '../';
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= sanitize($title) ?> | AssetFlow Admin</title>
  <link rel="stylesheet" href="<?= $base ?>assets/css/style.css" />
  <script>window.ASSETFLOW_API = '../api/index.php?route=';</script>
  <script src="<?= $base ?>assets/js/api.js"></script>
</head>
<body class="panel-admin">
  <div class="admin-shell">
    <aside class="admin-sidebar">
      <div class="sidebar-brand">
        <span class="logo">AF</span>
        <div>
          <strong>AssetFlow</strong>
          <small>Admin Panel</small>
        </div>
      </div>
      <nav class="sidebar-nav">
        <a href="dashboard.php" class="<?= $current === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
        <?php if ($user['role'] === 'admin'): ?>
        <a href="organization.php" class="<?= $current === 'organization.php' ? 'active' : '' ?>">Organization</a>
        <?php endif; ?>
        <a href="assets.php" class="<?= $current === 'assets.php' ? 'active' : '' ?>">Assets</a>
        <a href="allocation.php" class="<?= $current === 'allocation.php' ? 'active' : '' ?>">Allocation</a>
        <a href="booking.php" class="<?= $current === 'booking.php' ? 'active' : '' ?>">Bookings</a>
        <a href="maintenance.php" class="<?= $current === 'maintenance.php' ? 'active' : '' ?>">Maintenance</a>
        <a href="audit.php" class="<?= $current === 'audit.php' ? 'active' : '' ?>">Audit</a>
        <a href="reports.php" class="<?= $current === 'reports.php' ? 'active' : '' ?>">Reports</a>
        <a href="notifications.php" class="<?= $current === 'notifications.php' ? 'active' : '' ?>">Notifications</a>
      </nav>
      <div class="sidebar-user">
        <span><?= sanitize($user['name']) ?></span>
        <small><?= sanitize(role_label($user['role'])) ?></small>
        <button class="btn ghost sidebar-logout" type="button">Logout</button>
      </div>
    </aside>
    <div class="admin-main">
      <header class="admin-topbar">
        <h1><?= sanitize($title) ?></h1>
        <span class="panel-badge admin-badge">Admin Panel</span>
      </header>
      <main class="page admin-page">
    <?php
}

function render_admin_footer(): void
{
    ?>
      </main>
    </div>
  </div>
  <script src="../assets/js/app.js"></script>
</body>
</html>
    <?php
}
