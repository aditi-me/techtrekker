<?php
// START ALL PHP PROCESSING *BEFORE* ANY HTML OR INCLUDES THAT OUTPUT HTML

// We do NOT need session_start() here directly, as it is already included
// at the very top of header.php, which is included later in this file.
session_start();
// 2. Include database connection file
require 'db.php'; // Assuming db.php establishes $connection

// 3. Check if the form is submitted and process it
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and get form data
    $admin_name = htmlspecialchars(trim($_POST['admin_name']));
    $admin_email = htmlspecialchars(trim($_POST['admin_email']));
    $admin_password = $_POST['upassword'];
    $confirm_admin_password = $_POST['confirm_upassword'];

    // Basic server-side validation
    if (empty($admin_name) || empty($admin_email) || empty($admin_password) || empty($confirm_admin_password)) {
        $_SESSION['signup_error'] = "All fields are required.";
        // IMPORTANT: If there's an error, redirect back to admin.php to display the error.
        header("Location: admin.php");
        exit(); // Always exit after a header redirect
    } elseif (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['signup_error'] = "Invalid email format.";
        header("Location: admin.php");
        exit();
    } elseif ($admin_password !== $confirm_admin_password) {
        $_SESSION['signup_error'] = "Passwords do not match.";
        header("Location: admin.php");
        exit();
    } else {
        // Check if email already exists
        $check_stmt = $connection->prepare("SELECT admin_id FROM admin WHERE admin_email = ?");
        $check_stmt->bind_param("s", $admin_email);
        $check_stmt->execute();
        $check_stmt->store_result();
        if ($check_stmt->num_rows > 0) {
            $_SESSION['signup_error'] = "Email address already registered.";
            $check_stmt->close(); // Close before redirecting
            $connection->close(); // Close connection before redirecting
            header("Location: admin.php");
            exit();
        } else {
            $check_stmt->close(); // Close the check statement as we are done with it

            // Hash the password before storing
            $admin_password_hashed = password_hash($admin_password, PASSWORD_DEFAULT);

            // Prepare and execute the SQL statement to insert data into the 'admin' table
            $stmt = $connection->prepare("INSERT INTO admin (admin_name, admin_email, admin_password_hashed) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $admin_name, $admin_email, $admin_password_hashed);

            if ($stmt->execute()) {
                // Registration successful, set a session variable for the modal success message
                $_SESSION['registration_success'] = "admin"; // Set to "admin" for clear differentiation
                $stmt->close(); // Close statement before redirect
                $connection->close(); // Close connection before redirect
                header("Location: index.php"); // Redirect to index.php
                exit(); // Always exit after a header redirect
            } else {
                // Registration failed
                $_SESSION['signup_error'] = "Error: " . $stmt->error;
                $stmt->close(); // Close statement before redirect
                $connection->close(); // Close connection before redirect
                header("Location: admin.php"); // Redirect back to admin.php
                exit();
            }
        }
    }
}

// NOW, AND ONLY NOW, INCLUDE THE HEADER THAT CONTAINS HTML
// The session will be active here because header.php calls session_start().
include 'header.php';
?>

<style>
    /* Custom styles to refine the layout and appearance */
    /*
        If your header.php does not set a background for the body,
        you might need to uncomment and adjust this:
    */
    /*
    body {
        background-color: #f8f9fa;
        margin: 0;
        padding: 0;
    }
    */

    .signup-page-wrapper {
        margin-top: 50px;
        min-height: 100vh; /* Full viewport height */
        display: flex;
        align-items: center; /* Vertically center content */
        justify-content: center; /* Horizontally center content */
        padding: 20px 0; /* Add some vertical padding */
    }

    .signup-container {
        padding: 30px;
        background-color: #fff; /* Default, will be overridden by media queries for glass effect */
        border-radius: 8px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1); /* Stronger shadow */
        margin: 0 auto;
    }

    .signup-container h2 {
        text-align: center;
        margin-bottom: 30px;
        color: #343a40;
        font-weight: 600; /* Slightly bolder heading */
    }

    /* Adjustments for Bootstrap form elements */
    .form-group label {
        font-weight: bold;
        color: #495057;
        margin-bottom: 0.5rem;
    }

    .form-control {
        padding: 0.75rem 1rem; /* More padding for inputs */
        border-radius: 0.25rem;
        border: 1px solid #ced4da;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-control:focus {
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
        color: #fff;
        padding: 0.75rem 1.5rem;
        border-radius: 0.25rem;
        cursor: pointer;
        font-size: 1.1rem; /* Slightly larger button text */
        font-weight: bold;
        width: 100%;
        margin-top: 15px; /* Space above button */
        transition: background-color 0.3s ease, border-color 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #0056b3;
        border-color: #0056b3;
    }

    .error-message {
        color: #dc3545; /* Bootstrap danger red */
        font-size: 0.875em; /* Smaller font size */
        margin-top: 0.25rem;
        display: block; /* Ensure it takes its own line */
    }

    /* Custom layout for form fields */
    .form-row {
        display: flex;
        flex-wrap: wrap; /* Allows items to wrap onto the next line on smaller screens */
        gap: 20px; /* Space between inline elements */
    }

    .form-row .form-group {
        flex: 1; /* Distribute available space equally */
        margin-bottom: 1rem;
        min-width: calc(50% - 10px); /* Adjust for two columns on wider screens */
    }

    /* Ensure single column layout on very small screens */
    @media (max-width: 575.98px) {
        .form-row .form-group {
            min-width: 100%; /* Full width for extra small screens */
        }
    }


    /* --- Glass Effect and Responsiveness for all screen sizes --- */
    /* Apply glass effect for screens wider than 768px (tablets and desktops) */
    @media (min-width: 768px) {
        .signup-container {
            background-color: rgba(167, 139, 250, 0.81); /* Semi-transparent background */
            backdrop-filter: blur(15px); /* Glass effect blur */
            -webkit-backdrop-filter: blur(15px); /* Safari compatibility */
            color: #fff;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            width: 80vw; /* Responsive width */
            max-width: 900px; /* Max width to prevent it from getting too wide */
        }

        .signup-container h2,
        .form-group label {
            color: #fff; /* White text for glass effect */
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.2); /* Lighter input background for glass effect */
            border-color: rgba(255, 255, 255, 0.5); /* Lighter border for glass effect */
            color: #fff; /* White text in inputs */
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.7); /* Lighter placeholder text */
        }

        .form-control:focus {
            border-color: #fff;
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
        }

        .btn-primary {
            background-color: #663399;
            border-color: #663399;
            color: #fff;
        }

        .btn-primary:hover {
            background-color: #4c2470;
            border-color: #4c2470;
        }

        .error-message {
            color: #ffcc00; /* Adjust error color for better visibility on glass effect */
        }
    }

    /* --- Glass Effect for Smaller Screens (Phones and Tablets) --- */
    /* Apply glass effect for screens smaller than 768px */
    @media (max-width: 767.98px) {
        .signup-container {
            background-color: rgba(167, 139, 250, 0.81); /* Semi-transparent background */
            backdrop-filter: blur(10px); /* Slightly less blur for smaller screens */
            -webkit-backdrop-filter: blur(10px); /* Safari compatibility */
            color: #fff;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            width: 90vw; /* Take up more width on smaller screens */
            padding: 20px; /* Slightly less padding */
        }

        .signup-container h2,
        .form-group label {
            color: #fff;
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.5);
            color: #fff;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .form-control:focus {
            border-color: #fff;
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
        }

        .btn-primary {
            background-color: #663399;
            border-color: #663399;
            color: #fff;
        }

        .btn-primary:hover {
            background-color: #4c2470;
            border-color: #4c2470;
        }

        .error-message {
            color: #ffcc00; /* Adjust error color */
        }

        /* Stack form-row elements on smaller screens */
        .form-row {
            flex-direction: column;
            gap: 0; /* Remove gap when stacked */
        }

        .form-row .form-group {
            margin-bottom: 1rem; /* Add margin between stacked groups */
        }
    }
</style>

<div class="signup-page-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="signup-container">
                    <h2>Admin Signup!</h2>
                    <?php
                    // Check for a signup error message
                    if (isset($_SESSION['signup_error'])) {
                        echo '
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Error!</strong> ' . htmlspecialchars($_SESSION['signup_error']) . '
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
                        // Unset the session variable so the alert doesn't show again on refresh
                        unset($_SESSION['signup_error']);
                    }
                    ?>
                    <form action="" method="POST"> <div class="form-group">
                            <label for="admin_name">Admin Name:</label>
                            <input type="text" class="form-control" id="admin_name" name="admin_name" required>
                        </div>

                        <div class="form-group">
                            <label for="admin_email">Email Address:</label>
                            <input type="email" class="form-control" id="admin_email" name="admin_email" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="password">Password:</label>
                                <input type="password" class="form-control" id="password" name="upassword" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirm Password:</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_upassword" required>
                                <span id="password_match_error" class="error-message" style="display: none;">Passwords do not match.</span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Register Admin</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    const passwordMatchError = document.getElementById('password_match_error');

    function validatePassword() {
        if (password.value !== confirmPassword.value) {
            passwordMatchError.style.display = 'block';
            confirmPassword.setCustomValidity("Passwords Don't Match");
        } else {
            passwordMatchError.style.display = 'none';
            confirmPassword.setCustomValidity(''); // Clear custom validity message
        }
    }

    // Add event listeners for immediate validation feedback
    password.addEventListener('change', validatePassword);
    confirmPassword.addEventListener('keyup', validatePassword);
</script>

<?php include 'footer.php'; ?>