<?php
// START ALL PHP PROCESSING *BEFORE* ANY HTML OR INCLUDES THAT OUTPUT HTML

// We do NOT need session_start() here directly, as it is already included
// at the very top of header.php, which is included later in this file.
session_start();
// 2. Include database connection file
require 'db.php'; // Assuming db.php establishes $connection

// Define the upload directory
$upload_dir = './theadshots/';

// Create the upload directory if it doesn't exist
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true); // Create directory with full permissions recursively
}

// Initialize $display_teacher_id for the form. It will be populated from $_POST if submitted.
$display_teacher_id = ''; 

// 3. Check if the form is submitted and process it
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and get form data for teacher registration
    $full_name = htmlspecialchars(trim($_POST['full_name']));
    $email_address = htmlspecialchars(trim($_POST['email_address']));
    $password = $_POST['upassword']; // Renamed from admin_password to password for clarity
    $confirm_password = $_POST['confirm_upassword']; // Renamed from confirm_admin_password

    // New required fields for teacher
    $stream_id = htmlspecialchars(trim($_POST['stream_id']));
    $course_id = htmlspecialchars(trim($_POST['course_id']));
    
    // Get the teacher_id directly from the form submission
    $teacher_id = htmlspecialchars(trim($_POST['teacher_id']));

    // Optional fields for teacher (set to null if not provided or empty)
    $phone_number = isset($_POST['phone_number']) && !empty(trim($_POST['phone_number'])) ? htmlspecialchars(trim($_POST['phone_number'])) : null;
    // $theadshot will be handled by file upload, not directly from $_POST
    $tdegrees = isset($_POST['tdegrees']) && !empty(trim($_POST['tdegrees'])) ? htmlspecialchars(trim($_POST['tdegrees'])) : null;
    $student_testimonials = isset($_POST['student_testimonials']) && !empty(trim($_POST['student_testimonials'])) ? htmlspecialchars(trim($_POST['student_testimonials'])) : null;
    // For star_rating, convert to float if present and numeric, otherwise null
    $star_rating = isset($_POST['star_rating']) && is_numeric($_POST['star_rating']) ? (float)$_POST['star_rating'] : null;

    // Initialize $theadshot_path to null, will be updated if a file is uploaded
    $theadshot_path = null;

    // --- File Upload Handling for theadshot ---
    if (isset($_FILES['theadshot']) && $_FILES['theadshot']['error'] == UPLOAD_ERR_OK) {
        $file_tmp_name = $_FILES['theadshot']['tmp_name'];
        $file_name = $_FILES['theadshot']['name'];
        $file_size = $_FILES['theadshot']['size'];
        $file_type = $_FILES['theadshot']['type'];

        // Get file extension
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Allowed file extensions
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        // Validate file extension
        if (in_array($file_ext, $allowed_extensions)) {
            // Generate a unique filename to prevent conflicts
            $unique_file_name = uniqid('headshot_', true) . '.' . $file_ext;
            $destination = $upload_dir . $unique_file_name;

            // Move the uploaded file to the desired directory
            if (move_uploaded_file($file_tmp_name, $destination)) {
                $theadshot_path = $destination; // Store the path to the file
            } else {
                $_SESSION['signup_error'] = "Error uploading headshot. Please try again.";
                header("Location: teacher.php");
                exit();
            }
        } else {
            $_SESSION['signup_error'] = "Invalid file type for headshot. Only JPG, JPEG, PNG, and GIF are allowed.";
            header("Location: teacher.php");
            exit();
        }
    }

    // Basic server-side validation
    if (empty($full_name) || empty($email_address) || empty($password) || empty($confirm_password) || empty($stream_id) || empty($course_id) || empty($teacher_id)) {
        $_SESSION['signup_error'] = "Full Name, Email, Password, Stream ID, Course ID, and Teacher ID are required.";
        header("Location: teacher.php");
        exit(); // Always exit after a header redirect
    } elseif (!filter_var($email_address, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['signup_error'] = "Invalid email format.";
        header("Location: teacher.php");
        exit();
    } elseif ($password !== $confirm_password) {
        $_SESSION['signup_error'] = "Passwords do not match.";
        header("Location: teacher.php");
        exit();
    } elseif (!is_numeric($stream_id) || !is_numeric($course_id)) {
        $_SESSION['signup_error'] = "Stream ID and Course ID must be numbers.";
        header("Location: teacher.php");
        exit();
    } elseif ($star_rating !== null && ($star_rating < 0.0 || $star_rating > 5.0)) {
        $_SESSION['signup_error'] = "Star rating must be between 0.0 and 5.0.";
        header("Location: teacher.php");
        exit();
    } else {
        // Check if email already exists in the 'teacher' table
        $check_stmt_email = $connection->prepare("SELECT teacher_id FROM teachers WHERE email_address = ?");
        $check_stmt_email->bind_param("s", $email_address);
        $check_stmt_email->execute();
        $check_stmt_email->store_result();
        if ($check_stmt_email->num_rows > 0) {
            $_SESSION['signup_error'] = "Email address already registered for a teacher.";
            $check_stmt_email->close(); 
            // Keep the connection open here
            header("Location: teacher.php");
            exit();
        }
        $check_stmt_email->close(); 

        // Check if the provided teacher_id already exists
        $check_stmt_teacher_id = $connection->prepare("SELECT teacher_id FROM teachers WHERE teacher_id = ?");
        $check_stmt_teacher_id->bind_param("s", $teacher_id);
        $check_stmt_teacher_id->execute();
        $check_stmt_teacher_id->store_result();
        if ($check_stmt_teacher_id->num_rows > 0) {
            $_SESSION['signup_error'] = "Teacher ID already exists. Please use a different one.";
            $check_stmt_teacher_id->close();
            // Keep the connection open here
            header("Location: teacher.php");
            exit();
        }
        $check_stmt_teacher_id->close();

        // Hash the password before storing
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        
        // Set the display_teacher_id to the one entered by the user
        $display_teacher_id = $teacher_id;

        // Prepare and execute the SQL statement to insert data into the 'teacher' table
        // Ensure the binding types match the database schema:
        // teacher_id (s), stream_id (i), course_id (i), full_name (s), theadshot (s),
        // tdegrees (s), student_testimonials (s), star_rating (d), phone_number (s),
        // email_address (s), password_hashed (s)
        $stmt = $connection->prepare("INSERT INTO teachers (teacher_id, stream_id, course_id, full_name, theadshot, tdegrees, student_testimonials, star_rating, phone_number, email_address, password_hashed) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "siissssdsss",
            $teacher_id, // Using the user-provided teacher_id
            $stream_id,
            $course_id,
            $full_name,
            $theadshot_path, // Use the stored path for the headshot
            $tdegrees,
            $student_testimonials,
            $star_rating,
            $phone_number,
            $email_address,
            $password_hashed
        );

        if ($stmt->execute()) {
            // Registration successful, set a session variable for the modal success message
            $_SESSION['registration_success'] = "teacher"; // Set to "teacher" for clear differentiation
            $_SESSION['registered_teacher_id'] = $teacher_id; // Store the provided ID for display
            $stmt->close(); // Close statement
            // DO NOT CLOSE THE CONNECTION HERE. It will be closed at the very end of the script.
            header("Location: index.php"); // Redirect to index.php or a success page
            exit(); // Always exit after a header redirect
        } else {
            // Registration failed
            $_SESSION['signup_error'] = "Error: " . $stmt->error;
            $stmt->close(); // Close statement
            // DO NOT CLOSE THE CONNECTION HERE.
            header("Location: teacher.php"); // Redirect back to teacher.php
            exit();
        }
    }
}

// NOW, AND ONLY NOW, INCLUDE THE HEADER THAT CONTAINS HTML
// The session will be active here because header.php calls session_start().
include 'header.php';
?>

<style>
    /* Custom styles to refine the layout and appearance */
    /*
        If your header.php does not set a background for the body,
        you might need to uncomment and adjust this:
    */
    /*
    body {
        background-color: #f8f9fa;
        margin: 0;
        padding: 0;
    }
    */

    .signup-page-wrapper {
        margin-top: 50px;
        min-height: 100vh; /* Full viewport height */
        display: flex;
        align-items: center; /* Vertically center content */
        justify-content: center; /* Horizontally center content */
        padding: 20px 0; /* Add some vertical padding */
    }

    .signup-container {
        padding: 30px;
        background-color: #fff; /* Default, will be overridden by media queries for glass effect */
        border-radius: 8px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1); /* Stronger shadow */
        margin: 0 auto;
    }

    .signup-container h2 {
        text-align: center;
        margin-bottom: 30px;
        color: #343a40;
        font-weight: 600; /* Slightly bolder heading */
    }

    /* Adjustments for Bootstrap form elements */
    .form-group label {
        font-weight: bold;
        color: #495057;
        margin-bottom: 0.5rem;
    }

    .form-control {
        padding: 0.75rem 1rem; /* More padding for inputs */
        border-radius: 0.25rem;
        border: 1px solid #ced4da;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-control:focus {
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
        color: #fff;
        padding: 0.75rem 1.5rem;
        border-radius: 0.25rem;
        cursor: pointer;
        font-size: 1.1rem; /* Slightly larger button text */
        font-weight: bold;
        width: 100%;
        margin-top: 15px; /* Space above button */
        transition: background-color 0.3s ease, border-color 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #0056b3;
        border-color: #0056b3;
    }

    .error-message {
        color: #dc3545; /* Bootstrap danger red */
        font-size: 0.875em; /* Smaller font size */
        margin-top: 0.25rem;
        display: block; /* Ensure it takes its own line */
    }

    /* Custom layout for form fields */
    .form-row {
        display: flex;
        flex-wrap: wrap; /* Allows items to wrap onto the next line on smaller screens */
        gap: 20px; /* Space between inline elements */
    }

    .form-row .form-group {
        flex: 1; /* Distribute available space equally */
        margin-bottom: 1rem;
        min-width: calc(50% - 10px); /* Adjust for two columns on wider screens */
    }

    /* Ensure single column layout on very small screens */
    @media (max-width: 575.98px) {
        .form-row .form-group {
            min-width: 100%; /* Full width for extra small screens */
        }
    }


    /* --- Glass Effect and Responsiveness for all screen sizes --- */
    /* Apply glass effect for screens wider than 768px (tablets and desktops) */
    @media (min-width: 768px) {
        .signup-container {
            background-color: rgba(167, 139, 250, 0.81); /* Semi-transparent background */
            backdrop-filter: blur(15px); /* Glass effect blur */
            -webkit-backdrop-filter: blur(15px); /* Safari compatibility */
            color: #fff;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            width: 80vw; /* Responsive width */
            max-width: 900px; /* Max width to prevent it from getting too wide */
        }

        .signup-container h2,
        .form-group label {
            color: #fff; /* White text for glass effect */
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.2); /* Lighter input background for glass effect */
            border-color: rgba(255, 255, 255, 0.5); /* Lighter border for glass effect */
            color: #fff; /* White text in inputs */
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.7); /* Lighter placeholder text */
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
            color: #ffcc00; /* Adjust error color for better visibility on glass effect */
        }
    }

    /* --- Glass Effect for Smaller Screens (Phones and Tablets) --- */
    /* Apply glass effect for screens smaller than 768px */
    @media (max-width: 767.98px) {
        .signup-container {
            background-color: rgba(167, 139, 250, 0.81); /* Semi-transparent background */
            backdrop-filter: blur(10px); /* Slightly less blur for smaller screens */
            -webkit-backdrop-filter: blur(10px); /* Safari compatibility */
            color: #fff;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            width: 90vw; /* Take up more width on smaller screens */
            padding: 20px; /* Slightly less padding */
        }

        .signup-container h2,
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
            color: #ffcc00; /* Adjust error color */
        }

        /* Stack form-row elements on smaller screens */
        .form-row {
            flex-direction: column;
            gap: 0; /* Remove gap when stacked */
        }

        .form-row .form-group {
            margin-bottom: 1rem; /* Add margin between stacked groups */
        }
    }

    /* Styles for the new teachers table */
    .teachers-table-container {
        margin-top: 50px;
        padding: 30px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        margin-bottom: 50px; /* Add some space at the bottom */
    }

    .teachers-table-container h2 {
        text-align: center;
        margin-bottom: 30px;
        color: #343a40;
        font-weight: 600;
    }

    .teachers-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .teachers-table th,
    .teachers-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    .teachers-table th {
        background-color: #f2f2f2;
        font-weight: bold;
        color: #343a40;
    }

    .teachers-table tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .teachers-table tr:hover {
        background-color: #f1f1f1;
    }

    .teachers-table img {
        max-width: 50px;
        height: auto;
        border-radius: 4px;
    }
    
    @media (min-width: 768px) {
        .teachers-table-container {
            background-color: rgba(167, 139, 250, 0.81);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            color: #fff;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
        }
        .teachers-table-container h2 {
            color: #fff;
        }
        .teachers-table th {
            background-color: rgba(255, 255, 255, 0.2);
            color: #fff;
        }
        .teachers-table td {
            color: #fff;
        }
        .teachers-table tr:nth-child(even) {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .teachers-table tr:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }
    }
    @media (max-width: 767.98px) {
        .teachers-table-container {
            background-color: rgba(167, 139, 250, 0.81);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            color: #fff;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        }
        .teachers-table-container h2 {
            color: #fff;
        }
        .teachers-table th {
            background-color: rgba(255, 255, 255, 0.2);
            color: #fff;
        }
        .teachers-table td {
            color: #fff;
        }
        .teachers-table tr:nth-child(even) {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .teachers-table tr:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }
    }
</style>

<div class="signup-page-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="signup-container">
                    <h2>Teacher Signup!</h2>
                    <?php
                    // Check for a signup error message
                    if (isset($_SESSION['signup_error'])) {
                        echo '
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Error!</strong> ' . htmlspecialchars($_SESSION['signup_error']) . '
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
                        // Unset the session variable so the alert doesn't show again on refresh
                        unset($_SESSION['signup_error']);
                    }
                    ?>
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="full_name">Full Name:</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>

                        <div class="form-group">
                            <label for="email_address">Email Address:</label>
                            <input type="email" class="form-control" id="email_address" name="email_address" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="stream_id">Stream ID:</label>
                                <input type="number" class="form-control" id="stream_id" name="stream_id" required>
                            </div>
                            <div class="form-group">
                                <label for="course_id">Course ID:</label>
                                <input type="number" class="form-control" id="course_id" name="course_id" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="password">Password:</label>
                                <input type="password" class="form-control" id="password" name="upassword" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirm Password:</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_upassword" required>
                                <span id="password_match_error" class="error-message" style="display: none;">Passwords do not match.</span>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone_number">Phone Number (Optional):</label>
                                <input type="text" class="form-control" id="phone_number" name="phone_number">
                            </div>
                            <div class="form-group">
                                <label for="star_rating">Star Rating (0.0-5.0, Optional):</label>
                                <input type="number" class="form-control" id="star_rating" name="star_rating" step="0.1" min="0.0" max="5.0">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="theadshot">Teacher Headshot:</label>
                            <input type="file" class="form-control" id="theadshot" name="theadshot" accept="image/*">
                        </div>
                        
                        <div class="form-group">
                            <label for="teacher_id">Teacher ID:</label>
                            <input type="text" class="form-control" id="teacher_id" name="teacher_id" required value="<?php echo htmlspecialchars($display_teacher_id); ?>">
                        </div>

                        <div class="form-group">
                            <label for="tdegrees">Degrees (Optional):</label>
                            <textarea class="form-control" id="tdegrees" name="tdegrees" rows="3" placeholder="e.g., Ph.D. in Computer Science, M.Sc. in Mathematics"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="student_testimonials">Student Testimonials (Optional):</label>
                            <textarea class="form-control" id="student_testimonials" name="student_testimonials" rows="4" placeholder="e.g., 'Excellent teacher, very clear explanations!'"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Register Teacher</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container teachers-table-container">
    <div class="row justify-content-center">
        <div class="col-12">
            <h2>Registered Teachers</h2>
            <div class="table-responsive">
                <table class="teachers-table">
                    <thead>
                        <tr>
                            <th>Teacher ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Stream ID</th>
                            <th>Course ID</th>
                            <th>Phone Number</th>
                            <th>Headshot</th>
                            <th>Degrees</th>
                            <th>Testimonials</th>
                            <th>Star Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch all teachers from the database
                        $sql = "SELECT teacher_id, full_name, email_address, stream_id, course_id, phone_number, theadshot, tdegrees, student_testimonials, star_rating FROM teachers";
                        $result = $connection->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['teacher_id']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['email_address']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['stream_id']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['course_id']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['phone_number']) . "</td>";
                                echo "<td>";
                                if (!empty($row['theadshot']) && file_exists($row['theadshot'])) {
                                    echo "<img src='" . htmlspecialchars($row['theadshot']) . "' alt='Headshot'>";
                                } else {
                                    echo "N/A";
                                }
                                echo "</td>";
                                echo "<td>" . htmlspecialchars($row['tdegrees']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['student_testimonials']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['star_rating']) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='10'>No teachers registered yet.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    const passwordMatchError = document.getElementById('password_match_error');

    function validatePassword() {
        if (password.value !== confirmPassword.value) {
            passwordMatchError.style.display = 'block';
            confirmPassword.setCustomValidity("Passwords Don't Match");
        } else {
            passwordMatchError.style.display = 'none';
            confirmPassword.setCustomValidity(''); // Clear custom validity message
        }
    }

    // Add event listeners for immediate validation feedback
    password.addEventListener('change', validatePassword);
    confirmPassword.addEventListener('keyup', validatePassword);
</script>

<?php 
// Close the database connection here, after all database operations are done
include 'footer.php'; 
$connection->close();

?>