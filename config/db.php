<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "donation";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
