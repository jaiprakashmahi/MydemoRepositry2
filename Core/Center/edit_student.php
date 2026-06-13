<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Database connection
@include '../conn.php';

// Check if user is logged in
if (!isset($_SESSION['center_logged_in']) || $_SESSION['center_logged_in'] !== true) {
    header("Location: member_login.php");
    exit();
}

// Get session variables with validation
$center_id = isset($_SESSION['center_id']) ? $_SESSION['center_id'] : 0;
$center_name = isset($_SESSION['center_name']) ? $_SESSION['center_name'] : '';

// Check if student ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: student_list.php");
    exit();
}

$student_id = intval($_GET['id']);

// Check connection
if (!$conn) {
    die("<div class='alert alert-danger'>
            <h4>Database Connection Failed!</h4>
            <p>Error: " . mysqli_connect_error() . "</p>
         </div>");
}

// Initialize variables
$success_message = '';
$error_message = '';
$student = null;
$courses = [];
$states = [];
$centers = [];

// Fetch student details with center join
$student_query = "SELECT os.*, cd.center_name as registered_center_name 
                  FROM onlinestudents os 
                  LEFT JOIN center_details cd ON os.center_id = cd.id 
                  WHERE os.id = ?";
$student_stmt = $conn->prepare($student_query);
$student_stmt->bind_param("i", $student_id);
$student_stmt->execute();
$student_result = $student_stmt->get_result();

if ($student_result && $student_result->num_rows > 0) {
    $student = $student_result->fetch_assoc();
    
    // Check if the logged-in center has permission to edit this student
    // Allow if: student has center_id = logged-in center_id OR study_center matches logged-in center_name
    if ($student['center_id'] != $center_id && $student['study_center'] != $center_name) {
        $error_message = "<div class='alert alert-danger'>
                            <h4><i class='fas fa-ban me-2'></i> Access Denied!</h4>
                            <p>You can only edit students registered through your center.</p>
                         </div>";
        include 'header.php';
        echo $error_message;
        include 'footer.php';
        exit();
    }
} else {
    $error_message = "<div class='alert alert-danger'>
                        <h4><i class='fas fa-exclamation-circle me-2'></i> Student Not Found!</h4>
                        <p>The student record you're trying to edit does not exist.</p>
                     </div>";
    include 'header.php';
    echo $error_message;
    include 'footer.php';
    exit();
}
$student_stmt->close();

// Fetch courses
$course_query = "SELECT DISTINCT course_name, duration, price FROM courses ORDER BY course_name";
$course_result = $conn->query($course_query);
if ($course_result && $course_result->num_rows > 0) {
    while ($row = $course_result->fetch_assoc()) {
        $courses[] = $row;
    }
}

// Fetch states
$state_query = "SELECT id, name FROM states ORDER BY name";
$state_result = $conn->query($state_query);
if ($state_result && $state_result->num_rows > 0) {
    while ($row = $state_result->fetch_assoc()) {
        $states[] = $row;
    }
}

// Fetch centers - Only show logged-in center
if ($center_id > 0) {
    $center_query = "SELECT id, center_name FROM center_details WHERE id = ?";
    $center_stmt = $conn->prepare($center_query);
    $center_stmt->bind_param("i", $center_id);
    $center_stmt->execute();
    $center_result = $center_stmt->get_result();
    
    if ($center_result && $center_result->num_rows > 0) {
        $centers = $center_result->fetch_all(MYSQLI_ASSOC);
    }
    $center_stmt->close();
}

// Handle AJAX request for districts
if (isset($_POST['state_id']) && isset($_POST['ajax']) && $_POST['ajax'] == 'true') {
    $state_id = intval($_POST['state_id']);
    
    $stmt = $conn->prepare("SELECT id, name FROM districts WHERE state_id = ? ORDER BY name");
    $stmt->bind_param("i", $state_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "<option value=''>Select District</option>";
    while ($row = $result->fetch_assoc()) {
        echo "<option value='{$row['id']}'>{$row['name']}</option>";
    }
    exit();
}

// Get state ID from state name
$state_id_from_db = 0;
if (!empty($student['state'])) {
    $state_stmt = $conn->prepare("SELECT id FROM states WHERE name = ?");
    $state_stmt->bind_param("s", $student['state']);
    $state_stmt->execute();
    $state_result = $state_stmt->get_result();
    if ($state_result && $state_result->num_rows > 0) {
        $state_row = $state_result->fetch_assoc();
        $state_id_from_db = $state_row['id'];
    }
    $state_stmt->close();
}

// Get district ID from district name
$district_id_from_db = 0;
if (!empty($student['district']) && $state_id_from_db > 0) {
    $district_stmt = $conn->prepare("SELECT id FROM districts WHERE name = ? AND state_id = ?");
    $district_stmt->bind_param("si", $student['district'], $state_id_from_db);
    $district_stmt->execute();
    $district_result = $district_stmt->get_result();
    if ($district_result && $district_result->num_rows > 0) {
        $district_row = $district_result->fetch_assoc();
        $district_id_from_db = $district_row['id'];
    }
    $district_stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    
    // Validate required fields
    $errors = [];
    
    $required = ['name', 'father_name', 'mother_name', 'email', 'dob', 'gender', 'mobile', 
                 'address', 'city', 'pin_code', 'state', 'district', 'study_center', 
                 'course_name', 'reg_amount', 'payment_mode', 'payment_status', 
                 'session_start', 'session_end'];
    
    foreach ($required as $field) {
        if (empty(trim($_POST[$field]))) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required";
        }
    }
    
    // Validate email
    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Validate mobile number
    if (!empty($_POST['mobile']) && !preg_match('/^[0-9]{10}$/', $_POST['mobile'])) {
        $errors[] = "Mobile number must be 10 digits";
    }
    
    // If no validation errors, proceed
    if (empty($errors)) {
        
        // Get form data
        $name = $conn->real_escape_string(trim($_POST['name']));
        $father_name = $conn->real_escape_string(trim($_POST['father_name']));
        $mother_name = $conn->real_escape_string(trim($_POST['mother_name']));
        $email = $conn->real_escape_string(trim($_POST['email']));
        $dob = $_POST['dob'];
        $gender = $conn->real_escape_string(trim($_POST['gender']));
        $blood_group = isset($_POST['blood_group']) ? $conn->real_escape_string(trim($_POST['blood_group'])) : '';
        $mobile = $conn->real_escape_string(trim($_POST['mobile']));
        $address = $conn->real_escape_string(trim($_POST['address']));
        $city = $conn->real_escape_string(trim($_POST['city']));
        $pin_code = $conn->real_escape_string(trim($_POST['pin_code']));
        $block = isset($_POST['block']) ? $conn->real_escape_string(trim($_POST['block'])) : '';
        $post_office = isset($_POST['post_office']) ? $conn->real_escape_string(trim($_POST['post_office'])) : '';
        $state_id_form = intval($_POST['state']);
        $district_id_form = intval($_POST['district']);
        $study_center = $conn->real_escape_string(trim($_POST['study_center']));
        $course_name = $conn->real_escape_string(trim($_POST['course_name']));
        $duration = isset($_POST['duration']) ? $conn->real_escape_string(trim($_POST['duration'])) : '';
        $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
        $reg_amount = floatval($_POST['reg_amount']);
        $payment_mode = $conn->real_escape_string(trim($_POST['payment_mode']));
        $payment_status = $conn->real_escape_string(trim($_POST['payment_status']));
        $session_start = $_POST['session_start'];
        $session_end = $_POST['session_end'];
        
        // Get state and district names
        $state_name = '';
        $district_name = '';
        
        if ($state_id_form > 0) {
            $state_stmt = $conn->prepare("SELECT name FROM states WHERE id = ?");
            $state_stmt->bind_param("i", $state_id_form);
            $state_stmt->execute();
            $state_result = $state_stmt->get_result();
            if ($state_result && $state_result->num_rows > 0) {
                $state_name = $state_result->fetch_assoc()['name'];
            }
            $state_stmt->close();
        }
        
        if ($district_id_form > 0) {
            $district_stmt = $conn->prepare("SELECT name FROM districts WHERE id = ?");
            $district_stmt->bind_param("i", $district_id_form);
            $district_stmt->execute();
            $district_result = $district_stmt->get_result();
            if ($district_result && $district_result->num_rows > 0) {
                $district_name = $district_result->fetch_assoc()['name'];
            }
            $district_stmt->close();
        }
        
        // Handle file uploads
        $photo = $student['photo'];
        $id_proof = $student['id_proof'];
        $upload_errors = [];
        
        // Upload new photo if provided
        if (!empty($_FILES['photo']['name'])) {
            $photo_name = $_FILES['photo']['name'];
            $photo_tmp = $_FILES['photo']['tmp_name'];
            $photo_size = $_FILES['photo']['size'];
            $photo_ext = strtolower(pathinfo($photo_name, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
            $max_size = 2 * 1024 * 1024; // 2MB
            
            if ($photo_size > $max_size) {
                $upload_errors[] = "Photo size exceeds 2MB limit";
            } elseif (in_array($photo_ext, $allowed_ext)) {
                $new_photo_name = "photo_" . $student['student_code'] . "." . $photo_ext;
                $photo_path = "../uploads/students/photos/" . $new_photo_name;
                
                // Delete old photo if exists
                if (!empty($photo) && file_exists("../uploads/students/photos/" . $photo)) {
                    unlink("../uploads/students/photos/" . $photo);
                }
                
                if (move_uploaded_file($photo_tmp, $photo_path)) {
                    $photo = $new_photo_name;
                } else {
                    $upload_errors[] = "Photo upload failed. Check permissions.";
                }
            } else {
                $upload_errors[] = "Invalid photo format. Allowed: JPG, JPEG, PNG, GIF";
            }
        }
        
        // Upload new ID proof if provided
        if (!empty($_FILES['id_proof']['name'])) {
            $id_name = $_FILES['id_proof']['name'];
            $id_tmp = $_FILES['id_proof']['tmp_name'];
            $id_size = $_FILES['id_proof']['size'];
            $id_ext = strtolower(pathinfo($id_name, PATHINFO_EXTENSION));
            $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if ($id_size > $max_size) {
                $upload_errors[] = "ID proof size exceeds 5MB limit";
            } elseif (in_array($id_ext, $allowed_ext)) {
                $new_id_name = "id_" . $student['student_code'] . "." . $id_ext;
                $id_path = "../uploads/students/id_proofs/" . $new_id_name;
                
                // Delete old ID proof if exists
                if (!empty($id_proof) && file_exists("../uploads/students/id_proofs/" . $id_proof)) {
                    unlink("../uploads/students/id_proofs/" . $id_proof);
                }
                
                if (move_uploaded_file($id_tmp, $id_path)) {
                    $id_proof = $new_id_name;
                } else {
                    $upload_errors[] = "ID proof upload failed. Check permissions.";
                }
            } else {
                $upload_errors[] = "Invalid ID proof format. Allowed: PDF, JPG, JPEG, PNG";
            }
        }
        
        // If file uploads successful (or no new files), update database
        if (empty($upload_errors)) {
            
            // First, check if center_id column exists
            $column_check = $conn->query("SHOW COLUMNS FROM onlinestudents LIKE 'center_id'");
            $has_center_id_column = ($column_check && $column_check->num_rows > 0);
            
            // Prepare SQL query based on whether center_id column exists
            if ($has_center_id_column) {
                // With center_id column
                $sql = "UPDATE onlinestudents SET 
                        name = ?, father_name = ?, mother_name = ?, 
                        email = ?, dob = ?, gender = ?, blood_group = ?, mobile = ?, 
                        address = ?, city = ?, pin_code = ?, block = ?, post_office = ?, 
                        state = ?, district = ?, study_center = ?, course_name = ?, 
                        duration = ?, price = ?, reg_amount = ?, payment_mode = ?, 
                        payment_status = ?, session_start = ?, session_end = ?, 
                        photo = ?, id_proof = ?, center_id = ?, updated_at = NOW()
                        WHERE id = ?";
                
                $stmt = $conn->prepare($sql);
                
                if ($stmt) {
                    // Debug: Count the parameters
                    $params = [
                        $name, $father_name, $mother_name, $email, $dob, $gender, 
                        $blood_group, $mobile, $address, $city, $pin_code, $block, 
                        $post_office, $state_name, $district_name, $study_center, $course_name,
                        $duration, $price, $reg_amount, $payment_mode, $payment_status,
                        $session_start, $session_end, $photo, $id_proof, $center_id,
                        $student_id
                    ];
                    
                    // Count parameters: 28 total
                    // Types: 26 strings (s), 2 doubles (d), 2 integers (i)
                    // But wait: price and reg_amount are doubles (d), center_id and student_id are integers (i)
                    // So: 24 strings, 2 doubles, 2 integers = 28 characters
                    $types = "ssssssssssssssssssssddssssssii";
                    
                    $stmt->bind_param($types, ...$params);
                }
            } else {
                // Without center_id column
                $sql = "UPDATE onlinestudents SET 
                        name = ?, father_name = ?, mother_name = ?, 
                        email = ?, dob = ?, gender = ?, blood_group = ?, mobile = ?, 
                        address = ?, city = ?, pin_code = ?, block = ?, post_office = ?, 
                        state = ?, district = ?, study_center = ?, course_name = ?, 
                        duration = ?, price = ?, reg_amount = ?, payment_mode = ?, 
                        payment_status = ?, session_start = ?, session_end = ?, 
                        photo = ?, id_proof = ?, updated_at = NOW()
                        WHERE id = ?";
                
                $stmt = $conn->prepare($sql);
                
                if ($stmt) {
                    // Debug: Count the parameters
                    $params = [
                        $name, $father_name, $mother_name, $email, $dob, $gender, 
                        $blood_group, $mobile, $address, $city, $pin_code, $block, 
                        $post_office, $state_name, $district_name, $study_center, $course_name,
                        $duration, $price, $reg_amount, $payment_mode, $payment_status,
                        $session_start, $session_end, $photo, $id_proof,
                        $student_id
                    ];
                    
                    // Count parameters: 27 total
                    // Types: 25 strings (s), 2 doubles (d), 1 integer (i)
                    $types = "ssssssssssssssssssssddssssssi";
                    
                    $stmt->bind_param($types, ...$params);
                }
            }
            
            if ($stmt) {
                if ($stmt->execute()) {
                    $success_message = "
                        <div class='alert alert-success alert-dismissible fade show'>
                            <h4><i class='fas fa-check-circle'></i> Update Successful!</h4>
                            <p>Student details have been updated successfully.</p>
                            <div class='mt-3'>
                                <a href='student_list.php' class='btn btn-primary me-2'>
                                    <i class='fas fa-list me-2'></i> View All Students
                                </a>
                                <a href='view_student.php?id=" . $student_id . "' class='btn btn-info'>
                                    <i class='fas fa-eye me-2'></i> View Student Details
                                </a>
                            </div>
                            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                        </div>
                    ";
                    
                    // Refresh student data
                    $student_result = $conn->query("SELECT * FROM onlinestudents WHERE id = $student_id");
                    if ($student_result && $student_result->num_rows > 0) {
                        $student = $student_result->fetch_assoc();
                    }
                    
                } else {
                    $errors[] = "Database Error: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $errors[] = "SQL Prepare Error: " . $conn->error;
            }
        } else {
            $errors = array_merge($errors, $upload_errors);
        }
    }
    
    // If there are errors, display them
    if (!empty($errors)) {
        $error_message = "<div class='alert alert-danger alert-dismissible fade show'>
                            <h4><i class='fas fa-exclamation-circle me-2'></i> Update Failed!</h4>
                            <p>Please correct the following errors:</p>
                            <ul>";
        foreach ($errors as $error) {
            $error_message .= "<li>" . htmlspecialchars($error) . "</li>";
        }
        $error_message .= "</ul><button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    }
}

include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student - Sharnay Institute</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .registration-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            margin: 20px auto;
            max-width: 1200px;
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .card-header h1 {
            font-weight: 700;
            font-size: 2.5rem;
            margin: 0;
        }
        
        .form-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }
        
        .section-title {
            color: #667eea;
            font-weight: 600;
            font-size: 1.3rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .required::after {
            content: " *";
            color: #dc3545;
        }
        
        .file-upload-container {
            border: 2px dashed #adb5bd;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .file-upload-container:hover {
            border-color: #667eea;
            background: #e9ecef;
        }
        
        .btn-update {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 12px 40px;
            font-weight: 600;
            font-size: 1.1rem;
            border-radius: 8px;
        }
        
        .btn-update:hover {
            background: linear-gradient(135deg, #218838, #1ba87e);
            color: white;
        }
        
        .photo-preview {
            width: 150px;
            height: 150px;
            background: #f8f9fa;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            margin: 10px auto;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        
        .current-center {
            background-color: #e8f4fd !important;
            font-weight: bold;
            color: #0066cc !important;
        }
        
        .current-center-indicator {
            font-size: 0.8rem;
            color: #0066cc;
            font-weight: normal;
        }
        
        .select2-container--classic .select2-selection--single {
            height: 38px;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
        }
        
        .select2-container--classic .select2-selection--single .select2-selection__rendered {
            line-height: 38px;
        }
        
        .select2-container--classic .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
        
        .student-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
        }
        
        .info-value {
            color: #212529;
        }
    </style>
</head>
<body>
    <div class="registration-card">
        <div class="card-header">
            <h1><i class="fas fa-user-edit me-3"></i>Edit Student</h1>
            <p class="mb-0">Sharnay Institute - Student Management System</p>
            <p class="mb-0 mt-2"><i class="fas fa-building me-2"></i>Center: <?php echo htmlspecialchars($center_name); ?></p>
        </div>
        
        <div class="card-body p-4">
            <!-- Success Message -->
            <?php if (!empty($success_message)): ?>
                <?php echo $success_message; ?>
            <?php endif; ?>
            
            <!-- Error Messages -->
            <?php if (!empty($error_message)): ?>
                <?php echo $error_message; ?>
            <?php endif; ?>
            
            <!-- Student Basic Info -->
            <div class="student-info mb-4">
                <h5 class="section-title"><i class="fas fa-info-circle me-2"></i> Student Information</h5>
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <span class="info-label">Student Code:</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['student_code']); ?></span>
                    </div>
                    <div class="col-md-3 mb-2">
                        <span class="info-label">Username:</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['username']); ?></span>
                    </div>
                    <div class="col-md-3 mb-2">
                        <span class="info-label">Registered Date:</span>
                        <span class="info-value"><?php echo date('d-m-Y', strtotime($student['created_at'])); ?></span>
                    </div>
                    <div class="col-md-3 mb-2">
                        <span class="info-label">Registered Center:</span>
                        <span class="info-value">
                            <?php echo isset($student['registered_center_name']) ? htmlspecialchars($student['registered_center_name']) : htmlspecialchars($student['study_center']); ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <form action="" method="POST" enctype="multipart/form-data" id="studentForm">
                <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
                
                <!-- Personal Information -->
                <div class="form-section">
                    <h5 class="section-title"><i class="fas fa-user-circle me-2"></i> Personal Information</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label required">Full Name</label>
                            <input type="text" class="form-control" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : htmlspecialchars($student['name']); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Father's Name</label>
                            <input type="text" class="form-control" name="father_name" value="<?php echo isset($_POST['father_name']) ? htmlspecialchars($_POST['father_name']) : htmlspecialchars($student['father_name']); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Mother's Name</label>
                            <input type="text" class="form-control" name="mother_name" value="<?php echo isset($_POST['mother_name']) ? htmlspecialchars($_POST['mother_name']) : htmlspecialchars($student['mother_name']); ?>" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label required">Email</label>
                            <input type="email" class="form-control" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : htmlspecialchars($student['email']); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Mobile</label>
                            <input type="tel" class="form-control" name="mobile" value="<?php echo isset($_POST['mobile']) ? htmlspecialchars($_POST['mobile']) : htmlspecialchars($student['mobile']); ?>" pattern="[0-9]{10}" required>
                            <small class="text-muted">10 digits only</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Date of Birth</label>
                            <input type="date" class="form-control" name="dob" value="<?php echo isset($_POST['dob']) ? htmlspecialchars($_POST['dob']) : htmlspecialchars($student['dob']); ?>" max="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label required">Gender</label>
                            <select class="form-select" name="gender" required>
                                <option value="">Select</option>
                                <option value="Male" <?php echo ((isset($_POST['gender']) && $_POST['gender'] == 'Male') || $student['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ((isset($_POST['gender']) && $_POST['gender'] == 'Female') || $student['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo ((isset($_POST['gender']) && $_POST['gender'] == 'Other') || $student['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Blood Group</label>
                            <select class="form-select" name="blood_group">
                                <option value="">Select</option>
                                <?php
                                $blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                                foreach ($blood_groups as $bg):
                                    $selected = ((isset($_POST['blood_group']) && $_POST['blood_group'] == $bg) || $student['blood_group'] == $bg) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $bg; ?>" <?php echo $selected; ?>><?php echo $bg; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Address Information -->
                <div class="form-section">
                    <h5 class="section-title"><i class="fas fa-map-marker-alt me-2"></i> Address Information</h5>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label required">Complete Address</label>
                            <textarea class="form-control" name="address" rows="2" required><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : htmlspecialchars($student['address']); ?></textarea>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label required">City</label>
                            <input type="text" class="form-control" name="city" value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : htmlspecialchars($student['city']); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Pin Code</label>
                            <input type="text" class="form-control" name="pin_code" value="<?php echo isset($_POST['pin_code']) ? htmlspecialchars($_POST['pin_code']) : htmlspecialchars($student['pin_code']); ?>" pattern="[0-9]{6}" required>
                            <small class="text-muted">6 digits only</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Block</label>
                            <input type="text" class="form-control" name="block" value="<?php echo isset($_POST['block']) ? htmlspecialchars($_POST['block']) : htmlspecialchars($student['block']); ?>">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Post Office</label>
                            <input type="text" class="form-control" name="post_office" value="<?php echo isset($_POST['post_office']) ? htmlspecialchars($_POST['post_office']) : htmlspecialchars($student['post_office']); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">State</label>
                            <select class="form-select select2-state" id="state" name="state" required>
                                <option value="">Select State</option>
                                <?php foreach ($states as $state): ?>
                                    <?php 
                                    $selected = '';
                                    if (isset($_POST['state']) && $_POST['state'] == $state['id']) {
                                        $selected = 'selected';
                                    } elseif ($state_id_from_db == $state['id']) {
                                        $selected = 'selected';
                                    }
                                    ?>
                                    <option value="<?php echo $state['id']; ?>" <?php echo $selected; ?>>
                                        <?php echo htmlspecialchars($state['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">District</label>
                            <select class="form-select select2-district" id="district" name="district" required>
                                <option value="">Select District</option>
                                <?php
                                // If we have district_id, fetch and display it
                                if ($district_id_from_db > 0) {
                                    $district_result = $conn->query("SELECT id, name FROM districts WHERE id = " . $district_id_from_db);
                                    if ($district_result && $district_result->num_rows > 0) {
                                        $district_row = $district_result->fetch_assoc();
                                        echo "<option value=\"{$district_row['id']}\" selected>{$district_row['name']}</option>";
                                    }
                                }
                                // Also check POST data
                                if (isset($_POST['district']) && $_POST['district'] > 0 && !empty($_POST['state'])) {
                                    $district_result = $conn->query("SELECT id, name FROM districts WHERE id = " . intval($_POST['district']));
                                    if ($district_result && $district_result->num_rows > 0) {
                                        $district = $district_result->fetch_assoc();
                                        echo "<option value=\"{$district['id']}\" selected>{$district['name']}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Institute Details -->
                <div class="form-section">
                    <h5 class="section-title"><i class="fas fa-graduation-cap me-2"></i> Institute Details</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label required">Study Center</label>
                            <select class="form-select" name="study_center" required <?php echo (count($centers) == 1) ? 'disabled' : ''; ?>>
                                <option value="">Select Center</option>
                                <?php foreach ($centers as $center): ?>
                                    <?php 
                                    $selected = '';
                                    if (isset($_POST['study_center']) && $_POST['study_center'] == $center['center_name']) {
                                        $selected = 'selected';
                                    } elseif ($student['study_center'] == $center['center_name'] || $center['center_name'] == $center_name) {
                                        $selected = 'selected';
                                    }
                                    ?>
                                    <option value="<?php echo htmlspecialchars($center['center_name']); ?>" <?php echo $selected; ?> class="<?php echo ($center['center_name'] == $center_name) ? 'current-center' : ''; ?>">
                                        <?php echo htmlspecialchars($center['center_name']); ?>
                                        <?php if ($center['center_name'] == $center_name): ?>
                                            <span class="current-center-indicator">(Your Center)</span>
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (count($centers) == 1): ?>
                                <input type="hidden" name="study_center" value="<?php echo htmlspecialchars($centers[0]['center_name']); ?>">
                                <small class="text-muted">You can only edit students for your own center</small>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label required">Course Name</label>
                            <select class="form-select" id="course_name" name="course_name" required>
                                <option value="">Select Course</option>
                                <?php foreach ($courses as $course): ?>
                                    <?php 
                                    $selected = '';
                                    if (isset($_POST['course_name']) && $_POST['course_name'] == $course['course_name']) {
                                        $selected = 'selected';
                                    } elseif ($student['course_name'] == $course['course_name']) {
                                        $selected = 'selected';
                                    }
                                    ?>
                                    <option value="<?php echo htmlspecialchars($course['course_name']); ?>"
                                            data-duration="<?php echo htmlspecialchars($course['duration']); ?>"
                                            data-price="<?php echo htmlspecialchars($course['price']); ?>"
                                            <?php echo $selected; ?>>
                                        <?php echo htmlspecialchars($course['course_name']); ?>
                                        <?php if ($course['price'] > 0): ?>
                                            (₹<?php echo number_format($course['price'], 2); ?>)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Duration</label>
                            <input type="text" class="form-control" id="duration" name="duration" value="<?php echo isset($_POST['duration']) ? htmlspecialchars($_POST['duration']) : htmlspecialchars($student['duration']); ?>" readonly>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Course Fee (₹)</label>
                            <input type="text" class="form-control" id="price" name="price" value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : htmlspecialchars($student['price']); ?>" readonly>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label required">Registration Amount (₹)</label>
                            <input type="number" class="form-control" name="reg_amount" value="<?php echo isset($_POST['reg_amount']) ? htmlspecialchars($_POST['reg_amount']) : htmlspecialchars($student['reg_amount']); ?>" min="0" step="0.01" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label required">Payment Mode</label>
                            <select class="form-select" name="payment_mode" required>
                                <option value="">Select</option>
                                <?php
                                $payment_modes = ['Cash', 'Online', 'Cheque', 'Card', 'UPI'];
                                foreach ($payment_modes as $mode):
                                    $selected = ((isset($_POST['payment_mode']) && $_POST['payment_mode'] == $mode) || $student['payment_mode'] == $mode) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $mode; ?>" <?php echo $selected; ?>><?php echo $mode; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label required">Payment Status</label>
                            <select class="form-select" name="payment_status" required>
                                <option value="">Select</option>
                                <?php
                                $payment_statuses = ['Paid', 'Pending', 'Partial'];
                                foreach ($payment_statuses as $status):
                                    $selected = ((isset($_POST['payment_status']) && $_POST['payment_status'] == $status) || $student['payment_status'] == $status) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $status; ?>" <?php echo $selected; ?>><?php echo $status; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label required">Session Start</label>
                            <input type="date" class="form-control" name="session_start" value="<?php echo isset($_POST['session_start']) ? htmlspecialchars($_POST['session_start']) : htmlspecialchars($student['session_start']); ?>" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label required">Session End</label>
                            <input type="date" class="form-control" name="session_end" value="<?php echo isset($_POST['session_end']) ? htmlspecialchars($_POST['session_end']) : htmlspecialchars($student['session_end']); ?>" required>
                        </div>
                    </div>
                </div>
                
                <!-- Document Upload -->
                <div class="form-section">
                    <h5 class="section-title"><i class="fas fa-file-upload me-2"></i> Document Upload</h5>
                    <p class="text-muted mb-3"><i class="fas fa-info-circle me-2"></i> Upload new files only if you want to replace existing ones.</p>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">Student Photo</label>
                            <div class="mb-3">
                                <?php if (!empty($student['photo'])): ?>
                                    <p class="mb-2"><strong>Current Photo:</strong> <?php echo htmlspecialchars($student['photo']); ?></p>
                                    <div class="photo-preview mb-3" id="currentPhotoPreview" style="background-image: url('../uploads/students/photos/<?php echo htmlspecialchars($student['photo']); ?>')"></div>
                                <?php else: ?>
                                    <p class="text-danger mb-3"><i class="fas fa-exclamation-triangle me-2"></i>No photo uploaded</p>
                                <?php endif; ?>
                            </div>
                            <div class="file-upload-container" onclick="document.getElementById('photo').click()">
                                <input type="file" id="photo" name="photo" accept="image/*" style="display: none;" onchange="previewPhoto(this)">
                                <i class="fas fa-camera fa-3x mb-3 text-muted"></i>
                                <h6>Upload New Photo</h6>
                                <p class="text-muted mb-2">JPG, PNG, GIF (Max 2MB)</p>
                                <p id="photoFileName" class="small text-muted">No file chosen</p>
                                <div class="photo-preview mt-3" id="photoPreview"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">ID Proof</label>
                            <div class="mb-3">
                                <?php if (!empty($student['id_proof'])): ?>
                                    <p class="mb-2"><strong>Current ID Proof:</strong> <?php echo htmlspecialchars($student['id_proof']); ?></p>
                                    <?php if (strtolower(pathinfo($student['id_proof'], PATHINFO_EXTENSION)) === 'pdf'): ?>
                                        <a href="../uploads/students/id_proofs/<?php echo htmlspecialchars($student['id_proof']); ?>" target="_blank" class="btn btn-sm btn-info">
                                            <i class="fas fa-file-pdf me-2"></i>View PDF
                                        </a>
                                    <?php else: ?>
                                        <a href="../uploads/students/id_proofs/<?php echo htmlspecialchars($student['id_proof']); ?>" target="_blank" class="btn btn-sm btn-info">
                                            <i class="fas fa-image me-2"></i>View Image
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p class="text-danger mb-3"><i class="fas fa-exclamation-triangle me-2"></i>No ID proof uploaded</p>
                                <?php endif; ?>
                            </div>
                            <div class="file-upload-container" onclick="document.getElementById('id_proof').click()">
                                <input type="file" id="id_proof" name="id_proof" accept=".pdf,.jpg,.jpeg,.png" style="display: none;" onchange="previewIdProof(this)">
                                <i class="fas fa-id-card fa-3x mb-3 text-muted"></i>
                                <h6>Upload New ID Proof</h6>
                                <p class="text-muted mb-2">PDF, JPG, PNG (Max 5MB)</p>
                                <p id="idProofFileName" class="small text-muted">No file chosen</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <div class="text-center mt-4">
                    <button type="submit" name="update" class="btn btn-update btn-lg me-3">
                        <i class="fas fa-save me-2"></i> Update Student
                    </button>
                    <a href="student_list.php" class="btn btn-secondary btn-lg me-3">
                        <i class="fas fa-times me-2"></i> Cancel
                    </a>
                    <a href="view_student.php?id=<?php echo $student_id; ?>" class="btn btn-info btn-lg">
                        <i class="fas fa-eye me-2"></i> View Student
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        $(document).ready(function() {
            console.log("Edit page loaded successfully");
            
            // Initialize Select2
            $('.form-select').select2({
                theme: "classic",
                width: '100%',
                placeholder: function() {
                    return $(this).data('placeholder') || 'Select an option';
                }
            });
            
            // Set maximum date for DOB to today
            $('input[name="dob"]').attr('max', new Date().toISOString().split('T')[0]);
            
            // Course change event
            $('#course_name').on('change', function() {
                const selected = $(this).find('option:selected');
                if (selected.val()) {
                    $('#duration').val(selected.data('duration') || '');
                    $('#price').val(selected.data('price') || '');
                } else {
                    $('#duration').val('');
                    $('#price').val('');
                }
            });
            
            // If course was selected, set duration and price
            const selectedCourse = $('#course_name').find('option:selected');
            if (selectedCourse.val() && selectedCourse.data('duration')) {
                $('#duration').val(selectedCourse.data('duration'));
                $('#price').val(selectedCourse.data('price'));
            }
            
            // State change event
            $('#state').on('change', function() {
                const stateId = $(this).val();
                
                if (stateId) {
                    $.ajax({
                        url: '',
                        type: 'POST',
                        data: { 
                            state_id: stateId,
                            ajax: 'true'
                        },
                        beforeSend: function() {
                            $('#district').html('<option value="">Loading...</option>');
                            $('#district').prop('disabled', true);
                        },
                        success: function(response) {
                            $('#district').html(response);
                            $('#district').prop('disabled', false);
                            $('#district').select2({
                                theme: "classic",
                                width: '100%'
                            });
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error:", error);
                            $('#district').html('<option value="">Error loading districts</option>');
                            $('#district').prop('disabled', false);
                            $('#district').select2({
                                theme: "classic",
                                width: '100%'
                            });
                        }
                    });
                } else {
                    $('#district').html('<option value="">Select District</option>');
                    $('#district').val('');
                    $('#district').trigger('change');
                }
            });
            
            // Trigger change if state is already selected
            if ($('#state').val()) {
                $('#state').trigger('change');
            }
            
            // Auto-calculate session end based on duration
            $('#duration').on('change', function() {
                const duration = $(this).val();
                const startDate = $('input[name="session_start"]').val();
                
                if (duration && startDate) {
                    const start = new Date(startDate);
                    const [num, unit] = duration.split(' ');
                    const numValue = parseInt(num);
                    
                    if (!isNaN(numValue) && unit) {
                        const end = new Date(start);
                        
                        switch(unit.toLowerCase()) {
                            case 'months':
                            case 'month':
                                end.setMonth(start.getMonth() + numValue);
                                break;
                            case 'years':
                            case 'year':
                                end.setFullYear(start.getFullYear() + numValue);
                                break;
                            case 'days':
                            case 'day':
                                end.setDate(start.getDate() + numValue);
                                break;
                            case 'weeks':
                            case 'week':
                                end.setDate(start.getDate() + (numValue * 7));
                                break;
                            default:
                                return;
                        }
                        
                        $('input[name="session_end"]').val(end.toISOString().split('T')[0]);
                    }
                }
            });
        });
        
        function previewPhoto(input) {
            const file = input.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    alert('Photo size must be less than 2MB');
                    input.value = '';
                    $('#photoFileName').text('No file chosen').removeClass('text-success').addClass('text-muted');
                    $('#photoPreview').css('background-image', 'none');
                    return;
                }
                
                $('#photoFileName').text(file.name).addClass('text-success').removeClass('text-muted');
                
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#photoPreview').css('background-image', 'url(' + e.target.result + ')');
                    };
                    reader.readAsDataURL(file);
                }
            } else {
                $('#photoFileName').text('No file chosen').removeClass('text-success').addClass('text-muted');
                $('#photoPreview').css('background-image', 'none');
            }
        }
        
        function previewIdProof(input) {
            const file = input.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) {
                    alert('ID proof size must be less than 5MB');
                    input.value = '';
                    $('#idProofFileName').text('No file chosen').removeClass('text-success').addClass('text-muted');
                    return;
                }
                
                $('#idProofFileName').text(file.name).addClass('text-success').removeClass('text-muted');
            } else {
                $('#idProofFileName').text('No file chosen').removeClass('text-success').addClass('text-muted');
            }
        }
    </script>

<?php
include 'footer.php';
// Close connection
if (isset($conn)) {
    $conn->close();
}
?>