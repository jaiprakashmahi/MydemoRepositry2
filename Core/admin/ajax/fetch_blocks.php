<?php
include '../conn.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log for debugging
file_put_contents('fetch_blocks_debug.log', date('Y-m-d H:i:s') . " - Request received\n", FILE_APPEND);
file_put_contents('fetch_blocks_debug.log', "POST data: " . print_r($_POST, true) . "\n", FILE_APPEND);

if (isset($_POST['district_id'])) {
    $district_id = intval($_POST['district_id']);
    
    file_put_contents('fetch_blocks_debug.log', "District ID after intval: " . $district_id . "\n", FILE_APPEND);
    
    // Query to fetch blocks
    $query = "SELECT id, name FROM blocks WHERE district_id = ? ORDER BY name";
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("i", $district_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        file_put_contents('fetch_blocks_debug.log', "Query executed. Rows found: " . $result->num_rows . "\n", FILE_APPEND);
        
        if ($result->num_rows > 0) {
            $options = '<option value="">Select Block</option>';
            while ($row = $result->fetch_assoc()) {
                $options .= '<option value="' . $row['id'] . '">' . htmlspecialchars($row['name']) . '</option>';
            }
            echo $options;
            file_put_contents('fetch_blocks_debug.log', "Options generated: " . strlen($options) . " characters\n", FILE_APPEND);
        } else {
            echo '<option value="">No blocks found for this district</option>';
            file_put_contents('fetch_blocks_debug.log', "No blocks found for district ID: " . $district_id . "\n", FILE_APPEND);
        }
        
        $stmt->close();
    } else {
        echo '<option value="">Database query error</option>';
        file_put_contents('fetch_blocks_debug.log', "Statement preparation failed: " . $conn->error . "\n", FILE_APPEND);
    }
} else {
    echo '<option value="">District ID not provided</option>';
    file_put_contents('fetch_blocks_debug.log', "No district_id in POST\n", FILE_APPEND);
}

$conn->close();
?>