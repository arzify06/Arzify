<?php
/**
 * Desire Travel - Authentication & Role-Based Access Control Helper
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/lang.php';

function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']) && !empty($_SESSION['role']);
}

function getCurrentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id'            => $_SESSION['user_id'],
        'employee_code' => $_SESSION['employee_code'] ?? '',
        'name'          => $_SESSION['user_name'] ?? '',
        'username'      => $_SESSION['username'] ?? '',
        'role'          => $_SESSION['role'] ?? '',
        'email'         => $_SESSION['email'] ?? '',
        'login_time'    => $_SESSION['login_time'] ?? '',
        'login_log_id'  => $_SESSION['login_log_id'] ?? null,
    ];
}

function isAdmin(): bool {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

function isEmployee(): bool {
    return isLoggedIn() && ($_SESSION['role'] === 'employee' || $_SESSION['role'] === 'admin');
}

function requireAuth(string $redirect = 'index.php'): void {
    if (!isLoggedIn()) {
        $_SESSION['flash_error'] = __('unauthorized_access');
        header('Location: ' . BASE_URL . $redirect);
        exit;
    }
}

function requireAdmin(string $redirect = 'index.php'): void {
    requireAuth($redirect);
    if (!isAdmin()) {
        $_SESSION['flash_error'] = __('unauthorized_access');
        header('Location: ' . BASE_URL . 'employee/dashboard.php');
        exit;
    }
}

function requireEmployee(string $redirect = 'index.php'): void {
    requireAuth($redirect);
}

/**
 * Authenticate employee or admin user
 */
function authenticateUser(string $username, string $password): array {
    global $pdo;

    $username = trim($username);
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE (username = :u OR email = :e) LIMIT 1");
    $stmt->execute([':u' => $username, ':e' => $username]);
    $user = $stmt->fetch();

    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown Browser';

    if (!$user) {
        // Record failed attempt
        try {
            $logStmt = $pdo->prepare("INSERT INTO employee_logins (username, role, login_time, ip_address, user_agent, status) VALUES (?, ?, NOW(), ?, ?, 'failed')");
            $logStmt->execute([$username, 'unknown', $ip, $userAgent]);
        } catch (Exception $e) {}
        return ['success' => false, 'message' => __('invalid_credentials')];
    }

    if ($user['status'] !== 'active') {
        return ['success' => false, 'message' => __('account_inactive')];
    }

    // Check Bcrypt password or fallback comparison for demo ease
    $passwordMatches = password_verify($password, $user['password']) || 
                       ($password === 'admin123' && $user['username'] === 'admin') ||
                       ($password === 'emp123' && in_array($user['username'], ['emp', 'clerk1'])) ||
                       ($password === 'clerk123' && in_array($user['username'], ['emp', 'clerk1']));

    if (!$passwordMatches) {
        try {
            $logStmt = $pdo->prepare("INSERT INTO employee_logins (employee_id, username, role, login_time, ip_address, user_agent, status) VALUES (?, ?, ?, NOW(), ?, ?, 'failed')");
            $logStmt->execute([$user['id'], $user['username'], $user['role'], $ip, $userAgent]);
        } catch (Exception $e) {}
        return ['success' => false, 'message' => __('invalid_credentials')];
    }

    // Update password hash if necessary
    if (password_needs_rehash($user['password'], PASSWORD_BCRYPT)) {
        $newHash = password_hash($password, PASSWORD_BCRYPT);
        $updateStmt = $pdo->prepare("UPDATE employees SET password = ? WHERE id = ?");
        $updateStmt->execute([$newHash, $user['id']]);
    }

    // Record Successful Login in employee_logins
    $loginLogId = null;
    try {
        $logStmt = $pdo->prepare("INSERT INTO employee_logins (employee_id, username, role, login_time, ip_address, user_agent, status) VALUES (?, ?, ?, NOW(), ?, ?, 'logged_in')");
        $logStmt->execute([$user['id'], $user['username'], $user['role'], $ip, $userAgent]);
        $loginLogId = $pdo->lastInsertId();
    } catch (Exception $e) {}

    // Initialize session
    $_SESSION['user_id']       = $user['id'];
    $_SESSION['employee_code'] = $user['employee_code'];
    $_SESSION['user_name']     = $user['name'];
    $_SESSION['username']      = $user['username'];
    $_SESSION['role']          = $user['role'];
    $_SESSION['email']         = $user['email'];
    $_SESSION['login_time']    = date('Y-m-d H:i:s');
    $_SESSION['login_log_id']  = $loginLogId;

    return ['success' => true, 'user' => $user];
}

/**
 * Logout handler
 */
function logoutUser(): void {
    global $pdo;

    if (isset($_SESSION['login_log_id'])) {
        try {
            $logStmt = $pdo->prepare("UPDATE employee_logins SET logout_time = NOW(), status = 'logged_out' WHERE id = ?");
            $logStmt->execute([$_SESSION['login_log_id']]);
        } catch (Exception $e) {}
    }

    // Clear session data
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
