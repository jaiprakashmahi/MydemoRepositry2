<?php
include '../conn.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_id = $_POST['member_id'] ?? 0;
    $applicant_name = $_POST['applicant_name'] ?? '';
    $father_name = $_POST['father_name'] ?? '';
    $mobile_number = $_POST['mobile_number'] ?? '';
    $email_id = $_POST['email_id'] ?? '';
    $member_type_id = $_POST['member_type_id'] ?? '';
    $member_status = $_POST['member_status'] ?? 'Inactive';
    $payment_status = $_POST['payment_status'] ?? 'Pending';
    
    // Check for duplicate email/mobile
    $checkQuery = "SELECT id FROM member_information WHERE (email_id = ? OR mobile_number = ?) AND id != ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("ssi", $email_id, $mobile_number, $member_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if($checkResult->num_rows > 0) {
        $response['status'] = 'error';
        $response['message'] = 'Email or Mobile Number already exists.';
        echo json_encode($response);
        exit();
    }
    
    // Build update query
    $update_fields = "applicant_name = ?, father_name = ?, mobile_number = ?, email_id = ?, 
                     member_type_id = ?, member_status = ?, payment_status = ?";
    $params = array($applicant_name, $father_name, $mobile_number, $email_id, 
                   $member_type_id, $member_status, $payment_status);
    $types = "ssssiss";
    
    // Handle photo upload
    if(isset($_FILES['passport_photo']) && $_FILES['passport_photo']['error'] == 0) {
        $upload_dir = '../uploads/members/';
        if(!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES['passport_photo']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        
        if(in_array($file_ext, $allowed_ext)) {
            $new_filename = time() . '_' . uniqid() . '.' . $file_ext;
            $upload_path = $upload_dir . $new_filename;
            
            if(move_uploaded_file($_FILES['passport_photo']['tmp_name'], $upload_path)) {
                // Get old photo to delete
                $old_photo_query = "SELECT passport_photo FROM member_information WHERE id = ?";
                $old_stmt = $conn->prepare($old_photo_query);
                $old_stmt->bind_param("i", $member_id);
                $old_stmt->execute();
                $old_result = $old_stmt->get_result();
                $old_member = $old_result->fetch_assoc();
                
                // Delete old photo if exists
                if($old_member['passport_photo'] && file_exists('../' . $old_member['passport_photo'])) {
                    unlink('../' . $old_member['passport_photo']);
                }
                
                $update_fields .= ", passport_photo = ?";
                $params[] = 'uploads/members/' . $new_filename;
                $types .= "s";
            }
        }
    }
    
    // Handle password update
    if(!empty($_POST['password'])) {
        $update_fields .= ", password = ?";
        $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $types .= "s";
    }
    
    $params[] = $member_id;
    $types .= "i";
    
    $query = "UPDATE member_information SET $update_fields WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    
    if($stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = 'Member updated successfully!';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to update member: ' . $stmt->error;
    }
    
    $conn->close();
    echo json_encode($response);
}
?>