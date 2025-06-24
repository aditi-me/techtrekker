<?php
session_start();
include 'header.php';
require 'db.php';
$isLoggedIn = (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']) || (isset($_SESSION['loggedin']) && $_SESSION['loggedin']);
$selected_stream_id = $_GET['stream_id'] ?? '';
$selected_duration = $_GET['duration'] ?? '';
$selected_price = $_GET['price'] ?? '';
$filters_applied_message = '';
$filters_active_on_load = false;
if (!empty($selected_stream_id) || !empty($selected_duration) || !empty($selected_price)) {
  $filters_active_on_load = true;
}
$sql = "SELECT c.*, s.stream_name AS display_stream_name FROM courses c JOIN streams s ON c.stream_id = s.stream_id";
$conditions = [];
$params = [];
$param_types = '';
$stream_name_for_alert = 'N/A';
if (!empty($selected_stream_id) && $selected_stream_id !== "0") {
  $stream_name_for_alert_query = mysqli_query($connection, "SELECT stream_name FROM streams WHERE stream_id = " . (int)$selected_stream_id);
  if ($stream_name_for_alert_row = mysqli_fetch_assoc($stream_name_for_alert_query)) {
    $stream_name_for_alert = $stream_name_for_alert_row['stream_name'];
  }
}
$is_stream_selected = !empty($selected_stream_id) && $selected_stream_id !== "0";
$duration_customized = !empty($selected_duration) && (int)$selected_duration > 0 && (int)$selected_duration !== 1;
$price_customized = !empty($selected_price) && (int)$selected_price > 0 && (int)$selected_price !== 5000;
$any_filter_active = $is_stream_selected || $duration_customized || $price_customized;
if (!$any_filter_active) {
  $sql = "SELECT c.*, s.stream_name AS display_stream_name FROM courses c JOIN streams s ON c.stream_id = s.stream_id WHERE c.course_id IN (SELECT MIN(course_id) FROM courses GROUP BY stream_id)";
  $filters_applied_message = '';
} else {
  if ($is_stream_selected) {
    $conditions[] = "c.stream_id = ?";
    $params[] = $selected_stream_id;
    $param_types .= 'i';
  }
  if ($duration_customized) {
    $conditions[] = "c.estimated_duration <= ?";
    $params[] = (int)$selected_duration;
    $param_types .= 'i';
  }
  if ($price_customized) {
    $conditions[] = "c.price <= ?";
    $params[] = (int)$selected_price;
    $param_types .= 'i';
  }
  if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
  }
  $message_parts = [];
  $message_parts_initial = 'Showing results for: ';
  if ($is_stream_selected) {
    $message_parts[] = '<strong>' . $stream_name_for_alert . '</strong>';
  }
  if ($duration_customized) {
    $message_parts[] = 'within <strong>' . $selected_duration . ' Months</strong>';
  }
  if ($price_customized) {
    $message_parts[] = 'price up to <strong>₹' . number_format($selected_price, 0, '.', ',') . '</strong>';
  }
  if (!empty($message_parts)) {
    $filters_applied_message = $message_parts_initial;
    if (count($message_parts) == 1) {
      $filters_applied_message .= $message_parts[0] . '.';
    } elseif (count($message_parts) == 2) {
      $filters_applied_message .= $message_parts[0] . ' and ' . $message_parts[1] . '.';
    } else {
      $last_part = array_pop($message_parts);
      $filters_applied_message .= implode(', ', $message_parts) . ', and ' . $last_part . '.';
    }
  }
}
$sql .= " ORDER BY c.course_id ASC";
if ($stmt = mysqli_prepare($connection, $sql)) {
  if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $param_types, ...$params);
  }
  mysqli_stmt_execute($stmt);
  $courses_result = mysqli_stmt_get_result($stmt);
} else {
  die("Error preparing statement: " . mysqli_error($connection));
}
$streams_result = mysqli_query($connection, "SELECT stream_id, stream_name FROM streams ORDER BY stream_id ASC");
if (!$streams_result) {
  die("Error fetching streams: " . mysqli_error($connection));
}
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
  body {
    background-color: #f9fafb;
  }

  h1 {
    display: flex;
    justify-content: center;
    justify-content: center;
    color: #1E3A8A;
    margin-top: 50px;
    text-align: center;
  }

  .main-content-wrapper {
    display: flex;
    min-height: calc(100vh - 0px);
    flex-grow: 1;
  }

  .sidebar {
    width: 350px;
    padding: 0px 60px 0px 60px;
    margin-top: 50px;
    border-right: 1px solid black;
    flex-shrink: 0;
  }

  .course-content {
    flex-grow: 1;
    padding: 0px 30px 50px 30px;
    overflow-y: auto;
    max-height: 950px;
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    align-content: flex-start;
    margin-top: 55px;
  }

  .course-card {
    width: calc(50% - 10px);
    min-width: 280px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s;
    display: flex;
    flex-direction: column;
    position: relative;
  }

  .course-card:hover {
    transform: translateY(-5px);
  }

  .course-card img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-bottom: 1px solid #e0e0e0;
  }

  .course-card-body {
    padding: 15px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
  }

  .course-card-body h5 {
    font-size: 1.1rem;
    margin-bottom: 10px;
    color: #1E3A8A;
    font-weight: 600;
  }

  .course-card-body p {
    font-size: 0.9rem;
    margin-bottom: 8px;
    color: #555;
  }

  .course-card-footer {
    padding: 15px;
    border-top: 1px solid #e0e0e0;
    background-color: #f9f9f9;
    display: flex;
    align-items: center;
  }

  .course-card-footer .badge {
    font-size: 0.8rem;
    padding: 5px 8px;
    border-radius: 4px;
  }

  .bg-info {
    --bs-bg-opacity: 1;
    background-color: rgb(255, 193, 7) !important;
    color: #1E3A8A;
  }

  .enroll {
    background-color: #a78bfa !important;
    color: white;
    font-weight: 600;
  }

  .enroll-btn {
    background-color: #a78bfa !important;
    color: white;
    font-weight: 600;
  }

  .filter-heading {
    display: flex;
    flex-direction: column-reverse;
    align-items: center;
    justify-content: center;
    gap: 20px;
    margin-bottom: 20px;
  }

  .filter-heading img {
    max-width: 300px;
    height: auto;
  }

  #duration::-webkit-slider-runnable-track {
    background: #d3d3d3;
    border-radius: 5px;
    height: 8px;
  }

  #duration::-moz-range-track {
    background: #d3d3d3;
    border-radius: 5px;
    height: 8px;
  }

  #duration::-ms-track {
    background: #d3d3d3;
    border-radius: 5px;
    height: 8px;
    color: transparent;
  }

  #duration::-webkit-slider-thumb {
    background: #1E3A8A;
    border: none;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    margin-top: -5px;
  }

  #duration::-moz-range-thumb {
    background: #1E3A8A;
    border: none;
    border-radius: 50%;
    width: 18px;
    height: 18px;
  }

  #duration::-ms-thumb {
    background: #1E3A8A;
    border: none;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    margin-top: 0;
  }

  #price::-webkit-slider-runnable-track {
    background: #d3d3d3;
    border-radius: 5px;
    height: 8px;
  }

  #price::-moz-range-track {
    background: #d3d3d3;
    border-radius: 50%;
    height: 8px;
  }

  #price::-ms-track {
    background: #d3d3d3;
    border-radius: 5px;
    height: 8px;
    color: transparent;
  }

  #price::-webkit-slider-thumb {
    background: #1E3A8A;
    border: none;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    margin-top: -5px;
  }

  #price::-moz-range-thumb {
    background: #1E3A8A;
    border: none;
    border-radius: 50%;
    width: 18px;
    height: 18px;
  }

  #price::-ms-thumb {
    background: #1E3A8A;
    border: none;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    margin-top: 0;
  }

  @media (max-width: 1200px) {
    .course-card {
      width: calc(50% - 20px);
    }
  }

  @media (max-width: 768px) {
    h1 {
      text-align: center;
    }

    .main-content-wrapper {
      flex-direction: column;
      min-height: auto;
    }

    .sidebar {
      width: 100%;
      border-right: none;
      border-bottom: 1px solid black;
      margin-top: 20px;
      padding: 20px;
    }

    .course-content {
      padding: 20px;
      max-height: none;
      flex-direction: column;
      align-items: center;
    }

    .course-card {
      width: 95%;
      margin: 0 auto;
    }
  }
</style>

<body>
  <h1>Unlock Your Potential: Explore Our Diverse Courses!</h1>
  <div class="main-content-wrapper">
    <div class="sidebar">
      <div class="filter-heading">
        <h5 class="text-primary mb-0" style="color: #1E3A8A !important; font-weight:700; text-align:center;">Filter Courses</h5>
        <img src="images/her4.png" alt="Filter Icon">
      </div>
      <form action="courses.php" method="GET">
        <div class="mb-3">
          <label for="stream_id" class="form-label text-secondary">Stream</label>
          <select class="form-select" id="stream_id" name="stream_id">
            <option value="0">-- Select a Stream --</option>
            <?php
            mysqli_data_seek($streams_result, 0);
            if (mysqli_num_rows($streams_result) > 0) {
              while ($row = mysqli_fetch_assoc($streams_result)) {
                $streamId = htmlspecialchars($row['stream_id']);
                $streamName = htmlspecialchars($row['stream_name']);
                $selected = ($selected_stream_id == $streamId) ? 'selected' : '';
                echo "<option value='$streamId' $selected>$streamName</option>";
              }
            } else {
              echo '<option value="0" disabled>No streams found</option>';
            }
            ?>
          </select>
        </div>
        <div class="mb-4">
          <label for="duration" class="form-label text-secondary" style="margin-top:20px;">Duration (Months)</label>
          <input type="range" class="form-range" min="1" max="8" step="1" id="duration" name="duration" value="<?php echo (!empty($selected_duration)) ? $selected_duration : '1'; ?>" />
          <div class="d-flex justify-content-between small text-muted mt-1">
            <span>1 month</span>
            <span>8 months</span>
          </div>
          <div id="duration_value" class="text-center fw-bold mt-2"></div>
        </div>
        <div class="mb-4">
          <label for="price" class="form-label text-secondary">Price (INR)</label>
          <input type="range" class="form-range" min="5000" max="20000" step="500" id="price" name="price" value="<?php echo (!empty($selected_price)) ? $selected_price : '5000'; ?>" />
          <div class="d-flex justify-content-between small text-muted mt-1">
            <span>₹5K</span>
            <span>₹20K</span>
          </div>
          <div id="price_value" class="text-center fw-bold mt-2"></div>
        </div>
        <div class="d-grid">
          <button type="submit" class="btn btn-primary ">Apply Filters</button>
        </div>
      </form>
      <div class="d-grid mt-3">
        <a href="courses.php" class="btn btn-outline-secondary">Reset Filters</a>
      </div>
      <hr />
      <p class="small text-center text-muted">
        <span class="badge bg-warning text-dark fw-semibold">Note:</span> Click "Apply Filters" to update.
      </p>
    </div>
    <div class="course-content" id="course_cards_container">
      <?php if (!empty($filters_applied_message)): ?>
        <div class="alert alert-success alert-dismissible fade show w-100" role="alert">
          <?php echo $filters_applied_message; ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>
      <?php
      function renderStars($rating)
      {
        $fullStars = floor($rating);
        $halfStar = ($rating - $fullStars >= 0.25 && $rating - $fullStars < 0.75) ? 1 : 0;
        $fullStars += ($rating - $fullStars >= 0.75) ? 1 : 0;
        $emptyStars = 5 - $fullStars - $halfStar;

        $output = str_repeat('<i class="fas fa-star" style="color: gold;"></i>', $fullStars);
        if ($halfStar) {
          $output .= '<i class="fas fa-star-half-alt" style="color: gold;"></i>';
        }
        $output .= str_repeat('<i class="far fa-star" style="color: gold;"></i>', $emptyStars);
        return $output;
      }

      if (mysqli_num_rows($courses_result) > 0) {
        while ($course = mysqli_fetch_assoc($courses_result)) {
          $imageUrl = htmlspecialchars($course['image_url'] ?? 'https://via.placeholder.com/400x180?text=Course+Image');
          $courseName = htmlspecialchars($course['course_name'] ?? 'N/A');
          $duration = htmlspecialchars($course['estimated_duration'] ?? 'N/A') . ' Months';
          $price = '₹' . number_format($course['price'] ?? 0, 0, '.', ',');
          $rating = htmlspecialchars($course['average_rating'] ?? 'N/A');
          $reviews = htmlspecialchars($course['num_reviews'] ?? 0);
          $students = htmlspecialchars($course['num_students_enrolled'] ?? 0);
          $prerequisites = htmlspecialchars($course['prerequisites'] ?? 'None');
          $badgeLabel = htmlspecialchars($course['badge_label'] ?? '');
          $courseLink = $isLoggedIn ? 'cdetails.php?course_id=' . urlencode($course['course_id']) : '#';
          $extraAttrs = $isLoggedIn ? '' : 'data-bs-toggle="modal" data-bs-target="#loginModal"';
          echo '
                    <div class="course-card">
                        ' . ($badgeLabel ? '<span class="badge bg-info position-absolute top-0 end-0 m-2">' . $badgeLabel . '</span>' : '') . '
                        <img src="' . $imageUrl . '" class="card-img-top" alt="' . $courseName . '">
                        <div class="course-card-body">
                            <h5 class="card-title">' . $courseName . '</h5>
                            <p class="card-text"><strong>Estimated Duration:</strong> ' . $duration . '</p>
                            <p class="card-text"><strong>Price:</strong> ' . $price . '</p>
                            <p class="card-text"><strong>Rating:</strong> ' . renderStars($course['average_rating']) . ' <span class="text-muted">(' . $rating . ' / 5, ' . $reviews . ' reviews)</span></p>
                            <p class="card-text"><strong>Enrolled Students:</strong> ' . $students . '</p>
                            <p class="card-text"><strong>Prerequisites:</strong> ' . $prerequisites . '</p>
                        </div>
                        <div class="course-card-footer justify-content-end">
                            <a href="' . $courseLink . '" class="btn btn-sm enroll-btn" data-course-id="' . $course['course_id'] . '" ' . $extraAttrs . '>
                                Enroll Now
                            </a>
                        </div>
                    </div>';
        }
      } else {
        echo '<p class="text-muted text-center w-100">No courses found matching your criteria.</p>';
      }
      ?>
      <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="loginModalLabel">Login Required</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <p>You need to be logged in to enroll in a course and view its details.</p>
              <p>Please log in or register to continue.</p>
              <a href="login.php" class="btn btn-primary me-2">Log In</a>
              <a href="register.php" class="btn btn-outline-secondary">Register</a>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.toString() !== '') {
        window.history.replaceState({}, document.title, "courses.php");
      }
      const durationRange = document.getElementById('duration');
      const durationValue = document.getElementById('duration_value');
      durationRange.addEventListener('input', function() {
        durationValue.textContent = this.value + ' M';
      });
      durationValue.textContent = durationRange.value + ' M';
      const priceRange = document.getElementById('price');
      const priceValue = document.getElementById('price_value');
      priceRange.addEventListener('input', function() {
        priceValue.textContent = '₹' + this.value;
      });
      priceValue.textContent = '₹' + priceRange.value;
      const enrollButtons = document.querySelectorAll('.enroll-btn');
      const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
      const isLoggedIn = <?php echo (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']) || (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) ? 'true' : 'false'; ?>;
      enrollButtons.forEach(button => {
        button.addEventListener('click', function(event) {
          event.preventDefault();
          if (isLoggedIn) {
            const courseId = this.getAttribute('data-course-id');
            window.location.href = 'cdetails.php?course_id=' + courseId;
          } else {
            loginModal.show();
          }
        });
      });
    });
  </script>
</body>
<?php include 'footer.php'; ?>
<?php mysqli_close($connection); ?>