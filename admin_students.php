<?php
include 'header.php';
require 'db.php'; // This line establishes the $connection

$sql = "
    SELECT
        s.student_id,
        s.first_name,
        s.surname,
        s.email,
        s.phone_number,
        s.deleted_at,
        e.course_name AS enrolled_course_name,
        e.total_duration AS enrollment_duration,
        c.estimated_duration AS course_standard_duration,
        e.enrolled_at
    FROM
        students s
    LEFT JOIN
        enrollments e ON s.email = e.buyer_email
    LEFT JOIN
        courses c ON e.course_id = c.course_id
    ORDER BY
        CASE WHEN s.deleted_at IS NOT NULL THEN 1 ELSE 0 END,
        CASE WHEN e.enrolled_at IS NULL THEN 1 ELSE 0 END,
        s.first_name, s.surname,
        e.enrolled_at DESC;
";

$result = $connection->query($sql);

?>

    <style>
        /* ... Your existing CSS styles ... */

       

        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }

        .card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
            padding: 20px;
            max-width: 1200px; /* Adjust as needed */
        }

        .card-header {
            font-size: 1.5em;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            color: #555;
        }

        /* The tr:nth-child(even) rule will now be overridden by .row-color-X !important for active rows */
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .no-enrollments {
            color: #888;
            font-style: italic;
        }

        /* COMPLETE Color definitions for active student rows with !important */
        .row-color-0 {
            background-color: #FFCDD2 !important;
            /* Lighter Sunday Red (from #E57373) */
        }

        .row-color-1 {
            background-color: #FFF9C4 !important;
            /* Lighter Monday Yellow (from #FFD700) */
        }

        .row-color-2 {
            background-color: #BBDEFB !important;
            /* Lighter Tuesday Blue (from #64B5F6) */
        }

        .row-color-3 {
            background-color: #C8E6C9 !important;
            /* Lighter Wednesday Green (from #81C784) */
        }

        .row-color-4 {
            background-color: #FFE0B2 !important;
            /* Lighter Thursday Orange (from #FFB74D) */
        }

        .row-color-5 {
            background-color: #E1BEE7 !important;
            /* Lighter Friday Purple (from #BA68C8) */
        }

        .row-color-6 {
            background-color: #B2EBF2 !important;
            /* Lighter Saturday Cyan (from #4DD0E1) */
        }


        /* Styling for the soft-deleted rows - these will override ANY other color */
        .deleted-student {
            filter: grayscale(100%);
            /* Makes the row black and white */
            opacity: 0.6;
            /* Makes it slightly faded */
            background-color: #f0f0f0 !important;
            /* Override all other row backgrounds */
            color: #555;
            /* Darker grey text for deleted rows */
        }

        /* Ensure deleted student cells also use the grey color */
        .deleted-student td {
            color: #555;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
            /* Space between buttons */
            justify-content: flex-end;
            /* Push buttons to the right */
        }

        .delete-btn,
        .restore-btn {
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            white-space: nowrap;
            /* Prevent text wrapping */
        }

        .delete-btn {
            background-color: #dc3545;
            /* Red */
            color: white;
        }

        .delete-btn:hover {
            background-color: #c82333;
            /* Darker red */
        }

        .restore-btn {
            background-color: #28a745;
            /* Green */
            color: white;
        }

        .restore-btn:hover {
            background-color: #218838;
            /* Darker green */
        }

        /* Optional: Icon styling if using Font Awesome */
        .fas {
            margin-right: 5px;
        }

        th {
            background-color: white;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<body>

    <div class="card">
        <div class="card-header" style="background-color:#a78bfa; color:white;font-weight:bold;">
            Admin Panel: Student Enrollment List
        </div>
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Phone Number</th>
                    <th>Email ID</th>
                    <th>Enrolled Course</th>
                    <th>Enrolled Duration</th>
                    <th>Course Completion</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    $active_row_counter = 0; // Initialize counter for active rows
                    while ($row = $result->fetch_assoc()) {
                        $is_deleted = !empty($row['deleted_at']);
                        $row_class = ''; // Default empty class

                        if ($is_deleted) {
                            $row_class = 'deleted-student';
                        } else {
                            // Apply color cycling to non-deleted students
                            $color_index = $active_row_counter % 7; // Get index for color (0-6)
                            $row_class = 'row-color-' . $color_index;
                            $active_row_counter++; // Increment only for active students
                        }

                        echo "<tr class='" . $row_class . "'>";
                        echo "<td>" . htmlspecialchars($row['first_name'] . " " . $row['surname']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['phone_number']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";

                        if ($row['enrolled_course_name']) {
                            echo "<td>" . htmlspecialchars($row['enrolled_course_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['enrollment_duration']) . "</td>";
                            echo "<td>N/A (Add Progress Tracking)</td>"; // Placeholder
                        } else {
                            echo "<td colspan='3' class='no-enrollments'>No active enrollments</td>";
                        }

                        // Actions cell
                        echo "<td style='text-align: right;'>";
                        echo "<div class='action-buttons'>";

                        if ($is_deleted) {
                            echo "<form action='restore_student.php' method='POST' onsubmit=\"return confirm('Are you sure you want to restore this student?');\">";
                            echo "<input type='hidden' name='student_id' value='" . htmlspecialchars($row['student_id']) . "'>";
                            echo "<button type='submit' class='restore-btn'>";
                            echo "<i class='fas fa-redo-alt'></i> Restore";
                            echo "</button>";
                            echo "</form>";
                        } else {
                            echo "<form action='archive_student.php' method='POST' onsubmit=\"return confirm('Are you sure you want to archive this student and all associated enrollments? This will mark them as inactive.');\">";
                            echo "<input type='hidden' name='student_id' value='" . htmlspecialchars($row['student_id']) . "'>";
                            echo "<button type='submit' class='delete-btn'>";
                            echo "<i class='fas fa-trash-alt'></i> Archive";
                            echo "</button>";
                            echo "</form>";
                        }
                        echo "</div>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No students found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

<?php include 'footer.php'; ?>

<?php
// THIS IS THE KEY CHANGE: Close the connection AFTER all includes and HTML rendering
$connection->close();
?>