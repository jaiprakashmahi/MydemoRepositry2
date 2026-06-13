<?php
include 'conn.php';

if (isset($_GET['student_code'])) {
    $student_code = mysqli_real_escape_string($conn, $_GET['student_code']);
    
    // Query from onlinestudents table
    $query = "SELECT * FROM onlinestudents WHERE student_code = '$student_code'";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $student = $result->fetch_assoc();
        
        // Display actual password if available in session, otherwise show asterisks
        session_start();
        $display_password = isset($_SESSION['temp_password_' . $student_code]) ? 
                          $_SESSION['temp_password_' . $student_code] : "********";
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Online Registration Confirmation - <?php echo $student_code; ?></title>
            
            <!-- Font Awesome for icons -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            
            <style>
                /* Print Styles */
                @media print {
                    @page {
                        size: A4;
                        margin: 15mm;
                    }
                    
                    body {
                        font-family: 'Times New Roman', serif;
                        line-height: 1.5;
                        color: #000;
                        background: white;
                        margin: 0;
                        padding: 0;
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }
                    
                    .no-print {
                        display: none !important;
                    }
                    
                    .print-container {
                        box-shadow: none !important;
                        padding: 0 !important;
                        border: none !important;
                        margin: 0 !important;
                    }
                    
                    .control-buttons, .print-actions {
                        display: none !important;
                    }
                    
                    .watermark {
                        opacity: 0.15 !important;
                    }
                    
                    .logo-watermark {
                        opacity: 0.1 !important;
                    }
                    
                    .stamp {
                        opacity: 0.9 !important;
                    }
                }
                
                /* Screen Styles */
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    margin: 0;
                    padding: 30px;
                    min-height: 100vh;
                    box-sizing: border-box;
                }
                
                .print-actions {
                    text-align: center;
                    margin-bottom: 30px;
                    background: rgba(255,255,255,0.9);
                    padding: 20px;
                    border-radius: 15px;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                    max-width: 800px;
                    margin-left: auto;
                    margin-right: auto;
                }
                
                .print-actions h2 {
                    color: #667eea;
                    margin-top: 0;
                    margin-bottom: 20px;
                    font-size: 24px;
                }
                
                .action-buttons {
                    display: flex;
                    justify-content: center;
                    gap: 15px;
                    flex-wrap: wrap;
                }
                
                .btn {
                    padding: 12px 25px;
                    border: none;
                    border-radius: 8px;
                    cursor: pointer;
                    font-size: 16px;
                    font-weight: 600;
                    text-decoration: none;
                    display: inline-flex;
                    align-items: center;
                    gap: 10px;
                    transition: all 0.3s ease;
                    min-width: 180px;
                    justify-content: center;
                }
                
                .btn:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
                }
                
                .btn-primary {
                    background: linear-gradient(135deg, #667eea, #764ba2);
                    color: white;
                    border: 2px solid #5a6fd8;
                }
                
                .btn-success {
                    background: linear-gradient(135deg, #28a745, #20c997);
                    color: white;
                    border: 2px solid #218838;
                }
                
                .btn-secondary {
                    background: linear-gradient(135deg, #6c757d, #5a6268);
                    color: white;
                    border: 2px solid #545b62;
                }
                
                .btn-warning {
                    background: linear-gradient(135deg, #ffc107, #fd7e14);
                    color: #212529;
                    border: 2px solid #e0a800;
                }
                
                .print-container {
                    max-width: 800px;
                    margin: 0 auto;
                    background: white;
                    padding: 40px;
                    border-radius: 20px;
                    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                    position: relative;
                    border: 2px solid #667eea;
                    overflow: hidden;
                }
                
                /* Watermark Background */
                .watermark-bg {
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: 
                        repeating-linear-gradient(
                            45deg,
                            rgba(102, 126, 234, 0.03),
                            rgba(102, 126, 234, 0.03) 10px,
                            rgba(118, 75, 162, 0.03) 10px,
                            rgba(118, 75, 162, 0.03) 20px
                        ),
                        repeating-linear-gradient(
                            -45deg,
                            rgba(102, 126, 234, 0.02),
                            rgba(102, 126, 234, 0.02) 15px,
                            rgba(118, 75, 162, 0.02) 15px,
                            rgba(118, 75, 162, 0.02) 30px
                        );
                    z-index: 0;
                    pointer-events: none;
                }
                
                /* Main Watermark Text */
                .watermark-text {
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%) rotate(-45deg);
                    font-size: 100px;
                    color: rgba(102, 126, 234, 0.08);
                    font-weight: 900;
                    z-index: 1;
                    white-space: nowrap;
                    text-transform: uppercase;
                    letter-spacing: 10px;
                    pointer-events: none;
                }
                
                /* Logo Watermark */
                .logo-watermark {
                    position: absolute;
                    top: 20px;
                    left: 20px;
                   width: 218px;
                   height: 138px;
                 
                    z-index: 1;
                    pointer-events: none;
                    background: url('logo.png') no-repeat center center;
                    background-size: contain;
                }
                
                /* Content wrapper */
                .content-wrapper {
                    position: relative;
                    z-index: 2;
                }
                
                /* Header */
                .header {
                    text-align: center;
                    padding-bottom: 25px;
                    margin-bottom: 30px;
                    border-bottom: 3px double #667eea;
                    position: relative;
                }
                
                .institute-name {
                    color: #667eea;
                    font-size: 32px;
                    font-weight: 900;
                    margin: 0;
                    text-transform: uppercase;
                    letter-spacing: 2px;
                    text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
                }
                
                .sub-title {
                    color: #764ba2;
                    font-size: 18px;
                    margin: 10px 0;
                    font-weight: 600;
                }
                
                .header-info {
    display: flex;
    /* justify-content: space-evenly; */
    gap: 9px;
    flex-wrap: wrap;
    margin-top: 15px;
    font-size: 19px;
    color: #555;
}
                
                .header-info p {
                    margin: 5px 0;
                    display: flex;
                    align-items: center;
                    gap: 5px;
                }
                
                .header-info i {
                    color: #667eea;
                }
                
                /* Document Title */
                .document-title {
                    text-align: center;
                    margin: 30px 0;
                    padding: 15px;
                    background: linear-gradient(135deg, #667eea, #764ba2);
                    color: white;
                    border-radius: 10px;
                    font-size: 22px;
                    font-weight: 700;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
                }
                
                /* Student Photo */
                .student-photo-container {
    position: absolute;
    right: 40px;
    top: 12px;
    width: 133px;
    height: 168px;
    border: 3px solid #3f4370;
    border-radius: 10px;
    overflow: hidden;
    background: #f8f9fa;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}
                
                .student-photo {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                    background: #f8f9fa;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: #999;
                }
                
                /* Section Styling */
                .section-title {
                    background: linear-gradient(135deg, #667eea, #764ba2);
                    color: white;
                    padding: 12px 20px;
                    border-radius: 8px;
                    font-size: 18px;
                    font-weight: 600;
                    margin: 25px 0 15px 0;
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    box-shadow: 0 3px 10px rgba(102, 126, 234, 0.2);
                }
                
                .section-title i {
                    font-size: 20px;
                }
                
                /* Details Table */
                .details-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 15px 0;
                    font-size: 14px;
                    border: 1px solid #dee2e6;
                    border-radius: 8px;
                    overflow: hidden;
                }
                
                .details-table tr:nth-child(even) {
                    background: #f8f9fa;
                }
                
                .details-table tr:hover {
                    background: #e9ecef;
                }
                
                .details-table td {
                    padding: 12px 15px;
                    border: 1px solid #dee2e6;
                    vertical-align: top;
                }
                
                .details-table .label {
                    width: 35%;
                    font-weight: 700;
                    color: #495057;
                    background: #f1f3f5;
                }
                
                .details-table .value {
                    width: 65%;
                    color: #212529;
                }
                
                /* Login Box */
                .login-box {
                    background: linear-gradient(135deg, #20c997, #28a745);
                    color: white;
                    padding: 25px;
                    border-radius: 15px;
                    margin: 25px 0;
                    text-align: center;
                    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
                    border: 2px dashed rgba(255,255,255,0.3);
                }
                
                .login-box h4 {
                    margin-top: 0;
                    color: white;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 12px;
                    font-size: 20px;
                    margin-bottom: 20px;
                }
                
                .credentials {
                    font-family: 'Courier New', monospace;
                    font-size: 20px;
                    margin: 20px 0;
                    background: rgba(255,255,255,0.15);
                    padding: 20px;
                    border-radius: 8px;
                    display: inline-block;
                    border: 1px solid rgba(255,255,255,0.2);
                }
                
                /* Important Notes */
                .important-notes {
                    background: linear-gradient(135deg, #fff3cd, #ffeaa7);
                    padding: 25px;
                    border-radius: 12px;
                    margin: 25px 0;
                    border-left: 6px solid #ffc107;
                    box-shadow: 0 5px 15px rgba(255, 193, 7, 0.2);
                }
                
                .important-notes h5 {
                    color: #856404;
                    margin-top: 0;
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    font-size: 18px;
                    margin-bottom: 15px;
                }
                
                .important-notes ol {
                    margin: 0;
                    padding-left: 20px;
                    color: #856404;
                }
                
                .important-notes li {
                    margin-bottom: 10px;
                    line-height: 1.5;
                }
                
                /* Signatures */
                .signature-area {
                    margin-top: 60px;
                    padding-top: 30px;
                    border-top: 2px dashed #dee2e6;
                }
                
                .signature-container {
                    display: flex;
                    justify-content: space-between;
                    flex-wrap: wrap;
                    gap: 30px;
                }
                
                .signature-box {
                    flex: 1;
                    min-width: 250px;
                    text-align: center;
                }
                
                .signature-line {
                    width: 80%;
                    height: 1px;
                    background: #333;
                    margin: 40px auto 15px auto;
                    position: relative;
                }
                
                .signature-line:after {
                    content: '';
                    position: absolute;
                    top: -5px;
                    left: 0;
                    right: 0;
                    height: 11px;
                    border-top: 1px solid #333;
                    border-bottom: 1px solid #333;
                }
                
                /* Stamp */
                .stamp {
                    position: absolute;
                    right: 40px;
                    bottom: 40px;
                    width: 180px;
                    opacity: 0.9;
                    transform: rotate(8deg);
                    filter: drop-shadow(0 5px 10px rgba(0,0,0,0.2));
                }
                
                .stamp-content {
                    text-align: center;
                    border: 3px solid #dc3545;
                    padding: 15px;
                    background: white;
                    border-radius: 50%;
                    width: 160px;
                    height: 160px;
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    align-items: center;
                    position: relative;
                    overflow: hidden;
                }
                
                .stamp-content:before {
                    content: '';
                    position: absolute;
                    top: -10px;
                    left: -10px;
                    right: -10px;
                    bottom: -10px;
                    border: 2px dashed #dc3545;
                    border-radius: 50%;
                    opacity: 0.5;
                }
                
                .stamp-title {
                    font-weight: 900;
                    color: #dc3545;
                    font-size: 18px;
                    margin-bottom: 8px;
                    text-transform: uppercase;
                }
                
                .stamp-institute {
                    font-size: 12px;
                    color: #333;
                    margin-bottom: 5px;
                    font-weight: 600;
                }
                
                .stamp-date {
                    font-size: 11px;
                    color: #666;
                    font-style: italic;
                }
                
                /* Footer */
                .footer-note {
                    margin-top: 40px;
                    padding-top: 20px;
                    border-top: 1px solid #dee2e6;
                    font-size: 12px;
                    color: #6c757d;
                    text-align: center;
                    background: #f8f9fa;
                    padding: 15px;
                    border-radius: 8px;
                }
                
                /* Online Registration Badge */
                .online-badge {
                    position: absolute;
                    top: 20px;
                    left: 20px;
                    background: linear-gradient(135deg, #667eea, #764ba2);
                    color: white;
                    padding: 8px 15px;
                    border-radius: 20px;
                    font-size: 12px;
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                    box-shadow: 0 3px 10px rgba(102, 126, 234, 0.3);
                    z-index: 3;
                }
                
                /* Responsive Design */
                @media (max-width: 768px) {
                    body {
                        padding: 15px;
                    }
                    
                    .print-container {
                        padding: 25px;
                    }
                    
                    .student-photo-container {
                        position: relative;
                        right: auto;
                        top: auto;
                        margin: 0 auto 20px auto;
                    }
                    
                    .action-buttons {
                        flex-direction: column;
                        align-items: center;
                    }
                    
                    .btn {
                        width: 100%;
                        max-width: 300px;
                    }
                    
                    .details-table {
                        font-size: 13px;
                    }
                    
                    .details-table td {
                        padding: 10px 12px;
                    }
                    
                    .watermark-text {
                        font-size: 60px;
                        letter-spacing: 5px;
                    }
                    
                    .stamp {
                        position: relative;
                        right: auto;
                        bottom: auto;
                        margin: 30px auto;
                    }
                }
            </style>
        </head>
        <body>
            <!-- Print Actions Panel -->
            <div class="print-actions no-print">
                <h2><i class="fas fa-print me-2"></i>Online Registration Print Options</h2>
                <div class="action-buttons">
                    <button class="btn btn-primary" onclick="window.print()">
                        <i class="fas fa-print"></i> Print Now
                    </button>
                    <button class="btn btn-success" onclick="downloadAsPDF()">
                        <i class="fas fa-file-pdf"></i> Save as PDF
                    </button>
                    <button class="btn btn-warning" onclick="copyCredentials()">
                        <i class="fas fa-copy"></i> Copy Credentials
                    </button>
                    <a href="student_reg.php" class="btn btn-secondary">
                        <i class="fas fa-plus-circle"></i> New Registration
                    </a>
                </div>
                <p style="margin-top: 15px; color: #666; font-size: 14px;">
                    <i class="fas fa-info-circle"></i> 
                    For best printing results, use A4 paper size and "Save as PDF" option
                </p>
            </div>
            
            <!-- Main Print Container -->
            <div class="print-container">
                <!-- Watermark Background -->
                <div class="watermark-bg"></div>
                
                <!-- Watermark Text -->
                <div class="watermark-text">ONLINE REGISTRATION</div>
                
                <!-- Logo Watermark -->
                <div class="logo-watermark"></div>
                
                <!-- Online Registration Badge -->
                <div class="online-badge">
                    <i class="fas fa-globe me-1"></i> Online Registration
                </div>
                
                <!-- Content Wrapper -->
                <div class="content-wrapper">
                    
                    <!-- Header -->
                    <div class="header">
                        <!-- Student Photo -->
                        <div class="student-photo-container">
                            <?php if (!empty($student['photo'])): ?>
                                <img src="uploads/students/photos/<?php echo htmlspecialchars($student['photo']); ?>" 
                                     alt="Student Photo" class="student-photo"
                                     onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"150\" height=\"180\" viewBox=\"0 0 150 180\"><rect width=\"150\" height=\"180\" fill=\"%23f8f9fa\"/><text x=\"50%\" y=\"50%\" font-family=\"Arial\" font-size=\"24\" fill=\"%23999\" text-anchor=\"middle\" dy=\".3em\">No Photo</text></svg>'">
                            <?php else: ?>
                                <div class="student-photo">
                                    <i class="fas fa-user fa-4x"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <h1 class="institute-name">SHARNAY INSTITUTE</h1>
                        <p class="sub-title">An ISO 9001:2015 Certified Educational Institute</p>
                        
                        <div class="header-info">
                            <p><i class="fas fa-map-marker-alt"></i> 123 Education Street, Lucknow, UP - 226001</p>
                            <p><i class="fas fa-phone"></i> +91 7004021899</p>
                            <p><i class="fas fa-envelope"></i> info@sharnayinstitute.com</p>
                            <p><i class="fas fa-globe"></i> www.sharnayinstitute.com</p>
                        </div>
                    </div>
                    
                    <!-- Document Title -->
                    <div class="document-title">
                        <i class="fas fa-file-certificate me-2"></i>
                        ONLINE REGISTRATION CONFIRMATION
                    </div>
                    
                    <!-- Registration Details -->
                    <div class="section-title">
                        <i class="fas fa-info-circle"></i> Registration Information
                    </div>
                    
                    <table class="details-table">
                        <tr>
                            <td class="label">Registration Number</td>
                            <td class="value"><strong style="color: #667eea; font-size: 16px;"><?php echo htmlspecialchars($student['student_code']); ?></strong></td>
                        </tr>
                        <tr>
                            <td class="label">Registration Date</td>
                            <td class="value"><?php echo date('d F, Y', strtotime($student['created_at'])); ?></td>
                        </tr>
                        <tr>
                            <td class="label">Registration Time</td>
                            <td class="value"><?php echo date('h:i A', strtotime($student['created_at'])); ?></td>
                        </tr>
                        <tr>
                            <td class="label">Registration Type</td>
                            <td class="value"><strong style="color: #28a745;">Online Registration</strong></td>
                        </tr>
                    </table>
                    
                    <!-- Personal Information -->
                    <div class="section-title">
                        <i class="fas fa-user-graduate"></i> Student Information
                    </div>
                    
                    <table class="details-table">
                        <tr>
                            <td class="label">Full Name</td>
                            <td class="value"><?php echo htmlspecialchars($student['name']); ?></td>
                        </tr>
                        <tr>
                            <td class="label">Father's Name</td>
                            <td class="value"><?php echo htmlspecialchars($student['father_name']); ?></td>
                        </tr>
                        <tr>
                            <td class="label">Mother's Name</td>
                            <td class="value"><?php echo htmlspecialchars($student['mother_name']); ?></td>
                        </tr>
                        <tr>
                            <td class="label">Date of Birth</td>
                            <td class="value"><?php echo date('d/m/Y', strtotime($student['dob'])); ?> (Age: <?php echo date_diff(date_create($student['dob']), date_create('today'))->y; ?> Years)</td>
                        </tr>
                        <tr>
                            <td class="label">Gender</td>
                            <td class="value"><?php echo htmlspecialchars($student['gender']); ?></td>
                        </tr>
                        <tr>
                            <td class="label">Blood Group</td>
                            <td class="value"><?php echo !empty($student['blood_group']) ? htmlspecialchars($student['blood_group']) : 'Not Specified'; ?></td>
                        </tr>
                        <tr>
                            <td class="label">Email Address</td>
                            <td class="value"><?php echo htmlspecialchars($student['email']); ?></td>
                        </tr>
                        <tr>
                            <td class="label">Mobile Number</td>
                            <td class="value"><?php echo htmlspecialchars($student['mobile']); ?></td>
                        </tr>
                    </table>
                    
                    <!-- Address Information -->
                    <div class="section-title">
                        <i class="fas fa-map-marked-alt"></i> Address Information
                    </div>
                    
                    <table class="details-table">
                        <tr>
                            <td class="label">Complete Address</td>
                            <td class="value"><?php echo htmlspecialchars($student['address']); ?></td>
                        </tr>
                        <tr>
                            <td class="label">City</td>
                            <td class="value"><?php echo htmlspecialchars($student['city']); ?></td>
                        </tr>
                        <tr>
                            <td class="label">District</td>
                            <td class="value"><?php echo htmlspecialchars($student['district']); ?></td>
                        </tr>
                        <tr>
                            <td class="label">State</td>
                            <td class="value"><?php echo htmlspecialchars($student['state']); ?></td>
                        </tr>
                        <tr>
                            <td class="label">Pin Code</td>
                            <td class="value"><?php echo htmlspecialchars($student['pin_code']); ?></td>
                        </tr>
                        <tr>
                            <td class="label">Block</td>
                            <td class="value"><?php echo !empty($student['block']) ? htmlspecialchars($student['block']) : 'Not Specified'; ?></td>
                        </tr>
                        <tr>
                            <td class="label">Post Office</td>
                            <td class="value"><?php echo !empty($student['post_office']) ? htmlspecialchars($student['post_office']) : 'Not Specified'; ?></td>
                        </tr>
                    </table>
                    
                    <!-- Course Details -->
                    <div class="section-title">
                        <i class="fas fa-graduation-cap"></i> Course Details
                    </div>
                    
                    <table class="details-table">
                        <tr>
                            <td class="label">Study Center</td>
                            <td class="value"><?php echo htmlspecialchars($student['study_center']); ?></td>
                        </tr>
                        <tr>
                            <td class="label">Course Name</td>
                            <td class="value"><?php echo htmlspecialchars($student['course_name']); ?></td>
                        </tr>
                        <tr>
                            <td class="label">Course Duration</td>
                            <td class="value"><?php echo htmlspecialchars($student['duration']); ?></td>
                        </tr>
                        <tr>
                            <td class="label">Total Course Fee</td>
                            <td class="value"><strong style="color: #28a745;">₹ <?php echo number_format($student['price'], 2); ?></strong></td>
                        </tr>
                        <tr>
                            <td class="label">Registration Amount Paid</td>
                            <td class="value"><strong style="color: #dc3545;">₹ <?php echo number_format($student['reg_amount'], 2); ?></strong></td>
                        </tr>
                        <tr>
                            <td class="label">Payment Mode</td>
                            <td class="value"><?php echo htmlspecialchars($student['payment_mode']); ?></td>
                        </tr>
                        <tr>
                            <td class="label">Payment Status</td>
                            <td class="value">
                                <?php 
                                $status_color = $student['payment_status'] == 'Paid' ? '#28a745' : 
                                              ($student['payment_status'] == 'Pending' ? '#ffc107' : '#fd7e14');
                                ?>
                                <span style="color: <?php echo $status_color; ?>; font-weight: bold;">
                                    <?php echo htmlspecialchars($student['payment_status']); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="label">Academic Session</td>
                            <td class="value">
                                <?php echo date('d/m/Y', strtotime($student['session_start'])); ?> 
                                to 
                                <?php echo date('d/m/Y', strtotime($student['session_end'])); ?>
                                (<?php 
                                    $start = new DateTime($student['session_start']);
                                    $end = new DateTime($student['session_end']);
                                    $interval = $start->diff($end);
                                    echo $interval->format('%y Year %m Month');
                                ?>)
                            </td>
                        </tr>
                    </table>
                    
                    <!-- Document Uploads -->
                    <div class="section-title">
                        <i class="fas fa-file-upload"></i> Document Verification
                    </div>
                    
                    <table class="details-table">
                        <tr>
                            <td class="label">Student Photo</td>
                            <td class="value"><?php echo !empty($student['photo']) ? 'Uploaded ✓' : 'Not Uploaded'; ?></td>
                        </tr>
                        <tr>
                            <td class="label">ID Proof Document</td>
                            <td class="value"><?php echo !empty($student['id_proof']) ? 'Uploaded ✓' : 'Not Uploaded'; ?></td>
                        </tr>
                    </table>
                    
                    <!-- Login Credentials -->
                    <div class="login-box">
                        <h4><i class="fas fa-user-lock"></i> Student Portal Access Credentials</h4>
                        <p>Use these credentials to login to your student portal:</p>
                        <div class="credentials">
                            <div style="margin-bottom: 10px;">
                                <strong>Portal URL:</strong> www.sharnayinstitute.com/student
                            </div>
                            <div style="margin-bottom: 10px;">
                                <strong>Username:</strong> <?php echo htmlspecialchars($student['username']); ?>
                            </div>
                            <div>
                                <strong>Password:</strong> <?php echo htmlspecialchars($display_password); ?>
                            </div>
                        </div>
                        <p><small><i class="fas fa-shield-alt"></i> Keep these credentials confidential and do not share with anyone.</small></p>
                    </div>
                    
                    <!-- Important Notes -->
                    <div class="important-notes">
                        <h5><i class="fas fa-exclamation-triangle"></i> Important Instructions & Notes</h5>
                        <ol>
                            <li>This is an official online registration confirmation document. Keep it safe for all future references.</li>
                            <li>Login to your student portal within 7 days using the provided credentials to complete your profile.</li>
                            <li>Carry original documents for verification on the first day of your class.</li>
                            <li>For any discrepancy in information, contact the institute within 15 days of registration.</li>
                            <li>Balance course fee must be paid as per the institute's payment schedule.</li>
                            <li>Institute rules and regulations are available on the official website and student portal.</li>
                            <li>This document is valid only with the official stamp and authorized signature.</li>
                        </ol>
                    </div>
                    
                    <!-- Signatures -->
                    <div class="signature-area">
                        <div class="signature-container">
                            <div class="signature-box">
                                <div class="signature-line"></div>
                                <p style="font-weight: bold; color: #333;">Student's Signature</p>
                                <p style="font-size: 12px; color: #666;">(<?php echo htmlspecialchars($student['name']); ?>)</p>
                                <p style="font-size: 11px; color: #999; margin-top: 5px;">Date: <?php echo date('d/m/Y'); ?></p>
                            </div>
                            <div class="signature-box">
                                <div class="signature-line"></div>
                                <p style="font-weight: bold; color: #333;">Authorized Signatory</p>
                                <p style="font-size: 12px; color: #666;">Sharnay Institute</p>
                                <p style="font-size: 11px; color: #999; margin-top: 5px;">Date: <?php echo date('d/m/Y'); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Stamp -->
                    <div class="stamp">
                        <div class="stamp-content">
                            <div class="stamp-title">REGISTERED</div>
                            <div class="stamp-institute">SHARNAY INSTITUTE</div>
                            <div class="stamp-date">ONLINE REGISTRATION</div>
                            <div class="stamp-date">Date: <?php echo date('d/m/Y'); ?></div>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="footer-note">
                        <p><strong><i class="fas fa-info-circle"></i> Note:</strong> This is a computer-generated online registration document. No physical signature is required.</p>
                        <p>Document Generated: <?php echo date('d/m/Y h:i A'); ?> | Reference ID: <?php echo htmlspecialchars($student['student_code']); ?></p>
                        <p>Verification: www.sharnayinstitute.com/verify?id=<?php echo urlencode($student['student_code']); ?> | Contact: help@sharnayinstitute.com</p>
                        <p style="margin-top: 10px; font-size: 11px; color: #999;">
                            <i class="fas fa-shield-alt"></i> This document is secured with digital watermark and unique registration code.
                        </p>
                    </div>
                </div>
            </div>
            
            <script>
                function downloadAsPDF() {
                    // Using browser's built-in print to PDF feature
                    alert('For best PDF quality:\n1. Click OK\n2. In print dialog, select "Save as PDF" as printer\n3. Adjust margins to "Minimum"\n4. Click Save');
                    window.print();
                }
                
                function copyCredentials() {
                    const username = "<?php echo $student['username']; ?>";
                    const password = "<?php echo $display_password; ?>";
                    const text = `Student Portal Credentials\n\nUsername: ${username}\nPassword: ${password}\n\nPortal URL: www.sharnayinstitute.com/student\n\nKeep these credentials safe and confidential.`;
                    
                    navigator.clipboard.writeText(text).then(() => {
                        alert('✓ Credentials copied to clipboard!\n\nUsername and password have been copied.\nYou can now paste them in a secure location.');
                    }).catch(err => {
                        alert('Failed to copy credentials. Please note them manually.');
                    });
                }
                
                // Auto print if requested
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.get('print') === 'true') {
                    setTimeout(() => {
                        window.print();
                    }, 1000);
                }
                
                // Handle print events
                window.addEventListener('beforeprint', () => {
                    console.log('Preparing for print...');
                });
                
                window.addEventListener('afterprint', () => {
                    alert('✅ Document printed successfully!\n\nPlease keep this registration confirmation safe.');
                });
                
                // Add logo fallback
                document.addEventListener('DOMContentLoaded', function() {
                    const logo = document.querySelector('.logo-watermark');
                    logo.addEventListener('error', function() {
                        this.style.background = 'none';
                        this.innerHTML = '<i class="fas fa-graduation-cap" style="font-size: 80px; color: rgba(102, 126, 234, 0.05);"></i>';
                    });
                });
            </script>
        </body>
        </html>
        <?php
    } else {
        // Student not found
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Student Not Found - Sharnay Institute</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <style>
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    margin: 0;
                    padding: 20px;
                }
                
                .error-card {
                    background: white;
                    padding: 50px;
                    border-radius: 20px;
                    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                    text-align: center;
                    max-width: 500px;
                    width: 100%;
                    border: 2px solid #667eea;
                }
                
                .error-icon {
                    font-size: 80px;
                    color: #dc3545;
                    margin-bottom: 25px;
                    animation: pulse 2s infinite;
                }
                
                @keyframes pulse {
                    0% { transform: scale(1); }
                    50% { transform: scale(1.1); }
                    100% { transform: scale(1); }
                }
                
                .error-title {
                    color: #dc3545;
                    font-size: 28px;
                    margin-bottom: 20px;
                    font-weight: 700;
                }
                
                .error-message {
                    color: #666;
                    margin-bottom: 30px;
                    line-height: 1.6;
                    font-size: 16px;
                }
                
                .btn-back {
                    display: inline-flex;
                    align-items: center;
                    gap: 12px;
                    padding: 15px 30px;
                    background: linear-gradient(135deg, #667eea, #764ba2);
                    color: white;
                    text-decoration: none;
                    border-radius: 10px;
                    font-size: 16px;
                    font-weight: 600;
                    transition: all 0.3s;
                    border: 2px solid #5a6fd8;
                }
                
                .btn-back:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
                }
            </style>
        </head>
        <body>
            <div class="error-card">
                <div class="error-icon">
                    <i class="fas fa-user-slash"></i>
                </div>
                <h2 class="error-title">Registration Not Found</h2>
                <p class="error-message">
                    The requested online registration details could not be found in our system.<br><br>
                    Please verify the registration code or contact the institute administration for assistance.
                </p>
                <a href="student_reg.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Registration Portal
                </a>
            </div>
        </body>
        </html>
        <?php
    }
} else {
    // No student code provided
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Invalid Request - Sharnay Institute</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
                margin: 0;
                padding: 20px;
            }
            
            .error-card {
                background: white;
                padding: 50px;
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                text-align: center;
                max-width: 500px;
                width: 100%;
                border: 2px solid #ffc107;
            }
            
            .error-icon {
                font-size: 80px;
                color: #ffc107;
                margin-bottom: 25px;
            }
            
            .error-title {
                color: #ffc107;
                font-size: 28px;
                margin-bottom: 20px;
                font-weight: 700;
            }
            
            .error-message {
                color: #666;
                margin-bottom: 30px;
                line-height: 1.6;
                font-size: 16px;
            }
            
            .btn-back {
                display: inline-flex;
                    align-items: center;
                    gap: 12px;
                    padding: 15px 30px;
                    background: linear-gradient(135deg, #667eea, #764ba2);
                    color: white;
                    text-decoration: none;
                    border-radius: 10px;
                    font-size: 16px;
                    font-weight: 600;
                    transition: all 0.3s;
                    border: 2px solid #5a6fd8;
                }
                
                .btn-back:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
                }
        </style>
    </head>
    <body>
        <div class="error-card">
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h2 class="error-title">Invalid Access Request</h2>
            <p class="error-message">
                No registration code was provided in your request.<br><br>
                Please access this page through the official registration portal or use a valid registration confirmation link.
            </p>
            <a href="student_reg.php" class="btn-back">
                <i class="fas fa-user-plus"></i> Go to Registration Portal
            </a>
        </div>
    </body>
    </html>
    <?php
}

$conn->close();
?>