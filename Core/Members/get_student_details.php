<?php
session_start();
include '../conn.php';

if (!isset($_SESSION['member_logged_in'])) {
    die('Unauthorized access');
}

if (isset($_POST['student_id'])) {
    $student_id = (int)$_POST['student_id'];
    
    $stmt = $conn->prepare("
        SELECT os.*, cd.center_name, cd.phone as center_phone
        FROM onlinestudents os
        LEFT JOIN center_details cd ON os.center_id = cd.id
        WHERE os.id = ?
    ");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo '<div class="row">';
        echo '<div class="col-md-6 mb-3">';
        echo '<strong>Student Code:</strong> ' . htmlspecialchars($row['student_code']) . '<br>';
        echo '<strong>Name:</strong> ' . htmlspecialchars($row['name']) . '<br>';
        echo '<strong>Father\'s Name:</strong> ' . htmlspecialchars($row['father_name']) . '<br>';
        echo '<strong>Mother\'s Name:</strong> ' . htmlspecialchars($row['mother_name']) . '<br>';
        echo '<strong>Date of Birth:</strong> ' . date('d M, Y', strtotime($row['dob'])) . '<br>';
        echo '<strong>Gender:</strong> ' . htmlspecialchars($row['gender']) . '<br>';
        echo '<strong>Blood Group:</strong> ' . htmlspecialchars($row['blood_group']) . '<br>';
        echo '</div>';
        
        echo '<div class="col-md-6 mb-3">';
        echo '<strong>Email:</strong> ' . htmlspecialchars($row['email']) . '<br>';
        echo '<strong>Mobile:</strong> ' . htmlspecialchars($row['mobile']) . '<br>';
        echo '<strong>Address:</strong> ' . htmlspecialchars($row['address']) . '<br>';
        echo '<strong>City:</strong> ' . htmlspecialchars($row['city']) . ', ';
        echo 'PIN: ' . htmlspecialchars($row['pin_code']) . '<br>';
        echo '<strong>State:</strong> ' . htmlspecialchars($row['state']) . '<br>';
        echo '<strong>District:</strong> ' . htmlspecialchars($row['district']) . '<br>';
        echo '</div>';
        
        echo '<div class="col-md-6 mb-3">';
        echo '<strong>Center:</strong> ' . htmlspecialchars($row['center_name']) . '<br>';
        echo '<strong>Center Phone:</strong> ' . htmlspecialchars($row['center_phone']) . '<br>';
        echo '<strong>Course:</strong> ' . htmlspecialchars($row['course_name']) . '<br>';
        echo '<strong>Duration:</strong> ' . htmlspecialchars($row['duration']) . '<br>';
        echo '<strong>Session:</strong> ' . date('d M, Y', strtotime($row['session_start'])) . ' to ' . 
             date('d M, Y', strtotime($row['session_end'])) . '<br>';
        echo '</div>';
        
        echo '<div class="col-md-6 mb-3">';
        echo '<strong>Course Price:</strong> ₹' . number_format($row['price'], 2) . '<br>';
        echo '<strong>Registration Amount:</strong> ₹' . number_format($row['reg_amount'], 2) . '<br>';
        echo '<strong>Payment Mode:</strong> ' . htmlspecialchars($row['payment_mode']) . '<br>';
        echo '<strong>Payment Status:</strong> <span class="badge ' . 
             ($row['payment_status'] == 'Paid' ? 'bg-success' : 'bg-warning') . '">' . 
             htmlspecialchars($row['payment_status']) . '</span><br>';
        echo '<strong>Student Status:</strong> <span class="badge ' . 
             ($row['status'] == 'Active' ? 'bg-success' : 'bg-danger') . '">' . 
             htmlspecialchars($row['status']) . '</span><br>';
        echo '<strong>Registration Date:</strong> ' . date('d M, Y H:i', strtotime($row['created_at'])) . '<br>';
        echo '</div>';
        echo '</div>';
    } else {
        echo '<div class="alert alert-danger">Student not found</div>';
    }
    
    $stmt->close();
}
?>