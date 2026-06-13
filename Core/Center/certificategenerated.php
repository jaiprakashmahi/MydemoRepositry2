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

// Handle search and filter
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$course_filter = isset($_GET['course']) ? $_GET['course'] : 'all';

// Pagination variables
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Build base query to fetch approved students for this center only
$base_query = "SELECT * FROM onlinestudents WHERE study_center = ? AND approved = 1";
$count_query = "SELECT COUNT(*) as total FROM onlinestudents WHERE study_center = ? AND approved = 1";

// Build WHERE conditions
$where_conditions = [];
$params = [$center_name];
$types = "s";
$count_params = [$center_name];
$count_types = "s";

// Add search filter
if (!empty($search)) {
    $where_conditions[] = "(name LIKE ? OR student_code LIKE ? OR email LIKE ? OR mobile LIKE ? OR course_name LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term, $search_term]);
    $count_params = array_merge($count_params, [$search_term, $search_term, $search_term, $search_term, $search_term]);
    $types .= "sssss";
    $count_types .= "sssss";
}

// Add course filter
if ($course_filter != 'all') {
    $where_conditions[] = "course_name = ?";
    $params[] = $course_filter;
    $count_params[] = $course_filter;
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
    SUM(CASE WHEN session_end IS NOT NULL AND session_end <= CURDATE() THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN session_end IS NULL OR session_end > CURDATE() THEN 1 ELSE 0 END) as ongoing,
    COUNT(DISTINCT course_name) as courses
    FROM onlinestudents WHERE study_center = ? AND approved = 1";
$stmt_stats = $conn->prepare($stats_query);
$stmt_stats->bind_param("s", $center_name);
$stmt_stats->execute();
$counts = $stmt_stats->get_result()->fetch_assoc();

// Get unique courses for this center
$courses_query = "SELECT DISTINCT course_name FROM onlinestudents WHERE study_center = ? AND approved = 1 ORDER BY course_name";
$stmt_courses = $conn->prepare($courses_query);
$stmt_courses->bind_param("s", $center_name);
$stmt_courses->execute();
$courses_result = $stmt_courses->get_result();

$page_title = "Certificate Generation";
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
    
    .stat-card.completed {
        border-left-color: var(--success-color);
    }
    
    .stat-card.ongoing {
        border-left-color: var(--warning-color);
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
        white-space: nowrap;
    }
    
    .table td {
        vertical-align: middle;
        white-space: nowrap;
    }
    
    .action-buttons .btn {
        padding: 0.25rem 0.5rem;
        margin: 0 2px;
    }
    
    .student-photo {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e3e6f0;
    }
    
    .btn-generate {
        background-color: var(--success-color);
        color: white;
        border: none;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        display: inline-block;
        text-decoration: none;
    }
    
    .btn-generate:hover {
        background-color: #17a673;
        transform: translateY(-2px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        color: white;
    }
    
    .btn-pending {
        background-color: #b0b0b0;
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        cursor: not-allowed;
        display: inline-block;
    }
    
    .btn-action {
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        transition: all 0.3s;
        margin: 2px;
        display: inline-block;
        text-decoration: none;
    }
    
    .btn-action i {
        margin-right: 4px;
    }
    
    .btn-primary {
        background-color: var(--primary-color);
        color: white;
    }
    
    .btn-primary:hover {
        background-color: #224abe;
        color: white;
    }
    
    .btn-success {
        background-color: var(--success-color);
        color: white;
    }
    
    .btn-success:hover {
        background-color: #17a673;
        color: white;
    }
    
    .btn-info {
        background-color: var(--info-color);
        color: white;
    }
    
    .btn-info:hover {
        background-color: #2c9faf;
        color: white;
    }
    
    .btn-warning {
        background-color: var(--warning-color);
        color: white;
    }
    
    .btn-warning:hover {
        background-color: #d59f1e;
        color: white;
    }
    
    .btn-danger {
        background-color: var(--danger-color);
        color: white;
    }
    
    .btn-danger:hover {
        background-color: #c13a2d;
        color: white;
    }
    
    .session-badge {
        font-size: 11px;
        padding: 3px 8px;
        border-radius: 12px;
        background-color: #e3e6f0;
        color: var(--dark-color);
    }
    
    .session-completed {
        background-color: #d4edda;
        color: #155724;
    }
    
    .session-ongoing {
        background-color: #fff3cd;
        color: #856404;
    }
    
    @media print {
        .btn, .action-buttons, .form-check-input {
            display: none !important;
        }
    }
</style>

<!-- Main Content -->
<div class="main-content container">
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container-fluid">
            <h4 class="mb-0">
                <i class="fas fa-certificate me-2 text-primary"></i> 
                Certificate Generation - <?php echo htmlspecialchars($center_name); ?>
            </h4>
            <div class="d-flex align-items-center">
                <span class="me-3 text-muted">
                    <i class="fas fa-calendar-alt me-1"></i> 
                    <?php echo date('F d, Y'); ?>
                </span>
                <span class="badge bg-info text-white p-2">
                    <i class="fas fa-check-circle me-1"></i> Approved Students Only
                </span>
            </div>
        </div>
    </nav>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Approved Students
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
            <div class="card stat-card completed">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Course Completed
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $counts['completed'] ?? 0; ?>
                            </div>
                            <small class="text-muted">Ready for certificate</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-graduation-cap fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card ongoing">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Ongoing Courses
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $counts['ongoing'] ?? 0; ?>
                            </div>
                            <small class="text-muted">In progress</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Courses
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $counts['courses'] ?? 0; ?>
                            </div>
                            <small class="text-muted">Active courses</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-book-open fa-2x text-gray-300"></i>
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
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" name="search" 
                               placeholder="Search by name, code, course, email or mobile..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                
                <div class="col-md-3">
                    <select class="form-select" name="course">
                        <option value="all" <?php echo $course_filter == 'all' ? 'selected' : ''; ?>>All Courses</option>
                        <?php 
                        // Reset pointer
                        $courses_result->data_seek(0);
                        while($course = $courses_result->fetch_assoc()): 
                        ?>
                            <option value="<?php echo htmlspecialchars($course['course_name']); ?>" 
                                <?php echo $course_filter == $course['course_name'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($course['course_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
                
                <div class="col-md-2">
                    <a href="certificategenerated.php" class="btn btn-secondary w-100">
                        <i class="fas fa-sync-alt"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Students Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i> Approved Students - Certificate Management
            </h5>
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
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Course</th>
                                <th>Duration</th>
                                <th>Price</th>
                                <th>Contact</th>
                                <th>Study Center</th>
                                <th>Session</th>
                                <th>Grade</th>
                                <th>ID Proof</th>
                                <th>ID Card</th>
                                <th>Certificate</th>
                                <th>Marks</th>
                                <th>Marksheet</th>
                                <th>View Cert.</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $counter = ($page - 1) * $per_page + 1;
                            while($student = $students_result->fetch_assoc()): 
                                $is_course_completed = (!empty($student['session_end']) && date('Y-m-d') >= $student['session_end']);
                                $session_class = $is_course_completed ? 'session-completed' : 'session-ongoing';
                                $session_text = $is_course_completed ? 'Completed' : 'Ongoing';
                            ?>
                                <tr>
                                    <td><?php echo $counter++; ?></td>
                                    <td>
                                        <span class="badge bg-info"><?php echo htmlspecialchars($student['student_code']); ?></span>
                                    </td>
                                    <td>
                                        <?php if (!empty($student['photo'])): ?>
                                            <img src="../uploads/students/photos/<?php echo htmlspecialchars($student['photo']); ?>" 
                                                 class="student-photo" alt="Photo">
                                        <?php else: ?>
                                            <div class="student-photo bg-light d-flex align-items-center justify-content-center">
                                                <i class="fas fa-user text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($student['name']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($student['father_name'] ?? ''); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($student['course_name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['duration'] ?? 'N/A'); ?></td>
                                    <td>₹<?php echo number_format($student['price'] ?? 0, 2); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($student['mobile']); ?><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($student['email']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($student['study_center']); ?></td>
                                    <td>
                                        <div class="session-badge <?php echo $session_class; ?>">
                                            <?php if (!empty($student['session_start']) && !empty($student['session_end'])): ?>
                                                <i class="fas fa-calendar-alt me-1"></i>
                                                <?php echo date('d-m-Y', strtotime($student['session_start'])); ?><br>
                                                <i class="fas fa-calendar-check me-1"></i>
                                                <?php echo date('d-m-Y', strtotime($student['session_end'])); ?>
                                                <br><span class="badge mt-1 <?php echo $is_course_completed ? 'bg-success' : 'bg-warning'; ?>">
                                                    <?php echo $session_text; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">Not Set</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="badge bg-secondary">
                                            <?php echo htmlspecialchars($student['grade'] ?? 'N/A'); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!empty($student['id_proof'])): ?>
                                            <a href="../uploads/students/id_proofs/<?php echo htmlspecialchars($student['id_proof']); ?>" 
                                               target="_blank" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">No ID</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="StudentIdCard.php?id=<?php echo $student['id']; ?>" 
                                           class="btn btn-sm btn-success" target="_blank">
                                            <i class="fas fa-id-card"></i> Download
                                        </a>
                                    </td>
                                    <td>
                                        <?php if ($is_course_completed): ?>
                                            <a href="Marksheet_Creation_Form.php?id=<?php echo $student['id']; ?>" 
                                               class="btn-generate">
                                                <i class="fas fa-certificate"></i> Generate
                                            </a>
                                        <?php else: ?>
                                            <span class="btn-pending">
                                                <i class="fas fa-clock"></i> Pending
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="add_marks.php?id=<?php echo $student['id']; ?>" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-edit"></i> Add
                                        </a>
                                    </td>
                                    <td>
                                        <a href="certificate.php?id=<?php echo $student['id']; ?>" 
                                           class="btn btn-sm btn-success" target="_blank">
                                            <i class="fas fa-file-alt"></i> View
                                        </a>
                                    </td>
                                    <td>
                                        <a href="org_certificate.php?id=<?php echo $student['id']; ?>" 
                                           class="btn btn-sm btn-primary" target="_blank">
                                            <i class="fas fa-certificate"></i> View
                                        </a>
                                    </td>
                                    <td class="action-buttons">
                                        <a href="edit_student.php?id=<?php echo $student['id']; ?>" 
                                           class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete_student.php?id=<?php echo $student['id']; ?>" 
                                           class="btn btn-sm btn-danger" title="Delete"
                                           onclick="return confirm('Are you sure to delete this student?');">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
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
                    <i class="fas fa-certificate fa-4x text-muted mb-3"></i>
                    <h4>No Approved Students Found</h4>
                    <p class="text-muted">No approved students found for your center.</p>
                    <p class="text-muted">Students need to be approved first to generate certificates.</p>
                    <a href="student.php" class="btn btn-primary">
                        <i class="fas fa-users"></i> Go to Student List
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Quick Actions Floating Button -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 5">
    <div class="dropdown">
        <button class="btn btn-primary rounded-circle shadow-lg" type="button" data-bs-toggle="dropdown" 
                style="width: 60px; height: 60px; font-size: 24px;">
            <i class="fas fa-plus"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="add-student.php"><i class="fas fa-user-plus me-2"></i>Add New Student</a></li>
            <li><a class="dropdown-item" href="student.php"><i class="fas fa-list me-2"></i>All Students</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="#" onclick="window.print()"><i class="fas fa-print me-2"></i>Print List</a></li>
        </ul>
    </div>
</div>

<script>
    // Search functionality with debounce
    let searchTimeout;
    document.querySelector('input[name="search"]').addEventListener('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            this.form.submit();
        }, 500);
    });
    
    // Auto-submit on course change
    document.querySelector('select[name="course"]').addEventListener('change', function() {
        this.form.submit();
    });
    
    // Check if course is completed
    function checkCourseCompletion(sessionEnd) {
        const today = new Date();
        const endDate = new Date(sessionEnd);
        return today >= endDate;
    }
    
    // Print functionality
    document.querySelectorAll('.btn-print').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            window.print();
        });
    });
</script>

<?php include 'footer.php'; ?>