<?php

include 'conn.php';

// Check if admin is logged in (you should have your own admin session check)


// Get all centers with their wallet balances
$query = "SELECT 
            cd.id,
            cd.center_code,
            cd.center_name,
            cd.owner_name,
            cd.email,
            cd.phone,
            cd.state,
            cd.district,
            cd.payment_status,
            cd.center_status,
            COALESCE(cw.balance, 0) as wallet_balance,
            (SELECT COUNT(*) FROM wallet_transactions WHERE center_id = cd.id AND transaction_type = 'credit' AND status = 'success') as total_deposits,
            (SELECT COUNT(*) FROM wallet_transactions WHERE center_id = cd.id AND transaction_type = 'debit' AND status = 'success') as total_withdrawals
          FROM center_details cd
          LEFT JOIN center_wallet cw ON cd.id = cw.center_id
          ORDER BY cw.balance DESC, cd.center_name ASC";

$result = $conn->query($query);

// Calculate totals
$total_wallet_balance = 0;
$total_centers = 0;
$total_active = 0;
$total_inactive = 0;

while ($row = $result->fetch_assoc()) {
    $total_wallet_balance += $row['wallet_balance'];
    $total_centers++;
    if ($row['center_status'] == 'Active') {
        $total_active++;
    } else {
        $total_inactive++;
    }
}

// Reset pointer for main result
$result->data_seek(0);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Center Wallet Report - Admin Dashboard</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="DexignLab">
    <meta name="robots" content="">
    <meta name="keywords" content="">
    <meta name="description" content="">
    <meta property="og:title" content="">
    <meta property="og:description" content="">
    <meta property="og:image" content="social-image.html">
    <meta name="format-detection" content="telephone=no">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/png" href="images/favicon.png">
    
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="vendor/chartist/css/chartist.min.css">
    <link href="vendor/wow-master/css/libs/animate.css" rel="stylesheet">
    <link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="vendor/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet">
    
    <!-- Main CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/admin-standard.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        .wallet-summary-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .summary-box {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        
        .summary-box .icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .summary-box.green { border-left: 4px solid #28a745; }
        .summary-box.blue { border-left: 4px solid #007bff; }
        .summary-box.orange { border-left: 4px solid #fd7e14; }
        .summary-box.purple { border-left: 4px solid #6f42c1; }
        
        .wallet-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }
        
        .balance-high { color: #28a745; font-weight: bold; }
        .balance-medium { color: #ffc107; font-weight: bold; }
        .balance-low { color: #dc3545; font-weight: bold; }
        
        .action-buttons .btn {
            margin-right: 5px;
            margin-bottom: 5px;
        }
        
        @media print {
            body * {
                visibility: hidden;
            }
            #print-section, #print-section * {
                visibility: visible;
            }
            #print-section {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(0,0,0,.03);
        }
        
        .search-area {
            max-width: 300px;
        }
    </style>
</head>

<body>
    <!-- Preloader -->
    <div id="preloader">
        <div class="loader"></div>
    </div>

    <!-- Main Wrapper -->
    <div id="main-wrapper">
        <!-- Include Admin Menu -->
        <?php include 'menu.php'; ?>

        <!-- Content Body -->
        <div class="content-body">
            <div class="container-fluid">
                <!-- Page Title -->
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>Center Wallet Management</h4>
                            <p class="mb-0">View and manage all center wallet balances</p>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <button onclick="window.print()" class="btn btn-primary btn-sm no-print">
                            <i class="fas fa-print"></i> Print Report
                        </button>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row" id="print-section">
                    <div class="col-xl-12">
                        <div class="wallet-summary-card">
                            <div class="row">
                                <div class="col-md-8">
                                    <h3 class="text-white">Total Wallet Balance</h3>
                                    <h1 class="display-4 text-white">₹<?php echo number_format($total_wallet_balance, 2); ?></h1>
                                    <p class="text-white-50">Across all registered centers</p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <i class="fas fa-wallet fa-4x text-white-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Stats -->
                    <div class="col-xl-3 col-lg-6 col-sm-6">
                        <div class="summary-box green">
                            <div class="icon text-success">
                                <i class="fas fa-university"></i>
                            </div>
                            <h4 class="mb-1"><?php echo $total_centers; ?></h4>
                            <span>Total Centers</span>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-lg-6 col-sm-6">
                        <div class="summary-box blue">
                            <div class="icon text-primary">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h4 class="mb-1"><?php echo $total_active; ?></h4>
                            <span>Active Centers</span>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-lg-6 col-sm-6">
                        <div class="summary-box orange">
                            <div class="icon text-warning">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <h4 class="mb-1"><?php echo $total_inactive; ?></h4>
                            <span>Inactive Centers</span>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-lg-6 col-sm-6">
                        <div class="summary-box purple">
                            <div class="icon text-purple">
                                <i class="fas fa-rupee-sign"></i>
                            </div>
                            <h4 class="mb-1">₹<?php echo number_format($total_wallet_balance, 0); ?></h4>
                            <span>Total Balance</span>
                        </div>
                    </div>

                    <!-- Search and Filter -->
                    <div class="col-xl-12">
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="input-group search-area mb-3">
                                            <input type="text" class="form-control" id="searchInput" placeholder="Search centers by name, code, or owner...">
                                            <span class="input-group-text">
                                                <i class="fas fa-search"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <select class="form-control" id="statusFilter">
                                            <option value="">All Status</option>
                                            <option value="Active">Active Only</option>
                                            <option value="Inactive">Inactive Only</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Wallet Details Table -->
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Center Wallet Details</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped" id="walletTable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Center Code</th>
                                                <th>Center Name</th>
                                                <th>Owner</th>
                                                <th>Contact</th>
                                                <th>Location</th>
                                                <th>Wallet Balance</th>
                                                <th>Status</th>
                                                <th>Payment Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $counter = 1;
                                            if ($result->num_rows > 0):
                                                while ($row = $result->fetch_assoc()):
                                                    // Determine balance color
                                                    $balance_class = 'balance-medium';
                                                    if ($row['wallet_balance'] >= 10000) {
                                                        $balance_class = 'balance-high';
                                                    } elseif ($row['wallet_balance'] <= 1000) {
                                                        $balance_class = 'balance-low';
                                                    }
                                            ?>
                                            <tr>
                                                <td><?php echo $counter++; ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($row['center_code']); ?></strong>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($row['center_name']); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['owner_name']); ?></td>
                                                <td>
                                                    <div><?php echo htmlspecialchars($row['phone']); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($row['email']); ?></small>
                                                </td>
                                                <td>
                                                    <div><?php echo htmlspecialchars($row['district']); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($row['state']); ?></small>
                                                </td>
                                                <td>
                                                    <span class="<?php echo $balance_class; ?>">
                                                        ₹<?php echo number_format($row['wallet_balance'], 2); ?>
                                                    </span>
                                                    <div class="small text-muted">
                                                        D: <?php echo $row['total_deposits']; ?> | 
                                                        W: <?php echo $row['total_withdrawals']; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if ($row['center_status'] == 'Active'): ?>
                                                        <span class="badge badge-success wallet-badge">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger wallet-badge">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($row['payment_status'] == 'Paid'): ?>
                                                        <span class="badge badge-success wallet-badge">Paid</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-warning wallet-badge">Pending</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="action-buttons">
                                                    <a href="center_wallet_detail.php?center_id=<?php echo $row['id']; ?>" 
                                                       class="btn btn-sm btn-info" title="View Wallet Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="center_transactions.php?center_id=<?php echo $row['id']; ?>" 
                                                       class="btn btn-sm btn-primary" title="View Transactions">
                                                        <i class="fas fa-history"></i>
                                                    </a>
                                                    <a href="add_wallet_balance.php?center_id=<?php echo $row['id']; ?>" 
                                                       class="btn btn-sm btn-success" title="Add Balance">
                                                        <i class="fas fa-plus"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php
                                                endwhile;
                                            else:
                                            ?>
                                            <tr>
                                                <td colspan="10" class="text-center">No centers found with wallet information.</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Summary -->
                    <div class="col-xl-12">
                        <div class="row">
                            <div class="col-xl-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Top 5 Centers by Balance</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php
                                        // Get top centers by balance
                                        $top_query = "SELECT cd.center_name, cd.center_code, COALESCE(cw.balance, 0) as balance 
                                                     FROM center_details cd 
                                                     LEFT JOIN center_wallet cw ON cd.id = cw.center_id 
                                                     ORDER BY cw.balance DESC LIMIT 5";
                                        $top_result = $conn->query($top_query);
                                        
                                        if ($top_result->num_rows > 0):
                                            while ($top = $top_result->fetch_assoc()):
                                        ?>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($top['center_name']); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($top['center_code']); ?></small>
                                            </div>
                                            <div class="text-success font-weight-bold">
                                                ₹<?php echo number_format($top['balance'], 2); ?>
                                            </div>
                                        </div>
                                        <?php
                                            endwhile;
                                        else:
                                        ?>
                                        <p class="text-muted">No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-xl-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Recent Wallet Transactions</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php
                                        // Get recent transactions
                                        $recent_query = "SELECT wt.*, cd.center_name, cd.center_code 
                                                       FROM wallet_transactions wt 
                                                       JOIN center_details cd ON wt.center_id = cd.id 
                                                       ORDER BY wt.created_at DESC LIMIT 5";
                                        $recent_result = $conn->query($recent_query);
                                        
                                        if ($recent_result->num_rows > 0):
                                            while ($recent = $recent_result->fetch_assoc()):
                                                $badge_color = $recent['transaction_type'] == 'credit' ? 'success' : 'danger';
                                        ?>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($recent['center_name']); ?></h6>
                                                <small class="text-muted">
                                                    <?php echo date('d-m-Y H:i', strtotime($recent['created_at'])); ?> | 
                                                    <span class="badge badge-<?php echo $badge_color; ?>">
                                                        <?php echo ucfirst($recent['transaction_type']); ?>
                                                    </span>
                                                </small>
                                            </div>
                                            <div class="font-weight-bold text-<?php echo $badge_color; ?>">
                                                ₹<?php echo number_format($recent['amount'], 2); ?>
                                            </div>
                                        </div>
                                        <?php
                                            endwhile;
                                        else:
                                        ?>
                                        <p class="text-muted">No recent transactions</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <?php include 'admin_footer.php'; ?>
    </div>

    <!-- JavaScript Libraries -->
    <script src="vendor/global/global.min.js"></script>
    <script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    
    <!-- DataTables -->
    <script src="vendor/datatables/js/jquery.dataTables.min.js"></script>
    
    <!-- WOW Animation -->
    <script src="vendor/wow-master/dist/wow.min.js"></script>
    
    <!-- Custom Scripts -->
    <script src="js/custom.min.js"></script>
    <script src="js/dlabnav-init.js"></script>
    <script src="js/admin-standard.js"></script>
    
    <script>
    // Initialize DataTable
    $(document).ready(function() {
        // Simple search functionality
        $('#searchInput').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('#walletTable tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });
        
        // Status filter
        $('#statusFilter').on('change', function() {
            var status = $(this).val();
            if (status === '') {
                $('#walletTable tbody tr').show();
            } else {
                $('#walletTable tbody tr').each(function() {
                    var rowStatus = $(this).find('td:eq(7)').text().trim();
                    $(this).toggle(rowStatus === status);
                });
            }
        });
        
        // Initialize DataTable with custom search
        $('#walletTable').DataTable({
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            "dom": '<"top"f>rt<"bottom"lip><"clear">',
            "language": {
                "search": "Filter:",
                "lengthMenu": "Show _MENU_ entries"
            }
        });
        
        // Print functionality
        window.printReport = function() {
            var originalContents = document.body.innerHTML;
            var printContents = document.getElementById('print-section').innerHTML;
            
            document.body.innerHTML = 
                '<html><head><title>Center Wallet Report</title>' +
                '<style>' +
                'body { font-family: Arial, sans-serif; }' +
                '.table { width: 100%; border-collapse: collapse; }' +
                '.table th, .table td { border: 1px solid #ddd; padding: 8px; }' +
                '.table th { background-color: #f2f2f2; text-align: left; }' +
                '.summary-box { margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; }' +
                '.text-success { color: green; }' +
                '.text-danger { color: red; }' +
                '.text-warning { color: orange; }' +
                '</style>' +
                '</head><body>' +
                '<h2>Center Wallet Report</h2>' +
                '<p>Generated on: <?php echo date("d-m-Y H:i:s"); ?></p>' +
                printContents +
                '</body></html>';
            
            window.print();
            document.body.innerHTML = originalContents;
            location.reload();
        };
    });
    
    // Export to Excel function
    function exportToExcel() {
        let table = document.getElementById("walletTable");
        let rows = [];
        
        // Get table headers
        let headers = [];
        table.querySelectorAll("thead th").forEach(function(th) {
            headers.push(th.innerText);
        });
        rows.push(headers);
        
        // Get table rows data
        table.querySelectorAll("tbody tr").forEach(function(tr) {
            let row = [];
            tr.querySelectorAll("td").forEach(function(td) {
                row.push(td.innerText);
            });
            rows.push(row);
        });
        
        // Convert to CSV
        let csvContent = "data:text/csv;charset=utf-8,";
        rows.forEach(function(rowArray) {
            let row = rowArray.join(",");
            csvContent += row + "\r\n";
        });
        
        // Download
        let encodedUri = encodeURI(csvContent);
        let link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "center_wallet_report_<?php echo date('Y-m-d'); ?>.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    </script>
</body>
</html>