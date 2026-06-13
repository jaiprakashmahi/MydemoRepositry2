<?php
include 'conn.php';

// Initialize filter variables
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$type_filter = isset($_GET['member_type']) ? $_GET['member_type'] : '';
$search_filter = isset($_GET['search']) ? $_GET['search'] : '';

// Build the query with filters
$query = "SELECT mi.*, mt.type_name, mwa.state_id, mwa.district_ids, mwa.block_ids
          FROM member_information mi
          LEFT JOIN member_types mt ON mi.member_type_id = mt.id
          LEFT JOIN member_working_area mwa ON mi.id = mwa.member_id
          WHERE 1=1";

if (!empty($status_filter)) {
    $query .= " AND mi.member_status = '$status_filter'";
}

if (!empty($type_filter)) {
    $query .= " AND mi.member_type_id = $type_filter";
}

if (!empty($search_filter)) {
    $query .= " AND (mi.applicant_name LIKE '%$search_filter%' 
                     OR mi.father_name LIKE '%$search_filter%' 
                     OR mi.mobile_number LIKE '%$search_filter%' 
                     OR mi.email_id LIKE '%$search_filter%')";
}

$query .= " ORDER BY mi.created_at DESC";
$result = mysqli_query($conn, $query);

// Get member types for filter dropdown
$type_query = "SELECT * FROM member_types ORDER BY type_name";
$type_result = mysqli_query($conn, $type_query);
$member_types = [];
while($type = mysqli_fetch_assoc($type_result)) {
    $member_types[] = $type;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="DexignLab">
    <meta name="robots" content="">
    <meta name="keywords" content="school, school admin, education, academy, admin dashboard, college, college management, education management, institute, school management, school management system, student management, teacher management, university, university management">
    <meta name="description" content="Discover Sharnay - the ultimate admin dashboard. Specially designed for professionals, and for business. Sharnay provides advanced features and an easy-to-use interface for creating a top-quality website with School and Education Dashboard">
    <meta property="og:title" content="Sharnay : Admin Dashboard">
    <meta property="og:description" content="Sharnay - the ultimate admin dashboard. Specially designed for professionals, and for business. Sharnay provides advanced features and an easy-to-use interface for creating a top-quality website with School and Education Dashboard">
    <meta property="og:image" content="social-image.html">
    <meta name="format-detection" content="telephone=no">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sharnay : Member Management</title>

    <!-- FAVICONS ICON -->
    <link rel="shortcut icon" type="image/png" href="images/favicon.png">
    <link href="vendor/wow-master/css/libs/animate.css" rel="stylesheet">
    <link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="vendor/bootstrap-datepicker-master/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <link href="vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Style css -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/admin-standard.css" rel="stylesheet">
    
    <style>
        /* Members Table Styles */
        .member-photo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #dee2e6;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .member-photo.bg-secondary {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .action-buttons .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .action-buttons .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .action-buttons .btn i {
            font-size: 14px;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.04);
            transform: scale(1.001);
            transition: all 0.2s;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
            border-top: none;
            padding: 15px 12px;
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
            padding: 12px;
        }

        /* Filter Section */
        .filter-section {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid #e9ecef;
        }

        .filter-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .filter-header h6 {
            margin: 0;
            color: #2c3e50;
            font-weight: 600;
        }

        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            font-weight: 500;
            margin-bottom: 6px;
            color: #495057;
            font-size: 14px;
            display: block;
        }

        .filter-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #e9ecef;
        }

        .btn-filter {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 8px 20px;
            border-radius: 6px;
            font-weight: 500;
        }

        .btn-reset {
            background: #6c757d;
            border: none;
            color: white;
            padding: 8px 20px;
            border-radius: 6px;
            font-weight: 500;
        }

        .btn-filter:hover, .btn-reset:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .filter-group {
                min-width: 100%;
            }
            
            .action-buttons {
                justify-content: center;
            }
            
            .table-responsive {
                border: 1px solid #dee2e6;
                border-radius: 8px;
                overflow: hidden;
            }
        }

        /* Badge styles */
        .badge {
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 500;
            border-radius: 20px;
            letter-spacing: 0.3px;
        }

        .badge.bg-success {
            background-color: #28a745 !important;
        }

        .badge.bg-danger {
            background-color: #dc3545 !important;
        }

        .badge.bg-info {
            background-color: #17a2b8 !important;
        }

        /* Search box enhancement */
        .search-box {
            position: relative;
            max-width: 300px;
        }

        .search-box .form-control {
            padding-left: 40px;
            border-radius: 20px;
            border: 1px solid #ddd;
            height: 40px;
        }

        .search-box .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }

        /* Add member button */
        .btn-add-member {
            background: linear-gradient(135deg, #00b09b 0%, #96c93d 100%);
            border: none;
            color: white;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-add-member:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 176, 155, 0.3);
            color: white;
        }

        /* Card header enhancement */
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 20px 25px !important;
        }

        .card-header h5 {
            margin: 0;
            font-weight: 600;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-header h5 i {
            font-size: 20px;
        }

        /* Status indicator */
        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
            animation: pulse 2s infinite;
        }

        .status-active {
            background-color: #28a745;
        }

        .status-inactive {
            background-color: #dc3545;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 60px;
            margin-bottom: 20px;
            color: #dee2e6;
        }

        .empty-state h5 {
            color: #495057;
            margin-bottom: 10px;
        }

        /* Working area styles */
        .working-area {
            max-width: 200px;
            word-wrap: break-word;
        }

        .working-area small {
            font-size: 11px;
            color: #6c757d;
            line-height: 1.4;
        }

        /* Table striped effect */
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0,0,0,.02);
        }

        /* Card styles */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .card-body {
            padding: 25px;
        }

        /* No results message */
        .no-results {
            text-align: center;
            padding: 40px;
            background: #f8f9fa;
            border-radius: 8px;
            margin: 20px 0;
        }

        .no-results i {
            font-size: 50px;
            color: #adb5bd;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <!--**********************************
        Main wrapper start
    ***********************************-->
    <div id="main-wrapper">
        <?php include 'menu.php'; ?>
        
        <!-- Content Body Start -->
        <div class="content-body">
            <div class="container-fluid">
                <!-- Page Heading -->
                <div class="row page-titles">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>Member Management</h4>
                            <p class="mb-0">Manage all members and their information</p>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">All Members</a></li>
                        </ol>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="filter-section">
                    <div class="filter-header">
                        <h6><i class="fas fa-filter me-2"></i>Filter Members</h6>
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <form method="GET" action="" class="d-flex">
                                <input type="text" name="search" class="form-control" placeholder="Search by name, mobile, email..." value="<?php echo htmlspecialchars($search_filter); ?>">
                            </form>
                        </div>
                    </div>
                    
                    <form method="GET" action="">
                        <div class="filter-row">
                            <div class="filter-group">
                                <label for="status"><i class="fas fa-circle me-1"></i>Status</label>
                                <select name="status" id="status" class="form-control form-control-sm">
                                    <option value="">All Status</option>
                                    <option value="Active" <?php echo ($status_filter == 'Active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="Inactive" <?php echo ($status_filter == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label for="member_type"><i class="fas fa-users me-1"></i>Member Type</label>
                                <select name="member_type" id="member_type" class="form-control form-control-sm">
                                    <option value="">All Types</option>
                                    <?php foreach($member_types as $type): ?>
                                        <option value="<?php echo $type['id']; ?>" <?php echo ($type_filter == $type['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($type['type_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="filter-actions">
                            <button type="submit" class="btn btn-filter">
                                <i class="fas fa-search me-1"></i> Apply Filters
                            </button>
                            <a href="all-members.php" class="btn btn-reset">
                                <i class="fas fa-redo me-1"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Members Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-users me-2"></i>All Members</h5>
                                <a href="create-member.php" class="btn-add-member">
                                    <i class="fas fa-plus-circle"></i> Add New Member
                                </a>
                            </div>
                            <div class="card-body">
                                <?php if(mysqli_num_rows($result) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered table-striped" id="membersTable">
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
                                                   
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $counter = 1;
                                                while($row = mysqli_fetch_assoc($result)):
                                                    // Get working area details
                                                    $working_area = '';
                                                    if($row['state_id']) {
                                                        $state_query = "SELECT name FROM states WHERE id = {$row['state_id']}";
                                                        $state_result = mysqli_query($conn, $state_query);
                                                        $state = mysqli_fetch_assoc($state_result);
                                                        $working_area .= '<strong>' . htmlspecialchars($state['name']) . '</strong>';
                                                    }
                                                    
                                                    if($row['district_ids']) {
                                                        $district_ids = explode(',', $row['district_ids']);
                                                        $district_names = [];
                                                        foreach($district_ids as $district_id) {
                                                            $district_query = "SELECT name FROM districts WHERE id = $district_id";
                                                            $district_result = mysqli_query($conn, $district_query);
                                                            if($district = mysqli_fetch_assoc($district_result)) {
                                                                $district_names[] = htmlspecialchars($district['name']);
                                                            }
                                                        }
                                                        if(!empty($district_names)) {
                                                            $working_area .= '<br><small>Districts: ' . implode(', ', $district_names) . '</small>';
                                                        }
                                                    }
                                                    
                                                    if($row['block_ids']) {
                                                        $block_ids = explode(',', $row['block_ids']);
                                                        $block_names = [];
                                                        foreach($block_ids as $block_id) {
                                                            $block_query = "SELECT name FROM blocks WHERE id = $block_id";
                                                            $block_result = mysqli_query($conn, $block_query);
                                                            if($block = mysqli_fetch_assoc($block_result)) {
                                                                $block_names[] = htmlspecialchars($block['name']);
                                                            }
                                                        }
                                                        if(!empty($block_names)) {
                                                            $working_area .= '<br><small>Blocks: ' . implode(', ', $block_names) . '</small>';
                                                        }
                                                    }
                                                    
                                                    $status_class = $row['member_status'] == 'Active' ? 'bg-success' : 'bg-danger';
                                                    $status_text = $row['member_status'] == 'Active' ? 'Active' : 'Inactive';
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $counter; ?></td>
                                                        <td>
                                                            <?php if(!empty($row['passport_photo'])): ?>
                                                                <img src="<?php echo htmlspecialchars($row['passport_photo']); ?>" class="member-photo" alt="Member Photo">
                                                            <?php else: ?>
                                                                <div class="member-photo bg-secondary d-flex align-items-center justify-content-center text-white">
                                                                    <i class="fas fa-user"></i>
                                                                </div>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <strong><?php echo htmlspecialchars($row['applicant_name']); ?></strong><br>
                                                            <small class="text-muted"><?php echo htmlspecialchars($row['father_name']); ?></small>
                                                        </td>
                                                        <td>
                                                            <i class="fas fa-phone me-1 text-muted"></i>
                                                            <?php echo htmlspecialchars($row['mobile_number']); ?>
                                                        </td>
                                                        <td>
                                                            <i class="fas fa-envelope me-1 text-muted"></i>
                                                            <?php echo htmlspecialchars($row['email_id']); ?>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-info">
                                                                <i class="fas fa-tag me-1"></i>
                                                                <?php echo htmlspecialchars($row['type_name']); ?>
                                                            </span>
                                                        </td>
                                                        <td class="working-area">
                                                            <small><?php echo $working_area ?: 'Not specified'; ?></small>
                                                        </td>
                                                        <td>
                                                            <span class="badge <?php echo $status_class; ?>">
                                                                <i class="fas fa-circle status-indicator status-<?php echo strtolower($status_text); ?>"></i>
                                                                <?php echo $status_text; ?>
                                                            </span>
                                                        </td>
                                                     
                                                    </tr>
                                                    <?php
                                                    $counter++;
                                                endwhile;
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="no-results">
                                        <i class="fas fa-users-slash"></i>
                                        <h5>No Members Found</h5>
                                        <p class="text-muted mb-4"><?php echo !empty($search_filter) ? 'No members match your search criteria.' : 'No members have been added yet.'; ?></p>
                                        <a href="create-member.php" class="btn-add-member">
                                            <i class="fas fa-plus-circle"></i> Add Your First Member
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Content Body End -->
    </div>

    <!-- Required vendors -->
    <script src="vendor/global/global.min.js"></script>
    <script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="vendor/bootstrap-datepicker-master/js/bootstrap-datepicker.min.js"></script>
    <script src="vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="js/custom.min.js"></script>
    <script src="js/dlabnav-init.js"></script>
    <script src="js/admin-standard.js"></script>
    <script src="js/demo.js"></script>
    <script src="js/styleSwitcher.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#membersTable').DataTable({
                "pageLength": 10,
                "order": [[0, 'desc']],
                "language": {
                    "search": "Search members:",
                    "lengthMenu": "Show _MENU_ entries",
                    "info": "Showing _START_ to _END_ of _TOTAL_ members",
                    "paginate": {
                        "previous": "<i class='fas fa-chevron-left'></i>",
                        "next": "<i class='fas fa-chevron-right'></i>"
                    }
                },
                "drawCallback": function() {
                    $('.dataTables_paginate > .pagination').addClass('pagination-sm');
                }
            });

            // View Member
            $('.view-member').click(function() {
                var memberId = $(this).data('id');
                window.location.href = 'ajax/view_member.php?id=' + memberId;
            });

            // Edit Member
            $('.edit-member').click(function() {
                var memberId = $(this).data('id');
                window.location.href = 'edit-member.php?id=' + memberId;
            });

            // Delete Member
            $('.delete-member').click(function() {
                var memberId = $(this).data('id');
                if(confirm('Are you sure you want to delete this member?')) {
                    $.ajax({
                        url: 'delete-member.php',
                        type: 'POST',
                        data: { id: memberId },
                        success: function(response) {
                            var result = JSON.parse(response);
                            if(result.success) {
                                alert('Member deleted successfully!');
                                location.reload();
                            } else {
                                alert('Error deleting member: ' + result.message);
                            }
                        },
                        error: function() {
                            alert('Error deleting member. Please try again.');
                        }
                    });
                }
            });

            // Real-time search
            $('input[name="search"]').on('keyup', function(e) {
                if(e.keyCode === 13) { // Enter key
                    $(this).closest('form').submit();
                }
            });

            // Date pickers (if any)
            $(".datepicker").datepicker({ 
                autoclose: true, 
                todayHighlight: true,
                format: 'yyyy-mm-dd'
            });

            // Tooltips
            $('[title]').tooltip({
                placement: 'top',
                trigger: 'hover'
            });
        });
    </script>
</body>
</html>