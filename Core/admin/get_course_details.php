<?php
include 'conn.php';

if (isset($_GET['course_name'])) {
    $course_name = $_GET['course_name'];

    $stmt = $conn->prepare("SELECT duration, price FROM courses WHERE course_name = ?");
    $stmt->bind_param("s", $course_name);
    $stmt->execute();
    $stmt->bind_result($duration, $price);

    if ($stmt->fetch()) {
        echo json_encode(['duration' => $duration, 'price' => $price]);
    } else {
        echo json_encode(['duration' => '', 'price' => '']);
    }

    $stmt->close();
}
$conn->close();
?>
