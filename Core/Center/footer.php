    </div> <!-- Close container div -->

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Logout functionality
        document.getElementById('logoutBtn').addEventListener('click', function(e) {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        });
        
        // Copy to clipboard function
        function copyToClipboard(text, button) {
            navigator.clipboard.writeText(text).then(function() {
                const originalHTML = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check"></i> Copied!';
                button.classList.remove('btn-outline-secondary');
                button.classList.add('btn-success');
                
                setTimeout(function() {
                    button.innerHTML = originalHTML;
                    button.classList.remove('btn-success');
                    button.classList.add('btn-outline-secondary');
                }, 2000);
            }, function(err) {
                alert('Failed to copy: ' + err);
            });
        }
        
        // Confirm delete function
        function confirmDelete(studentId) {
            if (confirm('Are you sure you want to delete this student? This action cannot be undone.')) {
                window.location.href = 'delete_student.php?id=' + studentId;
            }
        }
        
        // Real-time clock (optional)
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit',
                second: '2-digit',
                hour12: true 
            });
            const dateString = now.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            if(document.getElementById('current-time')) {
                document.getElementById('current-time').innerHTML = 
                    `<i class="far fa-clock"></i> ${timeString} | ${dateString}`;
            }
        }
        
        // Update time every second if clock element exists
        if(document.getElementById('current-time')) {
            setInterval(updateTime, 1000);
            updateTime();
        }
        
        // Menu active state
        document.querySelectorAll('.menu-item a').forEach(link => {
            link.addEventListener('click', function() {
                document.querySelectorAll('.menu-item').forEach(item => {
                    item.classList.remove('active');
                });
                this.parentElement.classList.add('active');
            });
        });
    </script>
</body>
</html>