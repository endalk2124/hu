<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HU Informatics</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        /* Navbar Styling */
        .navbar {
            background: linear-gradient(135deg, #fff, #fff); /* White gradient background */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2); /* Subtle shadow */
            transition: all 0.3s ease;
        }

        /* Scrolled State */
        body.scroll-down .navbar {
            background: linear-gradient(135deg, #fff, #fff); /* White gradient remains unchanged */
            backdrop-filter: blur(10px); /* Frosted glass effect */
        }

        /* Brand Styling */
        .navbar-brand .hu-part {
            font-weight: 800;
            color: #2563eb; /* Blue-600 */
        }

        .navbar-brand span.d-none.d-md-inline {
            color: #000000; /* Black text for "Informatics" */
        }

        /* Nav Link Styling */
        .navbar-nav .nav-link {
            color: #000000; /* Black text for navigation links */
            position: relative;
            transition: color 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            color: #2563eb; /* Blue on hover */
        }

        /* Hover Underline Effect */
        .navbar-nav .nav-link:hover::after {
            content: '';
            position: absolute;
            width: 60%;
            height: 2px;
            bottom: 0;
            left: 20%;
            background-color: #2563eb; /* Blue underline */
        }

        /* Dropdown Menu Styling */
        .dropdown-menu {
            background-color: #FFFFFF; /* White background for dropdown */
            border: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .dropdown-item {
            color: #000000; /* Black text for dropdown items */
            transition: color 0.3s ease;
        }

        .dropdown-item:hover {
            color: #2563eb; /* Blue on hover */
            background-color: #f8f9fa; /* Light gray background */
        }

        /* Auth Buttons */
        .btn-link {
            color: #000000; /* Black text for auth buttons */
            text-decoration: none;
        }

        .btn-link:hover {
            color: #2563eb; /* Blue on hover */
            text-decoration: none;
        }

        .btn-primary {
            background-color: #2563eb; /* Primary blue button */
            border: none;
        }

        .btn-primary:hover {
            background-color: #1d4ed8; /* Darker blue on hover */
        }

        /* Dropdown Hover Effect */
        .dropdown:hover .dropdown-menu {
            display: block;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top py-3" id="mainNavbar">
        <div class="container">
            <!-- Brand/Logo -->
            <a class="navbar-brand" href="#">
                <span class="hu-part">HU</span>
                <span class="d-none d-md-inline">Informatics</span>
            </a>
            
            <!-- Mobile Toggle Button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navbar Content -->
            <div class="collapse navbar-collapse" id="navbarContent">
                <!-- Center Links -->
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Resources</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Forums</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            About
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">About Us</a></li>
                            <li><a class="dropdown-item" href="#">Contact</a></li>
                            <li><a class="dropdown-item" href="#">FAQ</a></li>
                        </ul>
                    </li>
                </ul>
                
                <!-- Right Auth Buttons -->
                <div class="d-flex gap-2">
                    <?php if (isset($_SESSION['logged_in'])) { ?>
                        <a href="/dashboard" class="btn btn-outline-primary btn-sm">Dashboard</a>
                        <a href="/logout" class="btn btn-link btn-sm">Sign Out</a>
                    <?php } else { ?>
                        <!-- Login Dropdown -->
                        <div class="dropdown">
                            <a class="btn btn-link btn-sm dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Sign In
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="studentlogin.php">Student</a></li>
                                <li><a class="dropdown-item" href="instructorlogin.php">Instructor</a></li>
                                <li><a class="dropdown-item" href="adminlogin.php">Admin</a></li>
                            </ul>
                        </div>

                        <!-- Register Dropdown -->
                        <div class="dropdown">
                            <a class="btn btn-primary btn-sm dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Register
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="register.php">Student</a></li>
                                <li><a class="dropdown-item" href="instructor_regist.php">Instructor</a></li>
                            </ul>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Bootstrap JS and Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Scroll Effect -->
    <script>
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('mainNavbar');
            if (window.scrollY > 10) {
                document.body.classList.add('scroll-down');
            } else {
                document.body.classList.remove('scroll-down');
            }
        });
    </script>
</body>
</html>