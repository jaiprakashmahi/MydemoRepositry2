<?php

include 'conn.php';

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = test_input($_POST["email"]);
    $password = test_input($_POST["password"]);
    
    // Fetch the user data based on email
    $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email); // "s" is the type for the email (string)
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc(); // fetch the user data as an associative array

    if ($user) {
        // Use password_verify to check the password
        if (password_verify($password, $user['password'])) {
            header("location: index.php");
            exit(); // Always call exit after a redirect
        } else {
            echo "<script language='javascript'>";
            echo "alert('WRONG INFORMATION');";
            echo "</script>";
        }
    } else {
        echo "<script language='javascript'>";
        echo "alert('User not found.');";
        echo "</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Login Panel</title>
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
    <p><img src="images/logo.png" alt="Admin Logo"></p>
    <form>
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" placeholder="Enter your username" />
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" placeholder="Enter your password" />
      </div>
      <button type="submit">Login</button>
      <!-- <div class="forgot-link">
        <a href="#">Forgot Password?</a>
      </div> -->
    </form>
  </div>
</body>
</html>

