<?php
/**
 * Desire Travel - Comprehensive Booking & Revenue Reports
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/lang.php';
require_once __DIR__ . '/../helpers/auth.php';

requireAdmin();

$pageTitle = __('menu_reports');

// Extract Filters
$filterRoute = (int)($_GET['route_id'] ?? 0);
$filterBus = (int)($_GET['bus_id'] ?? 0);
$filterDateFrom = $_GET['date_from'] ?? '';
$filterDateTo = $_GET['date_to'] ?? '';
$filterStatus = $_GET['status'] ?? '';

// Build Query
$sql = "
    SELECT b.*, c.name as customer_name, c.contact as customer_contact, c.email as customer_email,
           r.route_name, r.start_point, r.end_point,
           bu.bus_number, bu.bus_name, bu.bus_type,
           rt.travel_date, rt.departure_time, rt.arrival_time,
           e.name as employee_name, e.employee_code
    FROM bookings b
    JOIN customers c ON b.customer_id = c.id
    JOIN routines rt ON b.routine_id = rt.id
    JOIN routes r ON rt.route_id = r.id
    JOIN buses bu ON rt.bus_id = bu.id
    LEFT JOIN employees e ON b.booked_by_employee_id = e.id
    WHERE 1=1
";

$params = [];

if ($filterRoute > 0) {
    $sql .= " AND r.id = ?";
    $params[] = $filterRoute;
}
if ($filterBus > 0) {
    $sql .= " AND bu.id = ?";
    $params[] = $filterBus;
}
if (!empty($filterDateFrom)) {
    $sql .= " AND DATE(b.booking_date) >= ?";
    $params[] = $filterDateFrom;
}
if (!empty($filterDateTo)) {
    $sql .= " AND DATE(b.booking_date) <= ?";
    $params[] = $filterDateTo;
}
if (!empty($filterStatus)) {
    $sql .= " AND b.booking_status = ?";
    $params[] = $filterStatus;
}

$sql .= " ORDER BY b.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reportData = $stmt->fetchAll();

// CSV Export Mode
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Desire_Travel_Bookings_' . date('Y-m-d_His') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Ticket Number', 'Booking Date', 'Customer Name', 'Contact', 'Route', 'Bus', 'Travel Date', 'Departure', 'Seats', 'Fare (INR)', 'Payment Status', 'Booking Status', 'Refund (INR)', 'Booked By']);
    
    foreach ($reportData as $row) {
        fputcsv($output, [
            $row['ticket_number'],
            $row['booking_date'],
            $row['customer_name'],
            $row['customer_contact'],
            $row['route_name'],
            $row['bus_name'] . ' (' . $row['bus_number'] . ')',
            $row['travel_date'],
            $row['departure_time'],
            $row['seat_numbers'],
            $row['total_fare'],
            $row['payment_status'],
            $row['booking_status'],
            $row['refund_amount'],
            $row['employee_name'] ?? 'Self'
        ]);
    }
    fclose($output);
    exit;
}

// Calculate Summary Totals
$totalTickets = count($reportData);
$totalGrossRevenue = 0.0;
$totalRefunds = 0.0;
$totalConfirmedCount = 0;
$totalCancelledCount = 0;

foreach ($reportData as $row) {
    if ($row['booking_status'] === 'Confirmed') {
        $totalGrossRevenue += (float)$row['total_fare'];
        $totalConfirmedCount++;
    } else {
        $totalRefunds += (float)$row['refund_amount'];
        $totalCancelledCount++;
    }
}
$netRevenue = $totalGrossRevenue - $totalRefunds;

// Fetch routes and buses for filter dropdowns
$allRoutes = $pdo->query("SELECT id, route_name FROM routes ORDER BY route_name ASC")->fetchAll();
$allBuses = $pdo->query("SELECT id, bus_name, bus_number FROM buses ORDER BY bus_name ASC")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 no-print">
        <div>
            <h2 class="fw-bold text-dark mb-1"><?= _e('booking_reports') ?></h2>
            <p class="text-muted small mb-0">Financial reports, booking statements, route performance, and audit export</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button onclick="window.print();" class="btn btn-sm btn-outline-secondary bg-white shadow-sm">
                <i class="bi bi-printer me-1"></i> <?= _e('print') ?> / PDF
            </button>
            <?php
            $exportQuery = $_GET;
            $exportQuery['export'] = 'csv';
            ?>
            <a href="?<?= http_build_query($exportQuery) ?>" class="btn btn-sm btn-success shadow-sm">
                <i class="bi bi-file-earmark-excel me-1"></i> <?= _e('export_csv') ?>
            </a>
        </div>
    </div>

    <!-- Filter Form Bar (Hidden in Print) -->
    <div class="dt-card mb-4 no-print">
        <form method="GET" action="<?= BASE_URL ?>admin/booking_reports.php">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted"><?= _e('filter_by_route') ?></label>
                    <select name="route_id" class="form-select">
                        <option value="0"><?= _e('all') ?> Routes</option>
                        <?php foreach ($allRoutes as $r): ?>
                            <option value="<?= $r['id'] ?>" <?= $filterRoute == $r['id'] ? 'selected' : '' ?>><?= htmlspecialchars($r['route_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted"><?= _e('filter_by_bus') ?></label>
                    <select name="bus_id" class="form-select">
                        <option value="0"><?= _e('all') ?> Fleet Buses</option>
                        <?php foreach ($allBuses as $b): ?>
                            <option value="<?= $b['id'] ?>" <?= $filterBus == $b['id'] ? 'selected' : '' ?>><?= htmlspecialchars($b['bus_name']) ?> (<?= $b['bus_number'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted"><?= _e('filter_by_date_from') ?></label>
                    <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($filterDateFrom) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted"><?= _e('filter_by_date_to') ?></label>
                    <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($filterDateTo) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted"><?= _e('filter_by_status') ?></label>
                    <select name="status" class="form-select">
                        <option value=""><?= _e('all') ?></option>
                        <option value="Confirmed" <?= $filterStatus === 'Confirmed' ? 'selected' : '' ?>><?= _e('confirmed') ?></option>
                        <option value="Cancelled" <?= $filterStatus === 'Cancelled' ? 'selected' : '' ?>><?= _e('cancelled') ?></option>
                    </select>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                    <a href="<?= BASE_URL ?>admin/booking_reports.php" class="btn btn-sm btn-secondary"><?= _e('reset') ?></a>
                    <button type="submit" class="btn btn-sm btn-primary px-4"><?= _e('filter') ?></button>
                </div>
            </div>
        </form>
    </div>

    <!-- KPI Summary Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="p-3 bg-white border rounded-3 text-center shadow-sm">
                <div class="text-muted small text-uppercase fw-bold"><?= _e('total_tickets') ?></div>
                <div class="fs-4 fw-bold text-primary"><?= $totalTickets ?></div>
                <div class="small text-muted"><?= $totalConfirmedCount ?> Active / <?= $totalCancelledCount ?> Cancelled</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="p-3 bg-white border rounded-3 text-center shadow-sm">
                <div class="text-muted small text-uppercase fw-bold"><?= _e('total_confirmed_revenue') ?></div>
                <div class="fs-4 fw-bold text-success">₹<?= number_format($totalGrossRevenue, 2) ?></div>
                <div class="small text-success">Gross Passenger Fares</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="p-3 bg-white border rounded-3 text-center shadow-sm">
                <div class="text-muted small text-uppercase fw-bold"><?= _e('total_refunds') ?></div>
                <div class="fs-4 fw-bold text-danger">₹<?= number_format($totalRefunds, 2) ?></div>
                <div class="small text-danger">Cancelled Fares Refunded</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="p-3 bg-white border rounded-3 text-center shadow-sm">
                <div class="text-muted small text-uppercase fw-bold">Net Realized Revenue</div>
                <div class="fs-4 fw-bold text-dark">₹<?= number_format($netRevenue, 2) ?></div>
                <div class="small text-muted">Net Settled Cashflow</div>
            </div>
        </div>
    </div>

    <!-- Print Header (Visible only during Print) -->
    <div class="d-none d-print-block text-center mb-4">
        <h2><?= htmlspecialchars(APP_NAME) ?> - Financial Booking Report</h2>
        <p class="text-muted">Generated on <?= date('d M Y, h:i A') ?> by <?= htmlspecialchars($currentUser['name']) ?></p>
        <hr>
    </div>

    <!-- Detailed Bookings Report Grid -->
    <div class="dt-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle table-sm table-custom" id="reportsTable">
                <thead>
                    <tr>
                        <th><?= _e('ticket_number') ?></th>
                        <th><?= _e('date') ?></th>
                        <th><?= _e('passenger') ?></th>
                        <th><?= _e('journey') ?></th>
                        <th><?= _e('menu_buses') ?></th>
                        <th><?= _e('travel_date') ?></th>
                        <th><?= _e('seats') ?></th>
                        <th><?= _e('total_payable') ?></th>
                        <th><?= _e('status') ?></th>
                        <th>Booked By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($reportData)): ?>
                        <?php foreach ($reportData as $bk): ?>
                            <tr>
                                <td>
                                    <span class="fw-bold font-monospace text-primary"><?= htmlspecialchars($bk['ticket_number']) ?></span>
                                </td>
                                <td>
                                    <small class="text-muted"><?= date('d M Y, h:i A', strtotime($bk['booking_date'])) ?></small>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark small"><?= htmlspecialchars($bk['customer_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($bk['customer_contact']) ?></small>
                                </td>
                                <td>
                                    <div class="small fw-semibold"><?= htmlspecialchars($bk['route_name']) ?></div>
                                </td>
                                <td>
                                    <div class="small"><?= htmlspecialchars($bk['bus_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($bk['bus_number']) ?></small>
                                </td>
                                <td>
                                    <div class="small fw-semibold"><?= date('d M Y', strtotime($bk['travel_date'])) ?></div>
                                    <small class="text-muted"><?= date('h:i A', strtotime($bk['departure_time'])) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-primary border fw-bold"><?= htmlspecialchars($bk['seat_numbers']) ?></span>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark">₹<?= number_format((float)$bk['total_fare'], 2) ?></span>
                                    <?php if ($bk['booking_status'] === 'Cancelled'): ?>
                                        <div class="text-danger small">(Ref: ₹<?= number_format((float)$bk['refund_amount'], 2) ?>)</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($bk['booking_status'] === 'Confirmed'): ?>
                                        <span class="badge-active"><?= _e('confirmed') ?></span>
                                    <?php else: ?>
                                        <span class="badge-inactive"><?= _e('cancelled') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted"><?= htmlspecialchars($bk['employee_name'] ?? 'System') ?></small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted py-5">No bookings match the selected filter criteria.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
