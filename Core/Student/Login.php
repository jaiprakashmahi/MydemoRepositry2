<?php
session_start();
include '../conn.php';

$error = '';

// Already logged in
if (isset($_SESSION['student_id'])) {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    if (is_numeric($username)) {
        $query = "SELECT * FROM onlinestudents WHERE mobile='$username'";
    } else {
        $query = "SELECT * FROM onlinestudents WHERE username='$username'";
    }

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) == 1) {
        $student = mysqli_fetch_assoc($result);

        if (password_verify($password, $student['password'])) {
            $_SESSION['student_id'] = $student['id'];
            $_SESSION['student_name'] = $student['name'];
            $_SESSION['student_email'] = $student['email'];
            $_SESSION['student_photo'] = $student['photo'];

            header('Location: dashboard.php');
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Invalid username or mobile number!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Login | Sharnay Institute</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
body{
    margin:0;
    font-family:'Segoe UI',sans-serif;
    height:100vh;
}
.auth-wrapper{
    display:flex;
    height:100vh;
}

/* LEFT */
.auth-left{
    width:50%;
    background:linear-gradient(135deg,#667eea,#764ba2);
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
}
.left-content{
    text-align:center;
    animation:fadeIn 1.2s ease;
}
.left-content img{
    width: 500px;
    margin-bottom:20px;
}
.left-content h1{
    font-size:2.8rem;
    font-weight:700;
}
.left-content p{
    opacity:.9;
    font-size:1.1rem;
}
.icons i{
    font-size:2rem;
    margin:15px;
    animation:float 3s infinite ease-in-out;
}
.icons i:nth-child(2){animation-delay:.5s}
.icons i:nth-child(3){animation-delay:1s}

/* RIGHT */
.auth-right{
    width:50%;
    background:#f8f9fa;
    display:flex;
    align-items:center;
    justify-content:center;
}
.login-card{
    background:#fff;
    width:100%;
    max-width:380px;
    padding:35px;
    border-radius:15px;
    box-shadow:0 20px 40px rgba(0,0,0,.1);
}
.login-card h3{
    font-weight:700;
}

/* Animations */
@keyframes float{
    0%{transform:translateY(0)}
    50%{transform:translateY(-12px)}
    100%{transform:translateY(0)}
}
@keyframes fadeIn{
    from{opacity:0;transform:translateY(20px)}
    to{opacity:1;transform:translateY(0)}
}

/* Mobile */
@media(max-width:768px){
    .auth-wrapper{flex-direction:column;}
    .auth-left,.auth-right{width:100%;}
    .auth-left{padding:40px 20px;}
}
</style>
</head>

<body>

<div class="auth-wrapper">

    <!-- LEFT PANEL -->
    <div class="auth-left">
        <div class="left-content">
            <img src="../flogo.png" alt="Institute Logo">
            <h1>Welcome Back</h1>
            <p>
                Learn • Grow • Succeed<br>
                Access your dashboard anytime
            </p>
            <div class="icons">
                <i class="fas fa-graduation-cap"></i>
                <i class="fas fa-book"></i>
                <i class="fas fa-laptop"></i>
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="auth-right">
        <div class="login-card">

            <h3 class="text-center mb-2">Student Login</h3>
            <p class="text-muted text-center mb-4">
                Sharnay Institute Portal
            </p>

            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">Username / Mobile</label>
                    <input type="text" name="username" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>

                <button class="btn btn-primary w-100 mb-3">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </button>

                <div class="text-center mb-3">
                    <a href="forgot_password.php" class="small">Forgot Password?</a>
                </div>

                <hr>

                <div class="text-center">
                    <span class="small">New Student?</span><br>
                    <a href="../student_reg.php" class="btn btn-outline-secondary mt-2">
                        Register Now
                    </a>
                </div>

            </form>
        </div>
    </div>

</div>

</body>
</html>
