<?php
session_start();
include 'conn.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Validate ID
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id === 0) {
    $_SESSION['error_msg'] = "Invalid Teacher ID.";
    header("Location: edit_teacher.php");
    exit();
}

// Fetch current teacher info
$stmt = $conn->prepare("SELECT * FROM teachers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    $_SESSION['error_msg'] = "Teacher not found.";
    header("Location: edit_teacher.php");
    exit();
}
$teacher = $result->fetch_assoc();

// Collect form data
$name         = $_POST['name'] ?? '';
$father_name  = $_POST['father_name'] ?? '';
$mother_name  = $_POST['mother_name'] ?? '';
$email        = $_POST['email'] ?? '';
$dob          = $_POST['dob'] ?? '';
$gender       = $_POST['gender'] ?? '';
$blood_group  = $_POST['blood_group'] ?? '';
$mobile       = $_POST['mobile'] ?? '';
$state_id     = $_POST['state_id'] ?? '';
$district_id  = $_POST['district_id'] ?? '';
$address      = $_POST['address'] ?? '';
$pin_code     = $_POST['pin_code'] ?? '';
$city         = $_POST['city'] ?? '';
$block        = $_POST['block'] ?? '';
$post_office  = $_POST['post_office'] ?? '';
$allot_center = $_POST['allot_center'] ?? '';

// Handle file uploads
$photo_name = $teacher['photo'];
if (!empty($_FILES['photo']['name'])) {
    $photo_name = time() . '_' . basename($_FILES['photo']['name']);
    move_uploaded_file($_FILES['photo']['tmp_name'], 'uploads/' . $photo_name);
}

$id_proof_name = $teacher['id_proof'];
if (!empty($_FILES['id_proof']['name'])) {
    $id_proof_name = time() . '_' . basename($_FILES['id_proof']['name']);
    move_uploaded_file($_FILES['id_proof']['tmp_name'], 'uploads/' . $id_proof_name);
}

// Update teacher record
$update = $conn->prepare("UPDATE teachers SET name=?, father_name=?, mother_name=?, email=?, dob=?, gender=?, blood_group=?, mobile=?, state_id=?, district_id=?, address=?, pin_code=?, city=?, block=?, post_office=?, photo=?, id_proof=?, allot_center=? WHERE id=?");
$update->bind_param(
    "ssssssssisssssssssi",
    $name, $father_name, $mother_name, $email, $dob, $gender, $blood_group, $mobile, $state_id, $district_id,
    $address, $pin_code, $city, $block, $post_office, $photo_name, $id_proof_name, $allot_center, $id
);

if ($update->execute()) {
    // === EDUCATION ===
    $conn->query("DELETE FROM teacher_education WHERE teacher_id = $id");
    $edu_count = count($_POST['education']);
    for ($i = 0; $i < $edu_count; $i++) {
        $education    = $_POST['education'][$i] ?? '';
        $session_from = $_POST['session_from'][$i] ?? '';
        $session_to   = $_POST['session_to'][$i] ?? '';
        $total_marks  = $_POST['total_marks'][$i] ?? '';
        $obt_marks    = $_POST['obt_marks'][$i] ?? '';
        $percentage   = $_POST['percentage'][$i] ?? '';
        $grade        = $_POST['grade'][$i] ?? '';

        $marksheet_name = '';
        if (!empty($_FILES['marksheet']['name'][$i])) {
            $marksheet_name = time() . '_' . basename($_FILES['marksheet']['name'][$i]);
            move_uploaded_file($_FILES['marksheet']['tmp_name'][$i], 'uploads/education_docs/' . $marksheet_name);
        }

        $stmt = $conn->prepare("INSERT INTO teacher_education (teacher_id, education, session_from, session_to, total_marks, obt_marks, percentage, grade, marksheet) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssiiss", $id, $education, $session_from, $session_to, $total_marks, $obt_marks, $percentage, $grade, $marksheet_name);
        $stmt->execute();
    }

    // === EXPERIENCE ===
    $conn->query("DELETE FROM teacher_experience WHERE teacher_id = $id");
    $exp_count = count($_POST['experience_org']);
    for ($i = 0; $i < $exp_count; $i++) {
        $org  = $_POST['experience_org'][$i] ?? '';
        $role = $_POST['experience_role'][$i] ?? '';
        $from = $_POST['experience_from'][$i] ?? '';
        $to   = $_POST['experience_to'][$i] ?? '';

        $exp_doc_name = '';
        if (!empty($_FILES['experience_doc']['name'][$i])) {
            $exp_doc_name = time() . '_' . basename($_FILES['experience_doc']['name'][$i]);
            move_uploaded_file($_FILES['experience_doc']['tmp_name'][$i], 'uploads/experience_docs/' . $exp_doc_name);
        }

        $stmt = $conn->prepare("INSERT INTO teacher_experience (teacher_id, org, role, experience_from, experience_to, experience_doc) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $id, $org, $role, $from, $to, $exp_doc_name);
        $stmt->execute();
    }
    $_SESSION['success_msg'] = "Teacher details updated successfully!";
    header("Location: EditTeacher.php?id=" . $_POST['id']);
    exit();
}

?>
