<?php
session_start();

// --- Include your database configuration ---
// IMPORTANT: This file must exist and establish a MySQLi connection named $connection.
require_once 'db.php';

// Check if MySQLi connection is available
if (!isset($connection) || !$connection) {
    die("<h1>Database connection error. Please ensure db.php is correctly configured and loads \$connection.</h1>");
}

// Add logic to ensure only admins can access this page
// if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
//     header("Location: login.php"); // Redirect to login page if not logged in as admin
//     exit();
// }

// You might fetch admin-specific data here if needed for other dashboard sections later
$admin_id = $_SESSION['admin_id'] ?? 'admin_default_id'; // Using a default admin ID if not set in session

// --- New Logic for Dynamic Sections ---
$display_disclaimer = false;
$new_purchase_details = null; // To store details of the latest purchase for notifications

$dashboard_course_id = $_GET['course_id'] ?? null; // Get course_id from URL parameter
$dashboard_stream_id = $_GET['stream_id'] ?? null; // Get stream_id from URL parameter (optional, for more specific filtering)

$enrolled_students = [];
$course_name_for_dashboard = "Selected Course"; // Default name if no specific course is opened
$teacher_name_for_dashboard = "N/A"; // Placeholder for teacher name for the course

// Data for Notice Dropdowns
$all_streams = [];
$all_courses = [];
$teachers_notices = [];
$admin_notices = []; // To store notices posted by the admin


// 1. New Purchase Notification Logic
// Query 'enrollments' table for the latest purchase (e.g., in the last 2 hours)
$recent_time_threshold = date('Y-m-d H:i:s', strtotime('-2 hours')); // Check for purchases in the last 2 hours
$query = "SELECT course_name, buyer_name, enrolled_at FROM enrollments WHERE enrolled_at > ? ORDER BY enrolled_at DESC LIMIT 1";
$stmt = mysqli_prepare($connection, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, 's', $recent_time_threshold);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $latest_purchase = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($latest_purchase) {
        $display_disclaimer = true;
        $new_purchase_details = $latest_purchase;
    }
} else {
    error_log("MySQLi prepare error for new purchases: " . mysqli_error($connection));
    // Optionally display a user-friendly message, but don't halt the page for minor errors.
}


// 2. Progress Chart & 3. Students List: Filter by course_id and stream_id
if ($dashboard_course_id !== null) {
    // Fetch course name and instructor from the 'courses' and 'teachers' tables
    $course_info = null;
    $query_params = [];
    $types = '';

    $course_query = "SELECT c.course_name, t.full_name AS instructor_name
                     FROM courses c
                     LEFT JOIN teachers t ON c.course_id = t.course_id
                     WHERE c.course_id = ?";
    $query_params[] = $dashboard_course_id;
    $types .= 'i';

    if ($dashboard_stream_id !== null) {
        $course_query .= " AND c.stream_id = ?";
        $query_params[] = $dashboard_stream_id;
        $types .= 'i';
    }

    $stmt_course = mysqli_prepare($connection, $course_query);
    if ($stmt_course) {
        // Use call_user_func_array for mysqli_stmt_bind_param when params are dynamic
        if (!empty($query_params)) {
             mysqli_stmt_bind_param($stmt_course, $types, ...$query_params);
        }
        mysqli_stmt_execute($stmt_course);
        $result_course = mysqli_stmt_get_result($stmt_course);
        $course_info = mysqli_fetch_assoc($result_course);
        mysqli_stmt_close($stmt_course);

        if ($course_info) {
            $course_name_for_dashboard = $course_info['course_name'];
            $teacher_name_for_dashboard = $course_info['instructor_name'] ?? "N/A"; // Handle case where teacher might not be linked
        }
    } else {
        error_log("MySQLi prepare error for course info: " . mysqli_error($connection));
        $course_name_for_dashboard = "Error Fetching Course Data";
        $teacher_name_for_dashboard = "Error";
    }

    // Fetch students for the specific course (and stream if provided) from 'enrollments' table
    $enrolled_students = [];
    $query_params_students = [];
    $types_students = '';

    $students_query = "SELECT buyer_name, buyer_email, phone_number, enrolled_at
                       FROM enrollments
                       WHERE course_id = ?";
    $query_params_students[] = $dashboard_course_id;
    $types_students .= 'i';

    if ($dashboard_stream_id !== null) {
        $students_query .= " AND stream_id = ?";
        $query_params_students[] = $dashboard_stream_id;
        $types_students .= 'i';
    }
    $students_query .= " ORDER BY enrolled_at DESC";

    $stmt_students = mysqli_prepare($connection, $students_query);
    if ($stmt_students) {
        if (!empty($query_params_students)) {
            mysqli_stmt_bind_param($stmt_students, $types_students, ...$query_params_students);
        }
        mysqli_stmt_execute($stmt_students);
        $result_students = mysqli_stmt_get_result($stmt_students);
        while ($row = mysqli_fetch_assoc($result_students)) {
            $enrolled_students[] = $row;
        }
        mysqli_stmt_close($stmt_students);
    } else {
        error_log("MySQLi prepare error for enrolled students: " . mysqli_error($connection));
        $enrolled_students = [];
    }

    // Fetch course completion data (for chart)
    $completion_data = [];
    $query_params_completion = [];
    $types_completion = '';

    $completion_query = "SELECT student_id, progress_percentage, last_updated
                         FROM course_completion
                         WHERE course_id = ?";
    $query_params_completion[] = $dashboard_course_id;
    $types_completion .= 'i';

    if ($dashboard_stream_id !== null) {
        $completion_query .= " AND stream_id = ?";
        $query_params_completion[] = $dashboard_stream_id;
        $types_completion .= 'i';
    }

    $completion_stmt = mysqli_prepare($connection, $completion_query);
    if ($completion_stmt) {
        if (!empty($query_params_completion)) {
            mysqli_stmt_bind_param($completion_stmt, $types_completion, ...$query_params_completion);
        }
        mysqli_stmt_execute($completion_stmt);
        $result_completion = mysqli_stmt_get_result($completion_stmt);
        while ($row = mysqli_fetch_assoc($result_completion)) {
            $completion_data[] = $row;
        }
        mysqli_stmt_close($completion_stmt);
    } else {
        error_log("MySQLi prepare error for course completion data: " . mysqli_error($connection));
        $completion_data = [];
    }
}

// Fetch all streams for dropdown
$streams_result = mysqli_query($connection, "SELECT stream_id, stream_name FROM streams ORDER BY stream_name");
if ($streams_result) {
    while ($row = mysqli_fetch_assoc($streams_result)) {
        $all_streams[] = $row;
    }
} else {
    error_log("MySQLi query error for streams: " . mysqli_error($connection));
}

// Fetch all courses for dropdown
$courses_result = mysqli_query($connection, "SELECT course_id, course_name, stream_id FROM courses ORDER BY course_name");
if ($courses_result) {
    while ($row = mysqli_fetch_assoc($courses_result)) {
        $all_courses[] = $row;
    }
} else {
    error_log("MySQLi query error for courses: " . mysqli_error($connection));
}

// Fetch notices from teachers
$notices_query = "SELECT n.notice_content, n.posted_at, t.full_name AS teacher_name, s.stream_name, co.course_name
                  FROM notices n
                  LEFT JOIN teachers t ON n.teacher_id = t.teacher_id
                  LEFT JOIN streams s ON n.stream_id = s.stream_id
                  LEFT JOIN courses co ON n.course_id = co.course_id
                  WHERE n.teacher_id IS NOT NULL  -- Only notices posted by teachers
                  ORDER BY n.posted_at DESC LIMIT 5"; // Limit to latest 5 notices
$notices_result = mysqli_query($connection, $notices_query);
if ($notices_result) {
    while ($row = mysqli_fetch_assoc($notices_result)) {
        $teachers_notices[] = $row;
    }
} else {
    error_log("MySQLi query error for teacher notices: " . mysqli_error($connection));
}

// Fetch notices from admin
$admin_notices_query = "SELECT n.notice_content, n.posted_at, n.target_audience, s.stream_name, co.course_name
                        FROM notices n
                        LEFT JOIN streams s ON n.stream_id = s.stream_id
                        LEFT JOIN courses co ON n.course_id = co.course_id
                        WHERE n.admin_id IS NOT NULL  -- Only notices posted by admin
                        ORDER BY n.posted_at DESC LIMIT 5"; // Limit to latest 5 notices
$admin_notices_result = mysqli_query($connection, $admin_notices_query);
if ($admin_notices_result) {
    while ($row = mysqli_fetch_assoc($admin_notices_result)) {
        $admin_notices[] = $row;
    }
} else {
    error_log("MySQLi query error for admin notices: " . mysqli_error($connection));
}


// --- To-Do List PHP Logic (remains unchanged) ---
// Initialize tasks array in session if it doesn't exist
if (!isset($_SESSION['todo_tasks'])) {
    $_SESSION['todo_tasks'] = [];
}

// --- IMPORTANT: Handle ALL AJAX requests at the TOP of the file ---
// This prevents any HTML (from includes or the main document) from being outputted.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json'); // Set header once for all AJAX responses

    switch ($_POST['action']) {
        case 'add_task':
            $task_description = trim($_POST['task_description'] ?? '');
            if (!empty($task_description)) {
                $newTask = ['id' => uniqid('task_'), 'description' => $task_description, 'done' => false];
                array_unshift($_SESSION['todo_tasks'], $newTask);
                echo json_encode(['status' => 'success', 'task' => $newTask]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Task description cannot be empty.']);
            }
            break;

        case 'toggle_task_status':
            $task_id = $_POST['task_id'] ?? '';
            foreach ($_SESSION['todo_tasks'] as $key => $task) {
                if ($task['id'] === $task_id) {
                    $_SESSION['todo_tasks'][$key]['done'] = !$_SESSION['todo_tasks'][$key]['done'];
                    break;
                }
            }
            echo json_encode(['status' => 'success']);
            break;

        case 'delete_task':
            $task_id = $_POST['task_id'] ?? '';
            if (!empty($task_id)) {
                $_SESSION['todo_tasks'] = array_filter($_SESSION['todo_tasks'], function($task) use ($task_id) {
                    return $task['id'] !== $task_id;
                });
                $_SESSION['todo_tasks'] = array_values($_SESSION['todo_tasks']);
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Task ID cannot be empty.']);
            }
            break;

        case 'reorder_tasks':
            $ordered_task_ids = json_decode($_POST['ordered_tasks'] ?? '[]', true);
            $new_tasks_order = [];
            $undone_reordered = [];
            $done_reordered = [];

            $task_map = [];
            foreach ($_SESSION['todo_tasks'] as $task) {
                $task_map[$task['id']] = $task;
            }

            foreach ($ordered_task_ids as $id) {
                if (isset($task_map[$id])) {
                    if ($task_map[$id]['done']) {
                        $done_reordered[] = $task_map[$id];
                    } else {
                        $undone_reordered[] = $task_map[$id];
                    }
                }
            }
            $_SESSION['todo_tasks'] = array_merge($undone_reordered, $done_reordered);
            echo json_encode(['status' => 'success']);
            break;

        // New action to handle submitting a notice
        case 'submit_notice':
            $target_audience = $_POST['target_audience'] ?? '';
            $notice_content = trim($_POST['notice_content'] ?? '');

            // Determine stream_id and course_id based on current dashboard context or form input
            $notice_stream_id = null;
            $notice_course_id = null;

            // If coming from a course-specific dashboard, prioritize URL parameters
            if (isset($_GET['course_id']) && isset($_GET['stream_id'])) {
                $notice_course_id = (int)$_GET['course_id'];
                $notice_stream_id = (int)$_GET['stream_id'];
            } else {
                // Otherwise, use form inputs
                $notice_stream_id = empty($_POST['stream_id']) ? null : (int)$_POST['stream_id'];
                $notice_course_id = empty($_POST['course_id']) ? null : (int)$_POST['course_id'];
            }

            if (empty($notice_content) || empty($target_audience)) {
                echo json_encode(['status' => 'error', 'message' => 'Notice content and target audience are required.']);
                exit();
            }

            // Sanitize inputs
            $safe_target_audience = mysqli_real_escape_string($connection, $target_audience);
            $safe_notice_content = mysqli_real_escape_string($connection, $notice_content);
            $safe_admin_id = mysqli_real_escape_string($connection, $admin_id); // Ensure admin_id is sanitized

            $insert_query = "INSERT INTO notices (admin_id, target_audience, stream_id, course_id, notice_content) VALUES (?, ?, ?, ?, ?)";
            $stmt_insert_notice = mysqli_prepare($connection, $insert_query);

            if ($stmt_insert_notice) {
                // Bind parameters: 's' for string, 'i' for integer. Use null for optional integers
                // We need to pass references for bind_param, even for null.
                $temp_stream_id = $notice_stream_id;
                $temp_course_id = $notice_course_id;

                // Adjust bind types if any integer is null (though 'i' handles null fine for MySQLi)
                mysqli_stmt_bind_param($stmt_insert_notice, 'ssiis', $safe_admin_id, $safe_target_audience, $temp_stream_id, $temp_course_id, $safe_notice_content);

                if (mysqli_stmt_execute($stmt_insert_notice)) {
                    // Fetch the just-inserted notice details for immediate display
                    // This is a simplified way; in production, you might fetch by UUID or LAST_INSERT_ID()
                    // or return the data directly from PHP if you know it was successful.
                    $newly_posted_notice = [
                        'notice_content' => $notice_content,
                        'posted_at' => date('Y-m-d H:i:s'),
                        'target_audience' => $target_audience,
                        'stream_name' => '', // Will be filled by JS if needed
                        'course_name' => '' // Will be filled by JS if needed
                    ];

                    // If stream_id or course_id were set, try to get their names for display
                    if ($notice_stream_id) {
                        $stream_name_res = mysqli_query($connection, "SELECT stream_name FROM streams WHERE stream_id = {$notice_stream_id}");
                        if ($stream_name_res) {
                            $row = mysqli_fetch_assoc($stream_name_res);
                            $newly_posted_notice['stream_name'] = $row['stream_name'] ?? '';
                        }
                    }
                    if ($notice_course_id) {
                        $course_name_res = mysqli_query($connection, "SELECT course_name FROM courses WHERE course_id = {$notice_course_id}");
                        if ($course_name_res) {
                            $row = mysqli_fetch_assoc($course_name_res);
                            $newly_posted_notice['course_name'] = $row['course_name'] ?? '';
                        }
                    }


                    echo json_encode(['status' => 'success', 'message' => 'Notice submitted successfully.', 'notice' => $newly_posted_notice]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to submit notice: ' . mysqli_error($connection)]);
                }
                mysqli_stmt_close($stmt_insert_notice);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to prepare notice submission statement: ' . mysqli_error($connection)]);
            }
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
            break;
    }
    exit(); // EXIT HERE AFTER HANDLING ANY AJAX REQUEST!
}

// --- If it's NOT an AJAX POST request, then proceed to render the full HTML page ---
// Include header AFTER all AJAX handling logic
include 'header.php';

// Separate done and undone tasks for display
$display_tasks = $_SESSION['todo_tasks'];
?>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css">
    <style>
            body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f4f7f6;
        color: #333;
    }

    .main-content-wrapper {
        display: flex;
        min-height: calc(100vh - 80px);
        padding: 20px;
        gap: 20px;
    }

    .dashboard-left-sidebar,
    .dashboard-middle-section,
    .dashboard-right-section {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        padding: 20px;
        /* Removed fixed flex-shrink: 0 from here so individual items can control it */
        overflow-y: auto;
    }

    .dashboard-left-sidebar {
        flex-basis: 350px; /* Increased from 300px */
        max-width: 350px; /* Increased from 300px */
        flex-shrink: 1; /* Allows this item to shrink if container is too small */
        /* Removed: width: 300px; */
    }

    .dashboard-middle-section {
        flex-grow: 1; /* Takes remaining space */
        flex-shrink: 1; /* Allows this item to shrink */
        min-width: 0; /* Allows content to shrink below its intrinsic width */
        max-width: 830px; /* Adjusted max-width further */
    }

    .dashboard-right-section {
        flex-basis: 300px; /* Slightly reduced from 350px to give middle more space */
        max-width: 300px; /* Slightly reduced from 350px */
        flex-shrink: 1; /* Allows this item to shrink if container is too small */
        /* Removed: width: 350px; */
    }

    h1,
    h5 {
        color: #1a237e;
        margin-bottom: 20px;
    }

    .dashboard-card {
        margin-bottom: 20px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 15px;
        background-color: #fdfdff;
    }

    /* FullCalendar Customizations for the MODAL ONLY */
    #full-calendar-modal .modal-body {
        min-height: 400px;
    }

    /* FullCalendar Specific Overrides */
    /* Day of the week headers */
    .fc .fc-col-header-cell-cushion {
        /* Default styling for all day names in the header */
        font-weight: bold;
        text-decoration: none !important;
        /* Remove underline */
        padding: 8px 0;
        /* Add some padding */
    }

    /* Specific colors for each day name in the header */
    .fc-col-header-cell.fc-day-sun .fc-col-header-cell-cushion {
        color: #E57373;
        /* Sunday Red */
    }

    .fc-col-header-cell.fc-day-mon .fc-col-header-cell-cushion {
        color: #FFD700;
        /* Monday Yellow */
    }

    .fc-col-header-cell.fc-day-tue .fc-col-header-cell-cushion {
        color: #64B5F6;
        /* Tuesday Blue */
    }

    .fc-col-header-cell.fc-day-wed .fc-col-header-cell-cushion {
        color: #81C784;
        /* Wednesday Green */
    }

    .fc-col-header-cell.fc-day-thu .fc-col-header-cell-cushion {
        color: #FFB74D;
        /* Thursday Orange */
    }

    .fc-col-header-cell.fc-day-fri .fc-col-header-cell-cushion {
        color: #BA68C8;
        /* Friday Purple */
    }

    .fc-col-header-cell.fc-day-sat .fc-col-header-cell-cushion {
        color: #4DD0E1;
        /* Saturday Cyan/Teal */
    }


    /* Date cells - reset any previous background/text color from general styling */
    .fc .fc-daygrid-day-number {
        color: #333;
        /* Default text color for all dates */
        font-weight: normal;
        text-decoration: none !important;
        /* Remove underline */
        padding: 5px;
        /* Add some padding */
        background-color: transparent !important;
        /* Ensure no background color for dates by default */
        border: none !important;
        /* Ensure no border for dates by default */
        border-radius: 0 !important;
        /* Ensure no border-radius for dates by default */
    }

    /* Current date styling - apply only to the date number */
    .fc .fc-day-today .fc-daygrid-day-number {
        background-color: #e3f2fd;
        /* Light blue background for today's number */
        color: #1a237e;
        /* Darker blue for today's date number */
        font-weight: bold;
        padding: 5px 8px;
        /* Slightly more padding for the highlighted number */
        border-radius: 4px;
        /* Rounded corners for the highlighted number */
        display: inline-block;
        /* Make it an inline block to apply padding/background/border-radius effectively */
    }

    /* Remove default FullCalendar blue borders/highlights and underlines */
    .fc .fc-button-primary {
        background-color: #6c757d;
        /* Use a secondary gray for buttons */
        border-color: #6c757d;
        color: #fff;
        text-decoration: none !important;
    }

    .fc .fc-button-primary:hover {
        background-color: #5a6268;
        border-color: #545b62;
    }

    .fc .fc-button-primary:focus {
        box-shadow: 0 0 0 0.25rem rgba(108, 117, 125, 0.5);
        /* Custom focus for gray buttons */
    }

    .fc .fc-button-primary:active {
        background-color: #545b62;
        border-color: #4e555b;
    }

    .fc-event {
        /* Remove underline from events if any are added later */
        text-decoration: none !important;
    }

    /* Ensure no blue underline on links within FullCalendar if applicable */
    .fc a {
        text-decoration: none !important;
    }

    /* To-do list styling */
    .todo-list-card h5 {
        cursor: pointer;
        color: #4a148c;
    }

    /* --- Custom Weekly Calendar Styling (remains unchanged as per previous request) --- */
    #custom-weekly-calendar {
        cursor: pointer;
        /* Indicate it's clickable */
        margin-bottom: 20px;
        padding: 0;
        /* Remove padding as it's now inside #weeklyCalendarDisplay for consistent border */
        border-radius: 8px;
        /* Keep outer border radius */
        overflow: hidden;
        /* Ensures border-radius clips content */
    }

    #custom-weekly-calendar .calendar-nav {
        margin-bottom: 10px;
        /* Smaller margin */
        padding: 10px 15px 0 15px;
        /* Add some padding to navigation, consistent with card padding */
    }

    #weeklyCalendarDisplay {
        border: 1px solid #e0e0e0;
        /* Main border covering both rows */
        border-radius: 8px;
        /* Apply border radius here as well */
        overflow: hidden;
        /* Important for border-radius to work with inner elements */
        margin: 0 15px 15px 15px;
        /* Margin to align with navigation buttons */
    }

    .calendar-header-row,
    .calendar-date-row {
        display: flex;
        /* Use flexbox for horizontal arrangement */
        justify-content: space-around;
        /* Distribute items evenly */
        align-items: stretch;
        /* Stretch items to fill height */
        width: 100%;
        /* Take full width */
    }

    .calendar-header-row div,
    .calendar-date-row div {
        flex: 1;
        /* Each item takes equal space */
        text-align: center;
        /* Center the text */
        padding: 5px 0;
        /* Add vertical padding */
        /* Add right border to create vertical separators */
        border-right: 1px solid #eee;
        /* Default border color */
    }

    /* Remove right border from the last item in each row */
    .calendar-header-row div:last-child,
    .calendar-date-row div:last-child {
        border-right: none;
    }

    .calendar-header-row {
        font-weight: bold;
        color: #333;
        font-size: 0.75em;
        /* Reduced font size for day names */
        border-bottom: 1px solid #e0e0e0;
        /* Separator line between day and date rows */
        background-color: #f8f8f8;
        /* Slightly different background for header row */
    }

    .calendar-date-row {
        font-size: 0.8em;
        /* Slightly reduced font size for dates */
        color: #666;
        background-color: #fff;
        /* White background for date row */
    }

    /* ... your existing styles ... */

    /* FullCalendar Customizations for the MODAL ONLY */
    #full-calendar-modal .modal-body {
        height: 400px;
        /* Keep your minimum height */
        /* Add these properties to make the modal body a flex container
            and allow it to define a flexible height for its children */
        display: flex;
        flex-direction: column;
        overflow: hidden;
        /* Hide any overflow from children that might escape, though the main fix is on #full-calendar */
    }

    #full-calendar {
        /*
        This is the crucial part for controlling the FullCalendar's height
        and preventing overflow within the modal body.
        */
        flex-grow: 1;
        /* Allows #full-calendar to grow and shrink within the flex container (.modal-body) */
        overflow-y: auto;
        /* Adds a vertical scrollbar to #full-calendar if its content overflows */
        height: 100%;
        /* Important: Makes #full-calendar take up 100% of the available flex space */
        height: 350px;
        /* Optional: A minimum height for the calendar itself */
        box-sizing: border-box;
        /* Ensures padding/border don't add to the total height */
    }

    #full-calendar-modal .fc .fc-toolbar-title {
        flex-grow: 1;
        /* Allow the title to take available space */
        text-align: center;
        /* Center the text */
        margin: 0;
        /* Remove default margins if any */
    }

    /* Adjust button group margin if needed */
    #full-calendar-modal .fc .fc-toolbar-chunk:nth-child(1) {
        display: flex;
        justify-content: center;
        /* Center the prev,next today buttons */
        flex-grow: 1;
        /* Allow to take space */
    }

    #full-calendar-modal .fc .fc-toolbar-chunk:nth-child(3) {
        flex-grow: 1;
        /* Allow the right chunk to take space for centering effect */
    }

    /* Responsive Adjustments */
    @media (max-width: 992px) {
        .main-content-wrapper {
            flex-direction: column;
            padding: 15px;
        }

        .dashboard-left-sidebar,
        .dashboard-middle-section,
        .dashboard-right-section {
            width: 100%; /* Ensures they take full width when stacked */
            max-width: none; /* Remove max-width constraint when stacked */
            flex-basis: auto; /* Remove flex-basis constraint when stacked */
            margin-bottom: 20px;
        }
    }

    /* Additional responsiveness for small calendar for very narrow screens */
    @media (max-width: 400px) {

        .calendar-header-row div,
        .calendar-date-row div {
            font-size: 0.65em;
            /* Even smaller font for very narrow screens */
            padding: 3px 0;
        }
    }

    .todo-list-card {
        /* Add some padding or styling for the card */
        padding: 20px;
    }

    .todo-heading {
        cursor: default;
        /* Remove pointer cursor as it's no longer toggling */
    }

    #todo-add-form-container {
        margin-top: 15px;
        /* Space between existing tasks and the add form */
    }

    .list-group-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 1.25rem;
        border: 1px solid rgba(0, 0, 0, .125);
        margin-bottom: -1px;
        /* To make borders collapse nicely */
    }

    .list-group-item:first-child {
        border-top-left-radius: 0.25rem;
        border-top-right-radius: 0.25rem;
    }

    .list-group-item:last-child {
        margin-bottom: 0;
        border-bottom-right-radius: 0.25rem;
        border-bottom-left-radius: 0.25rem;
    }

    .form-check {
        display: flex;
        align-items: center;
        flex-grow: 1;
        /* Allow the checkbox and label to take available space */
    }

    .form-check-input {
        margin-right: 0.75rem;
        /* Space between checkbox and text */
        cursor: pointer;
    }

    .task-text {
        flex-grow: 1;
        word-break: break-word;
        /* Ensure long task descriptions wrap */
    }

    .task-actions .reorder-handle {
        cursor: grab;
        margin-left: 1rem;
        /* Space between text and handle */
        color: #ccc;
        /* Lighter color for grip icon */
    }

    /* Style for completed tasks */
    .list-group-item.done .task-text {
        text-decoration: line-through;
        color: #6c757d !important;
        /* Bootstrap's text-muted color */
    }

    .list-group-item.done {
        background-color: #f8f9fa;
        /* Light grey background for done tasks */
        color: #6c757d;
        /* Overall muted color for done task item */
    }

    /* Hide the "No tasks" message if the list is not empty */
    /* Add this to your <style> block or CSS file */
.list-group-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.task-actions {
    display: flex;
    align-items: center;
}

.reorder-handle,
.delete-task {
    cursor: grab; /* Indicate draggable */
    margin-left: 10px; /* Space between checkbox and icons */
    color: #6c757d; /* Muted color for icons */
}

.delete-task {
    cursor: pointer;
    color: #dc3545; /* Red color for delete icon */
}

.reorder-handle:active {
    cursor: grabbing;
}

.list-group-item.done .task-text {
    text-decoration: line-through;
    color: #888;
}

.no-tasks-message {
    text-align: center;
    padding: 15px;
    color: #6c757d;
}

/* Styles for new purchase notification cards */
.new-purchase-notification-card {
    border-left: 5px solid #28a745; /* Green border for success */
    background-color: #e6ffed; /* Light green background */
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
.new-purchase-notification-card h6 {
    color: #28a745; /* Darker green heading */
    margin-bottom: 10px;
}
.new-purchase-notification-card p {
    margin-bottom: 5px;
    font-size: 0.9em;
}

/* Styles for Admin Notices */
.admin-notice-card {
    border-left: 5px solid #007bff; /* Blue border for admin notices */
    background-color: #e7f3ff; /* Light blue background */
    padding: 15px;
    margin-bottom: 10px; /* Slightly less margin for denser list */
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
.admin-notice-card h6 {
    color: #007bff; /* Darker blue heading */
    margin-bottom: 10px;
}
.admin-notice-card p {
    margin-bottom: 5px;
    font-size: 0.9em;
}

        </style>
<body>
    <div class="container-fluid">
        <h1 class="text-center mt-4 mb-4">Admin Dashboard</h1>

        <div class="main-content-wrapper">
            <div class="dashboard-left-sidebar">
                <div class="dashboard-card calendar-card">
                    <h5>Calendar</h5>
                    <div id="custom-weekly-calendar">
                        <div class="calendar-nav mb-3 d-flex justify-content-between align-items-center">
                            <button class="btn btn-sm btn-outline-secondary" id="prevWeekBtn"><i class="fas fa-chevron-left"></i></button>
                            <span id="currentWeekRange" class="fw-bold"></span>
                            <button class="btn btn-sm btn-outline-secondary" id="nextWeekBtn"><i class="fas fa-chevron-right"></i></button>
                        </div>
                        <div id="weeklyCalendarDisplay">
                            <div class="calendar-header-row" id="calendarDaysRow"></div>
                            <div class="calendar-date-row" id="calendarDatesRow"></div>
                        </div>
                    </div>
                    <div id='full-calendar-modal' class="modal fade" tabindex="-1" aria-labelledby="fullCalendarModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="fullCalendarModalLabel">Full Calendar View</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div id='full-calendar'></div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dashboard-card todo-list-card">
                    <h5 class="todo-heading">To Do List <i class="fas fa-chevron-down ms-2 toggle-arrow" style="display:none;"></i></h5>
                    <div id="todo-content">
                        <div id="todo-add-form-container" style="display: block;">
                            <form id="add-task-form">
                                <input type="hidden" name="action" value="add_task">
                                <input type="text" name="task_description" id="task-description-input" class="form-control mb-2" placeholder="Add a new task..." required>
                                <button type="submit" class="btn btn-primary btn-sm w-100">Add Task</button>
                            </form>
                        </div>

                        <ul id="todo-items-list" class="list-group mt-3">
                            <?php if (empty($display_tasks)) : ?>
                                <p class="text-muted small no-tasks-message" style="display: block;">No tasks for today! Add one above.</p>
                            <?php else : ?>
                                <?php foreach ($display_tasks as $task) : ?>
                                    <li class="list-group-item <?= $task['done'] ? 'done' : '' ?>" data-id="<?= htmlspecialchars($task['id']) ?>">
                                        <div class="form-check">
                                            <input class="form-check-input task-checkbox" type="checkbox" <?= $task['done'] ? 'checked' : '' ?> data-id="<?= htmlspecialchars($task['id']) ?>">
                                            <label class="form-check-label task-text"><?= htmlspecialchars($task['description']) ?></label>
                                        </div>
                                        <div class="task-actions">
                                            <i class="fas fa-grip-vertical reorder-handle me-2"></i>
                                            <i class="fas fa-trash-alt delete-task" data-id="<?= htmlspecialchars($task['id']) ?>"></i>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                                <p class="text-muted small no-tasks-message" style="display: none;">No tasks for today! Add one above.</p>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="dashboard-middle-section">
                <h5>Overview & Analytics</h5>

                <?php
                // 2. Progress Chart & 3. Students List: Show only for the course for which the dashboard opened.
                if ($dashboard_course_id !== null) :
                ?>
                <div class="dashboard-card mt-4 course-progress-chart-card">
                    <h6>
                        Course Progress Charts for "<?= htmlspecialchars($course_name_for_dashboard) ?>"
                        
                    </h6>
                    <p class="text-muted small">
                        Taught by: <b><?= htmlspecialchars($teacher_name_for_dashboard) ?></b>
                    </p>
                    <p class="text-muted small">
                        Interactive charts showing course completion rates for this specific course will be displayed here.
                        Data for this chart would come from the `course_completion` table,
                        populated by teachers, and then processed with JavaScript charting libraries (e.g., Chart.js).
                    </p>
                    <!-- Placeholder for actual chart rendering. -->
                    <div class="chart-placeholder" style="height: 200px; background-color: #f8f9fa; border: 1px dashed #ccc; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                        [Placeholder for Chart for Course ID: <?= htmlspecialchars($dashboard_course_id) ?>]
                        <?php if (!empty($completion_data)) : ?>
                            <br>
                            <small class="text-muted">Example completion data available (<?= count($completion_data) ?> records):</small>
                            <ul>
                                <?php foreach ($completion_data as $comp) : ?>
                                    <li>Student: <?= htmlspecialchars($comp['student_id'] ?? 'N/A') ?>, Progress: <?= htmlspecialchars($comp['progress_percentage']) ?>%</li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else : ?>
                            <br>
                            <small class="text-muted">No completion data found for this course yet.</small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="dashboard-card mt-4 student-list-card">
                    <h6>
                        Enrolled Students for "<?= htmlspecialchars($course_name_for_dashboard) ?>"
                    </h6>
                    <?php if (!empty($enrolled_students)) : ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone Number</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($enrolled_students as $student) : ?>
                                        <tr>
                                            <td><?= htmlspecialchars($student['buyer_name']) ?></td>
                                            <td><?= htmlspecialchars($student['buyer_email']) ?></td>
                                            <td><?= htmlspecialchars($student['phone_number'] ?? 'N/A') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <p class="text-muted small">
                            No students enrolled for this course (ID: <?= htmlspecialchars($dashboard_course_id) ?>)
                            <?php if ($dashboard_stream_id !== null) : ?>
                                in this stream (ID: <?= htmlspecialchars($dashboard_stream_id) ?>)
                            <?php endif; ?>
                            yet, or an error occurred fetching data.
                        </p>
                    <?php endif; ?>
                </div>
                <?php else : ?>
                <div class="dashboard-card mt-4">
                    <p class="text-info small">
                        To view course-specific progress charts and enrolled students, please open the dashboard for a particular course.
                    </p>
                    <p class="text-muted small">
                        You can do this by adding <code>?course_id=X</code> to the URL,
                        and optionally <code>&stream_id=Y</code>
                        (e.g., <code>admin_dashboard.php?course_id=1&stream_id=101</code>).
                    </p>
                </div>
                <?php endif; ?>
            </div>

            <div class="dashboard-right-section">
                <h5>Notice Board</h5>

                <?php
                // 1. New Purchase Notification on Right Sidebar
                if ($display_disclaimer && $new_purchase_details) :
                ?>
                <div class="dashboard-card new-purchase-notification-card">
                    <h6>New Purchase! <i class="fas fa-bell text-success ms-2"></i></h6>
                    <p>
                        A new student, <b><?= htmlspecialchars($new_purchase_details['buyer_name'] ?? 'N/A') ?></b>,
                        just enrolled in <b><?= htmlspecialchars($new_purchase_details['course_name'] ?? 'a course') ?></b>!
                    </p>
                    <small class="text-muted">
                        <?= htmlspecialchars(date('Y-m-d H:i', strtotime($new_purchase_details['enrolled_at'] ?? 'now'))) ?>
                    </small>
                </div>
                <?php endif; ?>

                <div class="dashboard-card">
                    <h6>Notices from Teachers</h6>
                    <?php if (!empty($teachers_notices)) : ?>
                        <div class="notices-list">
                            <?php foreach ($teachers_notices as $notice) : ?>
                                <div class="alert alert-info py-2 px-3 mb-2 small" role="alert">
                                    <strong><?= htmlspecialchars($notice['teacher_name'] ?? 'Teacher') ?>:</strong>
                                    <?= htmlspecialchars($notice['notice_content']) ?>
                                    <br>
                                    <small class="text-muted">
                                        Posted: <?= date('Y-m-d H:i', strtotime($notice['posted_at'])) ?>
                                        <?php if ($notice['stream_name']) echo " | Stream: " . htmlspecialchars($notice['stream_name']); ?>
                                        <?php if ($notice['course_name']) echo " | Course: " . htmlspecialchars($notice['course_name']); ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p class="text-muted small">No notices from teachers at the moment.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script>
        // Your existing JavaScript code for calendar and todo list
        document.addEventListener('DOMContentLoaded', function() {
            var customWeeklyCalendarEl = document.getElementById('custom-weekly-calendar');
            var calendarFullEl = document.getElementById('full-calendar');
            var fullCalendarModal = new bootstrap.Modal(document.getElementById('full-calendar-modal'));

            const calendarDaysRow = document.getElementById('calendarDaysRow');
            const calendarDatesRow = document.getElementById('calendarDatesRow');
            const currentWeekRangeSpan = document.getElementById('currentWeekRange');
            const prevWeekBtn = document.getElementById('prevWeekBtn');
            const nextWeekBtn = document.getElementById('nextWeekBtn');

            let currentWeekStart = new Date(); // Start with today
            currentWeekStart.setHours(0, 0, 0, 0); // Normalize to start of day

            // Get today's date normalized for comparison
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            // Define color palettes for each day of the week (these would typically be in your CSS now)
            // If these colors are critical, ensure they are defined in your external CSS file
            const dayColors = {
                'Sun': {
                    dayBgColor: '#FFF5F5',
                    dateBgColor: '#FFEDED',
                    textColor: '#E57373',
                    borderColor: '#FFDADA'
                },
                'Mon': {
                    dayBgColor: '#FFFCEE',
                    dateBgColor: '#FFF7E0',
                    textColor: '#FFD700',
                    borderColor: '#FFF0C0'
                },
                'Tue': {
                    dayBgColor: '#E8F6F9',
                    dateBgColor: '#D4ECF0',
                    textColor: '#64B5F6',
                    borderColor: '#C0E0E5'
                },
                'Wed': {
                    dayBgColor: '#F0FFF0',
                    dateBgColor: '#E0FFE0',
                    textColor: '#81C784',
                    borderColor: '#D0EED0'
                },
                'Thu': {
                    dayBgColor: '#FFF8E1',
                    dateBgColor: '#FFEFB3',
                    textColor: '#FFB74D',
                    borderColor: '#FFE0A0'
                },
                'Fri': {
                    dayBgColor: '#F5F0FA',
                    dateBgColor: '#EBDDFF',
                    textColor: '#BA68C8',
                    borderColor: '#DDC0FF'
                },
                'Sat': {
                    dayBgColor: '#E0FFFF',
                    dateBgColor: '#CCFFFF',
                    textColor: '#4DD0E1',
                    borderColor: '#B0EEEE'
                }
            };

            function getWeekNumberInMonth(date) {
                const firstDayOfMonth = new Date(date.getFullYear(), date.getMonth(), 1);
                const firstDayOfWeek = firstDayOfMonth.getDay(); // 0 for Sunday, 1 for Monday, etc.

                const adjustedFirstDayOfWeek = (firstDayOfWeek === 0) ? 6 : firstDayOfWeek - 1;

                const dayOfMonth = date.getDate();

                return Math.ceil((dayOfMonth + adjustedFirstDayOfWeek) / 7);
            }

            function renderCustomWeeklyCalendar(startDate) {
                const dayOfWeekIndex = startDate.getDay();
                let diffToMonday = dayOfWeekIndex === 0 ? 6 : dayOfWeekIndex - 1;
                let weekStart = new Date(startDate);
                weekStart.setDate(startDate.getDate() - diffToMonday);
                weekStart.setHours(0, 0, 0, 0);

                calendarDaysRow.innerHTML = '';
                calendarDatesRow.innerHTML = '';

                const currentMonthName = startDate.toLocaleDateString('en-US', {
                    month: 'long'
                });
                const weekNumber = getWeekNumberInMonth(startDate);
                currentWeekRangeSpan.textContent = `${currentMonthName} Week: ${weekNumber}`;

                for (let i = 0; i < 7; i++) {
                    const date = new Date(weekStart);
                    date.setDate(weekStart.getDate() + i);

                    const dayNameShort = date.toLocaleString('en-US', {
                        weekday: 'short'
                    });
                    const dateNum = date.getDate();

                    const dayDiv = document.createElement('div');
                    dayDiv.textContent = dayNameShort;
                    const dateDiv = document.createElement('div');
                    dateDiv.textContent = dateNum;

                    if (date.getTime() === today.getTime()) {
                        const colors = dayColors[dayNameShort];
                        if (colors) {
                             dayDiv.style.backgroundColor = colors.dayBgColor;
                             dayDiv.style.color = colors.textColor;
                             dateDiv.style.backgroundColor = colors.dateBgColor;
                             dateDiv.style.color = colors.textColor;

                             dayDiv.style.borderRightColor = colors.borderColor;
                             dateDiv.style.borderRightColor = colors.borderColor;

                             if (i === 6) {
                                 dayDiv.style.borderRight = 'none';
                                 dateDiv.style.borderRight = 'none';
                             }
                        }
                    }

                    calendarDaysRow.appendChild(dayDiv);
                    calendarDatesRow.appendChild(dateDiv);
                }
            }

            renderCustomWeeklyCalendar(currentWeekStart);

            prevWeekBtn.addEventListener('click', () => {
                currentWeekStart.setDate(currentWeekStart.getDate() - 7);
                renderCustomWeeklyCalendar(currentWeekStart);
            });

            nextWeekBtn.addEventListener('click', () => {
                currentWeekStart.setDate(currentWeekStart.getDate() + 7);
                renderCustomWeeklyCalendar(currentWeekStart);
            });

            customWeeklyCalendarEl.addEventListener('click', function(event) {
                if (event.target.closest('#prevWeekBtn') || event.target.closest('#nextWeekBtn')) {
                    return;
                }
                fullCalendarModal.show();
            });

            var calendarFull = new FullCalendar.Calendar(calendarFullEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next',
                    center: 'title',
                    right: ''
                },
                height: 'auto',
            });

            document.getElementById('full-calendar-modal').addEventListener('shown.bs.modal', function() {
                calendarFull.render();
                calendarFull.updateSize();
            });

            // --- To-Do List functionality ---
            const todoAddForm = document.getElementById('add-task-form');
            const taskDescriptionInput = document.getElementById('task-description-input');
            const todoItemsList = document.getElementById('todo-items-list');
            // Select the no-tasks-message dynamically to ensure it's the right one
            const noTasksMessage = todoItemsList.querySelector('.no-tasks-message');


            // Function to create a new task list item HTML
            function createTaskListItem(task) {
                const listItem = document.createElement('li');
                listItem.classList.add('list-group-item');
                if (task.done) {
                    listItem.classList.add('done');
                }
                listItem.dataset.id = task.id;
                listItem.innerHTML = `
                    <div class="form-check">
                        <input class="form-check-input task-checkbox" type="checkbox" ${task.done ? 'checked' : ''} data-id="${task.id}">
                        <label class="form-check-label task-text">${task.description}</label>
                    </div>
                    <div class="task-actions">
                        <i class="fas fa-grip-vertical reorder-handle me-2"></i>
                        <i class="fas fa-trash-alt delete-task" data-id="${task.id}"></i>
                    </div>
                `;
                return listItem;
            }

            // Function to update the visibility of the "No tasks" message
            function updateNoTasksMessage() {
                const hasTasks = todoItemsList.querySelectorAll('.list-group-item:not(.no-tasks-message)').length > 0;
                if (noTasksMessage) {
                    noTasksMessage.style.display = hasTasks ? 'none' : 'block';
                }
            }

            // Handle adding a new task via AJAX
            if (todoAddForm) {
                todoAddForm.addEventListener('submit', function(event) {
                    event.preventDefault();

                    const taskDescription = taskDescriptionInput.value.trim();
                    if (taskDescription === '') {
                        // Using a custom message box or Bootstrap alert instead of alert()
                        // alert('Task description cannot be empty.');
                        const modal = new bootstrap.Modal(document.getElementById('messageModal'));
                        document.getElementById('messageModalBody').textContent = 'Task description cannot be empty.';
                        modal.show();
                        return;
                    }

                    const formData = new FormData();
                    formData.append('action', 'add_task');
                    formData.append('task_description', taskDescription);

                    fetch('admin_dashboard.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => {
                            if (!response.ok) {
                                return response.text().then(text => {
                                    throw new Error(`HTTP error! status: ${response.status}, response: ${text}`);
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.status === 'success' && data.task) {
                                const newTaskElement = createTaskListItem(data.task);
                                todoItemsList.prepend(newTaskElement);
                                taskDescriptionInput.value = '';
                                updateNoTasksMessage();
                            } else {
                                console.error('Server reported an error:', data.message || 'Unknown error.');
                                // Using a custom message box or Bootstrap alert instead of alert()
                                // alert('Error adding task: ' + (data.message || 'Unknown error.'));
                                const modal = new bootstrap.Modal(document.getElementById('messageModal'));
                                document.getElementById('messageModalBody').textContent = 'Error adding task: ' + (data.message || 'Unknown error.');
                                modal.show();
                            }
                        })
                        .catch(error => {
                            console.error('Fetch error for add task:', error);
                            // Using a custom message box or Bootstrap alert instead of alert()
                            // alert('Failed to add task. Please check the console for details. (Likely a server-side error or corrupted JSON response)');
                            const modal = new bootstrap.Modal(document.getElementById('messageModal'));
                            document.getElementById('messageModalBody').textContent = 'Failed to add task. Please check the console for details. (Likely a server-side error or corrupted JSON response)';
                            modal.show();
                        });
                });
            }

            // Handle task completion/uncompletion with AJAX for immediate UI update and reordering
            if (todoItemsList) {
                todoItemsList.addEventListener('change', function(event) {
                    if (event.target.classList.contains('task-checkbox')) {
                        const taskId = event.target.dataset.id;
                        const listItem = event.target.closest('.list-group-item');
                        const isChecked = event.target.checked;

                        // Optimistically update UI
                        if (isChecked) {
                            listItem.classList.add('done');
                            // Move to bottom after a slight delay for visual effect
                            setTimeout(() => {
                                let firstDoneItem = null;
                                const items = Array.from(todoItemsList.children);
                                for (const item of items) {
                                    if (item.classList.contains('done')) {
                                        firstDoneItem = item;
                                        break;
                                    }
                                }

                                if (firstDoneItem) {
                                    todoItemsList.insertBefore(listItem, firstDoneItem.nextSibling);
                                } else {
                                    todoItemsList.appendChild(listItem);
                                }

                                updateNoTasksMessage();
                                sendReorderRequest();
                            }, 300);
                        } else {
                            listItem.classList.remove('done');
                            updateNoTasksMessage();
                            sendReorderRequest();
                        }

                        const formData = new FormData();
                        formData.append('action', 'toggle_task_status');
                        formData.append('task_id', taskId);

                        fetch('admin_dashboard.php', {
                                method: 'POST',
                                body: formData
                            })
                        .then(response => {
                            if (!response.ok) {
                                return response.text().then(text => {
                                    throw new Error(`HTTP error! status: ${response.status}, response: ${text}`);
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.status === 'success') {
                                console.log('Task status updated successfully.');
                            } else {
                                console.error('Server reported an error updating task status:', data.message || 'Unknown error.');
                                listItem.classList.toggle('done');
                                event.target.checked = !isChecked;
                                // Using a custom message box or Bootstrap alert instead of alert()
                                // alert('Failed to update task status. Please try again.');
                                const modal = new bootstrap.Modal(document.getElementById('messageModal'));
                                document.getElementById('messageModalBody').textContent = 'Failed to update task status. Please try again.';
                                modal.show();
                            }
                        })
                        .catch(error => {
                            console.error('Fetch error toggling task status:', error);
                            listItem.classList.toggle('done');
                            event.target.checked = !isChecked;
                            // Using a custom message box or Bootstrap alert instead of alert()
                            // alert('Failed to update task status. Please check the console for details.');
                            const modal = new bootstrap.Modal(document.getElementById('messageModal'));
                            document.getElementById('messageModalBody').textContent = 'Failed to update task status. Please check the console for details.';
                            modal.show();
                        });
                    }
                });

                // Function to handle deleting a task
                function handleDeleteTask(taskId, listItem) {
                    // Using a custom message box or Bootstrap confirmation instead of confirm()
                    const confirmModalEl = document.getElementById('confirmModal');
                    const confirmModal = new bootstrap.Modal(confirmModalEl);
                    document.getElementById('confirmModalBody').textContent = 'Are you sure you want to delete this task?';
                    
                    document.getElementById('confirmProceedBtn').onclick = function() {
                        confirmModal.hide(); // Hide the modal after confirmation

                        const formData = new FormData();
                        formData.append('action', 'delete_task');
                        formData.append('task_id', taskId);

                        fetch('admin_dashboard.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => {
                                if (!response.ok) {
                                    return response.text().then(text => {
                                        throw new Error(`HTTP error! status: ${response.status}, response: ${text}`);
                                    });
                                }
                                return response.json();
                            })
                            .then(data => {
                                if (data.status === 'success') {
                                    listItem.remove();
                                    updateNoTasksMessage();
                                    console.log('Task deleted successfully.');
                                } else {
                                    console.error('Server reported an error deleting task:', data.message || 'Unknown error.');
                                    // Using a custom message box or Bootstrap alert instead of alert()
                                    // alert('Error deleting task: ' + (data.message || 'Unknown error.'));
                                    const modal = new bootstrap.Modal(document.getElementById('messageModal'));
                                    document.getElementById('messageModalBody').textContent = 'Error deleting task: ' + (data.message || 'Unknown error.');
                                    modal.show();
                                }
                            })
                            .catch(error => {
                                console.error('Fetch error deleting task:', error);
                                // Using a custom message box or Bootstrap alert instead of alert()
                                // alert('Failed to delete task. Please check the console for details.');
                                const modal = new bootstrap.Modal(document.getElementById('messageModal'));
                                document.getElementById('messageModalBody').textContent = 'Failed to delete task. Please check the console for details.';
                                modal.show();
                            });
                    };
                    document.getElementById('confirmCancelBtn').onclick = function() {
                        confirmModal.hide(); // Hide the modal if cancelled
                    };

                    confirmModal.show(); // Show the confirmation modal
                }

                // Add event listener for delete icons (using event delegation)
                todoItemsList.addEventListener('click', function(event) {
                    if (event.target.classList.contains('delete-task')) {
                        const taskId = event.target.dataset.id;
                        const listItem = event.target.closest('.list-group-item');
                        handleDeleteTask(taskId, listItem);
                    }
                });

                // Function to send reorder request after drag-and-drop or status change
                function sendReorderRequest() {
                    const order = [];
                    // Exclude the "no tasks" message from the order array
                    todoItemsList.querySelectorAll('.list-group-item:not(.no-tasks-message)').forEach(item => {
                        order.push(item.dataset.id);
                    });

                    const formData = new FormData();
                    formData.append('action', 'reorder_tasks');
                    formData.append('ordered_tasks', JSON.stringify(order));

                    fetch('admin_dashboard.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => {
                            if (!response.ok) {
                                return response.text().then(text => {
                                    throw new Error(`HTTP error! status: ${response.status}, response: ${text}`);
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.status === 'success') {
                                console.log('Tasks reordered successfully.');
                            } else {
                                console.error('Server reported an error reordering tasks:', data.message || 'Unknown error.');
                                // Using a custom message box or Bootstrap alert instead of alert()
                                // alert('Failed to reorder tasks on server. Page might need refresh.');
                                const modal = new bootstrap.Modal(document.getElementById('messageModal'));
                                document.getElementById('messageModalBody').textContent = 'Failed to reorder tasks on server. Page might need refresh.';
                                modal.show();
                            }
                        })
                        .catch(error => {
                            console.error('Fetch error reordering tasks:', error);
                            // Using a custom message box or Bootstrap alert instead of alert()
                            // alert('Failed to reorder tasks. Please try again. Check console for details.');
                            const modal = new bootstrap.Modal(document.getElementById('messageModal'));
                            document.getElementById('messageModalBody').textContent = 'Failed to reorder tasks. Please try again. Check console for details.';
                            modal.show();
                        });
                }

                // Initialize SortableJS for reordering
                // The filter option ensures that only the reorder handle triggers drag, not clicking the checkbox.
                new Sortable(todoItemsList, {
                    animation: 150,
                    handle: '.reorder-handle',
                    filter: '.form-check, .delete-task', // Prevent dragging when clicking checkbox/text or delete icon
                    onUpdate: function(evt) {
                        sendReorderRequest();
                    }
                });
            }

            // Initial check for "No tasks" message visibility on page load
            updateNoTasksMessage();

            // --- Notice Submission Form Logic ---
            const submitNoticeForm = document.getElementById('submit-notice-form');
            const noticeStreamSelect = document.getElementById('noticeStream');
            const noticeCourseSelect = document.getElementById('noticeCourse');
            const noticeAudienceSelect = document.getElementById('noticeAudience');
            const noticeContentTextarea = document.getElementById('noticeContent');
            const adminNoticesList = document.getElementById('admin-notices-list');
            const noAdminNoticesMessage = adminNoticesList ? adminNoticesList.querySelector('.no-admin-notices-message') : null;

            // Get current course and stream IDs from URL if available
            const urlParams = new URLSearchParams(window.location.search);
            const dashboardCourseId = urlParams.get('course_id');
            const dashboardStreamId = urlParams.get('stream_id');

            // Store all courses data from PHP for JavaScript filtering
            const allCoursesData = <?= json_encode($all_courses) ?>;

            // Function to create a new notice card element
            function createNoticeCard(notice) {
                const noticeCard = document.createElement('div');
                noticeCard.classList.add('admin-notice-card'); // Use the new class for admin notices
                
                let streamAndCourseInfo = '';
                if (notice.stream_name) {
                    streamAndCourseInfo += ` | Stream: ${notice.stream_name}`;
                }
                if (notice.course_name) {
                    streamAndCourseInfo += ` | Course: ${notice.course_name}`;
                }

                noticeCard.innerHTML = `
                    <p class="mb-1"><strong>To:</strong> ${notice.target_audience ? notice.target_audience.charAt(0).toUpperCase() + notice.target_audience.slice(1) : 'N/A'}
                        ${streamAndCourseInfo}
                    </p>
                    <p class="mb-1">${notice.notice_content}</p>
                    <small class="text-muted">Posted: ${new Date(notice.posted_at).toLocaleString('en-US', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' })}</small>
                `;
                return noticeCard;
            }

            // Function to update the visibility of the "No admin notices" message
            function updateNoAdminNoticesMessage() {
                if (noAdminNoticesMessage) {
                    const hasNotices = adminNoticesList.querySelectorAll('.admin-notice-card').length > 0;
                    noAdminNoticesMessage.style.display = hasNotices ? 'none' : 'block';
                }
            }


            // Function to filter courses based on selected stream for the notice form
            function filterCoursesByStream() {
                const selectedStreamId = noticeStreamSelect.value;
                noticeCourseSelect.innerHTML = '<option value="">All Courses</option>'; // Reset courses

                allCoursesData.forEach(course => {
                    // Convert both to string for consistent comparison
                    if (selectedStreamId === '' || String(course.stream_id) === selectedStreamId) {
                        const option = document.createElement('option');
                        option.value = course.course_id;
                        option.textContent = `${course.course_name} (Stream: ${course.stream_id})`;
                        noticeCourseSelect.appendChild(option);
                    }
                });

                // Re-select the correct course if coming from a course-specific dashboard
                if (dashboardCourseId && String(dashboardStreamId) === selectedStreamId) {
                    noticeCourseSelect.value = dashboardCourseId;
                }
            }

            // Disable stream/course dropdowns if on a specific course dashboard
            if (dashboardCourseId) {
                if (noticeStreamSelect) noticeStreamSelect.disabled = true;
                if (noticeCourseSelect) noticeCourseSelect.disabled = true;
            }
            
            // Initial filter on page load for the notice form
            if (noticeStreamSelect) {
                noticeStreamSelect.addEventListener('change', filterCoursesByStream);
                filterCoursesByStream();
            }


            if (submitNoticeForm) {
                submitNoticeForm.addEventListener('submit', function(event) {
                    event.preventDefault();

                    const formData = new FormData(this); // 'this' refers to the form
                    formData.append('action', 'submit_notice');

                    // If on a course-specific dashboard, add course/stream IDs to form data
                    if (dashboardCourseId) {
                        formData.append('course_id', dashboardCourseId);
                        if (dashboardStreamId) {
                            formData.append('stream_id', dashboardStreamId);
                        }
                    }

                    fetch('admin_dashboard.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => {
                            if (!response.ok) {
                                return response.text().then(text => {
                                    throw new Error(`HTTP error! status: ${response.status}, response: ${text}`);
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            const modal = new bootstrap.Modal(document.getElementById('messageModal'));
                            if (data.status === 'success' && data.notice) {
                                document.getElementById('messageModalBody').textContent = data.message;
                                
                                // Create and prepend the new notice card
                                const newNoticeElement = createNoticeCard(data.notice);
                                if (adminNoticesList) {
                                    // Remove "No notices" message if it exists
                                    if (noAdminNoticesMessage && noAdminNoticesMessage.style.display !== 'none') {
                                        noAdminNoticesMessage.style.display = 'none';
                                    }
                                    adminNoticesList.prepend(newNoticeElement);
                                }
                                submitNoticeForm.reset(); // Clear form on success
                                filterCoursesByStream(); // Reset course dropdown as well
                                noticeStreamSelect.value = ''; // Reset stream dropdown

                            } else {
                                console.error('Server reported an error submitting notice:', data.message || 'Unknown error.');
                                document.getElementById('messageModalBody').textContent = 'Error submitting notice: ' + (data.message || 'Unknown error.');
                            }
                            modal.show();
                        })
                        .catch(error => {
                            console.error('Fetch error submitting notice:', error);
                            const modal = new bootstrap.Modal(document.getElementById('messageModal'));
                            document.getElementById('messageModalBody').textContent = 'Failed to submit notice. Please check console for details.';
                            modal.show();
                        });
                });
            }
            updateNoAdminNoticesMessage(); // Initial check for admin notices message
        });
    </script>

    <!-- Message Modal (replaces alert()) -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="messageModalLabel">Notification</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="messageModalBody">
            <!-- Message will be inserted here -->
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirmation Modal (replaces confirm()) -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="confirmModalLabel">Confirm Action</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="confirmModalBody">
            <!-- Confirmation message will be inserted here -->
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="confirmCancelBtn" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" id="confirmProceedBtn">Proceed</button>
          </div>
        </div>
      </div>
    </div>

</body>
<?php include 'footer.php'; ?>
