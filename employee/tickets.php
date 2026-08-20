<?php
/**
 * Desire Travel - Issued Tickets & Boarding Passes History
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/lang.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/ticket_generator.php';

requireEmployee();

$pageTitle = __('menu_tickets');
$searchTerm = trim($_GET['search'] ?? '');

// Fetch Tickets with JOINs
$sql = "
    SELECT b.*, c.name as customer_name, c.contact as customer_contact, c.email as customer_email,
           r.route_name, r.start_point, r.end_point, r.distance_km, r.estimated_duration,
           bu.bus_number, bu.bus_name, bu.bus_type,
           rt.travel_date, rt.departure_time, rt.arrival_time,
           e.name as employee_name
    FROM bookings b
    JOIN customers c ON b.customer_id = c.id
    JOIN routines rt ON b.routine_id = rt.id
    JOIN routes r ON rt.route_id = r.id
    JOIN buses bu ON rt.bus_id = bu.id
    LEFT JOIN employees e ON b.booked_by_employee_id = e.id
    WHERE 1=1
";

$params = [];
if (!empty($searchTerm)) {
    $sql .= " AND (b.ticket_number LIKE ? OR c.name LIKE ? OR c.contact LIKE ? OR r.route_name LIKE ?)";
    $likeTerm = "%{$searchTerm}%";
    $params = [$likeTerm, $likeTerm, $likeTerm, $likeTerm];
}
$sql .= " ORDER BY b.id DESC LIMIT 100";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ticketsList = $stmt->fetchAll();

// Modal Ticket Renderer for direct AJAX/URL view
$viewBooking = null;
if (isset($_GET['view_ticket_id'])) {
    $viewId = (int)$_GET['view_ticket_id'];
    foreach ($ticketsList as $t) {
        if ($t['id'] === $viewId) {
            $viewBooking = $t;
            break;
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1"><?= _e('ticket_records') ?></h2>
            <p class="text-muted small mb-0">View all customer reservations, verify boarding pass barcodes, and reprint tickets</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>employee/booking.php" class="btn btn-primary shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> <?= _e('book_ticket') ?>
            </a>
        </div>
    </div>

    <!-- Active Ticket Modal if requested via view link -->
    <?php if ($viewBooking): ?>
        <div class="dt-card border-primary shadow-lg mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-primary mb-0">
                    <i class="bi bi-receipt me-2"></i>Boarding Pass (#<?= htmlspecialchars($viewBooking['ticket_number']) ?>)
                </h5>
                <a href="<?= BASE_URL ?>employee/tickets.php" class="btn btn-sm btn-secondary"><?= _e('close') ?></a>
            </div>
            <?= renderTicketHtml($viewBooking) ?>
        </div>
    <?php endif; ?>

    <!-- Tickets Directory Card -->
    <div class="dt-card">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-pill px-3 py-2 fs-6"><?= count($ticketsList) ?> Issued Tickets</span>
            </div>
            <div class="d-flex gap-2" style="max-width:340px; width:100%;">
                <form method="GET" action="<?= BASE_URL ?>employee/tickets.php" class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search ticket, name, phone..." value="<?= htmlspecialchars($searchTerm) ?>" data-table-search="#ticketsTable">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                    <?php if (!empty($searchTerm)): ?>
                        <a href="<?= BASE_URL ?>employee/tickets.php" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle table-custom" id="ticketsTable">
                <thead>
                    <tr>
                        <th><?= _e('ticket_number') ?></th>
                        <th><?= _e('passenger') ?></th>
                        <th><?= _e('journey') ?></th>
                        <th><?= _e('travel_date') ?></th>
                        <th><?= _e('seats') ?></th>
                        <th><?= _e('total_payable') ?></th>
                        <th><?= _e('status') ?></th>
                        <th class="text-end"><?= _e('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($ticketsList)): ?>
                        <?php foreach ($ticketsList as $t): ?>
                            <tr>
                                <td>
                                    <a href="?view_ticket_id=<?= $t['id'] ?>" class="fw-bold font-monospace text-primary text-decoration-none">
                                        <?= htmlspecialchars($t['ticket_number']) ?>
                                    </a>
                                    <div class="text-muted small" style="font-size:0.75rem;"><?= date('d M, h:i A', strtotime($t['booking_date'])) ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($t['customer_name']) ?></div>
                                    <small class="text-muted"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($t['customer_contact']) ?></small>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($t['route_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($t['bus_name']) ?> (<?= htmlspecialchars($t['bus_number']) ?>)</small>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= date('D, d M Y', strtotime($t['travel_date'])) ?></div>
                                    <small class="text-muted"><?= date('h:i A', strtotime($t['departure_time'])) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary fw-bold font-monospace"><?= htmlspecialchars($t['seat_numbers']) ?></span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">₹<?= number_format((float)$t['total_fare'], 2) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($t['payment_method']) ?></small>
                                </td>
                                <td>
                                    <?php if ($t['booking_status'] === 'Confirmed'): ?>
                                        <span class="badge-active"><?= _e('confirmed') ?></span>
                                    <?php else: ?>
                                        <span class="badge-inactive"><?= _e('cancelled') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="?view_ticket_id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary rounded-2 me-1" title="<?= _e('view_ticket') ?>">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if ($t['booking_status'] === 'Confirmed'): ?>
                                        <a href="<?= BASE_URL ?>employee/cancel_booking.php?search=<?= urlencode($t['ticket_number']) ?>" class="btn btn-sm btn-outline-danger rounded-2" title="<?= _e('cancel') ?>">
                                            <i class="bi bi-x-circle"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">No tickets found matching your query.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
