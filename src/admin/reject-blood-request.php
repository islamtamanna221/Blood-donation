<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$id = (int)$_GET['id'];

/* Update blood request status */
$conn->query("
  UPDATE blood_requests
  SET status = 'rejected'
  WHERE id = $id
");

/* Update donation request status to 'rejected' */
$conn->query("
  UPDATE donation_requests
  SET status = 'rejected'
  WHERE blood_request_id = $id
");

/* Add notification for recipient */
$conn->query("
  INSERT INTO notifications (user_id, title, message)
  SELECT u.id,
         'Blood Request Rejected',
         'Your blood request has been rejected by admin'
  FROM blood_requests br
  JOIN recipients r ON br.recipient_id = r.id
  JOIN users u ON r.user_id = u.id
  WHERE br.id = $id
");

header("Location: blood-requests.php?rejected=1");
exit;
?>