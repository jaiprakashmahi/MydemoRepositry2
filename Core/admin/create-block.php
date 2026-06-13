<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'conn.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['block_name'])) {
    $district_id = $_POST['district_id'];
    $block_name = trim($_POST['block_name']);
    
    // Validate inputs
    if (empty($district_id) || empty($block_name)) {
        $error = "Please fill all required fields!";
    } else {
        // Check if block already exists in this district
        $check_sql = "SELECT id FROM blocks WHERE district_id = ? AND name = ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "is", $district_id, $block_name);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);
        
        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $error = "Block already exists in this district!";
            mysqli_stmt_close($check_stmt);
        } else {
            mysqli_stmt_close($check_stmt);
            
            // Insert into database
            $sql = "INSERT INTO blocks (district_id, name) VALUES (?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "is", $district_id, $block_name);
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Block created successfully!";
                    // Reset form
                    $_POST = array();
                } else {
                    $error = "Error creating block: " . mysqli_error($conn);
                }
                mysqli_stmt_close($stmt);
            } else {
                $error = "Database error: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">    
<head>
    <meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="author" content="DexignLab" >
	<meta name="robots" content="" >
	<meta name="keywords" content="school, school admin, education, academy, admin dashboard, college, college management, education management, institute, school management, school management system, student management, teacher management, university, university management" >
	<meta name="description" content="Discover Sharnay - the ultimate admin dashboard. Specially designed for professionals, and for business. Sharnay provides advanced features and an easy-to-use interface for creating a top-quality website with School and Education Dashboard" >
	<meta property="og:title" content="Sharnay : Admin Dashboard" >
	<meta property="og:description" content="Sharnay - the ultimate admin dashboard. Specially designed for professionals, and for business. Sharnay provides advanced features and an easy-to-use interface for creating a top-quality website with School and Education Dashboard">
	<meta property="og:image" content="social-image.html" >
	<meta name="format-detection" content="telephone=no">

	<!-- Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- Page Title Here -->
	<title>Create Block - Sharnay Dashboard</title>

    <!-- FAVICONS ICON -->
	<link rel="shortcut icon" type="image/png" href="images/favicon.png" >
	<link href="vendor/wow-master/css/libs/animate.css" rel="stylesheet">
	<link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
	<link href="vendor/bootstrap-datepicker-master/css/bootstrap-datepicker.min.css" rel="stylesheet">
	<link href="vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/admin-standard.css" rel="stylesheet">
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .card {
            border: none;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px 8px 0 0 !important;
        }
        
        .card-header h4 {
            margin: 0;
            font-weight: 600;
            font-size: 18px;
        }
        
        .form-control {
            border-radius: 6px;
            border: 1px solid #e0e0e0;
            padding: 10px 15px;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-back {
            background: #6c757d;
            border: none;
            color: white;
            padding: 12px 25px;
            border-radius: 6px;
            font-weight: 500;
        }
        
        .btn-back:hover {
            background: #5a6268;
            color: white;
        }
        
        .alert {
            border-radius: 8px;
            border: none;
            padding: 15px 20px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .required:after {
            content: " *";
            color: #dc3545;
        }
        
        .form-label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 8px;
        }
        
        .info-text {
            color: #6c757d;
            font-size: 12px;
            margin-top: 5px;
        }
        
        /* Select2 customization */
        .select2-container--default .select2-selection--single {
            height: 45px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 45px;
            padding-left: 15px;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 45px;
        }
        
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        /* Table styling */
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }
        
        /* Loading spinner */
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 10px;
            color: #6c757d;
        }
        
        .loading-spinner.active {
            display: block;
        }
    </style>
</head>
<body>
    <!--**********************************
        Main wrapper start
    ***********************************-->
    <div id="main-wrapper">
	
        <?php include 'menu.php'; ?>
		
        <!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <!-- row -->
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>Create New Block</h4>
                            <p class="mb-0">Add a new block under selected district</p>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">Create Block</a></li>
                        </ol>
                    </div>
                </div>
                
                <!-- Success/Error Messages -->
                <?php if(isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title"><i class="fas fa-cube me-2"></i> Block Information</h4>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="" id="blockForm">
                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <label for="working_state" class="form-label required">State</label>
                                            <select class="form-control select2-state" id="working_state" name="state_id" required>
                                                <option value="">Select State</option>
                                                <?php
                                                $query = "SELECT * FROM states ORDER BY name";
                                                $result = mysqli_query($conn, $query);
                                                while($row = mysqli_fetch_assoc($result)) {
                                                    $selected = (isset($_POST['state_id']) && $_POST['state_id'] == $row['id']) ? 'selected' : '';
                                                    echo "<option value='{$row['id']}' $selected>{$row['name']}</option>";
                                                }
                                                ?>
                                            </select>
                                            <div class="info-text">Select the state first to load districts</div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-4">
                                            <label for="working_district" class="form-label required">District</label>
                                            <select class="form-control select2-district" id="working_district" name="district_id" required>
                                                <option value="">Select District</option>
                                                <?php
                                                // If state is already selected, load its districts
                                                if(isset($_POST['state_id']) && !empty($_POST['state_id'])) {
                                                    $state_id = $_POST['state_id'];
                                                    $district_query = "SELECT * FROM districts WHERE state_id = $state_id ORDER BY name";
                                                    $district_result = mysqli_query($conn, $district_query);
                                                    while($district = mysqli_fetch_assoc($district_result)) {
                                                        $selected = (isset($_POST['district_id']) && $_POST['district_id'] == $district['id']) ? 'selected' : '';
                                                        echo "<option value='{$district['id']}' $selected>{$district['name']}</option>";
                                                    }
                                                }
                                                ?>
                                            </select>
                                            <div class="info-text">Select district where block belongs</div>
                                            <div class="loading-spinner" id="districtLoading">
                                                <i class="fas fa-spinner fa-spin me-2"></i>Loading districts...
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-12 mb-4">
                                            <label for="block_name" class="form-label required">Block Name</label>
                                            <input type="text" class="form-control" id="block_name" name="block_name" 
                                                   placeholder="Enter block name (e.g., Gopalganj Block, Siwan Block)" 
                                                   value="<?php echo isset($_POST['block_name']) ? htmlspecialchars($_POST['block_name']) : ''; ?>" 
                                                   required>
                                            <div class="info-text">Enter the full name of the block</div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="d-flex justify-content-between">
                                                <a href="dashboard.php" class="btn btn-back">
                                                    <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                                                </a>
                                                <button type="submit" class="btn btn-submit">
                                                    <i class="fas fa-save me-2"></i> Create Block
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title"><i class="fas fa-info-circle me-2"></i> Instructions</h4>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        Select State first
                                    </li>
                                    <li class="list-group-item">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        Then select District
                                    </li>
                                    <li class="list-group-item">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        Enter Block Name
                                    </li>
                                    <li class="list-group-item">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        Click Create Block
                                    </li>
                                    <li class="list-group-item">
                                        <i class="fas fa-lightbulb text-warning me-2"></i>
                                        Block names must be unique within a district
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title"><i class="fas fa-history me-2"></i> Recent Blocks</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th>Block</th>
                                                <th>District</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $recent_query = "SELECT b.*, d.name as district_name 
                                                            FROM blocks b 
                                                            JOIN districts d ON b.district_id = d.id 
                                                            ORDER BY b.id DESC LIMIT 5";
                                            $recent_result = mysqli_query($conn, $recent_query);
                                            
                                            if(mysqli_num_rows($recent_result) > 0) {
                                                while($recent = mysqli_fetch_assoc($recent_result)) {
                                                    echo "<tr>";
                                                    echo "<td><strong>{$recent['name']}</strong></td>";
                                                    echo "<td>{$recent['district_name']}</td>";
                                                    echo "</tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='2' class='text-center text-muted py-2'>No blocks added yet</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-center mt-2">
                                    <a href="all-blocks.php" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-list me-1"></i> View All Blocks
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--**********************************
            Content body end
        ***********************************-->
        
        <!--**********************************
            Footer start
        ***********************************-->
        <div class="footer">
            <div class="copyright">
                <p>Copyright © <script>document.write(new Date().getFullYear())</script> Sharnay Institute. All rights reserved.</p>
            </div>
        </div>
        <!--**********************************
            Footer end
        ***********************************-->
    </div>
    <!--**********************************
        Main wrapper end
    ***********************************-->

    <!-- Required vendors -->
    <script src="vendor/global/global.min.js"></script>
    <script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="vendor/bootstrap-datepicker-master/js/bootstrap-datepicker.min.js"></script>
    <script src="js/custom.min.js"></script>
    <script src="js/dlabnav-init.js"></script>
    <script src="js/admin-standard.js"></script>
    <script src="js/demo.js"></script>
    <script src="js/styleSwitcher.js"></script>
    
    <!-- Additional Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2-state').select2({
                placeholder: "Select State",
                allowClear: true
            });
            
            $('.select2-district').select2({
                placeholder: "Select District",
                allowClear: true
            });
            
            // Load districts when state changes
            $('#working_state').change(function() {
                var stateId = $(this).val();
                if(stateId) {
                    $('#districtLoading').addClass('active');
                    
                    $.ajax({
                        url: 'ajax/fetch_districts.php',
                        type: 'POST',
                        data: { state_id: stateId },
                        success: function(response) {
                            $('#working_district').html(response);
                            $('#working_district').trigger('change');
                            $('#districtLoading').removeClass('active');
                        },
                        error: function() {
                            $('#working_district').html('<option value="">Error loading districts</option>');
                            $('#working_district').trigger('change');
                            $('#districtLoading').removeClass('active');
                            console.error("Error loading districts");
                        }
                    });
                } else {
                    $('#working_district').html('<option value="">Select District</option>');
                    $('#working_district').trigger('change');
                }
            });
            
            // Form validation
            $('#blockForm').submit(function(e) {
                var state = $('#working_state').val();
                var district = $('#working_district').val();
                var blockName = $('#block_name').val().trim();
                
                if(!state) {
                    e.preventDefault();
                    alert('Please select a state');
                    $('#working_state').select2('open');
                    return false;
                }
                
                if(!district) {
                    e.preventDefault();
                    alert('Please select a district');
                    $('#working_district').select2('open');
                    return false;
                }
                
                if(!blockName) {
                    e.preventDefault();
                    alert('Please enter block name');
                    $('#block_name').focus();
                    return false;
                }
                
                return true;
            });
            
            // Auto-focus on block name field when district is selected
            $('#working_district').change(function() {
                if($(this).val()) {
                    setTimeout(function() {
                        $('#block_name').focus();
                    }, 300);
                }
            });
            
            // If state was previously selected (after form submission error), trigger change
            <?php if(isset($_POST['state_id']) && !empty($_POST['state_id'])): ?>
                $('#working_state').trigger('change');
            <?php endif; ?>
        });
    </script>
</body>
</html>