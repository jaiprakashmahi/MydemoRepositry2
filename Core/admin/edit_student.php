<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

include 'conn.php';

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$success_message = '';
$error_message = '';
$centers = [];
$courses = [];
$states = [];
$student = null;

// Check if student ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid student ID. <a href='all_students.php'>Go back</a>");
}

$student_id = intval($_GET['id']);

// Create upload directories - EXACT SAME as add-student.php
$upload_dirs = ['uploads/students/photos/', 'uploads/students/id_proofs/'];
foreach ($upload_dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Fetch existing student data
$stmt_fetch = $conn->prepare("SELECT * FROM onlinestudents WHERE id = ? LIMIT 1");
$stmt_fetch->bind_param("i", $student_id);
$stmt_fetch->execute();
$result = $stmt_fetch->get_result();

if ($result->num_rows === 0) {
    die("Student not found. <a href='all_students.php'>Go back</a>");
}

$student = $result->fetch_assoc();
$stmt_fetch->close();

// Fetch centers - EXACT SAME as add-student.php
$center_result = $conn->query("SELECT id, center_name FROM center_details ORDER BY center_name ASC");
while ($center_result && $row = $center_result->fetch_assoc()) {
    $centers[] = $row;
}

// Fetch courses - EXACT SAME as add-student.php
$course_result = $conn->query("SELECT DISTINCT course_name, duration, price FROM courses ORDER BY course_name ASC");
while ($course_result && $row = $course_result->fetch_assoc()) {
    $courses[] = $row;
}

// Fetch states - EXACT SAME as add-student.php
$state_result = $conn->query("SELECT id, name FROM states ORDER BY name ASC");
while ($state_result && $row = $state_result->fetch_assoc()) {
    $states[] = $row;
}

// Get state ID from state name
$current_state_id = '';
$current_district_id = '';

if (!empty($student['state'])) {
    $stmtState = $conn->prepare("SELECT id FROM states WHERE name = ? LIMIT 1");
    $stmtState->bind_param("s", $student['state']);
    $stmtState->execute();
    $stateRow = $stmtState->get_result()->fetch_assoc();
    $current_state_id = $stateRow['id'] ?? '';
}

// Get district ID from district name
if (!empty($student['district'])) {
    $stmtDistrict = $conn->prepare("SELECT id FROM districts WHERE name = ? LIMIT 1");
    $stmtDistrict->bind_param("s", $student['district']);
    $stmtDistrict->execute();
    $districtRow = $stmtDistrict->get_result()->fetch_assoc();
    $current_district_id = $districtRow['id'] ?? '';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $errors = [];

    $required = [
        'name', 'father_name', 'mother_name', 'email', 'dob', 'gender', 'mobile',
        'address', 'city', 'pin_code', 'state', 'district', 'study_center',
        'course_name', 'reg_amount', 'payment_mode', 'payment_status',
        'session_start', 'session_end'
    ];

    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required";
        }
    }

    if (empty($errors)) {
        // EXACT SAME data handling as add-student.php
        $name = trim($_POST['name']);
        $father_name = trim($_POST['father_name']);
        $mother_name = trim($_POST['mother_name']);
        $email = trim($_POST['email']);
        $dob = $_POST['dob'];
        $gender = trim($_POST['gender']);
        $blood_group = trim($_POST['blood_group'] ?? '');
        $mobile = trim($_POST['mobile']);
        $address = trim($_POST['address']);
        $city = trim($_POST['city']);
        $pin_code = trim($_POST['pin_code']);
        $block = trim($_POST['block'] ?? '');
        $post_office = trim($_POST['post_office'] ?? '');
        $state_id = intval($_POST['state']);
        $district_id = intval($_POST['district']);
        $study_center = trim($_POST['study_center']);
        $course_name = trim($_POST['course_name']);
        $duration = trim($_POST['duration'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $reg_amount = floatval($_POST['reg_amount']);
        $payment_mode = trim($_POST['payment_mode']);
        $payment_status = trim($_POST['payment_status']);
        $session_start = $_POST['session_start'];
        $session_end = $_POST['session_end'];

        $state_name = '';
        $district_name = '';

        $stmtState = $conn->prepare("SELECT name FROM states WHERE id = ? LIMIT 1");
        $stmtState->bind_param("i", $state_id);
        $stmtState->execute();
        $stateRow = $stmtState->get_result()->fetch_assoc();
        $state_name = $stateRow['name'] ?? '';

        $stmtDistrict = $conn->prepare("SELECT name FROM districts WHERE id = ? LIMIT 1");
        $stmtDistrict->bind_param("i", $district_id);
        $stmtDistrict->execute();
        $districtRow = $stmtDistrict->get_result()->fetch_assoc();
        $district_name = $districtRow['name'] ?? '';

        // Keep existing files
        $photo = $student['photo'];
        $id_proof = $student['id_proof'];
        $upload_errors = [];

        // Photo upload - EXACT SAME as add-student.php
        if (!empty($_FILES['photo']['name'])) {
            $photo_ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowed_photo = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($photo_ext, $allowed_photo)) {
                // Delete old photo
                if (!empty($student['photo']) && file_exists("uploads/students/photos/" . $student['photo'])) {
                    unlink("uploads/students/photos/" . $student['photo']);
                }

                $photo = "photo_" . $student['student_code'] . "_" . time() . "." . $photo_ext;
                $photo_path = "uploads/students/photos/" . $photo;

                if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path)) {
                    // Try copy method
                    if (!copy($_FILES['photo']['tmp_name'], $photo_path)) {
                        $upload_errors[] = "Photo upload failed. Please contact administrator.";
                    }
                }
            } else {
                $upload_errors[] = "Invalid photo format. Allowed: jpg, jpeg, png, gif";
            }
        }

        // ID proof upload - EXACT SAME as add-student.php
        if (!empty($_FILES['id_proof']['name'])) {
            $id_ext = strtolower(pathinfo($_FILES['id_proof']['name'], PATHINFO_EXTENSION));
            $allowed_id = ['pdf', 'jpg', 'jpeg', 'png'];

            if (in_array($id_ext, $allowed_id)) {
                // Delete old ID proof
                if (!empty($student['id_proof']) && file_exists("uploads/students/id_proofs/" . $student['id_proof'])) {
                    unlink("uploads/students/id_proofs/" . $student['id_proof']);
                }

                $id_proof = "id_" . $student['student_code'] . "_" . time() . "." . $id_ext;
                $id_path = "uploads/students/id_proofs/" . $id_proof;

                if (!move_uploaded_file($_FILES['id_proof']['tmp_name'], $id_path)) {
                    // Try copy method
                    if (!copy($_FILES['id_proof']['tmp_name'], $id_path)) {
                        $upload_errors[] = "ID proof upload failed. Please contact administrator.";
                    }
                }
            } else {
                $upload_errors[] = "Invalid ID proof format. Allowed: pdf, jpg, jpeg, png";
            }
        }

        if (empty($upload_errors)) {
            // EXACT SAME SQL structure as add-student.php
            $sql = "UPDATE onlinestudents SET 
                name='$name', father_name='$father_name', mother_name='$mother_name', 
                email='$email', dob='$dob', gender='$gender', blood_group='$blood_group', 
                mobile='$mobile', address='$address', city='$city', pin_code='$pin_code', 
                block='$block', post_office='$post_office', state='$state_name', 
                district='$district_name', study_center='$study_center', 
                course_name='$course_name', duration='$duration', price=$price, 
                reg_amount=$reg_amount, payment_mode='$payment_mode', 
                payment_status='$payment_status', session_start='$session_start', 
                session_end='$session_end', photo='$photo', id_proof='$id_proof'
                WHERE id=$student_id";

            if ($conn->query($sql)) {
                $success_message = "
                <div class='alert alert-success alert-dismissible fade show'>
                    <h5><i class='fa fa-check-circle me-2'></i>Update Successful!</h5>
                    <p>Student <strong>{$student['student_code']}</strong> has been updated successfully.</p>
                    <a href='all_students.php' class='btn btn-sm btn-success me-2'>
                        <i class='fa fa-list me-1'></i>View All Students
                    </a>
                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                </div>";

                // Refresh student data
                $refresh_result = $conn->query("SELECT * FROM onlinestudents WHERE id = $student_id");
                if ($refresh_result && $refresh_result->num_rows > 0) {
                    $student = $refresh_result->fetch_assoc();
                    
                    // Update current state and district IDs
                    if (!empty($student['state'])) {
                        $stmtState = $conn->prepare("SELECT id FROM states WHERE name = ? LIMIT 1");
                        $stmtState->bind_param("s", $student['state']);
                        $stmtState->execute();
                        $stateRow = $stmtState->get_result()->fetch_assoc();
                        $current_state_id = $stateRow['id'] ?? '';
                    }

                    if (!empty($student['district'])) {
                        $stmtDistrict = $conn->prepare("SELECT id FROM districts WHERE name = ? LIMIT 1");
                        $stmtDistrict->bind_param("s", $student['district']);
                        $stmtDistrict->execute();
                        $districtRow = $stmtDistrict->get_result()->fetch_assoc();
                        $current_district_id = $districtRow['id'] ?? '';
                    }
                }
            } else {
                $errors[] = "Database Error: " . $conn->error;
            }
        } else {
            $errors = array_merge($errors, $upload_errors);
        }
    }

    if (!empty($errors)) {
        $error_message = "<div class='alert alert-danger alert-dismissible fade show'><h5>Update Failed!</h5><ul>";
        foreach ($errors as $error) {
            $error_message .= "<li>" . htmlspecialchars($error) . "</li>";
        }
        $error_message .= "</ul><button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Edit Student - Sharnay Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="shortcut icon" type="image/png" href="images/favicon.png">
    <link href="vendor/wow-master/css/libs/animate.css" rel="stylesheet">
    <link href="vendor/bootstrap-datepicker-master/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <link href="vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/admin-standard.css" rel="stylesheet">

    <style>
        .student-page-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 28px rgba(16, 24, 40, .07);
            border: 1px solid #edf0f5;
            overflow: hidden;
        }

        .student-page-header {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            padding: 24px 28px;
            color: #fff;
        }

        .student-page-header h4 {
            color: #fff;
            margin: 0;
            font-weight: 700;
        }

        .student-page-header p {
            margin: 6px 0 0;
            opacity: .9;
        }

        .form-section {
            background: #f8f9fc;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 22px;
            margin-bottom: 22px;
        }

        .section-title {
            color: #344054;
            font-weight: 700;
            font-size: 17px;
            margin-bottom: 18px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        .required::after {
            content: " *";
            color: #dc3545;
        }

        .form-label {
            font-weight: 600;
            color: #344054;
            font-size: 13px;
            margin-bottom: 7px;
        }

        .form-control,
        .form-select {
            height: 44px;
            border-radius: 10px;
            border: 1px solid #d0d5dd;
            font-size: 14px;
        }

        textarea.form-control {
            height: auto;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #f39c12;
            box-shadow: 0 0 0 3px rgba(243, 156, 18, .12);
        }

        .file-upload-container {
            border: 2px dashed #cbd5e1;
            border-radius: 14px;
            padding: 24px;
            text-align: center;
            background: #fff;
            cursor: pointer;
            transition: .25s;
            min-height: 205px;
        }

        .file-upload-container:hover {
            border-color: #f39c12;
            background: #fff9f0;
        }

        .photo-preview {
            width: 105px;
            height: 105px;
            border-radius: 12px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            margin: 12px auto 0;
            background-size: cover;
            background-position: center;
        }

        .btn-update {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: #fff;
            border: none;
            padding: 11px 34px;
            font-weight: 700;
            border-radius: 10px;
        }

        .btn-update:hover {
            color: #fff;
            opacity: .95;
        }

        .current-file {
            background: #e8f5e9;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-top: 10px;
        }

        .current-file a {
            color: #2e7d32;
            text-decoration: none;
            font-weight: 600;
        }

        .student-info-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 12px;
            border-radius: 6px;
            display: inline-block;
            margin-left: 10px;
            font-size: 14px;
        }
    </style>
</head>

<body>
<div id="main-wrapper">

    <?php include 'menu.php'; ?>

    <div class="content-body">
        <div class="container-fluid">

            <div class="student-page-card">
                <div class="student-page-header">
                    <h4>
                        <i class="fa fa-user-edit me-2"></i>Edit Student
                        <span class="student-info-badge">Code: <?= htmlspecialchars($student['student_code']) ?></span>
                    </h4>
                    <p>Update student information for <?= htmlspecialchars($student['name']) ?></p>
                </div>

                <div class="card-body p-4">
                    <?= $success_message ?>
                    <?= $error_message ?>

                    <form action="edit_student.php?id=<?= $student_id ?>" method="POST" enctype="multipart/form-data" id="studentForm">

                        <!-- Personal Information -->
                        <div class="form-section">
                            <h5 class="section-title"><i class="fa fa-user-circle me-2"></i>Personal Information</h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label required">Full Name</label>
                                    <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($student['name']) ?>" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">Father's Name</label>
                                    <input type="text" class="form-control" name="father_name" value="<?= htmlspecialchars($student['father_name']) ?>" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">Mother's Name</label>
                                    <input type="text" class="form-control" name="mother_name" value="<?= htmlspecialchars($student['mother_name']) ?>" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">Email</label>
                                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($student['email']) ?>" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">Mobile</label>
                                    <input type="tel" class="form-control" name="mobile" pattern="[0-9]{10}" value="<?= htmlspecialchars($student['mobile']) ?>" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">Date of Birth</label>
                                    <input type="date" class="form-control" name="dob" value="<?= htmlspecialchars($student['dob']) ?>" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">Gender</label>
                                    <select class="form-select" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male" <?= $student['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
                                        <option value="Female" <?= $student['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
                                        <option value="Other" <?= $student['gender'] == 'Other' ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Blood Group</label>
                                    <select class="form-select" name="blood_group">
                                        <option value="">Select Blood Group</option>
                                        <?php
                                        $blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                                        foreach ($blood_groups as $bg) {
                                            $selected = ($student['blood_group'] == $bg) ? 'selected' : '';
                                            echo "<option value=\"$bg\" $selected>$bg</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Address Information -->
                        <div class="form-section">
                            <h5 class="section-title"><i class="fa fa-map-marker-alt me-2"></i>Address Information</h5>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label required">Complete Address</label>
                                    <textarea class="form-control" name="address" rows="2" required><?= htmlspecialchars($student['address']) ?></textarea>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">City</label>
                                    <input type="text" class="form-control" name="city" value="<?= htmlspecialchars($student['city']) ?>" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">Pin Code</label>
                                    <input type="text" class="form-control" name="pin_code" pattern="[0-9]{6}" value="<?= htmlspecialchars($student['pin_code']) ?>" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Block</label>
                                    <input type="text" class="form-control" name="block" value="<?= htmlspecialchars($student['block'] ?? '') ?>">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Post Office</label>
                                    <input type="text" class="form-control" name="post_office" value="<?= htmlspecialchars($student['post_office'] ?? '') ?>">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">State</label>
                                    <select class="form-select" id="state" name="state" required>
                                        <option value="">Select State</option>
                                        <?php foreach ($states as $state): ?>
                                            <option value="<?= $state['id'] ?>" <?= ($state['id'] == $current_state_id) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($state['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">District</label>
                                    <select class="form-select" id="district" name="district" required <?= empty($current_state_id) ? 'disabled' : '' ?>>
                                        <?php if (!empty($current_state_id)): ?>
                                            <option value="">Loading districts...</option>
                                        <?php else: ?>
                                            <option value="">Select State First</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Institute Details -->
                        <div class="form-section">
                            <h5 class="section-title"><i class="fa fa-graduation-cap me-2"></i>Institute Details</h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label required">Study Center</label>
                                    <select class="form-select" name="study_center" required>
                                        <option value="">Select Center</option>
                                        <?php foreach ($centers as $center): ?>
                                            <option value="<?= htmlspecialchars($center['center_name']) ?>" 
                                                <?= ($student['study_center'] == $center['center_name']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($center['center_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">Course Name</label>
                                    <select class="form-select" id="course_name" name="course_name" required>
                                        <option value="">Select Course</option>
                                        <?php foreach ($courses as $course): ?>
                                            <option value="<?= htmlspecialchars($course['course_name']) ?>"
                                                    data-duration="<?= htmlspecialchars($course['duration']) ?>"
                                                    data-price="<?= htmlspecialchars($course['price']) ?>"
                                                    <?= ($student['course_name'] == $course['course_name']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($course['course_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Duration</label>
                                    <input type="text" class="form-control" id="duration" name="duration" value="<?= htmlspecialchars($student['duration']) ?>" readonly>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Course Fee (₹)</label>
                                    <input type="text" class="form-control" id="price" name="price" value="<?= htmlspecialchars($student['price']) ?>" readonly>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">Registration Amount (₹)</label>
                                    <input type="number" class="form-control" name="reg_amount" min="0" step="0.01" value="<?= htmlspecialchars($student['reg_amount']) ?>" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">Payment Mode</label>
                                    <select class="form-select" name="payment_mode" required>
                                        <option value="">Select Payment Mode</option>
                                        <?php
                                        $payment_modes = ['Cash', 'Online', 'Cheque', 'Card'];
                                        foreach ($payment_modes as $mode) {
                                            $selected = ($student['payment_mode'] == $mode) ? 'selected' : '';
                                            echo "<option value=\"$mode\" $selected>$mode</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">Payment Status</label>
                                    <select class="form-select" name="payment_status" required>
                                        <option value="">Select Payment Status</option>
                                        <?php
                                        $payment_statuses = ['Paid', 'Pending', 'Partial'];
                                        foreach ($payment_statuses as $status) {
                                            $selected = ($student['payment_status'] == $status) ? 'selected' : '';
                                            echo "<option value=\"$status\" $selected>$status</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">Session Start</label>
                                    <input type="date" class="form-control" name="session_start" value="<?= htmlspecialchars($student['session_start']) ?>" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">Session End</label>
                                    <input type="date" class="form-control" name="session_end" value="<?= htmlspecialchars($student['session_end']) ?>" required>
                                </div>
                            </div>
                        </div>

                        <!-- Document Upload -->
                        <div class="form-section">
                            <h5 class="section-title"><i class="fa fa-file-upload me-2"></i>Document Upload</h5>
                            <p class="text-muted small mb-3">Leave empty to keep current file. Upload new file to replace.</p>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">Student Photo</label>
                                    <div class="file-upload-container" onclick="document.getElementById('photo').click()">
                                        <input type="file" id="photo" name="photo" accept="image/*" style="display:none;" onchange="previewPhoto(this)">
                                        <i class="fa fa-camera fa-3x mb-3 text-muted"></i>
                                        <h6>Click to Upload New Photo</h6>
                                        <p id="photoFileName" class="small text-muted">No new file chosen</p>
                                        <div class="photo-preview" id="photoPreview" style="background-image: url('uploads/students/photos/<?= htmlspecialchars($student['photo']) ?>');"></div>
                                        <?php if (!empty($student['photo'])): ?>
                                            <div class="current-file">
                                                <i class="fa fa-image me-1"></i>
                                                Current: <?= htmlspecialchars($student['photo']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">ID Proof</label>
                                    <div class="file-upload-container" onclick="document.getElementById('id_proof').click()">
                                        <input type="file" id="id_proof" name="id_proof" accept=".pdf,.jpg,.jpeg,.png" style="display:none;" onchange="previewIdProof(this)">
                                        <i class="fa fa-id-card fa-3x mb-3 text-muted"></i>
                                        <h6>Click to Upload New ID Proof</h6>
                                        <p id="idProofFileName" class="small text-muted">No new file chosen</p>
                                        <?php if (!empty($student['id_proof'])): ?>
                                            <div class="current-file">
                                                <i class="fa fa-file me-1"></i>
                                                Current: <?= htmlspecialchars($student['id_proof']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" name="update" class="btn btn-update me-2">
                                <i class="fa fa-save me-2"></i>Update Student
                            </button>

                            <a href="student.php" class="btn btn-secondary me-2">
                                <i class="fa fa-arrow-left me-2"></i>Back to Students
                            </a>

                            <button type="reset" class="btn btn-outline-secondary" onclick="resetForm()">
                                <i class="fa fa-redo me-2"></i>Reset Changes
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>

    <?php include 'footer.php'; ?>

</div>

<script src="vendor/global/global.min.js"></script>
<script src="vendor/bootstrap-datepicker-master/js/bootstrap-datepicker.min.js"></script>
<script src="vendor/datatables/js/jquery.dataTables.min.js"></script>
<script src="js/custom.min.js"></script>
<script src="js/dlabnav-init.js"></script>
<script src="js/admin-standard.js"></script>

<script>
$(document).ready(function () {
    $('#course_name').on('change', function () {
        const selected = $(this).find('option:selected');
        $('#duration').val(selected.data('duration') || '');
        $('#price').val(selected.data('price') || '');
    });

    $('#state').on('change', function () {
        const stateId = $(this).val();
        $('#district').html('<option value="">Loading districts...</option>').prop('disabled', true);

        if (stateId !== '') {
            $.ajax({
                url: 'fetch_district.php',
                type: 'POST',
                data: { state_id: stateId },
                success: function (response) {
                    $('#district').html(response).prop('disabled', false);
                },
                error: function () {
                    $('#district').html('<option value="">Error loading districts</option>').prop('disabled', false);
                }
            });
        } else {
            $('#district').html('<option value="">Select State First</option>').prop('disabled', true);
        }
    });

    // Load districts on page load
    <?php if (!empty($current_state_id)): ?>
        $.ajax({
            url: 'fetch_district.php',
            type: 'POST',
            data: { state_id: '<?= $current_state_id ?>' },
            success: function (response) {
                $('#district').html(response).prop('disabled', false);
                <?php if (!empty($current_district_id)): ?>
                    $('#district').val('<?= $current_district_id ?>');
                <?php endif; ?>
            }
        });
    <?php endif; ?>
});

function previewPhoto(input) {
    const file = input.files[0];
    if (file) {
        $('#photoFileName').text(file.name).addClass('text-success').removeClass('text-muted');
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function (e) {
                $('#photoPreview').css('background-image', 'url(' + e.target.result + ')');
            };
            reader.readAsDataURL(file);
        }
    }
}

function previewIdProof(input) {
    const file = input.files[0];
    if (file) {
        $('#idProofFileName').text(file.name).addClass('text-success').removeClass('text-muted');
    }
}

function resetForm() {
    $('#photoFileName').text('No new file chosen').removeClass('text-success').addClass('text-muted');
    $('#idProofFileName').text('No new file chosen').removeClass('text-success').addClass('text-muted');
    $('#photo').val('');
    $('#id_proof').val('');
}
</script>

</body>
</html>
<?php
if (isset($conn)) {
    $conn->close();
}
?>