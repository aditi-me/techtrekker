<?php
// Include header.php (which should include session_start() and db.php)
include 'header.php';
require_once 'db.php'; // Ensure db.php is available for direct use

// Array to store all unique teachers and their associated courses
$unique_teachers = [];
$stream_names = [
    1 => 'Computer Science',
    2 => 'Artificial Intelligence & Data Science',
    3 => 'Mechanical Engineering',
    4 => 'Biotechnology',
    5 => 'Information Technology',
];

$fetch_error_message = ''; // Variable to store any database fetch errors

// Fetch all teachers along with their course and stream IDs
// We'll group courses by teacher later in PHP
$sql = "SELECT
            t.teacher_id,
            t.stream_id,
            t.course_id,
            t.full_name,
            t.theadshot,
            t.tdegrees,
            t.student_testimonials,
            t.star_rating,
            t.phone_number,
            t.email_address,
            s.stream_name,        -- Get stream name from the streams table
            c.course_name         -- Get course name from the courses table
        FROM
            teachers t
        JOIN
            streams s ON t.stream_id = s.stream_id
        JOIN
            courses c ON t.course_id = c.course_id
        ORDER BY
            t.full_name, t.stream_id, c.course_name"; // Corrected: changed t.course_name to c.course_name

$result = $connection->query($sql);

// Check if the query was successful
if ($result === FALSE) {
    // Query failed, get the error message from the database connection
    $fetch_error_message = "Database query failed: " . $connection->error;
} else {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $teacher_email = $row['email_address']; // Use email as a unique key for teachers

            // Initialize teacher entry if it doesn't exist
            if (!isset($unique_teachers[$teacher_email])) {
                $unique_teachers[$teacher_email] = [
                    'teacher_id' => $row['teacher_id'],
                    'full_name' => $row['full_name'],
                    'theadshot' => $row['theadshot'],
                    'tdegrees' => $row['tdegrees'],
                    'student_testimonials' => $row['student_testimonials'],
                    'star_rating' => $row['star_rating'],
                    'phone_number' => $row['phone_number'],
                    'email_address' => $row['email_address'],
                    'courses_taught' => [] // Initialize array to hold all courses for this teacher
                ];

                // Decode headshot path
                $unique_teachers[$teacher_email]['decoded_headshot'] = 'theadshots/default_placeholder.jpg';
                if (!empty($row['theadshot'])) {
                    // Assuming 'theadshot' is stored as a string path (e.g., './theadshots/image.jpg')
                    // If it's a binary string from a BLOB, you might need to convert it first.
                    $binary = $row['theadshot'];

                    // Remove leading './' or '.\' if present
                    $decoded_path = ltrim($binary, './\\');
                    $server_path = __DIR__ . '/' . $decoded_path; // Construct server-side path

                    if (!empty($decoded_path) && file_exists($server_path)) {
                        $unique_teachers[$teacher_email]['decoded_headshot'] = $decoded_path;
                    }
                }
            }

            // Add the current course to the teacher's list of courses
            $unique_teachers[$teacher_email]['courses_taught'][] = [
                'course_id' => $row['course_id'],
                'course_name' => $row['course_name'],
                'stream_name' => $row['stream_name'], // Use stream_name from the join
                'stream_id' => $row['stream_id']     // ADDED: Include stream_id in the course array
            ];
        }
    }
    $result->free(); // Free the result set only if the query was successful
}
?>

<style>
    /* Your existing CSS styles */
    .stream-section.left .row {
        justify-content: flex-start;
    }

    .stream-section.right .row {
        justify-content: flex-end;
    }

    .teacher-card {
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s ease-in-out;
        cursor: pointer;
        margin-bottom: 20px;
        overflow: hidden;
        text-align: center;
        width: 300px;
    }

    .teacher-card:hover {
        transform: translateY(-5px);
    }

    .teacher-card img {
        width: 100%;
        height: 300px;
        object-fit: cover;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
        /* for rounded images instead of square, uncomment below line */
        /* border-radius: 50%; */
    }

    .teacher-card .card-body {
        padding: 15px;
    }

    .teacher-card .card-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #343a40;
        margin-bottom: 5px;
    }

    .stream-title {
        text-align: center;
        color: #007bff;
        border-bottom: 2px solid #007bff;
        padding-bottom: 10px;
        margin: 30px auto;
    }

    /* Styles for the modal content */
    #modalTeacherCoursesList {
        list-style-type: none;
        padding: 0;
        text-align: left; /* Align course list to left */
    }
    #modalTeacherCoursesList li {
        margin-bottom: 5px;
        padding-left: 10px;
        position: relative;
    }
    #modalTeacherCoursesList li::before {
        content: "\2022"; /* Bullet point */
        color: #007bff; /* Blue bullet */
        font-weight: bold;
        display: inline-block;
        width: 1em;
        margin-left: -1em;
    }
</style>

<div class="container">
    <h1 class="text-center my-4">Our Esteemed Teachers</h1>

    <?php
    // Display database fetch error if any occurred
    if (!empty($fetch_error_message)) {
        echo '<div class="alert alert-danger text-center" role="alert">' . htmlspecialchars($fetch_error_message) . '</div>';
    }

    // Group teachers by stream for display, ensuring unique teachers are maintained
    $grouped_teachers_for_display = [];
    foreach ($unique_teachers as $email => $teacher_info) {
        // Iterate through each course the teacher teaches
        foreach ($teacher_info['courses_taught'] as $course_info) {
            // Ensure stream_id is numeric and valid before using as array key
            $stream_id = (int)$course_info['stream_id']; // This line will now correctly access stream_id
            if (!isset($grouped_teachers_for_display[$stream_id])) {
                $grouped_teachers_for_display[$stream_id] = [];
            }
            // Add the teacher to this stream's list if not already added to prevent duplicates
            $grouped_teachers_for_display[$stream_id][$teacher_info['teacher_id']] = $teacher_info;
        }
    }

    ksort($grouped_teachers_for_display); // Sort streams by ID
    $alignment_class = ['left', 'right'];
    $index = 0;

    foreach ($grouped_teachers_for_display as $stream_id => $teachers_in_stream) {
        $alignment = $alignment_class[$index % 2];
        $stream_display_name = $stream_names[$stream_id] ?? "Stream $stream_id";

        echo '<div class="stream-section ' . $alignment . '">';
        echo '<h2 class="stream-title">' . htmlspecialchars($stream_display_name) . '</h2>';
        echo '<div class="row">';

        foreach ($teachers_in_stream as $teacher) {
            // Encode the courses_taught array as a JSON string to pass it via data-attribute
            $courses_json = htmlspecialchars(json_encode($teacher['courses_taught']), ENT_QUOTES, 'UTF-8');

            echo '<div class="teacher-card mx-3" data-toggle="modal" data-target="#teacherModal" '
                . 'data-full_name="' . htmlspecialchars($teacher['full_name']) . '" '
                . 'data-degrees="' . htmlspecialchars($teacher['tdegrees'] ?? 'N/A') . '" '
                . 'data-testimonials="' . htmlspecialchars($teacher['student_testimonials'] ?? 'No testimonials yet.') . '" '
                . 'data-rating="' . htmlspecialchars($teacher['star_rating'] ?? '0.0') . '" '
                . 'data-phone="' . htmlspecialchars($teacher['phone_number'] ?? 'N/A') . '" '
                . 'data-email="' . htmlspecialchars($teacher['email_address']) . '" '
                . 'data-headshot="' . htmlspecialchars($teacher['decoded_headshot']) . '" '
                . 'data-courses="' . $courses_json . '">'; // Pass courses as JSON

            echo '<img src="' . htmlspecialchars($teacher['decoded_headshot']) . '" alt="' . htmlspecialchars($teacher['full_name']) . '">';
            echo '<div class="card-body">';
            echo '<h5 class="card-title">' . htmlspecialchars($teacher['full_name']) . '</h5>';
            echo '</div>';
            echo '</div>';
        }

        echo '</div>';
        echo '</div>';
        $index++;
    }

    // Only show "No teacher data available." if there was no fetch error and no teachers found
    if (empty($unique_teachers) && empty($fetch_error_message)) {
        echo '<p class="text-center">No teacher data available.</p>';
    }
    ?>
</div>

<div class="modal fade" id="teacherModal" tabindex="-1" aria-labelledby="teacherModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="teacherModalLabel">Teacher Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="modalTeacherHeadshot" src="" alt="Teacher Headshot" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                <h4 id="modalTeacherFullName" class="mb-2"></h4>
                <p><strong>Degrees:</strong> <span id="modalTeacherDegrees"></span></p>
                <p><strong>Star Rating:</strong> <span id="modalTeacherRating" class="star-rating"></span></p>
                <p><strong>Testimonials:</strong> <span id="modalTeacherTestimonials"></span></p>
                <p><strong>Phone:</strong> <span id="modalTeacherPhone"></span></p>
                <p><strong>Email:</strong> <span id="modalTeacherEmail"></span></p>

                <hr class="my-3">
                <h5>Courses Taught:</h5>
                <ul id="modalTeacherCoursesList">
                    </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    $('#teacherModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var fullName = button.data('full_name');
        var degrees = button.data('degrees');
        var testimonials = button.data('testimonials');
        var rating = button.data('rating');
        var phone = button.data('phone');
        var email = button.data('email');
        var headshot = button.data('headshot');
        var courses = button.data('courses'); // Get the JSON string of courses

        var modal = $(this);
        modal.find('#modalTeacherFullName').text(fullName);
        modal.find('#modalTeacherDegrees').text(degrees);
        modal.find('#modalTeacherTestimonials').text(testimonials);
        modal.find('#modalTeacherPhone').text(phone);
        modal.find('#modalTeacherEmail').text(email);
        modal.find('#modalTeacherHeadshot').attr('src', headshot);

        // Populate star rating
        var starHtml = '';
        var fullStars = Math.floor(rating);
        var hasHalfStar = (rating % 1 !== 0);
        for (var i = 0; i < fullStars; i++) { starHtml += '<i class="fas fa-star"></i>'; }
        if (hasHalfStar) { starHtml += '<i class="fas fa-star-half-alt"></i>'; }
        for (var i = 0; i < (5 - Math.ceil(rating)); i++) { starHtml += '<i class="far fa-star"></i>'; }
        modal.find('#modalTeacherRating').html(starHtml + ' (' + rating + ')');

        // Populate courses taught
        var coursesList = modal.find('#modalTeacherCoursesList');
        coursesList.empty(); // Clear previous courses
        if (courses && courses.length > 0) {
            courses.forEach(function(course) {
                coursesList.append('<li><strong>' + course.course_name + '</strong> (Stream: ' + course.stream_name + ')</li>');
            });
        } else {
            coursesList.append('<li>No courses listed for this teacher.</li>');
        }
    });
</script>

<?php include 'footer.php'; ?>