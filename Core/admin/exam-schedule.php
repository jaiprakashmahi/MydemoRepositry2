<?php
session_start();
include 'conn.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_email'])) {
    header("Location: index.php");
    exit();
}

// Get admin ID
$admin_id = $_SESSION['admin_id'] ?? 1;

// Handle form submission
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize inputs
    $exam_name = mysqli_real_escape_string($conn, $_POST['exam_name']);
    $center_ids = isset($_POST['center_ids']) ? $_POST['center_ids'] : [];
    $course_id = intval($_POST['course_id']);
    $module_id = intval($_POST['module_id']);
    $exam_date = mysqli_real_escape_string($conn, $_POST['exam_date']);
    $start_time = mysqli_real_escape_string($conn, $_POST['start_time']);
    $end_time = mysqli_real_escape_string($conn, $_POST['end_time']);
    $duration = intval($_POST['duration']);
    $total_marks = intval($_POST['total_marks']);
    $passing_marks = intval($_POST['passing_marks']);
    $instructions = mysqli_real_escape_string($conn, $_POST['instructions']);
    
    // Validate required fields
    if (empty($exam_name) || empty($center_ids) || empty($course_id) || 
        empty($module_id) || empty($exam_date) || empty($start_time) || 
        empty($end_time) || empty($total_marks) || empty($passing_marks)) {
        $error = "All required fields must be filled!";
    } elseif ($passing_marks > $total_marks) {
        $error = "Passing marks cannot be greater than total marks!";
    } else {
        // Calculate duration if not provided
        if (empty($duration)) {
            $start = strtotime($start_time);
            $end = strtotime($end_time);
            $duration = round(($end - $start) / 60);
            
            if ($duration <= 0) {
                $error = "End time must be after start time!";
            }
        }
        
        if (empty($error)) {
            // If "All Centers" is selected, get all center IDs
            if (in_array('all', $center_ids)) {
                $center_query = "SELECT id FROM center_details ";
                $center_result = $conn->query($center_query);
                $center_ids = [];
                while ($row = $center_result->fetch_assoc()) {
                    $center_ids[] = $row['id'];
                }
            }
            
            // Insert exam for each center
            $inserted_exams = [];
            $errors = [];
            
            foreach ($center_ids as $center_id) {
                $center_id = intval($center_id);
                
                // Check if center exists
                $check_center = $conn->prepare("SELECT id FROM center_details WHERE id = ? ");
                $check_center->bind_param("i", $center_id);
                $check_center->execute();
                $check_center->store_result();
                
                if ($check_center->num_rows > 0) {
                    $stmt = $conn->prepare("INSERT INTO exam_schedule (
                        exam_name, center_id, course_id, module_id, exam_date, 
                        start_time, end_time, duration_minutes, total_marks, 
                        passing_marks, instructions, created_by, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                    
                    $stmt->bind_param(
                        "siiisssiiisi",
                        $exam_name, $center_id, $course_id, $module_id, $exam_date,
                        $start_time, $end_time, $duration, $total_marks,
                        $passing_marks, $instructions, $admin_id
                    );
                    
                    if ($stmt->execute()) {
                        $exam_id = $conn->insert_id;
                        $inserted_exams[] = $exam_id;
                        
                        // Send notification to center
                        $center_name = '';
                        $center_result = $conn->query("SELECT center_name FROM center_details WHERE id = $center_id");
                        if ($center_result->num_rows > 0) {
                            $center_row = $center_result->fetch_assoc();
                            $center_name = $center_row['center_name'];
                        }
                        
                        $notification_title = "New Exam Scheduled";
                        $notification_message = "Exam '$exam_name' has been scheduled for " . date('d M Y', strtotime($exam_date)) . " at $start_time. Please prepare question paper.";
                        
                        $notify_stmt = $conn->prepare("INSERT INTO notifications (
                            notification_type, title, message, sender_id, 
                            receiver_type, receiver_id, related_id, created_at
                        ) VALUES ('Exam_Scheduled', ?, ?, ?, 'Center', ?, ?, NOW())");
                        
                        $notify_stmt->bind_param(
                            "ssiii",
                            $notification_title,
                            $notification_message,
                            $admin_id,
                            $center_id,
                            $exam_id
                        );
                        
                        $notify_stmt->execute();
                        $notify_stmt->close();
                    } else {
                        $errors[] = "Failed to schedule exam for center ID: $center_id";
                    }
                    $stmt->close();
                }
                $check_center->close();
            }
            
            if (!empty($inserted_exams)) {
                $success = "Exam scheduled successfully for " . count($inserted_exams) . " center(s)! Notifications sent.";
            } elseif (!empty($errors)) {
                $error = implode("<br>", $errors);
            } else {
                $error = "Failed to schedule exam. Please try again.";
            }
        }
    }
}

// Fetch active centers for dropdown
$centers = [];
$center_query = "SELECT id, center_name FROM center_details  ORDER BY center_name";
$center_result = $conn->query($center_query);
if ($center_result) {
    while ($row = $center_result->fetch_assoc()) {
        $centers[] = $row;
    }
}

// Fetch active courses for dropdown
$courses = [];
$course_query = "SELECT id, course_name FROM courses ORDER BY course_name";
$course_result = $conn->query($course_query);
if ($course_result) {
    while ($row = $course_result->fetch_assoc()) {
        $courses[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Exam - Sharnay Institute</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    
    <!-- Flatpickr for date/time -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="shortcut icon" type="image/png" href="images/favicon.png">
    
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --accent-color: #4cc9f0;
            --success-color: #06d6a0;
            --warning-color: #ffd166;
            --danger-color: #ef476f;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }
        
        
        
        .page-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            overflow: hidden;
            margin: 30px auto;
            max-width: 1200px;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .page-header h1 {
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .page-body {
            padding: 40px;
        }
        
        .form-section {
            background: var(--light-color);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            border-left: 5px solid var(--primary-color);
        }
        
        .section-title {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1.3rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(67, 97, 238, 0.2);
        }
        
        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 8px;
        }
        
        .required::after {
            content: " *";
            color: var(--danger-color);
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }
        
        .btn-schedule {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            font-weight: 600;
            font-size: 1.1rem;
            border-radius: 10px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-schedule:hover {
            background: linear-gradient(135deg, #3a56d4 0%, #320b8f 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.3);
        }
        
        .info-box {
            background: linear-gradient(135deg, var(--accent-color) 0%, var(--primary-color) 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .select2-container--default .select2-selection--multiple {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            min-height: 45px;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .alert-success {
            background-color: rgba(6, 214, 160, 0.2);
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }
        
        .alert-danger {
            background-color: rgba(239, 71, 111, 0.2);
            color: var(--danger-color);
            border-left: 4px solid var(--danger-color);
        }
        
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
    </style>
</head>
<body>
   
    <?php include 'menu.php'; ?>
    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-calendar-alt me-3"></i>Schedule New Exam</h1>
            <p>Create and schedule exams for centers with automatic notifications</p>
        </div>
        
        <div class="page-body">
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center">
                    <i class="fas fa-check-circle me-3 fs-4"></i>
                    <div><?php echo $success; ?></div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-3 fs-4"></i>
                    <div><?php echo $error; ?></div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="examForm" novalidate>
                <!-- Basic Information -->
                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-info-circle me-2"></i>Basic Information</h3>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label required">Exam Name</label>
                            <input type="text" class="form-control" name="exam_name" required 
                                   placeholder="e.g., Mid-Term Examination, Final Assessment"
                                   value="<?php echo isset($_POST['exam_name']) ? htmlspecialchars($_POST['exam_name']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Select Center(s)</label>
                            <select class="form-select select2-multiple" name="center_ids[]" multiple="multiple" required>
                                <option value="all">All Active Centers</option>
                                <?php foreach ($centers as $center): ?>
                                    <option value="<?php echo $center['id']; ?>"
                                        <?php echo (isset($_POST['center_ids']) && in_array($center['id'], $_POST['center_ids'])) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($center['center_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Select multiple centers or "All Active Centers"</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Select Course</label>
                            <select class="form-select" id="course_id" name="course_id" required>
                                <option value="">Select Course</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo $course['id']; ?>"
                                        <?php echo (isset($_POST['course_id']) && $_POST['course_id'] == $course['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($course['course_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Select Module</label>
                            <select class="form-select" id="module_id" name="module_id" required>
                                <option value="">First select a course</option>
                                <?php 
                                // If form was submitted and had module_id, preserve it
                                if (isset($_POST['module_id']) && $_POST['module_id'] > 0) {
                                    $module_id = intval($_POST['module_id']);
                                    $module_query = $conn->prepare("SELECT id, module_name FROM modules WHERE id = ?");
                                    $module_query->bind_param("i", $module_id);
                                    $module_query->execute();
                                    $module_result = $module_query->get_result();
                                    if ($module_result->num_rows > 0) {
                                        $module = $module_result->fetch_assoc();
                                        echo '<option value="' . $module['id'] . '" selected>' . htmlspecialchars($module['module_name']) . '</option>';
                                    }
                                    $module_query->close();
                                }
                                ?>
                            </select>
                            <div id="moduleLoading" class="mt-2" style="display: none;">
                                <small class="text-muted"><i class="fas fa-spinner fa-spin me-1"></i>Loading modules...</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Exam Date</label>
                            <input type="date" class="form-control" name="exam_date" required
                                   min="<?php echo date('Y-m-d'); ?>"
                                   value="<?php echo isset($_POST['exam_date']) ? htmlspecialchars($_POST['exam_date']) : ''; ?>">
                        </div>
                    </div>
                </div>
                
                <!-- Timing & Duration -->
                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-clock me-2"></i>Timing & Duration</h3>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Start Time</label>
                            <input type="time" class="form-control" name="start_time" required
                                   value="<?php echo isset($_POST['start_time']) ? htmlspecialchars($_POST['start_time']) : ''; ?>">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">End Time</label>
                            <input type="time" class="form-control" name="end_time" required
                                   value="<?php echo isset($_POST['end_time']) ? htmlspecialchars($_POST['end_time']) : ''; ?>">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Duration (minutes)</label>
                            <input type="number" class="form-control" name="duration" 
                                   placeholder="Auto-calculated from time"
                                   value="<?php echo isset($_POST['duration']) ? htmlspecialchars($_POST['duration']) : ''; ?>">
                        </div>
                    </div>
                </div>
                
                <!-- Marks & Instructions -->
                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-chart-line me-2"></i>Marks & Instructions</h3>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Total Marks</label>
                            <input type="number" class="form-control" name="total_marks" value="<?php echo isset($_POST['total_marks']) ? htmlspecialchars($_POST['total_marks']) : '100'; ?>" required min="1">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Passing Marks</label>
                            <input type="number" class="form-control" name="passing_marks" value="<?php echo isset($_POST['passing_marks']) ? htmlspecialchars($_POST['passing_marks']) : '35'; ?>" required min="1">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Passing Percentage</label>
                            <input type="text" class="form-control" id="passing_percentage" readonly>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label">Instructions for Centers</label>
                            <textarea class="form-control" name="instructions" rows="4" 
                                      placeholder="Provide instructions for question paper preparation, exam rules, etc."><?php echo isset($_POST['instructions']) ? htmlspecialchars($_POST['instructions']) : ''; ?></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Notification Preview -->
                <div class="info-box">
                    <h5><i class="fas fa-bell me-2"></i>Notification Preview</h5>
                    <p class="mb-2">Centers will receive this notification:</p>
                    <div class="bg-white text-dark p-3 rounded">
                        <strong>New Exam Scheduled</strong><br>
                        <span id="notificationPreview">Exam has been scheduled. Details will appear here.</span>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-schedule" id="submitBtn">
                        <i class="fas fa-calendar-plus me-2"></i>Schedule Exam & Send Notifications
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2-multiple').select2({
                theme: "classic",
                width: '100%',
                placeholder: "Select Centers",
                allowClear: true
            });
            
            // Load modules when course is selected
            $('#course_id').on('change', function() {
                const courseId = $(this).val();
                const moduleSelect = $('#module_id');
                const moduleLoading = $('#moduleLoading');
                
                if (courseId) {
                    moduleLoading.show();
                    $.ajax({
                        url: 'get_modules.php', // Create this file (see below)
                        type: 'GET',
                        data: { 
                            course_id: courseId,
                            action: 'get_modules'
                        },
                        dataType: 'json',
                        success: function(response) {
                            moduleSelect.html('<option value="">Select Module</option>');
                            if (response.success && response.modules.length > 0) {
                                $.each(response.modules, function(index, module) {
                                    moduleSelect.append(
                                        $('<option>', {
                                            value: module.id,
                                            text: module.module_name
                                        })
                                    );
                                });
                            } else {
                                moduleSelect.html('<option value="">No modules found</option>');
                            }
                            moduleLoading.hide();
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', error);
                            moduleSelect.html('<option value="">Error loading modules</option>');
                            moduleLoading.hide();
                        }
                    });
                } else {
                    moduleSelect.html('<option value="">First select a course</option>');
                    moduleSelect.prop('disabled', true);
                }
            });
            
            // Calculate passing percentage
            function calculatePercentage() {
                const total = $('input[name="total_marks"]').val();
                const passing = $('input[name="passing_marks"]').val();
                
                if (total && passing) {
                    if (parseInt(passing) > parseInt(total)) {
                        $('#passing_percentage').val('Invalid: Passing > Total');
                        return;
                    }
                    const percentage = ((passing / total) * 100).toFixed(1);
                    $('#passing_percentage').val(percentage + '%');
                } else {
                    $('#passing_percentage').val('');
                }
            }
            
            $('input[name="total_marks"], input[name="passing_marks"]').on('input', calculatePercentage);
            
            // Calculate duration from times
            function calculateDuration() {
                const startTime = $('input[name="start_time"]').val();
                const endTime = $('input[name="end_time"]').val();
                
                if (startTime && endTime) {
                    const start = new Date('2000-01-01T' + startTime + ':00');
                    const end = new Date('2000-01-01T' + endTime + ':00');
                    
                    if (end > start) {
                        const diff = Math.round((end - start) / (1000 * 60)); // in minutes
                        $('input[name="duration"]').val(diff);
                    } else {
                        $('input[name="duration"]').val('');
                    }
                }
            }
            
            $('input[name="start_time"], input[name="end_time"]').on('change', calculateDuration);
            
            // Update notification preview
            function updateNotificationPreview() {
                const examName = $('input[name="exam_name"]').val() || '[Exam Name]';
                const examDate = $('input[name="exam_date"]').val() || '[Date]';
                const startTime = $('input[name="start_time"]').val() || '[Time]';
                
                if (examDate) {
                    const dateObj = new Date(examDate);
                    const formattedDate = dateObj.toLocaleDateString('en-US', {
                        day: 'numeric',
                        month: 'short',
                        year: 'numeric'
                    });
                    
                    $('#notificationPreview').text(
                        `Exam '${examName}' has been scheduled for ${formattedDate} at ${startTime}. Please prepare question paper.`
                    );
                }
            }
            
            $('input[name="exam_name"], input[name="exam_date"], input[name="start_time"]').on('input change', updateNotificationPreview);
            
            // Form validation and submission
            $('#examForm').on('submit', function(e) {
                e.preventDefault();
                
                // Basic validation
                const startTime = $('input[name="start_time"]').val();
                const endTime = $('input[name="end_time"]').val();
                
                if (startTime && endTime) {
                    const start = new Date('2000-01-01T' + startTime + ':00');
                    const end = new Date('2000-01-01T' + endTime + ':00');
                    
                    if (end <= start) {
                        alert('End time must be after start time');
                        return false;
                    }
                }
                
                const totalMarks = parseInt($('input[name="total_marks"]').val());
                const passingMarks = parseInt($('input[name="passing_marks"]').val());
                
                if (passingMarks > totalMarks) {
                    alert('Passing marks cannot be greater than total marks');
                    return false;
                }
                
                // Disable submit button and show loading
                const submitBtn = $('#submitBtn');
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Scheduling...');
                $('#examForm').addClass('loading');
                
                // Submit the form
                this.submit();
            });
            
            // Initialize calculations
            calculatePercentage();
            updateNotificationPreview();
            
            // Trigger course change if course is already selected (form validation error scenario)
            if ($('#course_id').val()) {
                $('#course_id').trigger('change');
            }
        });
    </script>
</body>
</html>