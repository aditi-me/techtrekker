<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>TechTrekker - Learn Anytime, Anywhere</title>
    <link href="index.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        /* Base style for nav-links (no color, default font-weight) */
        .navbar-nav .nav-link {
            color: inherit;
            /* Inherit color, effectively no specific color set by default */
            font-weight: normal;
            /* Default font-weight */
        }
        /* Style for active nav-link */
        .navbar-nav .nav-link.active {
            font-weight: bold;
            color: black !important;
            /* Use !important to override Bootstrap's default link color if necessary */
        }
        /* Style for the welcome message */
        .welcome-message {
            margin-right: 15px;
            /* Adjust spacing as needed */
            font-weight: bold;
            color: #333;
            /* Darker color for visibility */
        }
        .welcome-message {
            margin-right: 15px;
            /* Adjust spacing as needed */
            font-weight: bold;
            color: #333;
            /* Darker color for visibility */
        }
        /* Add this style block for the sidebar */
        .right-sidebar {
            height: 100%;
            width: 0;
            /* Hidden by default */
            position: fixed;
            z-index: 1050;
            /* Above Bootstrap modals, but below higher z-indexes if any */
            top: 0;
            right: 0;
            background-color: #f8f9fa;
            /* Light background */
            overflow-x: hidden;
            transition: 0.5s;
            /* Smooth slide effect */
            padding-top: 60px;
            box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
            /* Subtle shadow */
            display: flex;
            flex-direction: column;
            justify-content: center;
            /* Center content vertically */
            align-items: center;
            /* Center content horizontally */
            text-align: center;
        }
        .right-sidebar.open {
            width: 350px;
            /* Adjust width as needed when open */
        }
        .right-sidebar .closebtn {
            position: absolute;
            top: 0;
            right: 25px;
            font-size: 36px;
            margin-left: 50px;
            text-decoration: none;
            color: #818181;
        }
        .right-sidebar-content {
            padding: 20px;
        }
        .right-sidebar .btn-login-signup {
            font-size: 1.2rem;
            /* Larger font for prominence */
            padding: 10px 30px;
            /* More padding */
            font-weight: bold;
            border-radius: 0.5rem;
            /* Slightly more rounded corners */
            margin-top: 20px;
            /* Space from the text */
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">TechTrekker</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-3">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="courses.php">Courses</a></li>
                    <li class="nav-item"><a class="nav-link" href="teachers.php">Teachers</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    <?php
                    // Check if the user is logged in using the session variable
                    $isLoggedIn = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
                    $userName = $_SESSION['user_name'] ?? ''; // Get user name, default to empty string
                    ?>

                    <?php if ($isLoggedIn) : ?>
                        <li class="nav-item welcome-message" style="color: #a78bfa;">
                            Welcome, <?php echo htmlspecialchars($userName); ?>
                    <?php else : ?>
                        <li class="nav-item">
                            <a class="nav-link btn btn-outline-primary px-3 rounded" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item d-none d-lg-block">
                        <span style="font-size: 1.8rem; cursor: pointer;" id="userIcon">
                            <?php
                            if ($isLoggedIn) {
                                echo '<i class="fa-solid fa-circle-user"></i>'; // Logged in icon
                            } else {
                                echo '<i class="fa-regular fa-circle-user"></i>'; // Not logged in icon
                            }
                            ?>
                        </span>
                    </li>
                    <?php if ($isLoggedIn) : ?>
                        <li class="nav-item d-none d-lg-block">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #A78BFA; color: white;">
                    <h5 class="modal-title w-100 text-center" id="loginModalLabel">Login</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="login_process.php" method="POST">
                        <div class="mb-3">
                            <label for="emailInput" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="emailInput" name="email" placeholder="name@example.com" required />
                        </div>
                        <div class="mb-3">
                            <label for="passwordInput" class="form-label">Password</label>
                            <input type="password" class="form-control" id="passwordInput" name="upassword" placeholder="Password" required />
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Submit</button>
                        <p class="text-center mt-3">If you do not have an account? <a href="signup.php">Sign up</a></p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57B+UoKzO3QdJgGgzaK9fG/I5aTq0QzB3I3R3S3T3" crossorigin="anonymous"></script>