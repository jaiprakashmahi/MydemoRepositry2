<?php
/**
 * termandcondition.php
 * 
 * Displays the terms and conditions data from the provided Excel structure
 * in a user-friendly format on a webpage.
 */

// Define the data as an associative array for easy management
// This data is extracted from the provided Excel content (Sheet2)
$termsData = [
    'website_security' => [
        'topic' => 'Website Security',
        'details' => 'Website must be secure.'
    ],
    'course_fee_display' => [
        'topic' => 'Course Fee Display',
        'details' => 'Course fee must be displayed clearly.'
    ],
    'privacy_policy' => [
        'topic' => 'Privacy Policy',
        'details' => 'Website must have a privacy policy page.'
    ],
    'terms_conditions' => [
        'topic' => 'Terms and Conditions',
        'details' => [
            'Registration fee of ₹3500/- shall be refundable.',
            'This amount of ₹3500/- shall be refunded in four installments over four years, i.e., ₹1000/- per year.',
            'After one year of registration, ₹1000/- shall be payable, applicable from January.',
            'Student registration fee of only ₹200/- shall be collected online.'
        ]
    ],
    'cancellation_refund' => [
        'topic' => 'Cancellation and Refund Policy',
        'details' => 'In case of course cancellation, the amount after deducting the registration fee not be refunded.'
    ],
    'payment_policy' => [
        'topic' => 'Payment Policy',
        'details' => 'From the collected fee of ₹200/- per student, the commission shall be distributed as: State Nodal (3%), Zonal Manager (5%), District Coordinator (8%), Block Coordinator (12%), totaling 28% of the collected amount. This commission shall be transferred to the respective members\' bank accounts.'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions | SIET</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 30px 40px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .header p {
            font-size: 1rem;
            opacity: 0.9;
        }

        .content {
            padding: 40px;
        }

        .terms-section {
            margin-bottom: 35px;
            border-left: 4px solid #3498db;
            padding-left: 20px;
            transition: all 0.3s ease;
        }

        .terms-section:hover {
            border-left-color: #e74c3c;
            padding-left: 25px;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            font-size: 1.3rem;
        }

        .section-content {
            color: #555;
            line-height: 1.8;
        }

        .bullet-list {
            list-style: none;
            padding-left: 0;
        }

        .bullet-list li {
            margin-bottom: 12px;
            padding-left: 25px;
            position: relative;
        }

        .bullet-list li:before {
            content: "•";
            color: #3498db;
            font-weight: bold;
            font-size: 1.2rem;
            position: absolute;
            left: 0;
        }

        .numbered-list {
            list-style: none;
            padding-left: 0;
        }

        .numbered-list li {
            margin-bottom: 12px;
            padding-left: 30px;
            position: relative;
            counter-increment: step-counter;
        }

        .numbered-list li:before {
            content: counter(step-counter) ".";
            color: #3498db;
            font-weight: bold;
            position: absolute;
            left: 0;
        }

        .commission-table {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 15px;
            border: 1px solid #e1e8ed;
        }

        .commission-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }

        .commission-row:last-child {
            border-bottom: none;
        }

        .commission-label {
            font-weight: 600;
            color: #2c3e50;
        }

        .commission-value {
            color: #e74c3c;
            font-weight: 500;
        }

        .total-commission {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 2px solid #3498db;
            font-weight: bold;
            color: #2c3e50;
            display: flex;
            justify-content: space-between;
        }

        .footer {
            background: #2c3e50;
            color: white;
            text-align: center;
            padding: 20px;
            font-size: 0.9rem;
        }

        .effective-date {
            background: #e8f4fd;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
            font-size: 0.9rem;
            color: #2c3e50;
        }

        @media (max-width: 768px) {
            .content {
                padding: 20px;
            }
            
            .header h1 {
                font-size: 1.5rem;
            }
            
            .section-title {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Terms and Conditions</h1>
            <p>Please read these terms carefully before proceeding with registration</p>
        </div>
        
        <div class="content">
            <div class="effective-date">
                <strong>📅 Effective Date:</strong> January 1, 2024 | 
                <strong>📌 Last Updated:</strong> January 1, 2024
            </div>

            <!-- Website Security Section -->
           

            <!-- Terms and Conditions Section (Main) -->
            <div class="terms-section">
                <div class="section-title">
                    📜 <?php echo htmlspecialchars($termsData['terms_conditions']['topic']); ?>
                </div>
                <div class="section-content">
                    <ol class="numbered-list" style="counter-reset: step-counter;">
                        <?php foreach ($termsData['terms_conditions']['details'] as $detail): ?>
                            <li><?php echo htmlspecialchars($detail); ?></li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            </div>

            <!-- Cancellation and Refund Policy Section -->
            <div class="terms-section">
                <div class="section-title">
                    🔄 <?php echo htmlspecialchars($termsData['cancellation_refund']['topic']); ?>
                </div>
                <div class="section-content">
                    <p><?php echo htmlspecialchars($termsData['cancellation_refund']['details']); ?></p>
                </div>
            </div>

            <!-- Payment Policy Section -->
            <div class="terms-section">
                <div class="section-title">
                    💳 <?php echo htmlspecialchars($termsData['payment_policy']['topic']); ?>
                </div>
                <div class="section-content">
                    <p><?php echo htmlspecialchars($termsData['payment_policy']['details']); ?></p>
                    
                    <div class="commission-table">
                        <div class="commission-row">
                            <span class="commission-label">State Nodal Commission:</span>
                            <span class="commission-value">3% of collected fee</span>
                        </div>
                        <div class="commission-row">
                            <span class="commission-label">Zonal Manager Commission:</span>
                            <span class="commission-value">5% of collected fee</span>
                        </div>
                        <div class="commission-row">
                            <span class="commission-label">District Coordinator Commission:</span>
                            <span class="commission-value">8% of collected fee</span>
                        </div>
                        <div class="commission-row">
                            <span class="commission-label">Block Coordinator Commission:</span>
                            <span class="commission-value">12% of collected fee</span>
                        </div>
                        <div class="total-commission">
                            <span>Total Commission:</span>
                            <span>28% of collected fee (₹56 per student based on ₹200 fee)</span>
                        </div>
                    </div>
                    <p style="margin-top: 15px; font-size: 0.9rem; color: #666;">
                        <strong>Note:</strong> The commission of 28% is calculated on the collected student registration fee of ₹200/- 
                        and will be transferred directly to the respective members' bank accounts.
                    </p>
                </div>
            </div>

            <!-- Additional Notes Section (from original data) -->
            <div class="terms-section">
                <div class="section-title">
                    ℹ️ Important Information
                </div>
                <div class="section-content">
                    <ul class="bullet-list">
                        <li>Student registration fee of ₹200/- shall be collected online only.</li>
                        <li>All payments and refunds are processed through authorized banking channels.</li>
                        <li>Terms and conditions are subject to change with prior notice.</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> SIET - All Rights Reserved</p>
            <p style="margin-top: 10px; font-size: 0.8rem;">For any queries, please contact our support team at support@siet.edu.in</p>
        </div>
    </div>
</body>
</html>