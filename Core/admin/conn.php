<?php
// Database connection
$servername = "162.215.230.15:3306 (default for MySQL, v8.0.39)";
$username = "sharnays";        // XAMPP MySQL default username
$password = "Sharnya@342";            // XAMPP me password empty hota hai
$dbname = "go4ongvh_Shrnya"; // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


?>