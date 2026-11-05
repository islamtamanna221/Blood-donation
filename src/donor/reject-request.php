<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor') {
    header("Location: login.php");
    exit;
}

$donation_request_id = (int)$_GET['id'];

// Update donation request status
$conn->query("
    UPDATE donation_requests
    SET status = 'rejected'
    WHERE id = $donation_request_id
");

header("Location: dashboard.php?rejected=1");
exit;
?>