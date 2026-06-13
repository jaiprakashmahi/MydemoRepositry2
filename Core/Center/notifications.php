<?php
session_start();
include '../conn.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['center_logged_in']) || $_SESSION['center_logged_in'] !== true) {
    header("Location: member_login.php");
    exit();
}

// Get session variables with validation
$center_id = isset($_SESSION['center_id']) ? $_SESSION['center_id'] : 0;
$center_name = isset($_SESSION['center_name']) ? $_SESSION['center_name'] : '';

// Mark notification as read if requested
if (isset($_GET['mark_read']) && isset($_GET['id'])) {
    $notification_id = intval($_GET['id']);
    $update_query = "UPDATE notifications SET is_read = TRUE WHERE id = ? AND receiver_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ii", $notification_id, $center_id);
    $stmt->execute();
    header("Location: notifications.php");
    exit();
}

// Get notifications for this center
$query = "SELECT n.*, 
          es.exam_name, es.exam_date, es.start_time,
          c.center_name as sender_name
          FROM notifications n
          LEFT JOIN exam_schedule es ON n.related_id = es.id
          LEFT JOIN center_details c ON n.sender_id = c.id
          WHERE n.receiver_id = ? AND n.receiver_type = 'Center'
          ORDER BY n.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $center_id);
$stmt->execute();
$result = $stmt->get_result();

// Get unread count
$unread_query = "SELECT COUNT(*) as count FROM notifications 
                 WHERE receiver_id = ? AND receiver_type = 'Center' AND is_read = FALSE";
$unread_stmt = $conn->prepare($unread_query);
$unread_stmt->bind_param("i", $center_id);
$unread_stmt->execute();
$unread_result = $unread_stmt->get_result();
$unread_data = $unread_result->fetch_assoc();
$unread_count = $unread_data['count'];

// Set page title for header
$page_title = "Notifications";
?>

<?php include 'header.php'; ?>

<div class="main-content">
    <div class="navbar">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Notifications</li>
            </ol>
        </nav>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-bell me-2"></i>Notifications
                <?php if ($unread_count > 0): ?>
                    <span class="badge bg-danger ms-2"><?php echo $unread_count; ?> New</span>
                <?php endif; ?>
            </h5>
            <div>
                <a href="notifications.php?mark_all_read=true" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-check-double me-1"></i> Mark All as Read
                </a>
            </div>
        </div>
        
        <div class="card-body p-4">
            <?php if ($result->num_rows > 0): ?>
                <div class="notifications-list">
                    <?php while ($notification = $result->fetch_assoc()): ?>
                        <div class="notification-item mb-3 border rounded p-3 <?php echo !$notification['is_read'] ? 'bg-light border-start border-primary border-start-4' : 'border-start border-start-4'; ?>">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1 fw-bold <?php echo !$notification['is_read'] ? 'text-primary' : 'text-dark'; ?>">
                                        <?php echo htmlspecialchars($notification['title']); ?>
                                        <?php if (!$notification['is_read']): ?>
                                            <span class="badge bg-primary ms-2">New</span>
                                        <?php endif; ?>
                                    </h6>
                                    <small class="text-muted">
                                        <i class="far fa-clock me-1"></i>
                                        <?php echo date('d M Y, h:i A', strtotime($notification['created_at'])); ?>
                                    </small>
                                </div>
                                <div class="notification-badges">
                                    <span class="badge bg-info"><?php echo str_replace('_', ' ', $notification['notification_type']); ?></span>
                                </div>
                            </div>
                            
                            <p class="mb-2"><?php echo nl2br(htmlspecialchars($notification['message'])); ?></p>
                            
                            <?php if (!empty($notification['sender_name'])): ?>
                                <p class="mb-1"><small class="text-muted">From: <?php echo htmlspecialchars($notification['sender_name']); ?></small></p>
                            <?php endif; ?>
                            
                            <?php if ($notification['exam_name']): ?>
                                <div class="exam-info bg-light p-2 rounded mb-2">
                                    <p class="mb-1">
                                        <i class="fas fa-calendar-alt me-2"></i>
                                        <strong>Exam:</strong> <?php echo htmlspecialchars($notification['exam_name']); ?>
                                    </p>
                                    <p class="mb-0">
                                        <i class="fas fa-clock me-2"></i>
                                        <strong>Date & Time:</strong> 
                                        <?php echo date('d M Y', strtotime($notification['exam_date'])); ?> at 
                                        <?php echo date('h:i A', strtotime($notification['start_time'])); ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="notification-actions mt-2">
                                <?php if (!$notification['is_read']): ?>
                                    <a href="?mark_read=true&id=<?php echo $notification['id']; ?>" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-check me-1"></i> Mark as Read
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($notification['notification_type'] == 'Exam_Scheduled' && $notification['related_id']): ?>
                                    <a href="create-question-paper.php?exam_id=<?php echo $notification['related_id']; ?>" 
                                       class="btn btn-sm btn-success ms-1">
                                        <i class="fas fa-file-alt me-1"></i> Create Question Paper
                                    </a>
                                    
                                    <a href="view-exam.php?id=<?php echo $notification['related_id']; ?>" 
                                       class="btn btn-sm btn-primary ms-1">
                                        <i class="fas fa-eye me-1"></i> View Exam Details
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-bell-slash fa-4x text-muted"></i>
                    </div>
                    <h4 class="text-muted">No Notifications</h4>
                    <p class="text-muted">You're all caught up! New notifications will appear here.</p>
                    <a href="dashboard.php" class="btn btn-primary mt-3">
                        <i class="fas fa-home me-1"></i> Back to Dashboard
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Logout Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Logout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to logout?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Logout confirmation
document.getElementById('logoutBtn').addEventListener('click', function() {
    var logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
    logoutModal.show();
});

// Mark all as read functionality (you need to implement this in PHP)
if(window.location.search.includes('mark_all_read=true')) {
    fetch('mark_all_read.php')
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                window.location.href = 'notifications.php';
            }
        });
}

// Auto refresh notifications every 30 seconds
setTimeout(function() {
    window.location.reload();
}, 30000);
</script>

</body>
</html>