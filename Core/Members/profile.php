<?php
session_start();
include '../conn.php';

if (!isset($_SESSION['member_logged_in'])) {
    header("Location: member_login.php");
    exit();
}

$member_id = $_SESSION['member_id'];
$message = '';
$message_type = '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = 'All fields are required.';
        $message_type = 'error';
    } elseif ($new_password !== $confirm_password) {
        $message = 'New passwords do not match.';
        $message_type = 'error';
    } elseif (strlen($new_password) < 6) {
        $message = 'Password must be at least 6 characters.';
        $message_type = 'error';
    } else {
        // Get current password hash
        $stmt = $conn->prepare("SELECT password FROM member_information WHERE id = ?");
        $stmt->bind_param("i", $member_id);
        $stmt->execute();
        $stmt->bind_result($db_password);
        $stmt->fetch();
        $stmt->close();
        
        if (password_verify($current_password, $db_password)) {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE member_information SET password = ? WHERE id = ?");
            $update_stmt->bind_param("si", $hashed_password, $member_id);
            
            if ($update_stmt->execute()) {
                $message = 'Password changed successfully!';
                $message_type = 'success';
            } else {
                $message = 'Failed to update password. Please try again.';
                $message_type = 'error';
            }
            $update_stmt->close();
        } else {
            $message = 'Current password is incorrect.';
            $message_type = 'error';
        }
    }
}

// Get member details
$stmt = $conn->prepare("
    SELECT applicant_name, unicode, post_applied, mobile_number, email_id, 
           state, district, created_at
    FROM member_information 
    WHERE id = ?
");
$stmt->bind_param("i", $member_id);
$stmt->execute();
$stmt->bind_result($applicant_name, $unicode, $post_applied, $mobile_number, 
                   $email_id, $state, $district, $created_at);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Profile - Sharnay Institute</title>
    
    <?php include 'menu.php'; ?>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    
    <style>
        body { background-color: #f8f9fc; }
        .main-content { margin-left: 250px; min-height: 100vh; padding-top: 70px; }
        .card { border: 1px solid #e3e6f0; border-radius: 0.35rem; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1); margin-bottom: 1.5rem; }
        .card-header { background-color: #f8f9fc; border-bottom: 1px solid #e3e6f0; padding: 1rem 1.35rem; }
        .alert-success { background-color: #d1e7dd; border-color: #badbcc; color: #0f5132; }
        .alert-danger { background-color: #f8d7da; border-color: #f5c2c7; color: #842029; }
        @media (max-width: 768px) { .main-content { margin-left: 0; } }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-lg-8">
                    <!-- Profile Information -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Profile Information</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($message): ?>
                                <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'danger'; ?>">
                                    <?php echo $message; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($applicant_name); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Member Code</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($unicode); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Member Type</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($post_applied); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mobile Number</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($mobile_number); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($email_id); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">State</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($state); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">District</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($district); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Member Since</label>
                                    <input type="text" class="form-control" value="<?php echo date('d M, Y', strtotime($created_at)); ?>" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Change Password -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Change Password</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Current Password</label>
                                        <input type="password" name="current_password" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">New Password</label>
                                        <input type="password" name="new_password" class="form-control" required minlength="6">
                                        <small class="text-muted">Minimum 6 characters</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Confirm New Password</label>
                                        <input type="password" name="confirm_password" class="form-control" required minlength="6">
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" name="change_password" class="btn btn-primary">
                                            <i class="bi bi-key me-2"></i> Change Password
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>