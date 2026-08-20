<?php
/**
 * Desire Travel - Customer / Passenger Registry (CRUD)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/lang.php';
require_once __DIR__ . '/../helpers/auth.php';

requireEmployee();

$pageTitle = __('menu_customers');

// Handle Add Customer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_add_customer'])) {
    $name = trim($_POST['name'] ?? '');
    $fatherName = trim($_POST['father_name'] ?? '');
    $gender = trim($_POST['gender'] ?? 'Male');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $contact = trim($_POST['contact'] ?? '');
    $cnic = trim($_POST['cnic'] ?? '');
    $dob = !empty($_POST['dob']) ? $_POST['dob'] : null;
    $address = trim($_POST['address'] ?? '');

    // Duplicate Check
    $dupCheck = $pdo->prepare("SELECT id FROM customers WHERE email = ? OR cnic = ?");
    $dupCheck->execute([$email, $cnic]);
    if ($dupCheck->fetch()) {
        $_SESSION['flash_error'] = __('customer_duplicate_error');
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO customers (name, father_name, gender, email, contact, cnic, dob, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $fatherName, $gender, $email, $contact, $cnic, $dob, $address]);
            $_SESSION['flash_success'] = __('customer_added_success');
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Error registering passenger: " . $e->getMessage();
        }
    }
    header('Location: ' . BASE_URL . 'employee/customers.php');
    exit;
}

// Handle Edit Customer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_edit_customer'])) {
    $custId = (int)$_POST['customer_id'];
    $name = trim($_POST['name'] ?? '');
    $fatherName = trim($_POST['father_name'] ?? '');
    $gender = trim($_POST['gender'] ?? 'Male');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $contact = trim($_POST['contact'] ?? '');
    $cnic = trim($_POST['cnic'] ?? '');
    $dob = !empty($_POST['dob']) ? $_POST['dob'] : null;
    $address = trim($_POST['address'] ?? '');

    // Duplicate Check
    $dupCheck = $pdo->prepare("SELECT id FROM customers WHERE (email = ? OR cnic = ?) AND id != ?");
    $dupCheck->execute([$email, $cnic, $custId]);
    if ($dupCheck->fetch()) {
        $_SESSION['flash_error'] = __('customer_duplicate_error');
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE customers SET name = ?, father_name = ?, gender = ?, email = ?, contact = ?, cnic = ?, dob = ?, address = ? WHERE id = ?");
            $stmt->execute([$name, $fatherName, $gender, $email, $contact, $cnic, $dob, $address, $custId]);
            $_SESSION['flash_success'] = __('customer_updated_success');
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Error updating passenger: " . $e->getMessage();
        }
    }
    header('Location: ' . BASE_URL . 'employee/customers.php');
    exit;
}

// Handle Delete Customer
if (isset($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];
    try {
        $delStmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
        $delStmt->execute([$deleteId]);
        $_SESSION['flash_success'] = __('customer_deleted_success');
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') {
            $_SESSION['flash_error'] = "Cannot delete this customer because active bookings are attached to their profile.";
        } else {
            $_SESSION['flash_error'] = "Error deleting customer: " . $e->getMessage();
        }
    }
    header('Location: ' . BASE_URL . 'employee/customers.php');
    exit;
}

// Fetch all customers
$customersList = [];
try {
    $stmt = $pdo->query("
        SELECT c.*, 
               (SELECT COUNT(*) FROM bookings b WHERE b.customer_id = c.id) as total_trips_booked,
               (SELECT MAX(booking_date) FROM bookings b WHERE b.customer_id = c.id) as last_booking_date
        FROM customers c 
        ORDER BY c.id DESC
    ");
    $customersList = $stmt->fetchAll();
} catch (Exception $e) {}

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1"><?= _e('menu_customers') ?></h2>
            <p class="text-muted small mb-0">Register and manage verified passenger details, IDs, and booking histories</p>
        </div>
        <div>
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                <i class="bi bi-person-plus-fill me-1"></i> <?= _e('add_customer') ?>
            </button>
        </div>
    </div>

    <!-- Customers Grid Table -->
    <div class="dt-card">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-pill px-3 py-2 fs-6"><?= count($customersList) ?> Registered Passengers</span>
            </div>
            <div class="d-flex gap-2" style="max-width:320px; width:100%;">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" class="form-control" placeholder="Search by name, CNIC, phone..." data-table-search="#customersTable">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle table-custom" id="customersTable">
                <thead>
                    <tr>
                        <th><?= _e('passenger') ?></th>
                        <th><?= _e('father_name') ?></th>
                        <th><?= _e('cnic_id') ?></th>
                        <th><?= _e('contact_no') ?></th>
                        <th><?= _e('gender') ?></th>
                        <th>Total Bookings</th>
                        <th class="text-end"><?= _e('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($customersList)): ?>
                        <?php foreach ($customersList as $cust): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($cust['name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($cust['email']) ?></small>
                                </td>
                                <td>
                                    <div class="small fw-semibold text-secondary"><?= htmlspecialchars($cust['father_name'] ?: 'N/A') ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace"><?= htmlspecialchars($cust['cnic']) ?></span>
                                </td>
                                <td>
                                    <div class="small fw-semibold"><i class="bi bi-telephone-fill text-primary me-1"></i><?= htmlspecialchars($cust['contact']) ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary"><?= htmlspecialchars($cust['gender']) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary fw-bold"><?= $cust['total_trips_booked'] ?> Trips</span>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary rounded-2 me-1" 
                                            onclick='openEditCustomerModal(<?= json_encode($cust, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>)'
                                            title="<?= _e('edit') ?>">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <a href="<?= BASE_URL ?>employee/customers.php?delete_id=<?= $cust['id'] ?>" 
                                       class="btn btn-sm btn-outline-danger rounded-2 btn-confirm-delete"
                                       data-confirm-msg="<?= _e('delete_customer_confirm') ?>"
                                       title="<?= _e('delete') ?>">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">No passenger records found. Click "Register Passenger" to add.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add Customer -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="<?= BASE_URL ?>employee/customers.php" method="POST">
                <input type="hidden" name="action_add_customer" value="1">
                <div class="modal-header bg-primary text-white py-3 px-4">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-person-plus-fill me-2"></i><?= _e('add_customer') ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('full_name') ?> *</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Diya Patel" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('father_name') ?></label>
                            <input type="text" name="father_name" class="form-control" placeholder="e.g. Hasmukh Patel">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted"><?= _e('gender') ?> *</label>
                            <select name="gender" class="form-select" required>
                                <option value="Male"><?= _e('male') ?></option>
                                <option value="Female"><?= _e('female') ?></option>
                                <option value="Other"><?= _e('other') ?></option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted"><?= _e('date_of_birth') ?></label>
                            <input type="date" name="dob" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted"><?= _e('cnic_id') ?> *</label>
                            <input type="text" name="cnic" class="form-control" placeholder="e.g. 2410-8745-9654" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('email_address') ?> *</label>
                            <input type="email" name="email" class="form-control" placeholder="diya.patel@example.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('contact_no') ?> *</label>
                            <input type="text" name="contact" class="form-control" placeholder="+91 9825654321" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted"><?= _e('residential_address') ?></label>
                            <textarea name="address" class="form-control" rows="2" placeholder="Full residential city and street address"></textarea>
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

<!-- Modal: Edit Customer -->
<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="<?= BASE_URL ?>employee/customers.php" method="POST">
                <input type="hidden" name="action_edit_customer" value="1">
                <input type="hidden" name="customer_id" id="edit_cust_id">
                
                <div class="modal-header bg-primary text-white py-3 px-4">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square me-2"></i><?= _e('edit_customer') ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('full_name') ?> *</label>
                            <input type="text" name="name" id="edit_cust_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('father_name') ?></label>
                            <input type="text" name="father_name" id="edit_cust_father_name" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted"><?= _e('gender') ?> *</label>
                            <select name="gender" id="edit_cust_gender" class="form-select" required>
                                <option value="Male"><?= _e('male') ?></option>
                                <option value="Female"><?= _e('female') ?></option>
                                <option value="Other"><?= _e('other') ?></option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted"><?= _e('date_of_birth') ?></label>
                            <input type="date" name="dob" id="edit_cust_dob" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted"><?= _e('cnic_id') ?> *</label>
                            <input type="text" name="cnic" id="edit_cust_cnic" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('email_address') ?> *</label>
                            <input type="email" name="email" id="edit_cust_email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted"><?= _e('contact_no') ?> *</label>
                            <input type="text" name="contact" id="edit_cust_contact" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted"><?= _e('residential_address') ?></label>
                            <textarea name="address" id="edit_cust_address" class="form-control" rows="2"></textarea>
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
function openEditCustomerModal(cust) {
    document.getElementById('edit_cust_id').value = cust.id;
    document.getElementById('edit_cust_name').value = cust.name;
    document.getElementById('edit_cust_father_name').value = cust.father_name || '';
    document.getElementById('edit_cust_gender').value = cust.gender;
    document.getElementById('edit_cust_dob').value = cust.dob || '';
    document.getElementById('edit_cust_cnic').value = cust.cnic;
    document.getElementById('edit_cust_email').value = cust.email;
    document.getElementById('edit_cust_contact').value = cust.contact;
    document.getElementById('edit_cust_address').value = cust.address || '';

    new bootstrap.Modal(document.getElementById('editCustomerModal')).show();
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
