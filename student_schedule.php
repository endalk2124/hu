
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Schedule - HU Informatics</title>
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
        .schedule-card {
            transition: box-shadow 0.3s ease;
            height: 100%;
        }
        .schedule-card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .progress {
            height: 6px;
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
                    <h1 class="h2">My Schedule</h1>
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="week-tab" data-bs-toggle="tab" data-bs-target="#week" type="button" role="tab">Week</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="month-tab" data-bs-toggle="tab" data-bs-target="#month" type="button" role="tab">Month</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="day-tab" data-bs-toggle="tab" data-bs-target="#day" type="button" role="tab">Day</button>
                        </li>
                    </ul>
                </div>
                <div class="tab-content" id="myTabContent">
                    <!-- Week View -->
                    <div class="tab-pane fade show active" id="week" role="tabpanel">
                        <div class="row g-4" id="week-schedule">
                            <!-- Week schedule will be loaded here via AJAX -->
                        </div>
                    </div>
                    <!-- Month View -->
                    <div class="tab-pane fade" id="month" role="tabpanel">
                        <div class="row g-4" id="month-schedule">
                            <!-- Month schedule will be loaded here via AJAX -->
                        </div>
                    </div>
                    <!-- Day View -->
                    <div class="tab-pane fade" id="day" role="tabpanel">
                        <div class="row g-4" id="day-schedule">
                            <!-- Day schedule will be loaded here via AJAX -->
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
        const scheduleData = [
            {
                id: "1",
                title: "Introduction to Programming",
                type: "lecture",
                courseCode: "CS101",
                instructor: "Dr. Jane Smith",
                location: "CS Building, Room 105",
                startTime: "2023-10-15T10:00:00",
                endTime: "2023-10-15T11:30:00",
                description: "Fundamentals of programming using Python."
            },
            {
                id: "2",
                title: "Calculus II",
                type: "lecture",
                courseCode: "MATH201",
                instructor: "Prof. Robert Johnson",
                location: "Math Building, Room 203",
                startTime: "2023-10-15T14:00:00",
                endTime: "2023-10-15T15:30:00",
                description: "Advanced integration techniques and applications."
            },
            {
                id: "3",
                title: "Physics Lab",
                type: "lab",
                courseCode: "PHYS210",
                instructor: "Prof. Michael Brown",
                location: "Science Center, Lab 305",
                startTime: "2023-10-16T09:00:00",
                endTime: "2023-10-16T11:00:00",
                description: "Hands-on experiments on Newton's laws."
            }
        ];

        // Helper function to format time
        function formatTime(dateString) {
            const date = new Date(dateString);
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }

        // Function to render week schedule
        function renderWeekSchedule() {
            const container = document.getElementById('week-schedule');
            container.innerHTML = '';
            scheduleData.forEach(event => {
                const eventCard = document.createElement('div');
                eventCard.className = 'col-md-6 col-lg-4';
                eventCard.innerHTML = `
                    <div class="card schedule-card mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0">${event.title}</h5>
                                <span class="badge bg-primary event-badge">${event.type}</span>
                            </div>
                            <p class="card-text text-muted small mb-3">
                                <i class="fas fa-user me-1"></i>${event.instructor}
                            </p>
                            <p class="card-text text-muted small mb-3">
                                <i class="fas fa-map-pin me-1"></i>${event.location}
                            </p>
                            <p class="card-text text-muted small mb-3">
                                <i class="fas fa-clock me-1"></i>${formatTime(event.startTime)} - ${formatTime(event.endTime)}
                            </p>
                            <p class="card-text">${event.description}</p>
                        </div>
                    </div>
                `;
                container.appendChild(eventCard);
            });
        }

        // Function to render month schedule
        function renderMonthSchedule() {
            const container = document.getElementById('month-schedule');
            container.innerHTML = '';
            scheduleData.forEach(event => {
                const eventCard = document.createElement('div');
                eventCard.className = 'col-md-6 col-lg-4';
                eventCard.innerHTML = `
                    <div class="card schedule-card mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0">${event.title}</h5>
                                <span class="badge bg-success event-badge">${event.courseCode}</span>
                            </div>
                            <p class="card-text text-muted small mb-3">
                                <i class="fas fa-calendar me-1"></i>${new Date(event.startTime).toLocaleDateString()}
                            </p>
                            <p class="card-text text-muted small mb-3">
                                <i class="fas fa-clock me-1"></i>${formatTime(event.startTime)} - ${formatTime(event.endTime)}
                            </p>
                            <p class="card-text">${event.description}</p>
                        </div>
                    </div>
                `;
                container.appendChild(eventCard);
            });
        }

        // Function to render day schedule
        function renderDaySchedule() {
            const container = document.getElementById('day-schedule');
            container.innerHTML = '';
            scheduleData.forEach(event => {
                const eventCard = document.createElement('div');
                eventCard.className = 'col-md-6 col-lg-4';
                eventCard.innerHTML = `
                    <div class="card schedule-card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-2">${event.title}</h5>
                            <p class="card-text text-muted small mb-3">
                                <i class="fas fa-clock me-1"></i>${formatTime(event.startTime)} - ${formatTime(event.endTime)}
                            </p>
                            <p class="card-text">${event.description}</p>
                        </div>
                    </div>
                `;
                container.appendChild(eventCard);
            });
        }

        // Function to simulate AJAX loading
        function loadData() {
            setTimeout(() => {
                renderWeekSchedule();
                renderMonthSchedule();
                renderDaySchedule();
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
                    if (target === '#week') {
                        renderWeekSchedule();
                    } else if (target === '#month') {
                        renderMonthSchedule();
                    } else if (target === '#day') {
                        renderDaySchedule();
                    }
                });
            });
        });
    </script>
</body>
</html>