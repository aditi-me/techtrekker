<?php
session_start();
if (isset($_SESSION['registration_success'])) {
    echo "✅ Alert Should Show: " . $_SESSION['registration_success'];
    unset($_SESSION['registration_success']);
} else {
    echo "❌ No session set.";
}
