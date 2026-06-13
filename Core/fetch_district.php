<?php
include 'conn.php'; // Make sure this path is correct

if (isset($_POST['state_id'])) {
    $state_id = intval($_POST['state_id']); // Always sanitize input

    $stmt = $conn->prepare("SELECT id, name FROM districts WHERE state_id = ?");
    $stmt->bind_param("i", $state_id);
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<option value=''>Select District</option>";
    while ($row = $result->fetch_assoc()) {
        echo "<option value='{$row['id']}'>{$row['name']}</option>";
    }

    $stmt->close();
}
?>
