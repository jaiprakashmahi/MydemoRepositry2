<?php
// Set page title
$page_title = "My Profile";

// Include header
include 'header.php';
?>

<main class="main-content">
    <div class="page-header">
        <h1 class="h3 mb-2"><i class="fas fa-user me-2"></i>My Profile</h1>
        <p class="text-muted mb-0">View and update your personal information</p>
    </div>
    
    <div class="row">
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <?php
                    $photo_path = !empty($student['photo']) ? "../uploads/students/photos/" . $student['photo'] : "https://ui-avatars.com/api/?name=" . urlencode($student['name']) . "&background=667eea&color=fff&size=200";
                    ?>
                    <img src="<?php echo $photo_path; ?>" 
                         class="img-fluid rounded-circle mb-3" 
                         style="width: 200px; height: 200px; object-fit: cover;"
                         onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($student['name']); ?>&background=667eea&color=fff&size=200'"
                         alt="Profile Picture">
                    <h4><?php echo htmlspecialchars($student['name']); ?></h4>
                    <p class="text-muted"><?php echo htmlspecialchars($student['student_code']); ?></p>
                    
                    <!-- Photo Upload Form -->
                    <form action="upload_photo.php" method="POST" enctype="multipart/form-data" class="mt-3">
                        <div class="input-group">
                            <input type="file" class="form-control" name="photo" accept="image/*" required>
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-upload"></i> Upload
                            </button>
                        </div>
                        <small class="text-muted">Max 2MB. Allowed: JPG, PNG, GIF</small>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Personal Information</h5>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Full Name</label>
                            <p class="form-control-static"><?php echo htmlspecialchars($student['name']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email</label>
                            <p class="form-control-static"><?php echo htmlspecialchars($student['email']); ?></p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Mobile Number</label>
                            <p class="form-control-static"><?php echo htmlspecialchars($student['mobile']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date of Birth</label>
                            <p class="form-control-static"><?php echo date('d-m-Y', strtotime($student['dob'])); ?></p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Course Name</label>
                            <p class="form-control-static"><?php echo htmlspecialchars($student['course_name']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Study Center</label>
                            <p class="form-control-static"><?php echo htmlspecialchars($student['study_center']); ?></p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Session Start</label>
                            <p class="form-control-static"><?php echo date('d-m-Y', strtotime($student['session_start'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Session End</label>
                            <p class="form-control-static"><?php echo date('d-m-Y', strtotime($student['session_end'])); ?></p>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h5 class="card-title mb-4">Address Information</h5>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Address</label>
                        <p class="form-control-static"><?php echo htmlspecialchars($student['address']); ?></p>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">City</label>
                            <p class="form-control-static"><?php echo htmlspecialchars($student['city']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Pin Code</label>
                            <p class="form-control-static"><?php echo htmlspecialchars($student['pin_code']); ?></p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">State</label>
                            <p class="form-control-static"><?php echo htmlspecialchars($student['state']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">District</label>
                            <p class="form-control-static"><?php echo htmlspecialchars($student['district']); ?></p>
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