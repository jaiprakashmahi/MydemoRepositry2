<?php
include 'conn.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
    
// 1. Generate student_code
$year = date('Y');
$prefix = "SIET" . $year;

$query = "SELECT student_code FROM students WHERE student_code LIKE '$prefix%' ORDER BY student_code DESC LIMIT 1";
$result = $conn->query($query);

if ($result && $row = $result->fetch_assoc()) {
    $lastCode = $row['student_code'];
    $lastNumber = (int)substr($lastCode, -4);
    $newNumber = $lastNumber + 1;
} else {
    $newNumber = 1;
}

$student_code = $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

// 2. Get form data
$state         = $_POST['state'] ?? '';
$district      = $_POST['district'] ?? '';
$study_center  = $_POST['study_center'] ?? '';
$course_name   = $_POST['course_name'] ?? '';
$duration      = $_POST['duration'] ?? '';
$price         = $_POST['price'] ?? '';
$name          = $_POST['name'] ?? '';
$father_name   = $_POST['father_name'] ?? '';
$mother_name   = $_POST['mother_name'] ?? '';
$email         = $_POST['email'] ?? '';
$dob           = $_POST['dob'] ?? '';
$gender        = $_POST['gender'] ?? '';
$blood_group   = $_POST['blood_group'] ?? '';
$mobile        = $_POST['mobile'] ?? '';
$address       = $_POST['address'] ?? '';
$pin_code      = $_POST['pin_code'] ?? '';
$city          = $_POST['city'] ?? '';
$block         = $_POST['block'] ?? '';
$post_office   = $_POST['post_office'] ?? '';
$username      = $_POST['username'] ?? '';
$password      = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);
$reg_amount = $_POST['reg_amount'] ?? '';
$payment_mode = $_POST['payment_mode'] ?? '';
$payment_status = ($payment_mode === 'Offline') ? 'Pending (Offline)' : 'Pending';

// 3. Handle file uploads
$uploadPath = "uploads/";
if (!is_dir($uploadPath)) mkdir($uploadPath, 0777, true);

$photo = $_FILES['photo']['name'] ?? '';
$id_proof = $_FILES['id_proof']['name'] ?? '';
$photo_tmp = $_FILES['photo']['tmp_name'] ?? '';
$id_proof_tmp = $_FILES['id_proof']['tmp_name'] ?? '';

if ($photo && $photo_tmp) {
    move_uploaded_file($photo_tmp, $uploadPath . $photo);
}
if ($id_proof && $id_proof_tmp) {
    move_uploaded_file($id_proof_tmp, $uploadPath . $id_proof);
}

// 4. Check for duplicate username or email
$check = $conn->prepare("SELECT id FROM students WHERE username = ? OR email = ?");
$check->bind_param("ss", $username, $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        Swal.fire({
            title: 'Duplicate Entry!',
            text: 'Username or Email already exists.',
            icon: 'error',
            confirmButtonText: 'Back'
        }).then(() => {
            window.history.back();
        });
    </script>";
    exit();
}
$check->close();

// 5. Insert into database
// 5. Insert into database
$sql = "INSERT INTO students (
    student_code, state, district, study_center, course_name, duration, price,
    name, father_name, mother_name, email, dob, gender, blood_group, mobile,
    address, pin_code, city, block, post_office, photo, id_proof, username, password, reg_amount, payment_mode, payment_status,
    approved, session_start, session_end, created_at
) VALUES (
    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$created_at = date('Y-m-d H:i:s');
$approved = 0;
$session_start = null;
$session_end = null;

$stmt->bind_param("sssssssssssssssssssssssssssssss",
    $student_code, $state, $district, $study_center, $course_name, $duration, $price,
    $name, $father_name, $mother_name, $email, $dob, $gender, $blood_group, $mobile,
    $address, $pin_code, $city, $block, $post_office, $photo, $id_proof, $username, $password, $reg_amount, $payment_mode, $payment_status,
    $approved, $session_start, $session_end, $created_at
);

if ($stmt->execute()) {
    echo "
    <!DOCTYPE html>
    <html>
    <head><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head>
    <body>
        <script>
            Swal.fire({
                title: 'Student Registered!',
                text: 'Student Code: \"$student_code\"',
                icon: 'success',
                confirmButtonText: 'Go to Student List'
            }).then(() => {
                window.location.href = 'RegStudentList.php';
            });
        </script>
    </body>
    </html>";
    exit();
} else {
    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        Swal.fire({
            title: 'Database Error!',
            text: '" . addslashes($stmt->error) . "',
            icon: 'error',
            confirmButtonText: 'Back'
        }).then(() => {
            window.history.back();
        });
    </script>";
}

$stmt->close();
$conn->close();
?>
