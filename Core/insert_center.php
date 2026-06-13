<?php
// Database connection
include 'conn.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $center_name = $_POST['name'];
    $owner_name = $_POST['owner_name'];
    $email = $_POST['email'];
    $date_of_create = $_POST['date'];
    $phone = $_POST['phone'];
    $state = $_POST['state'];
    $district = $_POST['district'];
    $pincode = $_POST['pincode']; 
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password

    // Check if the username already exists
    $check_username_query = "SELECT * FROM center_details WHERE username = ?";
    $stmt = $conn->prepare($check_username_query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Username already exists
        echo "<script>alert('Username already exists. Please choose a different username.'); window.history.back();</script>";
        exit();
    }
    // File Uploads
    $photo = $_FILES['photo']['name'];
    $id_proof = $_FILES['id_proof']['name'];
    $photo_path = 'uploads/' . basename($photo);
    $id_proof_path = 'uploads/' . basename($id_proof);

    move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path);
    move_uploaded_file($_FILES['id_proof']['tmp_name'], $id_proof_path);
    // Generate Center Code
    $prefix = "SFCC"; // Define the prefix for the center code

    // Query to get the last inserted center code
    $result = $conn->query("SELECT center_code FROM center_details ORDER BY id DESC LIMIT 1");

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $last_code = $row['center_code'];

        // Extract the numeric part of the last code and increment it
        $number = intval(substr($last_code, strlen($prefix))) + 1;
    } else {
        // If no records exist, start from 1
        $number = 1;
    }

    // Generate the new center code
    $new_center_code = $prefix . str_pad($number, 4, "0", STR_PAD_LEFT);

    // Insert data into the database
   $sql = "INSERT INTO center_details (center_code, center_name, owner_name, email, date_of_create, photo, phone, state, district, pincode, id_proof, username, password)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssssssss", $new_center_code, $center_name, $owner_name, $email, $date_of_create, $photo, $phone, $state, $district, $pincode, $id_proof, $username, $password);

 if ($stmt->execute()) {
    echo "
    <!DOCTYPE html>
    <html>
    <head>
        <title>Center Created</title>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
        <script>
            Swal.fire({
                title: 'Success!',
                text: 'Center created successfully.',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'center_details.php';
                }
            });
        </script>
    </body>
    </html>";
    exit();
}

 else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
