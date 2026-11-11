<?php
function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function flash(string $message, string $type = 'success'): void {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}
function get_flash(): ?array {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

function is_admin(): bool {
    return ($_SESSION['role'] ?? '') === 'admin';
}

function require_login(): void {
    global $pdo;
    if (empty($_SESSION['user_id'])) {
        header('Location: index.php');
        exit;
    }
    $stmt = $pdo->prepare("SELECT status, role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $u = $stmt->fetch();
    if (!$u || $u['status'] !== 'approved') {
        session_unset();
        session_destroy();
        session_start();
        flash('Your account is no longer active.', 'error');
        header('Location: index.php');
        exit;
    }
    $_SESSION['role'] = $u['role']; // keep role in sync with DB
}

function require_admin(): void {
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        flash('Access denied. Administrators only.', 'error');
        header('Location: dashboard.php');
        exit;
    }
}

function can_modify_product(array $product): bool {
    return is_admin() || (int)$product['created_by'] === (int)$_SESSION['user_id'];
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}
function csrf_check(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400);
        die('Invalid request token. Go back and try again.');
    }
}
