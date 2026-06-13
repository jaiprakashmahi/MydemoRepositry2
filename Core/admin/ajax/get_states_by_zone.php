<?php
// ajax/get_states_by_zone.php
include '../conn.php';

if(isset($_POST['zone_id'])) {
    $zone_id = intval($_POST['zone_id']);
    
    // If zone has state_ids field
    $query = "SELECT s.id, s.name 
              FROM states s 
              INNER JOIN zone z ON FIND_IN_SET(s.id, z.state_ids) 
              WHERE z.id = ? 
              ORDER BY s.name";
    
    // Or if you have zone_states table:
    // $query = "SELECT s.id, s.name FROM states s 
    //           INNER JOIN zone_states zs ON s.id = zs.state_id 
    //           WHERE zs.zone_id = ? ORDER BY s.name";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $zone_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo '<option value="">-- Select State --</option>';
    while($row = $result->fetch_assoc()) {
        echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
    }
}
?>