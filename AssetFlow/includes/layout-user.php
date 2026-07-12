<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

function render_user_header(string $title, array $user): void
{
    $current = basename($_SERVER['PHP_SELF']);
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= sanitize($title) ?> | AssetFlow</title>
  <link rel="stylesheet" href="../assets/css/style.css" />
  <script>window.ASSETFLOW_API = '../api/index.php?route=';</script>
  <script src="../assets/js/api.js"></script>
</head>
<body class="panel-user">
  <nav class="navbar user-navbar">
    <div class="nav-brand">
      <span class="logo user-logo">AF</span>
      <div>
        <strong>AssetFlow</strong>
        <small>User Portal</small>
      </div>
    </div>
    <button class="nav-toggle" id="navToggle" aria-label="Toggle menu">☰</button>
    <div class="nav-links" id="navLinks">
      <a href="dashboard.php" class="<?= $current === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
      <a href="my-assets.php" class="<?= $current === 'my-assets.php' ? 'active' : '' ?>">My Assets</a>
      <a href="booking.php" class="<?= $current === 'booking.php' ? 'active' : '' ?>">Book Resource</a>
      <a href="maintenance.php" class="<?= $current === 'maintenance.php' ? 'active' : '' ?>">Maintenance</a>
      <a href="requests.php" class="<?= $current === 'requests.php' ? 'active' : '' ?>">Requests</a>
      <a href="../index.php">Home</a>
      <a href="notifications.php" class="<?= $current === 'notifications.php' ? 'active' : '' ?>">Notifications</a>
    </div>
    <div class="nav-user">
      <span class="panel-badge user-badge">User Panel</span>
      <span><?= sanitize($user['name']) ?> · <?= sanitize(role_label($user['role'])) ?></span>
      <button class="btn ghost" id="logoutBtn" type="button">Logout</button>
    </div>
  </nav>
  <main class="page user-page">
    <header class="page-header">
      <h1><?= sanitize($title) ?></h1>
    </header>
    <?php
}

function render_user_footer(): void
{
    ?>
  </main>
  <script src="../assets/js/app.js"></script>
</body>
</html>
    <?php
}
