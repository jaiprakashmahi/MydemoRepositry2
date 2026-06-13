<?php
include 'conn.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM teachers WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: Teachers.php?deleted=1");
        exit;
    } else {
        header("Location: Teachers.php?deleted=0");
        exit;
    }
} else {
    header("Location: Teachers.php?deleted=0");
    exit;
}
?>
