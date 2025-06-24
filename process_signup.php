<?php
// process_signup.php
session_start(); // Start the session at the very beginning of the script

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize input data
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $surname = htmlspecialchars(trim($_POST['surname']));
    $email = htmlspecialchars(trim($_POST['email']));
    $phone_number = htmlspecialchars(trim($_POST['phone_number']));
    $last_qualification = htmlspecialchars(trim($_POST['last_qualification']));
    $last_percentage = trim($_POST['last_percentage']); // Keep as string for initial validation
    $upassword = $_POST['upassword'];
    $confirm_upassword = $_POST['confirm_upassword'];

    // --- Server-side Validation ---
    // All fields are required
    if (empty($first_name) || empty($surname) || empty($email) || empty($phone_number) || empty($last_qualification) || empty($last_percentage) || empty($upassword) || empty($confirm_upassword)) {
        $_SESSION['signup_error'] = "All fields are required. Please fill them out.";
        header("Location: signup.php");
        exit();
    }

    // Passwords match validation
    if ($upassword !== $confirm_upassword) {
        $_SESSION['signup_error'] = "Passwords do not match. Please ensure both password fields are identical.";
        header("Location: signup.php");
        exit();
    }

    // Validate and convert last_percentage to a float
    if (!is_numeric($last_percentage) || $last_percentage < 0 || $last_percentage > 100) {
        $_SESSION['signup_error'] = "Please enter a valid percentage for 'Last Qualification Percentage' (0-100).";
        header("Location: signup.php");
        exit();
    }
    $last_percentage_float = (float)$last_percentage; // Convert to float after validation

    // Hash the password
    $hashed_password = password_hash($upassword, PASSWORD_DEFAULT);

    // Database Connection
    require 'db.php'; // Ensure this path is correct and establishes $connection

    // Check database connection
    if ($connection->connect_error) {
        error_log("Database connection failed: " . $connection->connect_error);
        $_SESSION['signup_error'] = "A server error occurred (DB connection). Please try again later.";
        header("Location: signup.php");
        exit();
    }

    // Check if email already exists
    $check_sql = "SELECT email FROM students WHERE email = ?";
    $check_stmt = $connection->prepare($check_sql);

    // Crucial check: Verify if prepare() for check_stmt was successful
    if ($check_stmt === false) {
        error_log("SQL prepare failed for email check: " . $connection->error . " Query: " . $check_sql);
        $_SESSION['signup_error'] = "An internal server error occurred during email check. Please try again.";
        header("Location: signup.php");
        exit();
    }

    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        $_SESSION['signup_error'] = "This email is already registered. Please try logging in or use a different email.";
        $check_stmt->close();
        $connection->close();
        header("Location: signup.php");
        exit();
    }
    $check_stmt->close();


    // Prepare and Execute SQL INSERT statement
    $sql = "INSERT INTO students (first_name, surname, email, phone_number, last_qualification, last_percentage, password_hash) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $connection->prepare($sql);

    // Crucial check: Verify if prepare() for insert was successful
    if ($stmt === false) {
        error_log("SQL prepare failed for insert: " . $connection->error . " Query: " . $sql);
        $_SESSION['signup_error'] = "An internal server error occurred during registration. Please try again.";
        header("Location: signup.php");
        exit();
    }

    // Bind parameters: "sssssds" -> 6 strings, 1 double (for percentage)
    $stmt->bind_param("sssssds", $first_name, $surname, $email, $phone_number, $last_qualification, $last_percentage_float, $hashed_password);

    if ($stmt->execute()) {
        // Registration successful
        $_SESSION['signup_success_message'] = "Registration successful! You can now log in.";
        header("Location: index.php"); // Redirect to your login/home page
        exit();
    } else {
        // If there's an error during execution (e.g., database constraint violation)
        error_log("SQL execute failed: " . $stmt->error); // Log the specific statement error
        $_SESSION['signup_error'] = "Registration failed. Please try again.";
        header("Location: signup.php"); // Redirect back to signup on error
        exit();
    }

    // Close statement and connection
    $stmt->close();
    $connection->close();

} else {
    // If the request is not a POST request, redirect to the signup form
    header("Location: signup.php");
    exit();
}
?>