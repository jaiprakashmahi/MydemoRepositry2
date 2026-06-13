<?php
// Determine active page
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <?php
        $photo_path = !empty($student['photo']) ? "../uploads/students/photos/" . $student['photo'] : "https://ui-avatars.com/api/?name=" . urlencode($student['name']) . "&background=667eea&color=fff&size=200";
        ?>
        <img src="<?php echo $photo_path; ?>" 
             alt="Student Photo" 
             class="sidebar-photo"
             onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($student['name']); ?>&background=667eea&color=fff&size=200'">
        <h5><?php echo htmlspecialchars($student['name']); ?></h5>
        <p class="mb-2"><?php echo htmlspecialchars($student['student_code']); ?></p>
        <div class="course-badge">
            <?php echo htmlspecialchars($student['course_name']); ?>
        </div>
    </div>
    
    <ul class="sidebar-menu">
        <li><a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a></li>
        <li><a href="profile.php" class="<?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
            <i class="fas fa-user"></i> My Profile
        </a></li>
        <li><a href="fee.php" class="<?php echo ($current_page == 'fee.php') ? 'active' : ''; ?>">
            <i class="fas fa-rupee-sign"></i> Fee Details
        </a></li>
        <li><a href="exam.php" class="<?php echo ($current_page == 'exam.php') ? 'active' : ''; ?>">
            <i class="fas fa-book"></i> Exams
        </a></li>
        <li><a href="attendance.php" class="<?php echo ($current_page == 'attendance.php') ? 'active' : ''; ?>">
            <i class="fas fa-calendar-check"></i> Attendance
        </a></li>
        <li><a href="results.php" class="<?php echo ($current_page == 'results.php') ? 'active' : ''; ?>">
            <i class="fas fa-chart-line"></i> Results
        </a></li>
        <li><a href="study_material.php" class="<?php echo ($current_page == 'study_material.php') ? 'active' : ''; ?>">
            <i class="fas fa-file-pdf"></i> Study Material
        </a></li>
        <li><a href="notifications.php" class="<?php echo ($current_page == 'notifications.php') ? 'active' : ''; ?>">
            <i class="fas fa-bell"></i> Notifications
            <?php if ($notifications_count > 0): ?>
                <span class="menu-badge"><?php echo $notifications_count; ?></span>
            <?php endif; ?>
        </a></li>
        <li><a href="change_password.php" class="<?php echo ($current_page == 'change_password.php') ? 'active' : ''; ?>">
            <i class="fas fa-key"></i> Change Password
        </a></li>
        <li><a href="help.php" class="<?php echo ($current_page == 'help.php') ? 'active' : ''; ?>">
            <i class="fas fa-question-circle"></i> Help & Support
        </a></li>
    </ul>
</div>