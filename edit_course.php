<?php
session_start(); // Start the session for messages
require 'db.php'; // Include your database connection file

$course_id = null;
$course_data = [];
$edit_mode = false; // Flag to indicate if we are editing an existing course

// --- Part 1: Handle GET request to display the edit form ---
if (isset($_GET['course_id']) && filter_var($_GET['course_id'], FILTER_VALIDATE_INT)) {
    $course_id = $_GET['course_id'];
    $edit_mode = true;

    // Fetch course data from the database
    $stmt = $connection->prepare("SELECT * FROM courses WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $course_data = $result->fetch_assoc();
    } else {
        $_SESSION['error_message'] = "Course not found.";
        header("Location: admin_courses.php");
        exit();
    }
    $stmt->close();
}

// --- Part 2: Handle POST request for updating the course ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $course_id_post = isset($_POST['course_id']) ? filter_var($_POST['course_id'], FILTER_VALIDATE_INT) : null;
    $course_name = htmlspecialchars(trim($_POST['course_name']));
    $estimated_duration = htmlspecialchars(trim($_POST['estimated_duration']));
    $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT); // Validate as float
    $key_topics_summary = htmlspecialchars(trim($_POST['key_topics_summary']));
    $image_url = htmlspecialchars(trim($_POST['image_url']));
    $instructor_name = htmlspecialchars(trim($_POST['instructor_name']));
    $skill_level = htmlspecialchars(trim($_POST['skill_level']));
    $average_rating = filter_var($_POST['average_rating'], FILTER_VALIDATE_FLOAT); // Validate as float
    $num_reviews = filter_var($_POST['num_reviews'], FILTER_VALIDATE_INT); // Validate as int
    $short_tagline = htmlspecialchars(trim($_POST['short_tagline']));
    $num_students_enrolled = filter_var($_POST['num_students_enrolled'], FILTER_VALIDATE_INT); // Validate as int
    $prerequisites = htmlspecialchars(trim($_POST['prerequisites']));
    $badge_label = isset($_POST['badge_label']) && !empty(trim($_POST['badge_label'])) ? htmlspecialchars(trim($_POST['badge_label'])) : null;
    $display_stream_name = htmlspecialchars(trim($_POST['display_stream_name']));
    $stream_id = filter_var($_POST['stream_id'], FILTER_VALIDATE_INT); // Validate as int

    // Basic validation
    if (
        empty($course_name) || empty($estimated_duration) || $price === false || empty($key_topics_summary) ||
        empty($instructor_name) || empty($skill_level) || $average_rating === false || $num_reviews === false ||
        empty($short_tagline) || $num_students_enrolled === false || empty($prerequisites) ||
        empty($display_stream_name) || $stream_id === false || $course_id_post === false
    ) {

        $_SESSION['error_message'] = "Please fill in all required fields correctly.";
    } else {
        // Update the course in the database
        $stmt = $connection->prepare("UPDATE courses SET 
            course_name = ?, 
            estimated_duration = ?, 
            price = ?, 
            key_topics_summary = ?, 
            image_url = ?, 
            instructor_name = ?, 
            skill_level = ?, 
            average_rating = ?, 
            num_reviews = ?, 
            short_tagline = ?, 
            num_students_enrolled = ?, 
            prerequisites = ?, 
            badge_label = ?, 
            display_stream_name = ?, 
            stream_id = ? 
            WHERE course_id = ?");

        $stmt->bind_param(
            "ssdsissdisissii",
            $course_name,
            $estimated_duration,
            $price,
            $key_topics_summary,
            $image_url,
            $instructor_name,
            $skill_level,
            $average_rating,
            $num_reviews,
            $short_tagline,
            $num_students_enrolled,
            $prerequisites,
            $badge_label,
            $display_stream_name,
            $stream_id,
            $course_id_post // Where clause for the specific course
        );

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Course updated successfully!";
            $stmt->close();
            $connection->close();
            header("Location: admin_courses.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Error updating course: " . $stmt->error;
            $stmt->close();
        }
    }
}

// Close the connection if it's open (it will be closed after redirect on success)
// ... all your PHP logic in edit_course.php ...

// Only close the connection at the absolute end of the script execution,
// AFTER all HTML and included files have been processed.
// This ensures any included files (like right_side_bar.php) can still use the connection.

?>
<?php include 'header.php'; ?>


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
    body {
        background-color: #f4f7f6;
        /* Light gray background for the entire page */
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
        padding: 0;
    }

    /* Styles for the header that is *outside* the form's styling */
    .page-header {
        text-align: center;
        margin-top: 30px;
        margin-bottom: 20px;
        color: #343a40;
        /* Dark gray for text, consistent across all sizes */
        font-weight: 600;
        font-size: 2.5em;
        /* Larger font size */
        /* You can add a specific background or other unique styles here */
    }

    /* NEW: Wrapper for the form and its glass effect */
    .form-wrapper {
        max-width: 900px;
        /* Same width as your container was */
        margin: 50px auto;
        /* Centered, with space above and below */
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        /* Default shadow */
        background-color: #fff;
        /* Default background for smaller screens */
        /* All glass effect styles will be applied to this wrapper */
    }

    /* The original .container now only handles padding and any inner spacing */
    .container-inner {
        /* Renamed from .container to clearly separate roles */
        padding: 30px;
        /* Keep padding here */
        /* Remove background-color, border-radius, box-shadow from here for default */
        /* Color of text elements inside will be handled by the form-wrapper's color property */
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        font-weight: bold;
        color: #495057;
        /* Default label color */
        margin-bottom: 0.5rem;
        display: block;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border-radius: 0.25rem;
        border: 1px solid #ced4da;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        box-sizing: border-box;
        /* Ensures padding doesn't increase width */
    }

    .form-control:focus {
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }

    .form-row .form-group {
        flex: 1;
        min-width: calc(50% - 10px);
        /* Two columns */
        margin-bottom: 1rem;
    }

    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
        color: #fff;
        padding: 0.75rem 1.5rem;
        border-radius: 0.25rem;
        cursor: pointer;
        font-size: 1.1rem;
        font-weight: bold;
        width: 100%;
        margin-top: 15px;
        transition: background-color 0.3s ease, border-color 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #0056b3;
        border-color: #0056b3;
    }

    .error-message {
        color: #dc3545;
        font-size: 0.875em;
        margin-top: 0.25rem;
        display: block;
    }

    /* Glass Effect Styles: NOW APPLIED TO .form-wrapper */
    @media (min-width: 768px) {
        .form-wrapper {
            /* Apply glass effect to the wrapper */
            background-color: rgba(167, 139, 250, 0.81);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            color: #fff;
            /* Text color for elements inside the wrapper */
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
        }

        .form-group label {
            color: #fff;
            /* Labels inside the glass wrapper turn white */
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.5);
            color: #fff;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .form-control:focus {
            border-color: #fff;
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
        }

        .btn-primary {
            background-color: #663399;
            border-color: #663399;
            color: #fff;
        }

        .btn-primary:hover {
            background-color: #4c2470;
            border-color: #4c2470;
        }

        .error-message {
            color: #ffcc00;
        }
    }

    @media (max-width: 767.98px) {
        .form-wrapper {
            /* Apply glass effect to the wrapper */
            background-color: rgba(167, 139, 250, 0.81);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            color: #fff;
            /* Text color for elements inside the wrapper */
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            /* Padding should be on the inner container, not the wrapper, for consistency */
        }

        .container-inner {
            padding: 20px;
            /* Adjust padding for smaller screens here */
        }

        .form-group label {
            color: #fff;
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.5);
            color: #fff;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .form-control:focus {
            border-color: #fff;
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
        }

        .btn-primary {
            background-color: #663399;
            border-color: #663399;
            color: #fff;
        }

        .btn-primary:hover {
            background-color: #4c2470;
            border-color: #4c2470;
        }

        .error-message {
            color: #ffcc00;
        }

        .form-row {
            flex-direction: column;
            gap: 0;
        }

        .form-row .form-group {
            margin-bottom: 1rem;
        }
    }
</style>


<body>

    <h1 class="page-header"><?php echo $edit_mode ? 'Edit Course' : 'Add New Course'; ?></h1>

    <div class="form-wrapper">
        <div class="container-inner"> <?php
                                        // Display session messages (success/error)
                                        if (isset($_SESSION['success_message'])) {
                                            echo '<div style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; margin-bottom: 15px; border-radius: 5px; text-align: center;">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
                                            unset($_SESSION['success_message']);
                                        }
                                        if (isset($_SESSION['error_message'])) {
                                            echo '<div style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; margin-bottom: 15px; border-radius: 5px; text-align: center;">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
                                            unset($_SESSION['error_message']);
                                        }
                                        ?>

            <form action="" method="POST">
                <?php if ($edit_mode): ?>
                    <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course_data['course_id']); ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="course_name">Course Name:</label>
                    <input type="text" class="form-control" id="course_name" name="course_name" value="<?php echo htmlspecialchars($course_data['course_name'] ?? ''); ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="estimated_duration">Estimated Duration:</label>
                        <input type="text" class="form-control" id="estimated_duration" name="estimated_duration" value="<?php echo htmlspecialchars($course_data['estimated_duration'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="price">Price (₹):</label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($course_data['price'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="key_topics_summary">Key Topics Summary:</label>
                    <textarea class="form-control" id="key_topics_summary" name="key_topics_summary" rows="3" required><?php echo htmlspecialchars($course_data['key_topics_summary'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="image_url">Image URL:</label>
                    <input type="text" class="form-control" id="image_url" name="image_url" value="<?php echo htmlspecialchars($course_data['image_url'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="instructor_name">Instructor Name:</label>
                        <input type="text" class="form-control" id="instructor_name" name="instructor_name" value="<?php echo htmlspecialchars($course_data['instructor_name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="skill_level">Skill Level:</label>
                        <input type="text" class="form-control" id="skill_level" name="skill_level" value="<?php echo htmlspecialchars($course_data['skill_level'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="average_rating">Average Rating (0.0-5.0):</label>
                        <input type="number" step="0.1" min="0.0" max="5.0" class="form-control" id="average_rating" name="average_rating" value="<?php echo htmlspecialchars($course_data['average_rating'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="num_reviews">Number of Reviews:</label>
                        <input type="number" class="form-control" id="num_reviews" name="num_reviews" value="<?php echo htmlspecialchars($course_data['num_reviews'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="short_tagline">Short Tagline:</label>
                    <input type="text" class="form-control" id="short_tagline" name="short_tagline" value="<?php echo htmlspecialchars($course_data['short_tagline'] ?? ''); ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="num_students_enrolled">Number of Students Enrolled:</label>
                        <input type="number" class="form-control" id="num_students_enrolled" name="num_students_enrolled" value="<?php echo htmlspecialchars($course_data['num_students_enrolled'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="prerequisites">Prerequisites:</label>
                        <input type="text" class="form-control" id="prerequisites" name="prerequisites" value="<?php echo htmlspecialchars($course_data['prerequisites'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="badge_label">Badge Label (Optional):</label>
                        <input type="text" class="form-control" id="badge_label" name="badge_label" value="<?php echo htmlspecialchars($course_data['badge_label'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="display_stream_name">Display Stream Name:</label>
                        <input type="text" class="form-control" id="display_stream_name" name="display_stream_name" value="<?php echo htmlspecialchars($course_data['display_stream_name'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="stream_id">Stream ID:</label>
                    <input type="number" class="form-control" id="stream_id" name="stream_id" value="<?php echo htmlspecialchars($course_data['stream_id'] ?? ''); ?>" required>
                </div>

                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </div>    
<?php
// THIS IS THE BEST PLACE TO CLOSE THE CONNECTION
// It ensures that any part of your included files (header, footer, sidebar)
// that might need database access can still use it.
include 'footer.php';
if (isset($connection) && $connection->ping()) {
    $connection->close();
}
?>
</body>

