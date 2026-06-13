<?php
session_start();
include '../conn.php';

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: Login.php');
    exit();
}

// Get student data
$student_id = $_SESSION['student_id'];
$query = "SELECT * FROM onlinestudents WHERE id = '$student_id'";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    session_destroy();
    header('Location: Login.php?error=invalid_session');
    exit();
}

$student = mysqli_fetch_assoc($result);

// Update last active time
$update_query = "UPDATE onlinestudents SET last_active = NOW() WHERE id = '$student_id'";
mysqli_query($conn, $update_query);

// Get notifications count
$notifications_query = "SELECT COUNT(*) as count FROM student_notifications WHERE student_id = '$student_id' AND is_read = 0";
$notifications_result = mysqli_query($conn, $notifications_query);
$notifications_data = mysqli_fetch_assoc($notifications_result);
$notifications_count = $notifications_data['count'];

// Get current page title
$page_title = isset($page_title) ? $page_title : 'Student Dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Sharnay Institute</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --sidebar-width: 250px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
        }
        
        /* Top Navigation Bar */
        .top-navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 0 20px;
            height: 70px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .navbar-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .institute-logo {
            background: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-size: 1.2rem;
        }
        
        .institute-info h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            line-height: 1.2;
        }
        
        .institute-info .study-center {
            font-size: 0.85rem;
            opacity: 0.9;
            margin: 0;
        }
        
        .navbar-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .student-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .student-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.3);
            object-fit: cover;
        }
        
        .student-name {
            font-weight: 600;
            font-size: 1rem;
        }
        
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 8px 20px;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
            color: white;
            transform: translateY(-2px);
        }
        
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }
        
        /* Sidebar */
        .sidebar {
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            color: white;
            width: var(--sidebar-width);
            position: fixed;
            top: 70px;
            left: 0;
            bottom: 0;
            overflow-y: auto;
            transition: transform 0.3s ease;
            z-index: 999;
            box-shadow: 2px 0 15px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 25px 20px;
            text-align: center;
            background: rgba(0,0,0,0.2);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid rgba(255,255,255,0.2);
            object-fit: cover;
            margin-bottom: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .sidebar-header h5 {
            font-weight: 600;
            margin: 0 0 5px 0;
            font-size: 1.1rem;
        }
        
        .sidebar-header p {
            font-size: 0.8rem;
            opacity: 0.8;
            margin: 0;
            line-height: 1.4;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        
        .sidebar-menu a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 15px 20px;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .sidebar-menu a:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            padding-left: 25px;
        }
        
        .sidebar-menu a.active {
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.2) 0%, rgba(118, 75, 162, 0.2) 100%);
            color: white;
            border-left: 4px solid var(--primary-color);
        }
        
        .sidebar-menu i {
            width: 25px;
            text-align: center;
            margin-right: 12px;
            font-size: 1.1rem;
        }
        
        .menu-badge {
            margin-left: auto;
            background: #e74c3c;
            color: white;
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 10px;
            font-weight: 600;
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: 70px;
            padding: 30px;
            min-height: calc(100vh - 140px);
            transition: margin-left 0.3s ease;
        }
        
        /* Course Badge */
        .course-badge {
            display: inline-block;
            background: linear-gradient(135deg, #00b09b 0%, #96c93d 100%);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 5px;
        }
        
        /* Card Styles */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            overflow: hidden;
            height: 100%;
        }
        
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .card-icon {
            font-size: 2.8rem;
            color: var(--primary-color);
            margin-bottom: 20px;
            display: inline-block;
            padding: 15px;
            background: rgba(102, 126, 234, 0.1);
            border-radius: 12px;
        }
        
        /* Page Header */
        .page-header {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            border-left: 5px solid var(--primary-color);
        }
        
        /* Mobile Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .student-name {
                display: none;
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                padding: 20px;
            }
            
            .page-header {
                padding: 20px;
            }
            
            .navbar-left .institute-info h2 {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Top Navigation Bar -->
    <nav class="top-navbar">
        <div class="navbar-left">
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="institute-logo">
                <i class="fas fa-graduation-cap"></i>
            </div>
            
            <div class="institute-info">
                <h2>Sharnay Institute</h2>
                <p class="study-center">
                    <i class="fas fa-map-marker-alt me-1"></i>
                    <?php echo htmlspecialchars($student['study_center']); ?>
                </p>
            </div>
        </div>
        
        <div class="navbar-right">
            <div class="student-info">
                <?php
                $avatar_path = !empty($student['photo']) ? "../uploads/students/photos/" . $student['photo'] : "https://ui-avatars.com/api/?name=" . urlencode($student['name']) . "&background=667eea&color=fff&size=128";
                ?>
                <img src="<?php echo $avatar_path; ?>" 
                     alt="Student Avatar" 
                     class="student-avatar"
                     onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($student['name']); ?>&background=667eea&color=fff&size=128'">
                <span class="student-name d-none d-md-block"><?php echo htmlspecialchars($student['name']); ?></span>
            </div>
            
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span class="d-none d-md-inline">Logout</span>
            </a>
        </div>
    </nav>
    
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>