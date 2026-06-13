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

$upload_dirs = ['uploads/students/photos/', 'uploads/students/id_proofs/'];
foreach ($upload_dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

$center_result = $conn->query("SELECT id, center_name FROM center_details ORDER BY center_name ASC");
while ($center_result && $row = $center_result->fetch_assoc()) {
    $centers[] = $row;
}

$course_result = $conn->query("SELECT DISTINCT course_name, duration, price FROM courses ORDER BY course_name ASC");
while ($course_result && $row = $course_result->fetch_assoc()) {
    $courses[] = $row;
}

$state_result = $conn->query("SELECT id, name FROM states ORDER BY name ASC");
while ($state_result && $row = $state_result->fetch_assoc()) {
    $states[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
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

    if (empty($_FILES['photo']['name'])) {
        $errors[] = "Student photo is required";
    }

    if (empty($_FILES['id_proof']['name'])) {
        $errors[] = "ID proof is required";
    }

    if (empty($errors)) {
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

        $student_code = "STU" . rand(100, 999);
        $username = strtolower(preg_replace('/[^a-z0-9]/i', '', $name)) . rand(1000, 9999);
        $raw_password = substr(md5(uniqid()), 0, 8);
        $password_hash = password_hash($raw_password, PASSWORD_DEFAULT);

        $photo = '';
        $id_proof = '';
        $upload_errors = [];

        if (!empty($_FILES['photo']['name'])) {
            $photo_ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowed_photo = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($photo_ext, $allowed_photo)) {
                $photo = "photo_" . $student_code . "." . $photo_ext;
                $photo_path = "uploads/students/photos/" . $photo;

                if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path)) {
                    $upload_errors[] = "Photo upload failed.";
                }
            } else {
                $upload_errors[] = "Invalid photo format.";
            }
        }

        if (!empty($_FILES['id_proof']['name'])) {
            $id_ext = strtolower(pathinfo($_FILES['id_proof']['name'], PATHINFO_EXTENSION));
            $allowed_id = ['pdf', 'jpg', 'jpeg', 'png'];

            if (in_array($id_ext, $allowed_id)) {
                $id_proof = "id_" . $student_code . "." . $id_ext;
                $id_path = "uploads/students/id_proofs/" . $id_proof;

                if (!move_uploaded_file($_FILES['id_proof']['tmp_name'], $id_path)) {
                    $upload_errors[] = "ID proof upload failed.";
                }
            } else {
                $upload_errors[] = "Invalid ID proof format.";
            }
        }

        if (empty($upload_errors)) {
            $table_check = $conn->query("SHOW TABLES LIKE 'onlinestudents'");

            if ($table_check && $table_check->num_rows > 0) {
                $sql = "INSERT INTO onlinestudents (
                    student_code, username, password, name, father_name, mother_name,
                    email, dob, gender, blood_group, mobile, address, city, pin_code,
                    block, post_office, state, district, study_center, course_name,
                    duration, price, reg_amount, payment_mode, payment_status,
                    session_start, session_end, photo, id_proof, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

                $stmt = $conn->prepare($sql);

                if ($stmt) {
                    $types = "sssssssssssssssssssssddssssss";

                    $stmt->bind_param(
                        $types,
                        $student_code,
                        $username,
                        $password_hash,
                        $name,
                        $father_name,
                        $mother_name,
                        $email,
                        $dob,
                        $gender,
                        $blood_group,
                        $mobile,
                        $address,
                        $city,
                        $pin_code,
                        $block,
                        $post_office,
                        $state_name,
                        $district_name,
                        $study_center,
                        $course_name,
                        $duration,
                        $price,
                        $reg_amount,
                        $payment_mode,
                        $payment_status,
                        $session_start,
                        $session_end,
                        $photo,
                        $id_proof
                    );

                    if ($stmt->execute()) {
                        $success_message = "
                        <div class='alert alert-success alert-dismissible fade show'>
                            <h5><i class='fa fa-check-circle me-2'></i>Registration Successful!</h5>
                            <p><strong>Student Code:</strong> {$student_code}</p>
                            <p><strong>Username:</strong> {$username}</p>
                            <p><strong>Password:</strong> {$raw_password}</p>
                            <button onclick='copyLoginDetails(\"{$username}\", \"{$raw_password}\")' type='button' class='btn btn-sm btn-primary'>
                                Copy Credentials
                            </button>
                            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                        </div>";
                    } else {
                        $errors[] = "Database Error: " . $stmt->error;
                    }

                    $stmt->close();
                } else {
                    $errors[] = "SQL Prepare Error: " . $conn->error;
                }
            } else {
                $errors[] = "Table onlinestudents does not exist.";
            }
        } else {
            $errors = array_merge($errors, $upload_errors);
        }
    }

    if (!empty($errors)) {
        $error_message = "<div class='alert alert-danger alert-dismissible fade show'><h5>Registration Failed!</h5><ul>";
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
    <title>Add Student - Sharnay Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

<!-- FAVICONS ICON -->
    <link rel="shortcut icon" type="image/png" href="images/favicon.png">
    <link href="vendor/wow-master/css/libs/animate.css" rel="stylesheet">

    <link href="vendor/bootstrap-datepicker-master/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <link href="vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Style css -->
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
            background: linear-gradient(135deg, #667eea, #764ba2);
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
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, .12);
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
            border-color: #667eea;
            background: #f8f9ff;
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

        .btn-register {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            border: none;
            padding: 11px 34px;
            font-weight: 700;
            border-radius: 10px;
        }

        .btn-register:hover {
            color: #fff;
            opacity: .95;
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
                    <h4><i class="fa fa-user-graduate me-2"></i>Add Student</h4>
                    <p>Register student with personal, address, course, payment and document details.</p>
                </div>

                <div class="card-body p-4">
                    <?= $success_message ?>
                    <?= $error_message ?>

                    <form action="add-student.php" method="POST" enctype="multipart/form-data" id="studentForm">

                        <div class="form-section">
                            <h5 class="section-title"><i class="fa fa-user-circle me-2"></i>Personal Information</h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label required">Full Name</label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">Father's Name</label>
                                    <input type="text" class="form-control" name="father_name" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">Mother's Name</label>
                                    <input type="text" class="form-control" name="mother_name" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">Email</label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">Mobile</label>
                                    <input type="tel" class="form-control" name="mobile" pattern="[0-9]{10}" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">Date of Birth</label>
                                    <input type="date" class="form-control" name="dob" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">Gender</label>
                                    <select class="form-select" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Blood Group</label>
                                    <select class="form-select" name="blood_group">
                                        <option value="">Select Blood Group</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h5 class="section-title"><i class="fa fa-map-marker-alt me-2"></i>Address Information</h5>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label required">Complete Address</label>
                                    <textarea class="form-control" name="address" rows="2" required></textarea>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">City</label>
                                    <input type="text" class="form-control" name="city" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">Pin Code</label>
                                    <input type="text" class="form-control" name="pin_code" pattern="[0-9]{6}" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Block</label>
                                    <input type="text" class="form-control" name="block">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Post Office</label>
                                    <input type="text" class="form-control" name="post_office">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">State</label>
                                    <select class="form-select" id="state" name="state" required>
                                        <option value="">Select State</option>
                                        <?php foreach ($states as $state): ?>
                                            <option value="<?= $state['id'] ?>"><?= htmlspecialchars($state['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">District</label>
                                    <select class="form-select" id="district" name="district" required disabled>
                                        <option value="">Select State First</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h5 class="section-title"><i class="fa fa-graduation-cap me-2"></i>Institute Details</h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label required">Study Center</label>
                                    <select class="form-select" name="study_center" required>
                                        <option value="">Select Center</option>
                                        <?php foreach ($centers as $center): ?>
                                            <option value="<?= htmlspecialchars($center['center_name']) ?>">
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
                                                    data-price="<?= htmlspecialchars($course['price']) ?>">
                                                <?= htmlspecialchars($course['course_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Duration</label>
                                    <input type="text" class="form-control" id="duration" name="duration" readonly>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Course Fee (₹)</label>
                                    <input type="text" class="form-control" id="price" name="price" readonly>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">Registration Amount (₹)</label>
                                    <input type="number" class="form-control" name="reg_amount" min="0" step="0.01" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">Payment Mode</label>
                                    <select class="form-select" name="payment_mode" required>
                                        <option value="">Select Payment Mode</option>
                                        <option value="Cash">Cash</option>
                                        <option value="Online">Online</option>
                                        <option value="Cheque">Cheque</option>
                                        <option value="Card">Card</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">Payment Status</label>
                                    <select class="form-select" name="payment_status" required>
                                        <option value="">Select Payment Status</option>
                                        <option value="Paid">Paid</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Partial">Partial</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">Session Start</label>
                                    <input type="date" class="form-control" name="session_start" value="<?= date('Y-m-d') ?>" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">Session End</label>
                                    <input type="date" class="form-control" name="session_end" value="<?= date('Y-m-d', strtotime('+1 year')) ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h5 class="section-title"><i class="fa fa-file-upload me-2"></i>Document Upload</h5>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label required">Student Photo</label>
                                    <div class="file-upload-container" onclick="document.getElementById('photo').click()">
                                        <input type="file" id="photo" name="photo" accept="image/*" style="display:none;" onchange="previewPhoto(this)" required>
                                        <i class="fa fa-camera fa-3x mb-3 text-muted"></i>
                                        <h6>Upload Photo</h6>
                                        <p id="photoFileName" class="small text-muted">No file chosen</p>
                                        <div class="photo-preview" id="photoPreview"></div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label required">ID Proof</label>
                                    <div class="file-upload-container" onclick="document.getElementById('id_proof').click()">
                                        <input type="file" id="id_proof" name="id_proof" accept=".pdf,.jpg,.jpeg,.png" style="display:none;" onchange="previewIdProof(this)" required>
                                        <i class="fa fa-id-card fa-3x mb-3 text-muted"></i>
                                        <h6>Upload ID Proof</h6>
                                        <p id="idProofFileName" class="small text-muted">No file chosen</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" name="submit" class="btn btn-register me-2">
                                <i class="fa fa-user-plus me-2"></i>Register Student
                            </button>

                            <button type="reset" class="btn btn-secondary" onclick="resetForm()">
                                <i class="fa fa-redo me-2"></i>Reset Form
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

        $('#district')
            .html('<option value="">Loading districts...</option>')
            .prop('disabled', true);

        if (stateId !== '') {
            $.ajax({
                url: 'fetch_district.php',
                type: 'POST',
                data: { state_id: stateId },
                success: function (response) {
                    $('#district')
                        .html(response)
                        .prop('disabled', false);
                },
                error: function (xhr) {
                    console.log(xhr.responseText);
                    $('#district')
                        .html('<option value="">District not loaded</option>')
                        .prop('disabled', false);

                    alert('District loading failed. Check fetch_district.php path.');
                }
            });
        } else {
            $('#district')
                .html('<option value="">Select State First</option>')
                .prop('disabled', true);
        }
    });
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
    $('#photoFileName').text('No file chosen').removeClass('text-success').addClass('text-muted');
    $('#idProofFileName').text('No file chosen').removeClass('text-success').addClass('text-muted');
    $('#photoPreview').css('background-image', 'none');
    $('#duration').val('');
    $('#price').val('');
    $('#district').html('<option value="">Select State First</option>').prop('disabled', true);
}

function copyLoginDetails(username, password) {
    const text = `Username: ${username}\nPassword: ${password}`;
    navigator.clipboard.writeText(text).then(() => {
        alert('Login credentials copied!');
    });
}
</script>

</body>
</html>
<?php
if (isset($conn)) {
    $conn->close();
}
?>