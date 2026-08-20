<?php
/**
 * Desire Travel - Bus Routines & Schedule Management (CRUD)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/lang.php';
require_once __DIR__ . '/../helpers/auth.php';

requireAdmin();

$pageTitle = __('menu_routines');

// Handle Add Routine
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_add_routine'])) {
    $busId = (int)$_POST['bus_id'];
    $routeId = (int)$_POST['route_id'];
    $travelDate = $_POST['travel_date'];
    $departureTime = $_POST['departure_time'];
    $arrivalTime = $_POST['arrival_time'];
    $fare = (float)$_POST['fare'];
    $status = $_POST['status'] ?? 'scheduled';

    // Conflict Check: Check if this bus is already assigned on same travel date with overlapping departure time
    $conflictCheck = $pdo->prepare("SELECT id FROM routines WHERE bus_id = ? AND travel_date = ? AND departure_time = ?");
    $conflictCheck->execute([$busId, $travelDate, $departureTime]);
    if ($conflictCheck->fetch()) {
        $_SESSION['flash_error'] = __('routine_conflict_error');
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO routines (bus_id, route_id, travel_date, departure_time, arrival_time, fare, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$busId, $routeId, $travelDate, $departureTime, $arrivalTime, $fare, $status]);
            $_SESSION['flash_success'] = __('routine_added_success');
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Error scheduling routine: " . $e->getMessage();
        }
    }
    header('Location: ' . BASE_URL . 'admin/routines.php');
    exit;
}

// Handle Edit Routine
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_edit_routine'])) {
    $routineId = (int)$_POST['routine_id'];
    $busId = (int)$_POST['bus_id'];
    $routeId = (int)$_POST['route_id'];
    $travelDate = $_POST['travel_date'];
    $departureTime = $_POST['departure_time'];
    $arrivalTime = $_POST['arrival_time'];
    $fare = (float)$_POST['fare'];
    $status = $_POST['status'] ?? 'scheduled';

    // Conflict check excluding current
    $conflictCheck = $pdo->prepare("SELECT id FROM routines WHERE bus_id = ? AND travel_date = ? AND departure_time = ? AND id != ?");
    $conflictCheck->execute([$busId, $travelDate, $departureTime, $routineId]);
    if ($conflictCheck->fetch()) {
        $_SESSION['flash_error'] = __('routine_conflict_error');
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE routines SET bus_id = ?, route_id = ?, travel_date = ?, departure_time = ?, arrival_time = ?, fare = ?, status = ? WHERE id = ?");
            $stmt->execute([$busId, $routeId, $travelDate, $departureTime, $arrivalTime, $fare, $status, $routineId]);
            $_SESSION['flash_success'] = __('routine_updated_success');
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Error updating routine: " . $e->getMessage();
        }
    }
    header('Location: ' . BASE_URL . 'admin/routines.php');
    exit;
}

// Handle Delete Routine
if (isset($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];
    try {
        $delStmt = $pdo->prepare("DELETE FROM routines WHERE id = ?");
        $delStmt->execute([$deleteId]);
        $_SESSION['flash_success'] = __('routine_deleted_success');
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') {
            $_SESSION['flash_error'] = "Cannot delete this schedule because passenger bookings are already booked for this trip.";
        } else {
            $_SESSION['flash_error'] = "Error deleting routine: " . $e->getMessage();
        }
    }
    header('Location: ' . BASE_URL . 'admin/routines.php');
    exit;
}

// Fetch active buses and routes for dropdowns
$activeBuses = $pdo->query("SELECT id, bus_number, bus_name, bus_type FROM buses WHERE status != 'inactive' ORDER BY bus_name ASC")->fetchAll();
$activeRoutes = $pdo->query("SELECT id, route_name, start_point, end_point, distance_km FROM routes WHERE status = 'active' ORDER BY route_name ASC")->fetchAll();

// Fetch scheduled routines with JOINs
$routinesList = [];
try {
    $stmt = $pdo->query("
        SELECT rt.*, b.bus_number, b.bus_name, b.bus_type, b.capacity,
               r.route_name, r.start_point, r.end_point, r.distance_km, r.estimated_duration,
               (SELECT COUNT(*) FROM bookings bk WHERE bk.routine_id = rt.id AND bk.booking_status = 'Confirmed') as booked_seats_count
        FROM routines rt
        JOIN buses b ON rt.bus_id = b.id
        JOIN routes r ON rt.route_id = r.id
        ORDER BY rt.travel_date DESC, rt.departure_time ASC
    ");
    $routinesList = $stmt->fetchAll();
} catch (Exception $e) {}

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1"><?= _e('menu_routines') ?></h2>
            <p class="text-muted small mb-0">Schedule buses for routes, travel dates, departure &amp; arrival times</p>
        </div>
        <div>
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addRoutineModal">
                <i class="bi bi-calendar-plus me-1"></i> <?= _e('add_routine') ?>
            </button>
        </div>
    </div>

    <!-- Routines Data Grid -->
    <div class="dt-card">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-pill px-3 py-2 fs-6"><?= count($routinesList) ?> Schedules</span>
            </div>
            <div class="d-flex gap-2" style="max-width:320px; width:100%;">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" class="form-control" placeholder="<?= _e('search') ?>" data-table-search="#routinesTable">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle table-custom" id="routinesTable">
                <thead>
                    <tr>
                        <th>Routine ID</th>
                        <th><?= _e('journey') ?></th>
                        <th><?= _e('menu_buses') ?></th>
                        <th><?= _e('travel_date') ?> &amp; <?= _e('time') ?></th>
                        <th><?= _e('base_fare') ?></th>
                        <th>Bookings</th>
                        <th><?= _e('routine_status') ?></th>
                        <th class="text-end"><?= _e('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($routinesList)): ?>
                        <?php foreach ($routinesList as $rt): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace">#RT-<?= $rt['id'] ?></span>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary"><?= htmlspecialchars($rt['route_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($rt['start_point']) ?> &rarr; <?= htmlspecialchars($rt['end_point']) ?> (<?= htmlspecialchars($rt['distance_km']) ?> km)</small>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($rt['bus_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($rt['bus_number']) ?> &bull; <?= htmlspecialchars($rt['bus_type']) ?></small>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><i class="bi bi-calendar-event me-1 text-primary"></i><?= date('D, d M Y', strtotime($rt['travel_date'])) ?></div>
                                    <small class="text-muted">
                                        <?= date('h:i A', strtotime($rt['departure_time'])) ?> &rarr; <?= date('h:i A', strtotime($rt['arrival_time'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="fw-bold text-success fs-6">₹<?= number_format((float)$rt['fare'], 2) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info-emphasis fw-bold">
                                        <?= $rt['booked_seats_count'] ?> / <?= $rt['capacity'] ?> <?= _e('seats') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($rt['status'] === 'scheduled'): ?>
                                        <span class="badge-active">Scheduled</span>
                                    <?php elseif ($rt['status'] === 'completed'): ?>
                                        <span class="badge bg-secondary">Completed</span>
                                    <?php else: ?>
                                        <span class="badge-inactive"><?= htmlspecialchars(ucfirst($rt['status'])) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary rounded-2 me-1" 
                                            onclick='openEditRoutineModal(<?= json_encode($rt, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>)'
                                            title="<?= _e('edit') ?>">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <a href="<?= BASE_URL ?>admin/routines.php?delete_id=<?= $rt['id'] ?>" 
                                       class="btn btn-sm btn-outline-danger rounded-2 btn-confirm-delete"
                                       data-confirm-msg="<?= _e('delete_routine_confirm') ?>"
                                       title="<?= _e('delete') ?>">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">No scheduled routines found. Click "Schedule Bus Routine" to create one.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add Routine -->
<div class="modal fade" id="addRoutineModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="<?= BASE_URL ?>admin/routines.php" method="POST">
                <input type="hidden" name="action_add_routine" value="1">
                <div class="modal-header bg-primary text-white py-3 px-4">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-calendar-plus me-2"></i><?= _e('add_routine') ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('select_route') ?> *</label>
                            <select name="route_id" class="form-select" required>
                                <option value="">-- Choose Route --</option>
                                <?php foreach ($activeRoutes as $ar): ?>
                                    <option value="<?= $ar['id'] ?>"><?= htmlspecialchars($ar['route_name']) ?> (<?= $ar['distance_km'] ?> km)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('select_bus') ?> *</label>
                            <select name="bus_id" class="form-select" required>
                                <option value="">-- Choose Fleet Bus --</option>
                                <?php foreach ($activeBuses as $ab): ?>
                                    <option value="<?= $ab['id'] ?>"><?= htmlspecialchars($ab['bus_name']) ?> (<?= htmlspecialchars($ab['bus_number']) ?> - <?= htmlspecialchars($ab['bus_type']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted"><?= _e('travel_date') ?> *</label>
                            <input type="date" name="travel_date" class="form-control" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted"><?= _e('departure_time') ?> *</label>
                            <input type="time" name="departure_time" class="form-control" value="08:00" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted"><?= _e('arrival_time') ?> *</label>
                            <input type="time" name="arrival_time" class="form-control" value="12:30" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('base_fare') ?> *</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="1" name="fare" class="form-control" placeholder="450.00" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('routine_status') ?></label>
                            <select name="status" class="form-select">
                                <option value="scheduled" selected>Scheduled</option>
                                <option value="departed">Departed</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal"><?= _e('cancel') ?></button>
                    <button type="submit" class="btn btn-primary rounded-3 shadow-sm px-4"><?= _e('save') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Routine -->
<div class="modal fade" id="editRoutineModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="<?= BASE_URL ?>admin/routines.php" method="POST">
                <input type="hidden" name="action_edit_routine" value="1">
                <input type="hidden" name="routine_id" id="edit_routine_id">
                
                <div class="modal-header bg-primary text-white py-3 px-4">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square me-2"></i><?= _e('edit_routine') ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('select_route') ?> *</label>
                            <select name="route_id" id="edit_route_id" class="form-select" required>
                                <?php foreach ($activeRoutes as $ar): ?>
                                    <option value="<?= $ar['id'] ?>"><?= htmlspecialchars($ar['route_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('select_bus') ?> *</label>
                            <select name="bus_id" id="edit_bus_id" class="form-select" required>
                                <?php foreach ($activeBuses as $ab): ?>
                                    <option value="<?= $ab['id'] ?>"><?= htmlspecialchars($ab['bus_name']) ?> (<?= htmlspecialchars($ab['bus_number']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted"><?= _e('travel_date') ?> *</label>
                            <input type="date" name="travel_date" id="edit_travel_date" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted"><?= _e('departure_time') ?> *</label>
                            <input type="time" name="departure_time" id="edit_departure_time" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted"><?= _e('arrival_time') ?> *</label>
                            <input type="time" name="arrival_time" id="edit_arrival_time" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('base_fare') ?> *</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="1" name="fare" id="edit_fare" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('routine_status') ?></label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="scheduled">Scheduled</option>
                                <option value="departed">Departed</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal"><?= _e('cancel') ?></button>
                    <button type="submit" class="btn btn-primary rounded-3 shadow-sm px-4"><?= _e('update') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditRoutineModal(routine) {
    document.getElementById('edit_routine_id').value = routine.id;
    document.getElementById('edit_route_id').value = routine.route_id;
    document.getElementById('edit_bus_id').value = routine.bus_id;
    document.getElementById('edit_travel_date').value = routine.travel_date;
    document.getElementById('edit_departure_time').value = routine.departure_time;
    document.getElementById('edit_arrival_time').value = routine.arrival_time;
    document.getElementById('edit_fare').value = routine.fare;
    document.getElementById('edit_status').value = routine.status;

    new bootstrap.Modal(document.getElementById('editRoutineModal')).show();
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
