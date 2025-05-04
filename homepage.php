<?php
// Start session for authentication check
session_start();
$isAuthenticated = isset($_SESSION['user']);

// Sample data for resources
$resources = [
    [
        'id' => 1,
        'title' => 'Web Development Fundamentals',
        'description' => 'Comprehensive guide covering HTML, CSS, and JavaScript basics for beginners.',
        'fileType' => 'pdf',
        'fileSize' => '2.4 MB',
        'uploadDate' => '2023-10-15',
        'department' => 'Computer Science',
        'course' => 'CS-101',
        'uploader' => 'Dr. Alemayehu'
    ],
    [
        'id' => 2,
        'title' => 'Network Security Protocols',
        'description' => 'Detailed explanation of modern network security protocols and implementations.',
        'fileType' => 'docx',
        'fileSize' => '1.8 MB',
        'uploadDate' => '2023-10-10',
        'department' => 'Information Technology',
        'course' => 'IT-205',
        'uploader' => 'Prof. Kebede'
    ],
    [
        'id' => 3,
        'title' => 'Welcome to Graduate Studies',
        'description' => 'Orientation materials for new graduate students in the Faculty of Informatics.',
        'fileType' => 'ppt',
        'fileSize' => '5.2 MB',
        'uploadDate' => '2023-09-28',
        'department' => 'Graduate School',
        'course' => 'GS-001',
        'uploader' => 'Dean Office'
    ]
];

// Sample data for forum previews
$forumPosts = [
    [
        'id' => 1,
        'title' => 'Web Development Fundamentals',
        'description' => 'Discussion about the basics of web development for beginners in the program.',
        'category' => 'CS-101',
        'participants' => 24,
        'replies' => 15,
        'lastActivity' => '2 hours ago',
        'creator' => [
            'name' => 'Dr. Alemayehu',
            'role' => 'Instructor'
        ]
    ],
    [
        'id' => 2,
        'title' => 'Assignment 3 Questions',
        'description' => 'Clarifications needed for the third assignment in Network Security course.',
        'category' => 'IT-205',
        'participants' => 18,
        'replies' => 7,
        'lastActivity' => '1 day ago',
        'creator' => [
            'name' => 'Meron',
            'role' => 'Student'
        ]
    ],
    [
        'id' => 3,
        'title' => 'Research Methodology Workshop',
        'description' => 'Planning the upcoming workshop on research methodologies for graduate students.',
        'category' => 'GS-001',
        'participants' => 12,
        'replies' => 5,
        'lastActivity' => '3 days ago',
        'creator' => [
            'name' => 'Prof. Kebede',
            'role' => 'Professor'
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hawassa University Faculty of Informatics - Learning Platform</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link href="style/style.css" rel="stylesheet">
</head>
<body>
    <!-- Include Navbar -->
    <?php include 'navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section position-relative vh-100 d-flex align-items-center justify-content-center overflow-hidden">
        <!-- Background decoration -->
        <div class="position-absolute top-0 start-0 w-100 h-100 overflow-hidden pe-none">
            <div class="position-absolute rounded-circle bg-primary bg-opacity-10 blur" style="width: 24rem; height: 24rem; top: -5rem; right: -5rem;"></div>
            <div class="position-absolute rounded-circle bg-info bg-opacity-10 blur" style="width: 24rem; height: 24rem; bottom: -10rem; left: -5rem;"></div>
            <div class="position-absolute rounded-circle bg-primary bg-opacity-10 blur" style="width: 18rem; height: 18rem; top: 33%; left: 25%;"></div>
        </div>

        <div class="container px-4 position-relative z-3">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 mb-4 fw-medium">
                        Hawassa University Faculty of Informatics
                    </span>
                    
                    <h1 class="display-4 fw-bold mb-4">
                        Modern Learning & Collaboration Platform
                    </h1>
                    
                    <p class="lead text-muted mb-5 mx-auto" style="max-width: 42rem;">
                        Access resources, collaborate with peers, and enhance your learning experience
                        with our comprehensive platform designed specifically for Hawassa University's
                        Faculty of Informatics.
                    </p>
                    
                    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                        <?php if ($isAuthenticated): ?>
                            <a href="/student_dashboard.php" class="btn btn-primary btn-lg px-4">
                                Go to login
                            </a>
                        <?php else: ?>
                            <a href="register.php" class="btn btn-primary btn-lg px-4">
                                Get Started
                            </a>
                            <a href="studentlogin.php" class="btn btn-outline-primary btn-lg px-4">
                                Sign In
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Scroll indicator -->
        <div class="position-absolute bottom-3 start-50 translate-middle-x d-none d-md-block">
            <i class="bi bi-chevron-down fs-4 text-muted animate-bounce"></i>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section py-5 bg-light">
        <div class="container px-4 py-5">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-8 text-center">
                    <h2 class="display-5 fw-bold mb-3">Comprehensive Learning Platform</h2>
                    <p class="lead text-muted">
                        Designed with students and instructors in mind, our platform offers everything
                        you need to enhance the teaching and learning experience.
                    </p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card p-4 rounded-3 h-100">
                        <div class="feature-icon bg-primary bg-opacity-10 text-primary rounded-2 p-2 mb-3 d-inline-flex">
                            <i class="bi bi-book fs-4"></i>
                        </div>
                        <h3 class="h4 fw-semibold mb-2">Learning Resources</h3>
                        <p class="text-muted mb-0">
                            Access a centralized repository of course materials, lecture notes, and educational resources organized by department and course.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card p-4 rounded-3 h-100">
                        <div class="feature-icon bg-primary bg-opacity-10 text-primary rounded-2 p-2 mb-3 d-inline-flex">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                        <h3 class="h4 fw-semibold mb-2">Role-Based Access</h3>
                        <p class="text-muted mb-0">
                            Different interfaces and capabilities for students, instructors, and administrators, ensuring everyone has the right tools.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card p-4 rounded-3 h-100">
                        <div class="feature-icon bg-primary bg-opacity-10 text-primary rounded-2 p-2 mb-3 d-inline-flex">
                            <i class="bi bi-chat-left-text fs-4"></i>
                        </div>
                        <h3 class="h4 fw-semibold mb-2">Discussion Forums</h3>
                        <p class="text-muted mb-0">
                            Participate in threaded course discussions, ask questions, and engage with peers and instructors in a collaborative environment.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card p-4 rounded-3 h-100">
                        <div class="feature-icon bg-primary bg-opacity-10 text-primary rounded-2 p-2 mb-3 d-inline-flex">
                            <i class="bi bi-file-earmark fs-4"></i>
                        </div>
                        <h3 class="h4 fw-semibold mb-2">File Management</h3>
                        <p class="text-muted mb-0">
                            Easily upload, download, and manage course materials with built-in file previews and organization tools.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card p-4 rounded-3 h-100">
                        <div class="feature-icon bg-primary bg-opacity-10 text-primary rounded-2 p-2 mb-3 d-inline-flex">
                            <i class="bi bi-search fs-4"></i>
                        </div>
                        <h3 class="h4 fw-semibold mb-2">Powerful Search</h3>
                        <p class="text-muted mb-0">
                            Quickly find the resources you need with our comprehensive search functionality across all platform content.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card p-4 rounded-3 h-100">
                        <div class="feature-icon bg-primary bg-opacity-10 text-primary rounded-2 p-2 mb-3 d-inline-flex">
                            <i class="bi bi-shield-lock fs-4"></i>
                        </div>
                        <h3 class="h4 fw-semibold mb-2">Secure Authentication</h3>
                        <p class="text-muted mb-0">
                            Robust security features including role-based permissions and password recovery to keep your account safe.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Resources Section -->
    <section class="resources-section py-5">
        <div class="container px-4 py-5">
            <h2 class="display-5 fw-bold mb-5 text-center">Featured Resources</h2>
            
            <div class="row g-4">
                <?php foreach ($resources as $resource): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-start gap-3">
                                <?php
                                $iconClass = '';
                                $bgClass = '';
                                if ($resource['fileType'] === 'pdf') {
                                    $iconClass = 'text-danger';
                                    $bgClass = 'bg-danger bg-opacity-10';
                                } elseif (in_array($resource['fileType'], ['doc', 'docx'])) {
                                    $iconClass = 'text-primary';
                                    $bgClass = 'bg-primary bg-opacity-10';
                                } elseif (in_array($resource['fileType'], ['ppt', 'pptx'])) {
                                    $iconClass = 'text-warning';
                                    $bgClass = 'bg-warning bg-opacity-10';
                                } elseif (in_array($resource['fileType'], ['xls', 'xlsx'])) {
                                    $iconClass = 'text-success';
                                    $bgClass = 'bg-success bg-opacity-10';
                                } else {
                                    $iconClass = 'text-secondary';
                                    $bgClass = 'bg-secondary bg-opacity-10';
                                }
                                ?>
                                <div class="flex-shrink-0 <?= $bgClass ?> rounded-2 d-flex align-items-center justify-content-center p-2">
                                    <i class="bi bi-file-earmark-<?= $resource['fileType'] === 'pdf' ? 'pdf' : 'text' ?> fs-4 <?= $iconClass ?>"></i>
                                </div>
                                <div>
                                    <h3 class="h5 fw-semibold mb-1"><?= htmlspecialchars($resource['title']) ?></h3>
                                    <div class="d-flex flex-wrap gap-1 mb-2">
                                        <span class="badge bg-primary bg-opacity-10 text-primary"><?= htmlspecialchars($resource['department']) ?></span>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary"><?= htmlspecialchars($resource['course']) ?></span>
                                        <span class="text-muted small"><?= strtoupper($resource['fileType']) ?> • <?= $resource['fileSize'] ?></span>
                                    </div>
                                    <p class="small text-muted mb-0"><?= htmlspecialchars($resource['description']) ?></p>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                <div class="small text-muted">
                                    Uploaded on <?= $resource['uploadDate'] ?> by <?= htmlspecialchars($resource['uploader']) ?>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye me-1"></i> Preview
                                    </button>
                                    <button class="btn btn-sm btn-primary">
                                        <i class="bi bi-download me-1"></i> Download
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Forum Preview Section -->
    <section class="forum-section py-5 bg-light">
        <div class="container px-4 py-5">
            <h2 class="display-5 fw-bold mb-5 text-center">Active Discussions</h2>
            
            <div class="row g-4">
                <?php foreach ($forumPosts as $post): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <span class="badge bg-light text-dark border mb-2"><?= htmlspecialchars($post['category']) ?></span>
                                    <h3 class="h5 fw-semibold"><?= htmlspecialchars($post['title']) ?></h3>
                                    <p class="small text-muted mb-0"><?= htmlspecialchars($post['description']) ?></p>
                                </div>
                                
                                <div class="bg-light rounded-3 px-3 py-2 text-center">
                                    <span class="fw-semibold d-block"><?= $post['replies'] ?></span>
                                    <span class="small text-muted">Replies</span>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center pt-3 mt-3 border-top">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px;">
                                        <?= strtoupper(substr($post['creator']['name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <span class="small fw-medium"><?= htmlspecialchars($post['creator']['name']) ?></span>
                                        <span class="small text-muted ms-1">(<?= htmlspecialchars($post['creator']['role']) ?>)</span>
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-3 small text-muted">
                                    <span class="d-flex align-items-center">
                                        <i class="bi bi-people-fill me-1"></i>
                                        <?= $post['participants'] ?>
                                    </span>
                                    <span class="d-flex align-items-center">
                                        <i class="bi bi-chat-left-text me-1"></i>
                                        <?= $post['replies'] ?>
                                    </span>
                                    <span class="d-flex align-items-center">
                                        <i class="bi bi-clock me-1"></i>
                                        <?= $post['lastActivity'] ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Include Footer -->
    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="js/script.js"></script>
</body>
</html>