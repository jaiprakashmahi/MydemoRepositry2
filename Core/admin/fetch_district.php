<?php
include 'conn.php';

if(isset($_POST["state_id"])) {
    $state_id = $_POST["state_id"];
    
    $query = "SELECT * FROM districts WHERE state_id = '$state_id' ORDER BY name";
    $result = mysqli_query($conn, $query);
    
    $options = '<option value="">Select District</option>';
    while($row = mysqli_fetch_assoc($result)) {
        $options .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
    }
    
    echo $options;
}
?>