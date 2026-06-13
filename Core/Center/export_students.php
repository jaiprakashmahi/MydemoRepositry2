<?php
session_start();
include '../conn.php';

if (!isset($_SESSION['center_logged_in'])) {
    header("Location: member_login.php");
    exit();
}

$center_name = $_SESSION['center_name'];

$query = "SELECT * FROM onlinestudents WHERE study_center = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $center_name);
$stmt->execute();
$result = $stmt->get_result();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=students_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, [
    'Student Code', 'Name', 'Father Name', 'Mother Name', 'Email', 'Mobile',
    'Course', 'Duration', 'Fees', 'Payment Status', 'Registration Date'
]);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['student_code'],
        $row['name'],
        $row['father_name'],
        $row['mother_name'],
        $row['email'],
        $row['mobile'],
        $row['course_name'],
        $row['duration'],
        $row['reg_amount'],
        $row['payment_status'],
        $row['created_at']
    ]);
}

fclose($output);
exit();
?>