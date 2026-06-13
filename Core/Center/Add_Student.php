<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
// Start output buffering
ob_start();

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
$phone = isset($_SESSION['phone']) ? $_SESSION['phone'] : '';

// Check connection
if (!$conn) {
    die("<div class='alert alert-danger'>
            <h4>Database Connection Failed!</h4>
            <p>Error: " . mysqli_connect_error() . "</p>
         </div>");
}

// Create upload directories
$upload_dirs = ['../uploads/students/photos/', '../uploads/students/id_proofs/'];
foreach ($upload_dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Fixed registration amount and payment mode
$fixed_reg_amount = 200.00;
$fixed_payment_mode = 'Wallet';

// Get wallet balance
$wallet_balance = 0;
$stmt = $conn->prepare("SELECT balance FROM center_wallet WHERE center_id = ?");
$stmt->bind_param("i", $center_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $wallet_balance = $row['balance'];
}
$stmt->close();

// Check if wallet has sufficient balance
$sufficient_balance = ($wallet_balance >= $fixed_reg_amount);

// Initialize variables
$success_message = '';
$error_message = '';
$centers = [];
$courses = [];
$states = [];

// Fetch centers - ONLY SHOW LOGGED-IN CENTER'S DETAILS
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
    
    // Check wallet balance first
    if (!$sufficient_balance) {
        $errors[] = "Insufficient wallet balance. Required: ₹{$fixed_reg_amount}, Available: ₹{$wallet_balance}";
    } else {
        // Validate required fields
        $errors = [];
        
        $required = ['name', 'father_name', 'mother_name', 'email', 'dob', 'gender', 'mobile', 
                     'address', 'city', 'pin_code', 'state', 'district', 'study_center', 
                     'course_name', 'session_start', 'session_end'];
        
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
        
        // Check file uploads
        if (empty($_FILES['photo']['name'])) {
            $errors[] = "Student photo is required";
        }
        if (empty($_FILES['id_proof']['name'])) {
            $errors[] = "ID proof is required";
        }
    }
    
    // If no validation errors, proceed
    if (empty($errors)) {
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
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
            $state_id = intval($_POST['state']);
            $district_id = intval($_POST['district']);
            $study_center = $conn->real_escape_string(trim($_POST['study_center']));
            $course_name = $conn->real_escape_string(trim($_POST['course_name']));
            $duration = isset($_POST['duration']) ? $conn->real_escape_string(trim($_POST['duration'])) : '';
            $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
            $session_start = $_POST['session_start'];
            $session_end = $_POST['session_end'];
            
            // Fixed values
            $reg_amount = $fixed_reg_amount;
            $payment_mode = $fixed_payment_mode;
            $payment_status = 'Paid'; // Always paid when using wallet
            
            // Get state and district names
            $state_name = '';
            $district_name = '';
            
            if ($state_id > 0) {
                $state_stmt = $conn->prepare("SELECT name FROM states WHERE id = ?");
                $state_stmt->bind_param("i", $state_id);
                $state_stmt->execute();
                $state_result = $state_stmt->get_result();
                if ($state_result && $state_result->num_rows > 0) {
                    $state_name = $state_result->fetch_assoc()['name'];
                }
                $state_stmt->close();
            }
            
            if ($district_id > 0) {
                $district_stmt = $conn->prepare("SELECT name FROM districts WHERE id = ?");
                $district_stmt->bind_param("i", $district_id);
                $district_stmt->execute();
                $district_result = $district_stmt->get_result();
                if ($district_result && $district_result->num_rows > 0) {
                    $district_name = $district_result->fetch_assoc()['name'];
                }
                $district_stmt->close();
            }
            
            // Generate student code and credentials
            $student_code = "STU" . date("YmdHis") . rand(100, 999);
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
                $photo_size = $_FILES['photo']['size'];
                $photo_ext = strtolower(pathinfo($photo_name, PATHINFO_EXTENSION));
                $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
                $max_size = 2 * 1024 * 1024; // 2MB
                
                if ($photo_size > $max_size) {
                    $upload_errors[] = "Photo size exceeds 2MB limit";
                } elseif (in_array($photo_ext, $allowed_ext)) {
                    $new_photo_name = "photo_" . $student_code . "." . $photo_ext;
                    $photo_path = "../uploads/students/photos/" . $new_photo_name;
                    
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
                $id_size = $_FILES['id_proof']['size'];
                $id_ext = strtolower(pathinfo($id_name, PATHINFO_EXTENSION));
                $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png'];
                $max_size = 5 * 1024 * 1024; // 5MB
                
                if ($id_size > $max_size) {
                    $upload_errors[] = "ID proof size exceeds 5MB limit";
                } elseif (in_array($id_ext, $allowed_ext)) {
                    $new_id_name = "id_" . $student_code . "." . $id_ext;
                    $id_path = "../uploads/students/id_proofs/" . $new_id_name;
                    
                    if (move_uploaded_file($id_tmp, $id_path)) {
                        $id_proof = $new_id_name;
                    } else {
                        $upload_errors[] = "ID proof upload failed. Check permissions.";
                    }
                } else {
                    $upload_errors[] = "Invalid ID proof format. Allowed: PDF, JPG, JPEG, PNG";
                }
            }
            
            // If file uploads have errors, throw exception
            if (!empty($upload_errors)) {
                throw new Exception(implode(', ', $upload_errors));
            }
            
            // Check if table exists
            $table_check = $conn->query("SHOW TABLES LIKE 'onlinestudents'");
            if (!$table_check || $table_check->num_rows == 0) {
                throw new Exception("Table 'onlinestudents' does not exist!");
            }
            
            // Step 1: Insert student record
            $sql = "INSERT INTO onlinestudents (
                student_code, username, password, name, father_name, mother_name, 
                email, dob, gender, blood_group, mobile, address, city, pin_code, 
                block, post_office, state, district, study_center, course_name, 
                duration, price, reg_amount, payment_mode, payment_status, 
                session_start, session_end, photo, id_proof, center_id, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("SQL Prepare Error: " . $conn->error);
            }
            
            $types = "sssssssssssssssssssssddssssssi";
            
            $stmt->bind_param(
                $types,
                $student_code, $username, $password_hash,
                $name, $father_name, $mother_name, $email, $dob, $gender, 
                $blood_group, $mobile, $address, $city, $pin_code, $block, 
                $post_office, $state_name, $district_name, $study_center, $course_name,
                $duration, $price, $reg_amount, $payment_mode, $payment_status,
                $session_start, $session_end, $photo, $id_proof, $center_id
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Student insertion failed: " . $stmt->error);
            }
            
            $student_id = $conn->insert_id;
            $stmt->close();
            
            // Step 2: Create wallet transaction record
            $transaction_id = 'STU-' . date('YmdHis') . rand(1000, 9999);
            
            $transaction_sql = "INSERT INTO wallet_transactions (
                center_id, transaction_type, amount, payment_method, 
                transaction_id, status, description
            ) VALUES (?, 'debit', ?, ?, ?, 'success', ?)";
            
            $transaction_desc = "Student registration: " . $name . " (Student Code: " . $student_code . ")";
            
            $stmt = $conn->prepare($transaction_sql);
            if (!$stmt) {
                throw new Exception("Transaction SQL Prepare Error: " . $conn->error);
            }
            
            $stmt->bind_param("idsss", $center_id, $reg_amount, $payment_mode, $transaction_id, $transaction_desc);
            
            if (!$stmt->execute()) {
                throw new Exception("Transaction insertion failed: " . $stmt->error);
            }
            
            $transaction_id = $stmt->insert_id;
            $stmt->close();
            
            // Step 3: Deduct amount from wallet
            $wallet_sql = "UPDATE center_wallet SET balance = balance - ? WHERE center_id = ?";
            $stmt = $conn->prepare($wallet_sql);
            if (!$stmt) {
                throw new Exception("Wallet SQL Prepare Error: " . $conn->error);
            }
            
            $stmt->bind_param("di", $reg_amount, $center_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Wallet update failed: " . $stmt->error);
            }
            
            // Check if wallet balance went negative
            $check_sql = "SELECT balance FROM center_wallet WHERE center_id = ?";
            $stmt = $conn->prepare($check_sql);
            $stmt->bind_param("i", $center_id);
            $stmt->execute();
            $check_result = $stmt->get_result();
            $new_balance = $check_result->fetch_assoc()['balance'];
            $stmt->close();
            
            if ($new_balance < 0) {
                throw new Exception("Wallet balance would go negative. Transaction rolled back.");
            }
            
            // Commit transaction
            $conn->commit();
            
            // Update wallet balance variable
            $wallet_balance = $new_balance;
            $sufficient_balance = ($wallet_balance >= $fixed_reg_amount);
            
            // Success message
            $success_message = "
                <div class='alert alert-success alert-dismissible fade show'>
                    <h4><i class='fas fa-check-circle'></i> Registration Successful!</h4>
                    <p><strong>Student ID:</strong> $student_id</p>
                    <p><strong>Student Code:</strong> $student_code</p>
                    <p><strong>Username:</strong> $username</p>
                    <p><strong>Password:</strong> $raw_password</p>
                    <div class='alert alert-info mt-3'>
                        <i class='fas fa-wallet'></i> 
                        <strong>Wallet Transaction:</strong> ₹{$reg_amount} deducted from your wallet.
                        <br>New Wallet Balance: ₹" . number_format($new_balance, 2) . "
                    </div>
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
            echo "<script>setTimeout(function(){ document.getElementById('studentForm').reset(); resetForm(); }, 100);</script>";
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $errors[] = "Registration failed: " . $e->getMessage();
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
include 'header.php';
?>
    <style>
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
        
        .wallet-info-section {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        
        .wallet-balance-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 10px;
        }
        
        .balance-success {
            border-left: 4px solid #28a745;
        }
        
        .balance-danger {
            border-left: 4px solid #dc3545;
        }
        
        .balance-amount {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 10px 0;
        }
    </style>

    <div class="registration-card">
        <div class="card-header">
            <h1><i class="fas fa-user-graduate me-3"></i>Student Registration</h1>
            <p class="mb-0">Sharnay Institute - Online Registration System</p>
            <p class="mb-0 mt-2"><i class="fas fa-building me-2"></i>Center: <?php echo htmlspecialchars($center_name); ?></p>
        </div>
        
        <div class="card-body p-4">
            <!-- Wallet Information Section -->
            <div class="wallet-info-section">
                <h4 class="mb-3"><i class="fas fa-wallet me-2"></i>Wallet Information</h4>
                <div class="row">
                    <div class="col-md-4">
                        <div class="wallet-balance-card <?php echo $sufficient_balance ? 'balance-success' : 'balance-danger'; ?>">
                            <h6>Current Balance</h6>
                            <div class="balance-amount <?php echo $sufficient_balance ? 'text-success' : 'text-danger'; ?>">
                                ₹<?php echo number_format($wallet_balance, 2); ?>
                            </div>
                            <p class="mb-0">
                                <?php if ($sufficient_balance): ?>
                                    <i class="fas fa-check-circle text-success"></i> Sufficient balance
                                <?php else: ?>
                                    <i class="fas fa-exclamation-circle text-danger"></i> Insufficient balance
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="wallet-balance-card">
                            <h6>Registration Fee</h6>
                            <div class="balance-amount text-primary">
                                ₹<?php echo number_format($fixed_reg_amount, 2); ?>
                            </div>
                            <p class="mb-0"><i class="fas fa-info-circle text-primary"></i> Fixed amount</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="wallet-balance-card">
                            <h6>Payment Mode</h6>
                            <div class="balance-amount text-info">
                                <?php echo $fixed_payment_mode; ?>
                            </div>
                            <p class="mb-0"><i class="fas fa-credit-card text-info"></i> Deducted from wallet</p>
                        </div>
                    </div>
                </div>
                
                <?php if (!$sufficient_balance): ?>
                    <div class="alert alert-danger mt-3">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Insufficient Wallet Balance!</strong> 
                        You need ₹<?php echo number_format($fixed_reg_amount - $wallet_balance, 2); ?> more to register a student.
                        <div class="mt-2">
                            <a href="wallet.php" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus-circle"></i> Add Money to Wallet
                            </a>
                            <a href="wallet.php" class="btn btn-sm btn-outline-primary ms-2">
                                <i class="fas fa-history"></i> View Wallet History
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success mt-3">
                        <i class="fas fa-check-circle"></i> 
                        <strong>Sufficient Balance!</strong> 
                        ₹<?php echo number_format($fixed_reg_amount, 2); ?> will be deducted from your wallet upon registration.
                        <br>Balance after registration: ₹<?php echo number_format($wallet_balance - $fixed_reg_amount, 2); ?>
                    </div>
                <?php endif; ?>
            </div>
            
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
                            <small class="text-muted">10 digits only</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Date of Birth</label>
                            <input type="date" class="form-control" name="dob" value="<?php echo isset($_POST['dob']) ? htmlspecialchars($_POST['dob']) : ''; ?>" max="<?php echo date('Y-m-d'); ?>" required>
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
                            <small class="text-muted">6 digits only</small>
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
                            <select class="form-select" name="study_center" required <?php echo (count($centers) == 1) ? 'disabled' : ''; ?>>
                                <option value="">Select Center</option>
                                <?php foreach ($centers as $center): ?>
                                    <?php 
                                    $selected = '';
                                    if (isset($_POST['study_center']) && $_POST['study_center'] == $center['center_name']) {
                                        $selected = 'selected';
                                    } elseif ($center['center_name'] == $center_name) {
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
                                <small class="text-muted">You can only register students for your own center</small>
                            <?php endif; ?>
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
                                        <?php if ($course['price'] > 0): ?>
                                            (₹<?php echo number_format($course['price'], 2); ?>)
                                        <?php endif; ?>
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
                            <label class="form-label">Registration Amount (₹)</label>
                            <input type="text" class="form-control" value="₹<?php echo number_format($fixed_reg_amount, 2); ?>" readonly>
                            <small class="text-muted">Fixed registration fee (Paid from wallet)</small>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Payment Mode</label>
                            <input type="text" class="form-control" value="<?php echo $fixed_payment_mode; ?>" readonly>
                            <small class="text-muted">Amount deducted from wallet</small>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Payment Status</label>
                            <input type="text" class="form-control" value="Paid" readonly>
                            <small class="text-muted">Auto-paid from wallet</small>
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
                
                <!-- Hidden fields for fixed values -->
                <input type="hidden" name="reg_amount" value="<?php echo $fixed_reg_amount; ?>">
                <input type="hidden" name="payment_mode" value="<?php echo $fixed_payment_mode; ?>">
                <input type="hidden" name="payment_status" value="Paid">
                
                <!-- Submit Button -->
                <div class="text-center mt-4">
                    <button type="submit" name="submit" class="btn btn-register btn-lg me-3" 
                            id="submitBtn" <?php echo !$sufficient_balance ? 'disabled' : ''; ?>>
                        <i class="fas fa-user-plus me-2"></i> Register Student
                    </button>
                    <button type="button" class="btn btn-secondary btn-lg" onclick="resetForm()">
                        <i class="fas fa-redo me-2"></i> Reset Form
                    </button>
                    <a href="wallet.php" class="btn btn-warning btn-lg ms-2">
                        <i class="fas fa-wallet me-2"></i> Add Money to Wallet
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        $(document).ready(function() {
            console.log("Page loaded successfully");
            
            // Disable/enable submit button based on wallet balance
            const sufficientBalance = <?php echo $sufficient_balance ? 'true' : 'false'; ?>;
            const submitBtn = $('#submitBtn');
            
            if (!sufficientBalance) {
                submitBtn.prop('disabled', true);
                submitBtn.attr('title', 'Insufficient wallet balance. Please add money to your wallet first.');
            }
            
            // Initialize Select2
            $('.form-select').select2({
                theme: "classic",
                width: '100%',
                placeholder: function() {
                    return $(this).data('placeholder') || 'Select an option';
                }
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
            
            // Set maximum date for DOB to today
            $('input[name="dob"]').attr('max', today);
            
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
            
            // Form validation before submit
            $('#studentForm').on('submit', function(e) {
                if (!sufficientBalance) {
                    e.preventDefault();
                    alert('Insufficient wallet balance. Please add money to your wallet first.');
                    return false;
                }
                
                // Check file uploads
                const photoFile = $('#photo')[0].files[0];
                const idProofFile = $('#id_proof')[0].files[0];
                
                if (!photoFile) {
                    e.preventDefault();
                    alert('Please upload student photo.');
                    return false;
                }
                
                if (!idProofFile) {
                    e.preventDefault();
                    alert('Please upload ID proof.');
                    return false;
                }
                
                return true;
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
        
        function resetForm() {
            // Reset form fields
            document.getElementById('studentForm').reset();
            
            // Reset file previews
            $('#photoFileName').text('No file chosen').removeClass('text-success').addClass('text-muted');
            $('#idProofFileName').text('No file chosen').removeClass('text-success').addClass('text-muted');
            $('#photoPreview').css('background-image', 'none');
            
            // Reset select fields
            $('#duration').val('');
            $('#price').val('');
            $('.form-select').val(null).trigger('change');
            
            // Set default dates
            const today = new Date().toISOString().split('T')[0];
            const nextYear = new Date();
            nextYear.setFullYear(nextYear.getFullYear() + 1);
            
            $('input[name="session_start"]').val(today);
            $('input[name="session_end"]').val(nextYear.toISOString().split('T')[0]);
            
            // Reset study center to logged-in center
            $('select[name="study_center"]').val('<?php echo htmlspecialchars($center_name); ?>').trigger('change');
            
            // Re-enable form if it was disabled
            $('#studentForm :input').prop('disabled', false);
        }
        
        function printStudentDetails(studentCode) {
            window.open('print_registration.php?student_code=' + encodeURIComponent(studentCode), '_blank');
        }
        
        function copyLoginDetails(username, password) {
            const text = `Username: ${username}\nPassword: ${password}`;
            
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(() => {
                    alert('Login credentials copied to clipboard!');
                }).catch(err => {
                    fallbackCopyText(text);
                });
            } else {
                fallbackCopyText(text);
            }
        }
        
        function fallbackCopyText(text) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            textArea.setSelectionRange(0, 99999); // For mobile devices
            
            try {
                document.execCommand('copy');
                alert('Login credentials copied to clipboard!');
            } catch (err) {
                alert('Failed to copy credentials. Please copy manually:\n\n' + text);
            }
            
            document.body.removeChild(textArea);
        }
    </script>

<?php
include 'footer.php';
// Close connection
if (isset($conn)) {
    $conn->close();
}
?>