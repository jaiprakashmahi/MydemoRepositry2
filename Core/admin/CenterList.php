<?php
include 'conn.php';

// Note: Passwords in database should be hashed with PASSWORD_BCRYPT
// Example: password_hash($password, PASSWORD_BCRYPT)

$query = "SELECT * FROM center_details";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Center List - Welcome To Sharnay Institute Dashboard</title>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="DexignLab" >
    <meta name="robots" content="" >
    <meta name="keywords" content="" >
    <meta name="description" content="" >
    <meta property="og:title" content="" >
    <meta property="og:description" content="">
    <meta property="og:image" content="social-image.html" >
    <meta name="format-detection" content="telephone=no">

    <!-- Mobile Specific -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- FAVICONS ICON -->
    <link rel="shortcut icon" type="image/png" href="images/favicon.png" >
    <link rel="stylesheet" href="vendor/chartist/css/chartist.min.css">
    <link href="vendor/wow-master/css/libs/animate.css" rel="stylesheet">
    <link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    
    <link href="vendor/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet">
    
    <!-- Style css -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/admin-standard.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
    /* Password display with BCRYPT hash */
    .password-container {
        position: relative;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .password-hash {
        font-family: monospace;
        background: #2d2d2d;
        color: #00ff00;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 11px;
        max-width: 200px;
        overflow-x: auto;
        white-space: nowrap;
        border: 1px solid #444;
        letter-spacing: 0.5px;
    }
    
    .password-hash::-webkit-scrollbar {
        height: 3px;
    }
    
    .password-hash::-webkit-scrollbar-thumb {
        background: #667eea;
        border-radius: 3px;
    }
    
    .hash-info {
        font-size: 10px;
        color: #999;
        margin-top: 2px;
        display: block;
    }
    
    .badge-bcrypt {
        background: #6f42c1;
        color: white;
        font-size: 9px;
        padding: 2px 6px;
        border-radius: 10px;
        margin-left: 5px;
    }
    
    /* Password toggle buttons */
    .password-actions {
        display: flex;
        gap: 3px;
    }
    
    .hash-btn {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 14px;
        padding: 5px;
        border-radius: 4px;
        transition: all 0.3s;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .hash-btn:hover {
        background: rgba(102, 126, 234, 0.1);
    }
    
    .copy-hash {
        color: #4CAF50;
    }
    
    .copy-hash:hover {
        background: rgba(76, 175, 80, 0.1);
    }
    
    .view-hash {
        color: #17a2b8;
    }
    
    .view-hash:hover {
        background: rgba(23, 162, 184, 0.1);
    }
    
    /* Table cell styling */
    .table td {
        vertical-align: middle;
        padding: 12px 8px;
        font-size: 14px;
    }
    
    .table th {
        font-size: 13px;
        font-weight: 600;
        color: #555;
        white-space: nowrap;
        background: #f8f9fa;
    }
    
    /* Badge styles */
    .badge-status {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        display: inline-block;
    }
    
    .badge-active {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .badge-inactive {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .badge-paid {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .badge-pending {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }
    
    .badge-info {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }
    
    .badge-hash {
        background: #6f42c1;
        color: white;
        font-size: 10px;
        padding: 2px 8px;
        border-radius: 12px;
    }
    
    /* Image styling */
    .center-image {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #667eea;
    }
    
    .no-image {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
    }
    
    /* Action buttons */
    .action-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 3px;
        transition: all 0.3s;
        color: white;
        text-decoration: none;
    }
    
    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        color: white;
    }
    
    .btn-view {
        background: #17a2b8;
    }
    
    .btn-edit {
        background: #ffc107;
        color: #333;
    }
    
    .btn-edit:hover {
        color: #333;
    }
    
    .btn-delete {
        background: #dc3545;
    }
    
    /* Center code badge */
    .center-code-badge {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
    }
    
    /* Username badge */
    .username-badge {
        background: #e9ecef;
        color: #495057;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    
    /* Search area */
    .search-area {
        max-width: 350px;
    }
    
    .search-area input {
        border-radius: 30px 0 0 30px;
        border: 2px solid #e1e1e1;
        padding: 10px 20px;
    }
    
    .search-area .input-group-text {
        border-radius: 0 30px 30px 0;
        border: 2px solid #e1e1e1;
        border-left: none;
        background: white;
    }
    
    /* Modal for full hash view */
    .hash-modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.7);
    }
    
    .hash-modal-content {
        background: white;
        margin: 10% auto;
        padding: 25px;
        border-radius: 15px;
        width: 80%;
        max-width: 700px;
        position: relative;
    }
    
    .hash-modal-content h3 {
        color: #333;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #667eea;
    }
    
    .full-hash {
        background: #2d2d2d;
        color: #00ff00;
        padding: 20px;
        border-radius: 8px;
        font-family: monospace;
        font-size: 14px;
        word-break: break-all;
        margin: 15px 0;
        border: 1px solid #444;
    }
    
    .close-modal {
        position: absolute;
        right: 20px;
        top: 15px;
        font-size: 28px;
        cursor: pointer;
        color: #999;
    }
    
    .close-modal:hover {
        color: #333;
    }
    
    .hash-info-box {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 5px;
        font-size: 13px;
        color: #666;
    }
    
    .hash-info-box i {
        color: #6f42c1;
        margin-right: 5px;
    }
    
    @media print {
        .btn, .action-btn, .hash-btn, .form-check-input {
            display: none !important;
        }
    }
    </style>
    
    <script>
    // Copy BCRYPT hash to clipboard
    function copyHash(hash) {
        const textarea = document.createElement('textarea');
        textarea.value = hash;
        document.body.appendChild(textarea);
        textarea.select();
        
        try {
            document.execCommand('copy');
            
            // Show success feedback
            const btn = event.currentTarget;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i>';
            btn.style.color = '#4CAF50';
            
            setTimeout(() => {
                btn.innerHTML = originalHtml;
                btn.style.color = '';
            }, 2000);
        } catch (err) {
            alert('Failed to copy hash');
        }
        
        document.body.removeChild(textarea);
    }
    
    // View full hash in modal
    function viewFullHash(hash, centerName) {
        const modal = document.getElementById('hashModal');
        const hashDisplay = document.getElementById('fullHashDisplay');
        const centerNameDisplay = document.getElementById('modalCenterName');
        
        hashDisplay.textContent = hash;
        centerNameDisplay.textContent = centerName + ' - BCRYPT Hash';
        modal.style.display = 'block';
    }
    
    // Close modal
    function closeModal() {
        document.getElementById('hashModal').style.display = 'none';
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('hashModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
    
    // Verify if hash is BCRYPT format
    function isBCRYPT(hash) {
        // BCRYPT hashes start with $2y$ or $2a$ and are 60 characters long
        return hash.startsWith('$2y$') || hash.startsWith('$2a$') || hash.startsWith('$2b$');
    }
    </script>
    
</head>
<body>

    <!-- Hash View Modal -->
    <div id="hashModal" class="hash-modal">
        <div class="hash-modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h3><i class="fas fa-key"></i> <span id="modalCenterName">BCRYPT Password Hash</span></h3>
            <div class="full-hash" id="fullHashDisplay"></div>
            <div class="hash-info-box">
                <i class="fas fa-info-circle"></i>
                <strong>BCRYPT Hash Information:</strong>
                <ul style="margin-top: 8px; margin-left: 20px;">
                    <li>Algorithm: BCRYPT (PASSWORD_BCRYPT)</li>
                    <li>Hash Length: 60 characters</li>
                    <li>Format: $2y$[cost]$[salt][hash]</li>
                    <li>Cost Factor: 10 (default)</li>
                </ul>
            </div>
            <div style="text-align: right; margin-top: 20px;">
                <button class="btn btn-primary" onclick="copyHash(document.getElementById('fullHashDisplay').textContent)">
                    <i class="fas fa-copy"></i> Copy Hash
                </button>
            </div>
        </div>
    </div>

    <!-- Preloader -->
    <div id="preloader">
        <div class="loader"></div>
    </div>

    <!-- Main wrapper -->
    <div id="main-wrapper">
    
        <?php include 'menu.php'; ?>
        
        <!-- Content body -->
        <div class="content-body">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="page-title flex-wrap">
                                    <div class="input-group search-area mb-md-0 mb-3">
                                        <input type="text" class="form-control" placeholder="Search centers..." id="searchInput">
                                        <span class="input-group-text">
                                            <i class="fas fa-search" style="color: #01A3FF;"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-success me-2" onclick="exportToExcel()">
                                            <i class="fas fa-file-excel"></i> Export
                                        </button>
                                        <button type="button" class="btn btn-warning me-2" onclick="window.print()">
                                            <i class="fas fa-print"></i> Print
                                        </button>
                                        <button type="button" class="btn btn-primary">
                                            <a href="create-center.php" style="color: white; text-decoration: none;">
                                                <i class="fas fa-plus-circle"></i> New Center
                                            </a>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Table -->
                            <div class="col-xl-12 wow fadeInUp" data-wow-delay="1.5s" id="print-section">
                                <div class="table-responsive full-data">
                                    <table class="table display dataTable" id="centerTable">
                                        <thead>
                                            <tr>
                                                <th width="40">
                                                    <input type="checkbox" class="form-check-input" id="checkAll">
                                                </th>
                                                <th>Center Code</th>
                                                <th>Photo</th>
                                                <th>Center Name</th>
                                                <th>Owner Name</th>
                                                <th>Username</th>
                                                <th>Password (BCRYPT Hash)</th>
                                                <th>Email</th>
                                                <th>Created</th>
                                                <th>Phone</th>
                                                <th>State</th>
                                                <th>District</th>
                                                <th>Block</th>
                                                <th>Pincode</th>
                                                <th>Reg. Amount</th>
                                                <th>Pay Mode</th>
                                                <th>Pay Status</th>
                                                <th>Center Status</th>
                                                <th width="120">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if ($result->num_rows > 0):
                                                while ($row = $result->fetch_assoc()):
                                                    $password_hash = $row['password']; // This should be BCRYPT hash
                                                    
                                                    // Check if it's a BCRYPT hash
                                                    $is_bcrypt = (strpos($password_hash, '$2y$') === 0 || 
                                                                 strpos($password_hash, '$2a$') === 0 || 
                                                                 strpos($password_hash, '$2b$') === 0);
                                                    
                                                    // Display first part of hash for preview
                                                    $hash_preview = substr($password_hash, 0, 30) . '...';
                                            ?>
                                            
                                            <tr>
                                                <td>
                                                    <input type="checkbox" class="form-check-input" name="selected[]" value="<?= $row['id']; ?>">
                                                </td>
                                                <td>
                                                    <span class="center-code-badge"><?= htmlspecialchars($row['center_code']) ?></span>
                                                </td>
                                                <td>
                                                    <?php if (!empty($row['photo'])): ?>
                                                        <img src="uploads/<?= htmlspecialchars($row['photo']) ?>" alt="Photo" class="center-image">
                                                    <?php else: ?>
                                                        <div class="no-image">
                                                            <i class="fas fa-building"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><?= htmlspecialchars($row['center_name']) ?></strong>
                                                </td>
                                                <td><?= htmlspecialchars($row['owner_name'] ?? '-') ?></td>
                                                <td>
                                                    <span class="username-badge">
                                                        <i class="fas fa-user me-1"></i><?= htmlspecialchars($row['username']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="password-container">
                                                        <div class="password-hash" title="BCRYPT Hash (<?= strlen($password_hash) ?> chars)">
                                                            <?= htmlspecialchars($hash_preview) ?>
                                                            <?php if ($is_bcrypt): ?>
                                                                <span class="badge-bcrypt">BCRYPT</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="password-actions">
                                                            <button class="hash-btn copy-hash" onclick="copyHash('<?= htmlspecialchars(addslashes($password_hash)) ?>')" title="Copy Full Hash">
                                                                <i class="fas fa-copy"></i>
                                                            </button>
                                                            <button class="hash-btn view-hash" onclick="viewFullHash('<?= htmlspecialchars(addslashes($password_hash)) ?>', '<?= htmlspecialchars($row['center_name']) ?>')" title="View Full Hash">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <span class="hash-info">
                                                        <i class="fas fa-hashtag"></i> 
                                                        Length: <?= strlen($password_hash) ?> chars | 
                                                        Algorithm: BCRYPT
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($row['email']) ?></td>
                                                <td><?= date('d-m-Y', strtotime($row['date_of_create'])) ?></td>
                                                <td><?= htmlspecialchars($row['phone']) ?></td>
                                                <td><?= htmlspecialchars($row['state']) ?></td>
                                                <td><?= htmlspecialchars($row['district']) ?></td>
                                                <td><?= htmlspecialchars($row['block'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($row['pincode']) ?></td>
                                                <td>₹<?= number_format($row['reg_amount'] ?? 0, 2) ?></td>
                                                <td>
                                                    <?php 
                                                    $payment_mode = $row['payment_mode'] ?? '-';
                                                    $mode_class = 'badge-info';
                                                    ?>
                                                    <span class="badge-status <?= $mode_class ?>"><?= htmlspecialchars($payment_mode) ?></span>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $payment_status = $row['payment_status'] ?? 'Pending';
                                                    $status_class = ($payment_status == 'Paid') ? 'badge-paid' : 'badge-pending';
                                                    ?>
                                                    <span class="badge-status <?= $status_class ?>">
                                                        <?= htmlspecialchars($payment_status) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $center_status = $row['center_status'] ?? 'Active';
                                                    $status_class = ($center_status == 'Active') ? 'badge-active' : 'badge-inactive';
                                                    ?>
                                                    <span class="badge-status <?= $status_class ?>">
                                                        <?= htmlspecialchars($center_status) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="view_center.php?id=<?= $row['id'] ?>" class="action-btn btn-view" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="edit_center.php?id=<?= $row['id'] ?>" class="action-btn btn-edit" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="delete_center.php?id=<?= $row['id'] ?>" class="action-btn btn-delete" 
                                                       title="Delete" onclick="return confirm('Are you sure to delete this center?');">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php
                                                endwhile;
                                            else:
                                            ?>
                                            <tr>
                                                <td colspan="19" style="text-align: center; padding: 50px;">
                                                    <i class="fas fa-building" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                                                    <br>
                                                    <h5 style="color: #666;">No centers found</h5>
                                                    <p style="color: #999;">Click the "New Center" button to add your first center</p>
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php include 'footer.php'; ?>
    </div>

    <!-- Scripts -->
    <script src="vendor/global/global.min.js"></script>
    <script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="js/plugins-init/datatables.init.js"></script>
    <script src="vendor/wow-master/dist/wow.min.js"></script>
    <script src="js/custom.min.js"></script>
    <script src="js/dlabnav-init.js"></script>
    <script src="js/admin-standard.js"></script>
    
    <script>
    // Check All functionality
    document.getElementById('checkAll')?.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('input[name="selected[]"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Search functionality
    document.getElementById('searchInput')?.addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        const tableRows = document.querySelectorAll('#centerTable tbody tr');
        
        tableRows.forEach(row => {
            if (row.cells.length > 1) {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            }
        });
    });

    // Export to Excel
    function exportToExcel() {
        const table = document.getElementById('centerTable');
        const rows = table.querySelectorAll('tr');
        const csv = [];
        
        // Get headers
        const headers = [];
        table.querySelectorAll('thead th').forEach((th, index) => {
            if (index !== 0 && index !== 18) {
                headers.push('"' + th.textContent.trim() + '"');
            }
        });
        csv.push(headers.join(','));
        
        // Get data rows
        rows.forEach(row => {
            if (row.parentElement.tagName === 'TBODY' && row.cells.length > 1) {
                const rowData = [];
                const cells = row.querySelectorAll('td');
                cells.forEach((cell, index) => {
                    if (index !== 0 && index !== 18) {
                        let cellText = cell.textContent.trim();
                        // Clean up password hash display
                        if (index === 6) { // Password column
                            // Get the full hash from the data attribute or element
                            const hashElement = cell.querySelector('.password-hash');
                            if (hashElement) {
                                cellText = hashElement.textContent.replace('BCRYPT', '').trim();
                            }
                        }
                        cellText = cellText.replace(/\s+/g, ' ');
                        rowData.push('"' + cellText + '"');
                    }
                });
                csv.push(rowData.join(','));
            }
        });
        
        // Download CSV
        const csvContent = csv.join('\n');
        const blob = new Blob(["\uFEFF" + csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'center_list_' + new Date().toISOString().slice(0,10) + '.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }
    </script>
    
</body>
</html>