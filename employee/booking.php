<?php
/**
 * Desire Travel - Ticket Reservation & Interactive Seat Selection Engine
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/lang.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/fare_calculator.php';
require_once __DIR__ . '/../helpers/ticket_generator.php';

requireEmployee();

$pageTitle = __('menu_booking');
$confirmedBooking = null;

// Handle Booking Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_confirm_booking'])) {
    $routineId = (int)$_POST['routine_id'];
    $customerId = (int)$_POST['customer_id'];
    $selectedSeatsStr = trim($_POST['selected_seats'] ?? '');
    $seatCount = (int)($_POST['seat_count'] ?? 1);
    $paymentMethod = trim($_POST['payment_method'] ?? 'Cash');
    $bookedByEmpId = (int)$currentUser['id'];

    if (empty($selectedSeatsStr) || $seatCount <= 0) {
        $_SESSION['flash_error'] = __('please_select_seat');
    } elseif (empty($customerId)) {
        $_SESSION['flash_error'] = __('please_select_passenger');
    } else {
        // Fetch routine details
        $rtStmt = $pdo->prepare("
            SELECT rt.*, b.capacity, b.bus_type, b.bus_name, b.bus_number,
                   r.route_name, r.start_point, r.end_point, r.distance_km, r.estimated_duration
            FROM routines rt
            JOIN buses b ON rt.bus_id = b.id
            JOIN routes r ON rt.route_id = r.id
            WHERE rt.id = ?
        ");
        $rtStmt->execute([$routineId]);
        $routine = $rtStmt->fetch();

        if (!$routine) {
            $_SESSION['flash_error'] = "Invalid travel routine selected.";
        } else {
            // Verify seats are not already booked for this routine
            $bookedSeatsStmt = $pdo->prepare("SELECT seat_numbers FROM bookings WHERE routine_id = ? AND booking_status = 'Confirmed'");
            $bookedSeatsStmt->execute([$routineId]);
            $existingBookedRows = $bookedSeatsStmt->fetchAll(PDO::FETCH_COLUMN);

            $alreadyOccupiedSeats = [];
            foreach ($existingBookedRows as $rowSeats) {
                $seatsArr = array_map('trim', explode(',', $rowSeats));
                $alreadyOccupiedSeats = array_merge($alreadyOccupiedSeats, $seatsArr);
            }

            $requestedSeats = array_map('trim', explode(',', $selectedSeatsStr));
            $conflictFound = false;
            foreach ($requestedSeats as $reqSeat) {
                if (in_array($reqSeat, $alreadyOccupiedSeats)) {
                    $conflictFound = true;
                    break;
                }
            }

            if ($conflictFound) {
                $_SESSION['flash_error'] = "One or more of your selected seats (Seat #{$reqSeat}) were just booked by another counter! Please choose different seats.";
            } else {
                // Calculate Fare
                $baseFarePerSeat = (float)$routine['fare'];
                $totalFare = $seatCount * $baseFarePerSeat;
                $ticketNumber = 'DT-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));

                try {
                    $insStmt = $pdo->prepare("
                        INSERT INTO bookings (ticket_number, routine_id, customer_id, booked_by_employee_id, seat_numbers, seat_count, base_fare, discount, total_fare, payment_status, payment_method, booking_status)
                        VALUES (?, ?, ?, ?, ?, ?, ?, 0.00, ?, 'Paid', ?, 'Confirmed')
                    ");
                    $insStmt->execute([$ticketNumber, $routineId, $customerId, $bookedByEmpId, $selectedSeatsStr, $seatCount, $baseFarePerSeat, $totalFare, $paymentMethod]);

                    $bookingId = $pdo->lastInsertId();

                    // Fetch complete booking object for instant ticket rendering
                    $fetchBkStmt = $pdo->prepare("
                        SELECT b.*, c.name as customer_name, c.contact as customer_contact, c.email as customer_email,
                               r.route_name, r.start_point, r.end_point, r.distance_km, r.estimated_duration,
                               bu.bus_number, bu.bus_name, bu.bus_type,
                               rt.travel_date, rt.departure_time, rt.arrival_time
                        FROM bookings b
                        JOIN customers c ON b.customer_id = c.id
                        JOIN routines rt ON b.routine_id = rt.id
                        JOIN routes r ON rt.route_id = r.id
                        JOIN buses bu ON rt.bus_id = bu.id
                        WHERE b.id = ?
                    ");
                    $fetchBkStmt->execute([$bookingId]);
                    $confirmedBooking = $fetchBkStmt->fetch();

                    $_SESSION['flash_success'] = __('booking_success');
                } catch (Exception $e) {
                    $_SESSION['flash_error'] = "Booking error: " . $e->getMessage();
                }
            }
        }
    }
}

// Selected Routine from GET parameter or default to first available
$selectedRoutineId = (int)($_GET['routine_id'] ?? 0);

// Fetch all scheduled upcoming routines
$routinesDropdown = $pdo->query("
    SELECT rt.id, rt.travel_date, rt.departure_time, rt.arrival_time, rt.fare,
           r.route_name, r.start_point, r.end_point, r.distance_km,
           b.bus_name, b.bus_number, b.bus_type, b.capacity
    FROM routines rt
    JOIN routes r ON rt.route_id = r.id
    JOIN buses b ON rt.bus_id = b.id
    WHERE rt.travel_date >= CURRENT_DATE() AND rt.status = 'scheduled'
    ORDER BY rt.travel_date ASC, rt.departure_time ASC
")->fetchAll();

if ($selectedRoutineId === 0 && !empty($routinesDropdown)) {
    $selectedRoutineId = $routinesDropdown[0]['id'];
}

// Fetch Active Routine Details
$currentRoutine = null;
$bookedSeatsForRoutine = [];

if ($selectedRoutineId > 0) {
    foreach ($routinesDropdown as $rd) {
        if ($rd['id'] == $selectedRoutineId) {
            $currentRoutine = $rd;
            break;
        }
    }

    if ($currentRoutine) {
        $bsStmt = $pdo->prepare("SELECT seat_numbers FROM bookings WHERE routine_id = ? AND booking_status = 'Confirmed'");
        $bsStmt->execute([$selectedRoutineId]);
        $seatRows = $bsStmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($seatRows as $sRow) {
            $arr = array_map('trim', explode(',', $sRow));
            $bookedSeatsForRoutine = array_merge($bookedSeatsForRoutine, $arr);
        }
    }
}

// Fetch Customers List for selection
$customers = $pdo->query("SELECT id, name, contact, cnic, email FROM customers ORDER BY name ASC")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1"><?= _e('book_ticket') ?></h2>
            <p class="text-muted small mb-0">Interactive visual seat layout, dynamic fare calculator, and instant boarding pass issuer</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>employee/tickets.php" class="btn btn-outline-primary bg-white shadow-sm">
                <i class="bi bi-receipt me-1"></i> <?= _e('menu_tickets') ?>
            </a>
        </div>
    </div>

    <?php if ($confirmedBooking): ?>
        <!-- Confirmed Ticket Modal / Banner -->
        <div class="dt-card border-success shadow-lg mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2 text-success">
                    <i class="bi bi-check-circle-fill fs-3"></i>
                    <h4 class="fw-bold mb-0">Booking Confirmed &amp; Boarding Pass Generated!</h4>
                </div>
                <button onclick="window.print();" class="btn btn-primary">
                    <i class="bi bi-printer-fill me-1"></i> Print Boarding Pass
                </button>
            </div>
            
            <?= renderTicketHtml($confirmedBooking) ?>

            <div class="text-center mt-3 no-print">
                <a href="<?= BASE_URL ?>employee/booking.php" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="bi bi-plus-circle me-1"></i> Book Another Ticket
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Step 1: Routine Selector Header Bar -->
    <div class="dt-card mb-4">
        <form method="GET" action="<?= BASE_URL ?>employee/booking.php" class="row g-3 align-items-end">
            <div class="col-lg-8">
                <label class="form-label small fw-bold text-muted"><?= _e('step_select_trip') ?> *</label>
                <select name="routine_id" class="form-select form-select-lg fw-semibold" onchange="this.form.submit()">
                    <?php if (!empty($routinesDropdown)): ?>
                        <?php foreach ($routinesDropdown as $rd): ?>
                            <option value="<?= $rd['id'] ?>" <?= $rd['id'] == $selectedRoutineId ? 'selected' : '' ?>>
                                <?= date('d M Y', strtotime($rd['travel_date'])) ?> &bull; <?= date('h:i A', strtotime($rd['departure_time'])) ?> &mdash; <?= htmlspecialchars($rd['route_name']) ?> (<?= htmlspecialchars($rd['bus_name']) ?> - <?= htmlspecialchars($rd['bus_type']) ?>) &bull; ₹<?= number_format($rd['fare'], 2) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="">No scheduled routines available</option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-lg-4 text-lg-end">
                <?php if ($currentRoutine): ?>
                    <span class="badge bg-primary fs-6 px-3 py-2">
                        <i class="bi bi-currency-rupee"></i><?= number_format($currentRoutine['fare'], 2) ?> / Seat
                    </span>
                    <span class="badge bg-secondary fs-6 px-3 py-2 ms-1">
                        <?= $currentRoutine['capacity'] - count($bookedSeatsForRoutine) ?> Seats Left
                    </span>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <?php if ($currentRoutine): ?>
        <div class="row g-4">
            <!-- Left Column: Interactive Seat Grid Map -->
            <div class="col-lg-6">
                <div class="dt-card h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-grid-3x3-gap-fill text-primary me-2"></i><?= _e('step_select_seat') ?>
                        </h5>
                        <small class="text-muted"><?= htmlspecialchars($currentRoutine['bus_name']) ?> (<?= htmlspecialchars($currentRoutine['bus_type']) ?>)</small>
                    </div>

                    <!-- Bus Cabin Container -->
                    <div class="bus-cabin-wrapper">
                        <!-- Front Windshield & Driver Cabin -->
                        <div class="bus-front-cabin">
                            <div class="bus-door"><i class="bi bi-door-open-fill me-1"></i>Passenger Entry</div>
                            <div class="driver-wheel">
                                <i class="bi bi-steering-wheel fs-5 text-dark"></i>
                                <span><?= _e('driver_cabin') ?></span>
                            </div>
                        </div>

                        <!-- Seat Legend -->
                        <div class="seat-legend">
                            <div class="legend-item">
                                <div class="legend-box available"></div>
                                <span><?= _e('available_seats') ?></span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-box selected"></div>
                                <span><?= _e('selected_seats') ?></span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-box booked"></div>
                                <span><?= _e('occupied_seats') ?></span>
                            </div>
                        </div>

                        <!-- 2x2 Seater Grid Layout Generator -->
                        <div class="seat-grid-container">
                            <?php
                            $totalCapacity = (int)$currentRoutine['capacity'];
                            $rows = ceil($totalCapacity / 4);

                            for ($r = 1; $r <= $rows; $r++) {
                                $s1 = ($r - 1) * 4 + 1;
                                $s2 = ($r - 1) * 4 + 2;
                                $s3 = ($r - 1) * 4 + 3;
                                $s4 = ($r - 1) * 4 + 4;
                                ?>
                                <div class="seat-row">
                                    <!-- Left Pair -->
                                    <div class="seat-pair">
                                        <?php foreach ([$s1, $s2] as $seatNo): ?>
                                            <?php if ($seatNo <= $totalCapacity): ?>
                                                <?php
                                                $formattedSeat = str_pad($seatNo, 2, '0', STR_PAD_LEFT);
                                                $isBooked = in_array($formattedSeat, $bookedSeatsForRoutine) || in_array((string)$seatNo, $bookedSeatsForRoutine);
                                                ?>
                                                <div class="seat-box <?= $isBooked ? 'booked' : 'available' ?>" data-seat-no="<?= $formattedSeat ?>">
                                                    <span class="seat-no"><?= $formattedSeat ?></span>
                                                    <i class="bi bi-person-fill seat-icon"></i>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>

                                    <!-- Center Aisle -->
                                    <div class="seat-aisle"><?= $r ?></div>

                                    <!-- Right Pair -->
                                    <div class="seat-pair">
                                        <?php foreach ([$s3, $s4] as $seatNo): ?>
                                            <?php if ($seatNo <= $totalCapacity): ?>
                                                <?php
                                                $formattedSeat = str_pad($seatNo, 2, '0', STR_PAD_LEFT);
                                                $isBooked = in_array($formattedSeat, $bookedSeatsForRoutine) || in_array((string)$seatNo, $bookedSeatsForRoutine);
                                                ?>
                                                <div class="seat-box <?= $isBooked ? 'booked' : 'available' ?>" data-seat-no="<?= $formattedSeat ?>">
                                                    <span class="seat-no"><?= $formattedSeat ?></span>
                                                    <i class="bi bi-person-fill seat-icon"></i>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Passenger Selector, Dynamic Pricing & Checkout -->
            <div class="col-lg-6">
                <div class="dt-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-person-badge text-primary me-2"></i><?= _e('step_select_passenger') ?>
                            </h5>
                            <button type="button" class="btn btn-sm btn-outline-success rounded-pill" data-bs-toggle="modal" data-bs-target="#quickAddPassengerModal">
                                <i class="bi bi-plus-lg me-1"></i> New Passenger
                            </button>
                        </div>

                        <form action="<?= BASE_URL ?>employee/booking.php" method="POST" id="bookingConfirmationForm">
                            <input type="hidden" name="action_confirm_booking" value="1">
                            <input type="hidden" name="routine_id" value="<?= $selectedRoutineId ?>">
                            <input type="hidden" name="selected_seats" id="selected_seats_input" value="">
                            <input type="hidden" name="seat_count" id="seat_count_input" value="0">
                            <input type="hidden" name="total_fare" id="total_fare_input" value="0.00">
                            <input type="hidden" id="base_fare_per_seat" value="<?= $currentRoutine['fare'] ?>">

                            <!-- Passenger Selection Dropdown -->
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted"><?= _e('passenger') ?> *</label>
                                <select name="customer_id" class="form-select" required id="customer_select_box">
                                    <option value="">-- Choose Registered Passenger --</option>
                                    <?php foreach ($customers as $c): ?>
                                        <option value="<?= $c['id'] ?>">
                                            <?= htmlspecialchars($c['name']) ?> &bull; <?= htmlspecialchars($c['contact']) ?> (ID: <?= htmlspecialchars($c['cnic']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Payment Method -->
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted"><?= _e('payment_method') ?> *</label>
                                <select name="payment_method" class="form-select">
                                    <option value="Cash" selected><?= _e('cash') ?> (Counter)</option>
                                    <option value="UPI"><?= _e('upi') ?> (Instant QR)</option>
                                    <option value="Credit/Debit Card"><?= _e('card') ?></option>
                                    <option value="Net Banking"><?= _e('net_banking') ?></option>
                                </select>
                            </div>

                            <!-- Real-Time Booking Summary Box -->
                            <div class="p-3 bg-light rounded-3 border mb-4">
                                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Live Booking Summary</h6>
                                
                                <div class="d-flex justify-content-between mb-2 small">
                                    <span class="text-muted">Route / Journey:</span>
                                    <span class="fw-bold text-dark"><?= htmlspecialchars($currentRoutine['route_name']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2 small">
                                    <span class="text-muted">Travel Date &amp; Time:</span>
                                    <span class="fw-semibold"><?= date('D, d M Y', strtotime($currentRoutine['travel_date'])) ?> at <?= date('h:i A', strtotime($currentRoutine['departure_time'])) ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2 small">
                                    <span class="text-muted">Distance / Bus:</span>
                                    <span><?= $currentRoutine['distance_km'] ?> KM &bull; <?= htmlspecialchars($currentRoutine['bus_number']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2 small">
                                    <span class="text-muted">Rate per Seat:</span>
                                    <span class="fw-semibold">₹<?= number_format($currentRoutine['fare'], 2) ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2 small">
                                    <span class="text-muted">Selected Seat Numbers:</span>
                                    <span class="fw-bold text-primary font-monospace" id="selected_seats_display">None selected</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold fs-6 text-dark"><?= _e('total_payable') ?>:</span>
                                    <span class="fw-bold fs-3 text-success" id="total_fare_display">₹0.00</span>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" id="submit_booking_btn" class="btn btn-primary w-100 py-3 fs-5 shadow" disabled>
                                <i class="bi bi-check2-circle me-2"></i> <?= _e('confirm_and_print') ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: Quick Add Passenger -->
<div class="modal fade" id="quickAddPassengerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="<?= BASE_URL ?>employee/customers.php" method="POST">
                <input type="hidden" name="action_add_customer" value="1">
                <div class="modal-header bg-primary text-white py-3 px-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-person-plus me-2"></i>Quick Register Passenger</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted"><?= _e('full_name') ?> *</label>
                            <input type="text" name="name" class="form-control" placeholder="Passenger Full Name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('contact_no') ?> *</label>
                            <input type="text" name="contact" class="form-control" placeholder="+91 9825..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('cnic_id') ?> *</label>
                            <input type="text" name="cnic" class="form-control" placeholder="Aadhaar / ID No." required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted"><?= _e('email_address') ?> *</label>
                            <input type="email" name="email" class="form-control" placeholder="passenger@email.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('gender') ?></label>
                            <select name="gender" class="form-select">
                                <option value="Male"><?= _e('male') ?></option>
                                <option value="Female"><?= _e('female') ?></option>
                                <option value="Other"><?= _e('other') ?></option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal"><?= _e('cancel') ?></button>
                    <button type="submit" class="btn btn-primary rounded-3 shadow-sm px-4">Register Passenger</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include Interactive Booking JS Engine -->
<script src="<?= BASE_URL ?>assets/js/booking.js"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
