<?php
include '../conn.php';

if(isset($_POST['state_id'])) {
    $state_id = intval($_POST['state_id']);
    
    $query = "SELECT id, name FROM districts WHERE state_id = ? ORDER BY name";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $state_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo '<option value="">Select District</option>';
    while($row = $result->fetch_assoc()) {
        echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['name']) . '</option>';
    }
    $stmt->close();
}
?>