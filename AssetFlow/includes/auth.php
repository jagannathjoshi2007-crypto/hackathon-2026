<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    static $user = null;
    if ($user !== null) {
        return $user;
    }

    $stmt = db()->prepare(
        'SELECT u.*, d.name AS department_name
         FROM users u
         LEFT JOIN departments d ON d.id = u.department_id
         WHERE u.id = ? AND u.status = "active"'
    );
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch() ?: null;

    if (!$user) {
        logout_user();
    }

    return $user;
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        if (str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
            json_response(['error' => 'Unauthorized'], 401);
        }
        header('Location: ' . login_redirect_url());
        exit;
    }
    return $user;
}

function login_redirect_url(): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (str_contains($uri, '/admin/') || str_contains($uri, '/user/')) {
        return '../login.php';
    }
    return 'login.php';
}

function require_admin_panel(): array
{
    $user = require_login();
    if (!is_admin_role($user['role'])) {
        header('Location: ' . user_panel_url());
        exit;
    }
    return $user;
}

function require_user_panel(): array
{
    $user = require_login();
    if (is_admin_role($user['role'])) {
        header('Location: ' . admin_panel_url());
        exit;
    }
    return $user;
}

function require_roles(array $roles): array
{
    $user = require_login();
    if (!in_array($user['role'], $roles, true)) {
        if (str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
            json_response(['error' => 'Forbidden'], 403);
        }
        http_response_code(403);
        echo 'Access denied';
        exit;
    }
    return $user;
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    log_activity((int) $user['id'], 'login', 'user', (int) $user['id']);
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
