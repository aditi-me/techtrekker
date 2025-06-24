<?php
session_start();
require_once 'db.php';

if (!($_SESSION['user_logged_in'] ?? false)) {
    header("Location: login.php");
    exit();
}

$userEmail = $_SESSION['user_email'];
$userType  = $_SESSION['user_type'] ?? '';
$userName  = '';
$phone     = '';

switch ($userType) {
    case 'student':
        $s = $connection->prepare("SELECT CONCAT(first_name,' ',surname), phone_number FROM students WHERE email = ?");
        break;
    case 'teacher':
        $s = $connection->prepare("SELECT full_name, phone_number FROM teachers WHERE email_address = ?");
        break;
    case 'admin':
        $s = $connection->prepare("SELECT admin_name, NULL FROM admin WHERE admin_email = ?");
        break;
    default:
        die('Unknown user type');
}
if (!$s) die("Prepare failed (user query): " . $connection->error);
$s->bind_param("s", $userEmail);
$s->execute();
$s->bind_result($userName, $phone);
$s->fetch();
$s->close();

$courseId = isset($_POST['course_id']) ? (int)$_POST['course_id'] : 0;
$streamId = isset($_POST['stream_id']) ? (int)$_POST['stream_id'] : 0;
$amount   = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;
$method   = $_POST['payment_method'] ?? '';

if (!$courseId || !$streamId || !$amount || !in_array($method, ['card', 'upi', 'netbanking'], true)) {
    die('Invalid request');
}

$stmt = $connection->prepare("SELECT price, course_name, estimated_duration FROM courses WHERE course_id = ?");
if (!$stmt) die("Prepare failed (course query): " . $connection->error);
$stmt->bind_param("i", $courseId);
$stmt->execute();
$stmt->bind_result($dbPrice, $courseName, $courseDuration);
$stmt->fetch();
$stmt->close();

if ((float)$dbPrice !== (float)$amount) {
    die('Price mismatch – possible tampering detected.');
}

$orderId = strtoupper(uniqid('ORD'));
$gatewayTxnRef = 'TXN' . rand(100000, 999999);
$gatewaySuccess = true; // mocked

if ($gatewaySuccess) {
    $enrollmentId = uniqid();
    $enrolledAt   = date('Y-m-d H:i:s');
    $receiptPath  = sprintf('receipts/receipt_%s.html', $enrollmentId);

    $e = $connection->prepare(
        "INSERT INTO enrollments
            (enrollment_id, stream_id, course_id, course_name, total_duration,
             buyer_name, buyer_email, phone_number, amount_paid, enrolled_at, receipt_path)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    if (!$e) die('Prepare failed (enrollments insert): ' . $connection->error);

    $e->bind_param(
        "siisssssdss",
        $enrollmentId,
        $streamId,
        $courseId,
        $courseName,
        $courseDuration,
        $userName,
        $userEmail,
        $phone,
        $amount,
        $enrolledAt,
        $receiptPath
    );
    $e->execute();
    $e->close();

    $html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt ' . $orderId . '</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 700px; margin: auto; padding: 30px; }
        h2 { text-align: center; margin-bottom: 25px; }
        .row { margin-bottom: 10px; }
        .label { display:inline-block; width: 180px; font-weight: 600; }
        .printBtn { margin-top: 30px; text-align:center; }
        @media print { .printBtn { display:none; } }
        hr { margin: 25px 0; }
    </style>
</head>
<body>
<h2>Payment Receipt</h2>
<hr>
<div class="row"><span class="label">Receipt No.:</span> ' . $orderId . '</div>
<div class="row"><span class="label">Date:</span> ' . date('d/m/Y H:i') . '</div>
<div class="row"><span class="label">Name:</span> ' . htmlspecialchars($userName) . '</div>
<div class="row"><span class="label">Email:</span> ' . htmlspecialchars($userEmail) . '</div>
<div class="row"><span class="label">Course:</span> ' . htmlspecialchars($courseName) . '</div>
<div class="row"><span class="label">Duration:</span> ' . htmlspecialchars($courseDuration) . '</div>
<div class="row"><span class="label">Stream ID:</span> ' . $streamId . '</div>
<div class="row"><span class="label">Amount:</span> ₹' . number_format($amount, 2) . '</div>
<div class="row"><span class="label">Payment Mode:</span> ' . ucfirst($method) . '</div>
<div class="row"><span class="label">Txn Ref:</span> ' . $gatewayTxnRef . '</div>
<hr>
<div class="printBtn">
    <button onclick="window.print()">Print / Save as PDF</button>
</div>
</body>
</html>';

    $dir = __DIR__ . '/receipts';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    // ✅ DON'T REASSIGN $receiptPath here
    file_put_contents(__DIR__ . '/' . $receiptPath, $html);
}

if ($gatewaySuccess) {
    $dashboardUrl = '#'; // fallback
    switch ($_SESSION['user_type'] ?? '') {
        case 'admin':
            $dashboardUrl = 'admin_dashboard.php';
            break;
        case 'teacher':
            $dashboardUrl = 'teacher_dashboard.php';
            break;
        case 'student':
            $dashboardUrl = 'student_dashboard.php';
            break;
    }

    echo '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Payment Successful</title>
        <style>
            body {
                background-color: #f9fafb !important;
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                margin: 0;
                padding: 0;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                
                text-align: center;
            }

            .container {
                background: #fff;
                padding: 40px 30px;
                border-radius: 12px;
                box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
                width: 60%;
               margin: 30px 0px 30px 0px;
               height: 550px;
            }

            .dancing-image {
            margin-top: 50px;
                width: 300px;
                margin-bottom: 20px;
                animation: dance 2s infinite;
            }

            @keyframes dance {
                0% { transform: translateY(0) rotate(0deg); }
                25% { transform: translateY(-10px) rotate(-5deg); }
                50% { transform: translateY(0) rotate(5deg); }
                75% { transform: translateY(-10px) rotate(-5deg); }
                100% { transform: translateY(0) rotate(0deg); }
            }

            .message {
                font-size: 24px;
                font-weight: bold;
                color: #333;
            }

            .subtext {
                font-size: 16px;
                color: #555;
                margin-top: 10px;
                margin-bottom: 25px;
            }

            .btn {
                display: inline-block;
                margin: 10px 10px 0;
                padding: 12px 20px;
                background-color: #a78bfa;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                font-size: 15px;
                transition: background-color 0.3s ease;
            }

            .btn:hover {
                background-color: #1e3a8a;
            }

            .btn.dashboard {
                background-color: #1e3a8a;
            }

            .btn.dashboard:hover {
                background-color: #a78bfa;
            }
        </style>
    </head>
    <body>
        <div class="container">
        <div class="message">Payment Successful!</div>
            <img class="dancing-image" src="images/her2.png" alt="Happy Image">
            <div class="subtext">Please wait up to 24 hours for your batch to be assigned.<br>Keep an eye on your <strong>Dashboard</strong>.</div>
            <a class="btn" href="' . $receiptPath . '" download>Download Receipt</a>
            <a class="btn dashboard" href="' . $dashboardUrl . '">Go to Dashboard</a>
        </div>
    </body>
    </html>
    ';
    exit;
}
