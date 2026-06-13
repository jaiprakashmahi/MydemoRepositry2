<?php
session_start();
include 'conn.php';

if (!isset($_SESSION['admin_email'])) {
    header("Location: index.php");
    exit();
}

// Mark as read
if (isset($_GET['mark_read']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->query("UPDATE notifications SET is_read = TRUE WHERE id = $id");
    header("Location: notifications.php");
    exit();
}

// Get notifications for admin
$query = "SELECT n.*, 
          cd.center_name as sender_name,
          es.exam_name,
          qp.paper_name
          FROM notifications n
          LEFT JOIN center_details cd ON n.sender_id = cd.id
          LEFT JOIN exam_schedule es ON n.related_id = es.id
          LEFT JOIN question_papers qp ON n.related_id = qp.id
          WHERE n.receiver_type = 'Admin' OR n.receiver_type = 'All'
          ORDER BY n.created_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Notifications</title>
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3a0ca3;
            --success: #06d6a0;
        }
        
        .notification-item {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid var(--primary);
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }
        
        .notification-item.unread {
            background: #f8f9ff;
            border-left: 4px solid var(--success);
        }
        
        .notification-item.question-paper {
            border-left: 4px solid #ffd166;
        }
        
        .view-paper-btn {
            background: var(--primary);
            color: white;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .view-paper-btn:hover {
            background: #3a56d4;
            color: white;
        }
    </style>
</head>
<body>
   
    
    <div class="container mt-4">
        <h2><i class="fas fa-bell me-2"></i>Admin Notifications</h2>
        
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="notification-item <?php echo !$row['is_read'] ? 'unread' : ''; ?> 
                 <?php echo $row['notification_type'] == 'Question_Paper' ? 'question-paper' : ''; ?>">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5><?php echo $row['title']; ?>
                            <?php if (!$row['is_read']): ?>
                                <span class="badge bg-success">New</span>
                            <?php endif; ?>
                        </h5>
                        <p class="mb-1"><?php echo $row['message']; ?></p>
                        <small class="text-muted">
                            From: <?php echo $row['sender_name'] ?: 'System'; ?> | 
                            <?php echo date('d M Y, h:i A', strtotime($row['created_at'])); ?>
                        </small>
                    </div>
                    <div>
                        <?php if (!$row['is_read']): ?>
                            <a href="?mark_read=true&id=<?php echo $row['id']; ?>" 
                               class="btn btn-sm btn-outline-success">
                                Mark Read
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($row['notification_type'] == 'Question_Paper' && $row['related_id']): ?>
                            <a href="view-question-paper.php?id=<?php echo $row['related_id']; ?>" 
                               class="view-paper-btn btn-sm">
                                <i class="fas fa-eye"></i> View Paper
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>