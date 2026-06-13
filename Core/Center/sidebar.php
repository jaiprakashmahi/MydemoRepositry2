<?php
// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="sidebar">
    <ul class="sidebar-menu">
        <li class="menu-item <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
            <a href="index.php">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="menu-item <?php echo $current_page == 'teacher_management.php' ? 'active' : ''; ?>">
            <a href="teacher_management.php">
                <i class="fas fa-users"></i>
                <span>Teachers Management</span>
            </a>
        </li>
        <li class="menu-item <?php echo $current_page == 'student.php' ? 'active' : ''; ?>">
            <a href="student.php">
                <i class="fas fa-users"></i>
                <span>Students</span>
            </a>
        </li>
       
        <li class="menu-item <?php echo $current_page == 'Add_Student.php' ? 'active' : ''; ?>">
            <a href="Add_Student.php">
                <i class="fas fa-calendar-check"></i>
                <span> Add Students</span>
            </a>
        </li>
        <li class="menu-item <?php echo $current_page == 'certificategenerated.php' ? 'active' : ''; ?>">
            <a href="certificategenerated.php">
                <i class="fas fa-calendar-check"></i>
                <span> Certificate Generated</span>
            </a>
        </li>
        <li class="menu-item <?php echo $current_page == 'wallet.php' ? 'active' : ''; ?>">
    <a href="wallet.php">
        <i class="fas fa-wallet"></i>
        <span>Wallet</span>
    </a>
    </li>
        <li class="menu-item <?php echo $current_page == 'reports.php' ? 'active' : ''; ?>">
            <a href="#">
                <i class="fas fa-chart-line"></i>
                <span>Reports</span>
            </a>
        </li>

        <li class="menu-item <?php echo $current_page == 'fees.php' ? 'active' : ''; ?>">
            <a href="#">
                <i class="fas fa-rupee-sign"></i>
                <span>Fee Management</span>
            </a>
        </li>
        <li class="menu-item <?php echo $current_page == 'notifications.php' ? 'active' : ''; ?>">
            <a href="notifications.php">
                <i class="fas fa-cog"></i>
                <span>notifications</span>
            </a>
        </li>
    </ul>
    
    <div class="center-info" style="background-color: rgba(0,0,0,0.05); padding: 15px; margin: 20px; border-radius: 8px; color: #333;">
        <h6 style="color: var(--primary-color);"><i class="fas fa-info-circle"></i> Center Information</h6>
        <p style="font-size: 12px; margin-bottom: 5px;">ID: <?php echo $center_id; ?></p>
        <p style="font-size: 12px; margin-bottom: 0;">Phone: <?php echo htmlspecialchars($phone); ?></p>
    </div>
</nav>