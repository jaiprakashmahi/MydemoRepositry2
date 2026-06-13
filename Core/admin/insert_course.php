<?php
include 'conn.php';

// Function to generate course code
function generateCourseCode($conn) {
    $query = "SELECT course_code FROM courses ORDER BY id DESC LIMIT 1";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastCode = $row['course_code'];
        $num = (int) substr($lastCode, 3); // Extract numeric part
        $num++;
        return "COU" . str_pad($num, 5, "0", STR_PAD_LEFT);
    } else {
        return "COU00001"; // First course code
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_code = generateCourseCode($conn);
    $course_name = $_POST['course_name'];
    $duration = $_POST['duration'];
    $price = $_POST['price'];
    $details = $_POST['details'];
    
    // Handle file upload
    $image_path = null;
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $target_dir = "uploads/courses/";
        
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $filename = $course_code . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $filename;
        
        // Check file size (max 2MB)
        if ($_FILES["image"]["size"] > 2000000) {
            echo "Error: File is too large. Maximum size is 2MB.";
            exit();
        }
        
        // Allow certain file formats
        $allowed_types = array("jpg", "jpeg", "png", "gif");
        if (!in_array(strtolower($file_extension), $allowed_types)) {
            echo "Error: Only JPG, JPEG, PNG & GIF files are allowed.";
            exit();
        }
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        }
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO courses (course_code, course_name, duration, price, details, image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssdss", $course_code, $course_name, $duration, $price, $details, $image_path);

    if ($stmt->execute()) {
        header("Location: courses.php?success=Course added successfully!");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>