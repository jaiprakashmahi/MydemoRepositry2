<?php
session_start();
include '../conn.php';

if (!isset($_SESSION['member_logged_in']) || $_SESSION['member_logged_in'] !== true) {
    header("Location: member_login.php?error=access_denied");
    exit();
}

$applicant_name = $_SESSION['applicant_name'] ?? 'Member';
$member_type = $_SESSION['member_type'] ?? '';
$member_id = intval($_SESSION['member_id'] ?? 0);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    header('Content-Type: application/json');

    if ($_POST['action'] == 'add_center') {
        $center_name = trim($_POST['name'] ?? '');
        $owner_name = trim($_POST['owner_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $date_of_create = $_POST['date'] ?? date('Y-m-d');
        $phone = trim($_POST['phone'] ?? '');
        $state_id = intval($_POST['state'] ?? 0);
        $district_id = intval($_POST['district'] ?? 0);
        $block_id = intval($_POST['block'] ?? 0);
        $pincode = intval($_POST['pincode'] ?? 0);
        $reg_amount = floatval($_POST['reg_amount'] ?? 3500);
        $payment_mode = trim($_POST['payment_mode'] ?? '');
        $payment_status = ($payment_mode == "Offline") ? "Pending" : "Paid";
        $username = trim($_POST['username'] ?? '');
        $password_plain = $_POST['password'] ?? '';

        if ($center_name == '' || $owner_name == '' || $email == '' || $phone == '' || $state_id <= 0 || $district_id <= 0 || $block_id <= 0 || $username == '' || $password_plain == '') {
            echo json_encode(['success' => false, 'message' => 'Please fill all required fields.']);
            exit();
        }

        $check = $conn->prepare("SELECT id FROM center_details WHERE username = ? LIMIT 1");
        $check->bind_param("s", $username);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Username already exists!']);
            exit();
        }

        $checkPhone = $conn->prepare("SELECT id FROM center_details WHERE phone = ? LIMIT 1");
        $checkPhone->bind_param("s", $phone);
        $checkPhone->execute();
        if ($checkPhone->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Phone number already exists!']);
            exit();
        }

        $photo_path = '';
        $id_proof_path = '';

        if (!file_exists('../uploads/centers/')) {
            mkdir('../uploads/centers/', 0777, true);
        }

        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $photo_ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $photo_name = time() . '_photo_' . uniqid() . '.' . $photo_ext;
            $photo_path = 'uploads/centers/' . $photo_name;
            move_uploaded_file($_FILES['photo']['tmp_name'], '../' . $photo_path);
        }

        if (isset($_FILES['id_proof']) && $_FILES['id_proof']['error'] == 0) {
            $proof_ext = pathinfo($_FILES['id_proof']['name'], PATHINFO_EXTENSION);
            $proof_name = time() . '_idproof_' . uniqid() . '.' . $proof_ext;
            $id_proof_path = 'uploads/centers/' . $proof_name;
            move_uploaded_file($_FILES['id_proof']['tmp_name'], '../' . $id_proof_path);
        }

        $prefix = "SFCC";
        $code_result = mysqli_query($conn, "SELECT center_code FROM center_details ORDER BY id DESC LIMIT 1");
        if ($code_result && mysqli_num_rows($code_result) > 0) {
            $row = mysqli_fetch_assoc($code_result);
            $number = intval(substr($row['center_code'], strlen($prefix))) + 1;
        } else {
            $number = 1;
        }

        $new_center_code = $prefix . str_pad($number, 4, "0", STR_PAD_LEFT);
        $password = password_hash($password_plain, PASSWORD_BCRYPT);

        $sql = "INSERT INTO center_details 
        (center_code, center_name, owner_name, email, date_of_create, photo, phone, state, district, block, pincode, id_proof, reg_amount, payment_mode, payment_status, username, password, center_status, member_id, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?, 'member')";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssssssiiiisdssssi",
            $new_center_code,
            $center_name,
            $owner_name,
            $email,
            $date_of_create,
            $photo_path,
            $phone,
            $state_id,
            $district_id,
            $block_id,
            $pincode,
            $id_proof_path,
            $reg_amount,
            $payment_mode,
            $payment_status,
            $username,
            $password,
            $member_id
        );

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Center added successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
        }
        exit();
    }

    if ($_POST['action'] == 'get_center') {
        $center_id = intval($_POST['center_id'] ?? 0);

        $stmt = $conn->prepare("SELECT * FROM center_details WHERE id = ? AND member_id = ?");
        $stmt->bind_param("ii", $center_id, $member_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            echo json_encode(['success' => true, 'data' => $row]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Center not found']);
        }
        exit();
    }

    if ($_POST['action'] == 'edit_center') {
        $center_id = intval($_POST['center_id'] ?? 0);
        $center_name = trim($_POST['name'] ?? '');
        $owner_name = trim($_POST['owner_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $state_id = intval($_POST['state'] ?? 0);
        $district_id = intval($_POST['district'] ?? 0);
        $block_id = intval($_POST['block'] ?? 0);
        $pincode = intval($_POST['pincode'] ?? 0);

        $sql = "UPDATE center_details SET 
                center_name = ?, owner_name = ?, email = ?, phone = ?, 
                state = ?, district = ?, block = ?, pincode = ?
                WHERE id = ? AND member_id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssssiiiiii",
            $center_name,
            $owner_name,
            $email,
            $phone,
            $state_id,
            $district_id,
            $block_id,
            $pincode,
            $center_id,
            $member_id
        );

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Center updated successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
        }
        exit();
    }

    if ($_POST['action'] == 'delete_center') {
        $center_id = intval($_POST['center_id'] ?? 0);

        $stmt = $conn->prepare("DELETE FROM center_details WHERE id = ? AND member_id = ?");
        $stmt->bind_param("ii", $center_id, $member_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Center deleted successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
        }
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Centers Management - Sharnay Institute</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Poppins',sans-serif;background:#f4f6fb;overflow-x:hidden}
        .sidebar{position:fixed;left:0;top:0;width:280px;height:100vh;background:linear-gradient(135deg,#182848,#4b6cb7);color:#fff;z-index:1000;overflow-y:auto}
        .sidebar-header{padding:25px;text-align:center;border-bottom:1px solid rgba(255,255,255,.15)}
        .sidebar-header img{max-width:160px}
        .sidebar-menu ul{list-style:none;padding:20px 0;margin:0}
        .sidebar-menu a{display:flex;align-items:center;padding:13px 25px;color:rgba(255,255,255,.85);text-decoration:none;font-weight:500}
        .sidebar-menu a:hover,.sidebar-menu a.active{background:rgba(255,255,255,.14);color:#fff}
        .sidebar-menu i{width:30px;font-size:18px;margin-right:8px}
        .sidebar-footer{position:absolute;bottom:0;left:0;right:0;padding:18px;text-align:center;border-top:1px solid rgba(255,255,255,.15);font-size:12px}
        .main-content{margin-left:280px;min-height:100vh}
        .top-nav{background:#fff;padding:15px 30px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 4px 20px rgba(0,0,0,.06);position:sticky;top:0;z-index:999}
        .sidebar-toggle{border:0;background:transparent;font-size:25px}
        .content-wrapper{padding:30px}
        .card{border:0;border-radius:18px;box-shadow:0 8px 30px rgba(0,0,0,.06)}
        .card-header{background:#fff;border-bottom:1px solid #eee;padding:20px 25px;border-radius:18px 18px 0 0}
        .btn-primary{background:linear-gradient(135deg,#667eea,#764ba2);border:0;border-radius:10px}
        .modal-content{border:0;border-radius:18px}
        .modal-header{background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border-radius:18px 18px 0 0}
        .btn-close{filter:brightness(0) invert(1)}
        .form-control,.form-select{border-radius:10px;padding:10px 14px}
        .required{color:#dc3545}
        .status-badge{padding:6px 12px;border-radius:50px;font-size:12px;font-weight:600}
        .status-active{background:#d1e7dd;color:#0f5132}
        .status-pending{background:#fff3cd;color:#664d03}
        .status-rejected{background:#f8d7da;color:#842029}
        .action-btns .btn{padding:5px 10px;margin:2px}
        @media(max-width:992px){.sidebar{left:-280px}.sidebar.show{left:0}.main-content{margin-left:0}}
    </style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="index.php"><img src="../images/logo.png" alt="Logo"></a>
    </div>
    <nav class="sidebar-menu">
        <ul>
            <li><a href="index.php"><i class="bi bi-speedometer2"></i>Dashboard</a></li>
            <li><a href="students.php"><i class="bi bi-people"></i>Students</a></li>
            <li><a href="create-center.php" class="active"><i class="bi bi-building"></i>Centers</a></li>
            <li><a href="payments.php"><i class="bi bi-credit-card"></i>Payments</a></li>
            <li><a href="reports.php"><i class="bi bi-graph-up"></i>Reports</a></li>
            <li><a href="profile.php"><i class="bi bi-person-circle"></i>Profile</a></li>
        </ul>
    </nav>
    <div class="sidebar-footer">
        <strong>Sharnay Institute</strong><br>
        Education Management System
    </div>
</div>

<div class="main-content">
    <header class="top-nav">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-list"></i></button>
        <strong><i class="bi bi-building me-2"></i>Centers Management</strong>
        <div>
            <span class="fw-semibold"><?php echo htmlspecialchars($applicant_name); ?></span>
            <a href="member_logout.php" class="btn btn-sm btn-outline-danger ms-3">Logout</a>
        </div>
    </header>

    <div class="content-wrapper">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">All Centers</h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCenterModal">
                    <i class="bi bi-plus-circle me-1"></i>Add New Center
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="centersTable">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Center Code</th>
                            <th>Center Name</th>
                            <th>Owner</th>
                            <th>Phone</th>
                            <th>Location</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $sql = "SELECT c.*, 
                                       s.name AS state_name, 
                                       d.name AS district_name,
                                       b.name AS block_name
                                FROM center_details c
                                LEFT JOIN states s ON s.id = CAST(c.state AS UNSIGNED)
                                LEFT JOIN districts d ON d.id = CAST(c.district AS UNSIGNED)
                                LEFT JOIN blocks b ON b.id = CAST(c.block AS UNSIGNED)
                                WHERE c.member_id = ?
                                ORDER BY c.id DESC";
                        $stmtList = $conn->prepare($sql);
                        $stmtList->bind_param("i", $member_id);
                        $stmtList->execute();
                        $result = $stmtList->get_result();

                        while ($row = $result->fetch_assoc()) {
                            $payment_class = ($row['payment_status'] == 'Paid') ? 'status-active' : 'status-pending';
                            $status_class = ($row['center_status'] == 'Pending') ? 'status-pending' : (($row['center_status'] == 'Active') ? 'status-active' : 'status-rejected');
                        ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['center_code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['center_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['owner_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($row['state_name'] ?? $row['state']); ?><br>
                                    <small><?php echo htmlspecialchars($row['district_name'] ?? $row['district']); ?></small><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($row['block_name'] ?? $row['block']); ?></small>
                                </td>
                                <td><span class="status-badge <?php echo $payment_class; ?>"><?php echo htmlspecialchars($row['payment_status']); ?></span></td>
                                <td><span class="status-badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($row['center_status']); ?></span></td>
                                <td class="action-btns">
                                    <button class="btn btn-sm btn-outline-primary edit-center" data-id="<?php echo $row['id']; ?>"><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-sm btn-outline-danger delete-center" data-id="<?php echo $row['id']; ?>" data-name="<?php echo htmlspecialchars($row['center_name']); ?>"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addCenterModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Add New Center</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="addCenterForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Center Name <span class="required">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Owner Name <span class="required">*</span></label>
                            <input type="text" name="owner_name" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="required">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone <span class="required">*</span></label>
                            <input type="text" name="phone" class="form-control" pattern="[0-9]{10}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">State <span class="required">*</span></label>
                            <select id="state-dropdown" name="state" class="form-select" required>
                                <option value="">Select State</option>
                                <?php
                                $states = mysqli_query($conn, "SELECT id, name FROM states ORDER BY name ASC");
                                while ($row = mysqli_fetch_assoc($states)) {
                                    echo '<option value="'.$row['id'].'">'.htmlspecialchars($row['name']).'</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">District <span class="required">*</span></label>
                            <select id="district-dropdown" name="district" class="form-select" required disabled>
                                <option value="">Select State First</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Block <span class="required">*</span></label>
                            <select id="block-dropdown" name="block" class="form-select" required disabled>
                                <option value="">Select District First</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pincode <span class="required">*</span></label>
                            <input type="text" name="pincode" class="form-control" pattern="[0-9]{6}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Photo</label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">ID Proof <span class="required">*</span></label>
                            <input type="file" name="id_proof" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Registration Amount</label>
                            <input type="text" name="reg_amount" class="form-control" value="3500.00" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Payment Mode <span class="required">*</span></label>
                            <select name="payment_mode" class="form-select" required>
                                <option value="">Select Payment Mode</option>
                                <option value="Online">Online</option>
                                <option value="Offline">Offline</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username <span class="required">*</span></label>
                            <input type="text" name="username" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password <span class="required">*</span></label>
                            <input type="password" name="password" class="form-control" minlength="6" required>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Center</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editCenterModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Edit Center</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="editCenterForm">
                <input type="hidden" name="center_id" id="edit_center_id">

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Center Name</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Owner Name</label>
                            <input type="text" name="owner_name" id="edit_owner_name" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" id="edit_phone" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">State</label>
                            <select name="state" id="edit_state" class="form-select" required>
                                <option value="">Select State</option>
                                <?php
                                $states2 = mysqli_query($conn, "SELECT id, name FROM states ORDER BY name ASC");
                                while ($row = mysqli_fetch_assoc($states2)) {
                                    echo '<option value="'.$row['id'].'">'.htmlspecialchars($row['name']).'</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">District</label>
                            <select name="district" id="edit_district" class="form-select" required>
                                <option value="">Select District</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Block</label>
                            <select name="block" id="edit_block" class="form-select" required>
                                <option value="">Select Block</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pincode</label>
                            <input type="text" name="pincode" id="edit_pincode" class="form-control" required>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Center</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    $('#sidebarToggle').on('click', function () {
        $('#sidebar').toggleClass('show');
    });

    $('#centersTable').DataTable({
        pageLength: 10,
        order: [[0, 'desc']],
        language: {
            emptyTable: "No centers found. Click Add New Center to create one."
        }
    });

    $('#state-dropdown').on('change', function () {
        let stateID = $(this).val();

        $('#district-dropdown').html('<option value="">Loading...</option>').prop('disabled', true);
        $('#block-dropdown').html('<option value="">Select District First</option>').prop('disabled', true);

        if (stateID) {
            $.post('fetch_district.php', { state_id: stateID }, function (html) {
                $('#district-dropdown').html(html).prop('disabled', false);
            });
        }
    });

    $('#district-dropdown').on('change', function () {
        let districtID = $(this).val();

        $('#block-dropdown').html('<option value="">Loading...</option>').prop('disabled', true);

        if (districtID) {
            $.post('fetch_block.php', { district_id: districtID }, function (html) {
                $('#block-dropdown').html(html).prop('disabled', false);
            });
        }
    });

    $('#edit_state').on('change', function () {
        let stateID = $(this).val();

        $('#edit_district').html('<option value="">Loading...</option>');
        $('#edit_block').html('<option value="">Select District First</option>');

        if (stateID) {
            $.post('fetch_district.php', { state_id: stateID }, function (html) {
                $('#edit_district').html(html);
            });
        }
    });

    $('#edit_district').on('change', function () {
        let districtID = $(this).val();

        $('#edit_block').html('<option value="">Loading...</option>');

        if (districtID) {
            $.post('fetch_block.php', { district_id: districtID }, function (html) {
                $('#edit_block').html(html);
            });
        }
    });

    $('#addCenterForm').on('submit', function (e) {
        e.preventDefault();

        let formData = new FormData(this);
        formData.append('action', 'add_center');

        Swal.fire({ title: 'Saving...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        $.ajax({
            url: 'create-center.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (res) {
                Swal.close();
                if (res.success) {
                    Swal.fire('Success', res.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            },
            error: function (xhr) {
                Swal.close();
                console.log(xhr.responseText);
                Swal.fire('Error', 'PHP error occurred. Check console.', 'error');
            }
        });
    });

    $(document).on('click', '.edit-center', function () {
        let centerID = $(this).data('id');

        $.ajax({
            url: 'create-center.php',
            type: 'POST',
            data: { action: 'get_center', center_id: centerID },
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    let d = res.data;

                    $('#edit_center_id').val(d.id);
                    $('#edit_name').val(d.center_name);
                    $('#edit_owner_name').val(d.owner_name);
                    $('#edit_email').val(d.email);
                    $('#edit_phone').val(d.phone);
                    $('#edit_pincode').val(d.pincode);

                    $('#edit_state').val(d.state);

                    $.post('fetch_district.php', { state_id: d.state }, function (districtHtml) {
                        $('#edit_district').html(districtHtml);
                        $('#edit_district').val(d.district);

                        $.post('fetch_block.php', { district_id: d.district }, function (blockHtml) {
                            $('#edit_block').html(blockHtml);
                            $('#edit_block').val(d.block);
                            $('#editCenterModal').modal('show');
                        });
                    });
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            }
        });
    });

    $('#editCenterForm').on('submit', function (e) {
        e.preventDefault();

        let formData = $(this).serialize() + '&action=edit_center';

        $.ajax({
            url: 'create-center.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    Swal.fire('Success', res.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            }
        });
    });

    $(document).on('click', '.delete-center', function () {
        let centerID = $(this).data('id');
        let centerName = $(this).data('name');

        Swal.fire({
            title: 'Are you sure?',
            text: 'Delete ' + centerName + '?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'create-center.php',
                    type: 'POST',
                    data: { action: 'delete_center', center_id: centerID },
                    dataType: 'json',
                    success: function (res) {
                        if (res.success) {
                            Swal.fire('Deleted', res.message, 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    }
                });
            }
        });
    });
});
</script>

</body>
</html>