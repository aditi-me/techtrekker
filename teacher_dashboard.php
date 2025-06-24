<?php
session_start();

// --- Include your database configuration ---
// IMPORTANT: This file must exist and establish a MySQLi connection named $connection.
require_once 'db.php';

// Check if MySQLi connection is available
if (!isset($connection) || !$connection) {
    die("<h1>Database connection error. Please ensure db.php is correctly configured and loads \$connection.</h1>");
}

// Add logic to ensure only teachers can access this page
if (!isset($_SESSION['teacher_logged_in']) || !$_SESSION['teacher_logged_in']) {
    header("Location: login.php"); // Redirect to your teacher login page
    exit();
}

$teacher_email = $_SESSION['teacher_email'] ?? '';
$teacher_id = null;
$teacher_stream_id = null;
$teacher_courses = []; // Array to store all course_ids taught by this teacher

// Fetch teacher details and all courses they teach based on email
if (!empty($teacher_email)) {
    $query = "SELECT teacher_id, stream_id, course_id FROM teachers WHERE email_address = ?";
    $stmt = mysqli_prepare($connection, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $teacher_email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $teacher_id = $row['teacher_id'];
            $teacher_stream_id = $row['stream_id']; // Assuming one primary stream per teacher for sidebar pre-selection
            $teacher_courses[] = ['course_id' => $row['course_id']];
        }
        mysqli_stmt_close($stmt);
    } else {
        error_log("MySQLi prepare error for teacher details: " . mysqli_error($connection));
    }
}

// If no teacher details found (e.g., email not in DB or no assigned courses), log out
if (is_null($teacher_id)) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Determine the dashboard's current stream and course context
// The stream is pre-selected based on the teacher's assigned stream_id
$dashboard_stream_id = $teacher_stream_id;
$dashboard_course_id = null;

// If a course_id is passed in the URL (e.g., from user selection), use it
if (isset($_GET['course_id'])) {
    $requested_course_id = (int)$_GET['course_id'];
    // Validate that the requested course_id is actually taught by this teacher
    $is_course_taught_by_teacher = false;
    foreach ($teacher_courses as $course) {
        if ($course['course_id'] === $requested_course_id) {
            $is_course_taught_by_teacher = true;
            $dashboard_course_id = $requested_course_id;
            break;
        }
    }
    // If not taught by this teacher, $dashboard_course_id remains null or its previous value
}

// If no course is selected yet (or invalid), and the teacher teaches only one course, pre-select it
if (is_null($dashboard_course_id) && count($teacher_courses) === 1) {
    $dashboard_course_id = $teacher_courses[0]['course_id'];
}


// Fetch stream name for display in the sidebar
$stream_name_for_dashboard = "N/A";
if ($dashboard_stream_id) {
    $query_stream_name = "SELECT stream_name FROM streams WHERE stream_id = ?";
    $stmt_stream_name = mysqli_prepare($connection, $query_stream_name);
    if ($stmt_stream_name) {
        mysqli_stmt_bind_param($stmt_stream_name, 'i', $dashboard_stream_id);
        mysqli_stmt_execute($stmt_stream_name);
        $result_stream_name = mysqli_stmt_get_result($stmt_stream_name);
        $stream_row = mysqli_fetch_assoc($result_stream_name);
        if ($stream_row) {
            $stream_name_for_dashboard = $stream_row['stream_name'];
        }
        mysqli_stmt_close($stmt_stream_name);
    }
}

// Fetch course name for display if a specific course is selected
$course_name_for_dashboard = "N/A";
if ($dashboard_course_id) {
    $query_course_name = "SELECT course_name FROM courses WHERE course_id = ?";
    $stmt_course_name = mysqli_prepare($connection, "SELECT course_name FROM courses WHERE course_id = ?");
    if ($stmt_course_name) {
        mysqli_stmt_bind_param($stmt_course_name, 'i', $dashboard_course_id);
        mysqli_stmt_execute($stmt_course_name);
        $result_course_name = mysqli_stmt_get_result($stmt_course_name);
        $course_row = mysqli_fetch_assoc($result_course_name);
        if ($course_row) {
            $course_name_for_dashboard = $course_row['course_name'];
        }
        mysqli_stmt_close($stmt_course_name);
    }
}

// Data for middle section (students, progress) - adapt from admin_dashboard.php
$enrolled_students = [];
$completion_data = [];

if ($dashboard_course_id !== null) {
    // Fetch students for the specific course and stream from 'enrollments' table
    $students_query = "SELECT buyer_name, buyer_email, phone_number, enrolled_at
                       FROM enrollments
                       WHERE course_id = ? AND stream_id = ? ORDER BY enrolled_at DESC";
    $stmt_students = mysqli_prepare($connection, $students_query);
    if ($stmt_students) {
        mysqli_stmt_bind_param($stmt_students, 'ii', $dashboard_course_id, $dashboard_stream_id);
        mysqli_stmt_execute($stmt_students);
        $result_students = mysqli_stmt_get_result($stmt_students);
        while ($row = mysqli_fetch_assoc($result_students)) {
            $enrolled_students[] = $row;
        }
        mysqli_stmt_close($stmt_students);
    }

    // Fetch course completion data (for chart)
    $completion_query = "SELECT student_id, progress_percentage, last_updated
                         FROM course_completion
                         WHERE course_id = ? AND stream_id = ?";
    $completion_stmt = mysqli_prepare($connection, $completion_query);
    if ($completion_stmt) {
        mysqli_stmt_bind_param($completion_stmt, 'ii', $dashboard_course_id, $dashboard_stream_id);
        mysqli_stmt_execute($completion_stmt);
        $result_completion = mysqli_stmt_get_result($completion_stmt);
        while ($row = mysqli_fetch_assoc($result_completion)) {
            $completion_data[] = $row;
        }
        mysqli_stmt_close($completion_stmt);
    }
}

// --- Include the header file ---
include 'header.php';
?>

<div class="container-fluid">
    <h1 class="text-center mt-4 mb-4">Teacher Dashboard</h1>
    <div class="main-content-wrapper">
        <div class="dashboard-left-sidebar">
            <div class="dashboard-card">
                <h5>My Stream</h5>
                <p class="mb-0 fw-bold"><?php echo htmlspecialchars($stream_name_for_dashboard); ?></p>
            </div>

            <div class="dashboard-card mt-3">
                <?php if (count($teacher_courses) === 1) : ?>
                    <h5>My Course</h5>
                    <p class="mb-0 fw-bold"><?php echo htmlspecialchars($course_name_for_dashboard); ?></p>
                <?php else : ?>
                    <h5>Select Course</h5>
                    <select id="teacherCourseSelect" class="form-select mb-2">
                        <?php
                        // Fetch names for all courses the teacher teaches
                        $all_teacher_course_details = [];
                        if (!empty($teacher_courses)) {
                            $course_ids_to_fetch = implode(',', array_column($teacher_courses, 'course_id'));
                            // Ensure $course_ids_to_fetch is not empty to prevent SQL errors
                            if (!empty($course_ids_to_fetch)) {
                                $query_all_teacher_courses = "SELECT course_id, course_name FROM courses WHERE course_id IN ($course_ids_to_fetch)";
                                $result_all_teacher_courses = mysqli_query($connection, $query_all_teacher_courses);
                                if ($result_all_teacher_courses) {
                                    while ($row = mysqli_fetch_assoc($result_all_teacher_courses)) {
                                        $all_teacher_course_details[$row['course_id']] = $row['course_name'];
                                    }
                                }
                            }
                        }

                        foreach ($teacher_courses as $course) :
                            $selected = ($course['course_id'] == $dashboard_course_id) ? 'selected' : '';
                            $display_course_name = $all_teacher_course_details[$course['course_id']] ?? "Course " . $course['course_id'];
                        ?>
                            <option value="<?php echo $course['course_id']; ?>" <?php echo $selected; ?>>
                                <?php echo htmlspecialchars($display_course_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-primary btn-sm w-100" onclick="applyTeacherCourseFilter()">View Course Data</button>
                    <script>
                        function applyTeacherCourseFilter() {
                            const selectedCourseId = document.getElementById('teacherCourseSelect').value;
                            // Redirect, passing the teacher's stream_id and the newly selected course_id
                            // Note: We always pass the teacher's fixed stream_id for consistency
                            window.location.href = `teacher_dashboard.php?stream_id=<?php echo $dashboard_stream_id; ?>&course_id=${selectedCourseId}`;
                        }
                    </script>
                <?php endif; ?>
            </div>

            <div class="dashboard-card calendar-card">
                <h5>Calendar</h5>
                <p>Calendar content goes here.</p>
            </div>
            <div class="dashboard-card todo-list-card">
                <h5>To Do List</h5>
                <p>To-Do list items go here.</p>
            </div>
        </div>

        <div class="dashboard-middle-section">
            <div class="dashboard-card">
                <h5>Currently Viewing:
                    <?php echo htmlspecialchars($course_name_for_dashboard); ?> (<?php echo htmlspecialchars($stream_name_for_dashboard); ?>)
                </h5>
            </div>

            <div class="dashboard-card">
                <h5>Enrolled Students</h5>
                <?php if (!empty($enrolled_students)) : ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Enrolled Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($enrolled_students as $student) : ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['buyer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['buyer_email']); ?></td>
                                        <td><?php echo htmlspecialchars($student['phone_number']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($student['enrolled_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else : ?>
                    <p class="text-muted">No students enrolled in this course yet.</p>
                <?php endif; ?>
            </div>

            <div class="dashboard-card">
                <h5>Student Progress Chart</h5>
                <?php if (!empty($completion_data)) : ?>
                    <canvas id="progressChart"></canvas>
                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <script>
                        const ctx = document.getElementById('progressChart').getContext('2d');
                        const completionData = <?php echo json_encode($completion_data); ?>;
                        const studentLabels = completionData.map(d => 'Student ' + d.student_id); // You might want to fetch actual student names
                        const progressPercentages = completionData.map(d => d.progress_percentage);

                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: studentLabels,
                                datasets: [{
                                    label: 'Progress Percentage',
                                    data: progressPercentages,
                                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                                    borderColor: 'rgba(54, 162, 235, 1)',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    title: {
                                        display: true,
                                        text: 'Student Course Progress'
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        max: 100,
                                        title: {
                                            display: true,
                                            text: 'Percentage (%)'
                                        }
                                    },
                                    x: {
                                        title: {
                                            display: true,
                                            text: 'Students'
                                        }
                                    }
                                }
                            }
                        });
                    </script>
                <?php else : ?>
                    <p class="text-muted">No completion data available for this course yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="dashboard-right-section">
            <div class="dashboard-card">
                <h5>Notices</h5>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">New assignment due date for IT-501: June 30, 2025.</li>
                    <li class="list-group-item">Faculty meeting on July 5, 2025, at 10 AM.</li>
                </ul>
            </div>

            <div class="dashboard-card mt-3">
                <h5>Quick Links</h5>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><a href="#">Upload New Course Material</a></li>
                    <li class="list-group-item"><a href="#">View Student Attendance</a></li>
                    <li class="list-group-item"><a href="#">Contact Support</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
// --- Include the footer file ---
include 'footer.php';
?>