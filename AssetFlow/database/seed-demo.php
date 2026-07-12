<?php
declare(strict_types=1);

/**
 * Demo seed data for dropdown menus (employees, allocations, bookable assets).
 * Run once: php database/seed-demo.php
 */
require_once __DIR__ . '/../config/database.php';

$pdo = db();
$hash = password_hash('employee123', PASSWORD_DEFAULT);

$employees = [
    ['John Sharma', 'john.sharma@company.com', 'employee', 2],
    ['Sarah Lee', 'sarah.lee@company.com', 'employee', 3],
    ['Mike Patel', 'mike.patel@company.com', 'department_head', 2],
    ['Priya Singh', 'priya.singh@company.com', 'asset_manager', 1],
];

$insertUser = $pdo->prepare(
    'INSERT INTO users (name, email, password_hash, role, department_id, status)
     SELECT ?, ?, ?, ?, ?, "active"
     WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = ?)'
);

foreach ($employees as [$name, $email, $role, $deptId]) {
    $insertUser->execute([$name, $email, $hash, $role, $deptId, $email]);
}

$pdo->exec("UPDATE departments SET head_user_id = (SELECT id FROM users WHERE email = 'mike.patel@company.com' LIMIT 1) WHERE name = 'IT'");

$johnId = (int) $pdo->query("SELECT id FROM users WHERE email = 'john.sharma@company.com' LIMIT 1")->fetchColumn();
if ($johnId) {
    $exists = (int) $pdo->query("SELECT COUNT(*) FROM allocations WHERE asset_id = 1 AND status IN ('active','overdue')")->fetchColumn();
    if ($exists === 0) {
        $pdo->prepare(
            'INSERT INTO allocations (asset_id, user_id, department_id, expected_return, status) VALUES (1, ?, 2, DATE_ADD(CURDATE(), INTERVAL 30 DAY), "active")'
        )->execute([$johnId]);
        $pdo->exec("UPDATE assets SET status = 'allocated' WHERE id = 1");
    }
}

echo "Demo data seeded.\n";
echo "Sample employees (password: employee123):\n";
foreach ($employees as [, $email]) {
    echo "  - $email\n";
}
echo "Active allocation: AF-0001 (Dell Laptop) → John Sharma\n";
echo "Bookable resources: AF-0002 (Conference Room B2), AF-0003 (Toyota Innova)\n";
