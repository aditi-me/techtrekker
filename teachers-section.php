<?php
// Include db.php if you need database connections for teacher-specific data
// require_once 'db.php';

// Assuming $userType, $userId, $streams, $coursesByStream are available from the main script
// If they are not available (e.g., if this file is accessed directly), you might need to
// fetch them here based on session or other means.

$streams = [];
$coursesByStream = [];
$teacherStreamId = null;
$teacherCourseId = null;
$teacherHasMultipleCourses = false;
$teacherName = "Teacher Name"; // Default name, will be replaced by database value

// Dummy connection for demonstration. Replace with your actual database connection.
// For example:
// $connection = mysqli_connect("your_host", "your_user", "your_password", "techtrekker");
// if (!$connection) {
//     die("Connection failed: " . mysqli_connect_error());
// }

// You should get the teacher's email from the session after login.
// session_start(); // Make sure session is started to access $_SESSION variables.
$loggedInTeacherEmail = $_SESSION['teacher_email'] ?? 'teacher@example.com'; // Use this for testing, but in production, rely solely on $_SESSION

if (isset($userType) && $userType === 'teacher' && isset($connection)) { // Check if $connection from db.php is available
    // Fetch all streams for the dropdown
    $sql_streams = "SELECT stream_id, stream_name FROM streams ORDER BY stream_name";
    if ($res_streams = mysqli_query($connection, $sql_streams)) {
        while ($row_streams = mysqli_fetch_assoc($res_streams)) {
            $streams[] = $row_streams;
        }
    }

    // Fetch all courses grouped by stream for the JavaScript
    $sql_courses = "SELECT course_id, course_name, stream_id FROM courses ORDER BY course_name";
    if ($res_courses = mysqli_query($connection, $sql_courses)) {
        while ($row_courses = mysqli_fetch_assoc($res_courses)) {
            $sid = $row_courses['stream_id'];
            $coursesByStream[$sid][] = [
                'course_id' => $row_courses['course_id'],
                'course_name' => $row_courses['course_name']
            ];
        }
    }

    // Fetch teacher's name, stream, and courses based on email
    $sql_teacher_info = "SELECT full_name, stream_id, course_id FROM teachers WHERE email_address = ?";
    if ($stmt_teacher_info = mysqli_prepare($connection, $sql_teacher_info)) {
        mysqli_stmt_bind_param($stmt_teacher_info, "s", $loggedInTeacherEmail);
        mysqli_stmt_execute($stmt_teacher_info);
        $res_teacher_info = mysqli_stmt_get_result($stmt_teacher_info);

        $teacherCourses = [];
        while ($row_teacher_info = mysqli_fetch_assoc($res_teacher_info)) {
            $teacherName = htmlspecialchars($row_teacher_info['full_name']); // Fetch and set the teacher's full name
            $teacherStreamId = $row_teacher_info['stream_id']; // Assuming a teacher is primarily associated with one stream
            $teacherCourses[] = $row_teacher_info['course_id'];
        }

        if (count($teacherCourses) > 1) {
            $teacherHasMultipleCourses = true;
        } elseif (count($teacherCourses) === 1) {
            $teacherCourseId = $teacherCourses[0];
        }
        mysqli_stmt_close($stmt_teacher_info);
    }
}
?>
<div class="mt-2">
    <h5 class="mb-3">Welcome, <?= $teacherName ?>!</h5> <h6 id="toggleDashboardFormTeacher" class="btn btn-outline-primary mt-3 w-100">Dashboard</h6>
    <div id="dashboardFormContainerTeacher" class="collapse-transition">
        <form action="teacher_dashboard.php" method="GET" id="applyStreamCourseForm">
            <select id="streamSelectTeacher" class="form-select mb-2" name="stream_id" required>
                <option value="">Choose Stream…</option>
                <?php foreach ($streams as $s) : ?>
                    <option value="<?= $s['stream_id'] ?>" <?= ($teacherStreamId == $s['stream_id']) ? 'selected' : '' ?>><?= htmlspecialchars($s['stream_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select id="courseSelectTeacher" class="form-select mb-2" name="course_id" <?= (!$teacherHasMultipleCourses && $teacherCourseId) ? 'disabled' : '' ?> required>
                <option value="">Choose stream first…</option>
                <?php
                if ($teacherStreamId && isset($coursesByStream[$teacherStreamId])) {
                    foreach ($coursesByStream[$teacherStreamId] as $c) {
                        $selected = ($teacherCourseId == $c['course_id']) ? 'selected' : '';
                        echo '<option value="' . $c['course_id'] . '" ' . $selected . '>' . htmlspecialchars($c['course_name']) . '</option>';
                    }
                }
                ?>
            </select>
            <button type="submit" class="btn btn-success mt-2 w-100">Apply</button>
            <button type="button" id="resetFiltersTeacher" class="btn btn-outline-secondary mt-2 w-100">Reset Filters</button>
        </form>
    </div>
</div>
<a href="teacher_students.php" class="btn btn-outline-primary mt-2 w-100">Students Section</a>
<a href="teacher_courses.php" class="btn btn-outline-primary mt-2 w-100">Courses Section</a>
<a href="teacher_holidays.php" class="btn btn-outline-primary mt-2 w-100">Holidays Section</a>
<a href="teacher_settings.php" class="btn btn-outline-primary mt-2 mb-3 w-100">Settings</a>
<a href="logout.php" class="btn btn-danger mt-5 w-100 border-top">Log Out</a>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const dashboardBtnTeacher = document.getElementById("toggleDashboardFormTeacher");
        const formContainerTeacher = document.getElementById("dashboardFormContainerTeacher");
        if (dashboardBtnTeacher && formContainerTeacher) {
            dashboardBtnTeacher.addEventListener("click", () => {
                formContainerTeacher.classList.toggle("show");
            });
        }

        const streamSelectTeacher = document.getElementById('streamSelectTeacher');
        const courseSelectTeacher = document.getElementById('courseSelectTeacher');
        const resetBtnTeacher = document.getElementById('resetFiltersTeacher');

        if (streamSelectTeacher && courseSelectTeacher) {
            const coursesByStreamTeacher = <?php echo json_encode($coursesByStream ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
            const teacherHasMultipleCourses = <?php echo json_encode($teacherHasMultipleCourses); ?>;

            const populateCoursesTeacher = (sid) => {
                courseSelectTeacher.innerHTML = '<option value="">' + (sid ? 'Choose Course…' : 'Choose stream first…') + '</option>';
                if (!sid || !coursesByStreamTeacher[sid]) {
                    courseSelectTeacher.disabled = true;
                    return;
                }
                coursesByStreamTeacher[sid].forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.course_id;
                    opt.textContent = c.course_name;
                    courseSelectTeacher.appendChild(opt);
                });
                courseSelectTeacher.disabled = false;
            };

            // Initial population based on pre-selected stream
            const initialStreamId = streamSelectTeacher.value;
            populateCoursesTeacher(initialStreamId);

            streamSelectTeacher.addEventListener('change', e => {
                populateCoursesTeacher(e.target.value);
            });

            resetBtnTeacher && resetBtnTeacher.addEventListener('click', () => {
                streamSelectTeacher.value = '';
                populateCoursesTeacher('');
            });

            // If the teacher only teaches one course, keep the course dropdown disabled.
            if (!teacherHasMultipleCourses && courseSelectTeacher.value !== '') {
                courseSelectTeacher.disabled = true;
            }
        }
    });
</script>