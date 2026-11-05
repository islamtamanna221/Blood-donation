<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor') {
    die("Unauthorized");
}

$donation_request_id = (int)$_GET['id'];

/* Accept donation request */
$conn->query("
    UPDATE donation_requests
    SET status = 'accepted', accepted_at = NOW()
    WHERE id = $donation_request_id
");

/* Get recipient user id */
$r = $conn->query("
    SELECT u.id AS user_id
    FROM donation_requests dr
    JOIN blood_requests br ON dr.blood_request_id = br.id
    JOIN recipients r ON br.recipient_id = r.id
    JOIN users u ON r.user_id = u.id
    WHERE dr.id = $donation_request_id
")->fetch_assoc();

/* Notify recipient */
$conn->query("
    INSERT INTO notifications (user_id, title, message)
    VALUES (
        {$r['user_id']},
        'Donor Accepted Request',
        'A donor has accepted your blood request. You can now view the details.'
    )
");

header("Location: dashboard.php?accepted=1");
exit;
