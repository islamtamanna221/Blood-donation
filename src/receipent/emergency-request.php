<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'recipient') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient_id = $_SESSION['recipient_id'];

    $stmt = $conn->prepare("
      INSERT INTO blood_requests
      (recipient_id, patient_name, blood_group, units_required,
       hospital_name, hospital_location, contact_phone,
       urgency_level, is_emergency, admin_approved, status)
      VALUES (?, ?, ?, ?, ?, ?, ?, 'critical', 1, 0, 'pending')
    ");

    $stmt->bind_param(
      "ississs",
      $recipient_id,
      $_POST['patient_name'],
      $_POST['blood_group'],
      $_POST['units'],
      $_POST['hospital'],
      $_POST['location'],
      $_POST['phone']
    );

    $stmt->execute();
    header("Location: dashboard.php?emergency=sent");
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Emergency Request</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-10">

<form method="POST" enctype="multipart/form-data" class="max-w-xl mx-auto bg-white p-6 rounded-xl shadow">
  <h2 class="text-xl font-bold text-red-600 mb-4">🚨 Emergency Blood Request</h2>

  <div class="mb-4">
    <h3 class="text-lg font-semibold text-gray-800 mb-2">Patient Information</h3>
    <input name="patient_name" placeholder="Patient Name" required class="w-full mb-3 p-2 border rounded">
    <input name="blood_group" placeholder="Blood Group (e.g., A+, O-, etc.)" required class="w-full mb-3 p-2 border rounded">
    <input name="units" type="number" placeholder="Units Required" required class="w-full mb-3 p-2 border rounded">
  </div>

  <div class="mb-4">
    <h3 class="text-lg font-semibold text-gray-800 mb-2">Hospital Information</h3>
    <input name="hospital" placeholder="Hospital Name" required class="w-full mb-3 p-2 border rounded">
    <input name="location" placeholder="Hospital Location" required class="w-full mb-3 p-2 border rounded">
    <input name="phone" placeholder="Contact Phone" required class="w-full mb-3 p-2 border rounded">
  </div>

  <button class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700">
    Submit Emergency Request
  </button>
</form>

</body>
</html>
