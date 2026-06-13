<?php
include '../conn.php';

if (isset($_GET['course_id'])) {
    $course_id = intval($_GET['course_id']);
    
    $query = "SELECT id, module_name FROM modules WHERE course_id = ? AND is_active = 1 ORDER BY module_name";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo '<option value="">Select Module</option>';
    while ($row = $result->fetch_assoc()) {
        echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['module_name']) . '</option>';
    }
}
?>