<?php
include 'conn.php';

// Get student ID from URL
$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($student_id === 0) {
    header("Location: students.php");
    exit();
}

// Get student details
$stmt = $conn->prepare("SELECT * FROM onlinestudents WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student_result = $stmt->get_result();

if ($student_result->num_rows === 0) {
    echo "<script>alert('Student not found.'); window.location.href='students.php';</script>";
    exit();
}

$student = $student_result->fetch_assoc();

// Check if student is approved
if ($student['approved'] != 1) {
    echo "<script>alert('Student not approved yet.'); window.location.href='students.php';</script>";
    exit();
}

$student_code = $student['student_code'];
$course_name = $student['course_name'];

// Get course_id
$course_id = $student['course_id'] ?? 0;
if ($course_id === 0 && !empty($course_name)) {
    $course_stmt = $conn->prepare("SELECT id FROM courses WHERE course_name = ? LIMIT 1");
    $course_stmt->bind_param("s", $course_name);
    $course_stmt->execute();
    $course_result = $course_stmt->get_result();
    if ($course_result->num_rows > 0) {
        $course = $course_result->fetch_assoc();
        $course_id = $course['id'];
    }
}

// Get all modules for this course
$modules = [];
if ($course_id > 0) {
    $module_stmt = $conn->prepare("SELECT id, module_code, module_name, max_written_marks, description, max_practical_marks FROM modules WHERE course_id = ? AND status = 'active' ORDER BY order_number, module_name");
    $module_stmt->bind_param("i", $course_id);
    $module_stmt->execute();
    $module_result = $module_stmt->get_result();
    
    while ($module = $module_result->fetch_assoc()) {
        $modules[] = $module;
    }
}

// Get existing marks for this student
$existing_marks = [];
if ($student_id > 0) {
    $marks_stmt = $conn->prepare("SELECT * FROM student_module_marks WHERE student_id = ?");
    $marks_stmt->bind_param("i", $student_id);
    $marks_stmt->execute();
    $marks_result = $marks_stmt->get_result();
    
    while ($mark = $marks_result->fetch_assoc()) {
        $existing_marks[$mark['module_id']] = $mark;
    }
}

// Handle form submission for adding marks
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    $success = '';
    
    if (isset($_POST['save_marks'])) {
        // Save module-wise marks
        $conn->begin_transaction();
        
        try {
            // Loop through submitted modules
            if (isset($_POST['module_marks'])) {
                foreach ($_POST['module_marks'] as $module_id => $marks) {
                    $module_id = intval($module_id);
                    
                    // Get module max marks
                    $mod_stmt = $conn->prepare("SELECT max_written_marks, max_practical_marks FROM modules WHERE id = ?");
                    $mod_stmt->bind_param("i", $module_id);
                    $mod_stmt->execute();
                    $mod_result = $mod_stmt->get_result();
                    
                    if ($mod_row = $mod_result->fetch_assoc()) {
                        $max_written = $mod_row['max_written_marks'];
                        $max_practical = $mod_row['max_practical_marks'];
                        
                        // Validate marks
                        $written_marks = intval($marks['written'] ?? 0);
                        $practical_marks = intval($marks['practical'] ?? 0);
                        $project_marks = intval($marks['project'] ?? 0);
                        $viva_marks = intval($marks['viva'] ?? 0);
                        $exam_date = $conn->real_escape_string($marks['exam_date'] ?? date('Y-m-d'));
                        
                        if ($written_marks > $max_written || $written_marks < 0) {
                            throw new Exception("Written marks for module must be between 0 and $max_written");
                        }
                        
                        if ($practical_marks > $max_practical || $practical_marks < 0) {
                            throw new Exception("Practical marks for module must be between 0 and $max_practical");
                        }
                        
                        // Project marks maximum is 50
                        if ($project_marks > 50 || $project_marks < 0) {
                            throw new Exception("Project marks must be between 0 and 50");
                        }
                        
                        // Viva marks maximum is 50
                        if ($viva_marks > 50 || $viva_marks < 0) {
                            throw new Exception("Viva marks must be between 0 and 50");
                        }
                        
                        // Calculate total and percentage
                        $total_marks = $written_marks + $practical_marks + $project_marks + $viva_marks;
                        // Total max: written + practical + 50 + 50
                        $total_max_marks = $max_written + $max_practical + 100;
                        $percentage = $total_max_marks > 0 ? ($total_marks / $total_max_marks) * 100 : 0;
                        
                        // Determine grade
                        if ($percentage >= 90) $grade = 'A+';
                        elseif ($percentage >= 80) $grade = 'A';
                        elseif ($percentage >= 70) $grade = 'B+';
                        elseif ($percentage >= 60) $grade = 'B';
                        elseif ($percentage >= 50) $grade = 'C';
                        elseif ($percentage >= 40) $grade = 'D';
                        else $grade = 'F';
                        
                        // Check if marks already exist
                        if (isset($existing_marks[$module_id])) {
                            // Update existing marks
                            $update_stmt = $conn->prepare("UPDATE student_module_marks SET 
                                written_marks = ?,
                                practical_marks = ?,
                                project_marks = ?,
                                viva_marks = ?,
                                total_marks = ?,
                                percentage = ?,
                                grade = ?,
                                exam_date = ?
                                WHERE student_id = ? AND module_id = ?");
                            
                            $update_stmt->bind_param(
                                "iiiiisssii",
                                $written_marks,
                                $practical_marks,
                                $project_marks,
                                $viva_marks,
                                $total_marks,
                                $percentage,
                                $grade,
                                $exam_date,
                                $student_id,
                                $module_id
                            );
                            $update_stmt->execute();
                            $update_stmt->close();
                        } else {
                            // Insert new marks
                            $insert_stmt = $conn->prepare("INSERT INTO student_module_marks 
                                (student_id, module_id, written_marks, practical_marks, 
                                project_marks, viva_marks, total_marks, percentage, grade, exam_date) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            
                            $insert_stmt->bind_param(
                                "iiiiiissss",
                                $student_id,
                                $module_id,
                                $written_marks,
                                $practical_marks,
                                $project_marks,
                                $viva_marks,
                                $total_marks,
                                $percentage,
                                $grade,
                                $exam_date
                            );
                            $insert_stmt->execute();
                            $insert_stmt->close();
                        }
                    }
                }
                
                // Calculate overall performance
                $overall_stmt = $conn->prepare("
                    SELECT 
                        SUM(written_marks) as total_written,
                        SUM(practical_marks) as total_practical,
                        SUM(project_marks) as total_project,
                        SUM(viva_marks) as total_viva,
                        SUM(total_marks) as total_obtained,
                        AVG(percentage) as avg_percentage
                    FROM student_module_marks 
                    WHERE student_id = ?
                ");
                $overall_stmt->bind_param("i", $student_id);
                $overall_stmt->execute();
                $overall_result = $overall_stmt->get_result();
                $overall_data = $overall_result->fetch_assoc();
                
                $total_max_all = 0;
                foreach ($modules as $module) {
                    // Total max for all modules: sum of (written + practical + 50 + 50)
                    $total_max_all += $module['max_written_marks'] + $module['max_practical_marks'] + 100;
                }
                
                $overall_percentage = $total_max_all > 0 ? 
                    ($overall_data['total_obtained'] / $total_max_all) * 100 : 0;
                
                // Determine overall grade
                if ($overall_percentage >= 90) $overall_grade = 'A+';
                elseif ($overall_percentage >= 80) $overall_grade = 'A';
                elseif ($overall_percentage >= 70) $overall_grade = 'B+';
                elseif ($overall_percentage >= 60) $overall_grade = 'B';
                elseif ($overall_percentage >= 50) $overall_grade = 'C';
                elseif ($overall_percentage >= 40) $overall_grade = 'D';
                else $overall_grade = 'F';
                
                // Update or insert in certificates table
                $cert_check = $conn->prepare("SELECT id FROM certificates WHERE student_id = ?");
                $cert_check->bind_param("i", $student_id);
                $cert_check->execute();
                $cert_result = $cert_check->get_result();
                
                if ($cert_result->num_rows > 0) {
                    // Update existing certificate - REMOVED updated_at column
                    $cert_update = $conn->prepare("UPDATE certificates SET 
                        written_marks = ?,
                        practical_marks = ?,
                        project_marks = ?,
                        viva_marks = ?,
                        percentage = ?,
                        grade = ?
                        WHERE student_id = ?");
                    
                    $cert_update->bind_param(
                        "iiiissi",
                        $overall_data['total_written'],
                        $overall_data['total_practical'],
                        $overall_data['total_project'],
                        $overall_data['total_viva'],
                        $overall_percentage,
                        $overall_grade,
                        $student_id
                    );
                    $cert_update->execute();
                    $cert_update->close();
                } else {
                    // Insert new certificate
                    $cert_insert = $conn->prepare("INSERT INTO certificates 
                        (student_id, student_code, name, dob, father_name, mother_name,
                        course_name, duration, study_center, written_marks, practical_marks,
                        project_marks, viva_marks, percentage, grade)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    
                    $cert_insert->bind_param(
                        "isssssssiiiiiss",
                        $student_id,
                        $student['student_code'],
                        $student['name'],
                        $student['dob'],
                        $student['father_name'],
                        $student['mother_name'],
                        $student['course_name'],
                        $student['duration'],
                        $student['study_center'],
                        $overall_data['total_written'],
                        $overall_data['total_practical'],
                        $overall_data['total_project'],
                        $overall_data['total_viva'],
                        $overall_percentage,
                        $overall_grade
                    );
                    $cert_insert->execute();
                    $cert_insert->close();
                }
                
                // Update student's grade
                $grade_update = $conn->prepare("UPDATE onlinestudents SET grade = ? WHERE id = ?");
                $grade_update->bind_param("si", $overall_grade, $student_id);
                $grade_update->execute();
                $grade_update->close();
                
                $conn->commit();
                $success = "Marks saved successfully! Overall Percentage: " . 
                          number_format($overall_percentage, 2) . "%, Grade: $overall_grade";
                
                // Refresh existing marks
                $marks_stmt->execute();
                $marks_result = $marks_stmt->get_result();
                $existing_marks = [];
                while ($mark = $marks_result->fetch_assoc()) {
                    $existing_marks[$mark['module_id']] = $mark;
                }
                
                // Refresh student data
                $student['grade'] = $overall_grade;
                
            } else {
                throw new Exception("No marks data submitted");
            }
            
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = $e->getMessage();
        }
    }
}

// Get overall certificate data
$certificate = null;
$cert_stmt = $conn->prepare("SELECT * FROM certificates WHERE student_id = ?");
$cert_stmt->bind_param("i", $student_id);
$cert_stmt->execute();
$cert_result = $cert_stmt->get_result();
if ($cert_result->num_rows > 0) {
    $certificate = $cert_result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student Marks</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/admin-standard.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        .module-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        .module-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .module-header {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 12px 20px;
            border-radius: 8px 8px 0 0;
        }
        .marks-input {
            text-align: center;
            font-weight: 600;
        }
        .max-marks-badge {
            background: #e9ecef;
            color: #495057;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.85rem;
        }
        .performance-summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .grade-badge {
            font-size: 1.5rem;
            padding: 10px 20px;
            border-radius: 8px;
        }
        .student-info-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .info-label {
            color: #6c757d;
            font-weight: 500;
        }
        .info-value {
            color: #2c3e50;
            font-weight: 600;
            font-size: 1.1rem;
        }
        .table-modules th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .alert {
            border-radius: 8px;
            border: none;
        }
        .project-viva-box {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            background: #f8f9fa;
        }
        .project-viva-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
        }
        .result-display {
            margin-top: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 6px;
        }
        .badge-success { background-color: #28a745 !important; }
        .badge-info { background-color: #17a2b8 !important; }
        .badge-warning { background-color: #ffc107 !important; }
        .badge-danger { background-color: #dc3545 !important; }
        .badge-secondary { background-color: #6c757d !important; }
    </style>
</head>
<body>

    <div id="preloader">
        <div class="loader"></div>
    </div>

    <div id="main-wrapper">
        <?php include 'menu.php'; ?>
        
        <div class="content-body">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <h2 class="mb-4"><i class="fas fa-chart-line me-2"></i> Add Student Marks</h2>
                        
                        <!-- Error Messages -->
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <h5><i class="fas fa-exclamation-circle me-2"></i> Errors Found:</h5>
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Success Message -->
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-check-circle me-3 fs-4"></i>
                                    <div><?php echo htmlspecialchars($success); ?></div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Student Information -->
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i> Student Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <div class="info-label">Student ID</div>
                                        <div class="info-value"><?php echo $student_id; ?></div>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <div class="info-label">Student Code</div>
                                        <div class="info-value"><?php echo htmlspecialchars($student['student_code']); ?></div>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <div class="info-label">Name</div>
                                        <div class="info-value"><?php echo htmlspecialchars($student['name']); ?></div>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <div class="info-label">Course</div>
                                        <div class="info-value"><?php echo htmlspecialchars($student['course_name']); ?></div>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <div class="info-label">Total Modules</div>
                                        <div class="info-value"><?php echo count($modules); ?></div>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <div class="info-label">Current Grade</div>
                                        <div class="info-value">
                                            <?php 
                                            $grade = $student['grade'] ?? 'Not Assigned';
                                            $badge_class = 'secondary';
                                            if ($grade == 'A+' || $grade == 'A') $badge_class = 'success';
                                            elseif ($grade == 'B+' || $grade == 'B') $badge_class = 'info';
                                            elseif ($grade == 'C' || $grade == 'D') $badge_class = 'warning';
                                            elseif ($grade == 'F') $badge_class = 'danger';
                                            ?>
                                            <span class="badge bg-<?php echo $badge_class; ?>">
                                                <?php echo htmlspecialchars($grade); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (empty($modules)): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                No modules found for this course. Please add modules first.
                            </div>
                        <?php else: ?>
                        
                        <!-- Module-wise Marks Form -->
                        <form method="POST" action="" id="marksForm">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-book me-2"></i> Enter Module-wise Marks</h5>
                                    <small class="text-muted">Note: Project and Viva marks are out of 50 each</small>
                                </div>
                                <div class="card-body">
                                    <?php foreach ($modules as $index => $module): 
                                        $existing = $existing_marks[$module['id']] ?? null;
                                    ?>
                                    <div class="module-card" data-module-id="<?php echo $module['id']; ?>">
                                        <div class="module-header">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h5 class="mb-0">Module <?php echo $index + 1; ?>: <?php echo htmlspecialchars($module['description']); ?></h5>
                                                <div>
                                                    <span class="max-marks-badge">Written: <?php echo $module['max_written_marks']; ?></span>
                                                    <span class="max-marks-badge ms-2">Practical: <?php echo $module['max_practical_marks']; ?></span>
                                                    <span class="max-marks-badge ms-2 bg-warning">Project: 50</span>
                                                    <span class="max-marks-badge ms-2 bg-warning">Viva: 50</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label fw-bold">Exam Date</label>
                                                    <input type="date" 
                                                           class="form-control exam-date" 
                                                           name="module_marks[<?php echo $module['id']; ?>][exam_date]"
                                                           value="<?php echo $existing['exam_date'] ?? date('Y-m-d'); ?>"
                                                           required>
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label fw-bold">Written Marks</label>
                                                    <input type="number" 
                                                           class="form-control marks-input written-marks" 
                                                           name="module_marks[<?php echo $module['id']; ?>][written]"
                                                           min="0" 
                                                           max="<?php echo $module['max_written_marks']; ?>"
                                                           value="<?php echo $existing['written_marks'] ?? 0; ?>"
                                                           required>
                                                    <small class="text-muted">Max: <?php echo $module['max_written_marks']; ?></small>
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label fw-bold">Practical Marks</label>
                                                    <input type="number" 
                                                           class="form-control marks-input practical-marks" 
                                                           name="module_marks[<?php echo $module['id']; ?>][practical]"
                                                           min="0" 
                                                           max="<?php echo $module['max_practical_marks']; ?>"
                                                           value="<?php echo $existing['practical_marks'] ?? 0; ?>"
                                                           required>
                                                    <small class="text-muted">Max: <?php echo $module['max_practical_marks']; ?></small>
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <div class="project-viva-box">
                                                        <div class="row">
                                                            <div class="col-6 mb-2">
                                                                <div class="project-viva-label">Project Marks</div>
                                                                <input type="number" 
                                                                       class="form-control marks-input project-marks" 
                                                                       name="module_marks[<?php echo $module['id']; ?>][project]"
                                                                       min="0" 
                                                                       max="50"
                                                                       value="<?php echo $existing['project_marks'] ?? 0; ?>"
                                                                       required>
                                                                <small class="text-muted d-block mt-1">Max: 50</small>
                                                            </div>
                                                            <div class="col-6 mb-2">
                                                                <div class="project-viva-label">Viva Marks</div>
                                                                <input type="number" 
                                                                       class="form-control marks-input viva-marks" 
                                                                       name="module_marks[<?php echo $module['id']; ?>][viva]"
                                                                       min="0" 
                                                                       max="50"
                                                                       value="<?php echo $existing['viva_marks'] ?? 0; ?>"
                                                                       required>
                                                                <small class="text-muted d-block mt-1">Max: 50</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="result-display">
                                                <?php if ($existing): ?>
                                                    <div class="row text-center">
                                                        <div class="col">
                                                            <small>Total: <strong><?php echo $existing['total_marks']; ?></strong></small>
                                                        </div>
                                                        <div class="col">
                                                            <small>Percentage: <strong><?php echo number_format($existing['percentage'], 2); ?>%</strong></small>
                                                        </div>
                                                        <div class="col">
                                                            <small>Grade: 
                                                                <span class="badge badge-<?php 
                                                                    $g = $existing['grade'];
                                                                    if ($g == 'A+') echo 'success';
                                                                    elseif ($g == 'A') echo 'success';
                                                                    elseif ($g == 'B+') echo 'info';
                                                                    elseif ($g == 'B') echo 'info';
                                                                    elseif ($g == 'C') echo 'warning';
                                                                    elseif ($g == 'D') echo 'warning';
                                                                    elseif ($g == 'F') echo 'danger';
                                                                    else echo 'secondary';
                                                                ?>">
                                                                    <?php echo $g; ?>
                                                                </span>
                                                            </small>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="row text-center">
                                                        <div class="col">
                                                            <small>Total: <strong class="module-total">0</strong></small>
                                                        </div>
                                                        <div class="col">
                                                            <small>Percentage: <strong class="module-percentage">0%</strong></small>
                                                        </div>
                                                        <div class="col">
                                                            <small>Grade: <span class="badge badge-secondary module-grade">-</span></small>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                    
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                        <button type="submit" name="save_marks" class="btn btn-primary btn-lg">
                                            <i class="fas fa-save me-2"></i> Save All Marks
                                        </button>
                                        <a href="students.php" class="btn btn-secondary btn-lg">
                                            <i class="fas fa-arrow-left me-2"></i> Back to Students
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                        
                        <!-- Marks Summary -->
                        <?php if (!empty($existing_marks)): ?>
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-table me-2"></i> Marks Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-modules">
                                        <thead>
                                            <tr class="table-light">
                                                <th>Module</th>
                                                <th>Written</th>
                                                <th>Practical</th>
                                                <th>Project</th>
                                                <th>Viva</th>
                                                <th>Total</th>
                                                <th>Percentage</th>
                                                <th>Grade</th>
                                                <th>Exam Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $grand_total = 0;
                                            $total_max = 0;
                                            foreach ($modules as $module):
                                                $existing = $existing_marks[$module['id']] ?? null;
                                                if ($existing):
                                                    $total_max += $module['max_written_marks'] + $module['max_practical_marks'] + 100; // 50+50
                                                    $grand_total += $existing['total_marks'];
                                            ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($module['module_name']); ?></strong></td>
                                                <td><?php echo $existing['written_marks']; ?>/<?php echo $module['max_written_marks']; ?></td>
                                                <td><?php echo $existing['practical_marks']; ?>/<?php echo $module['max_practical_marks']; ?></td>
                                                <td><?php echo $existing['project_marks']; ?>/50</td>
                                                <td><?php echo $existing['viva_marks']; ?>/50</td>
                                                <td><strong><?php echo $existing['total_marks']; ?></strong></td>
                                                <td class="fw-bold text-primary"><?php echo number_format($existing['percentage'], 2); ?>%</td>
                                                <td>
                                                    <?php 
                                                    $g = $existing['grade'];
                                                    $badge_class = 'secondary';
                                                    if ($g == 'A+' || $g == 'A') $badge_class = 'success';
                                                    elseif ($g == 'B+' || $g == 'B') $badge_class = 'info';
                                                    elseif ($g == 'C' || $g == 'D') $badge_class = 'warning';
                                                    elseif ($g == 'F') $badge_class = 'danger';
                                                    ?>
                                                    <span class="badge bg-<?php echo $badge_class; ?>">
                                                        <?php echo htmlspecialchars($g); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('d-m-Y', strtotime($existing['exam_date'])); ?></td>
                                            </tr>
                                            <?php endif; endforeach; ?>
                                            
                                            <?php if ($certificate): ?>
                                            <tr class="table-active">
                                                <td colspan="5" class="text-end"><strong>GRAND TOTAL:</strong></td>
                                                <td colspan="4"><strong><?php echo $grand_total; ?>/<?php echo $total_max; ?> 
                                                    (<?php echo number_format(($grand_total/$total_max)*100, 2); ?>%)</strong></td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <?php include 'footer.php'; ?>
    </div>

    <!-- Scripts -->
    <script src="vendor/global/global.min.js"></script>
    <script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="js/custom.min.js"></script>
    <script src="js/dlabnav-init.js"></script>
    <script src="js/admin-standard.js"></script>
    
    <script>
    // Auto-calculate totals for each module
    document.addEventListener('DOMContentLoaded', function() {
        // Calculate totals when inputs change
        const inputs = document.querySelectorAll('input[type="number"], input.exam-date');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                const moduleCard = this.closest('.module-card');
                if (moduleCard) {
                    calculateModuleTotal(moduleCard);
                }
            });
        });
        
        // Initial calculation for all modules
        document.querySelectorAll('.module-card').forEach(calculateModuleTotal);
        
        // Form validation
        const form = document.getElementById('marksForm');
        form.addEventListener('submit', function(e) {
            let isValid = true;
            let errorMessage = '';
            
            // Validate all project and viva marks
            const projectInputs = document.querySelectorAll('.project-marks');
            const vivaInputs = document.querySelectorAll('.viva-marks');
            
            projectInputs.forEach(input => {
                const value = parseInt(input.value) || 0;
                if (value > 50) {
                    isValid = false;
                    errorMessage = 'Project marks cannot exceed 50';
                    input.focus();
                }
            });
            
            vivaInputs.forEach(input => {
                const value = parseInt(input.value) || 0;
                if (value > 50) {
                    isValid = false;
                    errorMessage = 'Viva marks cannot exceed 50';
                    input.focus();
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showAlert(errorMessage, 'danger');
            }
        });
    });
    
    function calculateModuleTotal(moduleCard) {
        const writtenInput = moduleCard.querySelector('.written-marks');
        const practicalInput = moduleCard.querySelector('.practical-marks');
        const projectInput = moduleCard.querySelector('.project-marks');
        const vivaInput = moduleCard.querySelector('.viva-marks');
        
        const written = parseInt(writtenInput.value) || 0;
        const practical = parseInt(practicalInput.value) || 0;
        const project = parseInt(projectInput.value) || 0;
        const viva = parseInt(vivaInput.value) || 0;
        
        // Get max marks from badges
        const maxBadges = moduleCard.querySelectorAll('.max-marks-badge');
        const maxWritten = parseInt(maxBadges[0].textContent.match(/\d+/)[0]) || 100;
        const maxPractical = parseInt(maxBadges[1].textContent.match(/\d+/)[0]) || 100;
        
        const total = written + practical + project + viva;
        // Total max: written + practical + 50 + 50 = written + practical + 100
        const totalMax = maxWritten + maxPractical + 100;
        const percentage = totalMax > 0 ? (total / totalMax) * 100 : 0;
        
        // Determine grade
        let grade = '-';
        let gradeClass = 'secondary';
        if (percentage >= 90) { grade = 'A+'; gradeClass = 'success'; }
        else if (percentage >= 80) { grade = 'A'; gradeClass = 'success'; }
        else if (percentage >= 70) { grade = 'B+'; gradeClass = 'info'; }
        else if (percentage >= 60) { grade = 'B'; gradeClass = 'info'; }
        else if (percentage >= 50) { grade = 'C'; gradeClass = 'warning'; }
        else if (percentage >= 40) { grade = 'D'; gradeClass = 'warning'; }
        else if (percentage > 0) { grade = 'F'; gradeClass = 'danger'; }
        
        // Update result display
        const resultDiv = moduleCard.querySelector('.result-display');
        if (resultDiv) {
            resultDiv.innerHTML = `
                <div class="row text-center">
                    <div class="col">
                        <small>Total: <strong class="module-total">${total}</strong></small>
                    </div>
                    <div class="col">
                        <small>Percentage: <strong class="module-percentage">${percentage.toFixed(2)}%</strong></small>
                    </div>
                    <div class="col">
                        <small>Grade: <span class="badge badge-${gradeClass} module-grade">${grade}</span></small>
                    </div>
                </div>
            `;
        }
    }
    
    // Validate project and viva marks on input
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('project-marks') || e.target.classList.contains('viva-marks')) {
            const value = parseInt(e.target.value) || 0;
            if (value > 50) {
                e.target.value = 50;
                showAlert('Maximum marks for project and viva is 50', 'warning');
            }
            if (value < 0) {
                e.target.value = 0;
            }
        }
        
        // Validate written and practical marks against their max
        if (e.target.classList.contains('written-marks')) {
            const moduleCard = e.target.closest('.module-card');
            const maxBadges = moduleCard.querySelectorAll('.max-marks-badge');
            const maxWritten = parseInt(maxBadges[0].textContent.match(/\d+/)[0]) || 100;
            const value = parseInt(e.target.value) || 0;
            if (value > maxWritten) {
                e.target.value = maxWritten;
                showAlert(`Maximum written marks is ${maxWritten}`, 'warning');
            }
        }
        
        if (e.target.classList.contains('practical-marks')) {
            const moduleCard = e.target.closest('.module-card');
            const maxBadges = moduleCard.querySelectorAll('.max-marks-badge');
            const maxPractical = parseInt(maxBadges[1].textContent.match(/\d+/)[0]) || 100;
            const value = parseInt(e.target.value) || 0;
            if (value > maxPractical) {
                e.target.value = maxPractical;
                showAlert(`Maximum practical marks is ${maxPractical}`, 'warning');
            }
        }
    });
    
    function showAlert(message, type = 'info') {
        // Create alert element
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.top = '20px';
        alertDiv.style.right = '20px';
        alertDiv.style.zIndex = '1050';
        alertDiv.style.minWidth = '300px';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 3000);
    }
    </script>
</body>
</html>