<?php
session_start();
include 'conn.php';

// Check if it's an AJAX request
if (isset($_GET['action']) && $_GET['action'] == 'get_modules' && isset($_GET['course_id'])) {
    $course_id = intval($_GET['course_id']);
    
    // Fetch modules for the selected course
    $modules = [];
    $query = "SELECT id, module_name FROM modules WHERE course_id = ? AND status = 'Active' ORDER BY module_name";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $modules[] = $row;
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'modules' => $modules
    ]);
    exit();
}

// If not an AJAX request or invalid parameters
header('Content-Type: application/json');
echo json_encode([
    'success' => false,
    'message' => 'Invalid request'
]);
exit();