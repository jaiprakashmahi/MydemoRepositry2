<?php
session_start();
include '../conn.php';

// Redirect if already logged in
if (isset($_SESSION['center_logged_in']) && $_SESSION['center_logged_in'] === true) {
    header("Location: index.php");
    exit();
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login'])) {
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $password = $_POST['password'];
        
        // Use prepared statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT id, center_name, owner_name, username, password, phone, email, center_status, payment_status FROM center_details WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // Check if center is active
            if ($row['center_status'] !== 'Active') {
                $error = "Your center account is not active. Please contact administrator.";
            } 
            // Check if payment is completed
            elseif ($row['payment_status'] !== 'Paid') {
                $error = "Your registration payment is pending. Please complete the payment to access your account.";
            }
            // Verify password (assuming passwords are hashed with password_hash)
            elseif (password_verify($password, $row['password'])) {
                // Set session variables
                $_SESSION['center_logged_in'] = true;
                $_SESSION['center_id'] = $row['id'];
                $_SESSION['center_name'] = $row['center_name'];
                $_SESSION['owner_name'] = $row['owner_name'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['phone'] = $row['phone'];
                $_SESSION['email'] = $row['email'];
                
                // Redirect to dashboard
                header("Location: index.php");
                exit();
            } else {
                $error = "Invalid username or password!";
            }
        } else {
            $error = "Invalid username or password!";
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
    <title>Center Login - Education Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background Elements */
        .bg-bubbles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .bg-bubbles span {
            position: absolute;
            display: block;
            list-style: none;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.15);
            bottom: -160px;
            animation: animate 20s linear infinite;
        }

        .bg-bubbles span:nth-child(1) {
            left: 25%;
            width: 80px;
            height: 80px;
            animation-delay: 0s;
        }

        .bg-bubbles span:nth-child(2) {
            left: 10%;
            width: 40px;
            height: 40px;
            animation-delay: 2s;
            animation-duration: 12s;
        }

        .bg-bubbles span:nth-child(3) {
            left: 70%;
            width: 60px;
            height: 60px;
            animation-delay: 4s;
        }

        .bg-bubbles span:nth-child(4) {
            left: 40%;
            width: 50px;
            height: 50px;
            animation-delay: 0s;
            animation-duration: 18s;
        }

        .bg-bubbles span:nth-child(5) {
            left: 65%;
            width: 70px;
            height: 70px;
            animation-delay: 0s;
        }

        .bg-bubbles span:nth-child(6) {
            left: 75%;
            width: 45px;
            height: 45px;
            animation-delay: 3s;
        }

        .bg-bubbles span:nth-child(7) {
            left: 35%;
            width: 90px;
            height: 90px;
            animation-delay: 7s;
        }

        .bg-bubbles span:nth-child(8) {
            left: 50%;
            width: 55px;
            height: 55px;
            animation-delay: 15s;
            animation-duration: 45s;
        }

        .bg-bubbles span:nth-child(9) {
            left: 20%;
            width: 35px;
            height: 35px;
            animation-delay: 2s;
            animation-duration: 35s;
        }

        .bg-bubbles span:nth-child(10) {
            left: 85%;
            width: 120px;
            height: 120px;
            animation-delay: 0s;
            animation-duration: 11s;
        }

        @keyframes animate {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 1;
                border-radius: 0;
            }
            100% {
                transform: translateY(-1200px) rotate(720deg);
                opacity: 0;
                border-radius: 50%;
            }
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            z-index: 1;
            position: relative;
            animation: fadeInUp 0.8s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            padding: 50px 40px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 40px 80px rgba(0, 0, 0, 0.4);
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-header .logo {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }

        .login-header .logo i {
            font-size: 48px;
            color: white;
        }

        .login-header h1 {
            color: #333;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .login-header p {
            color: #666;
            font-size: 15px;
            font-weight: 400;
        }

        .input-group {
            margin-bottom: 25px;
            position: relative;
        }

        .input-group label {
            display: block;
            color: #555;
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 14px;
            letter-spacing: 0.5px;
        }

        .input-group label i {
            margin-right: 8px;
            color: #667eea;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 18px;
            transition: color 0.3s ease;
        }

        .input-wrapper .toggle-password {
            left: auto;
            right: 15px;
            cursor: pointer;
            z-index: 10;
        }

        .input-wrapper input {
            width: 100%;
            padding: 15px 20px 15px 50px;
            border: 2px solid #e1e1e1;
            border-radius: 15px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: white;
            color: #333;
            font-weight: 500;
        }

        .input-wrapper input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }

        .input-wrapper input:focus + i {
            color: #667eea;
        }

        .input-wrapper input::placeholder {
            color: #bbb;
            font-weight: 400;
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #666;
            cursor: pointer;
        }

        .remember input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #667eea;
            cursor: pointer;
        }

        .forgot-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .forgot-link:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .login-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 15px;
            color: white;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(102, 126, 234, 0.4);
        }

        .login-btn i {
            font-size: 18px;
            transition: transform 0.3s ease;
        }

        .login-btn:hover i {
            transform: translateX(5px);
        }

        .error-message {
            background: #fee;
            border: 1px solid #fcc;
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #e74c3c;
            font-size: 14px;
            font-weight: 500;
            animation: shake 0.5s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        .error-message i {
            font-size: 20px;
        }

        .success-message {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #155724;
            font-size: 14px;
            font-weight: 500;
        }

        .success-message i {
            font-size: 20px;
        }

        .login-footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 14px;
        }

        .login-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-footer a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        /* Info Box for Demo */
        .demo-info {
            background: linear-gradient(135deg, #667eea10 0%, #764ba210 100%);
            border-radius: 12px;
            padding: 15px;
            margin-top: 20px;
            border: 1px dashed #667eea;
            font-size: 13px;
            color: #555;
        }

        .demo-info p {
            margin: 5px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .demo-info i {
            color: #667eea;
            width: 20px;
        }

        /* Loading Animation */
        .btn-loading {
            position: relative;
            pointer-events: none;
            opacity: 0.8;
        }

        .btn-loading i {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 480px) {
            .login-card {
                padding: 40px 25px;
            }

            .login-header h1 {
                font-size: 24px;
            }

            .login-header p {
                font-size: 14px;
            }

            .input-wrapper input {
                padding: 13px 15px 13px 45px;
            }
        }

        /* Tooltip */
        .tooltip {
            position: relative;
            display: inline-block;
        }

        .tooltip .tooltip-text {
            visibility: hidden;
            width: 200px;
            background: #333;
            color: white;
            text-align: center;
            border-radius: 6px;
            padding: 8px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            margin-left: -100px;
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 12px;
        }

        .tooltip:hover .tooltip-text {
            visibility: visible;
            opacity: 1;
        }

        /* Center Code Display */
        .center-code-hint {
            text-align: center;
            margin-top: 15px;
            font-size: 13px;
            color: #888;
        }

        .center-code-hint i {
            color: #667eea;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="bg-bubbles">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </div>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <h1>Center Login</h1>
                <p>Enter your username and password to access your dashboard</p>
            </div>

            <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $error; ?></span>
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['registered']) && $_GET['registered'] == 1): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <span>Registration successful! Please login with your username and password.</span>
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['password_changed']) && $_GET['password_changed'] == 1): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <span>Password changed successfully! Please login with your new password.</span>
            </div>
            <?php endif; ?>

            <form method="POST" action="" id="loginForm">
                <div class="input-group">
                    <label for="username">
                        <i class="fas fa-user-circle"></i> Username
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               placeholder="Enter your username"
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                               required 
                               autocomplete="off"
                               autofocus>
                    </div>
                </div>

                <div class="input-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               placeholder="Enter your password"
                               required>
                        <i class="fas fa-eye toggle-password" onclick="togglePassword(this)"></i>
                    </div>
                </div>

                <div class="remember-forgot">
                    <label class="remember">
                        <input type="checkbox" name="remember" id="remember">
                        <span>Remember me</span>
                    </label>
                    <a href="forgot_password.php" class="forgot-link tooltip">
                        Forgot Password?
                        <span class="tooltip-text">Reset your password</span>
                    </a>
                </div>

                <button type="submit" name="login" class="login-btn" id="loginBtn">
                    <span>Login to Dashboard</span>
                    <i class="fas fa-arrow-right"></i>
                </button>

                <!-- Demo Info (Remove in production) -->
                <div class="demo-info">
                    <p><i class="fas fa-info-circle"></i> <strong>Demo Credentials:</strong></p>
                    <p><i class="fas fa-user"></i> Username: <strong>center_admin</strong></p>
                    <p><i class="fas fa-lock"></i> Password: <strong>Center@123</strong></p>
                    <p><i class="fas fa-building"></i> Center Code: <strong>CENT001</strong></p>
                </div>

                <div class="center-code-hint">
                    <i class="fas fa-hashtag"></i> 
                    Your center code is provided during registration
                </div>

                <div class="login-footer">
                    <p>Don't have an account? <a href="center_register.php">Register your center</a></p>
                    <p style="margin-top: 10px; font-size: 12px;">
                        <a href="admin_login.php">Admin Login</a> | 
                        <a href="student_login.php">Student Login</a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword(icon) {
            const input = icon.previousElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Form submission with loading animation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;

            if (!username || !password) {
                e.preventDefault();
                alert('Please fill in both username and password fields');
                return;
            }

            // Add loading animation to button
            const loginBtn = document.getElementById('loginBtn');
            loginBtn.classList.add('btn-loading');
            loginBtn.innerHTML = '<span>Logging in...</span><i class="fas fa-spinner"></i>';
        });

        // Remember me functionality
        document.addEventListener('DOMContentLoaded', function() {
            const rememberCheckbox = document.getElementById('remember');
            const savedUsername = localStorage.getItem('rememberedUsername');
            
            if (savedUsername && rememberCheckbox) {
                document.getElementById('username').value = savedUsername;
                rememberCheckbox.checked = true;
            }
        });

        document.getElementById('loginForm').addEventListener('submit', function() {
            const rememberCheckbox = document.getElementById('remember');
            const username = document.getElementById('username').value;
            
            if (rememberCheckbox.checked) {
                localStorage.setItem('rememberedUsername', username);
            } else {
                localStorage.removeItem('rememberedUsername');
            }
        });

        // Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        // Add floating label effect
        const inputs = document.querySelectorAll('.input-wrapper input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.querySelector('i:first-child').style.color = '#667eea';
            });
            
            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.parentElement.querySelector('i:first-child').style.color = '#999';
                }
            });
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl + U to focus on username
            if (e.ctrlKey && e.key === 'u') {
                e.preventDefault();
                document.getElementById('username').focus();
            }
            // Ctrl + P to focus on password
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                document.getElementById('password').focus();
            }
        });

        // Clear URL parameters on load (for security)
        if (window.location.search.includes('registered') || window.location.search.includes('password_changed')) {
            setTimeout(() => {
                window.history.replaceState({}, document.title, window.location.pathname);
            }, 5000);
        }
    </script>
</body>
</html>