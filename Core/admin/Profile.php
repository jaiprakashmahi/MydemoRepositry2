<?php
// Include session check - this page will NOT open without login
require_once 'check_session.php';

// Include database connection
include 'conn.php';

// Initialize variables
$message = '';
$message_type = '';
$admin_name = $_SESSION['admin_name'] ?? 'Admin';
$admin_id = $_SESSION['admin_id'] ?? 0;
$admin_email = $_SESSION['admin_email'] ?? '';

// Handle Create New Admin
if (isset($_POST['create_admin'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($email) || empty($password)) {
        $message = 'Please fill all required fields.';
        $message_type = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $message_type = 'danger';
    } elseif (strlen($password) < 6) {
        $message = 'Password must be at least 6 characters long.';
        $message_type = 'danger';
    } elseif ($password !== $confirm_password) {
        $message = 'Passwords do not match.';
        $message_type = 'danger';
    } else {
        // Check if email already exists in admins table
        $check_query = "SELECT id FROM admins WHERE email = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $message = 'Email already exists. Please use a different email.';
            $message_type = 'danger';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new admin into admins table
            $insert_query = "INSERT INTO admins (email, password) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("ss", $email, $hashed_password);
            
            if ($insert_stmt->execute()) {
                $message = 'New admin account created successfully!';
                $message_type = 'success';
            } else {
                $message = 'Error creating admin account: ' . $conn->error;
                $message_type = 'danger';
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
}

// Handle Password Change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_new_password = $_POST['confirm_new_password'] ?? '';
    
    // Validation
    if (empty($current_password) || empty($new_password)) {
        $message = 'Please fill all password fields.';
        $message_type = 'danger';
    } elseif (strlen($new_password) < 6) {
        $message = 'New password must be at least 6 characters long.';
        $message_type = 'danger';
    } elseif ($new_password !== $confirm_new_password) {
        $message = 'New passwords do not match.';
        $message_type = 'danger';
    } else {
        // Verify current password from admins table
        $verify_query = "SELECT password FROM admins WHERE id = ?";
        $verify_stmt = $conn->prepare($verify_query);
        $verify_stmt->bind_param("i", $admin_id);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();
        
        if ($verify_result->num_rows > 0) {
            $admin_data = $verify_result->fetch_assoc();
            if (password_verify($current_password, $admin_data['password'])) {
                // Hash new password
                $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Update password in admins table
                $update_query = "UPDATE admins SET password = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("si", $new_hashed_password, $admin_id);
                
                if ($update_stmt->execute()) {
                    $message = 'Password changed successfully!';
                    $message_type = 'success';
                } else {
                    $message = 'Error changing password: ' . $conn->error;
                    $message_type = 'danger';
                }
                $update_stmt->close();
            } else {
                $message = 'Current password is incorrect.';
                $message_type = 'danger';
            }
        } else {
            $message = 'Admin account not found.';
            $message_type = 'danger';
        }
        $verify_stmt->close();
    }
}

// Get all admins from admins table for listing
$admins_query = "SELECT id, email, created_at FROM admins ORDER BY id DESC";
$admins_result = $conn->query($admins_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Sharnay Institute">
    <title>Admin Management | Sharnay Institute</title>
    
    <!-- FAVICONS ICON -->
    <link rel="shortcut icon" type="image/png" href="images/favicon.png">
    <link href="vendor/wow-master/css/libs/animate.css" rel="stylesheet">
    <link href="vendor/bootstrap-datepicker-master/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <link href="vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,400,0,0" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
    <link href="css/admin-standard.css" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background: #f0f2f8;
        }
        
        .page-wraper {
            background: linear-gradient(135deg, #f6f9fc 0%, #edf2f9 100%);
            min-height: 100vh;
        }
        
        .content-body {
            padding: 1.5rem;
        }
        
        /* Modern Card Styles */
        .modern-card {
            background: white;
            border-radius: 28px;
            border: none;
            box-shadow: 0 12px 28px -8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .modern-card:hover {
            box-shadow: 0 20px 35px -12px rgba(0, 0, 0, 0.12);
        }
        
        .card-header-modern {
            background: linear-gradient(135deg, #1a4a6f 0%, #0b2b40 100%);
            color: white;
            padding: 1.2rem 1.8rem;
            border: none;
        }
        
        .card-header-modern h4 {
            margin: 0;
            font-weight: 700;
            font-size: 1.3rem;
        }
        
        .form-label-modern {
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #4a5b6e;
            margin-bottom: 0.5rem;
        }
        
        .form-control-modern {
            border-radius: 14px;
            border: 1.5px solid #e2e8f0;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        
        .form-control-modern:focus {
            border-color: #1a4a6f;
            box-shadow: 0 0 0 3px rgba(26, 74, 111, 0.1);
            outline: none;
        }
        
        .btn-modern-primary {
            background: linear-gradient(135deg, #1a4a6f 0%, #0f3550 100%);
            border: none;
            border-radius: 14px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            color: white;
            transition: all 0.2s;
            cursor: pointer;
        }
        
        .btn-modern-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(26, 74, 111, 0.3);
            background: linear-gradient(135deg, #0f3550 0%, #0a2538 100%);
        }
        
        .admin-table {
            border-collapse: separate;
            border-spacing: 0 8px;
        }
        
        .admin-table thead th {
            background: transparent;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #5a6e7c;
            padding: 1rem 0.75rem;
            border: none;
        }
        
        .admin-table tbody tr {
            background: white;
            border-radius: 16px;
            transition: all 0.2s;
        }
        
        .admin-table tbody tr:hover {
            background: #fefefe;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .admin-table td {
            padding: 1rem 0.75rem;
            border: none;
            font-size: 0.85rem;
            font-weight: 500;
            color: #1f2a3e;
            vertical-align: middle;
        }
        
        .badge-role {
            background: #eef2ff;
            color: #1a4a6f;
            border-radius: 40px;
            padding: 0.3rem 1rem;
            font-size: 0.7rem;
            font-weight: 600;
        }
        
        .alert-modern {
            border-radius: 16px;
            border: none;
            padding: 1rem 1.2rem;
        }
        
        .password-requirements {
            font-size: 0.7rem;
            color: #6c757d;
            margin-top: 0.3rem;
        }
        
        /* Toast alert style */
        .toast-alert {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 320px;
            animation: slideInRight 0.3s ease-out;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @media (max-width: 768px) {
            .content-body {
                padding: 1rem;
            }
            .card-header-modern h4 {
                font-size: 1.1rem;
            }
            .toast-alert {
                top: 10px;
                right: 10px;
                left: 10px;
                min-width: auto;
            }
        }
    </style>
</head>

<body>
<div class="page-wraper">
    <!-- Include Header Menu -->
    <?php include 'menu.php'; ?>
    
    <!-- Main Content -->
    <div class="content-body">
        <div class="container-fluid px-0 px-lg-3">
            
            <!-- Page Title & Breadcrumb -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1" style="color: #1a2a3a;">
                        <i class="bi bi-shield-lock-fill me-2" style="color: #1a4a6f;"></i>Admin Management
                    </h2>
                    <p class="text-muted">Create new admin accounts and manage your security credentials</p>
                </div>
                <div class="mt-2 mt-sm-0">
                    <span class="badge bg-white text-dark px-3 py-2 rounded-pill shadow-sm">
                        <i class="bi bi-person-circle me-1"></i> Logged in as: <?php echo htmlspecialchars($admin_name); ?>
                    </span>
                </div>
            </div>
            
            <!-- Two Column Layout -->
            <div class="row g-4">
                <!-- Left Column: Change Password -->
                <div class="col-lg-5">
                    <div class="modern-card">
                        <div class="card-header-modern">
                            <h4><i class="bi bi-key-fill me-2"></i> Change Your Password</h4>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST" action="" id="passwordChangeForm">
                                <div class="mb-4">
                                    <label class="form-label-modern d-block mb-2">
                                        <i class="bi bi-lock-fill me-1"></i> Current Password
                                    </label>
                                    <input type="password" name="current_password" id="current_password" 
                                           class="form-control form-control-modern" required>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label-modern d-block mb-2">
                                        <i class="bi bi-key me-1"></i> New Password
                                    </label>
                                    <input type="password" name="new_password" id="new_password" 
                                           class="form-control form-control-modern" required>
                                    <div class="password-requirements">
                                        <i class="bi bi-info-circle-fill me-1"></i> Minimum 6 characters, strong password recommended
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label-modern d-block mb-2">
                                        <i class="bi bi-check-circle me-1"></i> Confirm New Password
                                    </label>
                                    <input type="password" name="confirm_new_password" id="confirm_new_password" 
                                           class="form-control form-control-modern" required>
                                </div>
                                
                                <button type="submit" name="change_password" class="btn-modern-primary w-100" style="padding: 0.75rem 1.5rem;">
                                    <i class="bi bi-arrow-repeat me-2"></i> Update Password
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Security Tips -->
                    <div class="modern-card mt-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3"><i class="bi bi-shield-check me-2" style="color: #1a4a6f;"></i> Security Tips</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2 fs-6"></i> Use a strong, unique password</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2 fs-6"></i> Never share your credentials</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2 fs-6"></i> Change password regularly</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2 fs-6"></i> Enable 2FA if available</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column: Create New Admin & Admin List -->
                <div class="col-lg-7">
                    <!-- Create New Admin Card -->
                    <div class="modern-card">
                        <div class="card-header-modern">
                            <h4><i class="bi bi-person-plus-fill me-2"></i> Create New Admin Account</h4>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST" action="" id="createAdminForm">
                                <div class="mb-4">
                                    <label class="form-label-modern d-block mb-2">
                                        <i class="bi bi-envelope-fill me-1"></i> Email Address
                                    </label>
                                    <input type="email" name="email" id="admin_email" 
                                           class="form-control form-control-modern" 
                                           placeholder="admin@example.com" required>
                                </div>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label-modern d-block mb-2">
                                            <i class="bi bi-key me-1"></i> Password
                                        </label>
                                        <input type="password" name="password" id="admin_password" 
                                               class="form-control form-control-modern" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-modern d-block mb-2">
                                            <i class="bi bi-check-lg me-1"></i> Confirm Password
                                        </label>
                                        <input type="password" name="confirm_password" id="confirm_admin_password" 
                                               class="form-control form-control-modern" required>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <button type="submit" name="create_admin" class="btn-modern-primary w-100" style="padding: 0.75rem 1.5rem;">
                                        <i class="bi bi-person-badge me-2"></i> Create Admin Account
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Admin List Card -->
                    <div class="modern-card mt-4">
                        <div class="card-header-modern">
                            <h4><i class="bi bi-people-fill me-2"></i> Registered Administrators</h4>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table admin-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Email Address</th>
                                            <th>Created Date</th>
                                            <th>Role</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($admins_result && $admins_result->num_rows > 0): ?>
                                            <?php while($admin = $admins_result->fetch_assoc()): ?>
                                            <tr>
                                                <td class="fw-bold">#<?php echo $admin['id']; ?></td>
                                                <td>
                                                    <?php echo htmlspecialchars($admin['email']); ?>
                                                    <?php if(isset($_SESSION['admin_email']) && $_SESSION['admin_email'] == $admin['email']): ?>
                                                        <span class="badge bg-primary ms-2" style="font-size: 0.6rem;">You</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('d M Y, h:i A', strtotime($admin['created_at'] ?? 'now')); ?></td>
                                                <td><span class="badge-role">Administrator</span></td>
                                            </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">
                                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                                    No admin accounts found
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alert Message Display using Sweet Toast -->
<?php if ($message): ?>
<script>
    // Show alert message as floating toast
    function showFloatingAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `toast-alert alert alert-${type} d-flex align-items-center shadow-lg`;
        alertDiv.style.background = type === 'success' ? '#d4edda' : '#f8d7da';
        alertDiv.style.borderLeft = `4px solid ${type === 'success' ? '#28a745' : '#dc3545'}`;
        alertDiv.style.borderRadius = '12px';
        alertDiv.innerHTML = `
            <i class="bi bi-${type === 'success' ? 'check-circle-fill text-success' : 'exclamation-triangle-fill text-danger'} me-2 fs-5"></i>
            <div class="flex-grow-1" style="color: ${type === 'success' ? '#155724' : '#721c24'};">${message}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.body.appendChild(alertDiv);
        
        // Auto remove after 4 seconds
        setTimeout(() => {
            if (alertDiv && alertDiv.remove) {
                alertDiv.style.animation = 'slideOutRight 0.3s ease-out';
                setTimeout(() => alertDiv.remove(), 300);
            }
        }, 4000);
        
        // Add slide out animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideOutRight {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }
    
    // Show the alert
    showFloatingAlert('<?php echo addslashes($message); ?>', '<?php echo $message_type; ?>');
</script>
<?php endif; ?>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    // Initialize AOS
    AOS.init({
        duration: 600,
        once: true,
        offset: 20
    });
    
    // Validate Password Change Form
    document.getElementById('passwordChangeForm')?.addEventListener('submit', function(e) {
        const newPass = document.getElementById('new_password').value;
        const confirmPass = document.getElementById('confirm_new_password').value;
        
        if (newPass.length < 6) {
            e.preventDefault();
            showFormAlert('New password must be at least 6 characters long.', 'danger');
            return false;
        }
        
        if (newPass !== confirmPass) {
            e.preventDefault();
            showFormAlert('New passwords do not match!', 'danger');
            return false;
        }
        
        return true;
    });
    
    // Validate Create Admin Form
    document.getElementById('createAdminForm')?.addEventListener('submit', function(e) {
        const email = document.getElementById('admin_email').value;
        const password = document.getElementById('admin_password').value;
        const confirmPassword = document.getElementById('confirm_admin_password').value;
        
        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            showFormAlert('Please enter a valid email address.', 'danger');
            return false;
        }
        
        if (password.length < 6) {
            e.preventDefault();
            showFormAlert('Password must be at least 6 characters long.', 'danger');
            return false;
        }
        
        if (password !== confirmPassword) {
            e.preventDefault();
            showFormAlert('Passwords do not match!', 'danger');
            return false;
        }
        
        return true;
    });
    
    // Show form alert
    function showFormAlert(message, type) {
        // Remove any existing alert
        const existingAlert = document.querySelector('.form-alert-toast');
        if (existingAlert) existingAlert.remove();
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `form-alert-toast alert alert-${type} d-flex align-items-center mb-3`;
        alertDiv.style.borderRadius = '12px';
        alertDiv.innerHTML = `
            <i class="bi bi-${type === 'danger' ? 'exclamation-triangle-fill' : 'check-circle-fill'} me-2"></i>
            <div>${message}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        `;
        
        // Insert at top of the form
        const form = document.querySelector('form');
        if (form) {
            form.insertBefore(alertDiv, form.firstChild);
        }
        
        setTimeout(() => {
            if (alertDiv && alertDiv.remove) alertDiv.remove();
        }, 4000);
    }
    
    // Auto-hide bootstrap alerts after 5 seconds
    setTimeout(() => {
        document.querySelectorAll('.alert-dismissible').forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    }, 1000);
</script>
</body>
</html>