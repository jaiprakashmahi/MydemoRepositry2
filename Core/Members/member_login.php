<?php
session_start();
include '../conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_type = $_POST['member_type'];
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password) || empty($member_type)) {
        echo '<div class="error-message">All fields are required.</div>';
        return;
    }

    // Map form member types to database values
    $member_type_map = [
        'State Nodal' => 'State Nodal',
        'Zonal Manager' => 'Zonal Manager',
        'District Coordinator' => 'District Coordinator',
        'Block Coordinator' => 'Block Coordinator'
    ];
    
    $db_member_type = $member_type_map[$member_type] ?? $member_type;
    
    // Query for member login from member_information table
    $stmt = $conn->prepare("
        SELECT 
            id, 
            username, 
            password, 
            post_applied,
            applicant_name,
            unicode,
            state,
            district,
            member_status
        FROM member_information 
        WHERE username = ? AND post_applied = ?
    ");
    
    if (!$stmt) {
        echo '<div class="error-message">Database error: ' . $conn->error . '</div>';
        exit();
    }
    
    $stmt->bind_param("ss", $username, $db_member_type);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $db_username, $db_password, $post_applied, 
                          $applicant_name, $unicode, $state, $district, $member_status);
        $stmt->fetch();

        if (empty($db_password)) {
            echo '<div class="error-message">Account not properly set up. Please contact administrator.</div>';
            $stmt->close();
            exit();
        }

        if (password_verify($password, $db_password)) {
            // Check member status
            if ($member_status !== 'Active') {
                echo '<div class="error-message">Your account is not active. Please contact administrator.</div>';
                $stmt->close();
                exit();
            }
            
            // Set session variables
            $_SESSION['member_logged_in'] = true;
            $_SESSION['member_id'] = $id;
            $_SESSION['username'] = $db_username;
            $_SESSION['applicant_name'] = $applicant_name;
            $_SESSION['unicode'] = $unicode;
            $_SESSION['member_type'] = $post_applied;
            $_SESSION['state'] = $state;
            $_SESSION['district'] = $district;
            
            $stmt->close();
            
            // Redirect to index.php
            header("Location: index.php");
            exit();
        } else {
            echo '<div class="error-message">Incorrect password.</div>';
        }
    } else {
        echo '<div class="error-message">Member not found. Please check username and member type.</div>';
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Member Login Panel</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
    body { background-color: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; padding: 20px; }
    .container { background-color: #fff; padding: 40px; border-radius: 12px; box-shadow: 0px 10px 20px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
    h2 { text-align: center; margin-bottom: 24px; color: #333; }
    .form-group { margin-bottom: 20px; }
    label { display: block; margin-bottom: 8px; color: #555; }
    input[type="text"], input[type="password"], select { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px; font-size: 14px; }
    button { background-color: #007bff; color: white; border: none; padding: 12px; width: 100%; border-radius: 8px; font-size: 16px; cursor: pointer; transition: background-color 0.3s; }
    button:hover { background-color: #0056b3; }
    .forgot-link { text-align: center; margin-top: 12px; }
    a { color: #007bff; text-decoration: none; }
    a:hover { text-decoration: underline; }
    img { display: block; margin: 0 auto 20px; max-width: 270px; height: auto; }
    .error-message { color: red; text-align: center; margin-bottom: 15px; padding: 10px; background-color: #ffe6e6; border-radius: 5px; font-size: 14px; }
    .success-message { color: green; text-align: center; margin-bottom: 15px; padding: 10px; background-color: #e6ffe6; border-radius: 5px; }
    @media (max-width: 480px) { .container { padding: 20px; } }
  </style>
</head>
<body>
  <div class="container">
    <p><img src="../images/logo.png" alt="Admin Logo"></p>
    <h2>Member Login</h2>
    
    <?php
    if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
        echo '<div class="success-message">You have been logged out successfully.</div>';
    }
    if (isset($_GET['error']) && $_GET['error'] == 'access_denied') {
        echo '<div class="error-message">Please login to access the member panel.</div>';
    }
    if (isset($_GET['registered']) && $_GET['registered'] == 'true') {
        echo '<div class="success-message">Registration successful! Please wait for admin approval.</div>';
    }
    ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="member_type">Member Type</label>
            <select id="member_type" name="member_type" required>
                <option value="">Select Member Type</option>
                <option value="State Nodal">State Nodal</option>
                <option value="Zonal Manager">Zonal Manager</option>
                <option value="District Coordinator">District Coordinator</option>
                <option value="Block Coordinator">Block Coordinator</option>
            </select>
        </div>

        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" placeholder="Enter your username" required />
        </div>
      
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" placeholder="Enter your password" required />
        </div>
      
        <button type="submit">Login</button>
    </form>
    
    <div class="forgot-link">
      <a href="forgot_password.php">Forgot Password?</a>
    </div>
    
    <div class="forgot-link" style="margin-top: 10px;">
      <a href="../index.php">Back to Home</a>
    </div>
  </div>
</body>
</html>