<?php
session_start();
include 'conn.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php-error.log');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Generate Teacher Code
    $year = date('Y');
    $prefix = "SIET-TEACH" . $year;
    $query = "SELECT teacher_code FROM teachers WHERE teacher_code LIKE '$prefix%' ORDER BY teacher_code DESC LIMIT 1";
    $result = $conn->query($query);

    if ($result && $row = $result->fetch_assoc()) {
        $lastCode = $row['teacher_code'];
        $lastNumber = (int)substr($lastCode, -4);
        $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    } else {
        $newNumber = "0001";
    }
    $teacher_code = $prefix . '-' . $newNumber;

    // 2. Upload photo and ID proof
    $photo = '';
    if (!empty($_FILES['photo']['name'])) {
        $photo = uniqid() . '_' . $_FILES['photo']['name'];
        move_uploaded_file($_FILES['photo']['tmp_name'], "uploads/" . $photo);
    }

    $id_proof = '';
    if (!empty($_FILES['id_proof']['name'])) {
        $id_proof = uniqid() . '_' . $_FILES['id_proof']['name'];
        move_uploaded_file($_FILES['id_proof']['tmp_name'], "uploads/" . $id_proof);
    }

    // 3. Insert Teacher Data
    $stmt = $conn->prepare("INSERT INTO teachers 
        (teacher_code, name, father_name, mother_name, email, dob, gender, blood_group, mobile, state_id, district_id, address, pin_code, city, block, post_office, photo, id_proof) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("ssssssssisssssssss",
        $teacher_code,
        $_POST['name'],
        $_POST['father_name'],
        $_POST['mother_name'],
        $_POST['email'],
        $_POST['dob'],
        $_POST['gender'],
        $_POST['blood_group'],
        $_POST['mobile'],
        $_POST['state'],
        $_POST['district'],
        $_POST['address'],
        $_POST['pin_code'],
        $_POST['city'],
        $_POST['block'],
        $_POST['post_office'],
        $photo,
        $id_proof
    );

    if ($stmt->execute()) {
        $teacher_id = $stmt->insert_id;

        // 4. Insert Education Records
        foreach ($_POST['education'] as $index => $edu) {
            $marksheet_name = '';
            if (!empty($_FILES['marksheet']['name'][$index])) {
                $marksheet_name = uniqid() . '_' . $_FILES['marksheet']['name'][$index];
                move_uploaded_file($_FILES['marksheet']['tmp_name'][$index], "uploads/" . $marksheet_name);
            }

            $stmtEdu = $conn->prepare("INSERT INTO teacher_education 
                (teacher_id, education, session_from, session_to, total_marks, obt_marks, percentage, grade, marksheet) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmtEdu->bind_param("isssiiiss",
                $teacher_id,
                $edu,
                $_POST['session_from'][$index],
                $_POST['session_to'][$index],
                $_POST['total_marks'][$index],
                $_POST['obt_marks'][$index],
                $_POST['percentage'][$index],
                $_POST['grade'][$index],
                $marksheet_name
            );
            $stmtEdu->execute();
        }

        // 5. Insert Experience Records
        if (!empty($_POST['experience_org'])) {
            foreach ($_POST['experience_org'] as $i => $org) {
                $doc_name = '';
                if (!empty($_FILES['experience_doc']['name'][$i])) {
                    $doc_name = uniqid() . '_' . $_FILES['experience_doc']['name'][$i];
                    move_uploaded_file($_FILES['experience_doc']['tmp_name'][$i], "uploads/" . $doc_name);
                }

                $stmtExp = $conn->prepare("INSERT INTO teacher_experience 
                    (teacher_id, org, role, experience_from, experience_to, experience_doc) 
                    VALUES (?, ?, ?, ?, ?, ?)");

                $stmtExp->bind_param("isssss",
                    $teacher_id,
                    $org,
                    $_POST['experience_role'][$i],
                    $_POST['experience_from'][$i],
                    $_POST['experience_to'][$i],
                    $doc_name
                );
                $stmtExp->execute();
            }
        }

        // ✅ Store teacher code for SweetAlert
        $_SESSION['success_code'] = $teacher_code;
        header("Location: TeacherReg.php");
        exit();

    } else {
        // ❌ Store error message for SweetAlert
        $_SESSION['error_msg'] = $stmt->error;
        header("Location: TeacherReg.php");
        exit();
    }
}
?>
