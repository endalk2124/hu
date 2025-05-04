<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="d-flex align-items-center">
            <i class="bi bi-layout-sidebar me-2"></i>
            <span class="fw-semibold">Admin Panel</span>
        </div>
    </div>
    <div class="sidebar-content">
        <ul class="sidebar-menu">
            <li class="sidebar-menu-item">
                <a href="admin_dashboard.php" class="sidebar-menu-button <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="user-management.php" class="sidebar-menu-button <?= basename($_SERVER['PHP_SELF']) === 'user-management.php' ? 'active' : '' ?>">
                    <i class="bi bi-people"></i>
                    <span>User Management</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="analytics.php" class="sidebar-menu-button <?= basename($_SERVER['PHP_SELF']) === 'analytics.php' ? 'active' : '' ?>">
                    <i class="bi bi-bar-chart"></i>
                    <span>Analytics</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="content-management.php" class="sidebar-menu-button <?= basename($_SERVER['PHP_SELF']) === 'content-management.php' ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>Content Management</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="settings.php" class="sidebar-menu-button <?= basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : '' ?>">
                    <i class="bi bi-gear"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
    </div>
    <div class="sidebar-footer">
        <div class="d-flex flex-column gap-2">
            <div class="d-flex align-items-center gap-3 p-2 rounded">
                <div class="bg-primary-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 2.5rem; height: 2.5rem;">
                    <?= strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)) ?>
                </div>
                <div>
                    <div class="fw-medium"><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></div>
                    <small class="text-muted"><?= htmlspecialchars($_SESSION['email'] ?? 'admin@example.com') ?></small>
                </div>
            </div>
            <a href="logout.php" class="btn btn-outline-primary w-100">
                <i class="bi bi-box-arrow-right me-2"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
</aside>