<!-- student_sidebar.php -->
<nav id="sidebar" class="sidebar bg-white text-dark">
    <div class="sidebar-header d-flex justify-content-between align-items-center p-3 border-bottom">
        <div class="d-flex align-items-center">
            <i class="fas fa-graduation-cap me-2"></i>
            <span class="font-weight-bold">Student Portal</span>
        </div>
        <button class="btn btn-sm btn-outline-secondary d-lg-none" id="sidebarCollapse">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <ul class="list-unstyled components p-3">
        <li class="mb-1">
            <a href="student_dashboard.php" class="btn btn-light w-100 text-start <?php echo basename($_SERVER['PHP_SELF']) === 'student_dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-home me-2"></i> Dashboard
            </a>
        </li>
        <li class="mb-1">
            <a href="student_course.php" class="btn btn-light w-100 text-start <?php echo basename($_SERVER['PHP_SELF']) === 'student_courses.php' ? 'active' : ''; ?>">
                <i class="fas fa-book-open me-2"></i> My Courses
            </a>
        </li>
        <li class="mb-1">
            <a href="student_schedule.php" class="btn btn-light w-100 text-start">
                <i class="fas fa-calendar me-2"></i> Schedule
            </a>
        </li>
        <li class="mb-1">
            <a href="#" class="btn btn-light w-100 text-start">
                <i class="fas fa-file-alt me-2"></i> Resources
            </a>
        </li>
        <li class="mb-1">
            <a href="#" class="btn btn-light w-100 text-start">
                <i class="fas fa-comments me-2"></i> Forums
            </a>
        </li>
        <li class="mb-1">
            <a href="student_real_time_room.php" class="btn btn-light w-100 text-start <?php echo basename($_SERVER['PHP_SELF']) === 'real_time_room.php' ? 'active' : ''; ?>">
                <i class="fas fa-video me-2"></i> Join Real-Time Room
            </a>
        </li>
        <li class="mb-1">
            <a href="#" class="btn btn-light w-100 text-start">
                <i class="fas fa-cog me-2"></i> Settings
            </a>
        </li>
    </ul>

    <div class="sidebar-footer p-3 border-top">
        <div class="d-flex align-items-center mb-3">
            <div class="avatar me-2">J</div>
            <div>
                <div class="fw-bold">John Student</div>
                <small class="text-muted">student@example.com</small>
            </div>
        </div>
        <a href="#" class="btn btn-outline-secondary w-100">
            <i class="fas fa-sign-out-alt me-2"></i> <span class="logout-text">Logout</span>
        </a>
    </div>
</nav>

<script>
    // Sidebar toggle functionality
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarCollapse = document.getElementById('sidebarCollapse');

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function () {
                sidebar.classList.toggle('active');
            });
        }

        if (sidebarCollapse) {
            sidebarCollapse.addEventListener('click', function () {
                sidebar.classList.toggle('active');
            });
        }

        // Logout text visibility
        const logoutBtn = document.querySelector('.sidebar-footer .btn');
        if (logoutBtn) {
            const logoutText = logoutBtn.querySelector('.logout-text');
            logoutText.style.visibility = 'hidden';

            logoutBtn.addEventListener('mouseenter', function () {
                logoutText.style.visibility = 'visible';
            });

            logoutBtn.addEventListener('mouseleave', function () {
                logoutText.style.visibility = 'hidden';
            });
        }
    });
</script>