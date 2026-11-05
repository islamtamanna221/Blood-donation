<?php
include "../../config/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$donor_id = (int) $_GET['id'];

// donors
$conn->query("UPDATE donors SET is_active=1 WHERE id=$donor_id");

// users
$conn->query("
    UPDATE users u
    JOIN donors d ON u.id=d.user_id
    SET u.status='approved'
    WHERE d.id=$donor_id
");

// donor_verifications
$conn->query("
    UPDATE donor_verifications
    SET status='approved',
        verified_by={$_SESSION['admin_id']},
        verified_at=NOW()
    WHERE donor_id=$donor_id
");

header("Location: dashboard.php");
exit;
