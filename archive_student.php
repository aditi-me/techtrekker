<?php
// archive_student.php

// 1. Include your database connection file
require 'db.php';

// 2. Check if the request method is POST and if student_id is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'])) {
    // 3. Get and validate the student_id
    // FILTER_VALIDATE_INT ensures it's an integer and sanitizes it
    $student_id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);

    // If student_id is not a valid integer, redirect with an error status
    if ($student_id === false || $student_id === null) {
        // You might want to log this error for debugging
        error_log("Invalid student ID received for archiving: " . $_POST['student_id']);
        header('Location: admin_students.php?status=invalid_id'); // Redirect back to admin_students.php
        exit(); // Stop script execution
    }

    // 4. Start a database transaction for data integrity
    // This ensures that either all operations succeed, or none do.
    $connection->begin_transaction();

    try {
        // 5. Prepare the SQL statement to update the student's 'deleted_at' column
        // We set deleted_at to the current timestamp to mark the student as archived.
        $stmt = $connection->prepare("UPDATE students SET deleted_at = CURRENT_TIMESTAMP WHERE student_id = ?");

        // Check if the prepare statement failed
        if ($stmt === false) {
            throw new Exception("Prepare statement failed: " . $connection->error);
        }

        // 6. Bind the student_id parameter to the prepared statement
        // "i" specifies that the parameter is an integer
        $stmt->bind_param("i", $student_id);

        // 7. Execute the prepared statement
        if ($stmt->execute()) {
            // 8. Commit the transaction if the update was successful
            $connection->commit();
            // Redirect back to the student list page with a success status
            header('Location: admin_students.php?status=archived_success'); // Redirect to admin_students.php
            exit();
        } else {
            // If execution failed, throw an exception
            throw new Exception("Execute statement failed: " . $stmt->error);
        }

    } catch (Exception $e) {
        // 9. Rollback the transaction if any error occurred
        $connection->rollback();
        // Log the error
        error_log("Student archiving failed: " . $e->getMessage());
        // Redirect back with an error status
        header('Location: admin_students.php?status=archive_error'); // Redirect to admin_students.php
        exit();
    } finally {
        // 10. Close the prepared statement and the database connection
        if (isset($stmt)) {
            $stmt->close();
        }
        $connection->close();
    }
} else {
    // 11. If not a POST request or student_id is not set, redirect to the list page
    header('Location: admin_students.php'); // Redirect to admin_students.php
    exit();
}
?>