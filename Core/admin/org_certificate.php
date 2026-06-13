<?php
include 'conn.php';

// Simple QR code generator function (no external dependencies)
function generateQRCode($data, $size = 150) {
    // Use Google Charts API as fallback
    $encoded_data = urlencode($data);
    return "https://chart.googleapis.com/chart?chs={$size}x{$size}&cht=qr&chl=" . $encoded_data . "&choe=UTF-8";
}

// Alternative QR code API (if Google is blocked)
function generateQRCodeAlternative($data, $size = 150) {
    $encoded_data = urlencode($data);
    return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data=" . $encoded_data;
}

if (!isset($_GET['id'])) {
    die("Certificate ID not provided.");
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT c.*, s.name AS student_name, c.father_name, c.mother_name, c.course_name, c.duration, s.study_center, s.photo, c.issue_date,s.id
                        FROM certificates c
                        JOIN onlinestudents s ON c.student_id = s.id
                        WHERE c.student_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Certificate not found.");
}

$student = $result->fetch_assoc();

// Format the issue date
if (!empty($student['issue_date']) && $student['issue_date'] != '0000-00-00') {
    $issue_date = date('d-m-Y', strtotime($student['issue_date']));
} else {
    $issue_date = date('d-m-Y');
}

// Create QR code data string with all required information
$qr_data = "SHARNAY INSTITUTE CERTIFICATE\n";
$qr_data .= "=============================\n";
$qr_data .= "Student Code: " . ($student['student_code'] ?? 'N/A') . "\n";
$qr_data .= "Name: " . ($student['student_name'] ?? 'N/A') . "\n";
$qr_data .= "Father: " . ($student['father_name'] ?? 'N/A') . "\n";
$qr_data .= "Course: " . ($student['course_name'] ?? 'N/A') . "\n";
$qr_data .= "Duration: " . ($student['duration'] ?? 'N/A') . "\n";
$qr_data .= "Grade: " . ($student['grade'] ?? 'N/A') . "\n";
$qr_data .= "Center: " . ($student['study_center'] ?? 'N/A') . "\n";
$qr_data .= "Issue Date: " . $issue_date . "\n";
$qr_data .= "Certificate ID: " . ($student['id'] ?? 'N/A');

// Generate QR code URL - try Google API first, fallback to alternative
$qr_code_url = generateQRCode($qr_data, 120);
// Uncomment next line if Google API doesn't work
// $qr_code_url = generateQRCodeAlternative($qr_data, 120);

$photo = !empty($student['photo']) ? htmlspecialchars($student['photo']) : 'default.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate - Sharnay Institute</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f2f2f2;
            font-family: 'Times New Roman', serif;
        }

        .certificate-container {
            width: 811px;
            height: 591px;
            position: relative;
            margin: 30px auto;
            background: url('images/Certificate_idcard/Certificate_sh.jpg') no-repeat center center;
            background-size: cover;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.4);
        }

        .photo {
            position: absolute;
            top: 182px;
            right: 21px;
            width: 114px;
            height: 121px;
            border: 1px solid #47ce3bff;
            object-fit: cover;
        }

        .field {
            position: absolute;
            font-size: 20px;
            font-weight: bold;
            color: #000;
            
        }

        .reg-no { top: 25px; left: 154px; }
        .serial-no { top: 26px; right: 282px; }
        .student-name { top: 247px; left: 428px; }
        .father-name { top: 281px; left: 325px; }
        .course-name { top: 323px; left: 347px; }
        .duration { top: 320px; right: 63px; }
        .grade { top: 362px; left: 175px; }
        .center { top: 358px; left: 420px; }
        .issue-date { top: 398px; left: 145px; }

        /* QR Code Styling - Better positioning */
        .qr-code-container {
            position: absolute;
            bottom: 60px;
            left: 40px;
            z-index: 10;
            text-align: center;
        }
        
        .qr-code {
            width: 100px;
            height: 100px;
            background: white;
            padding: 5px;
            border: 1px solid #333;
            box-shadow: 0 0 5px rgba(0,0,0,0.3);
        }
        
        .qr-code img {
            width: 100%;
            height: 100%;
            display: block;
        }
        
        .qr-label {
            font-size: 10px;
            margin-top: 3px;
            color: #333;
            font-weight: bold;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                margin: 0;
                padding: 0;
                background: white;
            }

            .certificate-container {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                box-shadow: none;
                margin: 0 auto;
            }

            button {
                display: none;
            }
            
            .qr-code {
                border: 1px solid #000 !important;
                box-shadow: none !important;
                background: white !important;
            }
        }

        .print-btn-container {
            text-align: center;
            margin: 20px auto;
            padding: 10px;
        }
        
        .print-btn {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .print-btn:hover {
            background: #45a049;
        }
        
        .debug-info {
            display: none;
            background: #f8f9fa;
            border: 1px solid #ddd;
            padding: 10px;
            margin: 10px auto;
            max-width: 800px;
            font-family: monospace;
            font-size: 12px;
        }
    </style>
</head>
<body>

<div class="certificate-container">
    <img class="photo" src="../uploads/students/photos/<?= htmlspecialchars($photo) ?>" alt="Student Photo">
    
    <!-- QR Code -->
    <div class="qr-code-container">
        <div class="qr-code">
            <img src="<?= $qr_code_url ?>" alt="Certificate QR Code" 
                 onerror="this.onerror=null; this.src='https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?= urlencode($qr_data) ?>';">
        </div>
    
    </div>

    <div class="field reg-no"><?= htmlspecialchars($student['student_code']) ?></div>
    <div class="field serial-no"><?= htmlspecialchars($student['id']) ?></div>
    <div class="field student-name"><?= htmlspecialchars($student['student_name']) ?></div>
    <div class="field father-name"><?= htmlspecialchars($student['father_name']) ?></div>
    <div class="field course-name"><?= htmlspecialchars($student['course_name']) ?></div>
    <div class="field duration"><?= htmlspecialchars($student['duration']) ?></div>
    <div class="field grade"><?= htmlspecialchars($student['grade']) ?></div>
    <div class="field center"><?= htmlspecialchars($student['study_center']) ?></div>
    <div class="field issue-date"><?= htmlspecialchars($issue_date) ?></div>
</div>

<div class="print-btn-container">
    <button class="print-btn" onclick="window.print()">🖨️ Print Certificate</button>
    <button class="print-btn" onclick="toggleDebug()" style="background: #666; margin-left: 10px;">Debug Info</button>
</div>

<div class="debug-info" id="debugInfo">
    <strong>QR Code Data:</strong><br>
    <pre><?= htmlspecialchars($qr_data) ?></pre>
    <strong>QR Code URL:</strong><br>
    <pre><?= htmlspecialchars($qr_code_url) ?></pre>
    <strong>Student Data:</strong><br>
    <pre><?= print_r($student, true) ?></pre>
</div>

<script>
    function toggleDebug() {
        var debugInfo = document.getElementById('debugInfo');
        debugInfo.style.display = debugInfo.style.display === 'block' ? 'none' : 'block';
    }
    
    // Test QR code image on load
    window.onload = function() {
        var qrImg = document.querySelector('.qr-code img');
        qrImg.onerror = function() {
            console.log("Primary QR code failed, trying alternative...");
            // Try alternative API
            this.src = 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?= urlencode($qr_data) ?>';
        };
        
        console.log("Certificate loaded. QR data length:", <?= strlen($qr_data) ?>);
    };
</script>

</body>
</html>