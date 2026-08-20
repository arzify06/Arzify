<?php
/**
 * Desire Travel - Employee Login Audit & Telemetry Logs
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/lang.php';
require_once __DIR__ . '/../helpers/auth.php';

requireAdmin();

$pageTitle = __('menu_login_logs');

// Handle Pruning Outdated Logs
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_clear_logs'])) {
    $days = (int)($_POST['older_than_days'] ?? 30);
    try {
        $delStmt = $pdo->prepare("DELETE FROM employee_logins WHERE login_time < NOW() - INTERVAL ? DAY");
        $delStmt->execute([$days]);
        $_SESSION['flash_success'] = __('logs_cleared_success');
    } catch (Exception $e) {
        $_SESSION['flash_error'] = "Error clearing logs: " . $e->getMessage();
    }
    header('Location: ' . BASE_URL . 'admin/login_logs.php');
    exit;
}

// Fetch all login logs with employee details
$logsList = [];
try {
    $stmt = $pdo->query("
        SELECT el.*, e.name as employee_name, e.employee_code, e.email
        FROM employee_logins el
        LEFT JOIN employees e ON el.employee_id = e.id
        ORDER BY el.id DESC LIMIT 150
    ");
    $logsList = $stmt->fetchAll();
} catch (Exception $e) {}

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1"><?= _e('login_audit_logs') ?></h2>
            <p class="text-muted small mb-0">Track employee system access timestamps, IP addresses, session durations &amp; security telemetry</p>
        </div>
        <div>
            <button class="btn btn-outline-danger btn-sm bg-white shadow-sm" data-bs-toggle="modal" data-bs-target="#clearLogsModal">
                <i class="bi bi-trash3 me-1"></i> <?= _e('clear_old_logs') ?>
            </button>
        </div>
    </div>

    <!-- Audit Logs Table Card -->
    <div class="dt-card">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-pill px-3 py-2 fs-6"><?= count($logsList) ?> Telemetry Events</span>
            </div>
            <div class="d-flex gap-2" style="max-width:320px; width:100%;">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" class="form-control" placeholder="<?= _e('search') ?>" data-table-search="#auditTable">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle table-custom" id="auditTable">
                <thead>
                    <tr>
                        <th>Log ID</th>
                        <th><?= _e('employee') ?></th>
                        <th><?= _e('role') ?></th>
                        <th><?= _e('login_time') ?></th>
                        <th><?= _e('logout_time') ?></th>
                        <th><?= _e('session_duration') ?></th>
                        <th><?= _e('ip_address') ?></th>
                        <th><?= _e('status') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logsList)): ?>
                        <?php foreach ($logsList as $l): ?>
                            <?php
                            $durationText = 'Active / In Session';
                            if (!empty($l['logout_time']) && !empty($l['login_time'])) {
                                $t1 = strtotime($l['login_time']);
                                $t2 = strtotime($l['logout_time']);
                                $diff = abs($t2 - $t1);
                                $hours = floor($diff / 3600);
                                $mins = floor(($diff % 3600) / 60);
                                $durationText = "{$hours}h {$mins}m";
                            } elseif ($l['status'] === 'failed') {
                                $durationText = 'N/A (Failed)';
                            }
                            ?>
                            <tr>
                                <td>
                                    <span class="badge bg-light text-muted border font-monospace">#LOG-<?= $l['id'] ?></span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($l['employee_name'] ?? $l['username']) ?></div>
                                    <small class="text-muted font-monospace"><?= htmlspecialchars($l['username']) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border"><?= htmlspecialchars($l['role']) ?></span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><i class="bi bi-box-arrow-in-right text-success me-1"></i><?= date('d M Y, h:i:s A', strtotime($l['login_time'])) ?></div>
                                </td>
                                <td>
                                    <?php if (!empty($l['logout_time'])): ?>
                                        <div class="text-muted"><i class="bi bi-box-arrow-right text-danger me-1"></i><?= date('d M Y, h:i:s A', strtotime($l['logout_time'])) ?></div>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success">Logged In (Active)</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= $durationText ?></span>
                                </td>
                                <td>
                                    <span class="font-monospace small"><?= htmlspecialchars($l['ip_address']) ?></span>
                                </td>
                                <td>
                                    <?php if ($l['status'] === 'logged_in'): ?>
                                        <span class="badge-active">Online</span>
                                    <?php elseif ($l['status'] === 'logged_out'): ?>
                                        <span class="badge bg-secondary-subtle text-secondary">Logged Out</span>
                                    <?php else: ?>
                                        <span class="badge-inactive">Failed Attempt</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">No employee activity records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Clear Old Logs -->
<div class="modal fade" id="clearLogsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="<?= BASE_URL ?>admin/login_logs.php" method="POST">
                <input type="hidden" name="action_clear_logs" value="1">
                <div class="modal-header bg-danger text-white py-3 px-4">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-trash3 me-2"></i><?= _e('clear_old_logs') ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted mb-3">Select the age threshold to delete past login/logout logs to maintain optimum database performance:</p>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Delete Logs Older Than:</label>
                        <select name="older_than_days" class="form-select">
                            <option value="7">Older than 7 Days</option>
                            <option value="30" selected>Older than 30 Days</option>
                            <option value="90">Older than 90 Days</option>
                            <option value="0">Clear All Past Records</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal"><?= _e('cancel') ?></button>
                    <button type="submit" class="btn btn-danger rounded-3 shadow-sm px-4">Prune Logs Now</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
