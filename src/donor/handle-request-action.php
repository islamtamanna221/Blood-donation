<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor') {
    header("Location: login.php");
    exit;
}

$donor_id = (int)$_POST['donor_id'];
$blood_request_id = (int)$_POST['blood_request_id']; // patient case
$patient_id = $blood_request_id; // ✅ patient_id
$action = $_POST['action']; // accepted | rejected

if (!in_array($action, ['accepted', 'rejected'])) {
    die("Invalid action");
}

/* 1️⃣ donation_requests এ insert */
$stmt1 = $conn->prepare("
    INSERT INTO donation_requests
    (blood_request_id, donor_id, status)
    VALUES (?, ?, ?)
");
$stmt1->bind_param("iis", $blood_request_id, $donor_id, $action);
$stmt1->execute();

/* last donation_request_id */
$donation_request_id = $conn->insert_id;

/* 2️⃣ Accept / Reject history WITH patient_id */
$stmt2 = $conn->prepare("
    INSERT INTO donation_request_actions
    (donation_request_id, donor_id, patient_id, action)
    VALUES (?, ?, ?, ?)
");
$stmt2->bind_param(
    "iiis",
    $donation_request_id,
    $donor_id,
    $patient_id,
    $action
);
$stmt2->execute();

/* 3️⃣ blood_requests status update */
if ($action === 'accepted') {
    $stmt3 = $conn->prepare("
        UPDATE blood_requests
        SET status = 'matched'
        WHERE id = ?
    ");
    $stmt3->bind_param("i", $blood_request_id);
    $stmt3->execute();
}

header("Location: recipients.php?status=$action");
exit;
