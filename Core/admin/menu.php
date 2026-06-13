<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentPage = basename($_SERVER['PHP_SELF']);
function si_active($pages) {
    global $currentPage;
    return in_array($currentPage, (array)$pages) ? 'active' : '';
}
function si_open($pages) {
    global $currentPage;
    return in_array($currentPage, (array)$pages) ? 'mm-active' : '';
}
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminRole = $_SESSION['admin_role'] ?? 'Administrator';
?>
<div class="nav-header">
    <a href="index.php" class="brand-logo">
        <img src="images/logo.png" alt="Sharnay Institute">
    </a>
    <div class="nav-control">
        <div class="hamburger">
            <span class="line"></span><span class="line"></span><span class="line"></span>
        </div>
    </div>
</div>

<div class="header">
    <div class="header-content">
        <nav class="navbar navbar-expand">
            <div class="collapse navbar-collapse justify-content-between">
                <div class="header-left">
                    <div class="dashboard_bar">Welcome, <?php echo htmlspecialchars($adminName); ?></div>
                </div>
                <ul class="navbar-nav header-right align-items-center">
                    <li class="nav-item me-3 d-none d-md-block">
                        <span class="badge bg-primary rounded-pill px-3 py-2"><?php echo htmlspecialchars($adminRole); ?></span>
                    </li>
                    <li class="nav-item dropdown header-profile2">
                        <a class="nav-link" href="javascript:void(0);" role="button" data-bs-toggle="dropdown">
                            <div class="header-info2 d-flex align-items-center">
                                <div class="me-3 text-end d-none d-md-block">
                                    <h5 class="mb-0 fs-16 font-w600"><?php echo htmlspecialchars($adminName); ?></h5>
                                    <span class="fs-12"><?php echo htmlspecialchars($adminRole); ?></span>
                                </div>
                                <img src="images/logo.png" alt="Admin">
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a href="profile.php" class="dropdown-item">Profile</a>
                            <div class="dropdown-divider"></div>
                            <a href="logout.php" class="dropdown-item text-danger">Logout</a>
                        </div>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="nav-link logout-btn" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</div>

<div class="dlabnav">
    <div class="dlabnav-scroll">
        <ul class="metismenu" id="menu">
            <li class="<?php echo si_open(['index.php']); ?>">
                <a href="index.php" class="<?php echo si_active(['index.php']); ?>" aria-expanded="false">
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <li class="<?php echo si_open(['member.php','create-member.php','newmember.php']); ?>">
                <a class="has-arrow <?php echo si_active(['member.php','create-member.php','newmember.php']); ?>" href="javascript:void(0);" aria-expanded="false"><span class="nav-text">Members</span></a>
                <ul aria-expanded="false">
                    <li><a href="member.php">All Members</a></li>
                    <li><a href="create-member.php">Add New Member</a></li>
                </ul>
            </li>
            <li class="<?php echo si_open(['Teachers.php','TeacherReg.php','RegTeacher.php','EditTeacher.php']); ?>">
                <a class="has-arrow <?php echo si_active(['Teachers.php','TeacherReg.php','RegTeacher.php','EditTeacher.php']); ?>" href="javascript:void(0);" aria-expanded="false"><span class="nav-text">Teachers</span></a>
                <ul aria-expanded="false">
                    <li><a href="Teachers.php">All Teachers</a></li>
                    <li><a href="TeacherReg.php">Add Teacher</a></li>
                </ul>
            </li>
            <li class="<?php echo si_open(['RegStudentList.php','PendingStudent.php','student.php','add-student.php','approve_student.php']); ?>">
                <a class="has-arrow <?php echo si_active(['RegStudentList.php','PendingStudent.php','student.php','add-student.php','approve_student.php']); ?>" href="javascript:void(0);" aria-expanded="false"><span class="nav-text">Students</span></a>
                <ul aria-expanded="false">
                    <li><a href="RegStudentList.php">All Students</a></li>
                    <li><a href="PendingStudent.php">Pending Students</a></li>
                    <li><a href="student.php">Approved Students</a></li>
                    <li><a href="add-student.php">Add New Student</a></li>
                </ul>
            </li>
            <li class="<?php echo si_open(['center_details.php','CenterList.php','create-center.php','edit_center.php','center_information.php','admin_wallet.php','center_wallet_detail.php']); ?>">
                <a class="has-arrow <?php echo si_active(['center_details.php','CenterList.php','create-center.php','edit_center.php','center_information.php','admin_wallet.php','center_wallet_detail.php']); ?>" href="javascript:void(0);" aria-expanded="false"><span class="nav-text">Centers</span></a>
                <ul aria-expanded="false">
                    <li><a href="center_details.php">All Centers</a></li>
                    <li><a href="CenterList.php">Center List</a></li>
                    <li><a href="create-center.php">Add New Center</a></li>
                    <li><a href="admin_wallet.php">Center Wallet</a></li>
                </ul>
            </li>
            <li class="<?php echo si_open(['courses.php','add_module.php','add-courses.php','edit_courses.php']); ?>">
                <a class="has-arrow <?php echo si_active(['courses.php','add_module.php','add-courses.php','edit_courses.php']); ?>" href="javascript:void(0);" aria-expanded="false"><span class="nav-text">Courses</span></a>
                <ul aria-expanded="false">
                    <li><a href="courses.php">All Courses</a></li>
                    <li><a href="add_module.php">Add Module</a></li>
                    <li><a href="add-courses.php">Add New Course</a></li>
                </ul>
            </li>
            <li class="<?php echo si_open(['exam-schedule.php','notifications.php','add_marks.php']); ?>">
                <a class="has-arrow <?php echo si_active(['exam-schedule.php','notifications.php','add_marks.php']); ?>" href="javascript:void(0);" aria-expanded="false"><span class="nav-text">Exam</span></a>
                <ul aria-expanded="false">
                    <li><a href="exam-schedule.php">Set Timetable</a></li>
                    <li><a href="add_marks.php">Add Marks</a></li>
                    <li><a href="notifications.php">Notifications</a></li>
                </ul>
            </li>
            <li class="<?php echo si_open(['create-block.php']); ?>"><a href="create-block.php" class="<?php echo si_active(['create-block.php']); ?>"><span class="nav-text">Create Block</span></a></li>
             <li class="<?php echo si_open(['profile.php']); ?>"><a href="profile.php" class="<?php echo si_active(['profile.php']); ?>"><span class="nav-text">Profile</span></a></li>
            
            <li class="<?php echo si_open(['VacencyList.php','CreateVacency.php']); ?>">
                <a class="has-arrow <?php echo si_active(['VacencyList.php','CreateVacency.php']); ?>" href="javascript:void(0);" aria-expanded="false"><span class="nav-text">Vacancies</span></a>
                <ul aria-expanded="false">
                    <li><a href="VacencyList.php">All Vacancies</a></li>
                    <li><a href="CreateVacency.php">Create Vacancy</a></li>
                </ul>
            </li>
            <li class="<?php echo si_open(['certificates.php','Certificate_Creation_Form.php','CertificateApprove.php','Marksheet_Creation_Form.php']); ?>">
                <a class="has-arrow <?php echo si_active(['certificates.php','Certificate_Creation_Form.php','CertificateApprove.php','Marksheet_Creation_Form.php']); ?>" href="javascript:void(0);" aria-expanded="false"><span class="nav-text">Certificates</span></a>
                <ul aria-expanded="false">
                    <li><a href="certificates.php">All Certificates</a></li>
                    <li><a href="Certificate_Creation_Form.php">Generate Certificate</a></li>
                    <li><a href="Marksheet_Creation_Form.php">Generate Marksheet</a></li>
                    <li><a href="CertificateApprove.php">Certificate Approval</a></li>
                </ul>
            </li>
        </ul>
    </div>
</div>
