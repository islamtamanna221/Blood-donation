<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['admin_id'])) {
    exit("Unauthorized");
}

$id = (int)$_GET['id'];

$conn->query("
  UPDATE blood_requests
  SET admin_approved = 1, status = 'approved'
  WHERE id = $id
");

header("Location: dashboard.php");
