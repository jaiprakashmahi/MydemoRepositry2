<?php
session_start();
include 'conn.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_email'])) {
    header("Location: index.php");
    exit();
}

function test_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = test_input($_POST["email"]);
    $password = test_input($_POST["password"]);

    // Check Admin Data
    $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_name'] = $admin['full_name'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['admin_id'] = $admin['id'];
        header("Location: index.php");
        exit();
    } else {
        $error_message = "Invalid Email or Password";
    }
}
?>


<!DOCTYPE html>
<html lang="en" class="h-100">

<head>
   <!-- All Meta -->
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="author" content="DexignLab" >
	<meta name="robots" content="" >
	<!-- Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- Page Title Here -->
	<title>Sharnay Dashboard</title>

<!-- FAVICONS ICON -->
	<link rel="shortcut icon" type="image/png" href="images/favicon.png" >
	<link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/admin-standard.css" rel="stylesheet">

</head>

<body class="body  h-100">
	<div class="authincation d-flex flex-column flex-lg-row flex-column-fluid">
		<div class="login-aside text-center  d-flex flex-column flex-row-auto">
			<div class="d-flex flex-column-auto flex-column pt-lg-40 pt-15">
				<div class="text-center mb-lg-4 mb-2 pt-5 logo">
					<img src="images/logo.png" alt="">
				</div>
				<h3 class="mb-2 text-white">Welcome to Sharny Institute!</h3>
				<!-- <p class="mb-4">User Experience & Interface Design <br>Strategy  Solutions</p> -->
			</div>
			<div class="aside-image position-relative" style="background-image:url(images/background/pic-2.png);">
				
				
			</div>
		</div>
		<div class="container flex-row-fluid d-flex flex-column justify-content-center position-relative overflow-hidden p-7 mx-auto">
			<div class="d-flex justify-content-center h-100 align-items-center">
				<div class="authincation-content style-2">
					<div class="row no-gutters">
						<div class="col-xl-12 tab-content">
							<div id="sign-up" class="auth-form tab-pane fade show active  form-validation">
								<form action="" method="POST">
									<div class="text-center mb-4">
										<h3 class="text-center mb-2 text-black">Sign In</h3>
										
									</div>
									
									<div class="sepertor">
										<span class="d-block mb-4 fs-13">With email</span>
									</div>
									<div class="mb-3">
										<label for="" class="form-label mb-2 fs-13 label-color font-w500">Email address</label>
									  <input type="email" name ="email" class="form-control" id="" placeholder="Email">
									</div>
									<div class="mb-3">
										<label for="" class="form-label mb-2 fs-13 label-color font-w500">Password</label>
									  <input type="password" name="password" class="form-control" id="" placeholder="Password">
									</div>
									
									<button class="btn btn-block btn-primary">Sign In</button>
									
								</form>
								</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>


    <!--**********************************
        Scripts
    ***********************************-->
    <!-- Required vendors -->
    <script src="vendor/global/global.min.js"></script>
    <script src="js/custom.min.js"></script>
    <script src="js/dlabnav-init.js"></script>
    <script src="js/admin-standard.js"></script>
	
</body>

<!-- Mirrored from akademi.dexignlab.com/xhtml/page-login.html by HTTrack Website Copier/3.x [XR&CO'2014], Sun, 26 Jan 2025 10:09:29 GMT -->
</html>