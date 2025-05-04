
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Learning - HU Informatics</title>
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
            max-width: 1440px; /* Constrain the entire page width */
            margin: 0 auto; /* Center the wrapper horizontally */
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
        .main-content {
            width: 100%;
            min-height: 100vh;
            padding: 20px;
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
        .course-card {
            transition: box-shadow 0.3s ease;
            height: 100%;
        }
        .course-card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .course-img-container {
            position: relative;
            overflow: hidden;
            height: 0;
            padding-bottom: 56.25%; /* 16:9 aspect ratio */
        }
        .course-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .department-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: rgba(13, 110, 253, 0.8);
        }
        .progress {
            height: 6px;
        }
        .assignment-card {
            border-left: 4px solid #fd7e14;
        }
        .assignment-card.completed {
            border-left-color: #198754;
        }
        .resource-table tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
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
            <?php include 'student_sidebar.php'; ?>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 ms-sm-auto col-lg-10 main-content bg-light">
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h2">My Learning</h1>
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="courses-tab" data-bs-toggle="tab" data-bs-target="#courses" type="button" role="tab">Courses</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="resources-tab" data-bs-toggle="tab" data-bs-target="#resources" type="button" role="tab">Resources</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="assignments-tab" data-bs-toggle="tab" data-bs-target="#assignments" type="button" role="tab">Assignments</button>
                        </li>
                    </ul>
                </div>
                <div class="tab-content" id="myTabContent">
                    <!-- Courses Tab -->
                    <div class="tab-pane fade show active" id="courses" role="tabpanel">
                        <div class="row g-4" id="courses-container">
                            <!-- Courses will be loaded here via AJAX -->
                        </div>
                    </div>
                    <!-- Resources Tab -->
                    <div class="tab-pane fade" id="resources" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2 class="h4">Course Resources</h2>
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-secondary btn-sm">Sort</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm">Filter</button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover resource-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Course</th>
                                        <th>Type</th>
                                        <th>Size</th>
                                        <th>Date</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="resources-table-body">
                                    <!-- Resources will be loaded here via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Assignments Tab -->
                    <div class="tab-pane fade" id="assignments" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2 class="h4">My Assignments</h2>
                            <button type="button" class="btn btn-outline-secondary btn-sm">Filter</button>
                        </div>
                        <div class="row" id="assignments-container">
                            <!-- Assignments will be loaded here via AJAX -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sample data (would normally come from an API via AJAX)
        const coursesData = [
            {
                id: "cs101",
                title: "Introduction to Computer Science",
                instructor: "Dr. Jane Smith",
                department: "Computer Science",
                progress: 65,
                coverImage: "https://images.unsplash.com/photo-1517694712202-14dd9538aa97",
                schedule: "Mon, Wed, Fri - 10:00 AM",
                room: "CS Building, Room 105",
                totalModules: 12,
                completedModules: 8,
                description: "An introduction to the fundamental principles of computer science and programming using Python.",
                upcoming: {
                    title: "Mid-term Exam",
                    date: "Oct 15, 2023",
                    type: "Examination"
                }
            },
            {
                id: "math201",
                title: "Calculus II",
                instructor: "Prof. Robert Johnson",
                department: "Mathematics",
                progress: 42,
                coverImage: "https://images.unsplash.com/photo-1635070041078-e363dbe005cb",
                schedule: "Tue, Thu - 2:00 PM",
                room: "Math Building, Room 203",
                totalModules: 10,
                completedModules: 4,
                description: "Continuing concepts from Calculus I, including integration techniques, applications, and infinite series.",
                upcoming: {
                    title: "Problem Set 5 Due",
                    date: "Oct 12, 2023",
                    type: "Assignment"
                }
            }
        ];

        const resourcesData = [
            {
                id: "1",
                title: "Introduction to Algorithms",
                type: "PDF",
                size: "2.4 MB",
                course: "CS101",
                uploadDate: "Sep 15, 2023"
            },
            {
                id: "2",
                title: "Calculus Reference Sheet",
                type: "PDF",
                size: "1.2 MB",
                course: "MATH201",
                uploadDate: "Sep 20, 2023"
            }
        ];

        const assignmentsData = [
            {
                id: "1",
                title: "Algorithm Analysis Exercise",
                course: "CS101",
                dueDate: "Oct 12, 2023",
                status: "pending",
                points: "20"
            },
            {
                id: "2",
                title: "Integral Problem Set",
                course: "MATH201",
                dueDate: "Oct 15, 2023",
                status: "completed",
                points: "15"
            }
        ];

        // Function to render courses
        function renderCourses() {
            const container = document.getElementById('courses-container');
            container.innerHTML = '';
            coursesData.forEach(course => {
                const courseCard = document.createElement('div');
                courseCard.className = 'col-md-6 col-lg-4';
                courseCard.innerHTML = `
                    <div class="card course-card mb-4">
                        <div class="course-img-container">
                            <img src="${course.coverImage}" alt="${course.title}" class="course-img">
                            <span class="badge department-badge">${course.department}</span>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0">${course.title}</h5>
                            </div>
                            <p class="card-text text-muted small mb-3">${course.instructor}</p>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span>Progress</span>
                                    <span class="fw-medium">${course.progress}%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: ${course.progress}%" aria-valuenow="${course.progress}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <div class="pt-2">
                                <div class="d-flex align-items-center text-muted small mb-2">
                                    <i class="bi bi-clock me-2"></i>
                                    <span>${course.schedule}</span>
                                </div>
                                <div class="d-flex align-items-center text-muted small">
                                    <i class="bi bi-mortarboard me-2"></i>
                                    <span>${course.completedModules} of ${course.totalModules} modules completed</span>
                                </div>
                            </div>
                            <div class="pt-3 mt-3 border-top">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="small text-muted mb-1">Next up</p>
                                        <p class="small fw-medium mb-0">${course.upcoming.title}</p>
                                        <p class="small text-muted">${course.upcoming.date}</p>
                                    </div>
                                    <a href="/course/${course.id}" class="btn btn-outline-primary btn-sm">
                                        Go to Course <i class="bi bi-chevron-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                container.appendChild(courseCard);
            });
        }

        // Function to render resources
        function renderResources() {
            const container = document.getElementById('resources-table-body');
            container.innerHTML = '';
            resourcesData.forEach(resource => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark me-2 text-muted"></i>
                            <span class="fw-medium">${resource.title}</span>
                        </div>
                    </td>
                    <td>${resource.course}</td>
                    <td><span class="badge bg-light text-dark border">${resource.type}</span></td>
                    <td class="text-muted">${resource.size}</td>
                    <td class="text-muted">${resource.uploadDate}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-link">Download</button>
                    </td>
                `;
                container.appendChild(row);
            });
        }

        // Function to render assignments
        function renderAssignments() {
            const container = document.getElementById('assignments-container');
            container.innerHTML = '';
            assignmentsData.forEach(assignment => {
                const assignmentCard = document.createElement('div');
                assignmentCard.className = 'col-12 mb-3';
                assignmentCard.innerHTML = `
                    <div class="card assignment-card ${assignment.status === 'completed' ? 'completed' : ''}">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-start">
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                        <i class="bi bi-file-earmark text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">${assignment.title}</h6>
                                        <div class="d-flex align-items-center text-muted small">
                                            <i class="bi bi-book me-1"></i>
                                            <span>${assignment.course}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="d-flex align-items-center justify-content-end small text-muted">
                                        <i class="bi bi-calendar me-1"></i>
                                        <span>Due ${assignment.dueDate}</span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-end small text-muted mt-1">
                                        <i class="bi bi-mortarboard me-1"></i>
                                        <span>${assignment.points} points</span>
                                    </div>
                                    <button class="btn btn-sm mt-2 ${assignment.status === 'completed' ? 'btn-outline-success' : 'btn-primary'}">
                                        ${assignment.status === 'completed' ? 'View Submission' : 'Submit'}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                container.appendChild(assignmentCard);
            });
        }

        // Function to simulate AJAX loading
        function loadData() {
            setTimeout(() => {
                renderCourses();
                renderResources();
                renderAssignments();
            }, 500);
        }

        // Initialize the page when loaded
        document.addEventListener('DOMContentLoaded', function () {
            loadData();

            // Sidebar toggle functionality
            const sidebar = document.querySelector('.sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function () {
                    sidebar.classList.toggle('active');
                });
            }
        });
    </script>
</body>
</html>