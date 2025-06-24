<?php
require_once 'db.php';


$isLoggedIn = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
$userName = $_SESSION['user_name'] ?? '';
$userType = $_SESSION['user_type'] ?? '';
$userId = $_SESSION['user_id'] ?? null;

$displayedName = '';
$newNoticeAvailable = false;
$newPurchaseAvailable = false;
$streams = [];
$coursesByStream = [];

if ($isLoggedIn) {
    switch ($userType) {
        case 'student':
            $sql = "SELECT first_name FROM students WHERE student_id = ?";
            if ($stmt = mysqli_prepare($connection, $sql)) {
                mysqli_stmt_bind_param($stmt, 'i', $userId);
                mysqli_stmt_execute($stmt);
                $res = mysqli_stmt_get_result($stmt);
                if ($row = mysqli_fetch_assoc($res)) {
                    $displayedName = htmlspecialchars($row['first_name']);
                }
                mysqli_stmt_close($stmt);
            } else {
                error_log('Student name query failed: ' . mysqli_error($connection));
                $displayedName = htmlspecialchars($userName);
            }

            $sql = "SELECT COUNT(*) AS cnt FROM notices WHERE is_new = 1";
            if ($res = mysqli_query($connection, $sql)) {
                $newNoticeAvailable = (mysqli_fetch_assoc($res)['cnt'] > 0);
            }
            break;

        case 'teacher':
            $sql = "SELECT full_name FROM teachers WHERE teacher_id = ?";
            if ($stmt = mysqli_prepare($connection, $sql)) {
                mysqli_stmt_bind_param($stmt, 'i', $userId);
                mysqli_stmt_execute($stmt);
                $res = mysqli_stmt_get_result($stmt);
                if ($row = mysqli_fetch_assoc($res)) {
                    $displayedName = htmlspecialchars($row['full_name']);
                }
                mysqli_stmt_close($stmt);
            } else {
                error_log('Teacher name query failed: ' . mysqli_error($connection));
                $displayedName = htmlspecialchars($userName);
            }
            // Teacher-specific data loading can be moved to teachers-section.php if needed
            // For example, if streams and courses are used in the teacher dashboard section,
            // they should be fetched in teachers-section.php or passed as parameters.
            // For now, removing them from here assuming they are only for admin dashboard context.
            break;

        case 'admin':
            $sql = "SELECT admin_name FROM admin WHERE admin_id = ?";
            if ($stmt = mysqli_prepare($connection, $sql)) {
                mysqli_stmt_bind_param($stmt, 'i', $userId);
                mysqli_stmt_execute($stmt);
                $res = mysqli_stmt_get_result($stmt);
                if ($row = mysqli_fetch_assoc($res)) {
                    $displayedName = htmlspecialchars($row['admin_name']);
                }
                mysqli_stmt_close($stmt);
            } else {
                error_log('Admin name query failed: ' . mysqli_error($connection));
                $displayedName = htmlspecialchars($userName);
            }

            // ✅ Check for new purchases in last 24 hours
            $sql = "SELECT COUNT(*) AS cnt FROM enrollments WHERE enrolled_at >= NOW() - INTERVAL 1 DAY";
            if ($res = mysqli_query($connection, $sql)) {
                $newPurchaseCount = 0;
                if ($res = mysqli_query($connection, $sql)) {
                    $row = mysqli_fetch_assoc($res);
                    $newPurchaseCount = (int)$row['cnt'];
                }
            }

            // Load streams for admin dashboard
            $sql = "SELECT stream_id, stream_name FROM streams ORDER BY stream_name";
            if ($res = mysqli_query($connection, $sql)) {
                while ($row = mysqli_fetch_assoc($res)) {
                    $streams[] = $row;
                }
            }

            // Load courses by stream for admin dashboard
            $sql = "SELECT course_id, course_name, stream_id FROM courses ORDER BY course_name";
            if ($res = mysqli_query($connection, $sql)) {
                while ($row = mysqli_fetch_assoc($res)) {
                    $sid = $row['stream_id'];
                    $coursesByStream[$sid][] = [
                        'course_id' => $row['course_id'],
                        'course_name' => $row['course_name']
                    ];
                }
            }
            break;

        default:
            $displayedName = htmlspecialchars($userName);
            break;
    }
}
?>

<div id="rightSidebar" class="right-sidebar">
    <a href="javascript:void(0)" class="closebtn" id="closeSidebar">&times;</a>
    <div class="right-sidebar-content">
        <?php if (!$isLoggedIn) : ?>
            <h4>Unlock a world of learning!</h4>
            <p>Log in or sign up to explore our courses and enhance your skills.</p>
            <button type="button" class="btn btn-primary btn-lg btn-login-signup" data-bs-toggle="modal" data-bs-target="#loginModal">Login / Sign Up</button>
        <?php else : ?>
            <div class="user-sidebar-profile">
                <?php $imageSrc = ($userType === 'student' ? 'images/student.png' : ($userType === 'teacher' ? 'images/teacher.png' : ($userType === 'admin' ? 'images/adminn.png' : 'images/placeholder.png'))); ?>
                <div class="profile-pic-placeholder mb-2 position-relative">
                    <img src="<?= $imageSrc ?>" alt="Profile Picture" class="img-fluid profile-img-rectangle">
                    <?php if ($userType === 'admin' && $newPurchaseCount > 0) : ?>
                        <div class="floating-purchase-alert">
                            🎉 New Purchase! (<?= $newPurchaseCount ?>)
                        </div>
                    <?php endif; ?>
                </div>

                <h5 class="mt-1 text-center"><?= ucfirst($userType) ?>: <?= $displayedName ?></h5>

                <?php /*------------------ STUDENT SECTION ------------------*/ ?>
                <?php if ($userType === 'student') : ?>
                    <?php if ($newNoticeAvailable) : ?>
                        <div class="alert alert-warning mt-3"><strong>Alert! New Notice Available.</strong></div>
                    <?php endif; ?>
                    <a href="student_dashboard.php" class="btn btn-outline-primary mt-3 w-100">My Dashboard</a>
                    <a href="student_holidays.php" class="btn btn-outline-info mt-2 w-100">Holidays</a>
                    <a href="student_download_certificate.php" class="btn btn-outline-success mt-2 w-100">Download Certificate</a>
                    <a href="student_purchase_history.php" class="btn btn-outline-secondary mt-2 w-100">Purchase History</a>
                    <button type="button" id="darkModeToggle" class="btn btn-outline-dark mt-2 w-100">Mode Change</button>
                    <a href="student_settings.php" class="btn btn-outline-warning mt-2 w-100">Settings</a>
                    <a href="logout.php" class="btn btn-danger mt-4 w-100 border-top">Logout</a>

                <?php /*------------------ TEACHER SECTION ------------------*/ ?>
                <?php elseif ($userType === 'teacher') : ?>
                    <?php include 'teachers-section.php' ?>

                <?php /*------------------ ADMIN SECTION ------------------*/ ?>
                <?php elseif ($userType === 'admin') : ?>
                    <div class="mt-2 w-100">
                        <h6 id="toggleDashboardForm" class="btn btn-outline-primary mt-3 w-100">Dashboard</h6>
                        <div id="dashboardFormContainer" class="collapse-transition">
                            <form action="admin_dashboard.php" method="GET" id="applyStreamCourseForm">
                                <select id="streamSelect" class="form-select mb-2" name="stream_id" required>
                                    <option value="">Choose Stream…</option>
                                    <?php foreach ($streams as $s) : ?>
                                        <option value="<?= $s['stream_id'] ?>"><?= htmlspecialchars($s['stream_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select id="courseSelect" class="form-select mb-2" name="course_id" disabled required>
                                    <option value="">Choose stream first…</option>
                                </select>
                                <button type="submit" class="btn btn-success mt-2 w-100">Apply</button>
                                <button type="button" id="resetFilters" class="btn btn-outline-secondary mt-2 w-100">Reset Filters</button>
                            </form>
                        </div>
                    </div>
                    <a href="admin_students.php" class="btn btn-outline-primary mt-2 w-100">Students Section</a>
                    <a href="admin_teachers.php" class="btn btn-outline-primary mt-2 w-100">Teachers Section</a>
                    <a href="admin_courses.php" class="btn btn-outline-primary mt-2 w-100">Courses Section</a>
                    <a href="admin_holidays.php" class="btn btn-outline-primary mt-2 w-100">Holidays Section</a>
                    <a href="admin_settings.php" class="btn btn-outline-primary mt-2 mb-3 w-100">Settings</a>
                    <a href="logout.php" class="btn btn-danger mt-5 w-100 border-top">Log Out</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const darkToggle = document.getElementById('darkModeToggle');
        if (darkToggle) {
            darkToggle.addEventListener('click', () => {
                document.body.classList.toggle('dark-mode');
                localStorage.setItem('darkModeEnabled', document.body.classList.contains('dark-mode'));
            });
            if (localStorage.getItem('darkModeEnabled') === 'true') {
                document.body.classList.add('dark-mode');
            }
        }

        const streamSelect = document.getElementById('streamSelect');
        const courseSelect = document.getElementById('courseSelect');
        const resetBtn = document.getElementById('resetFilters');
        if (streamSelect && courseSelect) {
            // Check if coursesByStream is defined for admin, otherwise initialize as empty
            const coursesByStream = <?php echo json_encode($coursesByStream ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;

            const populateCourses = (sid) => {
                courseSelect.innerHTML = '<option value="">' + (sid ? 'Choose Course…' : 'Choose stream first…') + '</option>';
                if (!sid || !coursesByStream[sid]) {
                    courseSelect.disabled = true;
                    return;
                }
                coursesByStream[sid].forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.course_id;
                    opt.textContent = c.course_name;
                    courseSelect.appendChild(opt);
                });
                courseSelect.disabled = false;
            };

            streamSelect.addEventListener('change', e => {
                populateCourses(e.target.value);
            });

            resetBtn && resetBtn.addEventListener('click', () => {
                streamSelect.value = '';
                populateCourses('');
            });

            populateCourses('');
        }
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
    const dashboardBtn = document.getElementById("toggleDashboardForm");
    const formContainer = document.getElementById("dashboardFormContainer");

    if (dashboardBtn && formContainer) {
        dashboardBtn.addEventListener("click", () => {
            formContainer.classList.toggle("show");
        });
    }
});
</script>

<style>
    body.dark-mode {
        background: #1a1a1a;
        color: #f8f9fa;
    }

    body.dark-mode .right-sidebar {
        background: #333;
        color: #f8f9fa;
    }

    body.dark-mode .card {
        background: #444;
        color: #f8f9fa;
        border-color: #555;
    }

    body.dark-mode .border-top {
        border-color: #555 !important;
    }

    .profile-pic-placeholder {
        width: 100%;
        height: 150px;
        background: #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: visible;
        margin: 0px 0px 15px 0px;
    }

    .profile-img-rectangle {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        border-radius: 10px;
    }

    .right-sidebar {
        justify-content: flex-start;
    }

    .right-sidebar-content {
        padding-top: 0px;
    }

    .collapse-transition {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.5s ease;
    }

    .collapse-transition.show {
        max-height: 1000px;
    }

    .floating-purchase-alert {
        position: absolute;
        top: -50px;
        right: 55px;
        background-color: rgb(255, 193, 7);
        color: black;
        padding: 5px 10px;
        border-radius: 12px;
        font-size: 20px;
        font-weight: bold;
        animation: blinkPurchase 1.2s infinite;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.3);
    }

    @keyframes blinkPurchase {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.6;
        }
    }

    .user-sidebar-profile{
        max-width: 100%;
    }
</style>
