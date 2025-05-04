<header class="header">
    <button class="toggle-btn" id="sidebarToggle">
        <i class="bi bi-list"></i>
    </button>
    <span class="fw-semibold">Learning Management System</span>
    <div class="ms-auto d-flex align-items-center gap-3">
        <button class="btn btn-sm btn-outline-secondary position-relative">
            <i class="bi bi-bell"></i>
            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                <span class="visually-hidden">New alerts</span>
            </span>
        </button>
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2" type="button" id="userDropdown" data-bs-toggle="dropdown">
                <div class="bg-primary-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 1.75rem; height: 1.75rem;">
                    <?= strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)) ?>
                </div>
                <span><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
                <li><a class="dropdown-item" href="settings.php">Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</header>
<style>
        :root {
            --sidebar-width: 250px;
            --header-height: 60px;
            --primary: #3b7ddd;
            --primary-light: rgba(59, 125, 221, 0.1);
            --secondary: #6c757d;
            --success: #28a745;
            --info: #17a2b8;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #343a40;
            --muted: #6c757d;
            --border-color: #e9ecef;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fb;
            overflow-x: hidden;
        }
        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            z-index: 100;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
        }
        .sidebar-header {
            height: var(--header-height);
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid var(--border-color);
            padding: 0 1rem;
        }
        .sidebar-content {
            flex: 1;
            overflow-y: auto;
            padding: 1rem 0;
        }
        .sidebar-footer {
            border-top: 1px solid var(--border-color);
            padding: 1rem;
        }
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .sidebar-menu-item {
            margin: 0.25rem 0;
        }
        .sidebar-menu-button {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 0.5rem 1rem;
            color: var(--secondary);
            text-decoration: none;
            border-radius: 0.25rem;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        .sidebar-menu-button:hover {
            background-color: var(--primary-light);
            color: var(--primary);
        }
        .sidebar-menu-button.active {
            background-color: var(--primary-light);
            color: var(--primary);
            border-left: 3px solid var(--primary);
        }
        .sidebar-menu-button i {
            margin-right: 0.75rem;
            font-size: 1.1rem;
        }
        /* Main Content Styles */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s;
        }
        /* Header Styles */
        .header {
            height: var(--header-height);
            position: sticky;
            top: 0;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 99;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
        }
        .header .toggle-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--secondary);
            margin-right: 1rem;
        }
        /* Card Styles */
        .stat-card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        .stat-card .card-icon {
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }
        .stat-card .card-value {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        /* Activity Item */
        .activity-item {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.2s;
        }
        .activity-item:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
        /* Tab Styles */
        .nav-tabs .nav-link {
            border: none;
            color: var(--secondary);
            font-weight: 500;
            padding: 0.5rem 1rem;
        }
        .nav-tabs .nav-link.active {
            color: var(--primary);
            border-bottom: 2px solid var(--primary);
            background: transparent;
        }
        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar-show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>