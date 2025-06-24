<?php
session_start();
$isLoggedIn = (
    (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']) ||
    (isset($_SESSION['loggedin']) && $_SESSION['loggedin'])
);

if (!$isLoggedIn) {
    header("Location: login.php");
    exit();
}

require 'db.php';


/* ---------- fetch chosen course ---------- */
$cid = $_GET['course_id'] ?? '';
if (!ctype_digit($cid)) {
    die('Bad ID');
}

$stmt = $connection->prepare(
    'SELECT c.*, s.stream_name
       FROM courses c
       JOIN streams s ON c.stream_id = s.stream_id
      WHERE c.course_id = ?'
);
$stmt->bind_param('i', $cid);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc() ?: die('Course not found');
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

?>


<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($course['course_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-light">
    <?php include 'header.php'; ?>

    <div class="container py-5">
        <div class="card shadow-lg">
            <div class="row g-0">
                <!-- image -->
                <div class="col-md-5">
                    <img src="<?= htmlspecialchars($course['image_url'] ?: 'https://via.placeholder.com/600x400?text=Course+Image') ?>"
                        class="img-fluid h-100 w-100 object-fit-cover rounded-start" alt="course image">
                </div>

                <!-- details -->
                <div class="col-md-7">
                    <div class="card-body p-4">
                        <h3 class="card-title mb-3"><?= htmlspecialchars($course['course_name']) ?></h3>

                        <p><strong>Stream:</strong> <?= htmlspecialchars($course['stream_name']) ?></p>
                        <p><strong>Duration:</strong> <?= htmlspecialchars($course['estimated_duration']) ?> months</p>
                        <p><strong>Price:</strong> ₹<?= number_format($course['price'], 0, '.', ',') ?></p>
                        <p><strong>Instructor:</strong> <?= htmlspecialchars($course['instructor_name']) ?></p>
                        <p><strong>Skill level:</strong> <?= htmlspecialchars($course['skill_level']) ?></p>
                        <p>
                            <strong>Rating:</strong> <?= renderStars($course['average_rating']) ?>
                            <span class="text-muted">(<?= htmlspecialchars($course['average_rating']) ?> / 5, <?= htmlspecialchars($course['num_reviews']) ?> reviews)</span>
                        </p>

                        <p><strong>Enrolled students:</strong> <?= htmlspecialchars($course['num_students_enrolled']) ?></p>
                        <p><strong>Prerequisites:</strong> <?= htmlspecialchars($course['prerequisites'] ?: 'None') ?></p>

                        <hr>
                        <p><?= nl2br(htmlspecialchars($course['key_topics_summary'])) ?></p>

                        <a href="payment.php?course_id=<?= $cid ?>" class="btn btn-primary btn-lg mt-3">
                            Purchase this course
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>

</html>