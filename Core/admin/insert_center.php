<?php
include 'conn.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: create-center.php");
    exit();
}

$center_name = trim($_POST['name'] ?? '');
$owner_name = trim($_POST['owner_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$date_of_create = $_POST['date'] ?? date('Y-m-d');
$phone = trim($_POST['phone'] ?? '');
$state = intval($_POST['state'] ?? 0);
$district = intval($_POST['district'] ?? 0);
$block = intval($_POST['block'] ?? 0);
$pincode = intval($_POST['pincode'] ?? 0);
$reg_amount = floatval($_POST['reg_amount'] ?? 3500);
$payment_mode = trim($_POST['payment_mode'] ?? '');
$payment_status = ($payment_mode === "Offline") ? "Pending" : "Paid";
$username = trim($_POST['username'] ?? '');
$password_plain = $_POST['password'] ?? '';

if ($center_name === '' || $owner_name === '' || $email === '' || $phone === '' || $state <= 0 || $district <= 0 || $block <= 0 || $username === '' || $password_plain === '') {
    echo "<script>alert('Please fill all required fields.'); window.history.back();</script>";
    exit();
}

$stmt = $conn->prepare("SELECT id FROM center_details WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo "<script>alert('Username already exists. Please choose a different username.'); window.history.back();</script>";
    exit();
}
$stmt->close();

if (!is_dir('uploads')) {
    mkdir('uploads', 0777, true);
}

$photo_path = '';
$id_proof_path = '';

if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === 0) {
    $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    $photo_path = time() . '_photo_' . uniqid() . '.' . $ext;
    move_uploaded_file($_FILES['photo']['tmp_name'], 'uploads/' . $photo_path);
}

if (!empty($_FILES['id_proof']['name']) && $_FILES['id_proof']['error'] === 0) {
    $ext = pathinfo($_FILES['id_proof']['name'], PATHINFO_EXTENSION);
    $id_proof_path = time() . '_proof_' . uniqid() . '.' . $ext;
    move_uploaded_file($_FILES['id_proof']['tmp_name'], 'uploads/' . $id_proof_path);
}

$prefix = "SFCC";
$result = $conn->query("SELECT center_code FROM center_details ORDER BY id DESC LIMIT 1");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $number = intval(substr($row['center_code'], strlen($prefix))) + 1;
} else {
    $number = 1;
}
$new_center_code = $prefix . str_pad($number, 4, "0", STR_PAD_LEFT);
$password = password_hash($password_plain, PASSWORD_BCRYPT);

$sql = "INSERT INTO center_details 
(center_code, center_name, owner_name, email, date_of_create, photo, phone, state, district, block, pincode, reg_amount, payment_mode, payment_status, id_proof, username, password, created_by, center_status)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'admin', 'Active')";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "sssssssiiiidsssss",
    $new_center_code,
    $center_name,
    $owner_name,
    $email,
    $date_of_create,
    $photo_path,
    $phone,
    $state,
    $district,
    $block,
    $pincode,
    $reg_amount,
    $payment_mode,
    $payment_status,
    $id_proof_path,
    $username,
    $password
);

if ($stmt->execute()) {
    echo "<script>alert('Center registered successfully.'); window.location.href='CenterList.php';</script>";
} else {
    echo "Error: " . $stmt->error;
}
$stmt->close();
$conn->close();
?>
