<?php
session_start();
include '../conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_type = $_POST['member_type'];
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password) || empty($member_type)) {
        echo "<p style='color:red'>All fields are required.</p>";
        return;
    }

    // Check for member
    $stmt = $conn->prepare("SELECT id, username, password, member_type FROM members WHERE username = ? AND member_type = ?");
    $stmt->bind_param("ss", $username, $member_type);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $db_username, $db_password, $db_member_type);
        $stmt->fetch();

        if (password_verify($password, $db_password)) {
            // Generate session token
        

            // Set session
            $_SESSION['member_logged_in'] = true;
            $_SESSION['member_id'] = $id;
            $_SESSION['username'] = $db_username;
            $_SESSION['member_type'] = $db_member_type;

            header("Location: index.php");
            exit();
        } else {
            echo "<p style='color:red'>Incorrect password.</p>";
        }
    } else {
        echo "<p style='color:red'>Member not found.</p>";
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
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background-color: #f0f2f5;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      padding: 20px;
    }

    .container {
      background-color: #fff;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0px 10px 20px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 400px;
    }

    h2 {
      text-align: center;
      margin-bottom: 24px;
      color: #333;
    }

    .form-group {
      margin-bottom: 20px;
    }

    label {
      display: block;
      margin-bottom: 8px;
      color: #555;
    }

    input[type="text"],
    input[type="password"] {
      width: 100%;
      padding: 12px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 14px;
    }

    button {
      background-color: #007bff;
      color: white;
      border: none;
      padding: 12px;
      width: 100%;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
    }

    button:hover {
      background-color: #0056b3;
    }

    .forgot-link {
      text-align: center;
      margin-top: 12px;
    }

    a {
      color: #007bff;
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }

    img {
      display: block;
      margin: 0 auto 20px;
      max-width: 270px;
      height: auto;
    }

    @media (max-width: 480px) {
      .container {
        padding: 20px;
      }

      input[type="text"],
      input[type="password"] {
        font-size: 12px;
      }

      button {
        font-size: 14px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <p><img src="../images/logo.png" alt="Admin Logo"></p>
    <h1>Member Login</h1>
    <form method="POST" action="">
        <div class="form-group">
            <label for="member_type">Member Type</label>
            <select id="member_type" name="member_type" required="" style="width: 100%; border-radius: 8px; padding: 12px; border: 1px solid #ccc;  ">
                <option value="">Select Member</option>
                <option value="State Node">State Node</option>
                <option value="Zonal Manager">Zonal Manager</option>
                <option value="Dristic Co-Ordinator">Dristic Co-Ordinator</option>
                <option value="Block Co-Ordinator">Block Co-Ordinator</option>
            </select>
        </div>

      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" placeholder="Enter your username" />
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" name="password" id="password" placeholder="Enter your password" />
      </div>
      <button type="submit">Login</button>
      <!-- <div class="forgot-link">
        <a href="#">Forgot Password?</a>
      </div> -->
    </form>
  </div>
</body>
</html>

