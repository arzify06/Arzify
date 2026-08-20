<?php
/**
 * Desire Travel - Fare List Structure & Distance Calculator
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/lang.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/fare_calculator.php';

requireEmployee();

$pageTitle = __('menu_price_list');

// Handle Interactive Distance Calculation
$calcDistance = (float)($_GET['distance'] ?? 0);
$calcBusType = $_GET['bus_type'] ?? 'AC Seater (2x2)';
$calcResult = null;

if ($calcDistance > 0) {
    $calcResult = calculateDistanceFare($calcDistance, $calcBusType);
}

// Fetch all system routes with base distance & computed base fares
$routesList = [];
try {
    $rStmt = $pdo->query("SELECT * FROM routes WHERE status = 'active' ORDER BY distance_km ASC");
    $routesList = $rStmt->fetchAll();
} catch (Exception $e) {}

// Fetch dynamic pricing rules from database
$pricingRules = [];
try {
    $prStmt = $pdo->query("SELECT * FROM pricing_rules ORDER BY id ASC");
    $pricingRules = $prStmt->fetchAll();
} catch (Exception $e) {}

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1"><?= _e('price_list_calculator') ?></h2>
            <p class="text-muted small mb-0">System tiered distance pricing rules, route fare tables, and interactive calculator</p>
        </div>
    </div>

    <!-- Distance Pricing Rules & Interactive Calculator Row -->
    <div class="row g-4 mb-4">
        <!-- Pricing Rules Card -->
        <div class="col-lg-5">
            <div class="dt-card h-100">
                <h5 class="fw-bold text-dark mb-3">
                    <i class="bi bi-diagram-3-fill text-primary me-2"></i><?= _e('tier_pricing_rules') ?>
                </h5>
                <p class="text-muted small mb-3">
                    Standard distance rate structure applied across all Desire Travel fleet journeys:
                </p>

                <div class="list-group list-group-flush mb-3">
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3 bg-light rounded-3 mb-2 border">
                        <div>
                            <div class="fw-bold text-dark">Tier 1: Initial Distance (0 &ndash; 5 km)</div>
                            <small class="text-muted">Flat base boarding fare</small>
                        </div>
                        <span class="badge bg-primary fs-6 px-3 py-2">₹5.00 Flat</span>
                    </div>

                    <div class="list-group-item d-flex justify-content-between align-items-center py-3 bg-light rounded-3 mb-2 border">
                        <div>
                            <div class="fw-bold text-dark">Tier 2: Medium Distance (5 &ndash; 15 km)</div>
                            <small class="text-muted">Incremental rate per km</small>
                        </div>
                        <span class="badge bg-success fs-6 px-3 py-2">+₹2.00 / km</span>
                    </div>

                    <div class="list-group-item d-flex justify-content-between align-items-center py-3 bg-light rounded-3 mb-2 border">
                        <div>
                            <div class="fw-bold text-dark">Tier 3: Long Distance (Beyond 15 km)</div>
                            <small class="text-muted">Intercity highway rate per km</small>
                        </div>
                        <span class="badge bg-info fs-6 px-3 py-2">+₹1.00 / km</span>
                    </div>

                    <div class="list-group-item d-flex justify-content-between align-items-center py-3 bg-light rounded-3 border">
                        <div>
                            <div class="fw-bold text-dark">Luxury / AC Multiplier</div>
                            <small class="text-muted">For Volvo Multi-Axle &amp; Sleeper Coaches</small>
                        </div>
                        <span class="badge bg-warning text-dark fs-6 px-3 py-2">1.25x (25% Extra)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interactive Calculator Widget -->
        <div class="col-lg-7">
            <div class="dt-card h-100" style="background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);">
                <h5 class="fw-bold text-dark mb-3">
                    <i class="bi bi-calculator-fill text-success me-2"></i>Live Dynamic Fare Calculator
                </h5>
                <p class="text-muted small mb-3">Enter any distance in kilometers to test instant fare calculation:</p>

                <form method="GET" action="<?= BASE_URL ?>employee/price_list.php" class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted"><?= _e('distance_km') ?> *</label>
                        <div class="input-group">
                            <input type="number" step="0.5" min="1" name="distance" class="form-control form-control-lg" placeholder="<?= _e('calc_distance_placeholder') ?>" value="<?= $calcDistance > 0 ? $calcDistance : '' ?>" required>
                            <span class="input-group-text bg-light fw-bold">KM</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted"><?= _e('bus_type') ?> *</label>
                        <select name="bus_type" class="form-select form-select-lg">
                            <option value="AC Seater (2x2)" <?= $calcBusType === 'AC Seater (2x2)' ? 'selected' : '' ?>>Standard Seater (1.0x)</option>
                            <option value="Luxury Volvo Multi-Axle" <?= $calcBusType === 'Luxury Volvo Multi-Axle' ? 'selected' : '' ?>>Luxury Volvo / AC Sleeper (1.25x)</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary w-100 py-2 fs-6 shadow-sm">
                            <i class="bi bi-lightning-charge me-1"></i> <?= _e('calculate_fare_btn') ?>
                        </button>
                    </div>
                </form>

                <?php if ($calcResult): ?>
                    <!-- Calculated Result Details -->
                    <div class="p-3 bg-white rounded-3 border shadow-sm">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-dark mb-0"><?= _e('fare_breakdown') ?> (<?= $calcDistance ?> KM)</h6>
                            <span class="fs-4 fw-bold text-success">₹<?= number_format($calcResult['total_fare'], 2) ?></span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0 small">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tier Range</th>
                                        <th>Applicable Distance</th>
                                        <th>Rate</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($calcResult['breakdown'] as $b): ?>
                                        <tr>
                                            <td class="fw-semibold"><?= $b['tier'] ?></td>
                                            <td><?= $b['distance'] ?></td>
                                            <td><?= $b['rate'] ?></td>
                                            <td class="fw-bold">₹<?= number_format($b['amount'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">Base Fare Sum:</td>
                                        <td class="fw-bold">₹<?= number_format($calcResult['base_fare'], 2) ?></td>
                                    </tr>
                                    <?php if ($calcResult['multiplier'] > 1.0): ?>
                                        <tr>
                                            <td colspan="3" class="text-end fw-bold text-primary">Luxury Multiplier (1.25x):</td>
                                            <td class="fw-bold text-primary">₹<?= number_format($calcResult['total_fare'], 2) ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Route-by-Route Official Fare List Table -->
    <div class="dt-card">
        <h5 class="fw-bold mb-3"><i class="bi bi-table text-primary me-2"></i>Official Fare Chart for Active Routes</h5>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle table-custom" id="fareTable">
                <thead>
                    <tr>
                        <th><?= _e('route_name') ?></th>
                        <th><?= _e('start_point') ?> &rarr; <?= _e('end_point') ?></th>
                        <th><?= _e('distance_km') ?></th>
                        <th><?= _e('estimated_duration') ?></th>
                        <th>Standard Fare (Calculated)</th>
                        <th>Luxury Fare (Calculated)</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($routesList)): ?>
                        <?php foreach ($routesList as $rt): ?>
                            <?php
                            $calcStd = calculateDistanceFare((float)$rt['distance_km'], 'AC Seater (2x2)');
                            $calcLux = calculateDistanceFare((float)$rt['distance_km'], 'Luxury Volvo Multi-Axle');
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-primary fs-6"><?= htmlspecialchars($rt['route_name']) ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($rt['start_point']) ?> &rarr; <?= htmlspecialchars($rt['end_point']) ?></span>
                                </td>
                                <td>
                                    <span class="fw-bold text-secondary"><?= htmlspecialchars($rt['distance_km']) ?> KM</span>
                                </td>
                                <td>
                                    <small class="text-muted"><?= htmlspecialchars($rt['estimated_duration']) ?></small>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark">₹<?= number_format($calcStd['total_fare'], 2) ?></span>
                                </td>
                                <td>
                                    <span class="fw-bold text-success">₹<?= number_format($calcLux['total_fare'], 2) ?></span>
                                </td>
                                <td class="text-end">
                                    <a href="<?= BASE_URL ?>employee/inquiry.php?source=<?= urlencode($rt['start_point']) ?>&destination=<?= urlencode($rt['end_point']) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        <i class="bi bi-search me-1"></i> Check Trips
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No active travel routes found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
