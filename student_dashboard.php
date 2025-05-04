<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - HU Informatics</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --sidebar-bg: #f8f9fa;
            --sidebar-text: #212529;
            --sidebar-active: #e9ecef;
            --sidebar-hover: #e9ecef;
            --primary-color: #0d6efd;
            --success-color: #198754;
            --purple-color: #6f42c1;
            --warning-color: #fd7e14;
            --danger-color: #dc3545;
        }

        body {
            min-height: 100vh;
            background-color: #f8f9fa;
        }

        .wrapper {
            min-height: 100vh;
            display: flex;
        }

        .sidebar {
            min-width: 250px;
            max-width: 250px;
            min-height: 100vh;
            background-color: var(--sidebar-bg);
            color: var(--sidebar-text);
            transition: all 0.3s;
            border-right: 1px solid #dee2e6;
        }

        .sidebar.active {
            margin-left: -250px;
        }

        .main-content {
            width: 100%;
            min-height: 100vh;
            transition: all 0.3s;
        }

        .stat-card {
            height: 100%;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .file-icon-pdf {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
        }

        .file-icon-doc {
            background-color: rgba(13, 110, 253, 0.1);
            color: var(--primary-color);
        }

        .file-icon-ppt {
            background-color: rgba(253, 126, 20, 0.1);
            color: var(--warning-color);
        }

        .badge-department {
            background-color: rgba(25, 135, 84, 0.1);
            color: var(--success-color);
        }

        .badge-course {
            background-color: rgba(108, 117, 125, 0.1);
            color: #6c757d;
        }

        @media (max-width: 992px) {
            .sidebar {
                margin-left: -250px;
                position: fixed;
                z-index: 1000;
                height: 100vh;
            }
            .sidebar.active {
                margin-left: 0;
            }
            .main-content {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!--#include file="student_sidebar.html" -->
        <?php include 'student_sidebar.php'; ?>
        <!-- Main Content -->
        <div class="main-content bg-light">
            <!-- Top Navigation -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
                <div class="container-fluid">
                    <button class="btn btn-light me-2 d-lg-none" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <span class="navbar-brand fw-bold">HU Informatics</span>
                </div>
            </nav>

            <!-- Dashboard Content -->
            <div class="container-fluid p-4">
                <!-- Tab Navigation -->
                <ul class="nav nav-tabs mb-4" id="dashboardTabs">
                    <li class="nav-item">
                        <a class="nav-link active" href="#overview" data-bs-toggle="tab">Overview</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#resources" data-bs-toggle="tab">Resources</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#forums" data-bs-toggle="tab">Forums</a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Overview Tab -->
                    <div class="tab-pane fade show active" id="overview">
                        <header class="mb-5">
                            <h2 class="fw-bold">Welcome, John Student</h2>
                            <p class="text-muted">Here's what's happening in your learning dashboard</p>
                        </header>

                        <!-- Quick Stats -->
                        <div class="row g-4 mb-4">
                            <div class="col-md-6 col-lg-3">
                                <div class="card h-100 shadow-sm stat-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="card-subtitle text-muted">Enrolled Courses</h6>
                                            <i class="fas fa-book-open text-primary fs-4"></i>
                                        </div>
                                        <h3 class="card-title fw-bold">6</h3>
                                        <p class="card-text text-muted small">Current semester</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 col-lg-3">
                                <div class="card h-100 shadow-sm stat-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="card-subtitle text-muted">Downloaded Resources</h6>
                                            <i class="fas fa-file-alt text-success fs-4"></i>
                                        </div>
                                        <h3 class="card-title fw-bold">24</h3>
                                        <p class="card-text text-muted small">Last 30 days</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 col-lg-3">
                                <div class="card h-100 shadow-sm stat-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="card-subtitle text-muted">Forum Participation</h6>
                                            <i class="fas fa-comments text-purple fs-4"></i>
                                        </div>
                                        <h3 class="card-title fw-bold">8</h3>
                                        <p class="card-text text-muted small">Topics contributed to</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 col-lg-3">
                                <div class="card h-100 shadow-sm stat-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="card-subtitle text-muted">Study Time</h6>
                                            <i class="fas fa-clock text-warning fs-4"></i>
                                        </div>
                                        <h3 class="card-title fw-bold">42h</h3>
                                        <p class="card-text text-muted small">This month</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="d-flex flex-wrap gap-2 mb-4">
                            <a href="#" class="btn btn-outline-primary">
                                <i class="fas fa-graduation-cap me-2"></i> My Courses
                            </a>
                            <a href="#resources" class="btn btn-outline-success" data-bs-toggle="tab">
                                <i class="fas fa-file-alt me-2"></i> Find Resources
                            </a>
                            <a href="#forums" class="btn btn-outline-purple" data-bs-toggle="tab">
                                <i class="fas fa-comments me-2"></i> Join Discussion
                            </a>
                        </div>

                        <!-- Recent Items -->
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-white">
                                        <h5 class="card-title mb-0">Recent Resources</h5>
                                    </div>
                                    <div class="list-group list-group-flush">
                                        <a href="#" class="list-group-item list-group-item-action">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-file-pdf text-danger me-3"></i>
                                                <div>
                                                    <h6 class="mb-1">Introduction to Database Systems</h6>
                                                    <small class="text-muted">Computer Science • 2023-05-15</small>
                                                </div>
                                            </div>
                                        </a>
                                        <a href="#" class="list-group-item list-group-item-action">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-file-word text-primary me-3"></i>
                                                <div>
                                                    <h6 class="mb-1">Web Development Fundamentals</h6>
                                                    <small class="text-muted">Information Systems • 2023-06-20</small>
                                                </div>
                                            </div>
                                        </a>
                                        <a href="#" class="list-group-item list-group-item-action">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-file-powerpoint text-warning me-3"></i>
                                                <div>
                                                    <h6 class="mb-1">Network Security Protocols</h6>
                                                    <small class="text-muted">Information Technology • 2023-07-10</small>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <a href="#resources" class="btn btn-link ps-0" data-bs-toggle="tab">View all resources</a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-6">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-white">
                                        <h5 class="card-title mb-0">Upcoming Events</h5>
                                    </div>
                                    <div class="list-group list-group-flush">
                                        <a href="#" class="list-group-item list-group-item-action">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-calendar-day text-primary me-3"></i>
                                                <div>
                                                    <h6 class="mb-1">Web Development Workshop</h6>
                                                    <small class="text-muted">Tomorrow, 2:00 PM • Virtual Meeting</small>
                                                </div>
                                            </div>
                                        </a>
                                        <a href="#" class="list-group-item list-group-item-action">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-calendar-check text-success me-3"></i>
                                                <div>
                                                    <h6 class="mb-1">Database Systems Final Exam</h6>
                                                    <small class="text-muted">June 15, 9:00 AM • Room 101</small>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <a href="#" class="btn btn-link ps-0">View calendar</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resources Tab -->
                    <div class="tab-pane fade" id="resources">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h2 class="fw-bold mb-1">Learning Resources</h2>
                                <p class="text-muted">Browse and access all available educational materials</p>
                            </div>
                            <button class="btn btn-primary">
                                <i class="fas fa-download me-2"></i> Download All
                            </button>
                        </div>

                        <div class="row g-4">
                            <!-- Resource 1 -->
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start gap-3 mb-3">
                                            <div class="file-icon-pdf rounded p-2">
                                                <i class="fas fa-file-pdf fs-4"></i>
                                            </div>
                                            <div>
                                                <h5 class="card-title mb-1">Introduction to Database Systems</h5>
                                                <div class="d-flex flex-wrap gap-1 mb-2">
                                                    <span class="badge badge-department">Computer Science</span>
                                                    <span class="badge badge-course">Database Systems</span>
                                                </div>
                                                <p class="card-text text-muted small">PDF • 4.2 MB</p>
                                            </div>
                                        </div>
                                        <p class="card-text">Comprehensive introduction to database concepts, design principles, and implementation.</p>
                                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                            <small class="text-muted">Uploaded: 2023-05-15 by Dr. Smith</small>
                                            <div>
                                                <button class="btn btn-sm btn-outline-secondary me-1">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-primary">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Resource 2 -->
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start gap-3 mb-3">
                                            <div class="file-icon-doc rounded p-2">
                                                <i class="fas fa-file-word fs-4"></i>
                                            </div>
                                            <div>
                                                <h5 class="card-title mb-1">Web Development Fundamentals</h5>
                                                <div class="d-flex flex-wrap gap-1 mb-2">
                                                    <span class="badge badge-department">Information Systems</span>
                                                    <span class="badge badge-course">Web Technologies</span>
                                                </div>
                                                <p class="card-text text-muted small">DOCX • 2.1 MB</p>
                                            </div>
                                        </div>
                                        <p class="card-text">Learn the basics of HTML, CSS, and JavaScript for modern web development.</p>
                                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                            <small class="text-muted">Uploaded: 2023-06-20 by Prof. Johnson</small>
                                            <div>
                                                <button class="btn btn-sm btn-outline-secondary me-1">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-primary">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Resource 3 -->
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start gap-3 mb-3">
                                            <div class="file-icon-ppt rounded p-2">
                                                <i class="fas fa-file-powerpoint fs-4"></i>
                                            </div>
                                            <div>
                                                <h5 class="card-title mb-1">Network Security Protocols</h5>
                                                <div class="d-flex flex-wrap gap-1 mb-2">
                                                    <span class="badge badge-department">Information Technology</span>
                                                    <span class="badge badge-course">Network Security</span>
                                                </div>
                                                <p class="card-text text-muted small">PPTX • 8.5 MB</p>
                                            </div>
                                        </div>
                                        <p class="card-text">An overview of modern security protocols used in computer networks.</p>
                                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                            <small class="text-muted">Uploaded: 2023-07-10 by Dr. Williams</small>
                                            <div>
                                                <button class="btn btn-sm btn-outline-secondary me-1">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-primary">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Forums Tab -->
                    <div class="tab-pane fade" id="forums">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h2 class="fw-bold mb-1">Discussion Forums</h2>
                                <p class="text-muted">Engage with the academic community and participate in discussions</p>
                            </div>
                            <button class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i> New Topic
                            </button>
                        </div>

                        <div class="row g-4">
                            <!-- Forum 1 -->
                            <div class="col-md-6">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <span class="badge bg-light text-dark mb-2">Database Systems</span>
                                                <h5 class="card-title mb-1">Database Normalization Techniques</h5>
                                                <p class="card-text text-muted">Discussion on various database normalization techniques and their applications.</p>
                                            </div>
                                            <div class="text-center bg-light rounded p-2">
                                                <div class="fw-bold">24</div>
                                                <small class="text-muted">Replies</small>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar me-2">J</div>
                                                <div>
                                                    <small class="fw-bold">John Doe</small>
                                                    <small class="text-muted ms-1">(Instructor)</small>
                                                </div>
                                            </div>
                                            <div class="d-flex gap-3">
                                                <small class="text-muted">
                                                    <i class="fas fa-users me-1"></i> 12
                                                </small>
                                                <small class="text-muted">
                                                    <i class="fas fa-comment me-1"></i> 24
                                                </small>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i> 2h ago
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Forum 2 -->
                            <div class="col-md-6">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <span class="badge bg-light text-dark mb-2">Web Development</span>
                                                <h5 class="card-title mb-1">Best Practices for Secure Web Development</h5>
                                                <p class="card-text text-muted">Let's discuss the best security practices when developing web applications.</p>
                                            </div>
                                            <div class="text-center bg-light rounded p-2">
                                                <div class="fw-bold">15</div>
                                                <small class="text-muted">Replies</small>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar me-2">J</div>
                                                <div>
                                                    <small class="fw-bold">Jane Smith</small>
                                                    <small class="text-muted ms-1">(Student)</small>
                                                </div>
                                            </div>
                                            <div class="d-flex gap-3">
                                                <small class="text-muted">
                                                    <i class="fas fa-users me-1"></i> 8
                                                </small>
                                                <small class="text-muted">
                                                    <i class="fas fa-comment me-1"></i> 15
                                                </small>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i> 1d ago
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar toggle functionality
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                });
            }

            // Activate tab based on URL hash
            if (window.location.hash) {
                const tabTrigger = document.querySelector(`[href="${window.location.hash}"]`);
                if (tabTrigger) {
                    const tab = new bootstrap.Tab(tabTrigger);
                    tab.show();
                }
            }

            // Update active state in sidebar when tab changes
            const tabLinks = document.querySelectorAll('[data-bs-toggle="tab"]');
            tabLinks.forEach(link => {
                link.addEventListener('shown.bs.tab', function(event) {
                    const target = event.target.getAttribute('href');
                    const sidebarLinks = document.querySelectorAll('.sidebar-menu a');
                    
                    sidebarLinks.forEach(link => {
                        link.classList.remove('active');
                        if (link.getAttribute('href') === target) {
                            link.classList.add('active');
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>