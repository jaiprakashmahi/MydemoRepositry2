<?php
include 'conn.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $stmt = $conn->prepare("UPDATE onlinestudents SET payment_status = 'Approved', approved = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<script>
                alert('Payment approved successfully!');
                window.location.href = 'PendingStudent.php';
             </script>";
    } else {
        echo "Error approving payment: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>
