<?php
include 'conn.php';

if (isset($_GET['id']) && isset($_GET['teacher_status'])) {
    $id = intval($_GET['id']);
    $current_status = intval($_GET['teacher_status']);
    $new_status = $current_status == 1 ? 0 : 1;

    $stmt = $conn->prepare("UPDATE teachers SET teacher_status = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_status, $id);

    if ($stmt->execute()) {
        header("Location: Teachers.php"); // replace with your listing page
        exit();
    } else {
        echo "Failed to update status: " . $stmt->error;
    }
} else {
    echo "Invalid parameters.";
}
?>
