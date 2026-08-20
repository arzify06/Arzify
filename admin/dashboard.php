<?php
/**
 * Desire Travel - Admin Analytics Dashboard
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/lang.php';
require_once __DIR__ . '/../helpers/auth.php';

requireAdmin();

$pageTitle = __('dashboard');

// Fetch Metrics & KPIs
try {
    // 1. Total Revenue
    $revStmt = $pdo->query("SELECT COALESCE(SUM(total_fare), 0) FROM bookings WHERE booking_status = 'Confirmed'");
    $totalRevenue = (float)$revStmt->fetchColumn();

    // 2. Active Buses
    $busStmt = $pdo->query("SELECT COUNT(*) FROM buses WHERE status = 'active'");
    $activeBuses = (int)$busStmt->fetchColumn();

    // 3. Active Routes
    $routeStmt = $pdo->query("SELECT COUNT(*) FROM routes WHERE status = 'active'");
    $activeRoutes = (int)$routeStmt->fetchColumn();

    // 4. Scheduled Routines
    $routineStmt = $pdo->query("SELECT COUNT(*) FROM routines WHERE travel_date >= CURRENT_DATE()");
    $scheduledRoutines = (int)$routineStmt->fetchColumn();

    // 5. Total Customers
    $custStmt = $pdo->query("SELECT COUNT(*) FROM customers");
    $totalCustomers = (int)$custStmt->fetchColumn();

    // 6. Today's Bookings
    $todayStmt = $pdo->query("SELECT COUNT(*), COALESCE(SUM(total_fare), 0) FROM bookings WHERE DATE(booking_date) = CURRENT_DATE() AND booking_status = 'Confirmed'");
    $todayRow = $todayStmt->fetch(PDO::FETCH_NUM);
    $todayBookingsCount = (int)($todayRow[0] ?? 0);
    $todayBookingsRevenue = (float)($todayRow[1] ?? 0.0);

    // 7. Recent Bookings List
    $recentStmt = $pdo->query("
        SELECT b.*, c.name as customer_name, c.contact as customer_contact, 
               r.route_name, bu.bus_number, bu.bus_name, rt.departure_time, rt.travel_date
        FROM bookings b
        JOIN customers c ON b.customer_id = c.id
        JOIN routines rt ON b.routine_id = rt.id
        JOIN routes r ON rt.route_id = r.id
        JOIN buses bu ON rt.bus_id = bu.id
        ORDER BY b.id DESC LIMIT 8
    ");
    $recentBookings = $recentStmt->fetchAll();

    // 8. Recent Employee Login Logs
    $loginStmt = $pdo->query("
        SELECT el.*, e.name as employee_name, e.employee_code 
        FROM employee_logins el
        LEFT JOIN employees e ON el.employee_id = e.id
        ORDER BY el.id DESC LIMIT 6
    ");
    $recentLogins = $loginStmt->fetchAll();

} catch (Exception $e) {
    $totalRevenue = $activeBuses = $activeRoutes = $scheduledRoutines = $totalCustomers = $todayBookingsCount = $todayBookingsRevenue = 0;
    $recentBookings = $recentLogins = [];
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <!-- Welcome Header & Quick Actions -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1"><?= _e('admin_panel') ?></h2>
            <p class="text-muted small mb-0">Welcome back, <strong><?= htmlspecialchars($currentUser['name']) ?></strong>! Here is an overview of Desire Travel fleet and booking telemetry.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= BASE_URL ?>admin/buses.php" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-circle me-1"></i> <?= _e('add_bus') ?>
            </a>
            <a href="<?= BASE_URL ?>admin/routines.php" class="btn btn-sm btn-gold">
                <i class="bi bi-calendar-plus me-1"></i> <?= _e('add_routine') ?>
            </a>
            <a href="<?= BASE_URL ?>admin/booking_reports.php" class="btn btn-sm btn-outline-secondary bg-white">
                <i class="bi bi-file-earmark-bar-graph me-1"></i> <?= _e('menu_reports') ?>
            </a>
        </div>
    </div>

    <!-- 6 KPI Metric Cards Grid -->
    <div class="row g-3 mb-4">
        <!-- 1. Total Revenue -->
        <div class="col-xl-4 col-md-6">
            <div class="kpi-card">
                <div class="kpi-icon" style="background:#ecfdf5;color:#059669;">
                    <i class="bi bi-currency-rupee"></i>
                </div>
                <div>
                    <div class="kpi-value">₹<?= number_format($totalRevenue, 2) ?></div>
                    <div class="kpi-label"><?= _e('kpi_total_revenue') ?></div>
                </div>
            </div>
        </div>

        <!-- 2. Today's Bookings -->
        <div class="col-xl-4 col-md-6">
            <div class="kpi-card">
                <div class="kpi-icon" style="background:#eff6ff;color:#2563eb;">
                    <i class="bi bi-ticket-detailed-fill"></i>
                </div>
                <div>
                    <div class="kpi-value"><?= $todayBookingsCount ?> <span class="fs-6 fw-normal text-muted">(₹<?= number_format($todayBookingsRevenue, 2) ?>)</span></div>
                    <div class="kpi-label"><?= _e('kpi_todays_bookings') ?></div>
                </div>
            </div>
        </div>

        <!-- 3. Active Buses -->
        <div class="col-xl-4 col-md-6">
            <div class="kpi-card">
                <div class="kpi-icon" style="background:#fffbeb;color:#d97706;">
                    <i class="bi bi-bus-front-fill"></i>
                </div>
                <div>
                    <div class="kpi-value"><?= $activeBuses ?></div>
                    <div class="kpi-label"><?= _e('kpi_active_buses') ?></div>
                </div>
            </div>
        </div>

        <!-- 4. Active Routes -->
        <div class="col-xl-4 col-md-6">
            <div class="kpi-card">
                <div class="kpi-icon" style="background:#f5f3ff;color:#7c3aed;">
                    <i class="bi bi-map-fill"></i>
                </div>
                <div>
                    <div class="kpi-value"><?= $activeRoutes ?></div>
                    <div class="kpi-label"><?= _e('kpi_total_routes') ?></div>
                </div>
            </div>
        </div>

        <!-- 5. Scheduled Trips -->
        <div class="col-xl-4 col-md-6">
            <div class="kpi-card">
                <div class="kpi-icon" style="background:#fdf2f8;color:#db2777;">
                    <i class="bi bi-calendar2-check-fill"></i>
                </div>
                <div>
                    <div class="kpi-value"><?= $scheduledRoutines ?></div>
                    <div class="kpi-label"><?= _e('kpi_scheduled_trips') ?></div>
                </div>
            </div>
        </div>

        <!-- 6. Total Registered Customers -->
        <div class="col-xl-4 col-md-6">
            <div class="kpi-card">
                <div class="kpi-icon" style="background:#f0fdf4;color:#16a34a;">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <div class="kpi-value"><?= $totalCustomers ?></div>
                    <div class="kpi-label"><?= _e('kpi_registered_customers') ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Tables Section: Recent Bookings & Staff Logins -->
    <div class="row g-4">
        <!-- Recent Bookings Table -->
        <div class="col-xl-8">
            <div class="dt-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-clock-history text-primary me-2"></i><?= _e('kpi_recent_bookings') ?>
                    </h5>
                    <a href="<?= BASE_URL ?>employee/tickets.php" class="btn btn-sm btn-outline-primary rounded-pill">
                        <?= _e('view_ticket') ?>s &rarr;
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?= _e('ticket_number') ?></th>
                                <th><?= _e('passenger') ?></th>
                                <th><?= _e('journey') ?></th>
                                <th><?= _e('seats') ?></th>
                                <th><?= _e('total_payable') ?></th>
                                <th><?= _e('status') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentBookings)): ?>
                                <?php foreach ($recentBookings as $bk): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= BASE_URL ?>employee/tickets.php?search=<?= urlencode($bk['ticket_number']) ?>" class="fw-bold font-monospace text-primary text-decoration-none">
                                                <?= htmlspecialchars($bk['ticket_number']) ?>
                                            </a>
                                            <div class="text-muted small" style="font-size:0.75rem;"><?= date('d M, h:i A', strtotime($bk['booking_date'])) ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars($bk['customer_name']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($bk['customer_contact']) ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-truncate" style="max-width:180px;"><?= htmlspecialchars($bk['route_name']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($bk['bus_number']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary fw-bold"><?= htmlspecialchars($bk['seat_numbers']) ?></span>
                                        </td>
                                        <td class="fw-bold text-dark">
                                            ₹<?= number_format((float)$bk['total_fare'], 2) ?>
                                        </td>
                                        <td>
                                            <?php if ($bk['booking_status'] === 'Confirmed'): ?>
                                                <span class="badge-active"><?= _e('confirmed') ?></span>
                                            <?php else: ?>
                                                <span class="badge-inactive"><?= _e('cancelled') ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No recent bookings recorded yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Live Staff Login Activity Stream -->
        <div class="col-xl-4">
            <div class="dt-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-shield-check text-success me-2"></i><?= _e('kpi_live_staff_logins') ?>
                    </h5>
                    <a href="<?= BASE_URL ?>admin/login_logs.php" class="btn btn-sm btn-outline-secondary rounded-pill">
                        <?= _e('all') ?> &rarr;
                    </a>
                </div>

                <div class="d-flex flex-column gap-3">
                    <?php if (!empty($recentLogins)): ?>
                        <?php foreach ($recentLogins as $log): ?>
                            <div class="p-3 bg-light rounded-3 border d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold text-dark small">
                                        <?= htmlspecialchars($log['employee_name'] ?? $log['username']) ?>
                                    </div>
                                    <div class="text-muted" style="font-size:0.75rem;">
                                        <span class="badge bg-secondary-subtle text-secondary me-1"><?= htmlspecialchars($log['role']) ?></span>
                                        <?= date('h:i A - d M', strtotime($log['login_time'])) ?>
                                    </div>
                                </div>
                                <div>
                                    <?php if ($log['status'] === 'logged_in'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Online</span>
                                    <?php elseif ($log['status'] === 'logged_out'): ?>
                                        <span class="badge bg-secondary-subtle text-muted">Out</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger">Failed</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">No login telemetry recorded.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
