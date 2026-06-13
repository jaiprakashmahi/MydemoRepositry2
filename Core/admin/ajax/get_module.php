<?php
session_start();
include '../conn.php';

header('Content-Type: application/json');

// Check if module ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid module ID']);
    exit();
}

$module_id = intval($_GET['id']);

// Fetch module data
$sql = "SELECT * FROM modules WHERE id = '$module_id'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $module = $result->fetch_assoc();
    echo json_encode(['success' => true, 'data' => $module]);
} else {
    echo json_encode(['success' => false, 'error' => 'Module not found']);
}

$conn->close();
?>