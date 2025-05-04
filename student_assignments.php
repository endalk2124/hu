
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignments - HU Informatics</title>
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
        .assignment-card {
            transition: box-shadow 0.3s ease;
            height: 100%;
        }
        .assignment-card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .event-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
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
                    <h1 class="h2">My Assignments</h1>
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">All</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">Pending</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab">Completed</button>
                        </li>
                    </ul>
                </div>
                <div class="tab-content" id="myTabContent">
                    <!-- All Assignments -->
                    <div class="tab-pane fade show active" id="all" role="tabpanel">
                        <div class="row g-4" id="all-assignments">
                            <!-- All assignments will be loaded here via AJAX -->
                        </div>
                    </div>
                    <!-- Pending Assignments -->
                    <div class="tab-pane fade" id="pending" role="tabpanel">
                        <div class="row g-4" id="pending-assignments">
                            <!-- Pending assignments will be loaded here via AJAX -->
                        </div>
                    </div>
                    <!-- Completed Assignments -->
                    <div class="tab-pane fade" id="completed" role="tabpanel">
                        <div class="row g-4" id="completed-assignments">
                            <!-- Completed assignments will be loaded here via AJAX -->
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
        const assignmentsData = [
            {
                id: "1",
                title: "Algorithm Analysis Exercise",
                course: "Introduction to Computer Science",
                courseCode: "CS101",
                description: "Analyze the time and space complexity of the provided algorithms.",
                dueDate: "Oct 12, 2023",
                status: "pending",
                points: "20",
                type: "Individual Assignment",
                timeRemaining: "3 days"
            },
            {
                id: "2",
                title: "Integral Problem Set",
                course: "Calculus II",
                courseCode: "MATH201",
                description: "Complete problems 1-15 on integration by parts and substitution methods.",
                dueDate: "Oct 15, 2023",
                status: "completed",
                points: "15",
                type: "Problem Set",
                timeRemaining: "Expired"
            },
            {
                id: "3",
                title: "Research Paper Outline",
                course: "Academic Writing",
                courseCode: "ENG150",
                description: "Submit an outline for your research paper on modern web technologies.",
                dueDate: "Oct 18, 2023",
                status: "pending",
                points: "25",
                type: "Group Assignment",
                timeRemaining: "5 days"
            },
            {
                id: "4",
                title: "Database Design Project",
                course: "Introduction to Computer Science",
                courseCode: "CS101",
                description: "Create an ER diagram and implement a simple relational database based on the case study.",
                dueDate: "Oct 20, 2023",
                status: "pending",
                points: "40",
                type: "Project",
                timeRemaining: "11 days"
            },
            {
                id: "5",
                title: "Calculus Mid-term Exam",
                course: "Calculus II",
                courseCode: "MATH201",
                description: "Comprehensive exam covering all material from weeks 1-6.",
                dueDate: "Oct 5, 2023",
                status: "completed",
                points: "100",
                type: "Exam",
                timeRemaining: "Expired"
            }
        ];

        // Function to render assignments
        function renderAssignments(containerId, filterStatus = null) {
            const container = document.getElementById(containerId);
            container.innerHTML = '';
            assignmentsData.forEach(assignment => {
                if (filterStatus && assignment.status !== filterStatus) return;

                const assignmentCard = document.createElement('div');
                assignmentCard.className = 'col-md-6 col-lg-4';
                assignmentCard.innerHTML = `
                    <div class="card assignment-card mb-4 ${assignment.status === 'completed' ? 'border-success' : 'border-warning'}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0">${assignment.title}</h5>
                                <span class="badge bg-${assignment.status === 'completed' ? 'success' : 'warning'} event-badge">${assignment.status}</span>
                            </div>
                            <p class="card-text text-muted small mb-3">
                                <i class="fas fa-book me-1"></i>${assignment.course} (${assignment.courseCode})
                            </p>
                            <p class="card-text small mb-3">
                                <i class="fas fa-calendar me-1"></i>Due: ${assignment.dueDate}
                            </p>
                            <p class="card-text small mb-3">
                                <i class="fas fa-clock me-1"></i>Time Remaining: ${assignment.timeRemaining}
                            </p>
                            <p class="card-text">${assignment.description}</p>
                            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                <small class="text-muted"><i class="fas fa-star me-1"></i>${assignment.points} Points</small>
                                <button class="btn btn-sm ${assignment.status === 'completed' ? 'btn-outline-success' : 'btn-primary'}">
                                    ${assignment.status === 'completed' ? 'View Submission' : 'Submit'}
                                </button>
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
                renderAssignments('all-assignments');
                renderAssignments('pending-assignments', 'pending');
                renderAssignments('completed-assignments', 'completed');
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

            // Tab switching logic
            const tabLinks = document.querySelectorAll('[data-bs-toggle="tab"]');
            tabLinks.forEach(link => {
                link.addEventListener('shown.bs.tab', function (event) {
                    const target = event.target.getAttribute('href');
                    if (target === '#all') {
                        renderAssignments('all-assignments');
                    } else if (target === '#pending') {
                        renderAssignments('pending-assignments', 'pending');
                    } else if (target === '#completed') {
                        renderAssignments('completed-assignments', 'completed');
                    }
                });
            });
        });
    </script>
</body>
</html>