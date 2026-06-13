<?php
session_start();
include '../conn.php';

$step = isset($_GET['step']) ? $_GET['step'] : 1;
$error = '';
$success = '';

// Step 3: Update Password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $mobile_number = trim($_POST['mobile_number']);
    $username = trim($_POST['username']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($mobile_number) || empty($username) || empty($new_password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Verify user exists with given mobile number and username
        $stmt = $conn->prepare("SELECT id, applicant_name FROM member_information WHERE mobile_number = ? AND username = ?");
        $stmt->bind_param("ss", $mobile_number, $username);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $applicant_name);
            $stmt->fetch();
            
            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password
            $update_stmt = $conn->prepare("UPDATE member_information SET password = ? WHERE id = ?");
            $update_stmt->bind_param("si", $hashed_password, $id);
            
            if ($update_stmt->execute()) {
                $success = "Password updated successfully! <a href='member_login.php'>Click here to login</a>";
                $step = 4; // Completion step
            } else {
                $error = "Failed to update password. Please try again.";
            }
            $update_stmt->close();
        } else {
            $error = "No member found with the provided Mobile Number and Username.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Member Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
        .container { background-color: #fff; padding: 40px; border-radius: 12px; box-shadow: 0px 10px 20px rgba(0,0,0,0.2); width: 100%; max-width: 450px; }
        h2 { text-align: center; margin-bottom: 10px; color: #333; }
        .subtitle { text-align: center; color: #666; margin-bottom: 30px; font-size: 14px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: #555; font-weight: 500; }
        input[type="text"], input[type="password"] { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; transition: border-color 0.3s; }
        input[type="text"]:focus, input[type="password"]:focus { outline: none; border-color: #667eea; }
        button { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 12px; width: 100%; border-radius: 8px; font-size: 16px; cursor: pointer; transition: transform 0.2s; font-weight: 600; }
        button:hover { transform: translateY(-2px); }
        .back-link { text-align: center; margin-top: 20px; }
        a { color: #667eea; text-decoration: none; }
        a:hover { text-decoration: underline; }
        img { display: block; margin: 0 auto 20px; max-width: 200px; height: auto; }
        .error-message { color: #dc3545; text-align: center; margin-bottom: 20px; padding: 10px; background-color: #ffe6e6; border-radius: 5px; font-size: 14px; }
        .success-message { color: #28a745; text-align: center; margin-bottom: 20px; padding: 10px; background-color: #e6ffe6; border-radius: 5px; font-size: 14px; }
        .info-box { background-color: #f8f9fa; border-left: 4px solid #667eea; padding: 12px; margin-bottom: 20px; border-radius: 5px; font-size: 14px; color: #555; }
        .step-indicator { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .step { flex: 1; text-align: center; padding: 10px; background-color: #f0f0f0; color: #999; font-size: 12px; position: relative; }
        .step.active { background-color: #667eea; color: white; }
        .step.completed { background-color: #28a745; color: white; }
        .step:first-child { border-radius: 5px 0 0 5px; }
        .step:last-child { border-radius: 0 5px 5px 0; }
        @media (max-width: 480px) { .container { padding: 25px; } .step { font-size: 10px; } }
    </style>
</head>
<body>
    <div class="container">
        <p><img src="../images/logo.png" alt="Logo"></p>
        <h2>Forgot Password</h2>
        <p class="subtitle">Reset your account password</p>
        
        <!-- Step Indicator -->
        <div class="step-indicator">
            <div class="step <?php echo $step == 1 ? 'active' : ($step > 1 ? 'completed' : ''); ?>">Verify Identity</div>
            <div class="step <?php echo $step == 2 ? 'active' : ($step > 2 ? 'completed' : ''); ?>">Reset Password</div>
            <div class="step <?php echo $step == 3 ? 'active' : ($step == 4 ? 'completed' : ''); ?>">Complete</div>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success && $step == 4): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($step == 1): ?>
            <!-- Step 1: Verify Mobile Number and Username -->
            <div class="info-box">
                <strong>Note:</strong> Enter your registered Mobile Number and Username to reset your password.
            </div>
            <form method="GET" action="">
                <input type="hidden" name="step" value="2">
                <div class="form-group">
                    <label for="mobile_number">Mobile Number</label>
                    <input type="text" id="mobile_number" name="mobile_number" placeholder="Enter your registered mobile number" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required autocomplete="off">
                </div>
                <button type="submit">Verify & Continue</button>
            </form>
            <div class="back-link">
                <a href="member_login.php">← Back to Login</a>
            </div>
            
        <?php elseif ($step == 2): ?>
            <!-- Step 2: Verify credentials and show password reset form -->
            <?php
            $mobile_number = isset($_GET['mobile_number']) ? trim($_GET['mobile_number']) : '';
            $username = isset($_GET['username']) ? trim($_GET['username']) : '';
            
            if (empty($mobile_number) || empty($username)) {
                header("Location: forgot_password.php?step=1&error=missing");
                exit();
            }
            
            // Verify user exists
            $stmt = $conn->prepare("SELECT id, applicant_name FROM member_information WHERE mobile_number = ? AND username = ?");
            $stmt->bind_param("ss", $mobile_number, $username);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows === 1) {
                $stmt->bind_result($id, $applicant_name);
                $stmt->fetch();
                ?>
                <div class="info-box">
                    <strong>Welcome <?php echo htmlspecialchars($applicant_name); ?>!</strong><br>
                    You can now reset your password.
                </div>
                <form method="POST" action="forgot_password.php?step=3">
                    <input type="hidden" name="mobile_number" value="<?php echo htmlspecialchars($mobile_number); ?>">
                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" placeholder="Enter new password (min 6 characters)" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
                    </div>
                    <button type="submit" name="update_password">Update Password</button>
                </form>
                <div class="back-link">
                    <a href="forgot_password.php?step=1">← Start Over</a>
                </div>
                <?php
            } else {
                echo '<div class="error-message">No member found with the provided Mobile Number and Username. <a href="forgot_password.php?step=1">Try again</a></div>';
            }
            $stmt->close();
            ?>
            
        <?php elseif ($step == 3): ?>
            <!-- Step 3: Process password update (handled at top of page) -->
            <?php
            // The form submission is handled at the top of the page
            // If we're here without POST, redirect to step 1
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header("Location: forgot_password.php?step=1");
                exit();
            }
            ?>
            
        <?php elseif ($step == 4): ?>
            <!-- Step 4: Success message shown at top -->
            <div class="back-link" style="margin-top: 20px;">
                <a href="member_login.php">Go to Login Page →</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>