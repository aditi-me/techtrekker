<?php
/* ------------------------------------------------------------------
   payment.php  –  confirms order + lets user pick a payment method
   ---------------------------------------------------------------- */
session_start();
require_once 'db.php';          // gives $connection

// ---------- 0. Guard: only logged-in users ----------
if (!($_SESSION['user_logged_in'] ?? false)) {
    header("Location: login.php");
    exit();
}

// ---------- 1. Pull user + course details ----------
$userEmail = $_SESSION['user_email'];
$userType  = $_SESSION['user_type'] ?? '';
$userName  = '';
$phone     = '';
$courseId  = $_GET['course_id']  ?? '';
$streamId  = $_GET['stream_id']  ?? '';
$courseName = '';

// -- a) name & phone --
switch ($userType) {
    case 'student':
        $stmt = $connection->prepare(
            "SELECT CONCAT(first_name,' ',surname), phone_number
             FROM students WHERE email = ?"
        );
        $stmt->bind_param("s", $userEmail);
        $stmt->execute();
        $stmt->bind_result($userName, $phone);
        $stmt->fetch();
        $stmt->close();
        break;

    case 'teacher':
        $stmt = $connection->prepare(
            "SELECT full_name, phone_number
             FROM teachers WHERE email_address = ?"
        );
        $stmt->bind_param("s", $userEmail);
        $stmt->execute();
        $stmt->bind_result($userName, $phone);
        $stmt->fetch();
        $stmt->close();
        break;

    case 'admin':
        $stmt = $connection->prepare(
            "SELECT admin_name FROM admin WHERE admin_email = ?"
        );
        $stmt->bind_param("s", $userEmail);
        $stmt->execute();
        $stmt->bind_result($userName);
        $stmt->fetch();
        $stmt->close();
        break;

    default:
        die("Unknown user type");
}

// -- b) course name --
// -- b) course name + stream_id --
if ($courseId) {
    $stmt = $connection->prepare(
        "SELECT course_name, stream_id FROM courses WHERE course_id = ?"
    );
    $stmt->bind_param("i", $courseId);
    $stmt->execute();
    $stmt->bind_result($courseName, $streamId);
    $stmt->fetch();
    $stmt->close();
}

/* ----------- NEW: fetch price (kept separate to avoid changing old query) ----------- */
$price = 0.00;
if ($courseId) {
    $pstmt = $connection->prepare("SELECT price FROM courses WHERE course_id = ?");
    $pstmt->bind_param("i", $courseId);
    $pstmt->execute();
    $pstmt->bind_result($price);
    $pstmt->fetch();
    $pstmt->close();
}

?>

<?php include 'header.php'; ?>

<style>
    /* --- compact, neutral styling --- */
    .pay-wrapper {
        width: 80%;
        margin: 50px auto;
        background: #fff;
        border: 1px solid #ccc;
        border-radius: 8px;
        padding: 30px;
        font-family: 'Segoe UI', sans-serif;
    }

    .pay-wrapper h2 {
        margin-top: 0;
        text-align: center;
        font-size: 24px;
        border-bottom: 1px solid #e0e0e0;
        padding-bottom: 12px;
    }

    .info-row {
        margin: 10px 0;
        font-size: 15px;
    }

    .info-row span {
        display: inline-block;
        width: 150px;
        font-weight: 600;
    }

    .method-section {
        margin-top: 25px;
    }

    .method-section label {
        display: block;
        margin: 6px 0;
        cursor: pointer;
    }

    .method-fields {
        display: none;
        margin: 12px 0 20px 0;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
        background: #fafafa;
    }

    .method-fields input,
    .method-fields select {
        width: 100%;
        padding: 8px;
        margin-top: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 14px;
    }

    .btn-pay {
        display: block;
        width: 100%;
        padding: 13px;
        font-size: 16px;
        font-weight: 600;
        background: #1E3A8A;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .btn-pay:hover {
        background: #2b4db2;
    }

    /* show the block just after the checked radio */
    input[name="payment_method"]:checked+.method-fields {
        display: block;
    }
</style>

<!-- existing header / styles remain unchanged -->

<div class="pay-wrapper">
    <h2>Confirm Your Details</h2>

    <div class="info-row"><span>Name:</span>   <?= htmlspecialchars($userName) ?></div>
    <div class="info-row"><span>E-mail:</span> <?= htmlspecialchars($userEmail) ?></div>
    <?php if ($phone): ?>
        <div class="info-row"><span>Phone:</span> <?= htmlspecialchars($phone) ?></div>
    <?php endif; ?>
    <div class="info-row"><span>Stream ID:</span> <?= htmlspecialchars($streamId) ?></div>
    <div class="info-row"><span>Course:</span>    <?= htmlspecialchars($courseName) ?></div>
    <div class="info-row"><span>Price:</span>     ₹<?= number_format($price, 2) ?></div>

    <form action="process_payment.php" method="post" class="method-section">
        <!-- gateway / backend needs -->
        <input type="hidden" name="course_id" value="<?= htmlspecialchars($courseId) ?>">
        <input type="hidden" name="stream_id" value="<?= htmlspecialchars($streamId) ?>">
        <input type="hidden" name="amount"    value="<?= htmlspecialchars($price)    ?>">

        <!-- 1) Card -->
        <label>
            <input type="radio" name="payment_method" value="card" required>
            Credit / Debit Card
        </label>
        <div class="method-fields">
            <select name="card_type">
                <option value="">Card Type</option>
                <option value="VISA">Visa</option>
                <option value="MASTERCARD">Mastercard</option>
                <option value="RUPAY">RuPay</option>
                <option value="AMEX">American Express</option>
            </select>

            <input type="text"     name="card_number" placeholder="Card number (16 digits)">
            <input type="text"     name="card_expiry" placeholder="MM/YY">
            <input type="password" name="card_cvv"    placeholder="CVV">
            <input type="text"     name="card_name"   placeholder="Name on card">
        </div>

        <!-- 2) UPI -->
        <label>
            <input type="radio" name="payment_method" value="upi">
            UPI
        </label>
        <div class="method-fields">
            <select name="upi_app">
                <option value="">Select UPI App</option>
                <option value="GPay">Google Pay</option>
                <option value="PhonePe">PhonePe</option>
                <option value="Paytm">Paytm</option>
                <option value="BHIM">BHIM</option>
            </select>
            <input type="text" name="upi_id" placeholder="yourname@bank">
            <small>You will be redirected to your chosen UPI app.</small>
        </div>

        <!-- 3) Net-banking -->
        <label>
            <input type="radio" name="payment_method" value="netbanking">
            Net Banking
        </label>
        <div class="method-fields">
            <select name="bank_code">
                <option value="">Select your bank</option>
                <option value="HDFC">HDFC Bank</option>
                <option value="ICICI">ICICI Bank</option>
                <option value="SBI">State Bank of India</option>
                <option value="AXIS">Axis Bank</option>
                <!-- add more as needed -->
            </select>
            <small>You will be redirected to the bank’s secure page.</small>
        </div>

        <button type="submit" class="btn-pay">Pay ₹<?= number_format($price, 2) ?></button>
    </form>

    <!-- Supplementary Information -->
    <div style="margin-top:30px; font-size:14px; line-height:1.7;">
        <h3 style="text-align:center; font-size:20px; border-bottom:1px solid #e0e0e0; padding-bottom:10px;">
            Payment Information & Policies
        </h3>

        <p><strong>Secure Checkout:</strong> All transactions are encrypted and processed through PCI-DSS-compliant gateways. We never store your payment credentials.</p>

        <p><strong>Order Summary:</strong><br>
           Course: <b><?= htmlspecialchars($courseName) ?></b><br>
           Stream ID: <b><?= htmlspecialchars($streamId) ?></b><br>
           Total Payable: <b>₹<?= number_format($price, 2) ?></b> (taxes included where applicable)</p>

        <p><strong>Refund / Cancellation:</strong> Payments are strictly non-refundable and non-transferable. Verify your selections before proceeding.</p>

        <p><strong>Invoice & Receipt:</strong> A PDF receipt is generated instantly after a successful payment and emailed to you. You can also download it anytime using the link below.</p>

        <p><strong>Need Help?</strong> Write to <a href="mailto:support@example.com">support@example.com</a> or call +91-XXXXXXXXXX (Mon–Sat, 10 AM – 6 PM IST).</p>
    </div>

    <!-- Download receipt button (appears once payment is successful) -->
    <?php if (isset($_GET['paid']) && $_GET['paid'] === '1'): ?>
        <a class="btn-download"
           href="download_receipt.php?course_id=<?= urlencode($courseId) ?>&stream_id=<?= urlencode($streamId) ?>"
           style="display:block; margin:30px auto 0; width:220px; text-align:center;
                  background:#198754; color:#fff; padding:10px 0; border-radius:5px; text-decoration:none;">
           Download Receipt (PDF)
        </a>
    <?php endif; ?>

</div>

<?php include 'footer.php'; ?>
