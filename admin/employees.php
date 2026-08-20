<?php
/**
 * Desire Travel - Staff & Employee Management (CRUD)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/lang.php';
require_once __DIR__ . '/../helpers/auth.php';

requireAdmin();

$pageTitle = __('menu_employees');

// Handle Add Employee
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_add_employee'])) {
    $name = trim($_POST['name'] ?? '');
    $username = strtolower(trim($_POST['username'] ?? ''));
    $email = strtolower(trim($_POST['email'] ?? ''));
    $contact = trim($_POST['contact'] ?? '');
    $role = trim($_POST['role'] ?? 'employee');
    $password = $_POST['password'] ?? 'emp123';
    $address = trim($_POST['address'] ?? '');
    $status = trim($_POST['status'] ?? 'active');

    // Generate Employee Code
    $codeNum = rand(100, 999);
    $empCode = ($role === 'admin') ? "DT-ADM-{$codeNum}" : "DT-EMP-{$codeNum}";

    // Duplicate Check
    $dupCheck = $pdo->prepare("SELECT id FROM employees WHERE username = ? OR email = ?");
    $dupCheck->execute([$username, $email]);
    if ($dupCheck->fetch()) {
        $_SESSION['flash_error'] = __('employee_duplicate_error');
    } else {
        try {
            $hashedPass = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO employees (employee_code, name, username, password, email, contact, role, address, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$empCode, $name, $username, $hashedPass, $email, $contact, $role, $address, $status]);
            $_SESSION['flash_success'] = __('employee_added_success');
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Error registering employee: " . $e->getMessage();
        }
    }
    header('Location: ' . BASE_URL . 'admin/employees.php');
    exit;
}

// Handle Edit Employee
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_edit_employee'])) {
    $empId = (int)$_POST['employee_id'];
    $name = trim($_POST['name'] ?? '');
    $username = strtolower(trim($_POST['username'] ?? ''));
    $email = strtolower(trim($_POST['email'] ?? ''));
    $contact = trim($_POST['contact'] ?? '');
    $role = trim($_POST['role'] ?? 'employee');
    $address = trim($_POST['address'] ?? '');
    $status = trim($_POST['status'] ?? 'active');
    $newPassword = $_POST['new_password'] ?? '';

    // Duplicate Check
    $dupCheck = $pdo->prepare("SELECT id FROM employees WHERE (username = ? OR email = ?) AND id != ?");
    $dupCheck->execute([$username, $email, $empId]);
    if ($dupCheck->fetch()) {
        $_SESSION['flash_error'] = __('employee_duplicate_error');
    } else {
        try {
            if (!empty($newPassword)) {
                $hashedPass = password_hash($newPassword, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE employees SET name = ?, username = ?, email = ?, contact = ?, role = ?, address = ?, status = ?, password = ? WHERE id = ?");
                $stmt->execute([$name, $username, $email, $contact, $role, $address, $status, $hashedPass, $empId]);
            } else {
                $stmt = $pdo->prepare("UPDATE employees SET name = ?, username = ?, email = ?, contact = ?, role = ?, address = ?, status = ? WHERE id = ?");
                $stmt->execute([$name, $username, $email, $contact, $role, $address, $status, $empId]);
            }
            $_SESSION['flash_success'] = __('employee_updated_success');
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Error updating employee: " . $e->getMessage();
        }
    }
    header('Location: ' . BASE_URL . 'admin/employees.php');
    exit;
}

// Handle Delete Employee
if (isset($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];
    
    // Prevent deleting self or last admin
    if ($deleteId === (int)$_SESSION['user_id']) {
        $_SESSION['flash_error'] = "You cannot delete your own logged-in admin account!";
    } else {
        try {
            $delStmt = $pdo->prepare("DELETE FROM employees WHERE id = ?");
            $delStmt->execute([$deleteId]);
            $_SESSION['flash_success'] = __('employee_deleted_success');
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Error deleting employee: " . $e->getMessage();
        }
    }
    header('Location: ' . BASE_URL . 'admin/employees.php');
    exit;
}

// Fetch all employees
$employeesList = [];
try {
    $stmt = $pdo->query("
        SELECT e.*, 
               (SELECT COUNT(*) FROM bookings b WHERE b.booked_by_employee_id = e.id) as bookings_issued,
               (SELECT login_time FROM employee_logins el WHERE el.employee_id = e.id ORDER BY el.id DESC LIMIT 1) as last_login
        FROM employees e 
        ORDER BY e.id ASC
    ");
    $employeesList = $stmt->fetchAll();
} catch (Exception $e) {}

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1"><?= _e('menu_employees') ?></h2>
            <p class="text-muted small mb-0">Register staff members, assign roles, manage system permissions</p>
        </div>
        <div>
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                <i class="bi bi-person-plus-fill me-1"></i> <?= _e('add_employee') ?>
            </button>
        </div>
    </div>

    <!-- Employees Directory Table -->
    <div class="dt-card">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-pill px-3 py-2 fs-6"><?= count($employeesList) ?> Active Staff</span>
            </div>
            <div class="d-flex gap-2" style="max-width:320px; width:100%;">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" class="form-control" placeholder="<?= _e('search') ?>" data-table-search="#employeesTable">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle table-custom" id="employeesTable">
                <thead>
                    <tr>
                        <th><?= _e('employee_code') ?></th>
                        <th><?= _e('full_name') ?></th>
                        <th><?= _e('username') ?> &amp; Email</th>
                        <th><?= _e('role') ?></th>
                        <th>Bookings Issued</th>
                        <th><?= _e('login_time') ?></th>
                        <th><?= _e('status') ?></th>
                        <th class="text-end"><?= _e('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($employeesList)): ?>
                        <?php foreach ($employeesList as $emp): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-light text-primary border font-monospace"><?= htmlspecialchars($emp['employee_code']) ?></span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($emp['name']) ?></div>
                                    <small class="text-muted"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($emp['contact']) ?></small>
                                </td>
                                <td>
                                    <div class="fw-semibold text-primary"><?= htmlspecialchars($emp['username']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($emp['email']) ?></small>
                                </td>
                                <td>
                                    <?php if ($emp['role'] === 'admin'): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-shield-lock me-1"></i>Administrator</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="bi bi-person-badge me-1"></i>Booking Staff</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary fw-bold"><?= $emp['bookings_issued'] ?> Tickets</span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= !empty($emp['last_login']) ? date('d M, h:i A', strtotime($emp['last_login'])) : 'Never logged in' ?>
                                    </small>
                                </td>
                                <td>
                                    <?php if ($emp['status'] === 'active'): ?>
                                        <span class="badge-active"><?= _e('active') ?></span>
                                    <?php else: ?>
                                        <span class="badge-inactive"><?= _e('inactive') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary rounded-2 me-1" 
                                            onclick='openEditEmpModal(<?= json_encode($emp, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>)'
                                            title="<?= _e('edit') ?>">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <?php if ($emp['id'] != $_SESSION['user_id']): ?>
                                        <a href="<?= BASE_URL ?>admin/employees.php?delete_id=<?= $emp['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger rounded-2 btn-confirm-delete"
                                           data-confirm-msg="<?= _e('delete_employee_confirm') ?>"
                                           title="<?= _e('delete') ?>">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">No staff records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add Employee -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="<?= BASE_URL ?>admin/employees.php" method="POST">
                <input type="hidden" name="action_add_employee" value="1">
                <div class="modal-header bg-primary text-white py-3 px-4">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-person-plus-fill me-2"></i><?= _e('add_employee') ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('full_name') ?> *</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Anand Sharma" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('username') ?> *</label>
                            <input type="text" name="username" class="form-control text-lowercase" placeholder="e.g. anand1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('email_address') ?> *</label>
                            <input type="email" name="email" class="form-control text-lowercase" placeholder="anand@desiretravel.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('contact_no') ?> *</label>
                            <input type="text" name="contact" class="form-control" placeholder="+91 9825001122" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted"><?= _e('role') ?> *</label>
                            <select name="role" class="form-select" required>
                                <option value="employee" selected>Employee / Booking Clerk</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted"><?= _e('password') ?> *</label>
                            <input type="password" name="password" class="form-control" placeholder="Initial Password" value="emp123" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted"><?= _e('account_status') ?></label>
                            <select name="status" class="form-select">
                                <option value="active" selected><?= _e('active') ?></option>
                                <option value="inactive"><?= _e('inactive') ?></option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted"><?= _e('residential_address') ?></label>
                            <input type="text" name="address" class="form-control" placeholder="Office / Terminal Desk Location">
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

<!-- Modal: Edit Employee -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="<?= BASE_URL ?>admin/employees.php" method="POST">
                <input type="hidden" name="action_edit_employee" value="1">
                <input type="hidden" name="employee_id" id="edit_emp_id">
                
                <div class="modal-header bg-primary text-white py-3 px-4">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square me-2"></i><?= _e('edit_employee') ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('full_name') ?> *</label>
                            <input type="text" name="name" id="edit_emp_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('username') ?> *</label>
                            <input type="text" name="username" id="edit_emp_username" class="form-control text-lowercase" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('email_address') ?> *</label>
                            <input type="email" name="email" id="edit_emp_email" class="form-control text-lowercase" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('contact_no') ?> *</label>
                            <input type="text" name="contact" id="edit_emp_contact" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted"><?= _e('role') ?> *</label>
                            <select name="role" id="edit_emp_role" class="form-select" required>
                                <option value="employee">Employee / Booking Clerk</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Reset Password (Optional)</label>
                            <input type="password" name="new_password" class="form-control" placeholder="Leave blank to keep unchanged">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted"><?= _e('account_status') ?></label>
                            <select name="status" id="edit_emp_status" class="form-select">
                                <option value="active"><?= _e('active') ?></option>
                                <option value="inactive"><?= _e('inactive') ?></option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted"><?= _e('residential_address') ?></label>
                            <input type="text" name="address" id="edit_emp_address" class="form-control">
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
function openEditEmpModal(emp) {
    document.getElementById('edit_emp_id').value = emp.id;
    document.getElementById('edit_emp_name').value = emp.name;
    document.getElementById('edit_emp_username').value = emp.username;
    document.getElementById('edit_emp_email').value = emp.email;
    document.getElementById('edit_emp_contact').value = emp.contact;
    document.getElementById('edit_emp_role').value = emp.role;
    document.getElementById('edit_emp_status').value = emp.status;
    document.getElementById('edit_emp_address').value = emp.address || '';

    new bootstrap.Modal(document.getElementById('editEmployeeModal')).show();
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
