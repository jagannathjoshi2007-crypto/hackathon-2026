<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

$messages = [];
$done = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $host = trim($_POST['db_host'] ?? '127.0.0.1');
        $user = trim($_POST['db_user'] ?? 'root');
        $pass = $_POST['db_pass'] ?? '';
        $name = trim($_POST['db_name'] ?? 'assetflow');

        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        $schema = file_get_contents(__DIR__ . '/database/schema.sql');
        foreach (array_filter(array_map('trim', explode(';', $schema))) as $statement) {
            if ($statement !== '') {
                $pdo->exec($statement);
            }
        }

        $pdo->exec("USE `$name`");

        $adminEmail = 'admin@assetflow.com';
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare('INSERT IGNORE INTO users (id, name, email, password_hash, role, status) VALUES (1, ?, ?, ?, "admin", "active")')
            ->execute(['System Admin', $adminEmail, $hash]);

        $pdo->exec("INSERT IGNORE INTO departments (id, name, status) VALUES (1, 'General', 'active'), (2, 'IT', 'active'), (3, 'Operations', 'active')");
        $pdo->exec("INSERT IGNORE INTO asset_categories (id, name, warranty_months) VALUES (1, 'Electronics', 24), (2, 'Furniture', 12), (3, 'Vehicles', 36), (4, 'Rooms', NULL)");

        $pdo->prepare('INSERT IGNORE INTO assets (name, category_id, asset_tag, serial_number, acquisition_date, acquisition_cost, condition_note, location, status, is_bookable) VALUES
            ("Dell Laptop", 1, "AF-0001", "SN1001", "2024-01-15", 85000, "Good", "IT Store", "available", 0),
            ("Conference Room B2", 4, "AF-0002", NULL, "2023-06-01", 0, "Good", "Floor 2", "available", 1),
            ("Toyota Innova", 3, "AF-0003", "VH2001", "2022-03-10", 1200000, "Good", "Parking A", "available", 1),
            ("Office Chair", 2, "AF-0004", NULL, "2023-09-01", 4500, "Good", "Operations", "available", 0)')
            ->execute();

        file_put_contents(__DIR__ . '/config/local.env.php', "<?php\nputenv('ASSETFLOW_DB_HOST=$host');\nputenv('ASSETFLOW_DB_NAME=$name');\nputenv('ASSETFLOW_DB_USER=$user');\nputenv('ASSETFLOW_DB_PASS=" . addslashes($pass) . "');\n");

        $messages[] = 'Database installed successfully!';
        $messages[] = 'Admin login: admin@assetflow.com / admin123';
        $done = true;
    } catch (Throwable $e) {
        $messages[] = 'Error: ' . $e->getMessage();
    }
}

if (file_exists(__DIR__ . '/config/local.env.php')) {
    require __DIR__ . '/config/local.env.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AssetFlow Setup</title>
  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body class="auth-page">
  <main class="auth-card">
    <h1>AssetFlow Setup</h1>
    <p>Install MySQL database for the Enterprise Asset & Resource Management System.</p>
    <?php foreach ($messages as $msg): ?>
      <div class="alert <?= $done ? 'success' : 'error' ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endforeach; ?>
    <?php if (!$done): ?>
    <form method="post">
      <label>DB Host</label>
      <input name="db_host" value="127.0.0.1" required />
      <label>DB User</label>
      <input name="db_user" value="root" required />
      <label>DB Password</label>
      <input name="db_pass" type="password" />
      <label>DB Name</label>
      <input name="db_name" value="assetflow" required />
      <button type="submit">Install Database</button>
    </form>
    <?php else: ?>
    <a class="btn primary" href="login.php">Go to Login</a>
    <?php endif; ?>
  </main>
</body>
</html>
