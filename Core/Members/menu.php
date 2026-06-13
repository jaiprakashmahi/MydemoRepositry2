<?php
// menu.php - DO NOT start session here
// The session should already be started in the main file

// Check if session variables exist before using them
$user_type = '';
$user_name = '';
$member_type = '';
$center_name = '';

if (isset($_SESSION['center_logged_in'])) {
    $user_type = 'center';
    $center_name = $_SESSION['center_name'] ?? 'Center';
    $member_type = 'Center Login';
    $user_name = $center_name;
} elseif (isset($_SESSION['member_logged_in'])) {
    $user_type = 'member';
    $user_name = $_SESSION['applicant_name'] ?? 'Member';
    $member_type = $_SESSION['member_type'] ?? '';
} else {
    // If not logged in, show minimal menu or redirect
    // For now, just set default values
    $user_type = '';
    $user_name = 'Guest';
    $member_type = '';
}

$show_member_links = ($user_type === 'member');
$show_center_links = ($user_type === 'center');
?>

<!-- Your HTML menu code here... -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sharnay Institute - Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
    
    <style>
        /* Additional inline styles for dashboard */
        .dashboard-container {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border-left: 5px solid;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }
        
        .stat-card.primary {
            border-left-color: #4e73df;
        }
        
        .stat-card.success {
            border-left-color: #1cc88a;
        }
        
        .stat-card.info {
            border-left-color: #36b9cc;
        }
        
        .stat-card.warning {
            border-left-color: #f6c23e;
        }
        
        .welcome-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .welcome-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 20px 20px;
            opacity: 0.1;
        }
        
        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        
        .centers-list {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            max-height: 400px;
            overflow-y: auto;
        }
        
        .recent-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(78, 115, 223, 0.05);
        }
        
        .badge {
            padding: 0.35em 0.65em;
            font-weight: 500;
        }
        
        .btn-action {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .btn-action:hover {
            transform: scale(1.1);
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #e74a3b;
            color: white;
            font-size: 0.75rem;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Custom animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease forwards;
        }
        
        /* Pulse animation for notifications */
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(231, 74, 59, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(231, 74, 59, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(231, 74, 59, 0);
            }
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="index.php" class="d-block">
    <img src=".././images/logo.png" alt="Sharnay Institute" class="logo">
</a>
            </div>
            
            <nav class="sidebar-menu">
                <ul>
                    <li>
                        <a href="index.php" class="active">
                            <i class="bi bi-speedometer2"></i>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </li>
                    
                    <?php if ($show_center_links): ?>
                        <!-- Center Links -->
                        <li>
                            <a href="#" class="has-arrow">
                                <i class="bi bi-people"></i>
                                <span class="nav-text">Students</span>
                            </a>
                            <ul class="sidebar-submenu">
                                <li><a href="student.php">All Students</a></li>
                                <li><a href="add-student.php">Add New Student</a></li>
                            </ul>
                        </li>
                        
                        <li>
                            <a href="#" class="has-arrow">
                                <i class="bi bi-book"></i>
                                <span class="nav-text">Courses</span>
                            </a>
                            <ul class="sidebar-submenu">
                                <li><a href="courses.php">All Courses</a></li>
                            </ul>
                        </li>
                        
                    <?php elseif ($show_member_links): ?>
                        <!-- Member Links -->
                        <li>
                            <a href="students.php">
                                <i class="bi bi-people"></i>
                                <span class="nav-text">Students</span>
                            </a>
                        </li>
                        
                        <li>
                            <a href="create-center.php">
                                <i class="bi bi-building"></i>
                                <span class="nav-text">Centers</span>
                            </a>
                        </li>
                        
                        <li>
                            <a href="payments.php">
                                <i class="bi bi-credit-card"></i>
                                <span class="nav-text">Payments</span>
                            </a>
                        </li>
                        
                        <li>
                            <a href="reports.php">
                                <i class="bi bi-graph-up"></i>
                                <span class="nav-text">Reports</span>
                            </a>
                        </li>
                        
                        <li>
                            <a href="profile.php">
                                <i class="bi bi-person-circle"></i>
                                <span class="nav-text">Profile</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <p><strong>Sharnay Institute</strong></p>
                <p class="fs-12">Education Management System</p>
                <p class="mt-2">Made with <span class="heart">❤️</span> by RKV</p>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navigation -->
            <header class="top-nav">
                <button class="sidebar-toggle d-lg-none">
                    <i class="bi bi-list"></i>
                </button>
                
                <div class="breadcrumb">
                    Dashboard
                    <?php if ($show_member_links && isset($_SESSION['working_state_name'])): ?>
                        <small class="text-muted">| <?php echo htmlspecialchars($_SESSION['working_state_name']); ?></small>
                    <?php endif; ?>
                </div>
                
                <div class="user-menu">
                    <div class="notification-bell position-relative">
                        <i class="bi bi-bell"></i>
                        <span class="notification-badge">3</span>
                    </div>
                    
                    <div class="dropdown">
                        <div class="user-profile dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-avatar">
                                <img src="../images/logo.png" alt="User Avatar">
                            </div>
                            <div class="user-info d-none d-md-block">
                                <span class="user-name"><?php echo htmlspecialchars($user_name); ?></span>
                                <span class="user-role"><?php echo htmlspecialchars($member_type); ?></span>
                            </div>
                            <i class="bi bi-chevron-down ms-2"></i>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>My Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <?php if ($show_center_links): ?>
                                    <a class="dropdown-item" href="center_logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
                                <?php else: ?>
                                    <a class="dropdown-item" href="member_logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
                                <?php endif; ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>
            
            <!-- Content will be inserted here by individual pages -->
            <div class="content-wrapper">