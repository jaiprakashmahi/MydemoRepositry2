<?php
// Set page title
$page_title = "Dashboard";

// Include header
include 'header.php';
?>

<main class="main-content">
    <div class="page-header">
        <h1 class="h3 mb-2">Welcome back, <span class="text-primary"><?php echo htmlspecialchars($student['name']); ?></span>!</h1>
        <p class="text-muted mb-0">
            <i class="fas fa-calendar-alt me-2"></i>
            <?php echo date('l, F j, Y'); ?> | 
            <i class="fas fa-clock me-1 ms-3"></i>
            <span class="current-time"><?php echo date('h:i A'); ?></span>
        </p>
    </div>
    
    <!-- Welcome Banner -->
    <div class="welcome-banner" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
                                        color: white; padding: 30px; border-radius: 12px; margin-bottom: 30px;
                                        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-3">Education for Excellence</h2>
                <p class="mb-0">"The beautiful thing about learning is that no one can take it away from you." - B.B. King</p>
            </div>
            <div class="col-md-4 text-end">
                <i class="fas fa-graduation-cap fa-5x opacity-75"></i>
            </div>
        </div>
    </div>
    
    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body text-center p-4">
                    <div class="card-icon">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                    <h5 class="card-title fw-bold mb-2">Fee Status</h5>
                    <p class="card-text mb-3">
                        <span class="badge bg-success fs-6 px-3 py-2">Paid</span>
                    </p>
                    <a href="fee.php" class="btn btn-outline-primary btn-sm">
                        View Details <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body text-center p-4">
                    <div class="card-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <h5 class="card-title fw-bold mb-2">Exams</h5>
                    <p class="card-text fs-4 fw-bold text-primary mb-3">2</p>
                    <p class="text-muted small mb-3">Upcoming Exams</p>
                    <a href="exam.php" class="btn btn-outline-primary btn-sm">
                        View Schedule <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body text-center p-4">
                    <div class="card-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h5 class="card-title fw-bold mb-2">Attendance</h5>
                    <p class="card-text fs-4 fw-bold text-success mb-3">95%</p>
                    <p class="text-muted small mb-3">Overall Attendance</p>
                    <a href="attendance.php" class="btn btn-outline-primary btn-sm">
                        View Report <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body text-center p-4">
                    <div class="card-icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <h5 class="card-title fw-bold mb-2">Results</h5>
                    <p class="card-text fs-4 fw-bold text-warning mb-3">85%</p>
                    <p class="text-muted small mb-3">Last Exam Score</p>
                    <a href="results.php" class="btn btn-outline-primary btn-sm">
                        View Results <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity & Upcoming Events -->
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fas fa-history text-primary me-2"></i>Recent Activity</h5>
                </div>
                <div class="card-body">
                    <div class="timeline" style="position: relative; padding-left: 30px;">
                        <div style="content: ''; position: absolute; left: 15px; top: 0; bottom: 0; width: 2px; background: #e9ecef;"></div>
                        
                        <div class="timeline-item" style="position: relative; margin-bottom: 25px;">
                            <div class="timeline-marker" style="position: absolute; left: -33px; top: 5px; width: 16px; height: 16px; border-radius: 50%; border: 3px solid white; background: #28a745;"></div>
                            <div class="timeline-content" style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 3px 10px rgba(0,0,0,0.05); border: 1px solid #e9ecef;">
                                <h6 class="mb-1 fw-bold">Fee Payment Successful</h6>
                                <p class="text-muted mb-2">You have successfully paid ₹5,000 for course fee</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        2 hours ago
                                    </small>
                                    <span class="badge bg-success">Completed</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="timeline-item" style="position: relative; margin-bottom: 25px;">
                            <div class="timeline-marker" style="position: absolute; left: -33px; top: 5px; width: 16px; height: 16px; border-radius: 50%; border: 3px solid white; background: #007bff;"></div>
                            <div class="timeline-content" style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 3px 10px rgba(0,0,0,0.05); border: 1px solid #e9ecef;">
                                <h6 class="mb-1 fw-bold">New Study Material Added</h6>
                                <p class="text-muted mb-2">Chapter 5 notes uploaded for Mathematics subject</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        1 day ago
                                    </small>
                                    <a href="#" class="btn btn-sm btn-outline-primary">Download</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="timeline-item" style="position: relative; margin-bottom: 25px;">
                            <div class="timeline-marker" style="position: absolute; left: -33px; top: 5px; width: 16px; height: 16px; border-radius: 50%; border: 3px solid white; background: #ffc107;"></div>
                            <div class="timeline-content" style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 3px 10px rgba(0,0,0,0.05); border: 1px solid #e9ecef;">
                                <h6 class="mb-1 fw-bold">Exam Scheduled</h6>
                                <p class="text-muted mb-2">Mid-term exam scheduled for 15th November 2024</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        2 days ago
                                    </small>
                                    <span class="badge bg-warning text-dark">Upcoming</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-4">
            <!-- Upcoming Events -->
            <div class="card mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fas fa-calendar text-primary me-2"></i>Upcoming Events</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item border-0 px-0 py-3">
                            <div class="d-flex w-100 justify-content-between align-items-center mb-2">
                                <h6 class="mb-0 fw-bold text-primary">Mathematics Exam</h6>
                                <span class="badge bg-danger">15 Nov</span>
                            </div>
                            <p class="text-muted small mb-0">Mid-term examination - 10:00 AM</p>
                        </div>
                        
                        <div class="list-group-item border-0 px-0 py-3">
                            <div class="d-flex w-100 justify-content-between align-items-center mb-2">
                                <h6 class="mb-0 fw-bold text-primary">Parent-Teacher Meeting</h6>
                                <span class="badge bg-info">20 Nov</span>
                            </div>
                            <p class="text-muted small mb-0">10:00 AM - Conference Hall</p>
                        </div>
                        
                        <div class="list-group-item border-0 px-0 py-3">
                            <div class="d-flex w-100 justify-content-between align-items-center mb-2">
                                <h6 class="mb-0 fw-bold text-primary">Science Practical</h6>
                                <span class="badge bg-success">25 Nov</span>
                            </div>
                            <p class="text-muted small mb-0">Chemistry lab session - Lab 3</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="card">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fas fa-link text-primary me-2"></i>Quick Links</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <a href="download_id_card.php" class="quick-link-btn" style="background: white; border: 2px solid #e9ecef; border-radius: 10px; padding: 15px; text-align: center; text-decoration: none; color: #333; transition: all 0.3s; display: block; margin-bottom: 15px;">
                            <i class="fas fa-id-card fa-2x mb-2"></i>
                            <h6 class="fw-bold mb-1">Download ID Card</h6>
                            <p class="small text-muted mb-0">Get your student ID card</p>
                        </a>
                        
                        <a href="download_certificate.php" class="quick-link-btn" style="background: white; border: 2px solid #e9ecef; border-radius: 10px; padding: 15px; text-align: center; text-decoration: none; color: #333; transition: all 0.3s; display: block; margin-bottom: 15px;">
                            <i class="fas fa-certificate fa-2x mb-2"></i>
                            <h6 class="fw-bold mb-1">Download Certificate</h6>
                            <p class="small text-muted mb-0">Get course completion certificate</p>
                        </a>
                        
                        <a href="contact.php" class="quick-link-btn" style="background: white; border: 2px solid #e9ecef; border-radius: 10px; padding: 15px; text-align: center; text-decoration: none; color: #333; transition: all 0.3s; display: block; margin-bottom: 15px;">
                            <i class="fas fa-headset fa-2x mb-2"></i>
                            <h6 class="fw-bold mb-1">Contact Support</h6>
                            <p class="small text-muted mb-0">24/7 student support</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Course Progress -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fas fa-chart-line text-primary me-2"></i>Course Progress</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center mb-3">
                            <div class="display-4 fw-bold text-primary">65%</div>
                            <p class="text-muted mb-0">Course Completion</p>
                        </div>
                        <div class="col-md-9">
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated" 
                                     role="progressbar" 
                                     style="width: 65%" 
                                     aria-valuenow="65" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    65% Complete
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <small>Started: <?php echo date('d M Y', strtotime($student['session_start'])); ?></small>
                                <small>Ends: <?php echo date('d M Y', strtotime($student['session_end'])); ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
// Include footer
include 'footer.php';
?>