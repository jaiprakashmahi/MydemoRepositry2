<?php
session_start();
include '../conn.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if center is logged in
if (!isset($_SESSION['center_logged_in']) || $_SESSION['center_logged_in'] !== true) {
    header("Location: member_login.php");
    exit();
}

// Get center details from session
$center_id = isset($_SESSION['center_id']) ? $_SESSION['center_id'] : 0;
$center_name = isset($_SESSION['center_name']) ? $_SESSION['center_name'] : '';
$phone = isset($_SESSION['phone']) ? $_SESSION['phone'] : '';

// Initialize variables
$show_form = false;
$edit_mode = false;
$teacher_id = 0;
$teacher_data = [];
$education_data = [];
$experience_data = [];

// Handle Delete Action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $teacher_id = intval($_GET['id']);
    
    // First verify this teacher belongs to this center
    $check_stmt = $conn->prepare("SELECT id FROM teachers WHERE id = ? AND center_id = ?");
    $check_stmt->bind_param("ii", $teacher_id, $center_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Delete education records first
        $del_edu = $conn->prepare("DELETE FROM teacher_education WHERE teacher_id = ?");
        $del_edu->bind_param("i", $teacher_id);
        $del_edu->execute();
        $del_edu->close();
        
        // Delete experience records
        $del_exp = $conn->prepare("DELETE FROM teacher_experience WHERE teacher_id = ?");
        $del_exp->bind_param("i", $teacher_id);
        $del_exp->execute();
        $del_exp->close();
        
        // Delete teacher
        $del_teacher = $conn->prepare("DELETE FROM teachers WHERE id = ?");
        $del_teacher->bind_param("i", $teacher_id);
        
        if ($del_teacher->execute()) {
            $_SESSION['success_msg'] = "Teacher deleted successfully!";
        } else {
            $_SESSION['error_msg'] = "Error deleting teacher: " . $conn->error;
        }
        $del_teacher->close();
    } else {
        $_SESSION['error_msg'] = "Teacher not found or doesn't belong to your center!";
    }
    $check_stmt->close();
    
    header("Location: teacher_management.php");
    exit();
}

// Handle Edit Action - Load teacher data
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $show_form = true;
    $edit_mode = true;
    $teacher_id = intval($_GET['id']);
    
    // Get teacher details
    $teacher_stmt = $conn->prepare("SELECT * FROM teachers WHERE id = ? AND center_id = ?");
    $teacher_stmt->bind_param("ii", $teacher_id, $center_id);
    $teacher_stmt->execute();
    $teacher_result = $teacher_stmt->get_result();
    
    if ($teacher_result->num_rows > 0) {
        $teacher_data = $teacher_result->fetch_assoc();
        
        // Get education details
        $edu_stmt = $conn->prepare("SELECT * FROM teacher_education WHERE teacher_id = ?");
        $edu_stmt->bind_param("i", $teacher_id);
        $edu_stmt->execute();
        $education_result = $edu_stmt->get_result();
        $education_data = [];
        while($row = $education_result->fetch_assoc()) {
            $education_data[] = $row;
        }
        $edu_stmt->close();
        
        // Get experience details
        $exp_stmt = $conn->prepare("SELECT * FROM teacher_experience WHERE teacher_id = ?");
        $exp_stmt->bind_param("i", $teacher_id);
        $exp_stmt->execute();
        $experience_result = $exp_stmt->get_result();
        $experience_data = [];
        while($row = $experience_result->fetch_assoc()) {
            $experience_data[] = $row;
        }
        $exp_stmt->close();
    } else {
        $_SESSION['error_msg'] = "Teacher not found!";
        header("Location: teacher_management.php");
        exit();
    }
    $teacher_stmt->close();
}

// Handle Add Action - Show form
if (isset($_GET['action']) && $_GET['action'] == 'add') {
    $show_form = true;
    $edit_mode = false;
}

// Process form submission (Add/Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'save_teacher') {
    
    $teacher_id = isset($_POST['teacher_id']) ? intval($_POST['teacher_id']) : 0;
    $edit_mode = ($teacher_id > 0);
    
    // Get form data
    $adv_id = mysqli_real_escape_string($conn, $_POST['adv_id'] ?? '');
    $name = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
    $father_name = mysqli_real_escape_string($conn, $_POST['father_name'] ?? '');
    $mother_name = mysqli_real_escape_string($conn, $_POST['mother_name'] ?? '');
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $dob = $_POST['dob'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $blood_group = mysqli_real_escape_string($conn, $_POST['blood_group'] ?? '');
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile'] ?? '');
    $state_id = intval($_POST['state'] ?? 0);
    $district_id = intval($_POST['district'] ?? 0);
    $address = mysqli_real_escape_string($conn, $_POST['address'] ?? '');
    $pin_code = mysqli_real_escape_string($conn, $_POST['pin_code'] ?? '');
    $city = mysqli_real_escape_string($conn, $_POST['city'] ?? '');
    $block = mysqli_real_escape_string($conn, $_POST['block'] ?? '');
    $post_office = mysqli_real_escape_string($conn, $_POST['post_office'] ?? '');
    
    // Create upload directories if not exist
    $upload_base = "../uploads/teachers/";
    $dirs = ['photos', 'id_proofs', 'marksheets', 'experience'];
    foreach($dirs as $dir) {
        if (!file_exists($upload_base . $dir)) {
            mkdir($upload_base . $dir, 0777, true);
        }
    }
    
    // File upload handling
    $photo_path = '';
    $id_proof_path = '';
    
    // Upload photo
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $photo_name = time() . '_' . basename($_FILES['photo']['name']);
        $target_file = $upload_base . 'photos/' . $photo_name;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
            $photo_path = $photo_name;
        }
    }
    
    // Upload ID proof
    if (isset($_FILES['id_proof']) && $_FILES['id_proof']['error'] == 0) {
        $id_proof_name = time() . '_' . basename($_FILES['id_proof']['name']);
        $target_file = $upload_base . 'id_proofs/' . $id_proof_name;
        if (move_uploaded_file($_FILES['id_proof']['tmp_name'], $target_file)) {
            $id_proof_path = $id_proof_name;
        }
    }
    
    if ($edit_mode) {
        // Update existing teacher
        $sql = "UPDATE teachers SET 
                adv_id = ?, name = ?, father_name = ?, mother_name = ?, email = ?, 
                dob = ?, gender = ?, blood_group = ?, mobile = ?, state_id = ?, 
                district_id = ?, address = ?, pin_code = ?, city = ?, block = ?, 
                post_office = ?";
        
        $params = [$adv_id, $name, $father_name, $mother_name, $email, $dob, $gender, 
                  $blood_group, $mobile, $state_id, $district_id, $address, 
                  $pin_code, $city, $block, $post_office];
        $types = "sssssssssiissssss";
        
        // Add photo if uploaded
        if (!empty($photo_path)) {
            $sql .= ", photo = ?";
            $params[] = $photo_path;
            $types .= "s";
        }
        
        // Add ID proof if uploaded
        if (!empty($id_proof_path)) {
            $sql .= ", id_proof = ?";
            $params[] = $id_proof_path;
            $types .= "s";
        }
        
        $sql .= " WHERE id = ? AND center_id = ?";
        $params[] = $teacher_id;
        $params[] = $center_id;
        $types .= "ii";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
        }
        
    } else {
        // Generate unique teacher code
        $teacher_code = 'TCH' . date('Ymd') . rand(100, 999);
        
        // Insert new teacher
        $sql = "INSERT INTO teachers (
                teacher_code, adv_id, name, father_name, mother_name, email, dob, 
                gender, blood_group, mobile, state_id, district_id, address, 
                pin_code, city, block, post_office, photo, id_proof, 
                allot_center, teacher_status, center_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param(
                "ssssssssssiissssssssi",
                $teacher_code, $adv_id, $name, $father_name, $mother_name, $email, $dob,
                $gender, $blood_group, $mobile, $state_id, $district_id, $address,
                $pin_code, $city, $block, $post_office, $photo_path, $id_proof_path,
                $center_name, $center_id
            );
        }
    }
    
    if ($stmt && $stmt->execute()) {
        if (!$edit_mode) {
            $teacher_id = $stmt->insert_id;
        }
        
        // Delete existing education and experience if editing
        if ($edit_mode) {
            $del_edu = $conn->prepare("DELETE FROM teacher_education WHERE teacher_id = ?");
            $del_edu->bind_param("i", $teacher_id);
            $del_edu->execute();
            $del_edu->close();
            
            $del_exp = $conn->prepare("DELETE FROM teacher_experience WHERE teacher_id = ?");
            $del_exp->bind_param("i", $teacher_id);
            $del_exp->execute();
            $del_exp->close();
        }
        
        // Handle education details
        if (isset($_POST['education']) && is_array($_POST['education'])) {
            $educations = $_POST['education'];
            $sessions_from = $_POST['session_from'];
            $sessions_to = $_POST['session_to'];
            $total_marks = $_POST['total_marks'];
            $obt_marks = $_POST['obt_marks'];
            $percentages = $_POST['percentage'];
            $grades = $_POST['grade'];
            
            for ($i = 0; $i < count($educations); $i++) {
                if (!empty($educations[$i])) {
                    // Upload marksheet
                    $marksheet_path = '';
                    if (isset($_FILES['marksheet']['name'][$i]) && !empty($_FILES['marksheet']['name'][$i]) && $_FILES['marksheet']['error'][$i] == 0) {
                        $marksheet_name = time() . '_' . $i . '_' . basename($_FILES['marksheet']['name'][$i]);
                        $target_file = $upload_base . 'marksheets/' . $marksheet_name;
                        if (move_uploaded_file($_FILES['marksheet']['tmp_name'][$i], $target_file)) {
                            $marksheet_path = $marksheet_name;
                        }
                    }
                    
                    $edu_sql = "INSERT INTO teacher_education (
                                teacher_id, education, session_from, session_to, 
                                total_marks, obt_marks, percentage, grade, marksheet
                                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    $edu_stmt = $conn->prepare($edu_sql);
                    if ($edu_stmt) {
                        $edu_stmt->bind_param(
                            "issssssss",
                            $teacher_id, $educations[$i], $sessions_from[$i], $sessions_to[$i],
                            $total_marks[$i], $obt_marks[$i], $percentages[$i], $grades[$i],
                            $marksheet_path
                        );
                        $edu_stmt->execute();
                        $edu_stmt->close();
                    }
                }
            }
        }
        
        // Handle experience details
        if (isset($_POST['experience_org']) && is_array($_POST['experience_org'])) {
            $orgs = $_POST['experience_org'];
            $roles = $_POST['experience_role'];
            $froms = $_POST['experience_from'];
            $tos = $_POST['experience_to'];
            
            for ($i = 0; $i < count($orgs); $i++) {
                if (!empty($orgs[$i])) {
                    // Upload experience document
                    $doc_path = '';
                    if (isset($_FILES['experience_doc']['name'][$i]) && !empty($_FILES['experience_doc']['name'][$i]) && $_FILES['experience_doc']['error'][$i] == 0) {
                        $doc_name = time() . '_' . $i . '_' . basename($_FILES['experience_doc']['name'][$i]);
                        $target_file = $upload_base . 'experience/' . $doc_name;
                        if (move_uploaded_file($_FILES['experience_doc']['tmp_name'][$i], $target_file)) {
                            $doc_path = $doc_name;
                        }
                    }
                    
                    $exp_sql = "INSERT INTO teacher_experience (
                                teacher_id, org, role, experience_from, experience_to, experience_doc
                                ) VALUES (?, ?, ?, ?, ?, ?)";
                    
                    $exp_stmt = $conn->prepare($exp_sql);
                    if ($exp_stmt) {
                        $exp_stmt->bind_param(
                            "isssss",
                            $teacher_id, $orgs[$i], $roles[$i], $froms[$i], $tos[$i], $doc_path
                        );
                        $exp_stmt->execute();
                        $exp_stmt->close();
                    }
                }
            }
        }
        
        $_SESSION['success_msg'] = $edit_mode ? "Teacher updated successfully!" : "Teacher registered successfully!";
    } else {
        $_SESSION['error_msg'] = "Error: " . ($stmt ? $stmt->error : "Statement preparation failed");
    }
    
    if ($stmt) $stmt->close();
    
    header("Location: teacher_management.php");
    exit();
}

// Get all teachers for this center
$teachers_query = "SELECT t.*, 
                   (SELECT COUNT(*) FROM teacher_education WHERE teacher_id = t.id) as edu_count,
                   (SELECT COUNT(*) FROM teacher_experience WHERE teacher_id = t.id) as exp_count
                   FROM teachers t 
                   WHERE t.center_id = ? 
                   ORDER BY t.id DESC";
$teachers_stmt = $conn->prepare($teachers_query);
$teachers_stmt->bind_param("i", $center_id);
$teachers_stmt->execute();
$teachers_result = $teachers_stmt->get_result();

$page_title = "Teacher Management";
include 'header.php';
?>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

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
    
  
    
    @media (max-width: 768px) {
        .main-content {
            margin-left: 0;
        }
    }
    
    .card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        margin-bottom: 20px;
    }
    
    .card-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, #224abe 100%);
        color: white;
        border-radius: 10px 10px 0 0 !important;
        padding: 15px 20px;
    }
    
    .card-header h5 {
        margin: 0;
        font-weight: 600;
    }
    
    .center-info-bar {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .center-info-bar i {
        margin-right: 8px;
    }
    
    .btn-add {
        background: linear-gradient(135deg, var(--success-color) 0%, #13855c 100%);
        color: white;
        border: none;
        padding: 10px 25px;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-block;
        cursor: pointer;
    }
    
    .btn-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(28, 200, 138, 0.4);
        color: white;
    }
    
    .btn-add i {
        margin-right: 8px;
    }
    
    .btn-action {
        padding: 5px 10px;
        border-radius: 5px;
        color: white;
        margin: 0 2px;
        display: inline-block;
        text-decoration: none;
        transition: all 0.3s;
        border: none;
        cursor: pointer;
    }
    
    .btn-action:hover {
        transform: translateY(-2px);
        color: white;
    }
    
    .btn-view {
        background: var(--info-color);
    }
    
    .btn-edit {
        background: var(--warning-color);
        color: #333;
    }
    
    .btn-edit:hover {
        color: #333;
    }
    
    .btn-delete {
        background: var(--danger-color);
    }
    
    .table {
        margin-bottom: 0;
    }
    
    .table th {
        border-top: none;
        font-weight: 600;
        color: var(--dark-color);
        background: #f8f9fc;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .teacher-photo {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--primary-color);
    }
    
    .teacher-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .teacher-name {
        font-weight: 600;
        color: var(--dark-color);
    }
    
    .teacher-code {
        font-size: 11px;
        color: #666;
        background: #f0f0f0;
        padding: 2px 8px;
        border-radius: 12px;
        display: inline-block;
    }
    
    .badge-status {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }
    
    .badge-active {
        background: #d4edda;
        color: #155724;
    }
    
    .badge-inactive {
        background: #f8d7da;
        color: #721c24;
    }
    
    .search-box {
        max-width: 300px;
        position: relative;
    }
    
    .search-box input {
        padding-right: 40px;
        border-radius: 50px;
        border: 2px solid #e3e6f0;
    }
    
    .search-box i {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
    }
    
    .form-section {
        background: #f8f9fc;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid #e3e6f0;
    }
    
    .form-section-title {
        color: var(--primary-color);
        font-weight: 600;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--primary-color);
    }
    
    .form-label {
        font-weight: 600;
        color: #555;
        margin-bottom: 5px;
    }
    
    .form-control, .form-select {
        border: 2px solid #e3e6f0;
        border-radius: 8px;
        padding: 10px 15px;
        transition: all 0.3s;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }
    
    .required {
        color: red;
        margin-left: 3px;
    }
    
    .btn-submit {
        background: linear-gradient(135deg, var(--success-color) 0%, #13855c 100%);
        color: white;
        border: none;
        padding: 12px 40px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 16px;
        transition: all 0.3s;
        cursor: pointer;
    }
    
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(28, 200, 138, 0.4);
        color: white;
    }
    
    .btn-cancel {
        background: var(--secondary-color);
        color: white;
        border: none;
        padding: 12px 40px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 16px;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-cancel:hover {
        background: #6c757d;
        color: white;
    }
    
    .empty-state {
        text-align: center;
        padding: 50px;
        background: #f8f9fc;
        border-radius: 10px;
    }
    
    .empty-state i {
        font-size: 60px;
        color: #ccc;
        margin-bottom: 20px;
    }
    
    .empty-state h4 {
        color: #666;
        margin-bottom: 10px;
    }
    
    .empty-state p {
        color: #999;
        margin-bottom: 20px;
    }
    
    .experience-row {
        background: white;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 15px;
        border: 1px solid #e3e6f0;
    }
    
    .detail-section {
        background: #f8f9fc;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        border-left: 4px solid var(--primary-color);
    }
    
    .detail-section h6 {
        color: var(--primary-color);
        font-weight: 600;
        margin-bottom: 10px;
    }
    
    .detail-item {
        display: flex;
        margin-bottom: 8px;
        padding-bottom: 8px;
        border-bottom: 1px dashed #dee2e6;
    }
    
    .detail-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    
    .detail-label {
        width: 40%;
        font-weight: 600;
        color: #666;
    }
    
    .detail-value {
        width: 60%;
        color: #333;
    }
</style>

<div class="main-content">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg mb-4">
        <div class="container-fluid">
            <h4 class="mb-0">
                <i class="fas fa-chalkboard-teacher me-2" style="color: var(--primary-color);"></i>
                Teacher Management
            </h4>
            <div class="d-flex align-items-center">
                <span class="me-3 text-muted">
                    <i class="fas fa-calendar-alt me-1"></i> 
                    <?php echo date('F d, Y'); ?>
                </span>
            </div>
        </div>
    </nav>

    <!-- Center Info Bar -->
    <div class="center-info-bar">
        <div>
            <i class="fas fa-building"></i> 
            <strong>Center:</strong> <?php echo htmlspecialchars($center_name); ?>
        </div>
        <div>
            <i class="fas fa-id-card"></i> 
            <strong>Center ID:</strong> <?php echo $center_id; ?>
        </div>
        <div>
            <i class="fas fa-phone"></i> 
            <strong>Contact:</strong> <?php echo htmlspecialchars($phone); ?>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="successAlert">
            <i class="fas fa-check-circle me-2"></i> <?php echo $_SESSION['success_msg']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_msg']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_msg'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="errorAlert">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $_SESSION['error_msg']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_msg']); ?>
    <?php endif; ?>

    <?php if (!$show_form): ?>
        <!-- Teacher List View -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i> Teachers List
                </h5>
                <div>
                    <a href="?action=add" class="btn-add">
                        <i class="fas fa-plus-circle"></i> Add New Teacher
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="search-box">
                            <input type="text" class="form-control" id="searchTeacher" placeholder="Search teachers...">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>
                </div>

                <?php if ($teachers_result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="teacherTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Teacher</th>
                                    <th>Teacher Code</th>
                                    <th>Contact</th>
                                    <th>Education</th>
                                    <th>Experience</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $counter = 1;
                                while($teacher = $teachers_result->fetch_assoc()): 
                                ?>
                                    <tr>
                                        <td><?php echo $counter++; ?></td>
                                        <td>
                                            <div class="teacher-info">
                                                <?php if(!empty($teacher['photo'])): ?>
                                                    <img src="../uploads/teachers/photos/<?php echo htmlspecialchars($teacher['photo']); ?>" 
                                                         class="teacher-photo" alt="">
                                                <?php else: ?>
                                                    <div class="teacher-photo bg-light d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-user text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <div class="teacher-name"><?php echo htmlspecialchars($teacher['name']); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($teacher['father_name']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="teacher-code">
                                                <i class="fas fa-qrcode me-1"></i>
                                                <?php echo htmlspecialchars($teacher['teacher_code']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <i class="fas fa-phone me-1 text-muted"></i> <?php echo htmlspecialchars($teacher['mobile']); ?><br>
                                            <small><i class="fas fa-envelope me-1 text-muted"></i> <?php echo htmlspecialchars($teacher['email']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <i class="fas fa-graduation-cap me-1"></i> <?php echo $teacher['edu_count']; ?> Records
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning">
                                                <i class="fas fa-briefcase me-1"></i> <?php echo $teacher['exp_count']; ?> Records
                                            </span>
                                        </td>
                                        <td>
                                            <?php if($teacher['teacher_status'] == 1): ?>
                                                <span class="badge-status badge-active">
                                                    <i class="fas fa-check-circle me-1"></i> Active
                                                </span>
                                            <?php else: ?>
                                                <span class="badge-status badge-inactive">
                                                    <i class="fas fa-times-circle me-1"></i> Inactive
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn-action btn-view" title="View Details" 
                                                    onclick="viewTeacher(<?php echo $teacher['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="?action=edit&id=<?php echo $teacher['id']; ?>" 
                                               class="btn-action btn-edit" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?action=delete&id=<?php echo $teacher['id']; ?>" 
                                               class="btn-action btn-delete" title="Delete"
                                               onclick="return confirmDelete(event, <?php echo $teacher['id']; ?>)">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <h4>No Teachers Found</h4>
                        <p>You haven't added any teachers yet. Click the button below to add your first teacher.</p>
                        <a href="?action=add" class="btn-add">
                            <i class="fas fa-plus-circle"></i> Add Your First Teacher
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <!-- Teacher Form View -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-<?php echo $edit_mode ? 'edit' : 'plus-circle'; ?> me-2"></i>
                    <?php echo $edit_mode ? 'Edit Teacher' : 'Add New Teacher'; ?>
                </h5>
            </div>
            <div class="card-body">
                <form action="" method="post" enctype="multipart/form-data" id="teacherForm" onsubmit="return validateForm()">
                    <input type="hidden" name="action" value="save_teacher">
                    <?php if($edit_mode): ?>
                        <input type="hidden" name="teacher_id" value="<?php echo $teacher_id; ?>">
                    <?php endif; ?>
                    
                    <!-- Personal Details -->
                    <div class="form-section">
                        <h6 class="form-section-title">
                            <i class="fas fa-user me-2"></i> Personal Details
                        </h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Adv. ID</label>
                                <input type="text" name="adv_id" class="form-control" 
                                       value="<?php echo $edit_mode ? htmlspecialchars($teacher_data['adv_id'] ?? '') : ''; ?>"
                                       placeholder="Enter Advisor ID">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Name <span class="required">*</span></label>
                                <input type="text" name="name" class="form-control" required
                                       value="<?php echo $edit_mode ? htmlspecialchars($teacher_data['name'] ?? '') : ''; ?>"
                                       placeholder="Enter Full Name">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Father's Name <span class="required">*</span></label>
                                <input type="text" name="father_name" class="form-control" required
                                       value="<?php echo $edit_mode ? htmlspecialchars($teacher_data['father_name'] ?? '') : ''; ?>"
                                       placeholder="Enter Father's Name">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Mother's Name</label>
                                <input type="text" name="mother_name" class="form-control"
                                       value="<?php echo $edit_mode ? htmlspecialchars($teacher_data['mother_name'] ?? '') : ''; ?>"
                                       placeholder="Enter Mother's Name">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Email ID <span class="required">*</span></label>
                                <input type="email" name="email" class="form-control" required
                                       value="<?php echo $edit_mode ? htmlspecialchars($teacher_data['email'] ?? '') : ''; ?>"
                                       placeholder="Enter Email ID">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Date of Birth <span class="required">*</span></label>
                                <input type="date" name="dob" class="form-control" required
                                       value="<?php echo $edit_mode ? htmlspecialchars($teacher_data['dob'] ?? '') : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Gender <span class="required">*</span></label>
                                <select name="gender" class="form-select" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php echo ($edit_mode && ($teacher_data['gender'] ?? '') == 'Male') ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo ($edit_mode && ($teacher_data['gender'] ?? '') == 'Female') ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo ($edit_mode && ($teacher_data['gender'] ?? '') == 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Blood Group</label>
                                <input type="text" name="blood_group" class="form-control"
                                       value="<?php echo $edit_mode ? htmlspecialchars($teacher_data['blood_group'] ?? '') : ''; ?>"
                                       placeholder="e.g., O+">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Mobile Number <span class="required">*</span></label>
                                <input type="text" name="mobile" class="form-control" required
                                       value="<?php echo $edit_mode ? htmlspecialchars($teacher_data['mobile'] ?? '') : ''; ?>"
                                       placeholder="Enter Mobile Number">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Address Details -->
                    <div class="form-section">
                        <h6 class="form-section-title">
                            <i class="fas fa-map-marker-alt me-2"></i> Address Details
                        </h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">State <span class="required">*</span></label>
                                <select id="state-dropdown" name="state" class="form-select" required>
                                    <option value="">Select State</option>
                                    <?php
                                    $query = "SELECT id, name FROM states ORDER BY name";
                                    $result = mysqli_query($conn, $query);
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $selected = ($edit_mode && !empty($teacher_data['state_id']) && $teacher_data['state_id'] == $row['id']) ? 'selected' : '';
                                        echo "<option value='{$row['id']}' $selected>{$row['name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">District <span class="required">*</span></label>
                                <select id="district-dropdown" name="district" class="form-select" required>
                                    <option value="">Select District</option>
                                    <?php if($edit_mode && !empty($teacher_data['district_id'])): ?>
                                        <?php
                                        $dist_query = "SELECT id, name FROM districts WHERE id = " . $teacher_data['district_id'];
                                        $dist_result = mysqli_query($conn, $dist_query);
                                        if($dist_result && $dist_result->num_rows > 0) {
                                            $dist = $dist_result->fetch_assoc();
                                            echo "<option value='{$dist['id']}' selected>{$dist['name']}</option>";
                                        }
                                        ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Address <span class="required">*</span></label>
                                <input type="text" name="address" class="form-control" required
                                       value="<?php echo $edit_mode ? htmlspecialchars($teacher_data['address'] ?? '') : ''; ?>"
                                       placeholder="Enter Address">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Pin Code <span class="required">*</span></label>
                                <input type="text" name="pin_code" class="form-control" required
                                       value="<?php echo $edit_mode ? htmlspecialchars($teacher_data['pin_code'] ?? '') : ''; ?>"
                                       placeholder="Enter Pincode">
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label">City <span class="required">*</span></label>
                                <input type="text" name="city" class="form-control" required
                                       value="<?php echo $edit_mode ? htmlspecialchars($teacher_data['city'] ?? '') : ''; ?>"
                                       placeholder="Enter City">
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Block <span class="required">*</span></label>
                                <input type="text" name="block" class="form-control" required
                                       value="<?php echo $edit_mode ? htmlspecialchars($teacher_data['block'] ?? '') : ''; ?>"
                                       placeholder="Enter Block">
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Post Office <span class="required">*</span></label>
                                <input type="text" name="post_office" class="form-control" required
                                       value="<?php echo $edit_mode ? htmlspecialchars($teacher_data['post_office'] ?? '') : ''; ?>"
                                       placeholder="Enter Post Office">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Documents -->
                    <div class="form-section">
                        <h6 class="form-section-title">
                            <i class="fas fa-file me-2"></i> Documents
                        </h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Photo <?php echo !$edit_mode ? '<span class="required">*</span>' : ''; ?></label>
                                <input type="file" name="photo" class="form-control" accept="image/*" <?php echo !$edit_mode ? 'required' : ''; ?>>
                                <?php if($edit_mode && !empty($teacher_data['photo'])): ?>
                                    <div class="mt-2">
                                        <img src="../uploads/teachers/photos/<?php echo htmlspecialchars($teacher_data['photo']); ?>" 
                                             style="max-width: 100px; border-radius: 5px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ID Proof <?php echo !$edit_mode ? '<span class="required">*</span>' : ''; ?></label>
                                <input type="file" name="id_proof" class="form-control" accept="image/*,.pdf" <?php echo !$edit_mode ? 'required' : ''; ?>>
                                <?php if($edit_mode && !empty($teacher_data['id_proof'])): ?>
                                    <div class="mt-2">
                                        <a href="../uploads/teachers/id_proofs/<?php echo htmlspecialchars($teacher_data['id_proof']); ?>" 
                                           target="_blank" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> View Current ID Proof
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Education Summary -->
                    <div class="form-section">
                        <h6 class="form-section-title">
                            <i class="fas fa-graduation-cap me-2"></i> Education Summary
                        </h6>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered" id="education-table">
                                <thead>
                                    <tr>
                                        <th>Education</th>
                                        <th>Session From</th>
                                        <th>Session To</th>
                                        <th>Total Marks</th>
                                        <th>Obt. Marks</th>
                                        <th>% / CGPA</th>
                                        <th>Grade</th>
                                        <th>Marksheet</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="education-body">
                                    <?php if($edit_mode && !empty($education_data)): ?>
                                        <?php foreach($education_data as $edu): ?>
                                            <tr>
                                                <td>
                                                    <select name="education[]" class="form-control" required>
                                                        <option value="">-- Select Education --</option>
                                                        <option value="PhD" <?php echo ($edu['education'] == 'PhD') ? 'selected' : ''; ?>>PhD</option>
                                                        <option value="M.Sc-IT" <?php echo ($edu['education'] == 'M.Sc-IT') ? 'selected' : ''; ?>>M.Sc-IT</option>
                                                        <option value="M.Tech" <?php echo ($edu['education'] == 'M.Tech') ? 'selected' : ''; ?>>M.Tech</option>
                                                        <option value="M.Sc." <?php echo ($edu['education'] == 'M.Sc.') ? 'selected' : ''; ?>>M.Sc.</option>
                                                        <option value="MCA" <?php echo ($edu['education'] == 'MCA') ? 'selected' : ''; ?>>MCA</option>
                                                        <option value="MBA" <?php echo ($edu['education'] == 'MBA') ? 'selected' : ''; ?>>MBA</option>
                                                        <option value="MA" <?php echo ($edu['education'] == 'MA') ? 'selected' : ''; ?>>MA</option>
                                                        <option value="B.Tech" <?php echo ($edu['education'] == 'B.Tech') ? 'selected' : ''; ?>>B.Tech</option>
                                                        <option value="B.Sc-IT" <?php echo ($edu['education'] == 'B.Sc-IT') ? 'selected' : ''; ?>>B.Sc-IT</option>
                                                        <option value="B.Sc" <?php echo ($edu['education'] == 'B.Sc') ? 'selected' : ''; ?>>B.Sc</option>
                                                        <option value="BCA" <?php echo ($edu['education'] == 'BCA') ? 'selected' : ''; ?>>BCA</option>
                                                        <option value="B.Com" <?php echo ($edu['education'] == 'B.Com') ? 'selected' : ''; ?>>B.Com</option>
                                                        <option value="BBA" <?php echo ($edu['education'] == 'BBA') ? 'selected' : ''; ?>>BBA</option>
                                                        <option value="B.ED" <?php echo ($edu['education'] == 'B.ED') ? 'selected' : ''; ?>>B.ED</option>
                                                        <option value="BA" <?php echo ($edu['education'] == 'BA') ? 'selected' : ''; ?>>BA</option>
                                                        <option value="12th" <?php echo ($edu['education'] == '12th') ? 'selected' : ''; ?>>12th</option>
                                                        <option value="10th" <?php echo ($edu['education'] == '10th') ? 'selected' : ''; ?>>10th</option>
                                                    </select>
                                                </td>
                                                <td><input type="text" name="session_from[]" class="form-control" placeholder="YYYY" value="<?php echo htmlspecialchars($edu['session_from'] ?? ''); ?>"></td>
                                                <td><input type="text" name="session_to[]" class="form-control" placeholder="YYYY" value="<?php echo htmlspecialchars($edu['session_to'] ?? ''); ?>"></td>
                                                <td><input type="number" name="total_marks[]" class="form-control" value="<?php echo htmlspecialchars($edu['total_marks'] ?? ''); ?>"></td>
                                                <td><input type="number" name="obt_marks[]" class="form-control" value="<?php echo htmlspecialchars($edu['obt_marks'] ?? ''); ?>"></td>
                                                <td><input type="text" name="percentage[]" class="form-control" value="<?php echo htmlspecialchars($edu['percentage'] ?? ''); ?>"></td>
                                                <td><input type="text" name="grade[]" class="form-control" value="<?php echo htmlspecialchars($edu['grade'] ?? ''); ?>"></td>
                                                <td>
                                                    <input type="file" name="marksheet[]" class="form-control">
                                                    <?php if(!empty($edu['marksheet'])): ?>
                                                        <small><a href="../uploads/teachers/marksheets/<?php echo htmlspecialchars($edu['marksheet']); ?>" target="_blank">View</a></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td>
                                                <select name="education[]" class="form-control" required>
                                                    <option value="">-- Select Education --</option>
                                                    <option value="PhD">PhD</option>
                                                    <option value="M.Sc-IT">M.Sc-IT</option>
                                                    <option value="M.Tech">M.Tech</option>
                                                    <option value="M.Sc.">M.Sc.</option>
                                                    <option value="MCA">MCA</option>
                                                    <option value="MBA">MBA</option>
                                                    <option value="MA">MA</option>
                                                    <option value="B.Tech">B.Tech</option>
                                                    <option value="B.Sc-IT">B.Sc-IT</option>
                                                    <option value="B.Sc">B.Sc</option>
                                                    <option value="BCA">BCA</option>
                                                    <option value="B.Com">B.Com</option>
                                                    <option value="BBA">BBA</option>
                                                    <option value="B.ED">B.ED</option>
                                                    <option value="BA">BA</option>
                                                    <option value="12th">12th</option>
                                                    <option value="10th">10th</option>
                                                </select>
                                            </td>
                                            <td><input type="text" name="session_from[]" class="form-control" placeholder="YYYY"></td>
                                            <td><input type="text" name="session_to[]" class="form-control" placeholder="YYYY"></td>
                                            <td><input type="number" name="total_marks[]" class="form-control"></td>
                                            <td><input type="number" name="obt_marks[]" class="form-control"></td>
                                            <td><input type="text" name="percentage[]" class="form-control"></td>
                                            <td><input type="text" name="grade[]" class="form-control"></td>
                                            <td><input type="file" name="marksheet[]" class="form-control"></td>
                                            <td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="9" class="text-left">
                                            <button type="button" class="btn btn-add" id="addRow">
                                                <i class="fas fa-plus"></i> Add More Education
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Experience -->
                    <div class="form-section">
                        <h6 class="form-section-title">
                            <i class="fas fa-briefcase me-2"></i> Experience
                        </h6>
                        
                        <div id="experience-container">
                            <?php if($edit_mode && !empty($experience_data)): ?>
                                <?php foreach($experience_data as $exp): ?>
                                    <div class="experience-row">
                                        <div class="row">
                                            <div class="col-md-2 mb-2">
                                                <label>org</label>
                                                <input type="text" name="experience_org[]" class="form-control" value="<?php echo htmlspecialchars($exp['org'] ?? ''); ?>" placeholder="org">
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <label>Role</label>
                                                <input type="text" name="experience_role[]" class="form-control" value="<?php echo htmlspecialchars($exp['role'] ?? ''); ?>" placeholder="Role">
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <label>From</label>
                                                <input type="text" name="experience_from[]" class="form-control" value="<?php echo htmlspecialchars($exp['experience_from'] ?? ''); ?>" placeholder="YYYY-MM">
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <label>To</label>
                                                <input type="text" name="experience_to[]" class="form-control" value="<?php echo htmlspecialchars($exp['experience_to'] ?? ''); ?>" placeholder="YYYY-MM">
                                            </div>
                                            <div class="col-md-3 mb-2">
                                                <label>Document</label>
                                                <input type="file" name="experience_doc[]" class="form-control">
                                                <?php if(!empty($exp['document'])): ?>
                                                    <small><a href="../uploads/teachers/experience/<?php echo htmlspecialchars($exp['document']); ?>" target="_blank">View</a></small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-1 mb-2 d-flex align-items-end">
                                                <button type="button" class="btn btn-danger btn-sm remove-experience">X</button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="experience-row">
                                    <div class="row">
                                        <div class="col-md-2 mb-2">
                                            <label>org</label>
                                            <input type="text" name="experience_org[]" class="form-control" placeholder="org">
                                        </div>
                                        <div class="col-md-2 mb-2">
                                            <label>Role</label>
                                            <input type="text" name="experience_role[]" class="form-control" placeholder="Role">
                                        </div>
                                        <div class="col-md-2 mb-2">
                                            <label>From</label>
                                            <input type="text" name="experience_from[]" class="form-control" placeholder="YYYY-MM">
                                        </div>
                                        <div class="col-md-2 mb-2">
                                            <label>To</label>
                                            <input type="text" name="experience_to[]" class="form-control" placeholder="YYYY-MM">
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label>Document</label>
                                            <input type="file" name="experience_doc[]" class="form-control">
                                        </div>
                                        <div class="col-md-1 mb-2 d-flex align-items-end">
                                            <button type="button" class="btn btn-danger btn-sm remove-experience">X</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="text-left mt-2">
                            <button type="button" class="btn btn-add" id="add-experience">
                                <i class="fas fa-plus"></i> Add More Experience
                            </button>
                        </div>
                    </div>
                    
                    <!-- Form Buttons -->
                    <div class="text-center mt-4">
                        <a href="teacher_management.php" class="btn btn-secondary me-2">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> 
                            <?php echo $edit_mode ? 'Update Teacher' : 'Save Teacher'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- View Teacher Modal -->
<div class="modal fade" id="viewTeacherModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-graduate me-2"></i>
                    Teacher Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="teacherDetails">
                Loading...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // State-District AJAX
    $('#state-dropdown').on('change', function () {
        let stateID = $(this).val();
        if (stateID) {
            $.ajax({
                type: 'POST',
                url: 'fetch_district_ajax.php',
                data: { state_id: stateID },
                success: function (html) {
                    $('#district-dropdown').html(html);
                },
                error: function (xhr, status, error) {
                    console.log("Error:", error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load districts'
                    });
                }
            });
        } else {
            $('#district-dropdown').html('<option value="">Select District</option>');
        }
    });
    
    // Add education row
    $('#addRow').click(function () {
        let newRow = `
            <tr>
                <td>
                    <select name="education[]" class="form-control" required>
                        <option value="">-- Select Education --</option>
                        <option value="PhD">PhD</option>
                        <option value="M.Sc-IT">M.Sc-IT</option>
                        <option value="M.Tech">M.Tech</option>
                        <option value="M.Sc.">M.Sc.</option>
                        <option value="MCA">MCA</option>
                        <option value="MBA">MBA</option>
                        <option value="MA">MA</option>
                        <option value="B.Tech">B.Tech</option>
                        <option value="B.Sc-IT">B.Sc-IT</option>
                        <option value="B.Sc">B.Sc</option>
                        <option value="BCA">BCA</option>
                        <option value="B.Com">B.Com</option>
                        <option value="BBA">BBA</option>
                        <option value="B.ED">B.ED</option>
                        <option value="BA">BA</option>
                        <option value="12th">12th</option>
                        <option value="10th">10th</option>
                    </select>
                </td>
                <td><input type="text" name="session_from[]" class="form-control" placeholder="YYYY"></td>
                <td><input type="text" name="session_to[]" class="form-control" placeholder="YYYY"></td>
                <td><input type="number" name="total_marks[]" class="form-control"></td>
                <td><input type="number" name="obt_marks[]" class="form-control"></td>
                <td><input type="text" name="percentage[]" class="form-control"></td>
                <td><input type="text" name="grade[]" class="form-control"></td>
                <td><input type="file" name="marksheet[]" class="form-control"></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>
            </tr>`;
        $('#education-body').append(newRow);
    });
    
    // Remove education row
    $(document).on('click', '.remove-row', function () {
        if ($('#education-body tr').length > 1) {
            $(this).closest('tr').remove();
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Cannot Remove',
                text: 'At least one education entry is required'
            });
        }
    });
    
    // Add experience row
    $('#add-experience').click(function () {
        const newRow = `
            <div class="experience-row">
                <div class="row">
                    <div class="col-md-2 mb-2">
                        <label>org</label>
                        <input type="text" name="experience_org[]" class="form-control" placeholder="org">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>Role</label>
                        <input type="text" name="experience_role[]" class="form-control" placeholder="Role">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>From</label>
                        <input type="text" name="experience_from[]" class="form-control" placeholder="YYYY-MM">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>To</label>
                        <input type="text" name="experience_to[]" class="form-control" placeholder="YYYY-MM">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label>Document</label>
                        <input type="file" name="experience_doc[]" class="form-control">
                    </div>
                    <div class="col-md-1 mb-2 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-sm remove-experience">X</button>
                    </div>
                </div>
            </div>`;
        $('#experience-container').append(newRow);
    });
    
    // Remove experience row
    $(document).on('click', '.remove-experience', function () {
        if ($('.experience-row').length > 1) {
            $(this).closest('.experience-row').remove();
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Cannot Remove',
                text: 'Keep at least one experience entry'
            });
        }
    });
    
    // Search functionality
    $('#searchTeacher').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#teacherTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
    
    // Auto-hide alerts
    setTimeout(function() {
        $('#successAlert, #errorAlert').fadeOut('slow');
    }, 5000);
    
    // Confirm delete
    function confirmDelete(event, teacherId) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '?action=delete&id=' + teacherId;
            }
        });
    }
    
    // View Teacher Function
    function viewTeacher(teacherId) {
        $('#teacherDetails').html('Loading...');
        $('#viewTeacherModal').modal('show');
        
        $.ajax({
            url: 'get_teacher_details_ajax.php',
            type: 'POST',
            data: { teacher_id: teacherId, center_id: <?php echo $center_id; ?> },
            success: function(response) {
                $('#teacherDetails').html(response);
            },
            error: function(xhr, status, error) {
                console.log("Error:", error);
                $('#teacherDetails').html('<p class="text-danger">Error loading teacher details</p>');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load teacher details'
                });
            }
        });
    }
    
    // Form validation
    function validateForm() {
        var name = $('input[name="name"]').val();
        var mobile = $('input[name="mobile"]').val();
        var email = $('input[name="email"]').val();
        
        if (!name || !mobile || !email) {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'Please fill all required fields'
            });
            return false;
        }
        
        return true;
    }
</script>

<?php include 'footer.php'; ?>