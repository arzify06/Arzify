<?php
/**
 * Desire Travel - Bus Fleet Management (CRUD)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/lang.php';
require_once __DIR__ . '/../helpers/auth.php';

requireAdmin();

$pageTitle = __('menu_buses');

// Handle Bus Insert
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_add_bus'])) {
    $busNumber = strtoupper(trim($_POST['bus_number'] ?? ''));
    $busName = trim($_POST['bus_name'] ?? '');
    $busType = trim($_POST['bus_type'] ?? 'AC Seater (2x2)');
    $capacity = (int)($_POST['capacity'] ?? 40);
    $driverName = trim($_POST['driver_name'] ?? '');
    $driverContact = trim($_POST['driver_contact'] ?? '');
    $status = trim($_POST['status'] ?? 'active');

    // Duplicate Check
    $dupCheck = $pdo->prepare("SELECT id FROM buses WHERE bus_number = ?");
    $dupCheck->execute([$busNumber]);
    if ($dupCheck->fetch()) {
        $_SESSION['flash_error'] = __('bus_duplicate_error');
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO buses (bus_number, bus_name, bus_type, capacity, driver_name, driver_contact, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$busNumber, $busName, $busType, $capacity, $driverName, $driverContact, $status]);
            $_SESSION['flash_success'] = __('bus_added_success');
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Error adding bus: " . $e->getMessage();
        }
    }
    header('Location: ' . BASE_URL . 'admin/buses.php');
    exit;
}

// Handle Bus Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_edit_bus'])) {
    $busId = (int)$_POST['bus_id'];
    $busNumber = strtoupper(trim($_POST['bus_number'] ?? ''));
    $busName = trim($_POST['bus_name'] ?? '');
    $busType = trim($_POST['bus_type'] ?? 'AC Seater (2x2)');
    $capacity = (int)($_POST['capacity'] ?? 40);
    $driverName = trim($_POST['driver_name'] ?? '');
    $driverContact = trim($_POST['driver_contact'] ?? '');
    $status = trim($_POST['status'] ?? 'active');

    // Duplicate Check excluding current
    $dupCheck = $pdo->prepare("SELECT id FROM buses WHERE bus_number = ? AND id != ?");
    $dupCheck->execute([$busNumber, $busId]);
    if ($dupCheck->fetch()) {
        $_SESSION['flash_error'] = __('bus_duplicate_error');
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE buses SET bus_number = ?, bus_name = ?, bus_type = ?, capacity = ?, driver_name = ?, driver_contact = ?, status = ? WHERE id = ?");
            $stmt->execute([$busNumber, $busName, $busType, $capacity, $driverName, $driverContact, $status, $busId]);
            $_SESSION['flash_success'] = __('bus_updated_success');
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Error updating bus: " . $e->getMessage();
        }
    }
    header('Location: ' . BASE_URL . 'admin/buses.php');
    exit;
}

// Handle Bus Delete
if (isset($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];
    try {
        $delStmt = $pdo->prepare("DELETE FROM buses WHERE id = ?");
        $delStmt->execute([$deleteId]);
        $_SESSION['flash_success'] = __('bus_deleted_success');
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') {
            $_SESSION['flash_error'] = "Cannot delete this bus because active trips or schedules are linked to it.";
        } else {
            $_SESSION['flash_error'] = "Error deleting bus: " . $e->getMessage();
        }
    }
    header('Location: ' . BASE_URL . 'admin/buses.php');
    exit;
}

// Fetch all buses
$busesList = [];
try {
    $stmt = $pdo->query("SELECT b.*, (SELECT COUNT(*) FROM routines rt WHERE rt.bus_id = b.id) as scheduled_trips FROM buses b ORDER BY b.id DESC");
    $busesList = $stmt->fetchAll();
} catch (Exception $e) {}

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1"><?= _e('menu_buses') ?></h2>
            <p class="text-muted small mb-0">Register, update, and manage the Desire Travel active bus fleet</p>
        </div>
        <div>
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addBusModal">
                <i class="bi bi-plus-lg me-1"></i> <?= _e('add_bus') ?>
            </button>
        </div>
    </div>

    <!-- Bus Fleet Grid -->
    <div class="dt-card">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-pill px-3 py-2 fs-6"><?= count($busesList) ?> Registered Buses</span>
            </div>
            <div class="d-flex gap-2" style="max-width:320px; width:100%;">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" class="form-control" placeholder="<?= _e('search') ?>" data-table-search="#busesTable">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle table-custom" id="busesTable">
                <thead>
                    <tr>
                        <th><?= _e('bus_number') ?></th>
                        <th><?= _e('bus_name') ?></th>
                        <th><?= _e('bus_type') ?></th>
                        <th><?= _e('capacity') ?></th>
                        <th><?= _e('driver_name') ?></th>
                        <th><?= _e('bus_status') ?></th>
                        <th class="text-end"><?= _e('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($busesList)): ?>
                        <?php foreach ($busesList as $b): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-primary font-monospace fs-6"><?= htmlspecialchars($b['bus_number']) ?></div>
                                    <small class="text-muted"><?= $b['scheduled_trips'] ?> Trips Scheduled</small>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($b['bus_name']) ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($b['bus_type']) ?></span>
                                </td>
                                <td>
                                    <span class="fw-bold text-secondary"><?= htmlspecialchars($b['capacity']) ?> <?= _e('seats') ?></span>
                                </td>
                                <td>
                                    <div class="fw-semibold small"><?= htmlspecialchars($b['driver_name']) ?></div>
                                    <small class="text-muted"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($b['driver_contact']) ?></small>
                                </td>
                                <td>
                                    <?php if ($b['status'] === 'active'): ?>
                                        <span class="badge-active"><?= _e('active') ?></span>
                                    <?php elseif ($b['status'] === 'maintenance'): ?>
                                        <span class="badge-maintenance"><?= _e('maintenance') ?></span>
                                    <?php else: ?>
                                        <span class="badge-inactive"><?= _e('inactive') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary rounded-2 me-1" 
                                            onclick='openEditBusModal(<?= json_encode($b, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>)'
                                            title="<?= _e('edit') ?>">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <a href="<?= BASE_URL ?>admin/buses.php?delete_id=<?= $b['id'] ?>" 
                                       class="btn btn-sm btn-outline-danger rounded-2 btn-confirm-delete"
                                       data-confirm-msg="<?= _e('delete_bus_confirm') ?>"
                                       title="<?= _e('delete') ?>">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">No buses found in fleet. Click "Register New Bus" to add one.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add New Bus -->
<div class="modal fade" id="addBusModal" tabindex="-1" aria-labelledby="addBusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="<?= BASE_URL ?>admin/buses.php" method="POST">
                <input type="hidden" name="action_add_bus" value="1">
                <div class="modal-header bg-primary text-white py-3 px-4">
                    <h5 class="modal-title fw-bold" id="addBusModalLabel">
                        <i class="bi bi-bus-front me-2"></i><?= _e('add_bus') ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('bus_number') ?> *</label>
                            <input type="text" name="bus_number" class="form-control text-uppercase" placeholder="e.g. GJ-01-DT-1001" required>
                            <div class="form-text">Must be a unique vehicle registration code.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('bus_name') ?> *</label>
                            <input type="text" name="bus_name" class="form-control" placeholder="e.g. Desire Royal Express" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('bus_type') ?> *</label>
                            <select name="bus_type" class="form-select" required>
                                <option value="AC Sleeper">AC Sleeper</option>
                                <option value="Non-AC Sleeper">Non-AC Sleeper</option>
                                <option value="AC Seater (2x2)" selected>AC Seater (2x2)</option>
                                <option value="Non-AC Seater">Non-AC Seater</option>
                                <option value="Luxury Volvo Multi-Axle">Luxury Volvo Multi-Axle</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('capacity') ?> (<?= _e('seats') ?>) *</label>
                            <input type="number" name="capacity" class="form-control" min="10" max="60" value="40" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('driver_name') ?> *</label>
                            <input type="text" name="driver_name" class="form-control" placeholder="e.g. Ramesh Bhai" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('driver_contact') ?> *</label>
                            <input type="text" name="driver_contact" class="form-control" placeholder="+91 9898011223" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('bus_status') ?></label>
                            <select name="status" class="form-select">
                                <option value="active" selected><?= _e('active') ?></option>
                                <option value="maintenance"><?= _e('maintenance') ?></option>
                                <option value="inactive"><?= _e('inactive') ?></option>
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

<!-- Modal: Edit Bus -->
<div class="modal fade" id="editBusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="<?= BASE_URL ?>admin/buses.php" method="POST">
                <input type="hidden" name="action_edit_bus" value="1">
                <input type="hidden" name="bus_id" id="edit_bus_id">
                
                <div class="modal-header bg-primary text-white py-3 px-4">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square me-2"></i><?= _e('edit_bus') ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('bus_number') ?> *</label>
                            <input type="text" name="bus_number" id="edit_bus_number" class="form-control text-uppercase" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('bus_name') ?> *</label>
                            <input type="text" name="bus_name" id="edit_bus_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('bus_type') ?> *</label>
                            <select name="bus_type" id="edit_bus_type" class="form-select" required>
                                <option value="AC Sleeper">AC Sleeper</option>
                                <option value="Non-AC Sleeper">Non-AC Sleeper</option>
                                <option value="AC Seater (2x2)">AC Seater (2x2)</option>
                                <option value="Non-AC Seater">Non-AC Seater</option>
                                <option value="Luxury Volvo Multi-Axle">Luxury Volvo Multi-Axle</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('capacity') ?> *</label>
                            <input type="number" name="capacity" id="edit_capacity" class="form-control" min="10" max="60" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('driver_name') ?> *</label>
                            <input type="text" name="driver_name" id="edit_driver_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('driver_contact') ?> *</label>
                            <input type="text" name="driver_contact" id="edit_driver_contact" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('bus_status') ?></label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="active"><?= _e('active') ?></option>
                                <option value="maintenance"><?= _e('maintenance') ?></option>
                                <option value="inactive"><?= _e('inactive') ?></option>
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
function openEditBusModal(bus) {
    document.getElementById('edit_bus_id').value = bus.id;
    document.getElementById('edit_bus_number').value = bus.bus_number;
    document.getElementById('edit_bus_name').value = bus.bus_name;
    document.getElementById('edit_bus_type').value = bus.bus_type;
    document.getElementById('edit_capacity').value = bus.capacity;
    document.getElementById('edit_driver_name').value = bus.driver_name;
    document.getElementById('edit_driver_contact').value = bus.driver_contact;
    document.getElementById('edit_status').value = bus.status;

    new bootstrap.Modal(document.getElementById('editBusModal')).show();
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
