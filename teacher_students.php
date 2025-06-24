<?php
// Ensure db.php is included to establish the database connection
require 'db.php';

// -----------------------------------------------------------
// Fetch All Enrollments for Display
// -----------------------------------------------------------
$enrollments = [];
$fetch_error = ''; // Initialize error variable

// Prepare the SQL query to select specific entries from the enrollments table,
// joining with 'streams' and 'courses' tables to get names instead of IDs.
// Assumes 'streams' table has 'stream_id' and 'stream_name' columns.
// Assumes 'courses' table has 'course_id' and 'course_name' columns.
$sql = "SELECT
            e.enrollment_id,
            s.stream_name,        -- Get stream name from the streams table
            c.course_name,        -- Get course name from the courses table
            e.total_duration,
            e.buyer_name,         -- Student's name
            e.buyer_email,        -- Student's email
            e.phone_number,       -- Student's phone number
            e.enrolled_at         -- Enrollment date
        FROM
            enrollments e
        JOIN
            streams s ON e.stream_id = s.stream_id
        JOIN
            courses c ON e.course_id = c.course_id
        ORDER BY
            e.enrolled_at DESC"; // Order by enrollment date descending

// Attempt to execute the query
if ($result = $connection->query($sql)) {
    // Check if any rows were returned
    if ($result->num_rows > 0) {
        // Loop through each row and add it to the enrollments array
        while ($row = $result->fetch_assoc()) {
            $enrollments[] = $row;
        }
    }
    // Free the result set
    $result->free();
} else {
    // If there's an error in the query, store the error message
    $fetch_error = "ERROR: Could not fetch enrollments. " . $connection->error;
}

// -----------------------------------------------------------
// Include the header file (assuming it contains necessary HTML head, body opening tags, and styling)
// -----------------------------------------------------------
require_once 'header.php';
?>

<div class="enrollment-wrapper" style="margin-top: 50px;">
    <div class="container">
        <div class="card p-4">
            <h1 class="text-center mb-4">All Course Enrollments!</h1>

            <?php
            // Display any fetch errors if they occurred
            if (!empty($fetch_error)) {
                echo "<p class='text-danger text-center'>" . $fetch_error . "</p>";
            }
            ?>

            <div class="p-4 border rounded card">
                <h2 class="mb-3">Enrollment Details</h2>
                <?php if (!empty($enrollments)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th scope="col">Stream Name</th>
                                <th scope="col">Course Name</th>
                                <th scope="col">Duration</th>
                                <th scope="col">Student Name</th>
                                <th scope="col">Student Email</th>
                                <th scope="col">Phone Number</th>
                                <th scope="col">Enrollment Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($enrollments as $enrollment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($enrollment['stream_name']); ?></td>
                                <td><?php echo htmlspecialchars($enrollment['course_name']); ?></td>
                                <td><?php echo htmlspecialchars($enrollment['total_duration']); ?></td>
                                <td><?php echo htmlspecialchars($enrollment['buyer_name']); ?></td>
                                <td><?php echo htmlspecialchars($enrollment['buyer_email']); ?></td>
                                <td><?php echo htmlspecialchars($enrollment['phone_number'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($enrollment['enrolled_at']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p class="text-center text-muted">No enrollments found in the database.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// -----------------------------------------------------------
// Include the footer file (assuming it contains necessary closing body and html tags)
// -----------------------------------------------------------
require_once 'footer.php';

// -----------------------------------------------------------
// Close Database Connection
// -----------------------------------------------------------
$connection->close();
?>
