<?php
// student_management.php

// Start the session to access session variables


// Default user data in case session data is missing (for demonstration purposes)
$user = [
    'name' => $_SESSION['user']['name'] ?? 'Instructor',
    'email' => $_SESSION['user']['email'] ?? 'instructor@example.com'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - HU Informatics</title>
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
            --warning-color: #fd7e14;
            --danger-color: #dc3545;
        }
        body {
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .wrapper {
            display: flex;
            min-height: 100vh;
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
        }
        .main-content {
            width: 100%;
            min-height: 100vh;
            padding: 20px;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .btn-group-sm .btn {
            font-size: 0.875rem;
        }
        .nav-tabs .nav-link.active {
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block sidebar bg-white text-dark p-3">
            <?php include 'instructor_sidebar.php'; ?>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 ms-sm-auto col-lg-10 main-content bg-light">
            <!-- Top Navigation -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
                <div class="container-fluid">
                    <button class="btn btn-light me-2 d-lg-none" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <span class="navbar-brand fw-bold">HU Informatics - Instructor</span>
                </div>
            </nav>

            <!-- Dashboard Content -->
            <div class="container-fluid p-4">
                <header>
                    <h2 class="text-2xl font-bold">Student Management</h2>
                    <p class="text-muted">Manage your students and their progress</p>
                </header>

                <!-- Tab Navigation -->
                <ul class="nav nav-tabs mb-4" id="studentTabs">
                    <li class="nav-item">
                        <a class="nav-link active" href="#students" data-bs-toggle="tab">Students</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#grades" data-bs-toggle="tab">Grades</a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="studentTabsContent">
                    <!-- Students Tab -->
                    <div class="tab-pane fade show active" id="students">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="h4">All Students</h3>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                                <i class="fas fa-plus-circle me-2"></i>Add Student
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Course</th>
                                        <th>Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="students-table-body">
                                    <?php
                                    $sampleStudents = [
                                        [
                                            "id" => "1",
                                            "name" => "John Doe",
                                            "email" => "john.doe@example.com",
                                            "course" => "CS301",
                                            "status" => "active"
                                        ],
                                        [
                                            "id" => "2",
                                            "name" => "Jane Smith",
                                            "email" => "jane.smith@example.com",
                                            "course" => "CS210",
                                            "status" => "inactive"
                                        ]
                                    ];
                                    foreach ($sampleStudents as $student) {
                                        echo '<tr>';
                                        echo '<td>' . $student['name'] . '</td>';
                                        echo '<td>' . $student['email'] . '</td>';
                                        echo '<td>' . $student['course'] . '</td>';
                                        echo '<td><span class="badge bg-' . ($student['status'] === 'active' ? 'success' : 'warning') . '">' . ucfirst($student['status']) . '</span></td>';
                                        echo '<td class="text-center">';
                                        echo '<button class="btn btn-sm btn-outline-secondary me-2"><i class="fas fa-eye"></i></button>';
                                        echo '<button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>';
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Grades Tab -->
                    <div class="tab-pane fade" id="grades">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="h4">Grades Overview</h3>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGradeModal">
                                <i class="fas fa-plus-circle me-2"></i>Add Grade
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Student Name</th>
                                        <th>Assignment</th>
                                        <th>Score</th>
                                        <th>Max Score</th>
                                        <th>Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="grades-table-body">
                                    <?php
                                    $sampleGrades = [
                                        [
                                            "id" => "1",
                                            "studentName" => "John Doe",
                                            "assignment" => "Database Design Project",
                                            "score" => "85",
                                            "maxScore" => "100",
                                            "status" => "graded"
                                        ],
                                        [
                                            "id" => "2",
                                            "studentName" => "Jane Smith",
                                            "assignment" => "Web Portfolio Creation",
                                            "score" => "78",
                                            "maxScore" => "100",
                                            "status" => "pending"
                                        ]
                                    ];
                                    foreach ($sampleGrades as $grade) {
                                        echo '<tr>';
                                        echo '<td>' . $grade['studentName'] . '</td>';
                                        echo '<td>' . $grade['assignment'] . '</td>';
                                        echo '<td>' . $grade['score'] . '</td>';
                                        echo '<td>' . $grade['maxScore'] . '</td>';
                                        echo '<td><span class="badge bg-' . ($grade['status'] === 'graded' ? 'success' : 'warning') . '">' . ucfirst($grade['status']) . '</span></td>';
                                        echo '<td class="text-center">';
                                        echo '<button class="btn btn-sm btn-outline-secondary me-2"><i class="fas fa-edit"></i></button>';
                                        echo '<button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>';
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Student Modal -->
    <div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStudentModalLabel">Add New Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="student-name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="student-name" placeholder="Enter student name">
                        </div>
                        <div class="mb-3">
                            <label for="student-email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="student-email" placeholder="Enter student email">
                        </div>
                        <div class="mb-3">
                            <label for="student-course" class="form-label">Course</label>
                            <select class="form-select" id="student-course">
                                <option value="CS301">Database Systems (CS301)</option>
                                <option value="CS210">Web Development (CS210)</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary">Add Student</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Grade Modal -->
    <div class="modal fade" id="addGradeModal" tabindex="-1" aria-labelledby="addGradeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addGradeModalLabel">Add Grade</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="student-name-grade" class="form-label">Student Name</label>
                            <input type="text" class="form-control" id="student-name-grade" placeholder="Enter student name">
                        </div>
                        <div class="mb-3">
                            <label for="assignment-name" class="form-label">Assignment</label>
                            <input type="text" class="form-control" id="assignment-name" placeholder="Enter assignment name">
                        </div>
                        <div class="mb-3">
                            <label for="score" class="form-label">Score</label>
                            <input type="number" class="form-control" id="score" placeholder="Enter score">
                        </div>
                        <div class="mb-3">
                            <label for="max-score" class="form-label">Max Score</label>
                            <input type="number" class="form-control" id="max-score" placeholder="Enter max score">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary">Save Grade</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar toggle functionality
        const sidebar = document.querySelector('.sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function () {
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
            link.addEventListener('shown.bs.tab', function (event) {
                const target = event.target.getAttribute('href');
                history.replaceState(null, '', target);
            });
        });

        // Simulate AJAX loading for students and grades
        function loadData() {
            setTimeout(() => {
                console.log("Data loaded via AJAX");
                // In a real app, replace this with actual AJAX calls to fetch data
            }, 500);
        }

        document.addEventListener('DOMContentLoaded', function () {
            loadData();
        });
    </script>
</body>
</html>