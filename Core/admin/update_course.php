<?php
include 'conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = $_POST['course_id'];
    $course_code = $_POST['course_code'];
    $course_name = $_POST['course_name'];
    $duration = $_POST['duration'];
    $price = $_POST['price'];
    $details = $_POST['details'];
    $current_image = $_POST['current_image'];
    
    // Handle file upload
    $image_path = $current_image;
    
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $target_dir = "uploads/courses/";
        
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        // Delete old image if it exists and we're uploading a new one
        if ($current_image && file_exists($current_image)) {
            unlink($current_image);
        }
        
        $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $filename = $course_code . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $filename;
        
        // Check file size (max 2MB)
        if ($_FILES["image"]["size"] > 2000000) {
            header("Location: courses.php?error=File is too large. Maximum size is 2MB.");
            exit();
        }
        
        // Allow certain file formats
        $allowed_types = array("jpg", "jpeg", "png", "gif");
        if (!in_array(strtolower($file_extension), $allowed_types)) {
            header("Location: courses.php?error=Only JPG, JPEG, PNG & GIF files are allowed.");
            exit();
        }
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        }
    }

    // Update database
    $stmt = $conn->prepare("UPDATE courses SET course_name = ?, duration = ?, price = ?, details = ?, image = ? WHERE id = ?");
    $stmt->bind_param("ssdssi", $course_name, $duration, $price, $details, $image_path, $course_id);

    if ($stmt->execute()) {
        header("Location: courses.php?success=Course updated successfully!");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>