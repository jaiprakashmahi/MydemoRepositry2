<?php
// center_logout.php
session_start();
include '../conn.php';

// Clear the token in DB
if (!empty($_SESSION['center_id'])) {
    $stmt = $conn->prepare("UPDATE center_details SET session_token = NULL WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['center_id']);
    $stmt->execute();
    $stmt->close();
}

session_unset();
session_destroy();

header('Location: center_login.php');
exit;
?>