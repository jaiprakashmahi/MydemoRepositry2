<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'conn.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Member Management System</title>
    
    <!-- Bootstrap 5 CSS -->
    <link rel="shortcut icon" type="image/png" href="images/favicon.png">
    <link href="vendor/wow-master/css/libs/animate.css" rel="stylesheet">
    <link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="vendor/bootstrap-datepicker-master/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <link href="vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Style css -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/admin-standard.css" rel="stylesheet">
    
    <style>
        .drop-zone {
            border: 2px dashed #007bff;
            border-radius: 5px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background-color: #f8f9fa;
            min-height: 150px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        .drop-zone:hover {
            background-color: #e9ecef;
            border-color: #0056b3;
        }
        .drop-zone.dragover {
            background-color: #d4edda;
            border-color: #28a745;
        }
        .drop-zone input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        .preview-image {
            max-width: 120px;
            max-height: 120px;
            margin: 10px auto;
            display: block;
            border-radius: 5px;
            object-fit: cover;
        }
        .required:after {
            content: " *";
            color: red;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .step {
            flex: 1;
            text-align: center;
            padding: 10px;
            background: #f8f9fa;
            margin: 0 5px;
            border-radius: 5px;
            cursor: pointer;
            min-width: 120px;
        }
        .step.active {
            background: #007bff;
            color: white;
        }
        .tab-pane {
            padding: 15px 0;
        }
        .modal-xl {
            max-width: 1200px;
        }
        .member-photo {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .action-buttons .btn {
            padding: 5px 10px;
            font-size: 12px;
        }
        .card-header {
            background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .table th {
            background-color: #667eea;
            color: white;
            border: none;
        }
        .badge-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 500;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .select2-container {
            width: 100% !important;
        }
        .form-control.is-invalid {
            border-color: #dc3545;
        }
        .text-danger {
            color: #dc3545;
            font-size: 0.875em;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
    <div name="main-wrapper" >
        <?php include 'menu.php'; ?>
        
        <div class="content-body">
            <div class="container-fluid">
                <!-- Header -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="mb-0 text-white"><i class="fas fa-users me-2"></i>Member Management System</h4>
                                    <button type="button" class="btn btn-light" id="createMemberBtn">
                                        <i class="fas fa-plus-circle me-1"></i> Create New Member
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Members List -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0 text-white">All Members</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered" id="membersTable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Photo</th>
                                                <th>Name</th>
                                                <th>Mobile</th>
                                                <th>Email</th>
                                                <th>Member Type</th>
                                                <th>Working Area</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $query = "SELECT mi.*, mt.type_name, mwa.state_id, mwa.district_ids, mwa.block_ids
                                                     FROM member_information mi
                                                     LEFT JOIN member_types mt ON mi.member_type_id = mt.id
                                                     LEFT JOIN member_working_area mwa ON mi.id = mwa.member_id
                                                     ORDER BY mi.created_at DESC";
                                            $result = mysqli_query($conn, $query);
                                            
                                            if(mysqli_num_rows($result) > 0) {
                                                $counter = 1;
                                                while($row = mysqli_fetch_assoc($result)) {
                                                    // Get working area details
                                                    $working_area = '';
                                                    
                                                    // Get state name
                                                    if($row['state_id']) {
                                                        $state_query = "SELECT name FROM states WHERE id = {$row['state_id']}";
                                                        $state_result = mysqli_query($conn, $state_query);
                                                        if($state_result && mysqli_num_rows($state_result) > 0) {
                                                            $state = mysqli_fetch_assoc($state_result);
                                                            $working_area .= '<strong>State:</strong> ' . $state['name'];
                                                        }
                                                    }
                                                    
                                                    // Get district names
                                                    if($row['district_ids']) {
                                                        $district_ids = explode(',', $row['district_ids']);
                                                        $district_names = [];
                                                        foreach($district_ids as $district_id) {
                                                            if(!empty($district_id)) {
                                                                $district_query = "SELECT name FROM districts WHERE id = " . intval($district_id);
                                                                $district_result = mysqli_query($conn, $district_query);
                                                                if($district_result && mysqli_num_rows($district_result) > 0) {
                                                                    $district = mysqli_fetch_assoc($district_result);
                                                                    $district_names[] = $district['name'];
                                                                }
                                                            }
                                                        }
                                                        if(!empty($district_names)) {
                                                            $working_area .= '<br><strong>Districts:</strong> ' . implode(', ', $district_names);
                                                        }
                                                    }
                                                    
                                                    // Get block names
                                                    if($row['block_ids']) {
                                                        $block_ids = explode(',', $row['block_ids']);
                                                        $block_names = [];
                                                        foreach($block_ids as $block_id) {
                                                            if(!empty($block_id)) {
                                                                $block_query = "SELECT name FROM blocks WHERE id = " . intval($block_id);
                                                                $block_result = mysqli_query($conn, $block_query);
                                                                if($block_result && mysqli_num_rows($block_result) > 0) {
                                                                    $block = mysqli_fetch_assoc($block_result);
                                                                    $block_names[] = $block['name'];
                                                                }
                                                            }
                                                        }
                                                        if(!empty($block_names)) {
                                                            $working_area .= '<br><strong>Blocks:</strong> ' . implode(', ', $block_names);
                                                        }
                                                    }
                                                    
                                                    $status_class = $row['member_status'] == 'Active' ? 'bg-success' : 'bg-danger';
                                                    $status_text = $row['member_status'] == 'Active' ? 'Active' : 'Inactive';
                                                    
                                                    // Fix photo path
                                                    $photo_path = !empty($row['passport_photo']) ? $row['passport_photo'] : '';
                                                    
                                                    echo "<tr>";
                                                    echo "<td>{$counter}</td>";
                                                    echo "<td>";
                                                    if(!empty($photo_path) && file_exists('../' . $photo_path)) {
                                                        echo "<img src='../{$photo_path}' class='member-photo' alt='Member Photo' onerror='this.style.display=\"none\"; this.nextElementSibling.style.display=\"flex\";'>";
                                                        echo "<div class='member-photo bg-secondary d-flex align-items-center justify-content-center text-white' style='display:none;'>
                                                                <i class='fas fa-user'></i>
                                                              </div>";
                                                    } else {
                                                        echo "<div class='member-photo bg-secondary d-flex align-items-center justify-content-center text-white'>
                                                                <i class='fas fa-user'></i>
                                                              </div>";
                                                    }
                                                    echo "</td>";
                                                    echo "<td>
                                                            <strong>{$row['applicant_name']}</strong><br>
                                                            <small class='text-muted'>{$row['father_name']}</small>
                                                          </td>";
                                                    echo "<td>{$row['mobile_number']}</td>";
                                                    echo "<td>{$row['email_id']}</td>";
                                                    echo "<td><span class='badge bg-info'>{$row['type_name']}</span></td>";
                                                    echo "<td><small>{$working_area}</small></td>";
                                                    echo "<td><span class='badge {$status_class}'>{$status_text}</span></td>";
                                                    echo "<td>
                                                            <div class='action-buttons'>
                                                                <button class='btn btn-sm btn-info view-member' data-id='{$row['id']}' title='View'>
                                                                    <i class='fas fa-eye'></i>
                                                                </button>
                                                                <button class='btn btn-sm btn-warning edit-member' data-id='{$row['id']}' title='Edit'>
                                                                    <i class='fas fa-edit'></i>
                                                                </button>
                                                                <button class='btn btn-sm btn-danger delete-member' data-id='{$row['id']}' title='Delete'>
                                                                    <i class='fas fa-trash'></i>
                                                                </button>
                                                            </div>
                                                          </td>";
                                                    echo "</tr>";
                                                    $counter++;
                                                }
                                            } else {
                                                echo "<tr><td colspan='9' class='text-center text-muted py-4'>No members found. Create your first member!</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include 'footer.php';?>
    </div>

    <!-- Create Member Modal -->
    <div class="modal fade" id="createMemberModal" tabindex="-1" aria-labelledby="createMemberModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form id="memberForm" action="insert_member.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="createMemberModalLabel">
                            <i class="fas fa-user-plus me-2"></i>Application Form
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Step Indicator -->
                        <div class="step-indicator mb-4">
                            <div class="step active" data-step="1">1. Personal Info</div>
                            <div class="step" data-step="2">2. Address</div>
                            <div class="step" data-step="3">3. Education & Bank</div>
                            <div class="step" data-step="4">4. Working Area</div>
                            <div class="step" data-step="5">5. Photo & Payment</div>
                        </div>

                        <!-- Tab Content -->
                        <div class="tab-content">
                            <!-- Step 1: Personal Information -->
                            <div class="tab-pane fade show active" id="step1">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label required">Applicant Name</label>
                                        <input type="text" class="form-control" name="applicant_name" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label required">Father's Name</label>
                                        <input type="text" class="form-control" name="father_name" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Mother's Name</label>
                                        <input type="text" class="form-control" name="mother_name">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label required">Mobile Number</label>
                                        <input type="tel" class="form-control" name="mobile_number" id="mobile_number" maxlength="10" pattern="[0-9]{10}" required>
                                        <small class="text-muted">Enter 10 digits only</small>
                                        <div class="invalid-feedback">Mobile number must be exactly 10 digits</div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label required">Email ID</label>
                                        <input type="email" class="form-control" name="email_id" required>
                                        <div class="invalid-feedback">Please enter a valid email address</div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">UID No (Aadhar)</label>
                                        <input type="text" class="form-control" name="uid_no" id="uid_no" maxlength="12" pattern="[0-9]{12}">
                                        <small class="text-muted">Enter 12 digits only</small>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">PAN No</label>
                                        <input type="text" class="form-control" name="pan_no" id="pan_no" maxlength="10" pattern="[A-Z]{5}[0-9]{4}[A-Z]{1}">
                                        <small class="text-muted">Format: ABCDE1234F</small>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label required">Date of Birth</label>
                                        <input type="date" class="form-control" name="date_of_birth" required max="<?php echo date('Y-m-d', strtotime('-18 years')); ?>">
                                        <small class="text-muted">Must be at least 18 years old</small>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label required">Post Applied</label>
                                        <select class="form-control" name="post_applied" id="post_applied" required>
                                            <option value="">Select Post</option>
                                            <option value="State Nodal">State Nodal</option>
                                            <option value="Zonal Manager">Zonal Manager</option>
                                            <option value="District Coordinator">District Coordinator</option>
                                            <option value="Block Coordinator">Block Coordinator</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Member Type</label>
                                        <select class="form-control" name="member_type_id" id="member_type_id" required>
                                            <option value="">Select Member Type</option>
                                            <?php
                                            $query = "SELECT * FROM member_types ORDER BY level";
                                            $result = mysqli_query($conn, $query);
                                            while($row = mysqli_fetch_assoc($result)) {
                                                echo "<option value='{$row['id']}' data-max-districts='{$row['max_districts']}' data-max-blocks='{$row['max_blocks']}'>{$row['type_name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 2: Address Information -->
                            <div class="tab-pane fade" id="step2">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label required">Full Address</label>
                                        <textarea class="form-control" name="full_address" rows="3" required></textarea>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label required">At/Village</label>
                                        <input type="text" class="form-control" name="at_village" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Via</label>
                                        <input type="text" class="form-control" name="via">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label required">Block/Mandal</label>
                                        <input type="text" class="form-control" name="block" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Police Station</label>
                                        <input type="text" class="form-control" name="police_station">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label required">District</label>
                                        <input type="text" class="form-control" name="district" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label required">Pincode</label>
                                        <input type="text" class="form-control" name="pincode" id="pincode" maxlength="6" pattern="[0-9]{6}" required>
                                        <div class="invalid-feedback">Pincode must be exactly 6 digits</div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label required">State</label>
                                        <select class="form-control" name="state" required>
                                            <option value="">Select State</option>
                                            <?php
                                            $query = "SELECT * FROM states ORDER BY name";
                                            $result = mysqli_query($conn, $query);
                                            while($row = mysqli_fetch_assoc($result)) {
                                                echo "<option value='{$row['name']}'>{$row['name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 3: Education & Bank Details -->
                            <div class="tab-pane fade" id="step3">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Educational Qualification</label>
                                        <select class="form-control" name="educational_qualification" required>
                                            <option value="">Select Qualification</option>
                                            <option value="10th">10th Pass</option>
                                            <option value="12th">12th Pass</option>
                                            <option value="Graduate">Graduate</option>
                                            <option value="Post Graduate">Post Graduate</option>
                                            <option value="Diploma">Diploma</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Extra Qualification</label>
                                        <input type="text" class="form-control" name="extra_qualification" placeholder="e.g., Computer Course, Language, etc.">
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Experience</label>
                                        <textarea class="form-control" name="experience" rows="3" placeholder="Previous work experience"></textarea>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label required">Bank Name</label>
                                        <input type="text" class="form-control" name="bank_name" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label required">Branch Name</label>
                                        <input type="text" class="form-control" name="branch_name" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label required">IFSC Code</label>
                                        <input type="text" class="form-control" name="ifsc_code" id="ifsc_code" required>
                                        <small class="text-muted">Format: SBIN0123456</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Account Number</label>
                                        <input type="text" class="form-control" name="account_number" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Account Type</label>
                                        <select class="form-control" name="account_type" required>
                                            <option value="">Select Type</option>
                                            <option value="Savings">Savings</option>
                                            <option value="Current">Current</option>
                                            <option value="Salary">Salary</option>
                                            <option value="Fixed Deposit">Fixed Deposit</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 4: Working Area -->
                            <div class="tab-pane fade" id="step4">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <h6 class="text-primary mb-3"><i class="fas fa-map-marker-alt me-2"></i>Working Area Selection</h6>
                                        <p class="text-muted">Select areas based on post applied</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">State</label>
                                        <select class="form-control select2-state" id="working_state">
                                            <option value="">Select State</option>
                                            <?php
                                            $query = "SELECT * FROM states ORDER BY name";
                                            $result = mysqli_query($conn, $query);
                                            while($row = mysqli_fetch_assoc($result)) {
                                                echo "<option value='{$row['id']}'>{$row['name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">District</label>
                                        <select class="form-control select2-district" id="working_district" multiple="multiple">
                                            <option value="">Select District</option>
                                        </select>
                                        <small class="text-muted" id="districtHelp"></small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Block</label>
                                        <select class="form-control select2-block" id="working_block" multiple="multiple">
                                            <option value="">Select Block</option>
                                        </select>
                                        <small class="text-muted" id="blockHelp"></small>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="alert alert-info">
                                            <strong><i class="fas fa-info-circle me-2"></i>Selection Rules:</strong><br>
                                            • <strong>State Nodal:</strong> Select 1 State only<br>
                                            • <strong>Zonal Manager:</strong> Select up to 5 Districts<br>
                                            • <strong>District Coordinator:</strong> Select 1 District only<br>
                                            • <strong>Block Coordinator:</strong> Select 1 District & up to 5 Blocks
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 5: Photo & Payment -->
                            <div class="tab-pane fade" id="step5">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Passport Size Photo</label>
                                        <div class="drop-zone" id="dropZone">
                                            <i class="fas fa-cloud-upload-alt fa-2x text-primary mb-2"></i>
                                            <p class="mb-1">Drag & drop photo here</p>
                                            <p class="text-muted small">or click to browse</p>
                                            <input type="file" id="passport_photo" name="passport_photo" accept="image/*" required>
                                            <div id="photoPreview" class="mt-3"></div>
                                        </div>
                                        <small class="text-muted">Max size: 2MB (JPG, JPEG, PNG, GIF)</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Registration Amount</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" class="form-control" name="reg_amount" value="3500" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label required">Payment Mode</label>
                                        <select class="form-control" name="payment_mode" required>
                                            <option value="">Select Mode</option>
                                            <option value="Online">Online</option>
                                            <option value="Cash">Cash</option>
                                            <option value="Bank Transfer">Bank Transfer</option>
                                            <option value="Cheque">Cheque</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label required">Payment Status</label>
                                        <select class="form-control" name="payment_status" required>
                                            <option value="">Select Status</option>
                                            <option value="Paid">Paid</option>
                                            <option value="Pending">Pending</option>
                                            <option value="Partial">Partial</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label required">Member Status</label>
                                        <select class="form-control" name="member_status" required>
                                            <option value="Active">Active</option>
                                            <option value="Inactive" selected>Inactive</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Username</label>
                                        <input type="text" class="form-control" name="username" required>
                                        <small class="text-muted">Must be unique</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Password</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" name="password" required minlength="6" id="passwordField">
                                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword()">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted">Minimum 6 characters</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden fields for working area -->
                        <input type="hidden" name="working_state_id" id="working_state_id">
                        <input type="hidden" name="working_district_ids" id="working_district_ids">
                        <input type="hidden" name="working_block_ids" id="working_block_ids">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-info" id="prevBtn">Previous</button>
                        <button type="button" class="btn btn-primary" id="nextBtn">Next</button>
                        <button type="submit" class="btn btn-success" id="submitBtn" style="display:none;">Submit Application</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Member Modal -->
    <div class="modal fade" id="viewMemberModal" tabindex="-1" aria-labelledby="viewMemberModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="viewMemberModalLabel">
                        <i class="fas fa-user me-2"></i>Member Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="viewMemberContent">
                    <!-- Content will be loaded via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Member Modal -->
    <div class="modal fade" id="editMemberModal" tabindex="-1" aria-labelledby="editMemberModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form id="editMemberForm" action="ajax/update_member.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title" id="editMemberModalLabel">
                            <i class="fas fa-edit me-2"></i>Edit Member
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="editMemberContent">
                        <!-- Content will be loaded via AJAX -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Update Member</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    $(document).ready(function() {
        console.log("Document ready");
        
        // Show create member modal
        $('#createMemberBtn').click(function() {
            console.log("Create button clicked");
            $('#createMemberModal').modal('show');
        });
        
        // Initialize multi-step form when modal is shown
        $('#createMemberModal').on('shown.bs.modal', function() {
            console.log("Modal shown, initializing form...");
            initializeMultiStepForm();
        });
        
        // View Member
        $(document).on('click', '.view-member', function() {
            var memberId = $(this).data('id');
            $.ajax({
                url: 'ajax/view_member.php',
                type: 'POST',
                data: {id: memberId},
                success: function(response) {
                    $('#viewMemberContent').html(response);
                    $('#viewMemberModal').modal('show');
                }
            });
        });
        
        // Edit Member
        $(document).on('click', '.edit-member', function() {
            var memberId = $(this).data('id');
            $.ajax({
                url: 'ajax/edit_member.php',
                type: 'POST',
                data: {id: memberId},
                success: function(response) {
                    $('#editMemberContent').html(response);
                    $('#editMemberModal').modal('show');
                }
            });
        });
        
        // Delete Member
        $(document).on('click', '.delete-member', function() {
            var memberId = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'ajax/delete_member.php',
                        type: 'POST',
                        data: {id: memberId},
                        success: function(response) {
                            var result = JSON.parse(response);
                            if(result.status == 'success') {
                                Swal.fire(
                                    'Deleted!',
                                    result.message,
                                    'success'
                                ).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Error!',
                                    result.message,
                                    'error'
                                );
                            }
                        }
                    });
                }
            });
        });
        
        // Input validations
        $('#mobile_number').on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 10);
        });
        
        $('#uid_no').on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 12);
        });
        
        $('#pan_no').on('input', function() {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 10);
        });
        
        $('#pincode').on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);
        });
    });
    
    // Initialize multi-step form
    function initializeMultiStepForm() {
        console.log("Initializing multi-step form");
        
        let currentStep = 1;
        const totalSteps = 5;
        
        // Destroy existing Select2 instances if they exist
        if ($('.select2-state').hasClass('select2-hidden-accessible')) {
            $('.select2-state').select2('destroy');
        }
        if ($('.select2-district').hasClass('select2-hidden-accessible')) {
            $('.select2-district').select2('destroy');
        }
        if ($('.select2-block').hasClass('select2-hidden-accessible')) {
            $('.select2-block').select2('destroy');
        }
        
        // Initialize Select2 dropdowns
        try {
            $('.select2-state').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Select State',
                dropdownParent: $('#createMemberModal')
            });
            
            $('.select2-district').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Select District',
                dropdownParent: $('#createMemberModal')
            });
            
            $('.select2-block').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Select Block',
                dropdownParent: $('#createMemberModal')
            });
            
            console.log("Select2 initialized successfully");
        } catch (error) {
            console.error("Select2 initialization error:", error);
        }
        
        // Load districts when state changes
        $('#working_state').off('change').on('change', function() {
            var stateId = $(this).val();
            if(stateId) {
                $('#working_district').prop('disabled', false);
                $.ajax({
                    url: 'ajax/fetch_districts.php',
                    type: 'POST',
                    data: { state_id: stateId },
                    success: function(response) {
                        $('#working_district').html(response);
                        $('#working_district').trigger('change.select2');
                    },
                    error: function() {
                        console.error("Error loading districts");
                        Swal.fire('Error', 'Failed to load districts', 'error');
                    }
                });
            } else {
                $('#working_district').html('<option value="">Select District</option>');
                $('#working_district').prop('disabled', true).trigger('change.select2');
            }
            $('#working_block').html('<option value="">Select Block</option>');
            $('#working_block').prop('disabled', true).trigger('change.select2');
        });
        
        // Load blocks when district changes
        $('#working_district').off('change').on('change', function() {
            var selectedDistricts = $(this).val();
            var postApplied = $('#post_applied').val();
            
            console.log("District changed:", selectedDistricts);
            console.log("Post Applied:", postApplied);
            
            // Clear and disable block selection initially
            $('#working_block').html('<option value="">Select Block</option>');
            $('#working_block').prop('disabled', true);
            
            if(postApplied === "Block Coordinator" && selectedDistricts && selectedDistricts.length > 0) {
                // For Block Coordinator, only allow single district
                if(selectedDistricts.length === 1) {
                    var districtId = selectedDistricts[0];
                    console.log("Loading blocks for district ID:", districtId);
                    
                    // Show loading in block dropdown
                    $('#working_block').html('<option value="">Loading blocks...</option>');
                    $('#working_block').prop('disabled', false);
                    
                    // Load blocks via AJAX
                    $.ajax({
                        url: 'ajax/fetch_blocks.php',
                        type: 'POST',
                        data: { district_id: districtId },
                        dataType: 'html',
                        success: function(response) {
                            console.log("Blocks AJAX response received");
                            console.log("Response:", response);
                            
                            // Check if response contains an error message
                            if (response.includes('Error loading blocks') || response.includes('not found')) {
                                console.error("Error in response:", response);
                                Swal.fire('Error', response.replace(/<[^>]*>/g, ''), 'error');
                            }
                            
                            $('#working_block').empty().html(response);
                            
                            // Reinitialize Select2
                            if ($('#working_block').hasClass('select2-hidden-accessible')) {
                                $('#working_block').select2('destroy');
                            }
                            $('#working_block').select2({
                                theme: 'bootstrap-5',
                                width: '100%',
                                placeholder: 'Select Block (Max 5)',
                                maximumSelectionLength: 5,
                                dropdownParent: $('#createMemberModal')
                            });
                            
                            console.log("Blocks loaded successfully");
                        },
                        error: function(xhr, status, error) {
                            console.error("Error loading blocks:", error);
                            console.error("Status:", status);
                            console.error("Response:", xhr.responseText);
                            Swal.fire('Error', 'Failed to load blocks. Please try again.', 'error');
                            $('#working_block').empty().html('<option value="">Error loading blocks</option>');
                            $('#working_block').prop('disabled', false);
                            
                            // Reinitialize Select2
                            if ($('#working_block').hasClass('select2-hidden-accessible')) {
                                $('#working_block').select2('destroy');
                            }
                            $('#working_block').select2({
                                theme: 'bootstrap-5',
                                width: '100%',
                                placeholder: 'Select Block (Max 5)',
                                maximumSelectionLength: 5,
                                dropdownParent: $('#createMemberModal')
                            });
                        }
                    });
                } else {
                    // Clear blocks if multiple districts selected
                    $('#working_block').empty().html('<option value="">Select Block</option>');
                    $('#working_block').prop('disabled', true);
                    
                    // Reinitialize Select2
                    if ($('#working_block').hasClass('select2-hidden-accessible')) {
                        $('#working_block').select2('destroy');
                    }
                    $('#working_block').select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        placeholder: 'Select Block',
                        dropdownParent: $('#createMemberModal'),
                        disabled: true
                    });
                    
                    Swal.fire('Warning', 'Block Coordinator can only select one district', 'warning');
                }
            } else {
                // For other post types, disable block selection
                $('#working_block').empty().html('<option value="">Select Block</option>');
                $('#working_block').prop('disabled', true);
                
                // Reinitialize Select2
                if ($('#working_block').hasClass('select2-hidden-accessible')) {
                    $('#working_block').select2('destroy');
                }
                $('#working_block').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: 'Select Block',
                    dropdownParent: $('#createMemberModal'),
                    disabled: true
                });
            }
        });
        
        // Step click handler
        $('.step').off('click').on('click', function() {
            const step = $(this).data('step');
            showStep(step);
        });
        
        // Next button handler
        $('#nextBtn').off('click').on('click', function() {
            console.log("Next button clicked, current step:", currentStep);
            if(validateStep(currentStep)) {
                if(currentStep < totalSteps) {
                    currentStep++;
                    showStep(currentStep);
                }
            }
        });
        
        // Previous button handler
        $('#prevBtn').off('click').on('click', function() {
            if(currentStep > 1) {
                currentStep--;
                showStep(currentStep);
            }
        });
        
        // Show specific step
        function showStep(step) {
            console.log("Showing step:", step);
            
            // Update step indicators
            $('.step').removeClass('active');
            $(`.step[data-step="${step}"]`).addClass('active');
            
            // Show/hide tab content
            $('.tab-pane').removeClass('show active');
            $(`#step${step}`).addClass('show active');
            
            // Update buttons
            $('#prevBtn').toggle(step > 1);
            $('#nextBtn').toggle(step < totalSteps);
            $('#submitBtn').toggle(step === totalSteps);
            
            currentStep = step;
            
            // Update working area rules when on step 4
            if(step === 4) {
                setTimeout(() => {
                    updateWorkingAreaRules();
                }, 100);
            }
        }
        
        // Validate current step
        function validateStep(step) {
            console.log("Validating step:", step);
            let isValid = true;
            
            // Clear previous validation
            $(`#step${step} .form-control, #step${step} .form-select`).removeClass('is-invalid');
            
            // Check required fields
            $(`#step${step} [required]`).each(function() {
                if(!$(this).val()) {
                    $(this).addClass('is-invalid');
                    isValid = false;
                    console.log("Validation failed for:", $(this).attr('name'));
                }
            });
            
            // Special validation for step 4 (Working Area)
            if(step === 4) {
                const postApplied = $('#post_applied').val();
                console.log("Post Applied for validation:", postApplied);
                
                if(postApplied) {
                    const selectedState = $('#working_state').val();
                    const selectedDistricts = $('#working_district').val();
                    const selectedBlocks = $('#working_block').val();
                    
                    console.log("=== STEP 4 VALIDATION ===");
                    console.log("Selected State:", selectedState);
                    console.log("Selected Districts:", selectedDistricts);
                    console.log("Selected Blocks:", selectedBlocks);
                    
                    // Convert to arrays for validation
                    let districtArray = [];
                    if (selectedDistricts) {
                        if (Array.isArray(selectedDistricts)) {
                            districtArray = selectedDistricts;
                        } else if (selectedDistricts) {
                            districtArray = [selectedDistricts];
                        }
                    }
                    
                    let blockArray = [];
                    if (selectedBlocks) {
                        if (Array.isArray(selectedBlocks)) {
                            blockArray = selectedBlocks;
                        } else if (selectedBlocks) {
                            blockArray = [selectedBlocks];
                        }
                    }
                    
                    // Clear previous validation
                    $('#working_state, #working_district, #working_block').removeClass('is-invalid');
                    
                    let validationError = '';
                    
                    if(postApplied === "State Nodal") {
                        if(!selectedState) {
                            validationError = 'Please select a state for State Nodal';
                            $('#working_state').addClass('is-invalid');
                            isValid = false;
                        }
                    } else if(postApplied === "Zonal Manager") {
                        if(districtArray.length === 0 || districtArray.length > 5) {
                            validationError = 'Zonal Manager must select 1 to 5 districts';
                            $('#working_district').addClass('is-invalid');
                            isValid = false;
                        }
                    } else if(postApplied === "District Coordinator") {
                        if(districtArray.length !== 1) {
                            validationError = 'District Coordinator must select exactly 1 district';
                            $('#working_district').addClass('is-invalid');
                            isValid = false;
                        }
                    } else if(postApplied === "Block Coordinator") {
                        if(districtArray.length !== 1) {
                            validationError = 'Block Coordinator must select exactly 1 district';
                            $('#working_district').addClass('is-invalid');
                            isValid = false;
                        }
                        if(blockArray.length === 0 || blockArray.length > 5) {
                            validationError = validationError ? validationError + ' and 1 to 5 blocks' : 'Block Coordinator must select 1 to 5 blocks';
                            $('#working_block').addClass('is-invalid');
                            isValid = false;
                        }
                    }
                    
                    if(validationError) {
                        Swal.fire('Error', validationError, 'error');
                        console.log("Validation failed:", validationError);
                    }
                } else {
                    Swal.fire('Error', 'Please select post applied first', 'error');
                    $('#post_applied').addClass('is-invalid');
                    isValid = false;
                }
            }
            
            // Validate phone number format for step 1
            if(step === 1) {
                const phoneNumber = $('input[name="mobile_number"]').val();
                if(phoneNumber && !/^[0-9]{10}$/.test(phoneNumber)) {
                    Swal.fire('Error', 'Mobile number must be exactly 10 digits', 'error');
                    $('input[name="mobile_number"]').addClass('is-invalid');
                    isValid = false;
                }
                
                // Validate UID (Aadhar) if provided
                const uidNo = $('input[name="uid_no"]').val();
                if(uidNo && !/^[0-9]{12}$/.test(uidNo)) {
                    Swal.fire('Error', 'UID (Aadhar) number must be exactly 12 digits', 'error');
                    $('input[name="uid_no"]').addClass('is-invalid');
                    isValid = false;
                }
                
                // Validate PAN if provided
                const panNo = $('input[name="pan_no"]').val();
                if(panNo && !/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/.test(panNo)) {
                    Swal.fire('Error', 'PAN number must be in format: ABCDE1234F (all caps)', 'error');
                    $('input[name="pan_no"]').addClass('is-invalid');
                    isValid = false;
                }
                
                // Validate email format
                const email = $('input[name="email_id"]').val();
                if(email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    Swal.fire('Error', 'Please enter a valid email address', 'error');
                    $('input[name="email_id"]').addClass('is-invalid');
                    isValid = false;
                }
            }
            
            // Validate pincode for step 2
            if(step === 2) {
                const pincode = $('input[name="pincode"]').val();
                if(pincode && !/^[0-9]{6}$/.test(pincode)) {
                    Swal.fire('Error', 'Pincode must be exactly 6 digits', 'error');
                    $('input[name="pincode"]').addClass('is-invalid');
                    isValid = false;
                }
            }
            
            // Validate IFSC code for step 3
            if(step === 3) {
                const ifscCode = $('input[name="ifsc_code"]').val();
                if(ifscCode && !/^[A-Z]{4}0[A-Z0-9]{6}$/.test(ifscCode)) {
                    Swal.fire('Error', 'Please enter a valid IFSC code (e.g., SBIN0123456)', 'error');
                    $('input[name="ifsc_code"]').addClass('is-invalid');
                    isValid = false;
                }
            }
            
            console.log("Step validation result:", isValid);
            return isValid;
        }
        
        // Update working area rules based on post applied
        function updateWorkingAreaRules() {
            const postApplied = $('#post_applied').val();
            const stateSelect = $('#working_state');
            const districtSelect = $('#working_district');
            const blockSelect = $('#working_block');
            const districtHelp = $('#districtHelp');
            const blockHelp = $('#blockHelp');
            
            console.log("Updating rules for post:", postApplied);
            
            // Get current values
            const currentState = stateSelect.val();
            const currentDistricts = districtSelect.val();
            const currentBlocks = blockSelect.val();
            
            if(!postApplied) {
                districtHelp.text('Please select post applied first');
                blockHelp.text('');
                return;
            }
            
            // Reset states
            stateSelect.prop('disabled', false);
            districtSelect.prop('disabled', false);
            blockSelect.prop('disabled', true); // Block disabled by default
            
            if(postApplied === "State Nodal") {
                districtSelect.prop('multiple', false);
                districtSelect.prop('disabled', true);
                blockSelect.prop('disabled', true);
                districtHelp.text('State Nodal works at state level only');
                blockHelp.text('Not applicable');
                
            } else if(postApplied === "Zonal Manager") {
                districtSelect.prop('multiple', true);
                blockSelect.prop('disabled', true);
                districtHelp.text('Select 1 to 5 districts');
                blockHelp.text('Not required');
                
            } else if(postApplied === "District Coordinator") {
                districtSelect.prop('multiple', false);
                blockSelect.prop('disabled', true);
                districtHelp.text('Select exactly 1 district');
                blockHelp.text('Not required');
                
            } else if(postApplied === "Block Coordinator") {
                districtSelect.prop('multiple', false);
                blockSelect.prop('disabled', false); // Enable block selection for Block Coordinator
                districtHelp.text('Select exactly 1 district');
                blockHelp.text('Select 1 to 5 blocks from that district');
            }
            
            // Reinitialize Select2 for district
            if (districtSelect.hasClass('select2-hidden-accessible')) {
                districtSelect.select2('destroy');
            }
            districtSelect.select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: districtSelect.prop('multiple') ? 'Select District(s)' : 'Select District',
                dropdownParent: $('#createMemberModal'),
                disabled: districtSelect.prop('disabled'),
                maximumSelectionLength: districtSelect.prop('multiple') ? 5 : 1
            });
            
            // Restore district value
            if (currentDistricts) {
                districtSelect.val(currentDistricts).trigger('change');
            }
            
            // Reinitialize Select2 for block
            if (blockSelect.hasClass('select2-hidden-accessible')) {
                blockSelect.select2('destroy');
            }
            blockSelect.select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Select Block',
                dropdownParent: $('#createMemberModal'),
                disabled: blockSelect.prop('disabled'),
                maximumSelectionLength: postApplied === "Block Coordinator" ? 5 : 1
            });
            
            // Restore block value
            if (currentBlocks) {
                blockSelect.val(currentBlocks).trigger('change');
            }
        }
        
        // Post applied change handler
        $('#post_applied').off('change').on('change', function() {
            console.log("Post applied changed to:", $(this).val());
            
            const postApplied = $(this).val();
            let memberTypeId = '';
            
            // Map post applied to member type
            if(postApplied === "State Nodal") {
                memberTypeId = '1';
            } else if(postApplied === "Zonal Manager") {
                memberTypeId = '2';
            } else if(postApplied === "District Coordinator") {
                memberTypeId = '3';
            } else if(postApplied === "Block Coordinator") {
                memberTypeId = '4';
            }
            
            // Set member type
            $('#member_type_id').val(memberTypeId).trigger('change');
            
            // Clear working area selections
            $('#working_state').val('').trigger('change');
            $('#working_district').val('').trigger('change');
            $('#working_district').html('<option value="">Select District</option>');
            $('#working_block').val('').trigger('change');
            $('#working_block').html('<option value="">Select Block</option>');
            
            // Update working area rules if on step 4
            if($('#step4').hasClass('active')) {
                updateWorkingAreaRules();
            }
        });
        
        // Member type change handler
        $('#member_type_id').off('change').on('change', function() {
            console.log("Member type changed to:", $(this).val());
            if($('#step4').hasClass('active')) {
                updateWorkingAreaRules();
            }
        });
        
        // Form submission
        $('#memberForm').on('submit', function(e) {
            e.preventDefault();
            console.log("Form submitted");
            
            // Set working area values
            const stateVal = $('#working_state').val();
            const districtVal = $('#working_district').val();
            const blockVal = $('#working_block').val();
            
            $('#working_state_id').val(stateVal || '');
            
            // Convert arrays to comma-separated strings
            if(districtVal && Array.isArray(districtVal)) {
                $('#working_district_ids').val(districtVal.join(','));
            } else {
                $('#working_district_ids').val(districtVal || '');
            }
            
            // Handle block values
            if(blockVal && Array.isArray(blockVal)) {
                $('#working_block_ids').val(blockVal.join(','));
            } else {
                $('#working_block_ids').val(blockVal || '');
            }
            
            // Debug: Log values
            console.log("Working State ID:", $('#working_state_id').val());
            console.log("Working District IDs:", $('#working_district_ids').val());
            console.log("Working Block IDs:", $('#working_block_ids').val());
            
            // Validate all steps before submission
            let allStepsValid = true;
            for(let i = 1; i <= totalSteps; i++) {
                if(!validateStep(i)) {
                    allStepsValid = false;
                    showStep(i); // Go to the step with error
                    break;
                }
            }
            
            if(!allStepsValid) {
                Swal.fire('Error', 'Please fill all required fields correctly.', 'error');
                return false;
            }
            
            // Additional validation for Block Coordinator
            const postApplied = $('#post_applied').val();
            if(postApplied === "Block Coordinator") {
                const blockIds = $('#working_block_ids').val();
                const blockArray = blockIds ? blockIds.split(',') : [];
                if(blockArray.length === 0 || blockArray.length > 5) {
                    Swal.fire('Error', 'Block Coordinator must select 1 to 5 blocks', 'error');
                    showStep(4);
                    $('#working_block').addClass('is-invalid');
                    return false;
                }
            }
            
            // Submit form via AJAX
            var formData = new FormData(this);
            
            // Show loading
            Swal.fire({
                title: 'Processing...',
                text: 'Please wait while we save the member information',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    Swal.close();
                    console.log("Raw Response:", response);
                    try {
                        var result = JSON.parse(response);
                        if(result.status == 'success') {
                            Swal.fire({
                                title: 'Success!',
                                text: result.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                $('#createMemberModal').modal('hide');
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', result.message, 'error');
                        }
                    } catch (error) {
                        console.error("JSON parse error:", error);
                        Swal.fire('Error', 'Server returned invalid response. Check console for details.', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    Swal.close();
                    console.error("AJAX error:", error);
                    console.error("Status:", status);
                    console.error("Response:", xhr.responseText);
                    Swal.fire('Error', 'An error occurred. Check console for details.', 'error');
                }
            });
        });
        
        // Drag and drop for photo upload
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('passport_photo');
        const photoPreview = document.getElementById('photoPreview');
        
        if(dropZone && fileInput) {
            // Remove existing event listeners
            const newDropZone = dropZone.cloneNode(true);
            dropZone.parentNode.replaceChild(newDropZone, dropZone);
            
            // Get new references
            const updatedDropZone = document.getElementById('dropZone');
            const updatedFileInput = document.getElementById('passport_photo');
            const updatedPhotoPreview = document.getElementById('photoPreview');
            
            updatedDropZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                updatedDropZone.classList.add('dragover');
            });
            
            updatedDropZone.addEventListener('dragleave', () => {
                updatedDropZone.classList.remove('dragover');
            });
            
            updatedDropZone.addEventListener('drop', (e) => {
                e.preventDefault();
                updatedDropZone.classList.remove('dragover');
                
                const files = e.dataTransfer.files;
                if(files.length > 0) {
                    updatedFileInput.files = files;
                    previewImage(files[0]);
                }
            });
            
            updatedFileInput.addEventListener('change', (e) => {
                if(e.target.files.length > 0) {
                    previewImage(e.target.files[0]);
                }
            });
            
            function previewImage(file) {
                if(file.type.startsWith('image/')) {
                    // Check file size (max 2MB)
                    if(file.size > 2 * 1024 * 1024) {
                        Swal.fire('Error', 'File size must be less than 2MB', 'error');
                        updatedFileInput.value = '';
                        updatedPhotoPreview.innerHTML = '';
                        return;
                    }
                    
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        updatedPhotoPreview.innerHTML = `
                            <img src="${e.target.result}" class="preview-image" alt="Preview">
                            <p class="text-success mt-2">${file.name} (${(file.size/1024).toFixed(2)} KB)</p>
                        `;
                    };
                    reader.readAsDataURL(file);
                } else {
                    Swal.fire('Error', 'Please upload an image file (JPG, PNG, GIF)', 'error');
                    updatedFileInput.value = '';
                    updatedPhotoPreview.innerHTML = '';
                }
            }
        }
        
        // Show first step
        showStep(1);
    }
    
    // Toggle password visibility
    function togglePassword() {
        const passwordField = document.getElementById('passwordField');
        if (passwordField) {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            
            // Toggle icon
            const icon = event.currentTarget.querySelector('i');
            if(icon) {
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            }
        }
    }
    
    // Fix for Bootstrap modal focus issue
    $(document).on('shown.bs.modal', function(e) {
        $(e.target).removeAttr('aria-hidden');
    });
    
    $(document).on('hidden.bs.modal', function(e) {
        $(e.target).attr('aria-hidden', 'true');
    });
    </script>
</body>
</html>