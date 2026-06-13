<?php
session_start();
include '../conn.php';

$error = '';
$success = '';

/* Generate captcha ONLY if not exists */
if (!isset($_SESSION['captcha'])) {
    $a = rand(1, 9);
    $b = rand(1, 9);
    $_SESSION['captcha']   = $a + $b;
    $_SESSION['captcha_q'] = "$a + $b";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $student_code = mysqli_real_escape_string($conn, $_POST['student_code']);
    $username     = mysqli_real_escape_string($conn, $_POST['username']);
    $dob          = mysqli_real_escape_string($conn, $_POST['dob']);
    $newpassword  = $_POST['newpassword'];
    $confirmpass  = $_POST['confirmpassword'];
    $captcha      = trim($_POST['captcha']);

    /* 1️⃣ Captcha check */
    if (!isset($_SESSION['captcha']) || $captcha != $_SESSION['captcha']) {
        $error = "❌ Captcha is incorrect!";
    }

    /* 2️⃣ Password match */
    elseif ($newpassword !== $confirmpass) {
        $error = "❌ New Password and Confirm Password do not match!";
    }

    /* 3️⃣ Password length */
    elseif (strlen($newpassword) < 6) {
        $error = "❌ Password must be at least 6 characters!";
    }

    else {

        /* 4️⃣ Verify student */
        $query = "SELECT id FROM onlinestudents 
                  WHERE student_code='$student_code'
                  AND username='$username'
                  AND dob='$dob'";

        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) == 1) {

            $hashed = password_hash($newpassword, PASSWORD_DEFAULT);

            mysqli_query($conn,
                "UPDATE onlinestudents 
                 SET password='$hashed' 
                 WHERE student_code='$student_code'"
            );

            $success = "✅ Password updated successfully. Please login.";

        } else {
            $error = "❌ Student details not matched!";
        }
    }

    /* 🔄 Always regenerate captcha AFTER POST */
    unset($_SESSION['captcha'], $_SESSION['captcha_q']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Forgot Password | Sharnay Institute</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
body{
    margin:0;
    font-family:'Segoe UI',sans-serif;
    height:100vh;
}
.wrapper{
    display:flex;
    height:100vh;
}
.left{
    width:50%;
    background:linear-gradient(135deg,#667eea,#764ba2);
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
}
.right{
    width:50%;
    background:#f8f9fa;
    display:flex;
    align-items:center;
    justify-content:center;
}
.card{
    width:100%;
    max-width:420px;
    padding:30px;
    border-radius:15px;
    box-shadow:0 20px 40px rgba(0,0,0,.1);
}
.password-group{
    position:relative;
}
.toggle-eye{
    position:absolute;
    right:15px;
    top:38px;
    cursor:pointer;
    color:#666;
}
@media(max-width:768px){
    .wrapper{flex-direction:column;}
    .left,.right{width:100%;}
    .left{padding:40px 20px;}
}
</style>
</head>

<body>

<div class="wrapper">

    <!-- LEFT -->
    <div class="left text-center">
        <div>
            <h1>Reset Password 🔐</h1>
            <p>Verify your identity to continue</p>
        </div>
    </div>

    <!-- RIGHT -->
    <div class="right">
        <div class="card">

            <h4 class="text-center mb-3">Forgot Password</h4>

            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?><br>
                    <a href="Login.php">Go to Login</a>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="mb-3">
                    <label>Student Code</label>
                    <input type="text" name="student_code" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Date of Birth</label>
                    <input type="date" name="dob" class="form-control" required>
                </div>

                <div class="mb-3 password-group">
                    <label>New Password</label>
                    <input type="password" name="newpassword" id="newpassword" class="form-control" required>
                    <i class="fas fa-eye toggle-eye" onclick="toggle('newpassword', this)"></i>
                </div>

                <div class="mb-3 password-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirmpassword" id="confirmpassword" class="form-control" required>
                    <i class="fas fa-eye toggle-eye" onclick="toggle('confirmpassword', this)"></i>
                </div>

                <div class="mb-3">
                    <label>
                        Captcha: 
                        <?php echo isset($_SESSION['captcha_q']) ? $_SESSION['captcha_q'] : 'Reload page'; ?> = ?
                    </label>
                    <input type="number" name="captcha" class="form-control" required>
                </div>

                <button class="btn btn-primary w-100">
                    Update Password
                </button>

                <div class="text-center mt-3">
                    <a href="Login.php" class="small">Back to Login</a>
                </div>

            </form>
        </div>
    </div>

</div>

<script>
function toggle(id, icon){
    const input = document.getElementById(id);
    if(input.type === "password"){
        input.type = "text";
        icon.classList.replace('fa-eye','fa-eye-slash');
    }else{
        input.type = "password";
        icon.classList.replace('fa-eye-slash','fa-eye');
    }
}
</script>

</body>
</html>
