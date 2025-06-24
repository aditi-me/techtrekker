<?php
session_start();

require 'db.php'; // Ensure this path is correct relative to login_process.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['upassword'] ?? ''; // This is the plain text password from the form

    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = "Please enter both email and password.";
        header('Location: index.php');
        exit();
    }

    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    $loggedIn = false;
    $userName = '';
    $userId = null;
    $userType = ''; // To store if it's a 'student', 'teacher', or 'admin'

    // --- Attempt to log in as a Student ---
    try {
        $sqlStudent = "SELECT student_id, first_name, password_hash FROM students WHERE email = ?";
        $stmtStudent = mysqli_prepare($connection, $sqlStudent);

        if ($stmtStudent === false) {
            error_log("MySQLi student statement preparation failed: " . mysqli_error($connection));
            // Don't set error here, try other tables
        } else {
            mysqli_stmt_bind_param($stmtStudent, "s", $email);
            mysqli_stmt_execute($stmtStudent);
            $resultStudent = mysqli_stmt_get_result($stmtStudent);
            $student = mysqli_fetch_assoc($resultStudent);
            mysqli_stmt_close($stmtStudent);

            if ($student && password_verify($password, $student['password_hash'])) {
                $loggedIn = true;
                $userName = $student['first_name'];
                $userId = $student['student_id'];
                $userType = 'student';
            }
        }
    } catch (Throwable $e) {
        error_log("Login process error (Student): " . $e->getMessage());
    }

    // --- If not logged in as a student, attempt to log in as a Teacher ---
    if (!$loggedIn) {
        try {
            $sqlTeacher = "SELECT teacher_id, full_name, password_hashed FROM teachers WHERE email_address = ?";
            $stmtTeacher = mysqli_prepare($connection, $sqlTeacher);

            if ($stmtTeacher === false) {
                error_log("MySQLi teacher statement preparation failed: " . mysqli_error($connection));
                // Don't set error here, try other tables
            } else {
                mysqli_stmt_bind_param($stmtTeacher, "s", $email);
                mysqli_stmt_execute($stmtTeacher);
                $resultTeacher = mysqli_stmt_get_result($stmtTeacher);
                $teacher = mysqli_fetch_assoc($resultTeacher);
                mysqli_stmt_close($stmtTeacher);

                if ($teacher && password_verify($password, $teacher['password_hashed'])) {
                    $loggedIn = true;
                    $userName = $teacher['full_name'];
                    $userId = $teacher['teacher_id'];
                    $userType = 'teacher';
                }
            }
        } catch (Throwable $e) {
            error_log("Login process error (Teacher): " . $e->getMessage());
        }
    }

    // --- If not logged in as a student or teacher, attempt to log in as an Admin ---
    if (!$loggedIn) {
        try {
            $sqlAdmin = "SELECT admin_id, admin_name, admin_password_hashed FROM admin WHERE admin_email = ?";
            $stmtAdmin = mysqli_prepare($connection, $sqlAdmin);

            if ($stmtAdmin === false) {
                error_log("MySQLi admin statement preparation failed: " . mysqli_error($connection));
            } else {
                mysqli_stmt_bind_param($stmtAdmin, "s", $email);
                mysqli_stmt_execute($stmtAdmin);
                $resultAdmin = mysqli_stmt_get_result($stmtAdmin);
                $admin = mysqli_fetch_assoc($resultAdmin);
                mysqli_stmt_close($stmtAdmin);

                if ($admin && password_verify($password, $admin['admin_password_hashed'])) {
                    $loggedIn = true;
                    $userName = $admin['admin_name'];
                    $userId = $admin['admin_id'];
                    $userType = 'admin'; // Set user type to 'admin'
                }
            }
        } catch (Throwable $e) {
            error_log("Login process error (Admin): " . $e->getMessage());
        }
    }


    // --- Final Check and Session Setup ---
    if ($loggedIn) {
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $userName;
        $_SESSION['user_type'] = $userType; // Store the user type

        unset($_SESSION['login_error']);
        unset($_SESSION['modal_success_message']); // Clear any pending success messages

        // Headers to prevent caching (good practice after login)
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

        header('Location: index.php');
        exit();
    } else {
        // Failed login: User not found in any table or password incorrect
        $_SESSION['login_error'] = "Invalid email or password.";
        header('Location: index.php');
        exit();
    }

} else {
    // If someone tries to access login_process.php directly without POST
    header('Location: index.php');
    exit();
}
?>