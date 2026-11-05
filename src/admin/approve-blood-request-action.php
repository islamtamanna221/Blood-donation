<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$id = (int)$_GET['id'];

/* Update status */
$conn->query("
  UPDATE blood_requests
  SET admin_approved = 1, status = 'approved'
  WHERE id = $id
");

/* (Optional) notification */
$conn->query("
  INSERT INTO notifications (user_id, title, message)
  SELECT u.id,
         'Blood Request Approved',
         'Your blood request has been approved by admin'
  FROM blood_requests br
  JOIN recipients r ON br.recipient_id = r.id
  JOIN users u ON r.user_id = u.id
  WHERE br.id = $id
");

header("Location: approve-blood-requests.php");
