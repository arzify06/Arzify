<?php
/**
 * Desire Travel - Responsive Role-Based Sidebar Navigation
 */

$currentScript = basename($_SERVER['SCRIPT_NAME'] ?? '');
$currentDir = basename(dirname($_SERVER['SCRIPT_NAME'] ?? ''));
?>
<aside class="sidebar">
    <!-- Sidebar Header / Brand -->
    <div class="sidebar-brand">
        <img src="<?= BASE_URL ?>assets/images/logo.svg" alt="Desire Travel Logo" style="height:36px;width:auto;filter:drop-shadow(0 2px 4px rgba(0,0,0,0.3));" />
    </div>

    <!-- Navigation Items -->
    <ul class="sidebar-menu">
        <?php if (isAdmin()): ?>
            <!-- ADMIN SECTION -->
            <li class="sidebar-heading"><?= _e('admin_panel') ?></li>
            <li class="nav-item">
                <a class="nav-link <?= ($currentScript === 'dashboard.php' && $currentDir === 'admin') ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/dashboard.php">
                    <i class="bi bi-speedometer2"></i>
                    <span><?= _e('dashboard') ?></span>
                </a>
            </li>

            <li class="sidebar-heading"><?= _e('menu_fleet_management') ?></li>
            <li class="nav-item">
                <a class="nav-link <?= $currentScript === 'buses.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/buses.php">
                    <i class="bi bi-bus-front"></i>
                    <span><?= _e('menu_buses') ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentScript === 'routes.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/routes.php">
                    <i class="bi bi-geo-alt"></i>
                    <span><?= _e('menu_routes') ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentScript === 'routines.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/routines.php">
                    <i class="bi bi-calendar-week"></i>
                    <span><?= _e('menu_routines') ?></span>
                </a>
            </li>

            <li class="sidebar-heading"><?= _e('menu_employees') ?> &amp; Audit</li>
            <li class="nav-item">
                <a class="nav-link <?= $currentScript === 'employees.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/employees.php">
                    <i class="bi bi-people"></i>
                    <span><?= _e('menu_employees') ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentScript === 'login_logs.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/login_logs.php">
                    <i class="bi bi-shield-check"></i>
                    <span><?= _e('menu_login_logs') ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentScript === 'booking_reports.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/booking_reports.php">
                    <i class="bi bi-bar-chart-line"></i>
                    <span><?= _e('menu_reports') ?></span>
                </a>
            </li>
        <?php endif; ?>

        <!-- EMPLOYEE & GENERAL TICKETING SECTION -->
        <li class="sidebar-heading"><?= _e('menu_operations') ?></li>
        <?php if (!isAdmin()): ?>
            <li class="nav-item">
                <a class="nav-link <?= ($currentScript === 'dashboard.php' && $currentDir === 'employee') ? 'active' : '' ?>" href="<?= BASE_URL ?>employee/dashboard.php">
                    <i class="bi bi-grid-1x2"></i>
                    <span><?= _e('dashboard') ?></span>
                </a>
            </li>
        <?php endif; ?>
        <li class="nav-item">
            <a class="nav-link <?= $currentScript === 'booking.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>employee/booking.php">
                <i class="bi bi-ticket-perforated"></i>
                <span><?= _e('menu_booking') ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentScript === 'cancel_booking.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>employee/cancel_booking.php">
                <i class="bi bi-x-circle"></i>
                <span><?= _e('menu_cancel_booking') ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentScript === 'tickets.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>employee/tickets.php">
                <i class="bi bi-receipt"></i>
                <span><?= _e('menu_tickets') ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentScript === 'customers.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>employee/customers.php">
                <i class="bi bi-person-lines-fill"></i>
                <span><?= _e('menu_customers') ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentScript === 'inquiry.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>employee/inquiry.php">
                <i class="bi bi-search"></i>
                <span><?= _e('menu_inquiry') ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentScript === 'price_list.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>employee/price_list.php">
                <i class="bi bi-calculator"></i>
                <span><?= _e('menu_price_list') ?></span>
            </a>
        </li>

        <li class="sidebar-heading">Account</li>
        <li class="nav-item">
            <a class="nav-link <?= $currentScript === 'change_password.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>employee/change_password.php">
                <i class="bi bi-key"></i>
                <span><?= _e('menu_change_password') ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-danger" href="<?= BASE_URL ?>logout.php">
                <i class="bi bi-box-arrow-right text-danger"></i>
                <span><?= _e('logout') ?></span>
            </a>
        </li>
    </ul>

    <!-- Sidebar Footer / Active User -->
    <div class="sidebar-user">
        <div class="d-flex align-items-center gap-2 overflow-hidden">
            <i class="bi bi-person-badge text-warning fs-4"></i>
            <div class="overflow-hidden">
                <div class="text-white text-truncate fw-semibold small"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Staff') ?></div>
                <div class="text-muted text-uppercase" style="font-size:0.68rem;"><?= htmlspecialchars($_SESSION['role'] ?? '') ?></div>
            </div>
        </div>
        <a href="<?= BASE_URL ?>logout.php" class="text-white-50 hover-white text-decoration-none" title="Sign Out">
            <i class="bi bi-power fs-5"></i>
        </a>
    </div>
</aside>
