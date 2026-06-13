    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 text-center py-3">
                    <div class="footer-content">
                        <p class="mb-1">
                            &copy; <?php echo date('Y'); ?> Sharnay Institute. All Rights Reserved.
                        </p>
                        <p class="mb-1">
                            Designed and Developed by 
                            <a href="https://www.rkvitsolutions.com/" target="_blank" class="footer-link">
                                <strong>RKV IT Solutions Pvt. Ltd.</strong>
                            </a>
                        </p>
                        <p class="mb-0">
                            <a href="https://www.rkvitsolutions.com/" target="_blank" class="footer-link">
                                https://www.rkvitsolutions.com/
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Mobile menu toggle
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const icon = this.querySelector('i');
            
            sidebar.classList.toggle('show');
            icon.classList.toggle('fa-bars');
            icon.classList.toggle('fa-times');
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const menuBtn = document.getElementById('mobileMenuBtn');
            
            if (window.innerWidth <= 992) {
                if (!sidebar.contains(event.target) && !menuBtn.contains(event.target)) {
                    sidebar.classList.remove('show');
                    menuBtn.querySelector('i').classList.remove('fa-times');
                    menuBtn.querySelector('i').classList.add('fa-bars');
                }
            }
        });
        
        // Auto-refresh notifications count every 30 seconds
        setInterval(function() {
            fetch('get_notifications_count.php')
                .then(response => response.json())
                .then(data => {
                    const badge = document.querySelector('.sidebar-menu a[href="notifications.php"] .menu-badge');
                    if (badge && data.count > 0) {
                        badge.textContent = data.count;
                        badge.style.display = 'inline-block';
                    } else if (badge && data.count === 0) {
                        badge.style.display = 'none';
                    }
                })
                .catch(error => console.error('Error fetching notifications:', error));
        }, 30000);
        
        // Logout confirmation
        document.querySelector('.logout-btn').addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to logout?')) {
                e.preventDefault();
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const menuBtn = document.getElementById('mobileMenuBtn');
            
            if (window.innerWidth > 992) {
                sidebar.classList.remove('show');
                menuBtn.querySelector('i').classList.remove('fa-times');
                menuBtn.querySelector('i').classList.add('fa-bars');
            }
        });
        
        // Update current time every minute
        function updateCurrentTime() {
            const now = new Date();
            const timeElement = document.querySelector('.current-time');
            if (timeElement) {
                timeElement.textContent = now.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
            }
        }
        
        // Initialize time update if element exists
        const timeElement = document.querySelector('.current-time');
        if (timeElement) {
            updateCurrentTime();
            setInterval(updateCurrentTime, 60000);
        }
    </script>
</body>
</html>
<?php
// Close database connection
mysqli_close($conn);
?>