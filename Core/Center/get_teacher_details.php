<?php
session_start();
include '../conn.php';

if (isset($_POST['teacher_id']) && isset($_POST['center_id'])) {
    $teacher_id = intval($_POST['teacher_id']);
    $center_id = intval($_POST['center_id']);
    
    // Get teacher details
    $teacher_query = "SELECT t.*, s.name as state_name, d.name as district_name 
                      FROM teachers t 
                      LEFT JOIN states s ON t.state_id = s.id 
                      LEFT JOIN districts d ON t.district_id = d.id 
                      WHERE t.id = ? AND t.center_id = ?";
    $stmt = $conn->prepare($teacher_query);
    $stmt->bind_param("ii", $teacher_id, $center_id);
    $stmt->execute();
    $teacher = $stmt->get_result()->fetch_assoc();
    
    if (!$teacher) {
        echo "<p class='text-center text-danger'>Teacher not found!</p>";
        exit();
    }
    
    // Get education details
    $edu_query = "SELECT * FROM teacher_education WHERE teacher_id = ?";
    $edu_stmt = $conn->prepare($edu_query);
    $edu_stmt->bind_param("i", $teacher_id);
    $edu_stmt->execute();
    $education = $edu_stmt->get_result();
    
    // Get experience details
    $exp_query = "SELECT * FROM teacher_experience WHERE teacher_id = ?";
    $exp_stmt = $conn->prepare($exp_query);
    $exp_stmt->bind_param("i", $teacher_id);
    $exp_stmt->execute();
    $experience = $exp_stmt->get_result();
    
    // Display teacher details
    ?>
    
    <div class="row mb-4">
        <div class="col-md-3 text-center">
            <?php if(!empty($teacher['photo'])): ?>
                <img src="../uploads/teachers/photos/<?php echo htmlspecialchars($teacher['photo']); ?>" 
                     class="img-fluid rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
            <?php else: ?>
                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto" 
                     style="width: 150px; height: 150px;">
                    <i class="fas fa-user fa-4x text-muted"></i>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-md-9">
            <h4><?php echo htmlspecialchars($teacher['name']); ?></h4>
            <p class="text-muted">Teacher Code: <strong><?php echo htmlspecialchars($teacher['teacher_code']); ?></strong></p>
            <p>
                <span class="badge <?php echo $teacher['teacher_status'] == 1 ? 'bg-success' : 'bg-danger'; ?>">
                    <?php echo $teacher['teacher_status'] == 1 ? 'Active' : 'Inactive'; ?>
                </span>
            </p>
            <p><i class="fas fa-envelope me-2"></i> <?php echo htmlspecialchars($teacher['email']); ?></p>
            <p><i class="fas fa-phone me-2"></i> <?php echo htmlspecialchars($teacher['mobile']); ?></p>
        </div>
    </div>
    
    <div class="detail-section">
        <h6><i class="fas fa-user"></i> Personal Information</h6>
        <div class="detail-item">
            <div class="detail-label">Father's Name:</div>
            <div class="detail-value"><?php echo htmlspecialchars($teacher['father_name']); ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Mother's Name:</div>
            <div class="detail-value"><?php echo htmlspecialchars($teacher['mother_name'] ?? 'N/A'); ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Date of Birth:</div>
            <div class="detail-value"><?php echo date('d-m-Y', strtotime($teacher['dob'])); ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Gender:</div>
            <div class="detail-value"><?php echo htmlspecialchars($teacher['gender']); ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Blood Group:</div>
            <div class="detail-value"><?php echo htmlspecialchars($teacher['blood_group'] ?? 'N/A'); ?></div>
        </div>
    </div>
    
    <div class="detail-section">
        <h6><i class="fas fa-map-marker-alt"></i> Address Information</h6>
        <div class="detail-item">
            <div class="detail-label">Address:</div>
            <div class="detail-value"><?php echo htmlspecialchars($teacher['address']); ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">City:</div>
            <div class="detail-value"><?php echo htmlspecialchars($teacher['city']); ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">State/District:</div>
            <div class="detail-value"><?php echo htmlspecialchars($teacher['state_name']); ?> / <?php echo htmlspecialchars($teacher['district_name']); ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Pin Code:</div>
            <div class="detail-value"><?php echo htmlspecialchars($teacher['pin_code']); ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Block/Post Office:</div>
            <div class="detail-value"><?php echo htmlspecialchars($teacher['block']); ?> / <?php echo htmlspecialchars($teacher['post_office']); ?></div>
        </div>
    </div>
    
    <?php if($education->num_rows > 0): ?>
    <div class="detail-section">
        <h6><i class="fas fa-graduation-cap"></i> Education Details</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Education</th>
                        <th>Session</th>
                        <th>Marks</th>
                        <th>% / CGPA</th>
                        <th>Grade</th>
                        <th>Marksheet</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($edu = $education->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($edu['education']); ?></td>
                        <td><?php echo htmlspecialchars($edu['session_from'] . ' - ' . $edu['session_to']); ?></td>
                        <td><?php echo htmlspecialchars($edu['obt_marks'] . '/' . $edu['total_marks']); ?></td>
                        <td><?php echo htmlspecialchars($edu['percentage']); ?></td>
                        <td><?php echo htmlspecialchars($edu['grade']); ?></td>
                        <td>
                            <?php if(!empty($edu['marksheet'])): ?>
                                <a href="../uploads/teachers/marksheets/<?php echo htmlspecialchars($edu['marksheet']); ?>" target="_blank">View</a>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if($experience->num_rows > 0): ?>
    <div class="detail-section">
        <h6><i class="fas fa-briefcase"></i> Experience Details</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Organization</th>
                        <th>Role</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Document</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($exp = $experience->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($exp['organization']); ?></td>
                        <td><?php echo htmlspecialchars($exp['role']); ?></td>
                        <td><?php echo htmlspecialchars($exp['from_date']); ?></td>
                        <td><?php echo htmlspecialchars($exp['to_date']); ?></td>
                        <td>
                            <?php if(!empty($exp['document'])): ?>
                                <a href="../uploads/teachers/experience/<?php echo htmlspecialchars($exp['document']); ?>" target="_blank">View</a>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
    
    <?php
    $edu_stmt->close();
    $exp_stmt->close();
}
?>