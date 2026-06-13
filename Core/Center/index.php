<?php
session_start();
include '../conn.php';

// Check if center is logged in
if (!isset($_SESSION['center_logged_in']) || $_SESSION['center_logged_in'] !== true) {
    header("Location: member_login.php");
    exit();
}

$center_id = $_SESSION['center_id'];
$center_name = $_SESSION['center_name'];
$phone = $_SESSION['phone'];

// Get center details from database
$stmt = $conn->prepare("SELECT * FROM center_details WHERE id = ?");
$stmt->bind_param("i", $center_id);
$stmt->execute();
$result = $stmt->get_result();
$center_data = $result->fetch_assoc();
$stmt->close();

// Get statistics for dashboard - CORRECTED QUERIES
// Total students for this center
$total_students_query = "SELECT COUNT(*) as total_students FROM onlinestudents WHERE study_center = ?";
$stmt_total = $conn->prepare($total_students_query);
$stmt_total->bind_param("s", $center_name);
$stmt_total->execute();
$total_result = $stmt_total->get_result()->fetch_assoc();
$total_students = $total_result['total_students'] ?? 0;
$stmt_total->close();

// Total fees collected for this center (only paid)
$total_fees_query = "SELECT SUM(reg_amount) as total_fees FROM onlinestudents WHERE study_center = ? AND payment_status = 'Paid'";
$stmt_fees = $conn->prepare($total_fees_query);
$stmt_fees->bind_param("s", $center_name);
$stmt_fees->execute();
$fees_result = $stmt_fees->get_result()->fetch_assoc();
$total_fees = $fees_result['total_fees'] ?? 0;
$stmt_fees->close();

// This month's new students
$this_month_query = "SELECT COUNT(*) as this_month FROM onlinestudents WHERE study_center = ? AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())";
$stmt_month = $conn->prepare($this_month_query);
$stmt_month->bind_param("s", $center_name);
$stmt_month->execute();
$month_result = $stmt_month->get_result()->fetch_assoc();
$this_month = $month_result['this_month'] ?? 0;
$stmt_month->close();

// Active courses count
$courses_query = "SELECT COUNT(DISTINCT course_name) as course_count FROM onlinestudents WHERE study_center = ?";
$stmt_courses = $conn->prepare($courses_query);
$stmt_courses->bind_param("s", $center_name);
$stmt_courses->execute();
$courses_result = $stmt_courses->get_result()->fetch_assoc();
$course_count = $courses_result['course_count'] ?? 0;
$stmt_courses->close();

// Get recent 5 students
$recent_students_query = "SELECT id, name, course_name, created_at, payment_status 
                          FROM onlinestudents 
                          WHERE study_center = ? 
                          ORDER BY created_at DESC 
                          LIMIT 5";
$stmt_recent = $conn->prepare($recent_students_query);
$stmt_recent->bind_param("s", $center_name);
$stmt_recent->execute();
$recent_students = $stmt_recent->get_result();
$stmt_recent->close();

// Handle password change via modal
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $password_error = '';
    $password_success = '';
    
    // Validate inputs
    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $password_error = "All password fields are required!";
    } elseif ($new_password !== $confirm_password) {
        $password_error = "New password and confirm password do not match!";
    } elseif (strlen($new_password) < 6) {
        $password_error = "New password must be at least 6 characters long!";
    } else {
        // Verify old password
        $check_stmt = $conn->prepare("SELECT password FROM center_details WHERE id = ?");
        $check_stmt->bind_param("i", $center_id);
        $check_stmt->execute();
        $check_stmt->store_result();
        $check_stmt->bind_result($db_password);
        $check_stmt->fetch();
        
        if (password_verify($old_password, $db_password)) {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE center_details SET password = ? WHERE id = ?");
            $update_stmt->bind_param("si", $hashed_password, $center_id);
            
            if ($update_stmt->execute()) {
                $password_success = "Password changed successfully!";
                // Clear form fields
                echo '<script>document.getElementById("old_password").value = ""; document.getElementById("new_password").value = ""; document.getElementById("confirm_password").value = "";</script>';
            } else {
                $password_error = "Failed to update password. Please try again.";
            }
            $update_stmt->close();
        } else {
            $password_error = "Old password is incorrect!";
        }
        $check_stmt->close();
    }
}
include 'header.php';
?>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

      

        .welcome-section h1 {
            color: #f1ff1f;
            font-size: 28px;
            margin-bottom: 5px;
        }

        .welcome-section p {
            color: #ffffff;
            font-size: 16px;
        }

      

        .action-btn, .logout-btn, .password-btn {
            padding: 10px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            font-size: 14px;
        }

        .action-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .logout-btn {
            background: #f1f1f1;
            color: #666;
        }

        .password-btn {
            background: #4CAF50;
            color: white;
        }

        .action-btn:hover, .password-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .logout-btn:hover {
            background: #e74c3c;
            color: white;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }

        .icon-students { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .icon-fees { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .icon-courses { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .icon-monthly { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .trend {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            color: #2ecc71;
            margin-top: 10px;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
        }

        .recent-students, .center-info {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            font-size: 20px;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f1f1f1;
        }

        .student-table {
            width: 100%;
            border-collapse: collapse;
        }

        .student-table th {
            text-align: left;
            padding: 12px 15px;
            background: #f8f9fa;
            color: #666;
            font-weight: 600;
            border-bottom: 2px solid #f1f1f1;
        }

        .student-table td {
            padding: 15px;
            border-bottom: 1px solid #f1f1f1;
        }

        .student-table tr:hover {
            background: #f8f9fa;
        }

        .payment-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-paid {
            background: #d4edda;
            color: #155724;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .info-grid {
            display: grid;
            gap: 15px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .info-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }

        .info-content h4 {
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .info-content p {
            color: #333;
            font-weight: 600;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 30px;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f1f1;
        }

        .modal-header h2 {
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
            transition: color 0.3s;
        }

        .close-modal:hover {
            color: #e74c3c;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .password-toggle {
            position: relative;
        }

        .password-toggle i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .header-actions {
                width: 100%;
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>

    <div class="dashboard-container">
        <!-- Header Section -->
        <div class="header">
            <div class="welcome-section">
                <h1>Welcome, <?php echo htmlspecialchars($center_name); ?>!</h1>
                <p>Center Management Dashboard</p>
            </div>
            <div class="header-actions">
                <button class="password-btn" onclick="openPasswordModal()">
                    <i class="fas fa-key"></i> Change Password
                </button>
                <a href="my_profile.php" class="action-btn">
                    <i class="fas fa-user-circle"></i> My Profile
                </a>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo $total_students; ?></div>
                        <div class="stat-label">Total Students</div>
                    </div>
                    <div class="stat-icon icon-students">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="trend">
                    <i class="fas fa-arrow-up"></i>
                    <?php echo $this_month; ?> new this month
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value">₹<?php echo number_format($total_fees, 2); ?></div>
                        <div class="stat-label">Total Fees Collected</div>
                    </div>
                    <div class="stat-icon icon-fees">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                </div>
                <div class="trend">
                    <i class="fas fa-chart-line"></i> All time collection
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo $course_count; ?></div>
                        <div class="stat-label">Active Courses</div>
                    </div>
                    <div class="stat-icon icon-courses">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                </div>
                <div class="trend">
                    <i class="fas fa-book"></i> Running courses
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo $this_month; ?></div>
                        <div class="stat-label">New Students This Month</div>
                    </div>
                    <div class="stat-icon icon-monthly">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
                <div class="trend">
                    <i class="fas fa-chart-bar"></i> Monthly performance
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="content-grid">
            <!-- Recent Students -->
            <div class="recent-students">
                <h2 class="section-title">Recent Students</h2>
                <?php if ($recent_students->num_rows > 0): ?>
                <table class="student-table">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Course</th>
                            <th>Join Date</th>
                            <th>Payment Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($student = $recent_students->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                            <td><?php echo htmlspecialchars($student['course_name']); ?></td>
                            <td><?php echo date('d M, Y', strtotime($student['created_at'])); ?></td>
                            <td>
                                <span class="payment-status status-<?php echo strtolower($student['payment_status']); ?>">
                                    <?php echo $student['payment_status']; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #666;">
                    <i class="fas fa-users" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
                    <p>No students found. Add your first student to get started!</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Center Information -->
            <div class="center-info">
                <h2 class="section-title">Center Information</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <div class="info-content">
                            <h4>Center ID</h4>
                            <p><?php echo htmlspecialchars($center_id); ?></p>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="info-content">
                            <h4>Center Name</h4>
                            <p><?php echo htmlspecialchars($center_name); ?></p>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="info-content">
                            <h4>Phone Number</h4>
                            <p><?php echo htmlspecialchars($phone); ?></p>
                        </div>
                    </div>

                    <?php if (isset($center_data['email'])): ?>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="info-content">
                            <h4>Email</h4>
                            <p><?php echo htmlspecialchars($center_data['email']); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions">
                    <a href="student.php" class="action-btn">
                        <i class="fas fa-users"></i> View Students
                    </a>
                    <a href="Add_Student.php" class="action-btn">
                        <i class="fas fa-user-plus"></i> Add Student
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Password Change Modal -->
    <div id="passwordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-key"></i> Change Password</h2>
                <button class="close-modal" onclick="closePasswordModal()">&times;</button>
            </div>
            
            <?php if (isset($password_error) && $password_error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $password_error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($password_success) && $password_success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $password_success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="passwordForm">
                <div class="form-group">
                    <label for="old_password">Old Password</label>
                    <div class="password-toggle">
                        <input type="password" class="form-control" id="old_password" name="old_password" required>
                        <i class="fas fa-eye" onclick="togglePassword('old_password', this)"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <div class="password-toggle">
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                        <i class="fas fa-eye" onclick="togglePassword('new_password', this)"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <div class="password-toggle">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        <i class="fas fa-eye" onclick="togglePassword('confirm_password', this)"></i>
                    </div>
                </div>

                <button type="submit" name="change_password" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-key"></i> Change Password
                </button>
            </form>
        </div>
    </div>

    <script>
        // Modal Functions
        function openPasswordModal() {
            document.getElementById('passwordModal').style.display = 'block';
        }

        function closePasswordModal() {
            document.getElementById('passwordModal').style.display = 'none';
            // Clear form fields
            document.getElementById('old_password').value = '';
            document.getElementById('new_password').value = '';
            document.getElementById('confirm_password').value = '';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('passwordModal');
            if (event.target == modal) {
                closePasswordModal();
            }
        }

        // Toggle password visibility
        function togglePassword(inputId, icon) {
            const input = document.getElementById(inputId);
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

        // Password strength indicator
        const newPassword = document.getElementById('new_password');
        if (newPassword) {
            newPassword.addEventListener('input', function() {
                const strength = checkPasswordStrength(this.value);
                updatePasswordStrength(strength);
            });
        }

        function checkPasswordStrength(password) {
            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            return strength;
        }

        function updatePasswordStrength(strength) {
            // Remove any existing strength indicator
            let existingIndicator = document.getElementById('password-strength');
            if (existingIndicator) {
                existingIndicator.remove();
            }

            if (strength === 0) return;

            const indicator = document.createElement('div');
            indicator.id = 'password-strength';
            indicator.style.marginTop = '10px';
            indicator.style.padding = '8px 12px';
            indicator.style.borderRadius = '5px';
            indicator.style.fontSize = '14px';
            indicator.style.fontWeight = '600';

            let message = '';
            let color = '';

            switch(strength) {
                case 1:
                    message = 'Very Weak';
                    color = '#e74c3c';
                    break;
                case 2:
                    message = 'Weak';
                    color = '#e67e22';
                    break;
                case 3:
                    message = 'Good';
                    color = '#f1c40f';
                    break;
                case 4:
                    message = 'Strong';
                    color = '#2ecc71';
                    break;
            }

            indicator.textContent = `Password Strength: ${message}`;
            indicator.style.backgroundColor = color + '20'; // 20% opacity
            indicator.style.color = color;
            indicator.style.border = `1px solid ${color}40`; // 40% opacity

            newPassword.parentNode.appendChild(indicator);
        }

        // Form validation
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const newPass = document.getElementById('new_password').value;
            const confirmPass = document.getElementById('confirm_password').value;
            
            if (newPass !== confirmPass) {
                e.preventDefault();
                alert('New password and confirm password do not match!');
                return false;
            }
            
            if (newPass.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return false;
            }
            
            return true;
        });
    </script>
<?php include 'footer.php'; ?>