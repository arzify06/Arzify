<?php
/**
 * Desire Travel - Travel Routes Management (CRUD)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/lang.php';
require_once __DIR__ . '/../helpers/auth.php';

requireAdmin();

$pageTitle = __('menu_routes');

// Handle Add Route
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_add_route'])) {
    $routeName = trim($_POST['route_name'] ?? '');
    $startPoint = trim($_POST['start_point'] ?? '');
    $endPoint = trim($_POST['end_point'] ?? '');
    $distanceKm = (float)($_POST['distance_km'] ?? 0);
    $estimatedDuration = trim($_POST['estimated_duration'] ?? '4h 00m');
    $status = trim($_POST['status'] ?? 'active');

    // Duplicate Check
    $dupCheck = $pdo->prepare("SELECT id FROM routes WHERE route_name = ?");
    $dupCheck->execute([$routeName]);
    if ($dupCheck->fetch()) {
        $_SESSION['flash_error'] = __('route_duplicate_error');
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO routes (route_name, start_point, end_point, distance_km, estimated_duration, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$routeName, $startPoint, $endPoint, $distanceKm, $estimatedDuration, $status]);
            $_SESSION['flash_success'] = __('route_added_success');
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Error adding route: " . $e->getMessage();
        }
    }
    header('Location: ' . BASE_URL . 'admin/routes.php');
    exit;
}

// Handle Edit Route
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_edit_route'])) {
    $routeId = (int)$_POST['route_id'];
    $routeName = trim($_POST['route_name'] ?? '');
    $startPoint = trim($_POST['start_point'] ?? '');
    $endPoint = trim($_POST['end_point'] ?? '');
    $distanceKm = (float)($_POST['distance_km'] ?? 0);
    $estimatedDuration = trim($_POST['estimated_duration'] ?? '4h 00m');
    $status = trim($_POST['status'] ?? 'active');

    // Duplicate Check excluding current
    $dupCheck = $pdo->prepare("SELECT id FROM routes WHERE route_name = ? AND id != ?");
    $dupCheck->execute([$routeName, $routeId]);
    if ($dupCheck->fetch()) {
        $_SESSION['flash_error'] = __('route_duplicate_error');
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE routes SET route_name = ?, start_point = ?, end_point = ?, distance_km = ?, estimated_duration = ?, status = ? WHERE id = ?");
            $stmt->execute([$routeName, $startPoint, $endPoint, $distanceKm, $estimatedDuration, $status, $routeId]);
            $_SESSION['flash_success'] = __('route_updated_success');
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Error updating route: " . $e->getMessage();
        }
    }
    header('Location: ' . BASE_URL . 'admin/routes.php');
    exit;
}

// Handle Delete Route
if (isset($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];
    try {
        $delStmt = $pdo->prepare("DELETE FROM routes WHERE id = ?");
        $delStmt->execute([$deleteId]);
        $_SESSION['flash_success'] = __('route_deleted_success');
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') {
            $_SESSION['flash_error'] = "Cannot delete route because active routines/schedules are linked to it.";
        } else {
            $_SESSION['flash_error'] = "Error deleting route: " . $e->getMessage();
        }
    }
    header('Location: ' . BASE_URL . 'admin/routes.php');
    exit;
}

// Fetch all routes
$routesList = [];
try {
    $stmt = $pdo->query("SELECT r.*, (SELECT COUNT(*) FROM routines rt WHERE rt.route_id = r.id) as scheduled_count FROM routes r ORDER BY r.id DESC");
    $routesList = $stmt->fetchAll();
} catch (Exception $e) {}

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1"><?= _e('menu_routes') ?></h2>
            <p class="text-muted small mb-0">Manage origin, destination, distance, and duration between travel terminals</p>
        </div>
        <div>
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addRouteModal">
                <i class="bi bi-plus-lg me-1"></i> <?= _e('add_route') ?>
            </button>
        </div>
    </div>

    <!-- Routes Grid Card -->
    <div class="dt-card">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-pill px-3 py-2 fs-6"><?= count($routesList) ?> Registered Routes</span>
            </div>
            <div class="d-flex gap-2" style="max-width:320px; width:100%;">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" class="form-control" placeholder="<?= _e('search') ?>" data-table-search="#routesTable">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle table-custom" id="routesTable">
                <thead>
                    <tr>
                        <th><?= _e('route_name') ?></th>
                        <th><?= _e('start_point') ?></th>
                        <th><?= _e('end_point') ?></th>
                        <th><?= _e('distance_km') ?></th>
                        <th><?= _e('estimated_duration') ?></th>
                        <th><?= _e('route_status') ?></th>
                        <th class="text-end"><?= _e('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($routesList)): ?>
                        <?php foreach ($routesList as $r): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-primary fs-6"><?= htmlspecialchars($r['route_name']) ?></div>
                                    <small class="text-muted"><?= $r['scheduled_count'] ?> Active Schedules</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><i class="bi bi-geo-alt-fill text-danger me-1"></i><?= htmlspecialchars($r['start_point']) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><i class="bi bi-pin-map-fill text-success me-1"></i><?= htmlspecialchars($r['end_point']) ?></span>
                                </td>
                                <td>
                                    <span class="fw-bold text-secondary"><?= htmlspecialchars($r['distance_km']) ?> KM</span>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary"><?= htmlspecialchars($r['estimated_duration']) ?></span>
                                </td>
                                <td>
                                    <?php if ($r['status'] === 'active'): ?>
                                        <span class="badge-active"><?= _e('active') ?></span>
                                    <?php else: ?>
                                        <span class="badge-inactive"><?= _e('inactive') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary rounded-2 me-1" 
                                            onclick='openEditRouteModal(<?= json_encode($r, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>)'
                                            title="<?= _e('edit') ?>">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <a href="<?= BASE_URL ?>admin/routes.php?delete_id=<?= $r['id'] ?>" 
                                       class="btn btn-sm btn-outline-danger rounded-2 btn-confirm-delete"
                                       data-confirm-msg="<?= _e('delete_route_confirm') ?>"
                                       title="<?= _e('delete') ?>">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">No routes found. Click "Add Travel Route" to register one.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add Route -->
<div class="modal fade" id="addRouteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="<?= BASE_URL ?>admin/routes.php" method="POST">
                <input type="hidden" name="action_add_route" value="1">
                <div class="modal-header bg-primary text-white py-3 px-4">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-map me-2"></i><?= _e('add_route') ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted"><?= _e('route_name') ?> *</label>
                            <input type="text" name="route_name" class="form-control" placeholder="e.g. Ahmedabad - Surat Express" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('start_point') ?> *</label>
                            <input type="text" name="start_point" class="form-control" placeholder="e.g. Ahmedabad" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('end_point') ?> *</label>
                            <input type="text" name="end_point" class="form-control" placeholder="e.g. Surat" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted"><?= _e('distance_km') ?> *</label>
                            <input type="number" step="0.1" name="distance_km" class="form-control" placeholder="265.0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted"><?= _e('estimated_duration') ?> *</label>
                            <input type="text" name="estimated_duration" class="form-control" placeholder="e.g. 4h 30m" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted"><?= _e('route_status') ?></label>
                            <select name="status" class="form-select">
                                <option value="active" selected><?= _e('active') ?></option>
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

<!-- Modal: Edit Route -->
<div class="modal fade" id="editRouteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="<?= BASE_URL ?>admin/routes.php" method="POST">
                <input type="hidden" name="action_edit_route" value="1">
                <input type="hidden" name="route_id" id="edit_route_id">
                
                <div class="modal-header bg-primary text-white py-3 px-4">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square me-2"></i><?= _e('edit_route') ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted"><?= _e('route_name') ?> *</label>
                            <input type="text" name="route_name" id="edit_route_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('start_point') ?> *</label>
                            <input type="text" name="start_point" id="edit_start_point" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('end_point') ?> *</label>
                            <input type="text" name="end_point" id="edit_end_point" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted"><?= _e('distance_km') ?> *</label>
                            <input type="number" step="0.1" name="distance_km" id="edit_distance_km" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted"><?= _e('estimated_duration') ?> *</label>
                            <input type="text" name="estimated_duration" id="edit_estimated_duration" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted"><?= _e('route_status') ?></label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="active"><?= _e('active') ?></option>
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
function openEditRouteModal(route) {
    document.getElementById('edit_route_id').value = route.id;
    document.getElementById('edit_route_name').value = route.route_name;
    document.getElementById('edit_start_point').value = route.start_point;
    document.getElementById('edit_end_point').value = route.end_point;
    document.getElementById('edit_distance_km').value = route.distance_km;
    document.getElementById('edit_estimated_duration').value = route.estimated_duration;
    document.getElementById('edit_status').value = route.status;

    new bootstrap.Modal(document.getElementById('editRouteModal')).show();
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
