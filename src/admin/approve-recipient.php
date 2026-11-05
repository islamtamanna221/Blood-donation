<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "../../config/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$recipient_id = (int) $_GET['id'];

/* users approve */
$conn->query("
    UPDATE users u
    JOIN recipients r ON u.id = r.user_id
    SET u.status = 'approved'
    WHERE r.id = $recipient_id
");

/* recipient verification approve */
$conn->query("
    UPDATE recipient_medical_verifications
    SET status = 'approved',
        verified_by = {$_SESSION['admin_id']},
        verified_at = NOW()
    WHERE recipient_id = $recipient_id
");

header("Location: dashboard.php");
exit;
