<?php
include 'conn.php';
session_start();

// Check if ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "No center ID provided!";
    header("Location: CenterList.php");
    exit();
}

$id = $_GET['id'];

// Get center details
$stmt = $conn->prepare("SELECT * FROM center_details WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$center = $result->fetch_assoc();

if (!$center) {
    $_SESSION['error_message'] = "Center not found!";
    header("Location: CenterList.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get all form data
    $center_name    = $_POST['center_name'] ?? '';
    $owner_name     = $_POST['owner_name'] ?? '';
    $email          = $_POST['email'] ?? '';
    $date_of_create = $_POST['date_of_create'] ?? '';
    $phone          = $_POST['phone'] ?? '';
    $state          = $_POST['state'] ?? '';
    $district       = $_POST['district'] ?? '';
    $block          = $_POST['block'] ?? '';
    $pincode        = $_POST['pincode'] ?? '';
    $username       = $_POST['username'] ?? '';
    $reg_amount     = $_POST['reg_amount'] ?? 0;
    $payment_mode   = $_POST['payment_mode'] ?? '';
    $payment_status = $_POST['payment_status'] ?? '';
    $center_status  = $_POST['center_status'] ?? '';

    // Base SQL query
    $sql = "UPDATE center_details SET 
            center_name = ?, 
            owner_name = ?, 
            email = ?, 
            date_of_create = ?, 
            phone = ?, 
            state = ?, 
            district = ?, 
            block = ?, 
            pincode = ?, 
            username = ?, 
            reg_amount = ?,
            payment_mode = ?,
            payment_status = ?,
            center_status = ?";

    $params = [$center_name, $owner_name, $email, $date_of_create, $phone, $state, $district, $block, $pincode, $username, $reg_amount, $payment_mode, $payment_status, $center_status];
    $types = "ssssssssssssss"; // 14 strings

    // Handle password update (only if provided)
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $sql .= ", password = ?";
        $params[] = $password;
        $types .= "s";
    }

    // Handle photo upload
    if (!empty($_FILES['photo']['name'])) {
        $photo = time() . '_' . basename($_FILES['photo']['name']);
        $target = "uploads/" . $photo;
        
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
            $sql .= ", photo = ?";
            $params[] = $photo;
            $types .= "s";
        }
    }

    // Handle ID proof upload
    if (!empty($_FILES['id_proof']['name'])) {
        $id_proof = time() . '_proof_' . basename($_FILES['id_proof']['name']);
        $target = "uploads/" . $id_proof;
        
        if (move_uploaded_file($_FILES['id_proof']['tmp_name'], $target)) {
            $sql .= ", id_proof = ?";
            $params[] = $id_proof;
            $types .= "s";
        }
    }

    // Add WHERE clause
    $sql .= " WHERE id = ?";
    $params[] = $id;
    $types .= "i";

    // Prepare and execute
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Center updated successfully!";
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['error_message'] = "Error updating center: " . $stmt->error;
        $_SESSION['message_type'] = 'error';
    }

    $stmt->close();
    $conn->close();
    
    header("Location: edit_center.php?id=" . $id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="Sharnay Institute">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>Edit Center - Sharnay Institute Dashboard</title>

    <!-- FAVICONS ICON -->
    <link rel="shortcut icon" type="image/png" href="images/favicon.png">
    <link href="vendor/wow-master/css/libs/animate.css" rel="stylesheet">
    <link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="vendor/bootstrap-datepicker-master/css/bootstrap-datepicker.min.css" rel="stylesheet">
    
    <!-- Style css -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/admin-standard.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        .required {
            color: red;
            margin-left: 3px;
        }
        
        .preview-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
            border: 3px solid #667eea;
            margin-top: 10px;
        }
        
        .password-field {
            position: relative;
        }
        
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #667eea;
            font-size: 18px;
        }
        
        .alert-message {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.5s ease;
        }
        
        @keyframes slideDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .card-header h5 {
            color: white;
        }
        
        .form-label {
            font-weight: 600;
            color: #555;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-update {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            font-weight: 600;
        }
        
        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .btn-cancel {
            background: #f1f1f1;
            color: #666;
            padding: 12px 30px;
            font-weight: 600;
        }
        
        .btn-cancel:hover {
            background: #e1e1e1;
            color: #333;
        }
        
        .current-file {
            margin-top: 5px;
            padding: 5px 10px;
            background: #f8f9fa;
            border-radius: 5px;
            font-size: 12px;
        }
        
        .readonly-field {
            background: #f8f9fa;
            cursor: not-allowed;
        }
        
        .info-text {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .info-text i {
            color: #667eea;
        }
    </style>
</head>
<body>

    <!-- Main wrapper -->
    <div id="main-wrapper">
    
        <?php include 'menu.php'; ?>
        
        <!-- Content body start -->
        <div class="content-body">
            <div class="container-fluid">
            
                <!-- Page Title -->
                <div class="page-title flex-wrap mb-3">
                    <h4 class="mb-0">
                        <i class="fas fa-edit me-2"></i>Edit Center
                    </h4>
                    <a href="CenterList.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            
                <!-- Display Messages -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert-message alert-success">
                        <i class="fas fa-check-circle fa-2x"></i>
                        <div>
                            <strong>Success!</strong> <?= $_SESSION['success_message'] ?>
                        </div>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert-message alert-error">
                        <i class="fas fa-exclamation-circle fa-2x"></i>
                        <div>
                            <strong>Error!</strong> <?= $_SESSION['error_message'] ?>
                        </div>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>
            
                <form action="" method="POST" enctype="multipart/form-data" id="editForm">
                    <input type="hidden" name="id" value="<?= $center['id'] ?>">
                    
                    <div class="row">
                        <!-- Center Details Card -->
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-building me-2"></i>Center Details
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Left Column -->
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">
                                                    Center Name <span class="required">*</span>
                                                </label>
                                                <input type="text" class="form-control" name="center_name" 
                                                       value="<?= htmlspecialchars($center['center_name'] ?? '') ?>" 
                                                       placeholder="Enter center name" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">
                                                    Owner Name <span class="required">*</span>
                                                </label>
                                                <input type="text" class="form-control" name="owner_name" 
                                                       value="<?= htmlspecialchars($center['owner_name'] ?? '') ?>" 
                                                       placeholder="Enter owner name" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">
                                                    Email <span class="required">*</span>
                                                </label>
                                                <input type="email" class="form-control" name="email" 
                                                       value="<?= htmlspecialchars($center['email'] ?? '') ?>" 
                                                       placeholder="Enter email" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">
                                                    Date of Create <span class="required">*</span>
                                                </label>
                                                <input type="date" class="form-control" name="date_of_create" 
                                                       value="<?= date('Y-m-d', strtotime($center['date_of_create'] ?? date('Y-m-d'))) ?>" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">
                                                    Phone Number <span class="required">*</span>
                                                </label>
                                                <input type="text" class="form-control" name="phone" 
                                                       value="<?= htmlspecialchars($center['phone'] ?? '') ?>" 
                                                       placeholder="Enter phone number" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">
                                                    Registration Amount
                                                </label>
                                                <input type="number" class="form-control readonly-field" 
                                                       name="reg_amount" value="<?= htmlspecialchars($center['reg_amount'] ?? '0') ?>" 
                                                       step="0.01" readonly>
                                                <div class="info-text">
                                                    <i class="fas fa-info-circle"></i> Registration amount cannot be changed
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Right Column -->
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">
                                                    State <span class="required">*</span>
                                                </label>
                                                <select name="state" class="form-control" id="state-dropdown" required>
                                                    <option value="">Select State</option>
                                                    <?php
                                                    $states = mysqli_query($conn, "SELECT id, name FROM states ORDER BY name");
                                                    while ($row = mysqli_fetch_assoc($states)) {
                                                        $selected = ($row['id'] == $center['state']) ? "selected" : "";
                                                        echo "<option value='{$row['id']}' $selected>" . htmlspecialchars($row['name']) . "</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">
                                                    District <span class="required">*</span>
                                                </label>
                                                <select name="district" class="form-control" id="district-dropdown" required>
                                                    <option value="">Select District</option>
                                                    <?php
                                                    if (!empty($center['state'])) {
                                                        $districts = mysqli_query($conn, "SELECT id, name FROM districts WHERE state_id = {$center['state']} ORDER BY name");
                                                        while ($row = mysqli_fetch_assoc($districts)) {
                                                            $selected = ($row['id'] == $center['district']) ? "selected" : "";
                                                            echo "<option value='{$row['id']}' $selected>" . htmlspecialchars($row['name']) . "</option>";
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">
                                                    Block
                                                </label>
                                                <input type="text" class="form-control" name="block" 
                                                       value="<?= htmlspecialchars($center['block'] ?? '') ?>" 
                                                       placeholder="Enter block">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">
                                                    Pincode <span class="required">*</span>
                                                </label>
                                                <input type="text" class="form-control" name="pincode" 
                                                       value="<?= htmlspecialchars($center['pincode'] ?? '') ?>" 
                                                       placeholder="Enter pincode" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">
                                                    Payment Mode <span class="required">*</span>
                                                </label>
                                                <select name="payment_mode" class="form-control" required>
                                                    <option value="">Select Payment Mode</option>
                                                    <option value="Online" <?= ($center['payment_mode'] == 'Online') ? 'selected' : '' ?>>Online</option>
                                                    <option value="Offline" <?= ($center['payment_mode'] == 'Offline') ? 'selected' : '' ?>>Offline</option>
                                                    <option value="Bank Transfer" <?= ($center['payment_mode'] == 'Bank Transfer') ? 'selected' : '' ?>>Bank Transfer</option>
                                                    <option value="Cash" <?= ($center['payment_mode'] == 'Cash') ? 'selected' : '' ?>>Cash</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment & Status Card -->
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-credit-card me-2"></i>Payment & Status
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">
                                                    Payment Status <span class="required">*</span>
                                                </label>
                                                <select name="payment_status" class="form-control" required id="payment_status">
                                                    <option value="">Select Status</option>
                                                    <option value="Paid" <?= ($center['payment_status'] == 'Paid') ? 'selected' : '' ?>>Paid</option>
                                                    <option value="Pending" <?= ($center['payment_status'] == 'Pending') ? 'selected' : '' ?>>Pending</option>
                                                    <option value="Failed" <?= ($center['payment_status'] == 'Failed') ? 'selected' : '' ?>>Failed</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">
                                                    Center Status <span class="required">*</span>
                                                </label>
                                                <select name="center_status" class="form-control" required>
                                                    <option value="">Select Status</option>
                                                    <option value="Active" <?= ($center['center_status'] == 'Active') ? 'selected' : '' ?>>Active</option>
                                                    <option value="Inactive" <?= ($center['center_status'] == 'Inactive') ? 'selected' : '' ?>>Inactive</option>
                                                    <option value="Suspended" <?= ($center['center_status'] == 'Suspended') ? 'selected' : '' ?>>Suspended</option>
                                                </select>
                                                <div class="info-text">
                                                    <i class="fas fa-info-circle"></i> 
                                                    <?php if ($center['payment_status'] == 'Paid'): ?>
                                                        <span class="text-success">Payment is paid, you can keep Active</span>
                                                    <?php else: ?>
                                                        <span class="text-warning">Payment is <?= $center['payment_status'] ?>, consider status</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Login Credentials Card -->
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-key me-2"></i>Login Credentials
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">
                                                    Username <span class="required">*</span>
                                                </label>
                                                <input type="text" class="form-control" name="username" 
                                                       value="<?= htmlspecialchars($center['username'] ?? '') ?>" 
                                                       placeholder="Enter username" required>
                                                <div class="info-text">
                                                    <i class="fas fa-info-circle"></i> Username for center login
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">
                                                    Password 
                                                    <small class="text-muted">(Leave empty to keep current)</small>
                                                </label>
                                                <div class="password-field">
                                                    <input type="password" class="form-control" name="password" 
                                                           id="password" placeholder="Enter new password">
                                                    <i class="fas fa-eye toggle-password" onclick="togglePassword()"></i>
                                                </div>
                                                <div class="info-text">
                                                    <i class="fas fa-lock"></i> 
                                                    <?php 
                                                    if (strlen($center['password'] ?? '') > 30) {
                                                        echo "Password is securely hashed";
                                                    } else {
                                                        echo "Current password is set";
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">
                                            Center Code
                                        </label>
                                        <input type="text" class="form-control readonly-field" 
                                               value="<?= htmlspecialchars($center['center_code'] ?? '') ?>" 
                                               readonly>
                                        <div class="info-text">
                                            <i class="fas fa-info-circle"></i> Center code cannot be changed
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Documents Card -->
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-file me-2"></i>Documents
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">
                                                    Center Photo
                                                </label>
                                                <input type="file" class="form-control" name="photo" accept="image/*">
                                                <?php if (!empty($center['photo'])): ?>
                                                    <div class="current-file">
                                                        <i class="fas fa-image text-primary"></i>
                                                        Current: <?= htmlspecialchars($center['photo']) ?>
                                                    </div>
                                                    <img src="uploads/<?= htmlspecialchars($center['photo']) ?>" 
                                                         alt="Center Photo" class="preview-image">
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">
                                                    ID Proof
                                                </label>
                                                <input type="file" class="form-control" name="id_proof" accept="image/*,.pdf">
                                                <?php if (!empty($center['id_proof'])): ?>
                                                    <div class="current-file">
                                                        <i class="fas fa-file-pdf text-danger"></i>
                                                        Current: <?= htmlspecialchars($center['id_proof']) ?>
                                                    </div>
                                                    <a href="uploads/<?= htmlspecialchars($center['id_proof']) ?>" 
                                                       target="_blank" class="btn btn-sm btn-info mt-2">
                                                        <i class="fas fa-eye"></i> View Current ID Proof
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="col-xl-12">
                            <div class="text-end mb-4">
                                <a href="CenterList.php" class="btn btn-cancel me-2">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-update" id="submitBtn">
                                    <i class="fas fa-save"></i> Update Center
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- Content body end -->
        
        <?php include 'footer.php'; ?>
    </div>

    <!-- Scripts -->
    <script src="vendor/global/global.min.js"></script>
    <script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="vendor/bootstrap-datepicker-master/js/bootstrap-datepicker.min.js"></script>
    <script src="js/custom.min.js"></script>
    <script src="js/dlabnav-init.js"></script>
    <script src="js/admin-standard.js"></script>
    
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const icon = document.querySelector('.toggle-password');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // State-District AJAX
        $('#state-dropdown').on('change', function() {
            let stateID = $(this).val();
            if (stateID) {
                $.ajax({
                    type: 'POST',
                    url: 'fetch_district.php',
                    data: { state_id: stateID },
                    success: function(html) {
                        $('#district-dropdown').html(html);
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to load districts'
                        });
                    }
                });
            } else {
                $('#district-dropdown').html('<option value="">Select District</option>');
            }
        });

        // Form submission with loading state
        $('#editForm').on('submit', function(e) {
            const submitBtn = $('#submitBtn');
            submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Updating...');
            submitBtn.prop('disabled', true);
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert-message').fadeOut('slow');
        }, 5000);

        // Show SweetAlert on success
        <?php if (isset($_SESSION['message_type']) && $_SESSION['message_type'] == 'success'): ?>
            Swal.fire({
                title: 'Success!',
                text: '<?= $_SESSION['success_message'] ?? "Center updated successfully!" ?>',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'CenterList.php';
                }
            });
            <?php unset($_SESSION['success_message'], $_SESSION['message_type']); ?>
        <?php endif; ?>

        // Show SweetAlert on error
        <?php if (isset($_SESSION['message_type']) && $_SESSION['message_type'] == 'error'): ?>
            Swal.fire({
                title: 'Error!',
                text: '<?= $_SESSION['error_message'] ?? "Failed to update center!" ?>',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            <?php unset($_SESSION['error_message'], $_SESSION['message_type']); ?>
        <?php endif; ?>
    </script>
</body>
</html>