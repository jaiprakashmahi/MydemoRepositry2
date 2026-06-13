<?php
include 'conn.php';

if (isset($_POST['district_id'])) {
    $district_id = intval($_POST['district_id']);
    $stmt = $conn->prepare("SELECT id, name FROM blocks WHERE district_id = ? ORDER BY name ASC");
    $stmt->bind_param("i", $district_id);
    $stmt->execute();
    $result = $stmt->get_result();

    echo '<option value="">Select Block</option>';
    while ($row = $result->fetch_assoc()) {
        echo '<option value="' . intval($row['id']) . '">' . htmlspecialchars($row['name']) . '</option>';
    }
    $stmt->close();
}
?>
