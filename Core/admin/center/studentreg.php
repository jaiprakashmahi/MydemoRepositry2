<?php
include '../conn.php';

// 1. Generate student_code
$year = date('Y');
$query = "SELECT student_code FROM students WHERE student_code LIKE 'STD$year%' ORDER BY student_code DESC LIMIT 1";
$result = $conn->query($query);

if ($result && $row = $result->fetch_assoc()) {
    $lastCode = $row['student_code'];
    $lastNumber = (int)substr($lastCode, 7); // e.g., STD20250001 → 0001
    $newNumber = $lastNumber + 1;
} else {
    $newNumber = 1;
}
$student_code = 'STD' . $year . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

// 2. Get form data
$state = $_POST['state'];
$district = $_POST['district'];
$study_center = $_POST['study_center'];
$course_name = $_POST['course_name'];
$duration = $_POST['duration'];
$price = $_POST['price'];
$name = $_POST['name'];
$father_name = $_POST['father_name'];
$email = $_POST['email'];
$dob = $_POST['dob'];
$gender = $_POST['gender'];
$mobile = $_POST['mobile'];
$address = $_POST['address'];
$pin_code = $_POST['pin_code'];
$city = $_POST['city'];
$username = $_POST['username'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

// 3. Handle file uploads
$photo = $_FILES['photo']['name'];
$id_proof = $_FILES['id_proof']['name'];
$photo_tmp = $_FILES['photo']['tmp_name'];
$id_proof_tmp = $_FILES['id_proof']['tmp_name'];

move_uploaded_file($photo_tmp, "uploads/" . $photo);
move_uploaded_file($id_proof_tmp, "uploads/" . $id_proof);

// 4. Insert into DB with student_code
$sql = "INSERT INTO students (
    student_code, state, district, study_center, course_name, duration, price,
    name, father_name, email, dob, gender, mobile, address, pin_code, city,
    photo, id_proof, username, password, created_at
) VALUES (
    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
)";

$created_at = date('Y-m-d H:i:s'); // Format: 2025-06-18 12:34:56


$stmt->bind_param("sssssssssssssssssssss",
    $student_code, $state, $district, $study_center, $course_name, $duration, $price,
    $name, $father_name, $email, $dob, $gender, $mobile, $address, $pin_code, $city,
    $photo, $id_proof, $username, $password, $created_at
);

if ($stmt->execute()) {
    echo "✅ Student registered successfully!<br>Student Code: <strong>$student_code</strong>";
} else {
    echo "❌ Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
