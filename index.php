<?php
/**
 * Desire Travel - Main Entry Point & Authentication Portal
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/lang.php';
require_once __DIR__ . '/helpers/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: ' . BASE_URL . 'admin/dashboard.php');
    } else {
        header('Location: ' . BASE_URL . 'employee/dashboard.php');
    }
    exit;
}

$errorMsg = '';
$username = '';

// Handle Login Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_action'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $errorMsg = __('invalid_credentials');
    } else {
        $auth = authenticateUser($username, $password);
        if ($auth['success']) {
            if ($auth['user']['role'] === 'admin') {
                header('Location: ' . BASE_URL . 'admin/dashboard.php');
            } else {
                header('Location: ' . BASE_URL . 'employee/dashboard.php');
            }
            exit;
        } else {
            $errorMsg = $auth['message'];
        }
    }
}

// Fetch active routes for public quick inquiry tab
$publicRoutes = [];
try {
    $rStmt = $pdo->query("SELECT r.*, COUNT(rt.id) as scheduled_count 
                         FROM routes r 
                         LEFT JOIN routines rt ON r.id = rt.route_id AND rt.travel_date >= CURRENT_DATE()
                         WHERE r.status = 'active'
                         GROUP BY r.id");
    $publicRoutes = $rStmt->fetchAll();
} catch (Exception $e) {}

$pageTitle = __('login');
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($CURRENT_LANG) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= _e('login_title') ?> | <?= htmlspecialchars(APP_NAME) ?></title>
    
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>assets/images/logo.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    
    <style>
        .login-hero-bg {
            min-height: 100vh;
            background: radial-gradient(circle at 10% 20%, rgba(var(--primary-rgb), 0.95) 0%, rgba(15, 23, 42, 0.98) 90%), url('https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&w=1920&q=80') center/cover fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 15px;
        }
        .login-glass-card {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            overflow: hidden;
            width: 100%;
            max-width: 980px;
        }
        body.theme-dark .login-glass-card {
            background: rgba(30, 41, 59, 0.96);
            border-color: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="<?= htmlspecialchars($CURRENT_THEME) ?>" data-theme="<?= htmlspecialchars($CURRENT_THEME) ?>" lang="<?= htmlspecialchars($CURRENT_LANG) ?>">

<div class="login-hero-bg">
    <div class="container">
        <!-- Top Toolbar: Theme & Language Switchers -->
        <div class="d-flex justify-content-between align-items-center mb-3 mx-auto" style="max-width: 980px;">
            <div class="d-flex align-items-center gap-2 text-white">
                <i class="bi bi-shield-lock-fill text-warning"></i>
                <span class="small fw-semibold opacity-75">SSL 256-Bit Encrypted Portal</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <!-- Theme Switcher -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-light bg-opacity-75 dropdown-toggle rounded-pill px-3 shadow-sm border-0" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-palette-fill text-warning"></i>
                        <span class="d-none d-sm-inline ms-1"><?= _e('theme') ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                        <?php foreach ($AVAILABLE_THEMES as $tKey => $tVal): ?>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 py-2 <?= $CURRENT_THEME === $tKey ? 'active' : '' ?>" href="?set_theme=<?= $tKey ?>">
                                    <span style="width:12px;height:12px;border-radius:50%;background:<?= $tVal['color'] ?>;"></span>
                                    <?= htmlspecialchars($tVal['name']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Language Switcher -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-light bg-opacity-75 dropdown-toggle rounded-pill px-3 shadow-sm border-0" type="button" data-bs-toggle="dropdown">
                        <span><?= $AVAILABLE_LANGS[$CURRENT_LANG]['flag'] ?? '🌐' ?></span>
                        <span class="fw-semibold ms-1"><?= htmlspecialchars($AVAILABLE_LANGS[$CURRENT_LANG]['name'] ?? 'Language') ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                        <?php foreach ($AVAILABLE_LANGS as $lCode => $lVal): ?>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 py-2 <?= $CURRENT_LANG === $lCode ? 'active' : '' ?>" href="?set_lang=<?= $lCode ?>">
                                    <span><?= $lVal['flag'] ?></span>
                                    <?= htmlspecialchars($lVal['name']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Glassmorphism Main Login Container -->
        <div class="login-glass-card mx-auto">
            <div class="row g-0">
                <!-- Left Brand Hero Section -->
                <div class="col-lg-5 p-4 p-md-5 d-flex flex-column justify-content-between text-white" style="background: var(--hero-grad);">
                    <div>
                        <div class="mb-4">
                            <img src="<?= BASE_URL ?>assets/images/logo.svg" alt="Desire Travel Logo" style="height: 52px; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));" />
                        </div>
                        <h3 class="fw-bold mb-2"><?= _e('tagline') ?></h3>
                        <p class="text-white-50 small mb-4">
                            Complete enterprise fleet management, intelligent dynamic pricing, route scheduling, interactive seat reservation, and audit telemetry.
                        </p>

                        <div class="d-flex flex-column gap-3 mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-white bg-opacity-20 p-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                                    <i class="bi bi-bus-front text-warning fs-5"></i>
                                </div>
                                <span class="small fw-semibold">Modern Luxury Fleet Tracking</span>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-white bg-opacity-20 p-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                                    <i class="bi bi-ticket-perforated text-warning fs-5"></i>
                                </div>
                                <span class="small fw-semibold">Interactive Seat Map &amp; Instant Ticket</span>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-white bg-opacity-20 p-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                                    <i class="bi bi-translate text-warning fs-5"></i>
                                </div>
                                <span class="small fw-semibold">English &amp; ગુજરાતી Dual Language</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 border-top border-white border-opacity-20 small text-white-50">
                        &copy; <?= date('Y') ?> <?= htmlspecialchars(APP_NAME) ?>. <?= _e('copyright') ?>
                    </div>
                </div>

                <!-- Right Form Section -->
                <div class="col-lg-7 p-4 p-md-5">
                    <ul class="nav nav-pills mb-4" id="loginTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active rounded-pill px-4 fw-semibold" id="auth-tab" data-bs-toggle="pill" data-bs-target="#auth-panel" type="button" role="tab">
                                <i class="bi bi-lock-fill me-1"></i> <?= _e('login') ?>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill px-4 fw-semibold" id="routes-tab" data-bs-toggle="pill" data-bs-target="#routes-panel" type="button" role="tab">
                                <i class="bi bi-search me-1"></i> <?= _e('inquiry_window') ?>
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="loginTabsContent">
                        <!-- Tab 1: Login Form -->
                        <div class="tab-pane fade show active" id="auth-panel" role="tabpanel">
                            <div class="mb-4">
                                <h4 class="fw-bold mb-1"><?= _e('login_title') ?></h4>
                                <p class="text-muted small"><?= _e('login_subtitle') ?></p>
                            </div>

                            <?php if (!empty($errorMsg)): ?>
                                <div class="alert alert-danger d-flex align-items-center gap-2 rounded-3 py-2" role="alert">
                                    <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                                    <div><?= htmlspecialchars($errorMsg) ?></div>
                                </div>
                            <?php endif; ?>

                            <form action="<?= BASE_URL ?>index.php" method="POST">
                                <input type="hidden" name="login_action" value="1">
                                
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted"><?= _e('username') ?> / Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                        <input type="text" name="username" class="form-control border-start-0 ps-0" placeholder="admin or emp" value="<?= htmlspecialchars($username) ?>" required autofocus>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted"><?= _e('password') ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-key text-muted"></i></span>
                                        <input type="password" name="password" id="loginPassword" class="form-control border-start-0 border-end-0 ps-0" placeholder="••••••••" required>
                                        <button class="btn btn-outline-secondary border-start-0 bg-light" type="button" onclick="togglePasswordVisibility()">
                                            <i class="bi bi-eye" id="togglePassIcon"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="rememberMe">
                                        <label class="form-check-label small text-muted" for="rememberMe"><?= _e('remember_me') ?></label>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-2 fs-6 shadow-sm mb-4">
                                    <i class="bi bi-box-arrow-in-right me-2"></i> <?= _e('login_btn') ?>
                                </button>
                            </form>

                            <!-- Quick Demo Buttons -->
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="small fw-bold text-muted text-uppercase mb-2">
                                    <i class="bi bi-lightning-charge-fill text-warning"></i> <?= _e('quick_login') ?>:
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="fillCredentials('admin', 'admin123')">
                                        <i class="bi bi-person-gear me-1"></i> Admin Demo
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="fillCredentials('emp', 'emp123')">
                                        <i class="bi bi-person-badge me-1"></i> Employee Demo
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 2: Public Bus Inquiry Quick View -->
                        <div class="tab-pane fade" id="routes-panel" role="tabpanel">
                            <div class="mb-3">
                                <h5 class="fw-bold mb-1"><?= _e('inquiry_window') ?></h5>
                                <p class="text-muted small">Search registered intercity travel routes and estimated timings</p>
                            </div>

                            <div class="table-responsive" style="max-height:340px; overflow-y:auto;">
                                <table class="table table-sm table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Route</th>
                                            <th>Distance</th>
                                            <th>Est. Time</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($publicRoutes)): ?>
                                            <?php foreach ($publicRoutes as $route): ?>
                                                <tr>
                                                    <td>
                                                        <div class="fw-bold text-primary"><?= htmlspecialchars($route['route_name']) ?></div>
                                                        <small class="text-muted"><?= htmlspecialchars($route['start_point']) ?> &rarr; <?= htmlspecialchars($route['end_point']) ?></small>
                                                    </td>
                                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($route['distance_km']) ?> km</span></td>
                                                    <td><small class="fw-semibold"><?= htmlspecialchars($route['estimated_duration']) ?></small></td>
                                                    <td><span class="badge bg-success-subtle text-success">Active</span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-3"><?= _e('no_trips_found') ?></td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function fillCredentials(user, pass) {
    document.querySelector('input[name="username"]').value = user;
    document.getElementById('loginPassword').value = pass;
}
function togglePasswordVisibility() {
    const passInput = document.getElementById('loginPassword');
    const icon = document.getElementById('togglePassIcon');
    if (passInput.type === 'password') {
        passInput.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        passInput.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}
</script>
</body>
</html>
