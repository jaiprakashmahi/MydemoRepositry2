<?php
session_start();
include '../conn.php';

// Set content type to JSON
header('Content-Type: application/json');

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect all form data
    $applicant_name = trim($_POST['applicant_name'] ?? '');
    $father_name = trim($_POST['father_name'] ?? '');
    $mother_name = trim($_POST['mother_name'] ?? '');
    $mobile_number = trim($_POST['mobile_number'] ?? '');
    $email_id = trim($_POST['email_id'] ?? '');
    $uid_no = trim($_POST['uid_no'] ?? '');
    $pan_no = trim($_POST['pan_no'] ?? '');
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $post_applied = $_POST['post_applied'] ?? '';
    $member_type_id = intval($_POST['member_type_id'] ?? 0);
    $full_address = trim($_POST['full_address'] ?? '');
    $at_village = trim($_POST['at_village'] ?? '');
    $via = trim($_POST['via'] ?? '');
    $block = trim($_POST['block'] ?? '');
    $police_station = trim($_POST['police_station'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $educational_qualification = $_POST['educational_qualification'] ?? '';
    $extra_qualification = trim($_POST['extra_qualification'] ?? '');
    $experience = trim($_POST['experience'] ?? '');
    $bank_name = trim($_POST['bank_name'] ?? '');
    $branch_name = trim($_POST['branch_name'] ?? '');
    $ifsc_code = trim($_POST['ifsc_code'] ?? '');
    $account_number = trim($_POST['account_number'] ?? '');
    $account_type = $_POST['account_type'] ?? '';
    $reg_amount = floatval($_POST['reg_amount'] ?? 3500);
    $payment_mode = $_POST['payment_mode'] ?? '';
    $payment_status = $_POST['payment_status'] ?? '';
    $member_status = $_POST['member_status'] ?? 'Inactive';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Working area
    $working_state_id = !empty($_POST['working_state_id']) ? intval($_POST['working_state_id']) : 0;
    $working_district_ids = !empty($_POST['working_district_ids']) ? $_POST['working_district_ids'] : '';
    $working_block_ids = !empty($_POST['working_block_ids']) ? $_POST['working_block_ids'] : '';
    
    // Validation
    $errors = [];
    
    // Required fields check
    $required_fields = [
        'applicant_name' => 'Applicant Name',
        'father_name' => 'Father\'s Name',
        'mobile_number' => 'Mobile Number',
        'email_id' => 'Email ID',
        'date_of_birth' => 'Date of Birth',
        'post_applied' => 'Post Applied',
        'member_type_id' => 'Member Type',
        'full_address' => 'Full Address',
        'at_village' => 'At/Village',
        'district' => 'District',
        'pincode' => 'Pincode',
        'state' => 'State',
        'educational_qualification' => 'Educational Qualification',
        'bank_name' => 'Bank Name',
        'branch_name' => 'Branch Name',
        'ifsc_code' => 'IFSC Code',
        'account_number' => 'Account Number',
        'account_type' => 'Account Type',
        'payment_mode' => 'Payment Mode',
        'payment_status' => 'Payment Status',
        'username' => 'Username',
        'password' => 'Password'
    ];
    
    foreach ($required_fields as $field => $label) {
        if (empty($$field)) {
            $errors[] = $label . ' is required';
        }
    }
    
    // Phone number validation (10 digits)
    if (!empty($mobile_number) && !preg_match('/^[0-9]{10}$/', $mobile_number)) {
        $errors[] = 'Mobile number must be exactly 10 digits';
    }
    
    // UID (Aadhar) validation (12 digits) - optional
    if (!empty($uid_no) && !preg_match('/^[0-9]{12}$/', $uid_no)) {
        $errors[] = 'UID (Aadhar) number must be exactly 12 digits';
    }
    
    // PAN validation (10 characters) - optional
    if (!empty($pan_no) && !preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $pan_no)) {
        $errors[] = 'PAN number must be in format: ABCDE1234F (all caps)';
    }
    
    // Pincode validation (6 digits)
    if (!empty($pincode) && !preg_match('/^[0-9]{6}$/', $pincode)) {
        $errors[] = 'Pincode must be exactly 6 digits';
    }
    
    // Email validation
    if (!empty($email_id) && !filter_var($email_id, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address';
    }
    
    // Date of birth validation (must be at least 18 years old)
    if (!empty($date_of_birth)) {
        $dob = new DateTime($date_of_birth);
        $today = new DateTime();
        $age = $today->diff($dob)->y;
        if ($age < 18) {
            $errors[] = 'Must be at least 18 years old';
        }
    }
    
    // Password validation (minimum 6 characters)
    if (!empty($password) && strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }
    
    // Working area validation based on post applied
    if ($post_applied === "State Nodal" && empty($working_state_id)) {
        $errors[] = 'State selection is required for State Nodal';
    } elseif ($post_applied === "Zonal Manager") {
        $district_array = !empty($working_district_ids) ? explode(',', $working_district_ids) : [];
        $district_count = count($district_array);
        if ($district_count < 1 || $district_count > 5) {
            $errors[] = 'Zonal Manager must select 1 to 5 districts';
        }
    } elseif ($post_applied === "District Coordinator") {
        $district_array = !empty($working_district_ids) ? explode(',', $working_district_ids) : [];
        if (count($district_array) !== 1) {
            $errors[] = 'District Coordinator must select exactly 1 district';
        }
    } elseif ($post_applied === "Block Coordinator") {
        $district_array = !empty($working_district_ids) ? explode(',', $working_district_ids) : [];
        $block_array = !empty($working_block_ids) ? explode(',', $working_block_ids) : [];
        
        if (count($district_array) !== 1) {
            $errors[] = 'Block Coordinator must select exactly 1 district';
        }
        
        $block_count = count($block_array);
        if ($block_count < 1 || $block_count > 5) {
            $errors[] = 'Block Coordinator must select 1 to 5 blocks';
        }
    }
    
    // If validation errors
    if (!empty($errors)) {
        $response['status'] = 'error';
        $response['message'] = implode('<br>', $errors);
        echo json_encode($response);
        exit();
    }
    
    // Check for duplicates
    $checkQuery = "SELECT id FROM member_information WHERE email_id = ? OR mobile_number = ? OR username = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("sss", $email_id, $mobile_number, $username);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if($checkResult->num_rows > 0) {
        $response['status'] = 'error';
        $response['message'] = 'Email, Mobile Number or Username already exists.';
        echo json_encode($response);
        exit();
    }
    $checkStmt->close();
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Handle photo upload
    $passport_photo = '';
    if(isset($_FILES['passport_photo']) && $_FILES['passport_photo']['error'] == 0) {
        $upload_dir = '../uploads/members/';
        
        // Create directory if it doesn't exist
        if(!file_exists($upload_dir)) {
            if(!mkdir($upload_dir, 0755, true)) {
                $response['status'] = 'error';
                $response['message'] = 'Failed to create upload directory. Please check permissions.';
                echo json_encode($response);
                exit();
            }
        }
        
        // Check if directory is writable
        if(!is_writable($upload_dir)) {
            $response['status'] = 'error';
            $response['message'] = 'Upload directory is not writable. Please check permissions.';
            echo json_encode($response);
            exit();
        }
        
        $file_name = $_FILES['passport_photo']['name'];
        $file_tmp = $_FILES['passport_photo']['tmp_name'];
        $file_size = $_FILES['passport_photo']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        
        if(!in_array($file_ext, $allowed_ext)) {
            $response['status'] = 'error';
            $response['message'] = 'Only JPG, JPEG, PNG, and GIF files are allowed';
            echo json_encode($response);
            exit();
        }
        
        // Check file size (max 2MB)
        if ($file_size > 2097152) {
            $response['status'] = 'error';
            $response['message'] = 'Photo size must be less than 2MB';
            echo json_encode($response);
            exit();
        }
        
        // Generate unique filename
        $new_filename = time() . '_' . uniqid() . '.' . $file_ext;
        $upload_path = $upload_dir . $new_filename;
        
        // Move uploaded file
        if(move_uploaded_file($file_tmp, $upload_path)) {
            // Store relative path
            $passport_photo = 'uploads/members/' . $new_filename;
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Failed to upload photo. Please try again.';
            echo json_encode($response);
            exit();
        }
    } else {
        $response['status'] = 'error';
        // Check specific upload error
        if(isset($_FILES['passport_photo'])) {
            $upload_error = $_FILES['passport_photo']['error'];
            if($upload_error == 1 || $upload_error == 2) {
                $response['message'] = 'File is too large. Maximum size is 2MB.';
            } elseif($upload_error == 3) {
                $response['message'] = 'File upload was interrupted.';
            } elseif($upload_error == 4) {
                $response['message'] = 'Passport photo is required';
            } else {
                $response['message'] = 'Passport photo upload failed with error code: ' . $upload_error;
            }
        } else {
            $response['message'] = 'Passport photo is required';
        }
        echo json_encode($response);
        exit();
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Generate member code
        $year = date('Y');
        
        // Get post code based on post applied
        $post_code_map = [
            "State Nodal" => "SN",
            "Zonal Manager" => "ZM", 
            "District Coordinator" => "DC",
            "Block Coordinator" => "BC"
        ];
        $post_code = $post_code_map[$post_applied] ?? "MB";
        
        // Get state code (first 2 letters)
        $state_code = strtoupper(substr($state, 0, 2));
        
        // Get next sequence number
        $seqQuery = "SELECT COUNT(*) as count FROM member_information WHERE YEAR(created_at) = ?";
        $seqStmt = $conn->prepare($seqQuery);
        $seqStmt->bind_param("s", $year);
        $seqStmt->execute();
        $seqResult = $seqStmt->get_result();
        $row = $seqResult->fetch_assoc();
        $next_seq = $row['count'] + 1;
        $seqStmt->close();
        
        // Format sequence number with leading zeros
        $seq_number = str_pad($next_seq, 4, '0', STR_PAD_LEFT);
        
        // Generate final code
        $unicode = "SIET/{$post_code}/{$state_code}/{$seq_number}/{$year}";
        
        // Insert into member_information
        $query = "INSERT INTO member_information (
            unicode, member_type_id, applicant_name, father_name, mother_name, mobile_number, 
            email_id, uid_no, pan_no, date_of_birth, post_applied, full_address, 
            at_village, via, block, police_station, district, pincode, state, 
            educational_qualification, extra_qualification, experience, bank_name, 
            branch_name, ifsc_code, account_number, account_type, passport_photo, 
            reg_amount, payment_mode, payment_status, member_status, username, password
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param(
            "sissssssssssssssssssssssssssdsssss",
            $unicode, $member_type_id, $applicant_name, $father_name, $mother_name, $mobile_number,
            $email_id, $uid_no, $pan_no, $date_of_birth, $post_applied, $full_address,
            $at_village, $via, $block, $police_station, $district, $pincode, $state,
            $educational_qualification, $extra_qualification, $experience, $bank_name,
            $branch_name, $ifsc_code, $account_number, $account_type, $passport_photo,
            $reg_amount, $payment_mode, $payment_status, $member_status, $username, $hashed_password
        );
        
        if($stmt->execute()) {
            $member_id = $stmt->insert_id;
            
            // Insert into member_working_area
            if($working_state_id || $working_district_ids || $working_block_ids) {
                $areaQuery = "INSERT INTO member_working_area (member_id, state_id, district_ids, block_ids, assigned_date) 
                             VALUES (?, ?, ?, ?, CURDATE())";
                $areaStmt = $conn->prepare($areaQuery);
                $areaStmt->bind_param("iiss", $member_id, $working_state_id, $working_district_ids, $working_block_ids);
                $areaStmt->execute();
                $areaStmt->close();
            }
            
            $conn->commit();
            $response['status'] = 'success';
            $response['message'] = "Member created successfully!<br>Member Unicode: $unicode<br>Member ID: $member_id";
            $response['member_id'] = $member_id;
            $response['unicode'] = $unicode;
        } else {
            throw new Exception("Failed to insert member information: " . $stmt->error);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        $conn->rollback();
        
        // Delete uploaded file if transaction failed
        if(!empty($passport_photo) && file_exists('../' . $passport_photo)) {
            @unlink('../' . $passport_photo);
        }
        
        $response['status'] = 'error';
        $response['message'] = "Error: " . $e->getMessage();
    }
    
    $conn->close();
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>