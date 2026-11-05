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
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 0, 'pending')
    ");

    $stmt->bind_param(
      "ississss",
      $recipient_id,
      $_POST['patient_name'],
      $_POST['blood_group'],
      $_POST['units'],
      $_POST['hospital'],
      $_POST['location'],
      $_POST['phone'],
      $_POST['urgency']
    );

    $stmt->execute();
    header("Location: dashboard.php?request=submitted");
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Create Blood Request</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-10">

<form method="POST" class="max-w-xl mx-auto bg-white p-6 rounded-xl shadow">
  <h2 class="text-xl font-bold text-blue-600 mb-4">🩸 Create Blood Request</h2>

  <input name="patient_name" placeholder="Patient Name" required class="w-full mb-3 p-2 border rounded">
  <input name="blood_group" placeholder="Blood Group (e.g., A+)" required class="w-full mb-3 p-2 border rounded">
  <input name="units" type="number" placeholder="Units Required" required class="w-full mb-3 p-2 border rounded">
  <input name="hospital" placeholder="Hospital Name" required class="w-full mb-3 p-2 border rounded">
  <input name="location" placeholder="Hospital Location" required class="w-full mb-3 p-2 border rounded">
  <input name="phone" placeholder="Contact Phone" required class="w-full mb-3 p-2 border rounded">

  <label class="block text-sm text-gray-600 mb-2">Urgency Level</label>
  <select name="urgency" required class="w-full mb-3 p-2 border rounded">
    <option value="low">Low</option>
    <option value="medium">Medium</option>
    <option value="high">High</option>
  </select>

  <button class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
    Submit Request
  </button>

  <p class="text-sm text-gray-500 mt-4">
    Your request will be reviewed and approved by an admin before it becomes active.
  </p>
</form>

</body>
</html></content>
<parameter name="filePath">c:\xamp\htdocs\weblab\send\src\receipent\create-blood-request.php