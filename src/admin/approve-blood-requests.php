<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$blood_request_id = (int)$_GET['id'];

/* 1. Approve blood request */
$conn->query("
    UPDATE blood_requests
    SET admin_approved = 1,
        status = 'approved'
    WHERE id = $blood_request_id
");

/* 2. Find active donor (same blood group) */
$donor_q = $conn->query("
    SELECT d.id
    FROM donors d
    JOIN blood_requests br ON br.blood_group = d.blood_group
    WHERE br.id = $blood_request_id
      AND d.is_active = 1
    LIMIT 1
");

if ($donor_q->num_rows === 0) {
    die("No active donor found");
}

$donor_id = $donor_q->fetch_assoc()['id'];

/* 3. Create / Update donation_requests */
$check = $conn->query("
    SELECT id FROM donation_requests
    WHERE blood_request_id = $blood_request_id
");

if ($check->num_rows === 0) {
    $conn->query("
        INSERT INTO donation_requests (blood_request_id, donor_id, status)
        VALUES ($blood_request_id, $donor_id, 'sent')
    ");
} else {
    $conn->query("
        UPDATE donation_requests
        SET donor_id = $donor_id,
            status = 'sent'
        WHERE blood_request_id = $blood_request_id
    ");
}

/* 4. Notify recipient */
$conn->query("
    INSERT INTO notifications (user_id, title, message)
    SELECT u.id,
           'Blood Request Approved',
           'Your blood request has been approved and sent to a donor.'
    FROM blood_requests br
    JOIN recipients r ON br.recipient_id = r.id
    JOIN users u ON r.user_id = u.id
    WHERE br.id = $blood_request_id
");

header("Location: blood-requests.php?approved=1");
exit;
