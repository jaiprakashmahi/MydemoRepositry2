<?php
// Database connection settings
$servername = "162.215.230.15:3306 (default for MySQL, v8.0.39)";
$username = "sharnays";        // XAMPP MySQL default username
$password = "Sharnya@342";            // XAMPP me password empty hota hai
$dbname = "go4ongvh_Shrnya"; // Your database name

// Create connection with error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Set charset to UTF-8
    $conn->set_charset("utf8mb4");
    
    // Connection successful - You can uncomment below line for debugging
    // echo "Database connected successfully!";
    
} catch (mysqli_sql_exception $e) {
    // Handle connection error gracefully
    die("Database connection failed: " . $e->getMessage());
}

// Optional: Set timezone if needed
date_default_timezone_set('Asia/Kolkata');
?>

