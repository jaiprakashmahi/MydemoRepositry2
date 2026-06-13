<?php
include 'conn.php';

function generateQRCode($data, $size = 150) {
    $encoded_data = urlencode($data);
    return "https://chart.googleapis.com/chart?chs={$size}x{$size}&cht=qr&chl=" . $encoded_data . "&choe=UTF-8";
}

function generateQRCodeAlternative($data, $size = 150) {
    $encoded_data = urlencode($data);
    return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data=" . $encoded_data;
}

if (!isset($_GET['id'])) {
    die("Student ID not provided.");
}

$student_id = intval($_GET['id']);

// Get student details with marks
$stmt = $conn->prepare("
    SELECT 
        s.*,
        c.written_marks,
        c.practical_marks,
        c.project_marks,
        c.viva_marks,
        c.percentage,
        c.grade,
        c.exam_date,
        cs.course_name as course_name_full,
        cs.duration as course_duration
    FROM onlinestudents s
    LEFT JOIN certificates c ON s.id = c.student_id
    LEFT JOIN courses cs ON s.course_id = cs.id
    WHERE s.id = ?
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Student not found.");
}

$student = $result->fetch_assoc();

// Get module-wise marks with descriptions
$modules_stmt = $conn->prepare("
    SELECT m.module_name, m.module_code, m.description, smm.* 
    FROM student_module_marks smm
    JOIN modules m ON smm.module_id = m.id
    WHERE smm.student_id = ?
    ORDER BY m.order_number, m.id
");
$modules_stmt->bind_param("i", $student_id);
$modules_stmt->execute();
$modules_result = $modules_stmt->get_result();

$modules_marks = [];
$total_written = 0;
$total_practical = 0;
$total_project = 0;
$total_viva = 0;
$grand_total = 0;
$grand_max = 0;

while ($module = $modules_result->fetch_assoc()) {
    $modules_marks[] = $module;
    $total_written += $module['written_marks'];
    $total_practical += $module['practical_marks'];
    $total_project += $module['project_marks'];
    $total_viva += $module['viva_marks'];
    $grand_total += $module['total_marks'];
    
    // Get max marks for this module
    $max_stmt = $conn->prepare("SELECT max_written_marks, max_practical_marks FROM modules WHERE id = ?");
    $max_stmt->bind_param("i", $module['module_id']);
    $max_stmt->execute();
    $max_result = $max_stmt->get_result();
    $max_data = $max_result->fetch_assoc();
    
    // CHANGED: Add 100 instead of 200 (50+50 for project and viva instead of 100+100)
    $grand_max += $max_data['max_written_marks'] + $max_data['max_practical_marks'] + 100;
    $max_stmt->close();
}

$stmt->close();
$modules_stmt->close();

// Calculate overall percentage
$overall_percentage = $grand_max > 0 ? ($grand_total / $grand_max) * 100 : 0;

// Generate serial number
$serial_no = date('Y') . '-' . str_pad($student_id, 5, '0', STR_PAD_LEFT);

// Generate QR code data
$qr_data = "SHARNAY INSTITUTE\n";
$qr_data .= "Student ID: {$student_id}\n";
$qr_data .= "Student Code: {$student['student_code']}\n";
$qr_data .= "Name: {$student['name']}\n";
$qr_data .= "Course: {$student['course_name_full']}\n";
$qr_data .= "Duration: {$student['course_duration']}\n";
$qr_data .= "Study Center: {$student['study_center']}\n";
$qr_data .= "Exam Date: " . date('d/m/Y', strtotime($student['exam_date'] ?? date('Y-m-d'))) . "\n";
$qr_data .= "Overall Percentage: " . number_format($overall_percentage, 2) . "%\n";
$qr_data .= "Grade: {$student['grade']}\n";
$qr_data .= "Result: " . ($overall_percentage >= 40 ? 'PASS' : 'FAIL') . "\n";
$qr_data .= "Total Marks: {$grand_total}/{$grand_max}";

$qr_data_encoded = urlencode($qr_data);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sharnay Institute - Marksheet</title>
  <style>
    /* Base styles */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Times New Roman', serif;
      display: flex;
      flex-direction: column;
      align-items: center;
      min-height: 100vh;
      padding: 20px;
    }

    .marksheet-container {
      width: 210mm;  /* A4 width */
      height: 297mm; /* A4 height */
      position: relative;
      background-image: url('images/Certificate_idcard/marksheet_final.jpg');
      background-size: 100% 100%;
      background-repeat: no-repeat;
      background-position: center;
      margin: 0 auto;
      box-shadow: 0 0 20px rgba(0,0,0,0.1);
      overflow: hidden;
    }

    /* Student ID and Code at top */
    .student-top-info {
      position: absolute;
      top: 8mm;
      left: 56mm;
      right: 15mm;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .student-id-code {
      display: flex;
      gap: 360px;
    margin-left: 5px;
    }

    .id-code-item {
      text-align: center;
    }

    .id-code-label {
      font-size: 12px;
      color: #666;
      margin-bottom: 3px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .id-code-value {
      font-size: 16px;
      font-weight: bold;
      color: #2c3e50;
    }

    /* Issue Date at top center */
    .issue-date-top {
      position: absolute;
      top: 54mm;
      left: 0;
      right: 0;
      text-align: center;
      font-size: 22px;
      color: #000;
      font-weight: bold;
      padding: 5px 20px;
      display: inline-block;
      margin: 0 auto;
      width: fit-content;
    }

    /* Student Photo Area */
    .photo-area {
      position: absolute;
          top: 56mm;
    right: 21mm;
      width: 125px;
      height: 155px;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      border: 2px solid #000;
      background: #fff;
      padding: 2px;
    }

    .student-photo {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    /* Student Details Container */
    .student-info-container {
      position: absolute;
      top: 65mm;
      left:16mm;
      width: 400px;
    }

    .detail-row {
      margin-bottom: 6px;
      font-size: 14px;
      line-height: 1.4;
      color: #333;
      display: flex;
      align-items: center;
      min-height: 22px;
    }

    .detail-label {
      width: 122px;
      font-weight: bold;
      color: #2c3e50;
      
    }

    .detail-value {
      flex: 1;
      font-weight: 600;
      color: #000;
      padding-left: 5px;
      white-space: nowrap;
 
      width: 100%;
     
    }

    /* Modules Description Table */
    .modules-description-area {
      position: absolute;
      top: 120mm;
      left: 15mm;
      right: 15mm;
    }

    .modules-description-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 12px;
      background: rgba(255, 255, 255, 0.95);
      border: 2px solid #000;
      margin-bottom: 10px;
    }

    .modules-description-table th {
      background: linear-gradient(135deg, #2e8b57, #3cb371);
      color: white;
      padding: 8px;
      text-align: left;
      border: 1px solid #000;
      font-weight: bold;
    }

    .modules-description-table td {
      padding: 6px 8px;
      text-align: left;
      border: 1px solid #000;
      vertical-align: top;
    }

    .module-number {
      font-weight: bold;
      width: 50px;
      text-align: center;
    }

    .module-name-col {
      font-weight: bold;
      color: #2c3e50;
      width: 200px;
    }

    .module-desc-col {
      color: #333;
    }

    /* Marksheet Title */
    .marksheet-title {
      position: absolute;
      top:172mm;
      left: 0;
      right: 0;
      text-align: center;
      font-size: 22px;
      font-weight: bold;
      color: #2e8b57;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    /* Marks Table Area */
    .marks-table-area {
      position: absolute;
      top: 179mm;
      left: 15mm;
      right: 15mm;
    }

    .marks-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 11px;
      background: rgba(255, 255, 255, 0.95);
      border: 2px solid #000;
    }

    .marks-table thead {
      background: linear-gradient(135deg, #2c3e50, #4a6491);
      color: white;
    }

    .marks-table th {
      padding: 6px 4px;
      text-align: center;
      border: 1px solid #000;
      font-weight: bold;
      vertical-align: middle;
    }

    .marks-table td {
      padding: 5px 4px;
      text-align: center;
      border: 1px solid #000;
      vertical-align: middle;
    }

    .module-cell {
      text-align: left;
      font-weight: bold;
      color: #2c3e50;
      padding-left: 8px;
      min-width: 120px;
    }

    .marks-table tbody tr:nth-child(even) {
      background-color: rgba(240, 240, 240, 0.7);
    }

    .total-row {
      background: linear-gradient(135deg, #e8e8e8, #d4d4d4) !important;
      font-weight: bold;
      color: #000;
    }

    .total-row td {
      border-top: 2px solid #000;
      font-size: 12px;
    }

    /* Performance Summary */
   .performance-summary {
  position: absolute;
  top: 232mm;
  left: 109mm;

  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 2px; /* reduced */
  text-align: center;
}

.summary-box {
  padding: 6px 5px; /* reduced */
  background: linear-gradient(135deg, #ffffff, #f8f9fa);
  border: 2px solid #2e8b57;
  border-radius: 6px; /* reduced */
  min-width: 65px; /* added */
}

.summary-value {
  font-size: 14px; /* reduced */
  font-weight: bold;
  color: #2c3e50;
  margin: 2px 0; /* reduced */
}

.summary-label {
  font-size: 9px; /* reduced */
  color: #2e8b57;
  font-weight: 600;
  text-transform: uppercase;
}

.grade-box {
  padding: 6px 5px; /* reduced */
  background: linear-gradient(135deg, #28a745, #2e8b57);
  border: 2px solid #218838;
  border-radius: 6px; /* reduced */
  min-width: 65px;
}

.grade-value {
  font-size: 16px; /* reduced */
  font-weight: bold;
  color: white;
  margin: 2px 0;
}

.grade-box .summary-label {
  color: rgba(255, 255, 255, 0.9);
}

.result-box {
  padding: 6px 5px; /* reduced */
  background: linear-gradient(
    135deg,
    <?php echo $overall_percentage >= 40 ? '#28a745' : '#dc3545'; ?>,
    <?php echo $overall_percentage >= 40 ? '#218838' : '#c82333'; ?>
  );
  border: 2px solid <?php echo $overall_percentage >= 40 ? '#1e7e34' : '#bd2130'; ?>;
  border-radius: 6px; /* reduced */
  min-width: 65px;
}

.result-box .summary-value {
  color: white;
  font-size: 16px; /* reduced */
}

.result-box .summary-label {
  color: rgba(255, 255, 255, 0.9);
}
    /* QR Code Section */
    .qr-section {
     position: absolute;
    bottom: 40mm;
    left: 9mm;
    top: 227mm;
    }

    .qr-container {
      text-align: center;
    }

    .qr-title {
      font-size: 14px;
      font-weight: bold;
      color: #2c3e50;
      margin-bottom: 8px;
      text-transform: uppercase;
    }

    .qr-code {
      width: 98px;
      height: 98px;
      margin: 0 auto;
      background: white;
      padding: 5px;
      border: 1px solid #000;
    }

    .qr-note {
      font-size: 10px;
      color: #666;
      margin-top: 5px;
      font-style: italic;
    }

    /* Footer Signatures */
    .footer-signatures {
      position: absolute;
      bottom: 25mm;
      left: 15mm;
      right: 15mm;
      display: flex;
      justify-content: space-between;
    }

    .signature-box {
      text-align: center;
      width: 30%;
    }

    .signature-line {
      width: 150px;
      border-top: 2px solid #000;
      margin: 25px auto 5px;
    }

    .signature-name {
      font-size: 11px;
      font-weight: bold;
      color: #2c3e50;
      text-transform: uppercase;
    }

    /* Print Button */
    .print-button {
      margin: 20px auto;
      padding: 12px 40px;
      font-size: 16px;
      background: linear-gradient(135deg, #004080, #0066cc);
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      display: block;
      transition: all 0.3s ease;
    }

    .print-button:hover {
      background: linear-gradient(135deg, #002d5a, #004080);
    }

    /* Center info */
    .center-info {
      position: absolute;
      top: 109mm;
      left: 7%;
      font-size: 15px;
      color: #000;
      font-weight: bold;
      z-index: 5;
      min-width: 290px;
    }

    /* Screen preview */
    @media screen {
      .marksheet-container {
        transform: scale(0.85);
        transform-origin: top center;
      }
    }

    /* Grade colors */
    .grade-A { color: #28a745 !important; }
    .grade-B { color: #17a2b8 !important; }
    .grade-C { color: #ffc107 !important; }
    .grade-D { color: #fd7e14 !important; }
    .grade-F { color: #dc3545 !important; }

    /* PRINT STYLES - FIXED */
  /* PRINT STYLES - UPDATED TO FIX GRADE & RESULT BOXES */
@media print {
  @page {
    size: A4 portrait;
    margin: 0;
  }

  body {
    margin: 0;
    padding: 0;
    background: none;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .marksheet-container {
    width: 210mm;
    height: 297mm;
    margin: 0;
    box-shadow: none;
    background-image: url('images/Certificate_idcard/marksheet_final.jpg') !important;
    background-size: 100% 100% !important;
    background-repeat: no-repeat !important;
    background-position: center !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .print-button {
    display: none !important;
  }

  /* CRITICAL FIX FOR GRADE BOX - Use solid color with opacity */
  .grade-box {
    background: #28a745 !important;
    background-color: #28a745 !important;
    border: 2px solid #218838 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    color-adjust: exact !important;
  }
  
  .grade-value {
    color: white !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    color-adjust: exact !important;
    text-shadow: none !important;
  }
  
  .grade-box .summary-label {
    color: rgba(255, 255, 255, 0.9) !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    color-adjust: exact !important;
  }
  
  /* CRITICAL FIX FOR RESULT BOX - Use solid color with opacity */
  .result-box {
    background: <?php echo $overall_percentage >= 40 ? '#28a745' : '#dc3545'; ?> !important;
    background-color: <?php echo $overall_percentage >= 40 ? '#28a745' : '#dc3545'; ?> !important;
    border: 2px solid <?php echo $overall_percentage >= 40 ? '#1e7e34' : '#bd2130'; ?> !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    color-adjust: exact !important;
  }
  
  .result-box .summary-value {
    color: white !important;
    font-size: 20px !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    color-adjust: exact !important;
    text-shadow: none !important;
  }
  
  .result-box .summary-label {
    color: rgba(255, 255, 255, 0.9) !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    color-adjust: exact !important;
  }

  

  /* Force gradients to print as solid colors */
  .modules-description-table th {
    background: #2e8b57 !important;
    background-color: #2e8b57 !important;
    color: white !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    color-adjust: exact !important;
  }

  .marks-table thead {
    background: #2c3e50 !important;
    background-color: #2c3e50 !important;
    color: white !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    color-adjust: exact !important;
  }

  .total-row {
    background: #e8e8e8 !important;
    background-color: #e8e8e8 !important;
    color: #000 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    color-adjust: exact !important;
  }

  /* Force ALL elements to print with colors */
  * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    color-adjust: exact !important;
  }
}
  </style>
</head>
<body>

<div class="marksheet-container">
  <!-- Student ID and Code at top -->
  <div class="student-top-info">
    <div class="student-id-code">
      <div class="id-code-item">
     <div class="id-code-value"><?php echo htmlspecialchars($student['student_code']); ?></div>
      </div>
      <div class="id-code-item">
         <div class="id-code-value"><?php echo $student_id; ?> </div>
      </div>
    </div>
  </div>

  <!-- Issue Date at top center -->
  <div class="issue-date-top">
    Issue Date: <?php echo date('d/m/Y', strtotime($student['exam_date'] ?? date('Y-m-d'))); ?>
  </div>

  <!-- Student Photo -->
  <div class="photo-area">
    <?php 
      $photo = !empty($student['photo']) ? $student['photo'] : 'default.png';
      $photo_path = "../uploads/students/photos/" . $photo;
      if (file_exists($photo_path)) {
        echo '<img src="' . $photo_path . '" alt="Student Photo" class="student-photo">';
      } else {
        echo '<div style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; color:#666; font-size:12px; text-align:center; padding:5px;">
                <span style="font-size:14px; font-weight:bold;">PHOTO</span>
                <span style="font-size:10px;">Not Available</span>
              </div>';
      }
    ?>
  </div>

  <!-- Student Details -->
  <div class="student-info-container">
    <div class="detail-row">
      <span class="detail-label">Student Name:</span>
      <span class="detail-value"><?php echo htmlspecialchars($student['name']); ?></span>
    </div>
    <div class="detail-row">
      <span class="detail-label">Father's Name:</span>
      <span class="detail-value"><?php echo htmlspecialchars($student['father_name']); ?></span>
    </div>
    <div class="detail-row">
      <span class="detail-label">Mother's Name:</span>
      <span class="detail-value"><?php echo htmlspecialchars($student['mother_name']); ?></span>
    </div>
    <div class="detail-row">
      <span class="detail-label">Date of Birth:</span>
      <span class="detail-value"><?php echo htmlspecialchars($student['dob']); ?></span>
    </div>
    <div class="detail-row">
      <span class="detail-label">Course Name:</span>
      <span class="detail-value"><?php echo htmlspecialchars($student['course_name_full'] ?? $student['course_name']); ?></span>
    </div>
    <div class="detail-row">
      <span class="detail-label">Duration:</span>
      <span class="detail-value"><?php echo htmlspecialchars($student['duration'] ?? 'N/A'); ?></span>
    </div>
  </div>
    <div class="center-info">
    <span class="detail-label gap-4">Center name:</span> <?php echo htmlspecialchars($student['study_center'] ?? 'N/A'); ?>
  </div>

  <!-- Modules Description Table -->
  <div class="modules-description-area">
    <table class="modules-description-table">
      <thead>
        <tr>
          <th colspan="3">Modules Covered</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $row_count = 0;
        foreach ($modules_marks as $module): 
          $row_count++;
        ?>
        <tr>
          <td class="module-number"><?php echo $row_count; ?>.</td>
          <td class="module-name-col"><?php echo htmlspecialchars($module['module_name']); ?></td>
          <td class="module-desc-col"><?php echo htmlspecialchars($module['description'] ?? 'Written, Practical, Project & Viva Examination'); ?></td>
        </tr>
        <?php endforeach; ?>
        
        <?php 
        // Show only actual modules, no empty rows
        if (empty($modules_marks)): 
        ?>
        <tr>
          <td colspan="3" style="text-align:center;">No modules found</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Marksheet Title -->
  <div class="marksheet-title">MARKS DETAILS</div>

  <!-- Marks Table -->
 <div class="marks-table-area">
    <table class="marks-table">
      <thead>
        <tr>
          <th rowspan="2">Module</th>
          <th colspan="2">Written</th>
          <th colspan="2">Practical</th>
          <th colspan="2">Project</th>
          <th colspan="2">Viva</th>
          <th rowspan="2">Total</th>
          <th rowspan="2">%</th>
          <th rowspan="2">Grade</th>
        </tr>
        <tr>
          <th>Max</th>
          <th>Obt</th>
          <th>Max</th>
          <th>Obt</th>
          <th>Max</th>
          <th>Obt</th>
          <th>Max</th>
          <th>Obt</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $row_count = 0;
        foreach ($modules_marks as $module): 
          $row_count++;
          // Get max marks for this module
          $max_stmt = $conn->prepare("SELECT max_written_marks, max_practical_marks FROM modules WHERE id = ?");
          $max_stmt->bind_param("i", $module['module_id']);
          $max_stmt->execute();
          $max_result = $max_stmt->get_result();
          $max_data = $max_result->fetch_assoc();
          
          // CHANGED: Add 100 instead of 200 (50+50 for project and viva instead of 100+100)
          $module_total_max = $max_data['max_written_marks'] + $max_data['max_practical_marks'] + 100;
          $module_percentage = $module_total_max > 0 ? ($module['total_marks'] / $module_total_max) * 100 : 0;
        ?>
        <tr>
          <td class="module-cell"><?php echo $row_count . '. ' . htmlspecialchars($module['module_name']); ?></td>
          <!-- Written: Max first, then Obt -->
          <td><?php echo $max_data['max_written_marks']; ?></td>
          <td><?php echo $module['written_marks']; ?></td>
          <!-- Practical: Max first, then Obt -->
          <td><?php echo $max_data['max_practical_marks']; ?></td>
          <td><?php echo $module['practical_marks']; ?></td>
          <!-- Project: Max first, then Obt -->
          <td>50</td>
          <td><?php echo $module['project_marks']; ?></td>
          <!-- Viva: Max first, then Obt -->
          <td>50</td>
          <td><?php echo $module['viva_marks']; ?></td>
          <td><strong><?php echo $module['total_marks']; ?></strong></td>
          <td><strong><?php echo number_format($module_percentage, 1); ?>%</strong></td>
          <td>
            <span style="font-weight:bold;" class="grade-<?php echo substr($module['grade'], 0, 1); ?>">
              <?php echo $module['grade']; ?>
            </span>
          </td>
        </tr>
        <?php 
          $max_stmt->close();
        endforeach; 
        
        // Show only actual modules, no empty rows
        if (!empty($modules_marks)): 
        ?>
        <tr class="total-row">
          <td><strong>GRAND TOTAL</strong></td>
          <!-- Written totals -->
          <td></td>
          <td><strong><?php echo $total_written; ?></strong></td>
          <!-- Practical totals -->
          <td></td>
          <td><strong><?php echo $total_practical; ?></strong></td>
          <!-- Project totals -->
          <td></td>
          <td><strong><?php echo $total_project; ?></strong></td>
          <!-- Viva totals -->
          <td></td>
          <td><strong><?php echo $total_viva; ?></strong></td>
          <td><strong><?php echo $grand_total; ?></strong></td>
          <!-- CHANGED: Use calculated overall percentage -->
          <td><strong><?php echo number_format($overall_percentage, 1); ?>%</strong></td>
          <td>
            <span style="font-weight:bold;" class="grade-<?php echo substr($student['grade'] ?? 'N', 0, 1); ?>">
              <?php echo htmlspecialchars($student['grade'] ?? 'N/A'); ?>
            </span>
          </td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Performance Summary -->
  <div class="performance-summary">
    <div class="summary-box">
      <div class="summary-value"><?php echo $grand_total; ?>/<?php echo $grand_max; ?></div>
      <div class="summary-label">Total Marks</div>
    </div>
    <div class="summary-box">
      <!-- CHANGED: Use calculated overall percentage -->
      <div class="summary-value"><?php echo number_format($overall_percentage, 1); ?>%</div>
      <div class="summary-label">Percentage</div>
    </div>
    <div class="grade-box">
      <div class="grade-value"><?php echo htmlspecialchars($student['grade'] ?? '-'); ?></div>
      <div class="summary-label">Final Grade</div>
    </div>
    <div class="result-box">
      <div class="summary-value"><?php echo $overall_percentage >= 40 ? 'PASS' : 'FAIL'; ?></div>
      <div class="summary-label">Result</div>
    </div>
  </div>

  <!-- QR Code Section -->
 <div class="qr-section">
  <div class="qr-container">
    <div class="qr-code">
      <?php
      $qr_size = 120;
      
      $qr_urls = [
        "https://api.qrserver.com/v1/create-qr-code/?size={$qr_size}x{$qr_size}&data=" . urlencode($qr_data),
        "https://quickchart.io/qr?text=" . urlencode($qr_data) . "&size={$qr_size}",
        "https://chart.googleapis.com/chart?cht=qr&chs={$qr_size}x{$qr_size}&chl=" . urlencode($qr_data) . "&choe=UTF-8"
      ];
      
      $qr_url = $qr_urls[0];
      
      echo "<img src='" . htmlspecialchars($qr_url) . "' alt='QR Code' style='width:100%; height:100%;' onerror=\"this.onerror=null; this.src='" . htmlspecialchars($qr_urls[1]) . "';\" onerror2=\"this.onerror=null; this.src='" . htmlspecialchars($qr_urls[2]) . "';\">";
      ?>
    </div>
    <div class="qr-note">Scan to verify authenticity</div>
  </div>
</div>

 
</div>

<button class="print-button" onclick="window.print()">Print Marksheet</button>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const container = document.querySelector('.marksheet-container');
    const bgImage = new Image();
    bgImage.src = 'images/Certificate_idcard/marksheet_final.jpg';
    
    bgImage.onload = function() {
      container.style.backgroundImage = `url('${bgImage.src}')`;
    };
    
    bgImage.onerror = function() {
      console.error('Background image failed to load');
      container.style.background = '#ffffff';
    };
    
    const printBtn = document.querySelector('.print-button');
    printBtn.addEventListener('click', function() {
      const printStyle = document.createElement('style');
      printStyle.innerHTML = `
        @media print {
          .grade-box {
            background: #28a745 !important;
            border: 2px solid #218838 !important;
          }
          
          .grade-value {
            color: white !important;
          }
          
          .grade-box .summary-label {
            color: rgba(255, 255, 255, 0.9) !important;
          }
          
          .result-box {
            background: <?php echo $overall_percentage >= 40 ? '#28a745' : '#dc3545'; ?> !important;
            border: 2px solid <?php echo $overall_percentage >= 40 ? '#1e7e34' : '#bd2130'; ?> !important;
          }
          
          .result-box .summary-value {
            color: white !important;
          }
          
          .result-box .summary-label {
            color: rgba(255, 255, 255, 0.9) !important;
          }
        }
      `;
      document.head.appendChild(printStyle);
      
      setTimeout(() => {
        window.print();
        
        setTimeout(() => {
          document.head.removeChild(printStyle);
        }, 1000);
      }, 100);
    });
  });
</script>

</body>
</html>