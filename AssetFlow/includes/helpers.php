<?php
declare(strict_types=1);

function json_response(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function read_json_body(): array
{
    $raw = file_get_contents('php://input');
    if (!$raw) {
        return [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function sanitize(string $value): string
{
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function log_activity(?int $userId, string $action, ?string $entityType = null, ?int $entityId = null, ?string $details = null): void
{
    $stmt = db()->prepare(
        'INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$userId, $action, $entityType, $entityId, $details]);
}

function notify_user(int $userId, string $type, string $title, string $message): void
{
    $stmt = db()->prepare(
        'INSERT INTO notifications (user_id, type, title, message) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$userId, $type, $title, $message]);
}

function next_asset_tag(): string
{
    $stmt = db()->query("SELECT asset_tag FROM assets ORDER BY id DESC LIMIT 1");
    $last = $stmt->fetchColumn();
    if (!$last) {
        return 'AF-0001';
    }
    $num = (int) substr($last, 3);
    return 'AF-' . str_pad((string) ($num + 1), 4, '0', STR_PAD_LEFT);
}

function update_overdue_allocations(): void
{
    db()->exec("UPDATE allocations SET status = 'overdue'
        WHERE status = 'active' AND expected_return IS NOT NULL AND expected_return < CURDATE()");
}

function role_label(string $role): string
{
    return match ($role) {
        'admin' => 'Admin',
        'asset_manager' => 'Asset Manager',
        'department_head' => 'Department Head',
        default => 'Employee',
    };
}

function can_manage_org(string $role): bool
{
    return $role === 'admin';
}

function can_manage_assets(string $role): bool
{
    return in_array($role, ['admin', 'asset_manager'], true);
}

function can_approve_maintenance(string $role): bool
{
    return in_array($role, ['admin', 'asset_manager'], true);
}

function can_approve_transfer(string $role): bool
{
    return in_array($role, ['admin', 'asset_manager', 'department_head'], true);
}

function is_admin_role(string $role): bool
{
    return in_array($role, ['admin', 'asset_manager'], true);
}

function panel_url_for_role(string $role): string
{
    return is_admin_role($role) ? 'admin/dashboard.php' : 'user/dashboard.php';
}

function admin_panel_url(): string
{
    return '../admin/dashboard.php';
}

function user_panel_url(): string
{
    return '../user/dashboard.php';
}
