<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering
ob_start();

// Database connection
@include 'conn.php';

// Check connection
if (!$conn) {
    die("<div class='alert alert-danger'>
            <h4>Database Connection Failed!</h4>
            <p>Error: " . mysqli_connect_error() . "</p>
         </div>");
}

// Create upload directories
$upload_dirs = ['uploads/students/photos/', 'uploads/students/id_proofs/'];
foreach ($upload_dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Initialize variables
$success_message = '';
$error_message = '';
$centers = [];
$courses = [];
$states = [];

// Fetch centers
$center_query = "SELECT id, center_name FROM center_details ORDER BY center_name";
$center_result = $conn->query($center_query);
if ($center_result && $center_result->num_rows > 0) {
    while ($row = $center_result->fetch_assoc()) {
        $centers[] = $row;
    }
}

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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    
    // Validate required fields
    $errors = [];
    
    $required = ['name', 'father_name', 'mother_name', 'email', 'dob', 'gender', 'mobile', 
                 'address', 'city', 'pin_code', 'state', 'district', 'study_center', 
                 'course_name', 'reg_amount', 'payment_mode', 'payment_status', 
                 'session_start', 'session_end'];
    
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required";
        }
    }
    
    // Check file uploads
    if (empty($_FILES['photo']['name'])) {
        $errors[] = "Student photo is required";
    }
    if (empty($_FILES['id_proof']['name'])) {
        $errors[] = "ID proof is required";
    }
    
    // If no validation errors, proceed
    if (empty($errors)) {
        
        // Get form data
        $name = $conn->real_escape_string($_POST['name']);
        $father_name = $conn->real_escape_string($_POST['father_name']);
        $mother_name = $conn->real_escape_string($_POST['mother_name']);
        $email = $conn->real_escape_string($_POST['email']);
        $dob = $_POST['dob'];
        $gender = $conn->real_escape_string($_POST['gender']);
        $blood_group = isset($_POST['blood_group']) ? $conn->real_escape_string($_POST['blood_group']) : '';
        $mobile = $conn->real_escape_string($_POST['mobile']);
        $address = $conn->real_escape_string($_POST['address']);
        $city = $conn->real_escape_string($_POST['city']);
        $pin_code = $conn->real_escape_string($_POST['pin_code']);
        $block = isset($_POST['block']) ? $conn->real_escape_string($_POST['block']) : '';
        $post_office = isset($_POST['post_office']) ? $conn->real_escape_string($_POST['post_office']) : '';
        $state_id = intval($_POST['state']);
        $district_id = intval($_POST['district']);
        $study_center = $conn->real_escape_string($_POST['study_center']);
        $course_name = $conn->real_escape_string($_POST['course_name']);
        $duration = isset($_POST['duration']) ? $conn->real_escape_string($_POST['duration']) : '';
        $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
        $reg_amount = floatval($_POST['reg_amount']);
        $payment_mode = $conn->real_escape_string($_POST['payment_mode']);
        $payment_status = $conn->real_escape_string($_POST['payment_status']);
        $session_start = $_POST['session_start'];
        $session_end = $_POST['session_end'];
        
        // Get state and district names
        $state_name = '';
        $district_name = '';
        
        if ($state_id > 0) {
            $state_result = $conn->query("SELECT name FROM states WHERE id = $state_id");
            if ($state_result && $state_result->num_rows > 0) {
                $state_name = $state_result->fetch_assoc()['name'];
            }
        }
        
        if ($district_id > 0) {
            $district_result = $conn->query("SELECT name FROM districts WHERE id = $district_id");
            if ($district_result && $district_result->num_rows > 0) {
                $district_name = $district_result->fetch_assoc()['name'];
            }
        }
        
        // Generate student code and credentials
        $student_code = "STU"  . rand(100, 999);
        $username = strtolower(preg_replace('/[^a-z0-9]/i', '', $name)) . rand(1000, 9999);
        $raw_password = substr(md5(uniqid()), 0, 8);
        $password_hash = password_hash($raw_password, PASSWORD_DEFAULT);
        
        // Handle file uploads
        $photo = '';
        $id_proof = '';
        $upload_errors = [];
        
        // Upload photo
        if (!empty($_FILES['photo']['name'])) {
            $photo_name = $_FILES['photo']['name'];
            $photo_tmp = $_FILES['photo']['tmp_name'];
            $photo_ext = strtolower(pathinfo($photo_name, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($photo_ext, $allowed_ext)) {
                $new_photo_name = "photo_" . $student_code . "." . $photo_ext;
                $photo_path = "uploads/students/photos/" . $new_photo_name;
                
                if (move_uploaded_file($photo_tmp, $photo_path)) {
                    $photo = $new_photo_name;
                } else {
                    $upload_errors[] = "Photo upload failed. Check permissions.";
                }
            } else {
                $upload_errors[] = "Invalid photo format. Allowed: JPG, JPEG, PNG, GIF";
            }
        }
        
        // Upload ID proof
        if (!empty($_FILES['id_proof']['name'])) {
            $id_name = $_FILES['id_proof']['name'];
            $id_tmp = $_FILES['id_proof']['tmp_name'];
            $id_ext = strtolower(pathinfo($id_name, PATHINFO_EXTENSION));
            $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png'];
            
            if (in_array($id_ext, $allowed_ext)) {
                $new_id_name = "id_" . $student_code . "." . $id_ext;
                $id_path = "uploads/students/id_proofs/" . $new_id_name;
                
                if (move_uploaded_file($id_tmp, $id_path)) {
                    $id_proof = $new_id_name;
                } else {
                    $upload_errors[] = "ID proof upload failed. Check permissions.";
                }
            } else {
                $upload_errors[] = "Invalid ID proof format. Allowed: PDF, JPG, JPEG, PNG";
            }
        }
        
        // If file uploads successful, insert into database
        if (empty($upload_errors) && !empty($photo) && !empty($id_proof)) {
            
            // Check if table exists
            $table_check = $conn->query("SHOW TABLES LIKE 'onlinestudents'");
            if ($table_check && $table_check->num_rows > 0) {
                
                // Prepare SQL query - FIXED: Count the parameters correctly
                // We have 30 parameters total
                $sql = "INSERT INTO onlinestudents (
                    student_code, username, password, name, father_name, mother_name, 
                    email, dob, gender, blood_group, mobile, address, city, pin_code, 
                    block, post_office, state, district, study_center, course_name, 
                    duration, price, reg_amount, payment_mode, payment_status, 
                    session_start, session_end, photo, id_proof, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                
                $stmt = $conn->prepare($sql);
                
                if ($stmt) {
                    // FIXED: The type definition string should have 29 's' for strings and 1 'd' for decimal
                    // Let's count: We have 30 parameters total
                    // Parameters: 1-3: strings, 4-20: strings, 21: string(duration), 22: decimal(price), 
                    // 23: decimal(reg_amount), 24-29: strings, 30: created_at is NOW() in SQL
                    
                    // Actually we need to bind 29 parameters (created_at is handled by SQL)
                    // Type string: s=string, d=decimal/double
                    $types = "sssssssssssssssssssssddssssss"; // 29 characters
                    
                    $stmt->bind_param(
                        $types,
                        $student_code, $username, $password_hash,
                        $name, $father_name, $mother_name, $email, $dob, $gender, 
                        $blood_group, $mobile, $address, $city, $pin_code, $block, 
                        $post_office, $state_name, $district_name, $study_center, $course_name,
                        $duration, $price, $reg_amount, $payment_mode, $payment_status,
                        $session_start, $session_end, $photo, $id_proof
                    );
                    
                    if ($stmt->execute()) {
                        $last_id = $conn->insert_id;
                        
                        $success_message = "
                            <div class='alert alert-success alert-dismissible fade show'>
                                <h4><i class='fas fa-check-circle'></i> Registration Successful!</h4>
                                <p><strong>Student ID:</strong> $last_id</p>
                                <p><strong>Student Code:</strong> $student_code</p>
                                <p><strong>Username:</strong> $username</p>
                                <p><strong>Password:</strong> $raw_password</p>
                                <p><strong>Photo:</strong> Uploaded successfully</p>
                                <p><strong>ID Proof:</strong> Uploaded successfully</p>
                                <div class='mt-3'>
                                    <button onclick='printStudentDetails(\"$student_code\")' class='btn btn-primary me-2'>
                                        <i class='fas fa-print'></i> Print Registration
                                    </button>
                                    <button onclick='copyLoginDetails(\"$username\", \"$raw_password\")' class='btn btn-secondary'>
                                        <i class='fas fa-copy'></i> Copy Credentials
                                    </button>
                                </div>
                                <div class='alert alert-warning mt-2'>
                                    <i class='fas fa-exclamation-triangle'></i> 
                                    Save these credentials securely!
                                </div>
                                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                            </div>
                        ";
                        
                        // Clear form after successful submission
                        echo "<script>setTimeout(function(){ document.getElementById('studentForm').reset(); }, 100);</script>";
                        
                    } else {
                        $errors[] = "Database Error: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $errors[] = "SQL Prepare Error: " . $conn->error;
                }
            } else {
                $errors[] = "Table 'onlinestudents' does not exist!";
            }
        } else {
            $errors = array_merge($errors, $upload_errors);
        }
    }
    
    // If there are errors, display them
    if (!empty($errors)) {
        $error_message = "<div class='alert alert-danger alert-dismissible fade show'>
                            <h4><i class='fas fa-exclamation-circle me-2'></i> Registration Failed!</h4>
                            <p>Please correct the following errors:</p>
                            <ul>";
        foreach ($errors as $error) {
            $error_message .= "<li>" . htmlspecialchars($error) . "</li>";
        }
        $error_message .= "</ul><button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    }
}

// Flush output buffer
ob_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - Sharnay Institute</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding: 20px 0;
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
        
        .btn-register {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 40px;
            font-weight: 600;
            font-size: 1.1rem;
            border-radius: 8px;
        }
        
        .btn-register:hover {
            background: linear-gradient(135deg, #5a6fd8, #6a4190);
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
        }
    </style>
</head>
<body>
    <div class="registration-card">
        <div class="card-header">
            <h1><i class="fas fa-user-graduate me-3"></i>Student Registration</h1>
            <p class="mb-0">Sharnay Institute - Online Registration System</p>
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
            
            <form action="" method="POST" enctype="multipart/form-data" id="studentForm">
                <!-- Personal Information -->
                <div class="form-section">
                    <h5 class="section-title"><i class="fas fa-user-circle me-2"></i> Personal Information</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label required">Full Name</label>
                            <input type="text" class="form-control" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Father's Name</label>
                            <input type="text" class="form-control" name="father_name" value="<?php echo isset($_POST['father_name']) ? htmlspecialchars($_POST['father_name']) : ''; ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Mother's Name</label>
                            <input type="text" class="form-control" name="mother_name" value="<?php echo isset($_POST['mother_name']) ? htmlspecialchars($_POST['mother_name']) : ''; ?>" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label required">Email</label>
                            <input type="email" class="form-control" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Mobile</label>
                            <input type="tel" class="form-control" name="mobile" value="<?php echo isset($_POST['mobile']) ? htmlspecialchars($_POST['mobile']) : ''; ?>" pattern="[0-9]{10}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Date of Birth</label>
                            <input type="date" class="form-control" name="dob" value="<?php echo isset($_POST['dob']) ? htmlspecialchars($_POST['dob']) : ''; ?>" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label required">Gender</label>
                            <select class="form-select" name="gender" required>
                                <option value="">Select</option>
                                <option value="Male" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Blood Group</label>
                            <select class="form-select" name="blood_group">
                                <option value="">Select</option>
                                <option value="A+" <?php echo (isset($_POST['blood_group']) && $_POST['blood_group'] == 'A+') ? 'selected' : ''; ?>>A+</option>
                                <option value="A-" <?php echo (isset($_POST['blood_group']) && $_POST['blood_group'] == 'A-') ? 'selected' : ''; ?>>A-</option>
                                <option value="B+" <?php echo (isset($_POST['blood_group']) && $_POST['blood_group'] == 'B+') ? 'selected' : ''; ?>>B+</option>
                                <option value="B-" <?php echo (isset($_POST['blood_group']) && $_POST['blood_group'] == 'B-') ? 'selected' : ''; ?>>B-</option>
                                <option value="AB+" <?php echo (isset($_POST['blood_group']) && $_POST['blood_group'] == 'AB+') ? 'selected' : ''; ?>>AB+</option>
                                <option value="AB-" <?php echo (isset($_POST['blood_group']) && $_POST['blood_group'] == 'AB-') ? 'selected' : ''; ?>>AB-</option>
                                <option value="O+" <?php echo (isset($_POST['blood_group']) && $_POST['blood_group'] == 'O+') ? 'selected' : ''; ?>>O+</option>
                                <option value="O-" <?php echo (isset($_POST['blood_group']) && $_POST['blood_group'] == 'O-') ? 'selected' : ''; ?>>O-</option>
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
                            <textarea class="form-control" name="address" rows="2" required><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label required">City</label>
                            <input type="text" class="form-control" name="city" value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Pin Code</label>
                            <input type="text" class="form-control" name="pin_code" value="<?php echo isset($_POST['pin_code']) ? htmlspecialchars($_POST['pin_code']) : ''; ?>" pattern="[0-9]{6}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Block</label>
                            <input type="text" class="form-control" name="block" value="<?php echo isset($_POST['block']) ? htmlspecialchars($_POST['block']) : ''; ?>">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Post Office</label>
                            <input type="text" class="form-control" name="post_office" value="<?php echo isset($_POST['post_office']) ? htmlspecialchars($_POST['post_office']) : ''; ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">State</label>
                            <select class="form-select select2-state" id="state" name="state" required>
                                <option value="">Select State</option>
                                <?php foreach ($states as $state): ?>
                                    <?php $selected = (isset($_POST['state']) && $_POST['state'] == $state['id']) ? 'selected' : ''; ?>
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
                                if (isset($_POST['district']) && $_POST['district'] > 0 && isset($_POST['state'])) {
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
                            <select class="form-select" name="study_center" required>
                                <option value="">Select Center</option>
                                <?php foreach ($centers as $center): ?>
                                    <?php $selected = (isset($_POST['study_center']) && $_POST['study_center'] == $center['center_name']) ? 'selected' : ''; ?>
                                    <option value="<?php echo htmlspecialchars($center['center_name']); ?>" <?php echo $selected; ?>>
                                        <?php echo htmlspecialchars($center['center_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label required">Course Name</label>
                            <select class="form-select" id="course_name" name="course_name" required>
                                <option value="">Select Course</option>
                                <?php foreach ($courses as $course): ?>
                                    <?php $selected = (isset($_POST['course_name']) && $_POST['course_name'] == $course['course_name']) ? 'selected' : ''; ?>
                                    <option value="<?php echo htmlspecialchars($course['course_name']); ?>"
                                            data-duration="<?php echo htmlspecialchars($course['duration']); ?>"
                                            data-price="<?php echo htmlspecialchars($course['price']); ?>"
                                            <?php echo $selected; ?>>
                                        <?php echo htmlspecialchars($course['course_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Duration</label>
                            <input type="text" class="form-control" id="duration" name="duration" value="<?php echo isset($_POST['duration']) ? htmlspecialchars($_POST['duration']) : ''; ?>" readonly>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Course Fee (₹)</label>
                            <input type="text" class="form-control" id="price" name="price" value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>" readonly>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label required">Registration Amount (₹)</label>
                            <input type="number" class="form-control" name="reg_amount" value="<?php echo isset($_POST['reg_amount']) ? htmlspecialchars($_POST['reg_amount']) : ''; ?>" min="0" step="0.01" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label required">Payment Mode</label>
                            <select class="form-select" name="payment_mode" required>
                                <option value="">Select</option>
                                <option value="Cash" <?php echo (isset($_POST['payment_mode']) && $_POST['payment_mode'] == 'Cash') ? 'selected' : ''; ?>>Cash</option>
                                <option value="Online" <?php echo (isset($_POST['payment_mode']) && $_POST['payment_mode'] == 'Online') ? 'selected' : ''; ?>>Online</option>
                                <option value="Cheque" <?php echo (isset($_POST['payment_mode']) && $_POST['payment_mode'] == 'Cheque') ? 'selected' : ''; ?>>Cheque</option>
                                <option value="Card" <?php echo (isset($_POST['payment_mode']) && $_POST['payment_mode'] == 'Card') ? 'selected' : ''; ?>>Card</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label required">Payment Status</label>
                            <select class="form-select" name="payment_status" required>
                                <option value="">Select</option>
                                <option value="Paid" <?php echo (isset($_POST['payment_status']) && $_POST['payment_status'] == 'Paid') ? 'selected' : ''; ?>>Paid</option>
                                <option value="Pending" <?php echo (isset($_POST['payment_status']) && $_POST['payment_status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="Partial" <?php echo (isset($_POST['payment_status']) && $_POST['payment_status'] == 'Partial') ? 'selected' : ''; ?>>Partial</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label required">Session Start</label>
                            <input type="date" class="form-control" name="session_start" value="<?php echo isset($_POST['session_start']) ? htmlspecialchars($_POST['session_start']) : date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label required">Session End</label>
                            <input type="date" class="form-control" name="session_end" value="<?php echo isset($_POST['session_end']) ? htmlspecialchars($_POST['session_end']) : date('Y-m-d', strtotime('+1 year')); ?>" required>
                        </div>
                    </div>
                </div>
                
                <!-- Document Upload -->
                <div class="form-section">
                    <h5 class="section-title"><i class="fas fa-file-upload me-2"></i> Document Upload</h5>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label required">Student Photo</label>
                            <div class="file-upload-container" onclick="document.getElementById('photo').click()">
                                <input type="file" id="photo" name="photo" accept="image/*" style="display: none;" onchange="previewPhoto(this)" required>
                                <i class="fas fa-camera fa-3x mb-3 text-muted"></i>
                                <h6>Upload Photo</h6>
                                <p class="text-muted mb-2">JPG, PNG, GIF (Max 2MB)</p>
                                <p id="photoFileName" class="small text-muted">No file chosen</p>
                                <div class="photo-preview mt-3" id="photoPreview"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label required">ID Proof</label>
                            <div class="file-upload-container" onclick="document.getElementById('id_proof').click()">
                                <input type="file" id="id_proof" name="id_proof" accept=".pdf,.jpg,.jpeg,.png" style="display: none;" onchange="previewIdProof(this)" required>
                                <i class="fas fa-id-card fa-3x mb-3 text-muted"></i>
                                <h6>Upload ID Proof</h6>
                                <p class="text-muted mb-2">PDF, JPG, PNG (Max 5MB)</p>
                                <p id="idProofFileName" class="small text-muted">No file chosen</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <div class="text-center mt-4">
                    <button type="submit" name="submit" class="btn btn-register btn-lg me-3">
                        <i class="fas fa-user-plus me-2"></i> Register Student
                    </button>
                    <button type="reset" class="btn btn-secondary btn-lg" onclick="resetForm()">
                        <i class="fas fa-redo me-2"></i> Reset Form
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        $(document).ready(function() {
            console.log("Page loaded successfully");
            
            // Initialize Select2
            $('.form-select').select2({
                theme: "classic",
                width: '100%'
            });
            
            // Set default dates
            const today = new Date().toISOString().split('T')[0];
            const nextYear = new Date();
            nextYear.setFullYear(nextYear.getFullYear() + 1);
            
            if (!$('input[name="session_start"]').val()) {
                $('input[name="session_start"]').val(today);
            }
            if (!$('input[name="session_end"]').val()) {
                $('input[name="session_end"]').val(nextYear.toISOString().split('T')[0]);
            }
            
            // Course change event
            $('#course_name').on('change', function() {
                const selected = $(this).find('option:selected');
                if (selected.val()) {
                    $('#duration').val(selected.data('duration') || '');
                    $('#price').val(selected.data('price') || '');
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
                        }
                    });
                }
            });
            
            // Trigger change if state is already selected
            if ($('#state').val()) {
                $('#state').trigger('change');
            }
        });
        
        function previewPhoto(input) {
            const file = input.files[0];
            if (file) {
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
                $('#idProofFileName').text(file.name).addClass('text-success').removeClass('text-muted');
            } else {
                $('#idProofFileName').text('No file chosen').removeClass('text-success').addClass('text-muted');
            }
        }
        
        function resetForm() {
            $('#photoFileName').text('No file chosen').removeClass('text-success').addClass('text-muted');
            $('#idProofFileName').text('No file chosen').removeClass('text-success').addClass('text-muted');
            $('#photoPreview').css('background-image', 'none');
            $('#duration').val('');
            $('#price').val('');
            $('.form-select').val(null).trigger('change');
        }
        
        function printStudentDetails(studentCode) {
            window.open('print_registration.php?student_code=' + studentCode, '_blank');
        }
        
        function copyLoginDetails(username, password) {
            const text = `Username: ${username}\nPassword: ${password}`;
            navigator.clipboard.writeText(text).then(() => {
                alert('Login credentials copied to clipboard!');
            });
        }
    </script>
</body>
</html>
<?php
// Close connection
if (isset($conn)) {
    $conn->close();
}
?>