<?php
/**
 * Desire Travel - Route & Fleet Schedule Inquiry Window
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/lang.php';
require_once __DIR__ . '/../helpers/auth.php';

requireEmployee();

$pageTitle = __('inquiry_window');

$sourceCity = trim($_GET['source'] ?? '');
$destCity = trim($_GET['destination'] ?? '');
$travelDate = trim($_GET['date'] ?? date('Y-m-d'));

// Fetch unique source and destination city list
$sourcesList = $pdo->query("SELECT DISTINCT start_point FROM routes ORDER BY start_point ASC")->fetchAll(PDO::FETCH_COLUMN);
$destList = $pdo->query("SELECT DISTINCT end_point FROM routes ORDER BY end_point ASC")->fetchAll(PDO::FETCH_COLUMN);

// Fetch Matching Trips
$sql = "
    SELECT rt.*, r.route_name, r.start_point, r.end_point, r.distance_km, r.estimated_duration,
           b.bus_number, b.bus_name, b.bus_type, b.capacity,
           (SELECT COUNT(*) FROM bookings bk WHERE bk.routine_id = rt.id AND bk.booking_status = 'Confirmed') as booked_seats
    FROM routines rt
    JOIN routes r ON rt.route_id = r.id
    JOIN buses b ON rt.bus_id = b.id
    WHERE rt.status = 'scheduled'
";

$params = [];

if (!empty($sourceCity)) {
    $sql .= " AND r.start_point = ?";
    $params[] = $sourceCity;
}
if (!empty($destCity)) {
    $sql .= " AND r.end_point = ?";
    $params[] = $destCity;
}
if (!empty($travelDate)) {
    $sql .= " AND rt.travel_date = ?";
    $params[] = $travelDate;
}

$sql .= " ORDER BY rt.travel_date ASC, rt.departure_time ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$matchingRoutines = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1"><?= _e('inquiry_window') ?></h2>
            <p class="text-muted small mb-0">Search available buses between cities, check seat availability &amp; fare details</p>
        </div>
    </div>

    <!-- Inquiry Filter Card -->
    <div class="dt-card mb-4">
        <form method="GET" action="<?= BASE_URL ?>employee/inquiry.php" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted"><?= _e('source_city') ?></label>
                <select name="source" class="form-select">
                    <option value=""><?= _e('all') ?> Origin Cities</option>
                    <?php foreach ($sourcesList as $src): ?>
                        <option value="<?= htmlspecialchars($src) ?>" <?= $sourceCity === $src ? 'selected' : '' ?>><?= htmlspecialchars($src) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted"><?= _e('destination_city') ?></label>
                <select name="destination" class="form-select">
                    <option value=""><?= _e('all') ?> Destination Cities</option>
                    <?php foreach ($destList as $dst): ?>
                        <option value="<?= htmlspecialchars($dst) ?>" <?= $destCity === $dst ? 'selected' : '' ?>><?= htmlspecialchars($dst) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted"><?= _e('travel_date') ?></label>
                <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($travelDate) ?>" min="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 py-2">
                    <i class="bi bi-search me-1"></i> <?= _e('search_trips') ?>
                </button>
                <a href="<?= BASE_URL ?>employee/inquiry.php" class="btn btn-outline-secondary py-2">
                    <?= _e('reset') ?>
                </a>
            </div>
        </form>
    </div>

    <!-- Matching Results -->
    <div class="dt-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-bus-front-fill text-primary me-2"></i>Available Travel Schedules (<?= count($matchingRoutines) ?> Found)
            </h5>
        </div>

        <div class="row g-3">
            <?php if (!empty($matchingRoutines)): ?>
                <?php foreach ($matchingRoutines as $trip): ?>
                    <?php
                    $availableSeats = $trip['capacity'] - $trip['booked_seats'];
                    ?>
                    <div class="col-lg-6">
                        <div class="p-3 bg-white rounded-3 border shadow-sm h-100 d-flex flex-column justify-content-between">
                            <div>
                                <!-- Header Route & Date -->
                                <div class="d-flex justify-content-between align-items-start border-bottom pb-2 mb-3">
                                    <div>
                                        <h5 class="fw-bold text-primary mb-1"><?= htmlspecialchars($trip['route_name']) ?></h5>
                                        <small class="text-muted"><i class="bi bi-geo-alt-fill text-danger me-1"></i><?= htmlspecialchars($trip['start_point']) ?> &rarr; <i class="bi bi-pin-map-fill text-success me-1"></i><?= htmlspecialchars($trip['end_point']) ?></small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-primary-subtle text-primary fw-bold"><?= date('D, d M Y', strtotime($trip['travel_date'])) ?></span>
                                        <div class="text-muted small mt-1"><?= htmlspecialchars($trip['estimated_duration']) ?> &bull; <?= $trip['distance_km'] ?> km</div>
                                    </div>
                                </div>

                                <!-- Bus & Timing Details -->
                                <div class="row g-2 mb-3">
                                    <div class="col-sm-6">
                                        <div class="small text-muted">Fleet Bus:</div>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($trip['bus_name']) ?></div>
                                        <small class="badge bg-light text-secondary border"><?= htmlspecialchars($trip['bus_type']) ?></small>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="small text-muted">Timing:</div>
                                        <div class="fw-bold text-dark">
                                            <?= date('h:i A', strtotime($trip['departure_time'])) ?> &rarr; <?= date('h:i A', strtotime($trip['arrival_time'])) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Price & Book Now -->
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <div>
                                    <span class="text-muted small">Fare: </span>
                                    <span class="fs-4 fw-bold text-success">₹<?= number_format((float)$trip['fare'], 2) ?></span>
                                    <span class="badge bg-info-subtle text-info fw-semibold ms-2"><?= $availableSeats ?> Seats Open</span>
                                </div>
                                <div>
                                    <a href="<?= BASE_URL ?>employee/booking.php?routine_id=<?= $trip['id'] ?>" class="btn btn-sm btn-primary rounded-pill px-4 shadow-sm">
                                        <i class="bi bi-ticket-perforated me-1"></i> <?= _e('book_now') ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center text-muted py-5">
                    <i class="bi bi-search fs-1 text-muted opacity-50 mb-2"></i>
                    <h5><?= _e('no_trips_found') ?></h5>
                    <p class="small">Try adjusting your travel date or destination search criteria above.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
