<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor') {
    header("Location: login.php");
    exit;
}

$request_id = (int)$_GET['id'];
$donor_user_id = $_SESSION['user_id'];

// Get donor_id
$donor_query = $conn->query("SELECT id FROM donors WHERE user_id = $donor_user_id");
$donor = $donor_query->fetch_assoc();
$donor_id = $donor['id'];

// Check if this emergency request exists and is approved
$check_query = $conn->query("
    SELECT br.id, br.blood_group, d.blood_group as donor_blood_group
    FROM blood_requests br
    JOIN donors d ON d.user_id = $donor_user_id
    WHERE br.id = $request_id 
      AND br.is_emergency = 1 
      AND br.admin_approved = 1 
      AND br.status = 'approved'
");

if ($check_query->num_rows === 0) {
    header("Location: dashboard.php?error=invalid_request");
    exit;
}

$request = $check_query->fetch_assoc();

// Check if donor's blood group matches
if ($request['blood_group'] !== $request['donor_blood_group']) {
    header("Location: dashboard.php?error=blood_group_mismatch");
    exit;
}

// Check if donor already accepted this request
$existing = $conn->query("
    SELECT id FROM donation_requests 
    WHERE blood_request_id = $request_id AND donor_id = $donor_id
");

if ($existing->num_rows > 0) {
    header("Location: dashboard.php?error=already_accepted");
    exit;
}

// Insert acceptance
$stmt = $conn->prepare("
    INSERT INTO donation_requests 
    (blood_request_id, donor_id, status, accepted_at) 
    VALUES (?, ?, 'accepted', NOW())
");

$stmt->bind_param("ii", $request_id, $donor_id);
$stmt->execute();

// Update blood request status to matched
$conn->query("UPDATE blood_requests SET status = 'matched' WHERE id = $request_id");

// Create notification for recipient
$recipient_query = $conn->query("
    SELECT u.id as user_id 
    FROM blood_requests br 
    JOIN recipients r ON br.recipient_id = r.id 
    JOIN users u ON r.user_id = u.id 
    WHERE br.id = $request_id
");
$recipient = $recipient_query->fetch_assoc();

$conn->query("
    INSERT INTO notifications (user_id, title, message) 
    VALUES ({$recipient['user_id']}, 'Emergency Request Accepted', 'A donor has accepted your emergency blood request!')
");

header("Location: dashboard.php?success=emergency_accepted");
exit;
?>