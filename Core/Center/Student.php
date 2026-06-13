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
$center_id = isset($_SESSION['center_id']) ? $_SESSION['center_id'] : 0;
$center_name = isset($_SESSION['center_name']) ? $_SESSION['center_name'] : '';
$phone = isset($_SESSION['phone']) ? $_SESSION['phone'] : '';

// Handle Approve Action
if (isset($_GET['approve_id'])) {
    $student_id = intval($_GET['approve_id']);
    
    // First verify this student belongs to this center
    $check_stmt = $conn->prepare("SELECT id FROM onlinestudents WHERE id = ? AND study_center = ?");
    $check_stmt->bind_param("is", $student_id, $center_name);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Update payment status and approved
        $update_stmt = $conn->prepare("UPDATE onlinestudents SET payment_status = 'Paid', approved = 1 WHERE id = ?");
        $update_stmt->bind_param("i", $student_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['success_message'] = "Payment approved successfully!";
        } else {
            $_SESSION['error_message'] = "Error approving payment: " . $conn->error;
        }
        $update_stmt->close();
    } else {
        $_SESSION['error_message'] = "Student not found or doesn't belong to your center!";
    }
    $check_stmt->close();
    
    header("Location: student.php");
    exit();
}

// Handle search and filter
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Pagination variables
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Build base query to fetch students for this center only
$base_query = "SELECT * FROM onlinestudents WHERE study_center = ?";
$count_query = "SELECT COUNT(*) as total FROM onlinestudents WHERE study_center = ?";

// Build WHERE conditions
$where_conditions = [];
$params = [$center_name];
$types = "s";
$count_params = [$center_name];
$count_types = "s";

// Add search filter
if (!empty($search)) {
    $where_conditions[] = "(name LIKE ? OR student_code LIKE ? OR email LIKE ? OR mobile LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
    $count_params = array_merge($count_params, [$search_term, $search_term, $search_term, $search_term]);
    $types .= "ssss";
    $count_types .= "ssss";
}

// Add status filter
if ($status_filter != 'all') {
    $where_conditions[] = "payment_status = ?";
    $params[] = $status_filter;
    $count_params[] = $status_filter;
    $types .= "s";
    $count_types .= "s";
}

// Add WHERE conditions to queries
if (!empty($where_conditions)) {
    $where_clause = " AND " . implode(" AND ", $where_conditions);
    $base_query .= $where_clause;
    $count_query .= $where_clause;
}

// Get total count for pagination
$stmt_count = $conn->prepare($count_query);
if (!empty($count_params)) {
    $stmt_count->bind_param($count_types, ...$count_params);
}
$stmt_count->execute();
$count_result = $stmt_count->get_result();
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $per_page);

// Add ordering and pagination to main query
$base_query .= " ORDER BY created_at DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $per_page;
$types .= "ii";

// Prepare and execute query
$stmt = $conn->prepare($base_query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$students_result = $stmt->get_result();

// Get total counts for statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN payment_status = 'Paid' THEN 1 ELSE 0 END) as paid,
    SUM(CASE WHEN payment_status = 'Pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN payment_status = 'Failed' THEN 1 ELSE 0 END) as failed
    FROM onlinestudents WHERE study_center = ?";
$stmt_stats = $conn->prepare($stats_query);
$stmt_stats->bind_param("s", $center_name);
$stmt_stats->execute();
$counts = $stmt_stats->get_result()->fetch_assoc();

// Get unique courses for this center
$courses_query = "SELECT DISTINCT course_name FROM onlinestudents WHERE study_center = ? ORDER BY course_name";
$stmt_courses = $conn->prepare($courses_query);
$stmt_courses->bind_param("s", $center_name);
$stmt_courses->execute();
$courses_result = $stmt_courses->get_result();

$page_title = "Student Management";
include 'header.php';
?>

<style>
    :root {
        --primary-color: #4e73df;
        --secondary-color: #858796;
        --success-color: #1cc88a;
        --info-color: #36b9cc;
        --warning-color: #f6c23e;
        --danger-color: #e74a3b;
        --light-color: #f8f9fc;
        --dark-color: #5a5c69;
    }
    
    body {
        background-color: #f8f9fc;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .card {
        border: none;
        border-radius: 0.35rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        margin-bottom: 20px;
    }
    
    .card-header {
        background-color: white;
        border-bottom: 1px solid #e3e6f0;
        padding: 1rem 1.35rem;
        font-weight: 600;
    }
    
    .stat-card {
        border-left: 0.25rem solid var(--primary-color);
    }
    
    .stat-card.paid {
        border-left-color: var(--success-color);
    }
    
    .stat-card.pending {
        border-left-color: var(--warning-color);
    }
    
    .stat-card.failed {
        border-left-color: var(--danger-color);
    }
    
    .badge-status {
        padding: 0.35em 0.65em;
        font-size: 0.75em;
        font-weight: 600;
    }
    
    .table th {
        border-top: none;
        font-weight: 600;
        color: var(--dark-color);
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .action-buttons .btn {
        padding: 0.25rem 0.5rem;
        margin: 0 2px;
    }
    
    .search-box {
        max-width: 300px;
    }
    
    .student-photo {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e3e6f0;
    }
    
    .btn-approve {
        background-color: var(--success-color);
        color: white;
        border: none;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .btn-approve:hover {
        background-color: #17a673;
        transform: translateY(-2px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    
    .btn-approve:disabled {
        background-color: #b0b0b0;
        cursor: not-allowed;
        transform: none;
    }
    
    .approved-badge {
        background-color: var(--success-color);
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
    }
    
    @media (max-width: 768px) {
        .sidebar {
            width: 0;
            overflow: hidden;
        }
        
        .search-box {
            max-width: 100%;
        }
    }
    
    /* Modal Styles */
    .modal-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, #224abe 100%);
        color: white;
    }
    
    .detail-section {
        background-color: #f8f9fc;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        border-left: 4px solid var(--primary-color);
    }
    
    .detail-section h6 {
        color: var(--primary-color);
        font-weight: 600;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
    }
    
    .detail-section h6 i {
        margin-right: 10px;
    }
    
    .detail-item {
        display: flex;
        margin-bottom: 12px;
        padding-bottom: 12px;
        border-bottom: 1px solid #e3e6f0;
    }
    
    .detail-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    
    .detail-label {
        width: 40%;
        font-weight: 600;
        color: var(--dark-color);
    }
    
    .detail-value {
        width: 60%;
        color: #333;
    }
    
    .payment-badge {
        font-size: 0.85rem;
        padding: 5px 12px;
        border-radius: 20px;
    }
    
    .student-image-container {
        text-align: center;
        padding: 15px;
    }
    
    .student-image {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 50%;
        border: 5px solid white;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .pagination .page-link {
        color: var(--primary-color);
    }
    
    .pagination .page-item.active .page-link {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
    }
</style>

<!-- Main Content -->
<div class="main-content container">
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container-fluid">
            <h4 class="mb-0"><i class="fas fa-users me-2"></i> Student Management - <?php echo htmlspecialchars($center_name); ?></h4>
            <div class="d-flex align-items-center">
                <span class="me-3 text-muted">
                    <i class="fas fa-calendar-alt me-1"></i> 
                    <?php echo date('F d, Y'); ?>
                </span>
            </div>
        </div>
    </nav>

    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?php echo $_SESSION['success_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $_SESSION['error_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Students
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $counts['total'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card paid">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Paid Fees
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $counts['paid'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card pending">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Fees
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $counts['pending'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card failed">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Failed Payments
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $counts['failed'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" name="search" placeholder="Search by name, code, email or mobile..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="Paid" <?php echo $status_filter == 'Paid' ? 'selected' : ''; ?>>Paid</option>
                        <option value="Pending" <?php echo $status_filter == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Failed" <?php echo $status_filter == 'Failed' ? 'selected' : ''; ?>>Failed</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
                
                <div class="col-md-2">
                    <a href="student.php" class="btn btn-secondary w-100">
                        <i class="fas fa-sync-alt"></i> Reset
                    </a>
                </div>
            </form>
            
            <div class="mt-3">
                <a href="../admin/add-student.php" class="btn btn-success">
                    <i class="fas fa-user-plus"></i> Add New Student
                </a>
            </div>
        </div>
    </div>

    <!-- Students Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i> Students List</h5>
            <span class="text-muted">
                Showing <?php echo ($offset + 1) . " - " . min($offset + $per_page, $total_rows); ?> of <?php echo $total_rows; ?> students
            </span>
        </div>
        <div class="card-body">
            <?php if($students_result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student Code</th>
                                <th>Name</th>
                                <th>Course</th>
                                <th>Duration</th>
                                <th>Price</th>
                                <th>Contact</th>
                                <th>Photo</th>
                                <th>ID Card</th>
                                <th>Apply Date</th>
                                <th>Reg. Amount</th>
                                <th>Pay Mode</th>
                                <th>Pay Status</th>
                                <th>Approve</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $counter = ($page - 1) * $per_page + 1;
                            while($student = $students_result->fetch_assoc()): 
                                $created_at = !empty($student['created_at']) ? date('d-m-Y', strtotime($student['created_at'])) : 'N/A';
                            ?>
                                <tr>
                                    <td><?php echo $counter++; ?></td>
                                    <td>
                                        <span class="badge bg-info"><?php echo htmlspecialchars($student['student_code']); ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            
                                            <strong><?php echo htmlspecialchars($student['name']); ?></strong>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($student['course_name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['duration']); ?></td>
                                    <td>₹<?php echo number_format($student['price'], 2); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($student['mobile']); ?><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($student['email']); ?></small>
                                    </td>
                                    <td>
                                        <?php if (!empty($student['photo'])): ?>
                                            <img src="../uploads/students/photos/<?php echo htmlspecialchars($student['photo']); ?>" alt="Photo" width="50" height="50" style="border-radius: 5px;">
                                        <?php else: ?>
                                            <span class="badge bg-secondary">No Photo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($student['id_proof'])): ?>
                                            <a href="../uploads/students/id_proofs/<?php echo htmlspecialchars($student['id_proof']); ?>" target="_blank" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">No ID</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $created_at; ?></td>
                                    <td>₹<?php echo number_format($student['reg_amount'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($student['payment_mode']); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge <?php 
                                            if($student['payment_status'] == 'Paid') echo 'bg-success';
                                            elseif($student['payment_status'] == 'Pending') echo 'bg-warning';
                                            else echo 'bg-danger';
                                        ?>">
                                            <?php echo htmlspecialchars($student['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if($student['payment_status'] != 'Paid'): ?>
                                            <a href="?approve_id=<?php echo $student['id']; ?>" 
                                               onclick="return confirmApprove()" 
                                               class="btn-approve">
                                                <i class="fas fa-check-circle"></i> Approve
                                            </a>
                                        <?php else: ?>
                                            <span class="approved-badge">
                                                <i class="fas fa-check"></i> Approved
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($student['approved'] == 1): ?>
                                            <span class="badge bg-success">Approved</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="action-buttons">
                                        <button class="btn btn-sm btn-info" onclick="viewStudent(<?php echo htmlspecialchars(json_encode($student)); ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="edit_student.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $student['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?<?php 
                                $query_params = $_GET;
                                $query_params['page'] = $page - 1;
                                echo http_build_query($query_params);
                            ?>">&laquo;</a>
                        </li>
                        
                        <?php 
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $start_page + 4);
                        
                        for($i = $start_page; $i <= $end_page; $i++): 
                        ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?<?php 
                                    $query_params = $_GET;
                                    $query_params['page'] = $i;
                                    echo http_build_query($query_params);
                                ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?<?php 
                                $query_params = $_GET;
                                $query_params['page'] = $page + 1;
                                echo http_build_query($query_params);
                            ?>">&raquo;</a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-users fa-4x text-muted mb-3"></i>
                    <h4>No Students Found</h4>
                    <p class="text-muted">No students have been registered for your center yet.</p>
                    <a href="../admin/add-student.php" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Add Your First Student
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- View Student Modal -->
<div class="modal fade" id="viewStudentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-graduate me-2"></i>
                    Student Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="studentDetails">
                <!-- Dynamic content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmApprove() {
        return confirm('Are you sure you want to approve this payment?');
    }
    
    function confirmDelete(studentId) {
        if (confirm('Are you sure you want to delete this student? This action cannot be undone.')) {
            window.location.href = 'delete_student.php?id=' + studentId;
        }
    }
    
    function viewStudent(student) {
        const modalBody = document.getElementById('studentDetails');
        
        // Format dates
        const dob = student.dob ? new Date(student.dob).toLocaleDateString('en-IN') : 'N/A';
        const created = student.created_at ? new Date(student.created_at).toLocaleDateString('en-IN') : 'N/A';
        const sessionStart = student.session_start ? new Date(student.session_start).toLocaleDateString('en-IN') : 'N/A';
        const sessionEnd = student.session_end ? new Date(student.session_end).toLocaleDateString('en-IN') : 'N/A';
        
        // Build HTML
        let html = `
            <div class="row mb-4">
                <div class="col-md-3 text-center">
                    ${student.photo ? 
                        `<img src="../uploads/students/photos/${student.photo}" class="student-image mb-3">` : 
                        `<div class="student-image bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                            <i class="fas fa-user fa-4x text-muted"></i>
                        </div>`
                    }
                </div>
                <div class="col-md-9">
                    <h4>${student.name}</h4>
                    <p class="text-muted mb-2">Student Code: ${student.student_code}</p>
                    <p class="mb-2">
                        <span class="badge payment-badge ${student.payment_status == 'Paid' ? 'bg-success' : (student.payment_status == 'Pending' ? 'bg-warning' : 'bg-danger')}">
                            ${student.payment_status} - ₹${parseFloat(student.reg_amount).toFixed(2)}
                        </span>
                        ${student.approved == 1 ? 
                            '<span class="badge bg-success ms-2">Approved</span>' : 
                            '<span class="badge bg-warning ms-2">Pending</span>'
                        }
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-mobile-alt me-2"></i>${student.mobile} | 
                        <i class="fas fa-envelope me-2 ms-3"></i>${student.email}
                    </p>
                </div>
            </div>
            
            <div class="detail-section">
                <h6><i class="fas fa-user"></i> Personal Information</h6>
                <div class="detail-item">
                    <div class="detail-label">Name:</div>
                    <div class="detail-value">${student.name}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Father:</div>
                    <div class="detail-value">${student.father_name || 'N/A'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Mother:</div>
                    <div class="detail-value">${student.mother_name || 'N/A'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Date of Birth:</div>
                    <div class="detail-value">${dob}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Gender:</div>
                    <div class="detail-value">${student.gender || 'N/A'}</div>
                </div>
            </div>
            
            <div class="detail-section">
                <h6><i class="fas fa-address-book"></i> Contact Information</h6>
                <div class="detail-item">
                    <div class="detail-label">Email:</div>
                    <div class="detail-value">${student.email}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Mobile:</div>
                    <div class="detail-value">${student.mobile}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Address:</div>
                    <div class="detail-value">${student.address || 'N/A'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">City:</div>
                    <div class="detail-value">${student.city || 'N/A'}</div>
                </div>
            </div>
            
            <div class="detail-section">
                <h6><i class="fas fa-graduation-cap"></i> Course Information</h6>
                <div class="detail-item">
                    <div class="detail-label">Course:</div>
                    <div class="detail-value">${student.course_name}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Duration:</div>
                    <div class="detail-value">${student.duration || 'N/A'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Course Price:</div>
                    <div class="detail-value">₹${parseFloat(student.price).toFixed(2)}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Study Center:</div>
                    <div class="detail-value">${student.study_center}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Session:</div>
                    <div class="detail-value">${sessionStart} to ${sessionEnd}</div>
                </div>
            </div>
            
            <div class="detail-section">
                <h6><i class="fas fa-key"></i> Login Information</h6>
                <div class="detail-item">
                    <div class="detail-label">Username:</div>
                    <div class="detail-value">${student.username}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Registration Date:</div>
                    <div class="detail-value">${created}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Payment Mode:</div>
                    <div class="detail-value">${student.payment_mode || 'N/A'}</div>
                </div>
            </div>
        `;
        
        // Add ID Proof section if exists
        if (student.id_proof) {
            const ext = student.id_proof.split('.').pop().toLowerCase();
            if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) {
                html += `
                    <div class="detail-section">
                        <h6><i class="fas fa-id-card"></i> ID Proof</h6>
                        <div class="text-center">
                            <a href="../uploads/students/id_proofs/${student.id_proof}" target="_blank">
                                <img src="../uploads/students/id_proofs/${student.id_proof}" 
                                     class="img-fluid mb-2" style="max-height: 150px; border-radius: 5px;">
                            </a><br>
                            <a href="../uploads/students/id_proofs/${student.id_proof}" target="_blank" class="btn btn-sm btn-primary mt-2">
                                <i class="fas fa-external-link-alt me-1"></i> View Full Image
                            </a>
                        </div>
                    </div>
                `;
            } else {
                html += `
                    <div class="detail-section">
                        <h6><i class="fas fa-id-card"></i> ID Proof</h6>
                        <div class="text-center">
                            <div class="alert alert-info">
                                <i class="fas fa-file-pdf fa-2x mb-2"></i><br>
                                <a href="../uploads/students/id_proofs/${student.id_proof}" 
                                   target="_blank" class="btn btn-sm btn-primary">
                                    <i class="fas fa-external-link-alt me-1"></i> View ID Proof (PDF)
                                </a>
                            </div>
                        </div>
                    </div>
                `;
            }
        }
        
        modalBody.innerHTML = html;
        
        // Show modal
        new bootstrap.Modal(document.getElementById('viewStudentModal')).show();
    }
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        document.querySelectorAll('.alert').forEach(alert => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);
</script>

<?php include 'footer.php'; ?>