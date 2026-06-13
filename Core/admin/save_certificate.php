<?php
include 'conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_GET['id'] ?? 0; // you can also use a hidden input in the form
    $student_code = $_POST['student_code'];
    $name = $_POST['name'];
    $dob = $_POST['dob'];
    $father_name = $_POST['father_name'];
    $mother_name = $_POST['mother_name'];
    $course_name = $_POST['course_name'];
    $duration = $_POST['duration'];
    $study_center = $_POST['study_center'];
    $written_marks = (int)$_POST['written_marks'];
    $practical_marks = (int)$_POST['practical_marks'];
    $project_marks = (int)$_POST['project_marks'];
    $viva_marks = (int)$_POST['viva_marks'];
    $issue_date = $_POST['issue_date'];

    // Auto calculate total and percentage
    $total_marks = $written_marks + $practical_marks + $project_marks + $viva_marks;
    $max_total = 400; // Assuming each section is out of 100
    $percentage = ($total_marks / $max_total) * 100;

    // Auto calculate grade
    if ($percentage >= 90) {
        $grade = 'A+';
    } elseif ($percentage >= 80) {
        $grade = 'A';
    } elseif ($percentage >= 70) {
        $grade = 'B+';
    } elseif ($percentage >= 60) {
        $grade = 'B';
    } elseif ($percentage >= 50) {
        $grade = 'C';
    } else {
        $grade = 'F';
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO certificates 
        (student_id, student_code, name, dob, father_name, mother_name, course_name, duration, study_center,
         written_marks, practical_marks, project_marks, viva_marks, percentage, grade, issue_date) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("issssssssiiiidss", $student_id, $student_code, $name, $dob, $father_name, $mother_name, $course_name, $duration, $study_center, $written_marks, $practical_marks, $project_marks, $viva_marks, $percentage, $grade, $issue_date);

    if ($stmt->execute()) {
        echo "<div style='padding: 20px; background: #d4edda; color: #155724;'>Certificate saved successfully. Waiting for admin approval.</div>";
    } else {
        echo "<div style='padding: 20px; background: #f8d7da; color: #721c24;'>Error: " . $stmt->error . "</div>";
    }

    $stmt->close();
}
?>
