<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/app.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

$path = $_GET['route'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];
$body = read_json_body();

try {
    match (true) {
        $path === 'auth/login' && $method === 'POST' => handle_login($body),
        $path === 'auth/signup' && $method === 'POST' => handle_signup($body),
        $path === 'auth/logout' && $method === 'POST' => handle_logout(),
        $path === 'auth/me' && $method === 'GET' => handle_me(),
        $path === 'auth/forgot' && $method === 'POST' => handle_reset_password($body),
        $path === 'auth/reset' && $method === 'POST' => handle_reset_password($body),

        $path === 'dashboard' && $method === 'GET' => handle_dashboard(require_login()),

        $path === 'departments' && $method === 'GET' => handle_departments_list(),
        $path === 'departments' && $method === 'POST' => handle_department_create(require_roles(['admin']), $body),
        $path === 'departments/update' && $method === 'POST' => handle_department_update(require_roles(['admin']), $body),

        $path === 'categories' && $method === 'GET' => handle_categories_list(require_login()),
        $path === 'categories' && $method === 'POST' => handle_category_create(require_roles(['admin']), $body),

        $path === 'employees' && $method === 'GET' => handle_employees_list(require_login()),
        $path === 'employees/promote' && $method === 'POST' => handle_employee_promote(require_roles(['admin']), $body),

        $path === 'assets' && $method === 'GET' => handle_assets_list(require_login()),
        $path === 'assets' && $method === 'POST' => handle_asset_create(require_roles(['admin', 'asset_manager']), $body),
        $path === 'assets/history' && $method === 'GET' => handle_asset_history(require_login()),

        $path === 'allocations' && $method === 'GET' => handle_allocations_list(require_login()),
        $path === 'allocations' && $method === 'POST' => handle_allocation_create(require_login(), $body),
        $path === 'allocations/return' && $method === 'POST' => handle_allocation_return(require_login(), $body),

        $path === 'transfers' && $method === 'GET' => handle_transfers_list(require_login()),
        $path === 'transfers' && $method === 'POST' => handle_transfer_create(require_login(), $body),
        $path === 'transfers/approve' && $method === 'POST' => handle_transfer_approve(require_login(), $body),

        $path === 'bookings' && $method === 'GET' => handle_bookings_list(require_login()),
        $path === 'bookings' && $method === 'POST' => handle_booking_create(require_login(), $body),
        $path === 'bookings/cancel' && $method === 'POST' => handle_booking_cancel(require_login(), $body),

        $path === 'maintenance' && $method === 'GET' => handle_maintenance_list(require_login()),
        $path === 'maintenance' && $method === 'POST' => handle_maintenance_create(require_login(), $body),
        $path === 'maintenance/update' && $method === 'POST' => handle_maintenance_update(require_login(), $body),

        $path === 'audits' && $method === 'GET' => handle_audits_list(require_login()),
        $path === 'audits' && $method === 'POST' => handle_audit_create(require_roles(['admin']), $body),
        $path === 'audits/items' && $method === 'POST' => handle_audit_item_update(require_login(), $body),
        $path === 'audits/close' && $method === 'POST' => handle_audit_close(require_roles(['admin', 'asset_manager']), $body),

        $path === 'reports' && $method === 'GET' => handle_reports(require_login()),
        $path === 'notifications' && $method === 'GET' => handle_notifications(require_login()),
        $path === 'notifications/read' && $method === 'POST' => handle_notification_read(require_login(), $body),
        $path === 'activity' && $method === 'GET' => handle_activity(require_login()),

        default => json_response(['error' => 'Not found'], 404),
    };
} catch (Throwable $e) {
    json_response(['error' => $e->getMessage()], 500);
}

function handle_login(array $body): void
{
    $email = trim($body['email'] ?? '');
    $password = $body['password'] ?? '';
    if (!$email || !$password) {
        json_response(['error' => 'Email and password required'], 422);
    }

    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? AND status = "active"');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['password_hash'])) {
        json_response(['error' => 'Invalid credentials'], 401);
    }

    login_user($user);
    unset($user['password_hash']);
    json_response(['user' => $user, 'message' => 'Login successful']);
}

function handle_signup(array $body): void
{
    $name = trim($body['name'] ?? '');
    $email = trim($body['email'] ?? '');
    $password = $body['password'] ?? '';
    $departmentId = !empty($body['department_id']) ? (int) $body['department_id'] : null;

    if (!$name || !$email || strlen($password) < 6) {
        json_response(['error' => 'Name, email, and password (min 6 chars) required'], 422);
    }

    $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        json_response(['error' => 'Email already registered'], 409);
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = db()->prepare(
        'INSERT INTO users (name, email, password_hash, role, department_id) VALUES (?, ?, ?, "employee", ?)'
    );
    $stmt->execute([$name, $email, $hash, $departmentId]);
    $id = (int) db()->lastInsertId();

    log_activity($id, 'signup', 'user', $id, 'Employee account created');
    notify_user($id, 'welcome', 'Welcome to AssetFlow', 'Your employee account has been created. Contact admin for role promotions.');

    json_response(['message' => 'Account created. Please login.', 'user_id' => $id], 201);
}

function handle_logout(): void
{
    $user = current_user();
    if ($user) {
        log_activity((int) $user['id'], 'logout', 'user', (int) $user['id']);
    }
    logout_user();
    json_response(['message' => 'Logged out']);
}

function handle_me(): void
{
    $user = require_login();
    unset($user['password_hash']);
    json_response(['user' => $user]);
}

function handle_reset_password(array $body): void
{
    $email = trim($body['email'] ?? '');
    $hash = trim($body['hash'] ?? $body['token'] ?? '');
    $password = $body['password'] ?? '';
    $confirm = $body['password_confirm'] ?? $body['confirm_password'] ?? '';

    if (!$email || !$hash || strlen($password) < 6) {
        json_response(['error' => 'Email, reset hash, and new password (min 6 chars) required'], 422);
    }
    if ($password !== $confirm) {
        json_response(['error' => 'Passwords do not match'], 422);
    }
    if (!hash_equals(RESET_HASH, $hash)) {
        json_response(['error' => 'Invalid reset hash'], 403);
    }

    $user = db()->prepare('SELECT id FROM users WHERE email = ? AND status = "active"');
    $user->execute([$email]);
    $row = $user->fetch();
    if (!$row) {
        json_response(['error' => 'No account found with this email'], 404);
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    db()->prepare('UPDATE users SET password_hash = ? WHERE email = ?')->execute([$passwordHash, $email]);

    log_activity((int) $row['id'], 'password_reset', 'user', (int) $row['id'], 'Password reset via static hash');
    json_response(['message' => 'Password updated successfully. You can login now.']);
}

function handle_dashboard(array $user): void
{
    update_overdue_allocations();

    $stats = [
        'assets_available' => (int) db()->query("SELECT COUNT(*) FROM assets WHERE status = 'available'")->fetchColumn(),
        'assets_allocated' => (int) db()->query("SELECT COUNT(*) FROM assets WHERE status = 'allocated'")->fetchColumn(),
        'maintenance_today' => (int) db()->query("SELECT COUNT(*) FROM maintenance_requests WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
        'active_bookings' => (int) db()->query("SELECT COUNT(*) FROM bookings WHERE status IN ('upcoming','ongoing')")->fetchColumn(),
        'pending_transfers' => (int) db()->query("SELECT COUNT(*) FROM transfer_requests WHERE status = 'requested'")->fetchColumn(),
        'upcoming_returns' => (int) db()->query("SELECT COUNT(*) FROM allocations WHERE status = 'active' AND expected_return >= CURDATE()")->fetchColumn(),
        'overdue_returns' => (int) db()->query("SELECT COUNT(*) FROM allocations WHERE status = 'overdue'")->fetchColumn(),
    ];

    $overdue = db()->query(
        "SELECT a.asset_tag, ast.name AS asset_name, u.name AS holder, al.expected_return
         FROM allocations al
         JOIN assets ast ON ast.id = al.asset_id
         JOIN users u ON u.id = al.user_id
         WHERE al.status = 'overdue'
         ORDER BY al.expected_return ASC LIMIT 10"
    )->fetchAll();

    $upcoming = db()->query(
        "SELECT a.asset_tag, ast.name AS asset_name, u.name AS holder, al.expected_return
         FROM allocations al
         JOIN assets ast ON ast.id = al.asset_id
         JOIN users u ON u.id = al.user_id
         WHERE al.status = 'active' AND al.expected_return IS NOT NULL AND al.expected_return >= CURDATE()
         ORDER BY al.expected_return ASC LIMIT 10"
    )->fetchAll();

    json_response(['stats' => $stats, 'overdue_returns' => $overdue, 'upcoming_returns' => $upcoming, 'user' => $user]);
}

function handle_departments_list(): void
{
    $rows = db()->query(
        'SELECT d.*, u.name AS head_name, p.name AS parent_name
         FROM departments d
         LEFT JOIN users u ON u.id = d.head_user_id
         LEFT JOIN departments p ON p.id = d.parent_id
         ORDER BY d.name'
    )->fetchAll();
    json_response(['departments' => $rows]);
}

function handle_department_create(array $user, array $body): void
{
    $stmt = db()->prepare('INSERT INTO departments (name, head_user_id, parent_id, status) VALUES (?, ?, ?, ?)');
    $stmt->execute([
        trim($body['name'] ?? ''),
        !empty($body['head_user_id']) ? (int) $body['head_user_id'] : null,
        !empty($body['parent_id']) ? (int) $body['parent_id'] : null,
        ($body['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active',
    ]);
    log_activity((int) $user['id'], 'create_department', 'department', (int) db()->lastInsertId());
    json_response(['message' => 'Department created', 'id' => (int) db()->lastInsertId()], 201);
}

function handle_department_update(array $user, array $body): void
{
    $id = (int) ($body['id'] ?? 0);
    $stmt = db()->prepare('UPDATE departments SET name = ?, head_user_id = ?, parent_id = ?, status = ? WHERE id = ?');
    $stmt->execute([
        trim($body['name'] ?? ''),
        !empty($body['head_user_id']) ? (int) $body['head_user_id'] : null,
        !empty($body['parent_id']) ? (int) $body['parent_id'] : null,
        ($body['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active',
        $id,
    ]);
    log_activity((int) $user['id'], 'update_department', 'department', $id);
    json_response(['message' => 'Department updated']);
}

function handle_categories_list(array $user): void
{
    json_response(['categories' => db()->query('SELECT * FROM asset_categories ORDER BY name')->fetchAll()]);
}

function handle_category_create(array $user, array $body): void
{
    $stmt = db()->prepare('INSERT INTO asset_categories (name, warranty_months) VALUES (?, ?)');
    $stmt->execute([trim($body['name'] ?? ''), !empty($body['warranty_months']) ? (int) $body['warranty_months'] : null]);
    log_activity((int) $user['id'], 'create_category', 'asset_category', (int) db()->lastInsertId());
    json_response(['message' => 'Category created', 'id' => (int) db()->lastInsertId()], 201);
}

function handle_employees_list(array $user): void
{
    $rows = db()->query(
        'SELECT u.id, u.name, u.email, u.role, u.status, u.department_id, d.name AS department_name
         FROM users u LEFT JOIN departments d ON d.id = u.department_id ORDER BY u.name'
    )->fetchAll();
    json_response(['employees' => $rows]);
}

function handle_employee_promote(array $user, array $body): void
{
    $id = (int) ($body['id'] ?? 0);
    $role = $body['role'] ?? 'employee';
    if (!in_array($role, ['employee', 'department_head', 'asset_manager'], true)) {
        json_response(['error' => 'Invalid role'], 422);
    }
    $stmt = db()->prepare('UPDATE users SET role = ?, department_id = ? WHERE id = ?');
    $stmt->execute([$role, !empty($body['department_id']) ? (int) $body['department_id'] : null, $id]);
    notify_user($id, 'role_change', 'Role Updated', 'Your role has been updated to ' . role_label($role));
    log_activity((int) $user['id'], 'promote_user', 'user', $id, "Role set to $role");
    json_response(['message' => 'Employee updated']);
}

function handle_assets_list(array $user): void
{
    $where = '1=1';
    $params = [];
    foreach (['status', 'category_id', 'department_id', 'location'] as $field) {
        if (!empty($_GET[$field])) {
            $where .= " AND a.$field = ?";
            $params[] = $_GET[$field];
        }
    }
    if (!empty($_GET['q'])) {
        $where .= ' AND (a.asset_tag LIKE ? OR a.serial_number LIKE ? OR a.name LIKE ?)';
        $q = '%' . $_GET['q'] . '%';
        $params[] = $q;
        $params[] = $q;
        $params[] = $q;
    }

    $stmt = db()->prepare(
        "SELECT a.*, c.name AS category_name, d.name AS department_name
         FROM assets a
         JOIN asset_categories c ON c.id = a.category_id
         LEFT JOIN departments d ON d.id = a.department_id
         WHERE $where ORDER BY a.id DESC"
    );
    $stmt->execute($params);
    json_response(['assets' => $stmt->fetchAll()]);
}

function handle_asset_create(array $user, array $body): void
{
    $tag = next_asset_tag();
    $stmt = db()->prepare(
        'INSERT INTO assets (name, category_id, asset_tag, serial_number, acquisition_date, acquisition_cost, condition_note, location, status, is_bookable, department_id)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, "available", ?, ?)'
    );
    $stmt->execute([
        trim($body['name'] ?? ''),
        (int) ($body['category_id'] ?? 0),
        $tag,
        trim($body['serial_number'] ?? '') ?: null,
        $body['acquisition_date'] ?? null,
        (float) ($body['acquisition_cost'] ?? 0),
        trim($body['condition_note'] ?? 'Good'),
        trim($body['location'] ?? '') ?: null,
        !empty($body['is_bookable']) ? 1 : 0,
        !empty($body['department_id']) ? (int) $body['department_id'] : null,
    ]);
    $id = (int) db()->lastInsertId();
    log_activity((int) $user['id'], 'register_asset', 'asset', $id, "Tag $tag");
    json_response(['message' => 'Asset registered', 'id' => $id, 'asset_tag' => $tag], 201);
}

function handle_asset_history(array $user): void
{
    $id = (int) ($_GET['asset_id'] ?? 0);
    $allocations = db()->prepare(
        'SELECT al.*, u.name AS user_name FROM allocations al JOIN users u ON u.id = al.user_id WHERE al.asset_id = ? ORDER BY al.allocated_at DESC'
    );
    $allocations->execute([$id]);
    $maintenance = db()->prepare('SELECT * FROM maintenance_requests WHERE asset_id = ? ORDER BY created_at DESC');
    $maintenance->execute([$id]);
    json_response(['allocations' => $allocations->fetchAll(), 'maintenance' => $maintenance->fetchAll()]);
}

function handle_allocations_list(array $user): void
{
    $stmt = db()->query(
        "SELECT al.*, a.asset_tag, a.name AS asset_name, u.name AS user_name, d.name AS department_name
         FROM allocations al
         JOIN assets a ON a.id = al.asset_id
         JOIN users u ON u.id = al.user_id
         LEFT JOIN departments d ON d.id = al.department_id
         ORDER BY al.allocated_at DESC"
    );
    json_response(['allocations' => $stmt->fetchAll()]);
}

function handle_allocation_create(array $user, array $body): void
{
    if (!can_manage_assets($user['role']) && $user['role'] !== 'department_head') {
        json_response(['error' => 'Forbidden'], 403);
    }

    $assetId = (int) ($body['asset_id'] ?? 0);
    $toUserId = (int) ($body['user_id'] ?? 0);

    $asset = db()->prepare('SELECT * FROM assets WHERE id = ?');
    $asset->execute([$assetId]);
    $assetRow = $asset->fetch();
    if (!$assetRow) {
        json_response(['error' => 'Asset not found'], 404);
    }
    if ($assetRow['status'] === 'allocated') {
        $holder = db()->prepare(
            'SELECT u.name FROM allocations al JOIN users u ON u.id = al.user_id WHERE al.asset_id = ? AND al.status IN ("active","overdue") LIMIT 1'
        );
        $holder->execute([$assetId]);
        $name = $holder->fetchColumn() ?: 'another user';
        json_response(['error' => "Asset currently held by $name", 'conflict' => true, 'holder' => $name], 409);
    }
    if (!in_array($assetRow['status'], ['available', 'reserved'], true)) {
        json_response(['error' => 'Asset is not available for allocation'], 409);
    }

    $stmt = db()->prepare(
        'INSERT INTO allocations (asset_id, user_id, department_id, expected_return, status) VALUES (?, ?, ?, ?, "active")'
    );
    $stmt->execute([
        $assetId,
        $toUserId,
        !empty($body['department_id']) ? (int) $body['department_id'] : null,
        $body['expected_return'] ?? null,
    ]);
    db()->prepare("UPDATE assets SET status = 'allocated' WHERE id = ?")->execute([$assetId]);

    notify_user($toUserId, 'asset_assigned', 'Asset Assigned', 'Asset ' . $assetRow['asset_tag'] . ' has been allocated to you.');
    log_activity((int) $user['id'], 'allocate_asset', 'asset', $assetId);
    json_response(['message' => 'Asset allocated']);
}

function handle_allocation_return(array $user, array $body): void
{
    $allocationId = (int) ($body['allocation_id'] ?? 0);
    $stmt = db()->prepare('SELECT * FROM allocations WHERE id = ?');
    $stmt->execute([$allocationId]);
    $alloc = $stmt->fetch();
    if (!$alloc) {
        json_response(['error' => 'Allocation not found'], 404);
    }

    db()->prepare('UPDATE allocations SET status = "returned", returned_at = NOW(), return_condition_notes = ? WHERE id = ?')
        ->execute([trim($body['condition_notes'] ?? ''), $allocationId]);
    db()->prepare("UPDATE assets SET status = 'available', condition_note = ? WHERE id = ?")
        ->execute([trim($body['condition_notes'] ?? 'Good'), (int) $alloc['asset_id']]);

    log_activity((int) $user['id'], 'return_asset', 'allocation', $allocationId);
    json_response(['message' => 'Asset returned']);
}

function handle_transfers_list(array $user): void
{
    json_response(['transfers' => db()->query(
        'SELECT t.*, a.asset_tag, fu.name AS from_name, tu.name AS to_name
         FROM transfer_requests t
         JOIN assets a ON a.id = t.asset_id
         JOIN users fu ON fu.id = t.from_user_id
         JOIN users tu ON tu.id = t.to_user_id
         ORDER BY t.created_at DESC'
    )->fetchAll()]);
}

function handle_transfer_create(array $user, array $body): void
{
    $assetId = (int) ($body['asset_id'] ?? 0);
    $toUserId = (int) ($body['to_user_id'] ?? 0);

    $holder = db()->prepare('SELECT user_id FROM allocations WHERE asset_id = ? AND status IN ("active","overdue") LIMIT 1');
    $holder->execute([$assetId]);
    $fromUserId = (int) $holder->fetchColumn();
    if (!$fromUserId) {
        json_response(['error' => 'Asset is not currently allocated'], 409);
    }

    $stmt = db()->prepare('INSERT INTO transfer_requests (asset_id, from_user_id, to_user_id, notes) VALUES (?, ?, ?, ?)');
    $stmt->execute([$assetId, $fromUserId, $toUserId, trim($body['notes'] ?? '')]);
    log_activity((int) $user['id'], 'transfer_request', 'transfer', (int) db()->lastInsertId());
    json_response(['message' => 'Transfer request submitted']);
}

function handle_transfer_approve(array $user, array $body): void
{
    if (!can_approve_transfer($user['role'])) {
        json_response(['error' => 'Forbidden'], 403);
    }
    $id = (int) ($body['id'] ?? 0);
    $action = $body['action'] ?? 'approve';

    $req = db()->prepare('SELECT * FROM transfer_requests WHERE id = ?');
    $req->execute([$id]);
    $transfer = $req->fetch();
    if (!$transfer) {
        json_response(['error' => 'Transfer not found'], 404);
    }

    if ($action === 'reject') {
        db()->prepare('UPDATE transfer_requests SET status = "rejected", approved_by = ?, updated_at = NOW() WHERE id = ?')
            ->execute([(int) $user['id'], $id]);
        notify_user((int) $transfer['to_user_id'], 'transfer_rejected', 'Transfer Rejected', 'Your transfer request was rejected.');
        json_response(['message' => 'Transfer rejected']);
    }

    db()->prepare('UPDATE transfer_requests SET status = "approved", approved_by = ?, updated_at = NOW() WHERE id = ?')
        ->execute([(int) $user['id'], $id]);
    db()->prepare('UPDATE allocations SET user_id = ?, status = "active" WHERE asset_id = ? AND status IN ("active","overdue")')
        ->execute([(int) $transfer['to_user_id'], (int) $transfer['asset_id']]);
    db()->prepare('UPDATE transfer_requests SET status = "completed" WHERE id = ?')->execute([$id]);

    notify_user((int) $transfer['to_user_id'], 'transfer_approved', 'Transfer Approved', 'Asset transfer has been approved and re-allocated.');
    log_activity((int) $user['id'], 'approve_transfer', 'transfer', $id);
    json_response(['message' => 'Transfer approved and re-allocated']);
}

function handle_bookings_list(array $user): void
{
    $assetId = !empty($_GET['asset_id']) ? (int) $_GET['asset_id'] : null;
    $sql = 'SELECT b.*, a.name AS asset_name, a.asset_tag, u.name AS user_name FROM bookings b JOIN assets a ON a.id = b.asset_id JOIN users u ON u.id = b.user_id';
    if ($assetId) {
        $sql .= ' WHERE b.asset_id = ? ORDER BY b.start_time';
        $stmt = db()->prepare($sql);
        $stmt->execute([$assetId]);
    } else {
        $sql .= ' ORDER BY b.start_time DESC';
        $stmt = db()->query($sql);
    }
    json_response(['bookings' => $stmt->fetchAll()]);
}

function handle_booking_create(array $user, array $body): void
{
    $assetId = (int) ($body['asset_id'] ?? 0);
    $start = $body['start_time'] ?? '';
    $end = $body['end_time'] ?? '';

    $overlap = db()->prepare(
        "SELECT COUNT(*) FROM bookings
         WHERE asset_id = ? AND status != 'cancelled'
         AND start_time < ? AND end_time > ?"
    );
    $overlap->execute([$assetId, $end, $start]);
    if ((int) $overlap->fetchColumn() > 0) {
        json_response(['error' => 'Booking overlaps with an existing reservation'], 409);
    }

    $stmt = db()->prepare('INSERT INTO bookings (asset_id, user_id, start_time, end_time) VALUES (?, ?, ?, ?)');
    $stmt->execute([$assetId, (int) $user['id'], $start, $end]);
    notify_user((int) $user['id'], 'booking_confirmed', 'Booking Confirmed', 'Your resource booking has been confirmed.');
    log_activity((int) $user['id'], 'create_booking', 'booking', (int) db()->lastInsertId());
    json_response(['message' => 'Booking created']);
}

function handle_booking_cancel(array $user, array $body): void
{
    $id = (int) ($body['id'] ?? 0);
    db()->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?")->execute([$id]);
    notify_user((int) $user['id'], 'booking_cancelled', 'Booking Cancelled', 'Your booking was cancelled.');
    json_response(['message' => 'Booking cancelled']);
}

function handle_maintenance_list(array $user): void
{
    json_response(['requests' => db()->query(
        'SELECT m.*, a.asset_tag, a.name AS asset_name, u.name AS requester_name
         FROM maintenance_requests m
         JOIN assets a ON a.id = m.asset_id
         JOIN users u ON u.id = m.requested_by
         ORDER BY m.created_at DESC'
    )->fetchAll()]);
}

function handle_maintenance_create(array $user, array $body): void
{
    $stmt = db()->prepare(
        'INSERT INTO maintenance_requests (asset_id, requested_by, description, priority) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([
        (int) ($body['asset_id'] ?? 0),
        (int) $user['id'],
        trim($body['description'] ?? ''),
        $body['priority'] ?? 'medium',
    ]);
    log_activity((int) $user['id'], 'maintenance_request', 'maintenance', (int) db()->lastInsertId());
    json_response(['message' => 'Maintenance request submitted'], 201);
}

function handle_maintenance_update(array $user, array $body): void
{
    $id = (int) ($body['id'] ?? 0);
    $status = $body['status'] ?? '';

    $req = db()->prepare('SELECT * FROM maintenance_requests WHERE id = ?');
    $req->execute([$id]);
    $row = $req->fetch();
    if (!$row) {
        json_response(['error' => 'Not found'], 404);
    }

    if ($status === 'approved' && can_approve_maintenance($user['role'])) {
        db()->prepare('UPDATE maintenance_requests SET status = "approved", approved_by = ? WHERE id = ?')
            ->execute([(int) $user['id'], $id]);
        db()->prepare("UPDATE assets SET status = 'under_maintenance' WHERE id = ?")->execute([(int) $row['asset_id']]);
        notify_user((int) $row['requested_by'], 'maintenance_approved', 'Maintenance Approved', 'Your maintenance request was approved.');
    } elseif ($status === 'rejected' && can_approve_maintenance($user['role'])) {
        db()->prepare('UPDATE maintenance_requests SET status = "rejected", approved_by = ? WHERE id = ?')
            ->execute([(int) $user['id'], $id]);
        notify_user((int) $row['requested_by'], 'maintenance_rejected', 'Maintenance Rejected', 'Your maintenance request was rejected.');
    } elseif ($status === 'in_progress') {
        db()->prepare('UPDATE maintenance_requests SET status = "in_progress", technician = ? WHERE id = ?')
            ->execute([trim($body['technician'] ?? ''), $id]);
    } elseif ($status === 'resolved') {
        db()->prepare('UPDATE maintenance_requests SET status = "resolved", resolved_at = NOW() WHERE id = ?')->execute([$id]);
        db()->prepare("UPDATE assets SET status = 'available' WHERE id = ?")->execute([(int) $row['asset_id']]);
    } else {
        json_response(['error' => 'Invalid action'], 422);
    }

    log_activity((int) $user['id'], 'maintenance_update', 'maintenance', $id, $status);
    json_response(['message' => 'Maintenance updated']);
}

function handle_audits_list(array $user): void
{
    $cycles = db()->query('SELECT * FROM audit_cycles ORDER BY created_at DESC')->fetchAll();
    json_response(['audits' => $cycles]);
}

function handle_audit_create(array $user, array $body): void
{
    $stmt = db()->prepare(
        'INSERT INTO audit_cycles (name, scope_department_id, scope_location, start_date, end_date, created_by, status)
         VALUES (?, ?, ?, ?, ?, ?, "open")'
    );
    $stmt->execute([
        trim($body['name'] ?? ''),
        !empty($body['scope_department_id']) ? (int) $body['scope_department_id'] : null,
        trim($body['scope_location'] ?? '') ?: null,
        $body['start_date'] ?? date('Y-m-d'),
        $body['end_date'] ?? date('Y-m-d', strtotime('+7 days')),
        (int) $user['id'],
    ]);
    $auditId = (int) db()->lastInsertId();

    if (!empty($body['auditor_ids']) && is_array($body['auditor_ids'])) {
        $assign = db()->prepare('INSERT INTO audit_assignments (audit_cycle_id, auditor_id) VALUES (?, ?)');
        foreach ($body['auditor_ids'] as $auditorId) {
            $assign->execute([$auditId, (int) $auditorId]);
        }
    }

    $assets = db()->query('SELECT id FROM assets')->fetchAll();
    $item = db()->prepare('INSERT INTO audit_items (audit_cycle_id, asset_id) VALUES (?, ?)');
    foreach ($assets as $asset) {
        $item->execute([$auditId, (int) $asset['id']]);
    }

    log_activity((int) $user['id'], 'create_audit', 'audit', $auditId);
    json_response(['message' => 'Audit cycle created', 'id' => $auditId], 201);
}

function handle_audit_item_update(array $user, array $body): void
{
    $stmt = db()->prepare(
        'UPDATE audit_items SET status = ?, notes = ?, updated_by = ?, updated_at = NOW()
         WHERE audit_cycle_id = ? AND asset_id = ?'
    );
    $stmt->execute([
        $body['status'] ?? 'verified',
        trim($body['notes'] ?? ''),
        (int) $user['id'],
        (int) ($body['audit_cycle_id'] ?? 0),
        (int) ($body['asset_id'] ?? 0),
    ]);
    if (($body['status'] ?? '') === 'missing') {
        notify_user((int) $user['id'], 'audit_discrepancy', 'Audit Discrepancy', 'Missing asset flagged during audit.');
    }
    json_response(['message' => 'Audit item updated']);
}

function handle_audit_close(array $user, array $body): void
{
    $auditId = (int) ($body['audit_cycle_id'] ?? 0);
    db()->prepare("UPDATE audit_cycles SET status = 'closed' WHERE id = ?")->execute([$auditId]);

    $missing = db()->prepare("SELECT asset_id FROM audit_items WHERE audit_cycle_id = ? AND status = 'missing'");
    $missing->execute([$auditId]);
    while ($assetId = $missing->fetchColumn()) {
        db()->prepare("UPDATE assets SET status = 'lost' WHERE id = ?")->execute([(int) $assetId]);
    }

    log_activity((int) $user['id'], 'close_audit', 'audit', $auditId);
    json_response(['message' => 'Audit cycle closed']);
}

function handle_reports(array $user): void
{
    $utilization = db()->query(
        "SELECT a.asset_tag, a.name, COUNT(al.id) AS allocation_count
         FROM assets a LEFT JOIN allocations al ON al.asset_id = a.id
         GROUP BY a.id ORDER BY allocation_count DESC LIMIT 10"
    )->fetchAll();

    $maintenanceFreq = db()->query(
        "SELECT c.name AS category, COUNT(m.id) AS request_count
         FROM asset_categories c
         JOIN assets a ON a.category_id = c.id
         LEFT JOIN maintenance_requests m ON m.asset_id = a.id
         GROUP BY c.id ORDER BY request_count DESC"
    )->fetchAll();

    $deptAlloc = db()->query(
        "SELECT d.name AS department, COUNT(al.id) AS allocations
         FROM departments d LEFT JOIN allocations al ON al.department_id = d.id
         GROUP BY d.id"
    )->fetchAll();

    $bookingHeat = db()->query(
        "SELECT HOUR(start_time) AS hour_slot, COUNT(*) AS bookings
         FROM bookings WHERE status != 'cancelled' GROUP BY hour_slot ORDER BY hour_slot"
    )->fetchAll();

    json_response([
        'utilization' => $utilization,
        'maintenance_by_category' => $maintenanceFreq,
        'department_allocations' => $deptAlloc,
        'booking_heatmap' => $bookingHeat,
    ]);
}

function handle_notifications(array $user): void
{
    $stmt = db()->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50');
    $stmt->execute([(int) $user['id']]);
    json_response(['notifications' => $stmt->fetchAll()]);
}

function handle_notification_read(array $user, array $body): void
{
    $id = (int) ($body['id'] ?? 0);
    db()->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?')->execute([$id, (int) $user['id']]);
    json_response(['message' => 'Marked as read']);
}

function handle_activity(array $user): void
{
    if ($user['role'] !== 'admin' && $user['role'] !== 'asset_manager') {
        json_response(['error' => 'Forbidden'], 403);
    }
    json_response(['logs' => db()->query(
        'SELECT l.*, u.name AS user_name FROM activity_logs l LEFT JOIN users u ON u.id = l.user_id ORDER BY l.created_at DESC LIMIT 100'
    )->fetchAll()]);
}
