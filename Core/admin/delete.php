<?php
include 'conn.php';

// Check if course_code is set
if (isset($_GET['course_code'])) {
    $course_code = $_GET['course_code'];

    // Delete query
    $sql = "DELETE FROM courses WHERE course_code = '$course_code'";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Course deleted successfully!'); window.location.href='index.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
