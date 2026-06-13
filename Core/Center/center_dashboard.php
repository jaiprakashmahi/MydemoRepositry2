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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Center Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: #333;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo img {
            height: 50px;
            width: auto;
        }

        .logo h1 {
            font-size: 24px;
            font-weight: 600;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-details {
            text-align: right;
        }

        .user-name {
            font-weight: 600;
            font-size: 18px;
        }

        .user-role {
            font-size: 14px;
            opacity: 0.9;
        }

        .logout-btn {
            background-color: rgba(255,255,255,0.2);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logout-btn:hover {
            background-color: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }

        .container {
            display: flex;
            min-height: calc(100vh - 90px);
        }

        .sidebar {
            width: 250px;
            background-color: white;
            padding: 30px 20px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
        }

        .sidebar-menu {
            list-style: none;
        }

        .menu-item {
            margin-bottom: 10px;
        }

        .menu-item a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            color: #555;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .menu-item a:hover {
            background-color: #f0f2f5;
            color: #667eea;
            transform: translateX(5px);
        }

        .menu-item.active a {
            background-color: #667eea;
            color: white;
        }

        .menu-item i {
            width: 20px;
            text-align: center;
        }

        .main-content {
            flex: 1;
            padding: 30px;
        }

        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }

        .card-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 24px;
            color: white;
        }

        .card-icon.students { background: linear-gradient(45deg, #FF6B6B, #FF8E53); }
        .card-icon.attendance { background: linear-gradient(45deg, #4ECDC4, #44A08D); }
        .card-icon.performance { background: linear-gradient(45deg, #FFD166, #FFB347); }
        .card-icon.fees { background: linear-gradient(45deg, #06D6A0, #1B9AAA); }

        .card h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .card .number {
            font-size: 32px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        .card .trend {
            font-size: 14px;
            color: #06D6A0;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .card .trend.down {
            color: #FF6B6B;
        }

        .welcome-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 20px;
            margin-bottom: 40px;
            text-align: center;
        }

        .welcome-section h2 {
            font-size: 36px;
            margin-bottom: 10px;
        }

        .welcome-section p {
            font-size: 18px;
            opacity: 0.9;
        }

        .center-details {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .center-details h3 {
            color: #667eea;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f2f5;
            font-size: 22px;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 10px;
        }

        .detail-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(45deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }

        .detail-content h4 {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }

        .detail-content p {
            font-size: 16px;
            font-weight: 500;
            color: #333;
        }

        .quick-actions {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .action-btn {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .action-btn.secondary {
            background: #f0f2f5;
            color: #333;
        }

        .action-btn.secondary:hover {
            background: #e4e6e9;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                order: 2;
            }
            
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .user-info {
                flex-direction: column;
            }
            
            .dashboard-cards {
                grid-template-columns: 1fr;
            }
            
            .details-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <img src="../images/logo.png" alt="Logo">
            <h1>Center Management System</h1>
        </div>
        <div class="user-info">
            <div class="user-details">
                <div class="user-name"><?php echo htmlspecialchars($center_name); ?></div>
                <div class="user-role">Center Login</div>
            </div>
           <button class="logout-btn" id="logoutBtn">
    <i class="fas fa-sign-out-alt"></i> Logout
</button>
        </div>
    </div>

    <div class="container">
        <nav class="sidebar">
            <ul class="sidebar-menu">
                <li class="menu-item active">
                    <a href="center_dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="student.php">
                        <i class="fas fa-users"></i>
                        <span>Students</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="#">
                        <i class="fas fa-calendar-check"></i>
                        <span>Attendance</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="#">
                        <i class="fas fa-chart-line"></i>
                        <span>Performance</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="#">
                        <i class="fas fa-rupee-sign"></i>
                        <span>Fee Management</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="#">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>
        </nav>

        <main class="main-content">
            <div class="welcome-section">
                <h2>Welcome, <?php echo htmlspecialchars($center_name); ?>!</h2>
                <p>Center Management Dashboard</p>
            </div>

            <div class="dashboard-cards">
                <div class="card">
                    <div class="card-icon students">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Total Students</h3>
                    <div class="number">156</div>
                    <div class="trend">
                        <i class="fas fa-arrow-up"></i> 12% from last month
                    </div>
                </div>

                <div class="card">
                    <div class="card-icon attendance">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3>Today's Attendance</h3>
                    <div class="number">89%</div>
                    <div class="trend">
                        <i class="fas fa-arrow-up"></i> 5% from yesterday
                    </div>
                </div>

                <div class="card">
                    <div class="card-icon performance">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Average Performance</h3>
                    <div class="number">78%</div>
                    <div class="trend down">
                        <i class="fas fa-arrow-down"></i> 2% from last month
                    </div>
                </div>

                <div class="card">
                    <div class="card-icon fees">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                    <h3>Fee Collection</h3>
                    <div class="number">₹84,500</div>
                    <div class="trend">
                        <i class="fas fa-arrow-up"></i> 8% from last month
                    </div>
                </div>
            </div>

            <div class="center-details">
                <h3>Center Information</h3>
                <div class="details-grid">
                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <div class="detail-content">
                            <h4>Center ID</h4>
                            <p><?php echo htmlspecialchars($center_id); ?></p>
                        </div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="detail-content">
                            <h4>Center Name</h4>
                            <p><?php echo htmlspecialchars($center_name); ?></p>
                        </div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="detail-content">
                            <h4>Phone Number</h4>
                            <p><?php echo htmlspecialchars($phone); ?></p>
                        </div>
                    </div>

                    <?php if (isset($center_data['address'])): ?>
                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="detail-content">
                            <h4>Address</h4>
                            <p><?php echo htmlspecialchars($center_data['address']); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($center_data['email'])): ?>
                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="detail-content">
                            <h4>Email</h4>
                            <p><?php echo htmlspecialchars($center_data['email']); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($center_data['established_date'])): ?>
                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="detail-content">
                            <h4>Established Date</h4>
                            <p><?php echo date('F d, Y', strtotime($center_data['established_date'])); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="quick-actions">
                    <button class="action-btn">
                        <i class="fas fa-user-plus"></i> Add New Student
                    </button>
                    <button class="action-btn secondary">
                        <i class="fas fa-print"></i> Print Report
                    </button>
                    <button class="action-btn secondary">
                        <i class="fas fa-download"></i> Export Data
                    </button>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Update current time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit',
                second: '2-digit',
                hour12: true 
            });
            const dateString = now.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            document.getElementById('current-time').innerHTML = 
                `<i class="far fa-clock"></i> ${timeString} | ${dateString}`;
        }

        // Update time every second
        setInterval(updateTime, 1000);
        updateTime();

        // Logout confirmation
        document.querySelector('.logout-btn').addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to logout?')) {
                e.preventDefault();
            }
        });
document.getElementById('logoutBtn').addEventListener('click', function(e) {
    if (confirm('Are you sure you want to logout?')) {
        // Redirect to logout handler
        window.location.href = 'logout.php';
    }
    // If user cancels, do nothing (no redirect)
});



        // Menu active state
        document.querySelectorAll('.menu-item a').forEach(link => {
            link.addEventListener('click', function() {
                document.querySelectorAll('.menu-item').forEach(item => {
                    item.classList.remove('active');
                });
                this.parentElement.classList.add('active');
            });
        });
    </script>
</body>
</html>