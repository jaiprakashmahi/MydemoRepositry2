<?php
session_start();
include '../conn.php';

if (!isset($_SESSION['center_logged_in'])) {
    header("Location: member_login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $center_name = $_SESSION['center_name'];
    
    // First verify student belongs to this center
    $check = $conn->prepare("SELECT id FROM onlinestudents WHERE id = ? AND study_center = ?");
    $check->bind_param("is", $id, $center_name);
    $check->execute();
    $check->store_result();
    
    if ($check->num_rows > 0) {
        $delete = $conn->prepare("DELETE FROM onlinestudents WHERE id = ?");
        $delete->bind_param("i", $id);
        
        if ($delete->execute()) {
            $_SESSION['success'] = "Student deleted successfully";
        } else {
            $_SESSION['error'] = "Error deleting student";
        }
    } else {
        $_SESSION['error'] = "Student not found or you don't have permission";
    }
}

header("Location: students.php");
exit();
?>