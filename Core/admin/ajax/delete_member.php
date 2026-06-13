<?php
include '../conn.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_id = $_POST['id'] ?? 0;
    
    // Get photo to delete
    $photo_query = "SELECT passport_photo FROM member_information WHERE id = ?";
    $photo_stmt = $conn->prepare($photo_query);
    $photo_stmt->bind_param("i", $member_id);
    $photo_stmt->execute();
    $photo_result = $photo_stmt->get_result();
    $member = $photo_result->fetch_assoc();
    
    // Delete member
    $query = "DELETE FROM member_information WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $member_id);
    
    if($stmt->execute()) {
        // Delete photo file if exists
        if($member && $member['passport_photo'] && file_exists('../' . $member['passport_photo'])) {
            unlink('../' . $member['passport_photo']);
        }
        
        $response['status'] = 'success';
        $response['message'] = 'Member deleted successfully!';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to delete member: ' . $stmt->error;
    }
    
    $conn->close();
    echo json_encode($response);
}
?>