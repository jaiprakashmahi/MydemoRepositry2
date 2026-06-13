<?php
// insert_member.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'conn.php';

    // Input fields
    $member_type   = $_POST['Node1'];
    $center_name   = $_POST['name'] ?? '';
    $owner_name    = $_POST['owner_name'];
    $father_name   = $_POST['father_name'];
    $email         = $_POST['email'];
    $created_at    = $_POST['date'];
    $phone         = $_POST['phone'];
    $state         = $_POST['state'];
    $districts     = isset($_POST['district']) ? $_POST['district'] : [];
    $district      = implode(',', $districts); // Store comma-separated
    $post_office   = $_POST['post_office'];
    $pincode       = $_POST['pincode'];
    $username      = $_POST['username'];
    $password      = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check uniqueness for email or username
    $checkQuery = "SELECT id FROM members WHERE email = ? OR username = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("ss", $email, $username);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        echo "<script>alert('Email or Username already exists.'); window.history.back();</script>";
        exit();
    }

    // File Uploads
    $photo = $_FILES['photo']['name'];
    $id_card = $_FILES['id_card']['name'];
    $photo_path = 'uploads/' . basename($photo);
    $id_card_path = 'uploads/' . basename($id_card);

    move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path);
    move_uploaded_file($_FILES['id_card']['tmp_name'], $id_card_path);

    // Unicode logic (you can make this dynamic later)
    $state_code = "01";
    $district_code = "05";
    $block_code = "09";

    switch ($member_type) {
        case "State Node":
            $unicode = "SIET/SN/$state_code";
            break;
        case "Zonal Manager":
            $unicode = "SIET/ZM/$state_code";
            break;
        case "Dristic Co-Ordinator":
            $unicode = "SIET/DCO/$state_code/$district_code";
            break;
        case "Block Co-Ordinator":
            $unicode = "SIET/BCO/$state_code/$district_code/$block_code";
            break;
        default:
            $unicode = "SIET/GEN";
    }

    // Insert into DB
    $sql = "INSERT INTO members (member_type, unicode, center_name, owner_name, father_name, email, created_at, photo, phone, state, district, post_office, pincode, id_card, username, password) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssssssss",
        $member_type, $unicode, $center_name, $owner_name, $father_name, $email, $created_at,
        $photo, $phone, $state, $district, $post_office, $pincode, $id_card, $username, $password
    );

    if ($stmt->execute()) {
        header("Location: member.php?success=1");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
