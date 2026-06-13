<?php
session_start();
include '../conn.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: student_login.php');
    exit();
}

$student_id = $_SESSION['student_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Get current password hash
    $query = "SELECT password FROM onlinestudents WHERE id = '$student_id'";
    $result = mysqli_query($conn, $query);
    $student = mysqli_fetch_assoc($result);
    
    if (password_verify($current_password, $student['password'])) {
        if ($new_password === $confirm_password) {
            if (strlen($new_password) >= 6) {
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $update_query = "UPDATE onlinestudents SET password = '$new_password_hash' WHERE id = '$student_id'";
                
                if (mysqli_query($conn, $update_query)) {
                    $success = "Password changed successfully!";
                } else {
                    $error = "Error updating password: " . mysqli_error($conn);
                }
            } else {
                $error = "New password must be at least 6 characters long";
            }
        } else {
            $error = "New password and confirm password do not match";
        }
    } else {
        $error = "Current password is incorrect";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
    <!-- Include same CSS as dashboard -->
</head>
<body>
    <!-- Same sidebar structure -->
    <div class="main-content">
        <div class="profile-header">
            <h1 class="h3 mb-0"><i class="fas fa-key me-2"></i>Change Password</h1>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="profile-card">
            <form method="POST" action="">
                <div class="mb-3">
                    <label>Current Password</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label>New Password</label>
                    <input type="password" name="new_password" class="form-control" required minlength="6">
                    <small class="text-muted">Minimum 6 characters</small>
                </div>
                
                <div class="mb-3">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Change Password</button>
            </form>
        </div>
    </div>
</body>
</html>