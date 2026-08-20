<?php
/**
 * Desire Travel - Ticket Cancellation & Seat Release Management
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/lang.php';
require_once __DIR__ . '/../helpers/auth.php';

requireEmployee();

$pageTitle = __('menu_cancel_booking');
$searchTerm = trim($_GET['search'] ?? '');
$searchedBooking = null;
$errorMsg = '';

// Search for Booking
if (!empty($searchTerm)) {
    try {
        $stmt = $pdo->prepare("
            SELECT b.*, c.name as customer_name, c.contact as customer_contact, c.email as customer_email,
                   r.route_name, r.start_point, r.end_point, r.distance_km,
                   bu.bus_number, bu.bus_name, bu.bus_type,
                   rt.travel_date, rt.departure_time, rt.arrival_time
            FROM bookings b
            JOIN customers c ON b.customer_id = c.id
            JOIN routines rt ON b.routine_id = rt.id
            JOIN routes r ON rt.route_id = r.id
            JOIN buses bu ON rt.bus_id = bu.id
            WHERE b.ticket_number = ? OR c.contact = ? OR b.id = ?
            LIMIT 1
        ");
        $stmt->execute([$searchTerm, $searchTerm, (int)$searchTerm]);
        $searchedBooking = $stmt->fetch();

        if (!$searchedBooking) {
            $errorMsg = __('ticket_not_found');
        }
    } catch (Exception $e) {
        $errorMsg = "Search error: " . $e->getMessage();
    }
}

// Handle Cancellation Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_cancel_booking'])) {
    $bookingId = (int)$_POST['booking_id'];
    $reason = trim($_POST['cancellation_reason'] ?? 'Passenger Request');
    $refundAmt = (float)($_POST['refund_amount'] ?? 0);

    try {
        $checkStmt = $pdo->prepare("SELECT ticket_number, booking_status, total_fare FROM bookings WHERE id = ?");
        $checkStmt->execute([$bookingId]);
        $bk = $checkStmt->fetch();

        if (!$bk) {
            $_SESSION['flash_error'] = __('ticket_not_found');
        } elseif ($bk['booking_status'] === 'Cancelled') {
            $_SESSION['flash_error'] = __('already_cancelled');
        } else {
            $updateStmt = $pdo->prepare("
                UPDATE bookings 
                SET booking_status = 'Cancelled',
                    payment_status = 'Refunded',
                    cancellation_reason = ?,
                    cancelled_at = NOW(),
                    refund_amount = ?
                WHERE id = ?
            ");
            $updateStmt->execute([$reason, $refundAmt, $bookingId]);

            $_SESSION['flash_success'] = sprintf(__('cancel_success'), $bk['ticket_number']);
        }
    } catch (Exception $e) {
        $_SESSION['flash_error'] = "Error cancelling ticket: " . $e->getMessage();
    }

    header('Location: ' . BASE_URL . 'employee/cancel_booking.php');
    exit;
}

// Fetch Recently Cancelled Tickets
$recentCancelled = [];
try {
    $rcStmt = $pdo->query("
        SELECT b.*, c.name as customer_name, c.contact as customer_contact, r.route_name, bu.bus_number
        FROM bookings b
        JOIN customers c ON b.customer_id = c.id
        JOIN routines rt ON b.routine_id = rt.id
        JOIN routes r ON rt.route_id = r.id
        JOIN buses bu ON rt.bus_id = bu.id
        WHERE b.booking_status = 'Cancelled'
        ORDER BY b.cancelled_at DESC LIMIT 6
    ");
    $recentCancelled = $rcStmt->fetchAll();
} catch (Exception $e) {}

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1"><?= _e('cancel_ticket') ?></h2>
            <p class="text-muted small mb-0">Search booking, release reserved seat numbers immediately, and process passenger refund</p>
        </div>
    </div>

    <!-- Search Box Card -->
    <div class="dt-card mb-4">
        <form method="GET" action="<?= BASE_URL ?>employee/cancel_booking.php" class="row g-3 align-items-end">
            <div class="col-md-9">
                <label class="form-label small fw-bold text-muted"><?= _e('search_ticket_cancel') ?> *</label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-light"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="e.g. DT-2026-1001 or +91 9825123456" value="<?= htmlspecialchars($searchTerm) ?>" required>
                </div>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100 py-2 fs-6 shadow-sm">
                    <i class="bi bi-search me-1"></i> <?= _e('search') ?>
                </button>
            </div>
        </form>
    </div>

    <?php if (!empty($errorMsg)): ?>
        <div class="alert alert-warning d-flex align-items-center gap-2 rounded-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div><?= htmlspecialchars($errorMsg) ?></div>
        </div>
    <?php endif; ?>

    <?php if ($searchedBooking): ?>
        <!-- Searched Booking Detail Card -->
        <div class="dt-card border-danger shadow mb-4">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                <h5 class="fw-bold text-danger mb-0">
                    <i class="bi bi-ticket-perforated me-2"></i>Booking Details Found (#<?= htmlspecialchars($searchedBooking['ticket_number']) ?>)
                </h5>
                <div>
                    <?php if ($searchedBooking['booking_status'] === 'Confirmed'): ?>
                        <span class="badge-active"><?= _e('confirmed') ?></span>
                    <?php else: ?>
                        <span class="badge-inactive"><?= _e('cancelled') ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-7">
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="row g-2 small">
                            <div class="col-sm-6">
                                <span class="text-muted"><?= _e('passenger') ?>:</span>
                                <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($searchedBooking['customer_name']) ?></div>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted"><?= _e('contact_no') ?>:</span>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($searchedBooking['customer_contact']) ?></div>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted"><?= _e('journey') ?>:</span>
                                <div class="fw-bold text-primary"><?= htmlspecialchars($searchedBooking['route_name']) ?></div>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted"><?= _e('travel_date') ?> &amp; Time:</span>
                                <div class="fw-semibold"><?= date('d M Y', strtotime($searchedBooking['travel_date'])) ?> (<?= date('h:i A', strtotime($searchedBooking['departure_time'])) ?>)</div>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted"><?= _e('seats') ?> Reserved:</span>
                                <div class="fw-bold text-danger fs-6"><?= htmlspecialchars($searchedBooking['seat_numbers']) ?> (<?= $searchedBooking['seat_count'] ?> Seats)</div>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted"><?= _e('total_payable') ?>:</span>
                                <div class="fw-bold text-success fs-5">₹<?= number_format((float)$searchedBooking['total_fare'], 2) ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <?php if ($searchedBooking['booking_status'] === 'Confirmed'): ?>
                        <!-- Cancellation Action Box -->
                        <div class="p-3 bg-danger bg-opacity-10 border border-danger rounded-3">
                            <h6 class="fw-bold text-danger mb-2"><i class="bi bi-exclamation-octagon me-1"></i>Confirm Seat Cancellation</h6>
                            <p class="small text-muted mb-3"><?= _e('cancel_confirmation_msg') ?></p>

                            <form action="<?= BASE_URL ?>employee/cancel_booking.php" method="POST">
                                <input type="hidden" name="action_cancel_booking" value="1">
                                <input type="hidden" name="booking_id" value="<?= $searchedBooking['id'] ?>">

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted"><?= _e('cancellation_reason') ?> *</label>
                                    <input type="text" name="cancellation_reason" class="form-control form-control-sm" placeholder="e.g. Passenger schedule change" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted"><?= _e('refund_amount') ?> *</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" step="1" name="refund_amount" class="form-control" value="<?= $searchedBooking['total_fare'] ?>" required>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-danger w-100 shadow-sm" onclick="return confirm('Confirm cancellation of Ticket #<?= $searchedBooking['ticket_number'] ?>?');">
                                    <i class="bi bi-trash3-fill me-1"></i> Release Seats &amp; Cancel Ticket
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-secondary rounded-3">
                            <i class="bi bi-info-circle me-1"></i> This ticket was already cancelled on <strong><?= date('d M Y, h:i A', strtotime($searchedBooking['cancelled_at'])) ?></strong>.
                            <div class="mt-2 text-danger small">Reason: <?= htmlspecialchars($searchedBooking['cancellation_reason']) ?></div>
                            <div class="text-success small">Refund Paid: ₹<?= number_format((float)$searchedBooking['refund_amount'], 2) ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Recently Cancelled Bookings -->
    <div class="dt-card">
        <h5 class="fw-bold mb-3"><i class="bi bi-clock-history text-secondary me-2"></i>Recently Cancelled Tickets (Seats Released)</h5>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th><?= _e('ticket_number') ?></th>
                        <th><?= _e('passenger') ?></th>
                        <th><?= _e('journey') ?></th>
                        <th>Freed Seats</th>
                        <th>Refund Amount</th>
                        <th>Cancelled At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recentCancelled)): ?>
                        <?php foreach ($recentCancelled as $rc): ?>
                            <tr>
                                <td>
                                    <span class="fw-bold font-monospace text-danger"><?= htmlspecialchars($rc['ticket_number']) ?></span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($rc['customer_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($rc['customer_contact']) ?></small>
                                </td>
                                <td>
                                    <div class="small fw-semibold"><?= htmlspecialchars($rc['route_name']) ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-danger-subtle text-danger fw-bold"><?= htmlspecialchars($rc['seat_numbers']) ?></span>
                                </td>
                                <td>
                                    <span class="fw-bold text-success">₹<?= number_format((float)$rc['refund_amount'], 2) ?></span>
                                </td>
                                <td>
                                    <small class="text-muted"><?= date('d M, h:i A', strtotime($rc['cancelled_at'])) ?></small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No cancelled tickets logged.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
