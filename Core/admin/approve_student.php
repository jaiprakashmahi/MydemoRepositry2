<?php
include 'conn.php';

    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);

        $result = $conn->query("SELECT duration FROM onlinestudents WHERE id = $id");

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $durationMonths = (int) $row['duration']; 
            $sessionStart = date('Y-m-d');
            $sessionEnd = date('Y-m-d', strtotime("+$durationMonths months", strtotime($sessionStart)));
            $update = "UPDATE onlinestudents 
                    SET approved = 1, session_start = '$sessionStart', session_end = '$sessionEnd' 
                    WHERE id = $id";

            if ($conn->query($update)) {
                echo "<script>alert('Student approved and session started.'); window.location.href='student.php';</script>";
            } else {
                echo "Error updating student: " . $conn->error;
            }
        } else {
            echo "Student not found.";
        }
    } else {
        echo "No ID provided.";
    }
?> 


