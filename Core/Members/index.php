<?php
session_start();
include '../conn.php';

if (!isset($_SESSION['member_logged_in'])) {
    header("Location: member_login.php?error=access_denied");
    exit();
}

$member_id = $_SESSION['member_id'] ?? 0;
$member_type = $_SESSION['member_type'] ?? '';
$applicant_name = $_SESSION['applicant_name'] ?? '';
$unicode = $_SESSION['unicode'] ?? '';

// Get working area info
$working_state_name = $_SESSION['working_state_name'] ?? '';
$working_district_names = $_SESSION['working_district_names'] ?? '';
$working_block_names = $_SESSION['working_block_names'] ?? '';

// Initialize variables
$total_centers = 0;
$total_students = 0;
$total_revenue = 0;
$total_commission = 0;
$center_ids = [];
$center_names = [];

// Build WHERE clause based on member type
$where_conditions = [];
$params = [];
$param_types = "";

// FOR ALL MEMBER TYPES - Filter based on their working area
switch ($member_type) {
    case 'State Nodal':
        if (!empty($working_state_name)) {
            $where_conditions[] = "state = ?";
            $params[] = $working_state_name;
            $param_types .= "s";
        }
        break;
        
    case 'Zonal Manager':
    case 'District Coordinator':
        if (!empty($working_district_names)) {
            if (strpos($working_district_names, ', ') !== false) {
                $district_names_array = explode(', ', $working_district_names);
            } elseif (strpos($working_district_names, ',') !== false) {
                $district_names_array = explode(',', $working_district_names);
            } else {
                $district_names_array = [$working_district_names];
            }
            
            $placeholders = implode(',', array_fill(0, count($district_names_array), '?'));
            $where_conditions[] = "district IN ($placeholders)";
            $params = array_merge($params, $district_names_array);
            $param_types .= str_repeat('s', count($district_names_array));
        }
        break;
        
    case 'Block Coordinator':
        // For Block Coordinators, filter by state and district (since center_details doesn't have block column)
        if (!empty($working_state_name) && !empty($working_district_names)) {
            $where_conditions[] = "state = ?";
            $params[] = $working_state_name;
            $param_types .= "s";
            
            if (strpos($working_district_names, ', ') !== false) {
                $district_names_array = explode(', ', $working_district_names);
            } elseif (strpos($working_district_names, ',') !== false) {
                $district_names_array = explode(',', $working_district_names);
            } else {
                $district_names_array = [$working_district_names];
            }
            
            $placeholders = implode(',', array_fill(0, count($district_names_array), '?'));
            $where_conditions[] = "district IN ($placeholders)";
            $params = array_merge($params, $district_names_array);
            $param_types .= str_repeat('s', count($district_names_array));
        }
        break;
}

// Count centers based on working area
if (!empty($where_conditions)) {
    $where_sql = "WHERE " . implode(" AND ", $where_conditions);
    
    $stmt = $conn->prepare("SELECT COUNT(*), GROUP_CONCAT(id), GROUP_CONCAT(center_name) FROM center_details $where_sql");
    
    if (!empty($params)) {
        $stmt->bind_param($param_types, ...$params);
    }
    
    $stmt->execute();
    $stmt->bind_result($total_centers, $center_ids_str, $center_names_str);
    $stmt->fetch();
    $stmt->close();
    
    if (!empty($center_ids_str)) {
        $center_ids = explode(',', $center_ids_str);
    }
    if (!empty($center_names_str)) {
        $center_names = explode(',', $center_names_str);
    }
} else {
    $no_working_area = true;
}

// Get statistics if centers exist
if (!empty($center_ids)) {
    $placeholders = implode(',', array_fill(0, count($center_ids), '?'));
    $types = str_repeat('i', count($center_ids));
    
    // Total active students
    $student_stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM onlinestudents 
        WHERE center_id IN ($placeholders) 
        AND status = 'Active'
    ");
    $student_stmt->bind_param($types, ...$center_ids);
    $student_stmt->execute();
    $student_stmt->bind_result($total_students);
    $student_stmt->fetch();
    $student_stmt->close();
    
    // Total revenue
    $revenue_stmt = $conn->prepare("
        SELECT COALESCE(SUM(price), 0) 
        FROM onlinestudents 
        WHERE center_id IN ($placeholders)
    ");
    $revenue_stmt->bind_param($types, ...$center_ids);
    $revenue_stmt->execute();
    $revenue_stmt->bind_result($total_revenue);
    $revenue_stmt->fetch();
    $revenue_stmt->close();
    
    // Total registration amount
    $reg_stmt = $conn->prepare("
        SELECT COALESCE(SUM(reg_amount), 0) 
        FROM onlinestudents 
        WHERE center_id IN ($placeholders)
    ");
    $reg_stmt->bind_param($types, ...$center_ids);
    $reg_stmt->execute();
    $reg_stmt->bind_result($total_reg_amount);
    $reg_stmt->fetch();
    $reg_stmt->close();
    
    // Calculate commission based on member type
    $commission_rates = [
        'State Nodal' => 3,
        'Zonal Manager' => 5,
        'District Coordinator' => 10,
        'Block Coordinator' => 15
    ];
    
    $commission_rate = $commission_rates[$member_type] ?? 0;
    $total_commission = ($total_reg_amount * $commission_rate) / 100;
} else {
    $total_students = 0;
    $total_revenue = 0;
    $total_commission = 0;
    $commission_rate = 0;
}

// Get recent students
$recent_students = [];
if (!empty($center_ids)) {
    $placeholders = implode(',', array_fill(0, count($center_ids), '?'));
    $types = str_repeat('i', count($center_ids));
    
    $recent_stmt = $conn->prepare("
        SELECT os.id, os.name, os.student_code, cd.center_name, os.course_name, 
               os.created_at, os.price, os.status
        FROM onlinestudents os
        LEFT JOIN center_details cd ON os.center_id = cd.id
        WHERE os.center_id IN ($placeholders)
        ORDER BY os.created_at DESC 
        LIMIT 10
    ");
    $recent_stmt->bind_param($types, ...$center_ids);
    $recent_stmt->execute();
    $recent_result = $recent_stmt->get_result();
    
    while ($row = $recent_result->fetch_assoc()) {
        $recent_students[] = $row;
    }
    $recent_stmt->close();
}

// Get monthly student registration data for chart
$monthly_data = [];
if (!empty($center_ids)) {
    $placeholders = implode(',', array_fill(0, count($center_ids), '?'));
    $types = str_repeat('i', count($center_ids));
    
    $monthly_stmt = $conn->prepare("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as count
        FROM onlinestudents 
        WHERE center_id IN ($placeholders)
        AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month ASC
    ");
    $monthly_stmt->bind_param($types, ...$center_ids);
    $monthly_stmt->execute();
    $monthly_result = $monthly_stmt->get_result();
    
    while ($row = $monthly_result->fetch_assoc()) {
        $monthly_data[] = $row;
    }
    $monthly_stmt->close();
}

// Prepare chart data
$chart_months = [];
$chart_counts = [];
foreach ($monthly_data as $data) {
    $chart_months[] = date('M Y', strtotime($data['month'] . '-01'));
    $chart_counts[] = (int)$data['count'];
}

// If no monthly data, create empty chart
if (empty($chart_months)) {
    $chart_months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
    $chart_counts = [0, 0, 0, 0, 0, 0];
}

// Calculate percentage changes (dummy data for now)
$student_change = 12.5;
$center_change = 8.3;
$revenue_change = 15.2;
$commission_change = 10.8;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Dashboard - Sharnay Institute</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-color: #4e73df;
            --primary-dark: #224abe;
            --success-color: #1cc88a;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --secondary-color: #858796;
            --light-color: #f8f9fc;
            --border-color: #e3e6f0;
        }
        
        body {
            background-color: #f5f7fb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        
        /* Main content wrapper */
        .content-wrapper {
            padding: 20px;
        }
        
        /* Welcome banner */
        .welcome-banner {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .welcome-banner h3 {
            color: white;
            font-weight: 600;
        }
        
        .welcome-banner p {
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 0.25rem;
        }
        
        .working-area {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            padding: 1rem;
            backdrop-filter: blur(10px);
        }
        
        .working-area h6 {
            color: white;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .working-area p {
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
        }
        
        /* Stat cards */
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid transparent;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            border-color: var(--border-color);
        }
        
        .stat-content {
            display: flex;
            align-items: center;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .stat-details {
            flex: 1;
        }
        
        .stat-details h3 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stat-details p {
            color: var(--secondary-color);
            margin-bottom: 0.75rem;
        }
        
        .bg-gradient-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        }
        
        .bg-gradient-success {
            background: linear-gradient(135deg, var(--success-color) 0%, #17a673 100%);
        }
        
        .bg-gradient-info {
            background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
        }
        
        .bg-gradient-warning {
            background: linear-gradient(135deg, var(--warning-color) 0%, #dda20a 100%);
        }
        
        /* Chart container */
        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            height: 100%;
        }
        
        /* Centers list */
        .centers-list {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            height: 100%;
        }
        
        /* Recent table */
        .recent-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        
        /* Footer */
        .dashboard-footer {
            background: transparent;
            padding: 1.5rem 0;
            margin-top: 2rem;
            border-top: 1px solid var(--border-color);
        }
        
        /* Animations */
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
            animation: fadeInUp 0.5s ease forwards;
        }
        
        /* Table styles */
        .table th {
            background-color: var(--light-color);
            border-bottom: 2px solid var(--border-color);
            color: var(--secondary-color);
            font-weight: 600;
            padding: 1rem 1.5rem;
        }
        
        .table td {
            padding: 1rem 1.5rem;
            vertical-align: middle;
        }
        
        .table tbody tr:hover {
            background-color: rgba(78, 115, 223, 0.03);
        }
        
        /* Badge styles */
        .badge.bg-primary {
            background-color: rgba(78, 115, 223, 0.1) !important;
            color: var(--primary-color) !important;
            border: 1px solid rgba(78, 115, 223, 0.2);
        }
        
        .badge.bg-success {
            background-color: rgba(28, 200, 138, 0.1) !important;
            color: var(--success-color) !important;
            border: 1px solid rgba(28, 200, 138, 0.2);
        }
        
        .badge.bg-info {
            background-color: rgba(54, 185, 204, 0.1) !important;
            color: #36b9cc !important;
            border: 1px solid rgba(54, 185, 204, 0.2);
        }
        
        /* Action buttons */
        .btn-action {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .content-wrapper {
                padding: 15px;
            }
            
            .stat-content {
                flex-direction: column;
                text-align: center;
            }
            
            .stat-icon {
                margin-right: 0;
                margin-bottom: 1rem;
            }
            
            .welcome-banner .row {
                flex-direction: column;
            }
            
            .welcome-banner .text-md-end {
                text-align: left !important;
                margin-top: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Include the menu/header -->
    <?php 
    // Create a common menu.php that works for all member types
    if (file_exists('menu.php')) {
        include 'menu.php'; 
    }
    ?>
    
    <!-- Dashboard Content -->
    <div class="content-wrapper animate-fade-in-up">
        <!-- Welcome Banner -->
        <div class="welcome-banner mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="fw-bold mb-2">Welcome back, <?php echo htmlspecialchars($applicant_name); ?>! 👋</h3>
                    <p class="mb-1"><i class="bi bi-person-badge me-2"></i>Member Code: <?php echo htmlspecialchars($unicode); ?></p>
                    <p class="mb-0"><i class="bi bi-award me-2"></i>Role: <?php echo htmlspecialchars($member_type); ?></p>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="working-area mt-3 mt-md-0">
                        <h6 class="mb-2"><i class="bi bi-geo-alt me-1"></i> Working Area</h6>
                        <?php if (!empty($working_state_name)): ?>
                            <p class="mb-1"><strong>State:</strong> <?php echo htmlspecialchars($working_state_name); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($working_district_names)): ?>
                            <p class="mb-0"><strong>Districts:</strong> <?php echo htmlspecialchars($working_district_names); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($working_block_names)): ?>
                            <p class="mb-0"><strong>Blocks:</strong> <?php echo htmlspecialchars($working_block_names); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card primary animate-fade-in-up" style="animation-delay: 0.1s;">
                    <div class="stat-content">
                        <div class="stat-icon bg-gradient-primary rounded-3 d-flex align-items-center justify-content-center">
                            <i class="bi bi-people fs-4 text-white"></i>
                        </div>
                        <div class="stat-details">
                            <h3 class="mb-1"><?php echo number_format($total_students); ?></h3>
                            <p class="text-muted mb-2">Total Students</p>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-success bg-opacity-10 text-success me-2">
                                    <i class="bi bi-arrow-up me-1"></i><?php echo $student_change; ?>%
                                </span>
                                <span class="text-muted small">Since last month</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="stat-card success animate-fade-in-up" style="animation-delay: 0.2s;">
                    <div class="stat-content">
                        <div class="stat-icon bg-gradient-success rounded-3 d-flex align-items-center justify-content-center">
                            <i class="bi bi-building fs-4 text-white"></i>
                        </div>
                        <div class="stat-details">
                            <h3 class="mb-1"><?php echo number_format($total_centers); ?></h3>
                            <p class="text-muted mb-2">Total Centers</p>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-success bg-opacity-10 text-success me-2">
                                    <i class="bi bi-arrow-up me-1"></i><?php echo $center_change; ?>%
                                </span>
                                <span class="text-muted small">Since last month</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="stat-card info animate-fade-in-up" style="animation-delay: 0.3s;">
                    <div class="stat-content">
                        <div class="stat-icon bg-gradient-info rounded-3 d-flex align-items-center justify-content-center">
                            <i class="bi bi-currency-rupee fs-4 text-white"></i>
                        </div>
                        <div class="stat-details">
                            <h3 class="mb-1">₹<?php echo number_format($total_revenue, 0); ?></h3>
                            <p class="text-muted mb-2">Total Revenue</p>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-success bg-opacity-10 text-success me-2">
                                    <i class="bi bi-arrow-up me-1"></i><?php echo $revenue_change; ?>%
                                </span>
                                <span class="text-muted small">Since last month</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="stat-card warning animate-fade-in-up" style="animation-delay: 0.4s;">
                    <div class="stat-content">
                        <div class="stat-icon bg-gradient-warning rounded-3 d-flex align-items-center justify-content-center">
                            <i class="bi bi-cash-stack fs-4 text-white"></i>
                        </div>
                        <div class="stat-details">
                            <h3 class="mb-1">₹<?php echo number_format($total_commission, 0); ?></h3>
                            <p class="text-muted mb-2">Commission (<?php echo $commission_rate; ?>%)</p>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-success bg-opacity-10 text-success me-2">
                                    <i class="bi bi-arrow-up me-1"></i><?php echo $commission_change; ?>%
                                </span>
                                <span class="text-muted small">Since last month</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Charts and Centers Section -->
        <div class="row g-4 mb-4">
            <!-- Chart Section -->
            <div class="col-lg-8">
                <div class="chart-container animate-fade-in-up" style="animation-delay: 0.5s;">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-graph-up me-2"></i>Student Registration Trend
                        </h5>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-primary active">6 Months</button>
                            <button class="btn btn-sm btn-outline-primary">1 Year</button>
                            <button class="btn btn-sm btn-outline-primary">All Time</button>
                        </div>
                    </div>
                    <div style="height: 300px;">
                        <canvas id="registrationChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Centers List -->
            <div class="col-lg-4">
                <div class="centers-list animate-fade-in-up" style="animation-delay: 0.6s;">
                    <div class="p-3 border-bottom">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-building me-2"></i>Centers Overview
                        </h5>
                    </div>
                    <div class="p-3" style="max-height: 300px; overflow-y: auto;">
                        <?php if (!empty($center_names)): ?>
                            <?php foreach ($center_names as $index => $center_name): ?>
                                <?php 
                                $center_student_count = 0;
                                if (isset($center_ids[$index])) {
                                    $center_id = $center_ids[$index];
                                    $center_count_stmt = $conn->prepare("
                                        SELECT COUNT(*) 
                                        FROM onlinestudents 
                                        WHERE center_id = ? AND status = 'Active'
                                    ");
                                    $center_count_stmt->bind_param("i", $center_id);
                                    $center_count_stmt->execute();
                                    $center_count_stmt->bind_result($center_student_count);
                                    $center_count_stmt->fetch();
                                    $center_count_stmt->close();
                                }
                                ?>
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                            <i class="bi bi-building text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-semibold"><?php echo htmlspecialchars($center_name); ?></h6>
                                            <small class="text-muted">Active Center</small>
                                        </div>
                                    </div>
                                    <span class="badge bg-primary rounded-pill"><?php echo $center_student_count; ?></span>
                                </div>
                            <?php endforeach; ?>
                            <div class="text-center mt-3">
                                <a href="centers.php" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i> View All Centers
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-building text-muted fs-1 mb-3"></i>
                                <p class="text-muted mb-0">No centers found in your working area</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Students -->
        <div class="recent-table animate-fade-in-up" style="animation-delay: 0.7s;">
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-clock-history me-2"></i>Recent Students
                </h5>
                <?php if (!empty($center_ids)): ?>
                    <a href="students.php" class="btn btn-sm btn-primary">
                        <i class="bi bi-arrow-right me-1"></i> View All
                    </a>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Student</th>
                            <th>Center</th>
                            <th>Course</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_students)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="bi bi-people text-muted fs-1 mb-3"></i>
                                    <p class="text-muted mb-0">No students found in your working area</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $counter = 1; ?>
                            <?php foreach ($recent_students as $student): ?>
                                <tr>
                                    <td class="ps-4 fw-semibold"><?php echo $counter++; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                                <i class="bi bi-person text-primary"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-semibold"><?php echo htmlspecialchars($student['name']); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($student['student_code']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fw-semibold"><?php echo htmlspecialchars($student['center_name']); ?></td>
                                    <td>
                                        <span class="badge bg-info bg-opacity-10 text-info">
                                            <?php echo htmlspecialchars($student['course_name']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?php echo date('d M, Y', strtotime($student['created_at'])); ?></small>
                                    </td>
                                    <td class="fw-semibold">₹<?php echo number_format($student['price'], 2); ?></td>
                                    <td>
                                        <span class="badge <?php echo $student['status'] == 'Active' ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo $student['status']; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary btn-action view-student" 
                                                data-id="<?php echo $student['id']; ?>"
                                                title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success btn-action" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Footer -->
        <footer class="dashboard-footer mt-4">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 text-center">
                        <p class="mb-0 text-muted">
                            © <?php echo date('Y'); ?> Sharnay Institute. All rights reserved.
                            <span class="mx-2">|</span>
                            Designed & Developed by <a href="#" target="_blank">RKVIT Pvt. Ltd.</a>
                        </p>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Modal for Student Details -->
    <div class="modal fade" id="studentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Student Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="studentDetails">
                    <!-- Details will be loaded here via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Print Details</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
    // Initialize registration chart
    const ctx = document.getElementById('registrationChart').getContext('2d');
    const registrationChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chart_months); ?>,
            datasets: [{
                label: 'Student Registrations',
                data: <?php echo json_encode($chart_counts); ?>,
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                borderColor: 'rgba(78, 115, 223, 1)',
                borderWidth: 3,
                pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8,
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        stepSize: 1,
                        callback: function(value) {
                            if (Number.isInteger(value)) {
                                return value;
                            }
                        }
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        font: {
                            size: 12,
                            family: "'Poppins', sans-serif"
                        },
                        padding: 20
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleFont: {
                        size: 13,
                        family: "'Poppins', sans-serif"
                    },
                    bodyFont: {
                        size: 13,
                        family: "'Poppins', sans-serif"
                    },
                    padding: 12,
                    cornerRadius: 8
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            },
            animations: {
                tension: {
                    duration: 1000,
                    easing: 'linear'
                }
            }
        }
    });

    // View student details
    $(document).on('click', '.view-student', function() {
        const studentId = $(this).data('id');
        
        $.ajax({
            url: 'get_student_details.php',
            method: 'POST',
            data: { student_id: studentId },
            beforeSend: function() {
                $('#studentDetails').html(`
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Loading student details...</p>
                    </div>
                `);
            },
            success: function(response) {
                $('#studentDetails').html(response);
                const modal = new bootstrap.Modal(document.getElementById('studentModal'));
                modal.show();
            },
            error: function() {
                $('#studentDetails').html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Error loading student details. Please try again.
                    </div>
                `);
                const modal = new bootstrap.Modal(document.getElementById('studentModal'));
                modal.show();
            }
        });
    });

    // Add hover effects to stat cards
    $('.stat-card').hover(
        function() {
            $(this).css('transform', 'translateY(-8px)');
        },
        function() {
            $(this).css('transform', 'translateY(0)');
        }
    );
    
    // Add click animation to buttons
    $('.btn-action').click(function() {
        $(this).css('transform', 'scale(0.95)');
        setTimeout(() => {
            $(this).css('transform', 'scale(1)');
        }, 200);
    });
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    </script>
</body>
</html>