<?php
// Include session check
require_once 'check_session.php';

// Include database connection
include 'conn.php';

// Get statistics from database
// Total Students
$student_query = "SELECT COUNT(*) as total FROM onlinestudents";
$student_result = $conn->query($student_query);
$student_data = $student_result->fetch_assoc();
$total_students = $student_data['total'];

// Total Teachers
$teacher_query = "SELECT COUNT(*) as total FROM teachers";
$teacher_result = $conn->query($teacher_query);
$teacher_data = $teacher_result->fetch_assoc();
$total_teachers = $teacher_data['total'];

// Total Centers
$center_query = "SELECT COUNT(*) as total FROM center_details";
$center_result = $conn->query($center_query);
$center_data = $center_result->fetch_assoc();
$total_centers = $center_data['total'];

// Total Courses
$course_query = "SELECT COUNT(*) as total FROM courses";
$course_result = $conn->query($course_query);
$course_data = $course_result->fetch_assoc();
$total_courses = $course_data['total'];

// Recent Students (last 5)
$recent_students_query = "SELECT * FROM onlinestudents ORDER BY created_at DESC LIMIT 5";
$recent_students_result = $conn->query($recent_students_query);

// Recent Teachers (last 5)
$recent_teachers_query = "SELECT * FROM teachers ORDER BY created_at DESC LIMIT 5";
$recent_teachers_result = $conn->query($recent_teachers_query);

// Recent Courses (last 5)
$recent_courses_query = "SELECT * FROM courses ORDER BY created_at DESC LIMIT 5";
$recent_courses_result = $conn->query($recent_courses_query);
?>

<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <!-- All Meta -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="DexignLab">
    <meta name="robots" content="">
    <!-- Mobile Specific -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Page Title Here -->
    <title>Sharnay Institute - Admin Dashboard</title>

    <!-- FAVICONS ICON -->
    <link rel="shortcut icon" type="image/png" href="images/favicon.png">
    <link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/admin-standard.css" rel="stylesheet">
    
    <style>
        .stat-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 10px;
            overflow: hidden;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin: 10px 0;
        }
        .recent-table {
            font-size: 0.9rem;
        }
        .recent-table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .dashboard-title {
            color: #2A353A;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .welcome-card {
            background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .welcome-card h3 {
            font-weight: 600;
            margin-bottom: 5px;
        }
    </style>
</head>

<body class="body h-100">
    <div class="page-wraper">
        <!-- Header Start -->
        <?php include 'menu.php'; ?>
        <!-- Header End -->

        <!-- Content Start -->
        <div class="content-body">
            <div class="container-fluid">
                <!-- Welcome Card -->
                <div class="welcome-card">
                    <div class="row">
                        <div class="col-md-8">
                            <h3>Welcome back, <?php echo $_SESSION['admin_name']; ?>!</h3>
                            <p>Here's what's happening with your institute today.</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <i class="material-symbols-outlined" style="font-size: 60px; opacity: 0.3;">school</i>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-xl-3 col-sm-6">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body" style="background: linear-gradient(45deg, #7d8cce 0%, #667f81 100%);">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4 class="stat-number"><?php echo number_format($total_students); ?></h4>
                                        <h6 class="mb-0">Total Students</h6>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="material-symbols-outlined">groups</i>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="RegStudentList.php" class="text-white" style="text-decoration: none;">
                                        View Details <i class="material-symbols-outlined" style="font-size: 14px; vertical-align: middle;">arrow_forward</i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-sm-6">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body" style="background: linear-gradient(45deg, #b81f73 0%, #2b4435 100%);">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4 class="stat-number"><?php echo number_format($total_teachers); ?></h4>
                                        <h6 class="mb-0">Total Teachers</h6>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="material-symbols-outlined">school</i>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="Teachers.php" class="text-white" style="text-decoration: none;">
                                        View Details <i class="material-symbols-outlined" style="font-size: 14px; vertical-align: middle;">arrow_forward</i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-sm-6">
                        <div class="card stat-card bg-warning text-white">
                            <div class="card-body" style="background: linear-gradient(45deg, #f7971e 0%, #ffd200 100%);">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4 class="stat-number"><?php echo number_format($total_centers); ?></h4>
                                        <h6 class="mb-0">Total Centers</h6>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="material-symbols-outlined">location_on</i>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="center_details.php" class="text-white" style="text-decoration: none;">
                                        View Details <i class="material-symbols-outlined" style="font-size: 14px; vertical-align: middle;">arrow_forward</i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-sm-6">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body" style="background: linear-gradient(45deg, #2193b0 0%, #6dd5ed 100%);">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4 class="stat-number"><?php echo number_format($total_courses); ?></h4>
                                        <h6 class="mb-0">Total Courses</h6>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="material-symbols-outlined">menu_book</i>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="courses.php" class="text-white" style="text-decoration: none;">
                                        View Details <i class="material-symbols-outlined" style="font-size: 14px; vertical-align: middle;">arrow_forward</i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="row">
                    <!-- Recent Students -->
                    <div class="col-xl-4 col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Recent Students</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive recent-table">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Course</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($student = $recent_students_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                                <td><?php echo htmlspecialchars($student['course_name']); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($student['created_at'])); ?></td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="RegStudentList.php" class="btn btn-primary btn-sm">View All Students</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Teachers -->
                    <div class="col-xl-4 col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Recent Teachers</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive recent-table">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Subject</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($teacher = $recent_teachers_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($teacher['name'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($teacher['subject'] ?? 'N/A'); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($teacher['created_at'] ?? date('Y-m-d'))); ?></td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="Teachers.php" class="btn btn-success btn-sm">View All Teachers</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Courses -->
                    <div class="col-xl-4 col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Recent Courses</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive recent-table">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Course Name</th>
                                                <th>Duration</th>
                                                <th>Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($course = $recent_courses_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                                <td><?php echo htmlspecialchars($course['duration']); ?></td>
                                                <td>₹<?php echo number_format($course['price'], 2); ?></td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="courses.php" class="btn btn-info btn-sm">View All Courses</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Quick Actions</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <a href="add-student.php" class="btn btn-primary btn-block">
                                            <i class="material-symbols-outlined me-2">person_add</i>
                                            Add Student
                                        </a>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <a href="TeacherReg.php" class="btn btn-success btn-block">
                                            <i class="material-symbols-outlined me-2">person_add</i>
                                            Add Teacher
                                        </a>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <a href="create-center.php" class="btn btn-warning btn-block">
                                            <i class="material-symbols-outlined me-2">add_location</i>
                                            Add Center
                                        </a>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <a href="add-courses.php" class="btn btn-info btn-block">
                                            <i class="material-symbols-outlined me-2">library_add</i>
                                            Add Course
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Content End -->
    </div>

    <!--**********************************
        Scripts
    ***********************************-->
    <!-- Required vendors -->
    <script src="vendor/global/global.min.js"></script>
    <script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="js/custom.min.js"></script>
    <script src="js/dlabnav-init.js"></script>
    <script src="js/admin-standard.js"></script>
    
    <script>
        // Simple chart for statistics (you can replace with actual chart library)
        document.addEventListener('DOMContentLoaded', function() {
            // Add animation to stat cards on hover
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-10px)';
                });
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
            
            // Update current time
            function updateTime() {
                const now = new Date();
                const timeStr = now.toLocaleTimeString('en-US', { 
                    hour: '2-digit', 
                    minute: '2-digit',
                    hour12: true 
                });
                const dateStr = now.toLocaleDateString('en-US', { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });
                
                const timeElement = document.querySelector('.dashboard_bar');
                if (timeElement) {
                    timeElement.innerHTML = `Dashboard | ${dateStr} | ${timeStr}`;
                }
            }
            
            // Update time every minute
            updateTime();
            setInterval(updateTime, 60000);
        });
    </script>
</body>
</html>