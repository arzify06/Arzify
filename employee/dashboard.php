<?php
/**
 * Desire Travel - Employee / Ticketing Clerk Dashboard
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/lang.php';
require_once __DIR__ . '/../helpers/auth.php';

requireEmployee();

$pageTitle = __('employee_panel');
$empId = (int)$currentUser['id'];

// Fetch stats for current employee
try {
    // Total bookings issued by this employee today
    $todayStmt = $pdo->prepare("SELECT COUNT(*), COALESCE(SUM(total_fare), 0) FROM bookings WHERE booked_by_employee_id = ? AND DATE(booking_date) = CURRENT_DATE() AND booking_status = 'Confirmed'");
    $todayStmt->execute([$empId]);
    $todayRow = $todayStmt->fetch(PDO::FETCH_NUM);
    $myTodayBookings = (int)($todayRow[0] ?? 0);
    $myTodaySales = (float)($todayRow[1] ?? 0.0);

    // Total overall bookings by this employee
    $allStmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE booked_by_employee_id = ?");
    $allStmt->execute([$empId]);
    $myAllBookings = (int)$allStmt->fetchColumn();

    // Available scheduled trips for today
    $tripStmt = $pdo->query("SELECT COUNT(*) FROM routines WHERE travel_date = CURRENT_DATE() AND status = 'scheduled'");
    $todayTripsCount = (int)$tripStmt->fetchColumn();

    // Recent bookings issued by this counter
    $recentStmt = $pdo->prepare("
        SELECT b.*, c.name as customer_name, c.contact as customer_contact,
               r.route_name, bu.bus_number, bu.bus_name, rt.departure_time, rt.travel_date
        FROM bookings b
        JOIN customers c ON b.customer_id = c.id
        JOIN routines rt ON b.routine_id = rt.id
        JOIN routes r ON rt.route_id = r.id
        JOIN buses bu ON rt.bus_id = bu.id
        WHERE b.booked_by_employee_id = ?
        ORDER BY b.id DESC LIMIT 6
    ");
    $recentStmt->execute([$empId]);
    $myRecentBookings = $recentStmt->fetchAll();

} catch (Exception $e) {
    $myTodayBookings = $myTodaySales = $myAllBookings = $todayTripsCount = 0;
    $myRecentBookings = [];
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <!-- Employee Header Card with Live Login Info -->
    <div class="dt-card mb-4" style="background: var(--hero-grad); color:#ffffff; border:none;">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <span class="badge bg-warning text-dark fw-bold mb-2"><?= htmlspecialchars($currentUser['employee_code']) ?> &bull; <?= _e('employee') ?></span>
                <h2 class="fw-bold mb-1">Hello, <?= htmlspecialchars($currentUser['name']) ?>!</h2>
                <p class="mb-0 text-white-50 small">
                    <i class="bi bi-clock me-1"></i> Current Login: <strong><?= date('d M Y, h:i A', strtotime($currentUser['login_time'] ?? date('Y-m-d H:i:s'))) ?></strong>
                    &nbsp;|&nbsp; Counter Terminal: <strong>Ahmedabad Central Desk</strong>
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>employee/booking.php" class="btn btn-warning fw-bold text-dark px-4 shadow-sm">
                    <i class="bi bi-ticket-perforated-fill me-1"></i> <?= _e('book_ticket') ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="kpi-card">
                <div class="kpi-icon" style="background:#eff6ff;color:#2563eb;">
                    <i class="bi bi-ticket-detailed-fill"></i>
                </div>
                <div>
                    <div class="kpi-value"><?= $myTodayBookings ?></div>
                    <div class="kpi-label">Today's Counter Bookings</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="kpi-card">
                <div class="kpi-icon" style="background:#ecfdf5;color:#059669;">
                    <i class="bi bi-currency-rupee"></i>
                </div>
                <div>
                    <div class="kpi-value">₹<?= number_format($myTodaySales, 2) ?></div>
                    <div class="kpi-label">Today's Collected Fare</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="kpi-card">
                <div class="kpi-icon" style="background:#fdf4ff;color:#c026d3;">
                    <i class="bi bi-bus-front-fill"></i>
                </div>
                <div>
                    <div class="kpi-value"><?= $todayTripsCount ?></div>
                    <div class="kpi-label">Active Trips Today</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="kpi-card">
                <div class="kpi-icon" style="background:#fff7ed;color:#ea580c;">
                    <i class="bi bi-award-fill"></i>
                </div>
                <div>
                    <div class="kpi-value"><?= $myAllBookings ?></div>
                    <div class="kpi-label">All-Time Issued Tickets</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action Hub -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-grid-fill text-primary me-2"></i>Quick Navigation Hub</h5>
        </div>
        
        <div class="col-md-4 col-sm-6">
            <a href="<?= BASE_URL ?>employee/booking.php" class="dt-card d-flex align-items-center gap-3 text-decoration-none text-dark p-3 h-100">
                <div class="rounded-3 p-3 bg-primary bg-opacity-10 text-primary fs-3">
                    <i class="bi bi-ticket-perforated"></i>
                </div>
                <div>
                    <div class="fw-bold fs-6"><?= _e('menu_booking') ?></div>
                    <small class="text-muted">Interactive seat picker &amp; booking</small>
                </div>
            </a>
        </div>

        <div class="col-md-4 col-sm-6">
            <a href="<?= BASE_URL ?>employee/cancel_booking.php" class="dt-card d-flex align-items-center gap-3 text-decoration-none text-dark p-3 h-100">
                <div class="rounded-3 p-3 bg-danger bg-opacity-10 text-danger fs-3">
                    <i class="bi bi-x-circle"></i>
                </div>
                <div>
                    <div class="fw-bold fs-6"><?= _e('menu_cancel_booking') ?></div>
                    <small class="text-muted">Cancel ticket &amp; release seat</small>
                </div>
            </a>
        </div>

        <div class="col-md-4 col-sm-6">
            <a href="<?= BASE_URL ?>employee/tickets.php" class="dt-card d-flex align-items-center gap-3 text-decoration-none text-dark p-3 h-100">
                <div class="rounded-3 p-3 bg-success bg-opacity-10 text-success fs-3">
                    <i class="bi bi-receipt"></i>
                </div>
                <div>
                    <div class="fw-bold fs-6"><?= _e('menu_tickets') ?></div>
                    <small class="text-muted">Search &amp; reprint boarding passes</small>
                </div>
            </a>
        </div>

        <div class="col-md-4 col-sm-6">
            <a href="<?= BASE_URL ?>employee/customers.php" class="dt-card d-flex align-items-center gap-3 text-decoration-none text-dark p-3 h-100">
                <div class="rounded-3 p-3 bg-info bg-opacity-10 text-info fs-3">
                    <i class="bi bi-person-lines-fill"></i>
                </div>
                <div>
                    <div class="fw-bold fs-6"><?= _e('menu_customers') ?></div>
                    <small class="text-muted">Register passenger profiles</small>
                </div>
            </a>
        </div>

        <div class="col-md-4 col-sm-6">
            <a href="<?= BASE_URL ?>employee/inquiry.php" class="dt-card d-flex align-items-center gap-3 text-decoration-none text-dark p-3 h-100">
                <div class="rounded-3 p-3 bg-warning bg-opacity-10 text-warning fs-3">
                    <i class="bi bi-search"></i>
                </div>
                <div>
                    <div class="fw-bold fs-6"><?= _e('menu_inquiry') ?></div>
                    <small class="text-muted">Search available buses &amp; timing</small>
                </div>
            </a>
        </div>

        <div class="col-md-4 col-sm-6">
            <a href="<?= BASE_URL ?>employee/price_list.php" class="dt-card d-flex align-items-center gap-3 text-decoration-none text-dark p-3 h-100">
                <div class="rounded-3 p-3 bg-secondary bg-opacity-10 text-secondary fs-3">
                    <i class="bi bi-calculator"></i>
                </div>
                <div>
                    <div class="fw-bold fs-6"><?= _e('menu_price_list') ?></div>
                    <small class="text-muted">Fare list &amp; km rate calculator</small>
                </div>
            </a>
        </div>
    </div>

    <!-- Recent Counter Bookings Table -->
    <div class="dt-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-clock-history text-primary me-2"></i>My Recent Counter Bookings
            </h5>
            <a href="<?= BASE_URL ?>employee/tickets.php" class="btn btn-sm btn-outline-primary rounded-pill">
                <?= _e('all') ?> Tickets &rarr;
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
                    <?php if (!empty($myRecentBookings)): ?>
                        <?php foreach ($myRecentBookings as $bk): ?>
                            <tr>
                                <td>
                                    <span class="fw-bold font-monospace text-primary"><?= htmlspecialchars($bk['ticket_number']) ?></span>
                                    <div class="text-muted small"><?= date('d M, h:i A', strtotime($bk['booking_date'])) ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($bk['customer_name']) ?></div>
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
                            <td colspan="6" class="text-center text-muted py-4">No recent bookings by your counter. Click "Book Ticket Window" to issue tickets.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
