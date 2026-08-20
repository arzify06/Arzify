<?php
/**
 * Desire Travel - Global Header Template
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/lang.php';
require_once __DIR__ . '/../helpers/auth.php';

$currentUser = getCurrentUser();
$pageTitle = $pageTitle ?? __('app_name');
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($CURRENT_LANG) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | <?= htmlspecialchars(APP_NAME) ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>assets/images/logo.svg">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/seat-layout.css">
</head>
<body class="<?= htmlspecialchars($CURRENT_THEME) ?>" data-theme="<?= htmlspecialchars($CURRENT_THEME) ?>" lang="<?= htmlspecialchars($CURRENT_LANG) ?>">

<div class="app-wrapper">
    <!-- Include Role-Based Sidebar if logged in -->
    <?php if (isLoggedIn()): ?>
        <?php include __DIR__ . '/sidebar.php'; ?>
    <?php endif; ?>

    <div class="main-content">
        <?php if (isLoggedIn()): ?>
        <!-- Top Navbar -->
        <header class="top-navbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-outline-secondary d-lg-none" id="sidebarToggle" type="button" aria-label="Toggle Sidebar">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div>
                    <h1 class="nav-title"><?= htmlspecialchars($pageTitle) ?></h1>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <!-- Live Dynamic Clock -->
                <div class="d-none d-md-flex align-items-center gap-2 px-3 py-1 bg-light rounded-pill border text-muted small fw-semibold">
                    <i class="bi bi-clock-history text-primary"></i>
                    <span id="live-clock">--:--:--</span>
                </div>

                <!-- Theme Switcher Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2 rounded-pill px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-palette-fill text-warning"></i>
                        <span class="d-none d-sm-inline"><?= _e('theme') ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                        <li><h6 class="dropdown-header text-uppercase small fw-bold"><?= _e('theme') ?></h6></li>
                        <?php foreach ($AVAILABLE_THEMES as $themeKey => $themeData): ?>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 py-2 <?= $CURRENT_THEME === $themeKey ? 'active' : '' ?>" href="?set_theme=<?= $themeKey ?>" data-set-theme="<?= $themeKey ?>">
                                    <span style="width:14px;height:14px;border-radius:50%;background:<?= $themeData['color'] ?>;display:inline-block;border:1px solid #fff;"></span>
                                    <?= htmlspecialchars($themeData['name']) ?>
                                    <?php if ($CURRENT_THEME === $themeKey): ?>
                                        <i class="bi bi-check-lg ms-auto"></i>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Language Switcher Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2 rounded-pill px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span><?= $AVAILABLE_LANGS[$CURRENT_LANG]['flag'] ?? '🌐' ?></span>
                        <span class="d-none d-sm-inline fw-semibold"><?= htmlspecialchars($AVAILABLE_LANGS[$CURRENT_LANG]['name'] ?? 'Language') ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                        <li><h6 class="dropdown-header text-uppercase small fw-bold"><?= _e('language') ?></h6></li>
                        <?php foreach ($AVAILABLE_LANGS as $langCode => $langData): ?>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 py-2 <?= $CURRENT_LANG === $langCode ? 'active' : '' ?>" href="?set_lang=<?= $langCode ?>">
                                    <span><?= $langData['flag'] ?></span>
                                    <?= htmlspecialchars($langData['name']) ?>
                                    <?php if ($CURRENT_LANG === $langCode): ?>
                                        <i class="bi bi-check-lg ms-auto"></i>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- User Profile & Quick Actions -->
                <div class="dropdown">
                    <button class="btn btn-link p-0 text-decoration-none dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-sm" style="width:38px;height:38px;font-weight:700;">
                            <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?>
                        </div>
                        <div class="text-start d-none d-xl-block">
                            <div class="fw-bold text-dark small lh-1"><?= htmlspecialchars($currentUser['name'] ?? 'User') ?></div>
                            <small class="text-muted text-uppercase" style="font-size:0.7rem;"><?= htmlspecialchars($currentUser['role'] ?? '') ?></small>
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 p-2" style="min-width:220px;">
                        <li class="p-2 border-bottom mb-2">
                            <div class="fw-bold text-dark"><?= htmlspecialchars($currentUser['name'] ?? '') ?></div>
                            <div class="text-muted small"><?= htmlspecialchars($currentUser['email'] ?? '') ?></div>
                            <div class="badge bg-primary-subtle text-primary mt-1"><?= htmlspecialchars($currentUser['employee_code'] ?? '') ?></div>
                        </li>
                        <li>
                            <a class="dropdown-item rounded-2 py-2" href="<?= BASE_URL ?>employee/change_password.php">
                                <i class="bi bi-key me-2 text-warning"></i> <?= _e('menu_change_password') ?>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item rounded-2 py-2 text-danger" href="<?= BASE_URL ?>logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i> <?= _e('logout') ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </header>
        <?php endif; ?>

        <!-- Page Content Wrapper -->
        <main class="p-3 p-md-4 flex-grow-1">
            <!-- Flash Message Alerts -->
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm rounded-3" role="alert">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <div><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm rounded-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <div><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>
