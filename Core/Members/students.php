<?php
session_start();
include '../conn.php';

if (!isset($_SESSION['member_logged_in'])) {
    header("Location: member_login.php");
    exit();
}

$member_id = $_SESSION['member_id'];
$member_type = $_SESSION['member_type'];

// Get working area names from session
$working_state_name = $_SESSION['working_state_name'] ?? '';
$working_district_names = $_SESSION['working_district_names'] ?? '';
$working_block_names = $_SESSION['working_block_names'] ?? '';

// Build WHERE clause for centers
$center_conditions = [];
$params = [];
$param_types = "";

switch ($member_type) {
    case 'State Nodal':
        if (!empty($working_state_name)) {
            $center_conditions[] = "state = ?";
            $params[] = $working_state_name;
            $param_types .= "s";
        }
        break;
        
    case 'Zonal Manager':
    case 'District Coordinator':
        if (!empty($working_district_names)) {
            // Convert comma-separated district names to array
            if (strpos($working_district_names, ', ') !== false) {
                $district_names_array = explode(', ', $working_district_names);
            } elseif (strpos($working_district_names, ',') !== false) {
                $district_names_array = explode(',', $working_district_names);
            } else {
                $district_names_array = [$working_district_names];
            }
            
            $placeholders = implode(',', array_fill(0, count($district_names_array), '?'));
            $center_conditions[] = "district IN ($placeholders)";
            $params = array_merge($params, $district_names_array);
            $param_types .= str_repeat('s', count($district_names_array));
        }
        break;
        
    case 'Block Coordinator':
        // Note: Your center_details table doesn't have a 'block' column
        // If you add this column, uncomment the code below
        /*
        if (!empty($working_block_names)) {
            if (strpos($working_block_names, ', ') !== false) {
                $block_names_array = explode(', ', $working_block_names);
            } elseif (strpos($working_block_names, ',') !== false) {
                $block_names_array = explode(',', $working_block_names);
            } else {
                $block_names_array = [$working_block_names];
            }
            
            $placeholders = implode(',', array_fill(0, count($block_names_array), '?'));
            $center_conditions[] = "block IN ($placeholders)";
            $params = array_merge($params, $block_names_array);
            $param_types .= str_repeat('s', count($block_names_array));
        }
        */
        break;
}

// Get center IDs
$center_ids = [];
if (!empty($center_conditions)) {
    $where_sql = "WHERE " . implode(" AND ", $center_conditions);
    $center_stmt = $conn->prepare("SELECT id, center_name FROM center_details $where_sql");
    
    if (!empty($params)) {
        $center_stmt->bind_param($param_types, ...$params);
    }
    
    $center_stmt->execute();
    $center_result = $center_stmt->get_result();
    
    while ($row = $center_result->fetch_assoc()) {
        $center_ids[] = $row['id'];
    }
    $center_stmt->close();
} else {
    // If no conditions (e.g., Block Coordinator without block column), show message
    $no_centers_message = "No centers found in your working area. Please contact administrator.";
}

// Get filter parameters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$course = $_GET['course'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

// Build student query
$student_conditions = [];
$student_params = [];
$student_types = "";

if (!empty($center_ids)) {
    $placeholders = implode(',', array_fill(0, count($center_ids), '?'));
    $student_types = str_repeat('i', count($center_ids));
    $student_conditions[] = "os.center_id IN ($placeholders)";
    $student_params = $center_ids;
} else {
    // If no center IDs, ensure no students are shown
    $student_conditions[] = "1 = 0"; // Always false condition
}

if (!empty($search)) {
    $student_conditions[] = "(os.name LIKE ? OR os.student_code LIKE ? OR os.mobile LIKE ?)";
    $student_params[] = "%$search%";
    $student_params[] = "%$search%";
    $student_params[] = "%$search%";
    $student_types .= "sss";
}

if (!empty($status) && $status != 'all') {
    $student_conditions[] = "os.status = ?";
    $student_params[] = $status;
    $student_types .= "s";
}

if (!empty($course) && $course != 'all') {
    $student_conditions[] = "os.course_name = ?";
    $student_params[] = $course;
    $student_types .= "s";
}

if (!empty($start_date)) {
    $student_conditions[] = "DATE(os.created_at) >= ?";
    $student_params[] = $start_date;
    $student_types .= "s";
}

if (!empty($end_date)) {
    $student_conditions[] = "DATE(os.created_at) <= ?";
    $student_params[] = $end_date;
    $student_types .= "s";
}

// Get total count
$total_students = 0;
if (!empty($center_ids)) {
    $count_sql = "SELECT COUNT(*) FROM onlinestudents os";
    if (!empty($student_conditions)) {
        $count_sql .= " WHERE " . implode(" AND ", $student_conditions);
    }
    
    $count_stmt = $conn->prepare($count_sql);
    if (!empty($student_params)) {
        $count_stmt->bind_param($student_types, ...$student_params);
    }
    $count_stmt->execute();
    $count_stmt->bind_result($total_students);
    $count_stmt->fetch();
    $count_stmt->close();
}

// Pagination
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$total_pages = ceil($total_students / $limit);

// Get students with pagination
$students = [];
if (!empty($center_ids)) {
    $student_sql = "
        SELECT os.id, os.student_code, os.name, os.father_name, os.mobile, 
               os.email, os.course_name, os.price, os.reg_amount, os.payment_status,
               os.status, os.created_at, cd.center_name
        FROM onlinestudents os
        LEFT JOIN center_details cd ON os.center_id = cd.id
    ";
    
    if (!empty($student_conditions)) {
        $student_sql .= " WHERE " . implode(" AND ", $student_conditions);
    }
    
    $student_sql .= " ORDER BY os.created_at DESC LIMIT ? OFFSET ?";
    
    $student_stmt = $conn->prepare($student_sql);
    
    if (!empty($student_params)) {
        $student_params_with_pagination = $student_params;
        $student_params_with_pagination[] = $limit;
        $student_params_with_pagination[] = $offset;
        $student_types_with_pagination = $student_types . "ii";
        $student_stmt->bind_param($student_types_with_pagination, ...$student_params_with_pagination);
    } else {
        $student_stmt->bind_param("ii", $limit, $offset);
    }
    
    $student_stmt->execute();
    $student_result = $student_stmt->get_result();
    
    while ($row = $student_result->fetch_assoc()) {
        $students[] = $row;
    }
    $student_stmt->close();
}

// Get unique courses for filter
$courses = [];
if (!empty($center_ids)) {
    $placeholders = implode(',', array_fill(0, count($center_ids), '?'));
    $types = str_repeat('i', count($center_ids));
    
    $course_stmt = $conn->prepare("
        SELECT DISTINCT course_name 
        FROM onlinestudents 
        WHERE center_id IN ($placeholders) AND course_name IS NOT NULL AND course_name != ''
        ORDER BY course_name
    ");
    $course_stmt->bind_param($types, ...$center_ids);
    $course_stmt->execute();
    $course_result = $course_stmt->get_result();
    
    while ($row = $course_result->fetch_assoc()) {
        $courses[] = $row['course_name'];
    }
    $course_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Students - Sharnay Institute</title>
    
    <!-- Include the modern menu -->
    <?php include 'menu.php'; ?>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
/* Page Header */
.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    padding: 1.5rem;
 
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
     width: 90% ;
   margin: 3rem 1rem 2rem 1rem;

}

/* Filter Card */
.filter-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    margin: 0 1rem 2rem 1rem;
    width: 90% ;
}

/* Students Table Card */
.students-table-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    margin: 0 1rem 2rem 1rem;
        width: 90% ;
}

.table-header {
    background: #f8f9fc;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #e3e6f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table-container {
    overflow-x: auto;
    padding: 0;
}

.students-table {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
}

.students-table thead {
    background: #f8f9fc;
    position: sticky;
    top: 0;
    z-index: 10;
}

.students-table th {
    padding: 1rem 1.5rem;
    font-weight: 600;
    color: #5a5c69;
    border-bottom: 2px solid #e3e6f0;
    text-align: left;
    white-space: nowrap;
}

.students-table tbody tr {
    border-bottom: 1px solid #f0f0f0;
    transition: all 0.3s ease;
}

.students-table tbody tr:hover {
    background: rgba(78, 115, 223, 0.05);
}

.students-table td {
    padding: 1rem 1.5rem;
    color: #6e707e;
    vertical-align: middle;
}

/* Badge styles */
.badge {
    padding: 0.35em 0.75em;
    font-weight: 500;
    border-radius: 20px;
    font-size: 0.75rem;
}

/* Status badges */
.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
}

.status-active {
    background: rgba(28, 200, 138, 0.1);
    color: #1cc88a;
    border: 1px solid rgba(28, 200, 138, 0.2);
}

.status-inactive {
    background: rgba(231, 74, 59, 0.1);
    color: #e74a3b;
    border: 1px solid rgba(231, 74, 59, 0.2);
}

.payment-paid {
    background: rgba(28, 200, 138, 0.1);
    color: #1cc88a;
    border: 1px solid rgba(28, 200, 138, 0.2);
}

.payment-pending {
    background: rgba(246, 194, 62, 0.1);
    color: #f6c23e;
    border: 1px solid rgba(246, 194, 62, 0.2);
}

/* Action buttons */
.action-buttons {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
}

.btn-action {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #d1d3e2;
    background: white;
    color: #858796;
    transition: all 0.3s ease;
    cursor: pointer;
}

.btn-action:hover {
    background: #4e73df;
    color: white;
    border-color: #4e73df;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(78, 115, 223, 0.3);
}

/* Pagination */
.pagination {
    margin: 0;
}

.pagination .page-link {
    color: #4e73df;
    border: 1px solid #d1d3e2;
    margin: 0 0.25rem;
    border-radius: 8px;
    padding: 0.5rem 1rem;
}

.pagination .page-item.active .page-link {
    background: #4e73df;
    border-color: #4e73df;
    color: white;
}

.pagination .page-link:hover {
    background: #f8f9fc;
    border-color: #4e73df;
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 3rem;
    color: #858796;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: #d1d3e2;
}

/* Search input */
.search-input {
    position: relative;
}

.search-input i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #858796;
    z-index: 1;
}

.search-input input {
    padding-left: 40px;
    position: relative;
}

/* Form controls */
.form-control {
    border: 1px solid #d1d3e2;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #4e73df;
    box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
}

/* Buttons */
.btn {
    border-radius: 8px;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #224abe 0%, #4e73df 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(78, 115, 223, 0.4);
}

.btn-outline-primary {
    color: #4e73df;
    border-color: #4e73df;
}

.btn-outline-primary:hover {
    background: #4e73df;
    color: white;
}

/* Animations */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fadeIn 0.5s ease forwards;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .main-content {
        padding: 15px;
        padding-top: 70px;
    }
    
    .page-header,
    .filter-card,
    .students-table-card {
        margin: 0 0.5rem 1rem 0.5rem;
        padding: 1rem;
    }
    
    .table-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .students-table th,
    .students-table td {
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
    }
    
    .action-buttons {
        flex-wrap: wrap;
        gap: 0.25rem;
    }
    
    .btn-action {
        width: 32px;
        height: 32px;
        font-size: 0.875rem;
    }
}

/* Print styles */
@media print {
    .filter-card,
    .btn-action,
    .pagination,
    .action-buttons button:not(.print-btn) {
        display: none !important;
    }
    
    .students-table-card {
        box-shadow: none;
        border: 1px solid #ddd;
    }
    
    .students-table th {
        background: #f0f0f0 !important;
        color: #000 !important;
    }
}
    </style>
</head>
<body>
    <!-- Main Content -->
    <div class="container">
        <div class="">
            <!-- Page Header -->
            <div class="page-header animate-fade-in">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="h2 mb-2">
                            <i class="bi bi-people me-2"></i>Students Management
                        </h1>
                        <p class="mb-0 opacity-75">
                            Manage and view all students in your working area
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="d-flex align-items-center justify-content-end">
                            <div class="me-3">
                                <h3 class="mb-0"><?php echo number_format($total_students); ?></h3>
                                <small class="opacity-75">Total Students</small>
                            </div>
                            <div class="bg-white rounded-circle p-3">
                                <i class="bi bi-people-fill text-primary fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filters Card -->
            <div class="filter-card animate-fade-in" style="animation-delay: 0.1s;">
                <h5 class="mb-3 fw-bold text-dark">
                    <i class="bi bi-funnel me-2"></i>Filter Students
                </h5>
                <form method="GET" action="" class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <div class="search-input">
                            <i class="bi bi-search"></i>
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Search by name, code or mobile" 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    
                    <div class="col-lg-2 col-md-6">
                        <select name="status" class="form-control">
                            <option value="all">All Status</option>
                            <option value="Active" <?php echo $status == 'Active' ? 'selected' : ''; ?>>Active</option>
                            <option value="Inactive" <?php echo $status == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <div class="col-lg-2 col-md-6">
                        <select name="course" class="form-control">
                            <option value="all">All Courses</option>
                            <?php foreach ($courses as $course_name): ?>
                                <option value="<?php echo htmlspecialchars($course_name); ?>" 
                                    <?php echo $course == $course_name ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-lg-2 col-md-6">
                        <input type="date" name="start_date" class="form-control" 
                               value="<?php echo htmlspecialchars($start_date); ?>"
                               onfocus="(this.type='date')" onblur="if(!this.value)this.type='text'">
                    </div>
                    
                    <div class="col-lg-2 col-md-6">
                        <input type="date" name="end_date" class="form-control" 
                               value="<?php echo htmlspecialchars($end_date); ?>"
                               onfocus="(this.type='date')" onblur="if(!this.value)this.type='text'">
                    </div>
                    
                    <div class="col-lg-1 col-md-6">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-filter"></i>
                        </button>
                    </div>
                    
                    <div class="col-lg-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <?php if (!empty($search) || !empty($status) || !empty($course) || !empty($start_date) || !empty($end_date)): ?>
                                    <span class="text-muted small">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Filters applied: 
                                        <?php 
                                        $filters = [];
                                        if (!empty($search)) $filters[] = "Search: '$search'";
                                        if (!empty($status) && $status != 'all') $filters[] = "Status: $status";
                                        if (!empty($course) && $course != 'all') $filters[] = "Course: $course";
                                        if (!empty($start_date)) $filters[] = "From: $start_date";
                                        if (!empty($end_date)) $filters[] = "To: $end_date";
                                        echo implode(', ', $filters);
                                        ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <a href="students.php" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i> Clear Filters
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Students Table Card -->
            <div class="students-table-card animate-fade-in" style="animation-delay: 0.2s;">
                <div class="table-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-list-ul me-2"></i>Students List
                    </h5>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-primary me-3">
                            Showing <?php echo min($limit, count($students)); ?> of <?php echo $total_students; ?> students
                        </span>
                        <button class="btn btn-sm btn-outline-primary" onclick="exportToExcel()">
                            <i class="bi bi-download me-1"></i> Export
                        </button>
                    </div>
                </div>
                
                <div class="table-container">
                    <table class="students-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student Code</th>
                                <th>Student Name</th>
                                <th>Father's Name</th>
                                <th>Mobile</th>
                                <th>Center</th>
                                <th>Course</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Registered On</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($students) || isset($no_centers_message)): ?>
                                <tr>
                                    <td colspan="12">
                                        <div class="empty-state py-5">
                                            <?php if (isset($no_centers_message)): ?>
                                                <i class="bi bi-building-slash"></i>
                                                <h5 class="mb-2">No Centers Assigned</h5>
                                                <p class="text-muted"><?php echo $no_centers_message; ?></p>
                                            <?php else: ?>
                                                <i class="bi bi-people"></i>
                                                <h5 class="mb-2">No Students Found</h5>
                                                <p class="text-muted">
                                                    <?php if (!empty($search) || !empty($status) || !empty($course)): ?>
                                                        No students match your filter criteria. Try different filters.
                                                    <?php else: ?>
                                                        No students found in your working area.
                                                    <?php endif; ?>
                                                </p>
                                            <?php endif; ?>
                                            <a href="students.php" class="btn btn-primary mt-3">
                                                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $counter = ($page - 1) * $limit + 1; ?>
                                <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td class="fw-semibold"><?php echo $counter++; ?></td>
                                        <td>
                                            <span class="badge bg-info bg-opacity-10 text-info">
                                                <?php echo htmlspecialchars($student['student_code']); ?>
                                            </span>
                                        </td>
                                        <td class="fw-semibold"><?php echo htmlspecialchars($student['name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['father_name']); ?></td>
                                        <td>
                                            <a href="tel:<?php echo htmlspecialchars($student['mobile']); ?>" 
                                               class="text-decoration-none">
                                                <i class="bi bi-telephone me-1"></i>
                                                <?php echo htmlspecialchars($student['mobile']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                <?php echo htmlspecialchars($student['center_name']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                                <?php echo htmlspecialchars($student['course_name']); ?>
                                            </span>
                                        </td>
                                        <td class="fw-bold text-dark">₹<?php echo number_format($student['price'], 2); ?></td>
                                        <td>
                                            <span class="badge <?php echo $student['payment_status'] == 'Paid' ? 'payment-paid' : 'payment-pending'; ?>">
                                                <?php echo $student['payment_status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $student['status'] == 'Active' ? 'status-active' : 'status-inactive'; ?>">
                                                <?php echo $student['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo date('d M, Y', strtotime($student['created_at'])); ?>
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <div class="action-buttons">
                                                <button class="btn-action view-student" 
                                                        data-id="<?php echo $student['id']; ?>"
                                                        title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="btn-action" 
                                                        onclick="editStudent(<?php echo $student['id']; ?>)"
                                                        title="Edit Student">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn-action" 
                                                        onclick="sendMessage('<?php echo htmlspecialchars($student['mobile']); ?>')"
                                                        title="Send SMS">
                                                    <i class="bi bi-chat-dots"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="table-header border-top-0">
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center mb-0">
                                <li class="page-item <?php echo $page == 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&<?php echo http_build_query($_GET); ?>">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                                
                                <?php 
                                // Show first page
                                if ($page > 3): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=1&<?php echo http_build_query($_GET); ?>">1</a>
                                    </li>
                                    <?php if ($page > 4): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <?php 
                                // Show pages around current page
                                $start = max(1, $page - 2);
                                $end = min($total_pages, $page + 2);
                                for ($i = $start; $i <= $end; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&<?php echo http_build_query($_GET); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php 
                                // Show last page
                                if ($page < $total_pages - 2): ?>
                                    <?php if ($page < $total_pages - 3): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    <?php endif; ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $total_pages; ?>&<?php echo http_build_query($_GET); ?>">
                                            <?php echo $total_pages; ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <li class="page-item <?php echo $page == $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&<?php echo http_build_query($_GET); ?>">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Student Details Modal -->
    <div class="modal fade" id="studentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-person-badge me-2"></i>Student Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="studentDetails">
                    <!-- Details will be loaded here via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Close
                    </button>
                    <button type="button" class="btn btn-primary" onclick="printStudentDetails()">
                        <i class="bi bi-printer me-1"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
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
                $('#studentModal').modal('show');
            },
            error: function() {
                $('#studentDetails').html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Error loading student details. Please try again.
                    </div>
                `);
                $('#studentModal').modal('show');
            }
        });
    });
    
    // Edit student (placeholder function)
    function editStudent(studentId) {
        alert('Edit feature coming soon! Student ID: ' + studentId);
        // In production, you would redirect to edit page:
        // window.location.href = 'edit_student.php?id=' + studentId;
    }
    
    // Send message (placeholder function)
    function sendMessage(mobile) {
        const message = prompt('Enter message to send to ' + mobile + ':');
        if (message) {
            alert('Message sent to ' + mobile + ':\n' + message);
            // In production, you would make an AJAX call to send SMS
        }
    }
    
    // Export to Excel (placeholder function)
    function exportToExcel() {
        alert('Export feature coming soon!');
        // In production, you would generate and download an Excel file
        // window.location.href = 'export_students.php?' + window.location.search;
    }
    
    // Print student details
    function printStudentDetails() {
        const printContent = document.getElementById('studentDetails').innerHTML;
        const originalContent = document.body.innerHTML;
        
        document.body.innerHTML = `
            <html>
                <head>
                    <title>Student Details - Sharnay Institute</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        .print-header { text-align: center; margin-bottom: 30px; }
                        .print-header h2 { color: #4e73df; }
                        .student-info { margin-bottom: 20px; }
                        .section-title { background: #f8f9fc; padding: 10px; font-weight: bold; margin: 15px 0; }
                        .info-row { display: flex; margin-bottom: 8px; }
                        .info-label { font-weight: bold; width: 200px; }
                        .info-value { flex: 1; }
                        .badge { padding: 4px 12px; border-radius: 20px; font-size: 12px; }
                        .badge-success { background: #d1e7dd; color: #0f5132; }
                        .badge-warning { background: #fff3cd; color: #856404; }
                        .badge-danger { background: #f8d7da; color: #842029; }
                        @media print {
                            .no-print { display: none; }
                        }
                    </style>
                </head>
                <body>
                    <div class="print-header">
                        <h2>Sharnay Institute</h2>
                        <p>Student Details Report</p>
                        <p>Printed on: ${new Date().toLocaleDateString()}</p>
                    </div>
                    ${printContent}
                    <div class="no-print" style="margin-top: 30px; text-align: center;">
                        <button onclick="window.print()" class="btn btn-primary">Print</button>
                        <button onclick="window.close()" class="btn btn-secondary">Close</button>
                    </div>
                </body>
            </html>
        `;
        
        window.print();
        document.body.innerHTML = originalContent;
        $('#studentModal').modal('hide');
    }
    
    // Initialize tooltips
    $(document).ready(function() {
        // Initialize Bootstrap tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Add hover effects to table rows
        $('.students-table tbody tr').hover(
            function() {
                $(this).css('transform', 'translateY(-2px)');
                $(this).css('box-shadow', '0 4px 12px rgba(0,0,0,0.1)');
            },
            function() {
                $(this).css('transform', 'translateY(0)');
                $(this).css('box-shadow', 'none');
            }
        );
        
        // Date input placeholder text
        $('input[type="date"]').each(function() {
            if (!$(this).val()) {
                $(this).attr('placeholder', 'Select date');
            }
        });
        
        // Auto-submit form when date changes (optional)
        $('input[type="date"]').change(function() {
            if ($(this).val()) {
                $(this).closest('form').submit();
            }
        });
        
        // Add animation to filter card
        $('.filter-card').addClass('animate-fade-in');
        
        // Add keyboard shortcut for search (Ctrl+F)
        $(document).keydown(function(e) {
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                $('input[name="search"]').focus();
            }
        });
    });
    </script>
</body>
</html>