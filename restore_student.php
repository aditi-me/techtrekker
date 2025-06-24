<?php
session_start(); // Start the session if not already started (good practice for messages)
require 'db.php'; // Include your database connection file

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if student_id is set and is an integer
    if (isset($_POST['student_id']) && filter_var($_POST['student_id'], FILTER_VALIDATE_INT)) {
        $student_id = $_POST['student_id'];

        // Prepare the SQL statement to update the deleted_at column to NULL
        $stmt = $connection->prepare("UPDATE students SET deleted_at = NULL WHERE student_id = ?");
        $stmt->bind_param("i", $student_id);

        if ($stmt->execute()) {
            // Success: Student restored
            $_SESSION['success_message'] = "Student restored successfully!";
        } else {
            // Error: Failed to restore student
            $_SESSION['error_message'] = "Error restoring student: " . $stmt->error;
        }

        $stmt->close();
    } else {
        // Error: Invalid or missing student_id
        $_SESSION['error_message'] = "Invalid request: Student ID is missing or invalid.";
    }
} else {
    // Error: Not a POST request
    $_SESSION['error_message'] = "Invalid request method.";
}

// Close the database connection
$connection->close();

// Redirect back to admin_students.php
header("Location: admin_students.php");
exit(); // Always exit after a header redirect
?>