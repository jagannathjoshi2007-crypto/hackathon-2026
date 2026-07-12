<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
$user = current_user();
if (!$user) { header('Location: login.php'); exit; }
header('Location: ' . panel_url_for_role($user['role']));
exit;
