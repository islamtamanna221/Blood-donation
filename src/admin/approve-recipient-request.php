<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$request_id = $_GET['id'];

$conn->query("
    UPDATE blood_requests
    SET admin_approved = 1,
        status = 'approved'
    WHERE id = $request_id
");

header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
