<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor') {
    header("Location: login.php");
    exit;
}

$uid = (int)$_SESSION['user_id'];

$donor_id = $conn->query("
    SELECT id FROM donors WHERE user_id = $uid
")->fetch_assoc()['id'];

$requests = $conn->query("
    SELECT
        br.id,
        br.patient_name,
        br.blood_group,
        br.units_required,
        br.hospital_name,
        br.hospital_location,
        br.contact_phone,
        br.urgency_level,
        br.status,
        br.created_at,
        u.full_name AS recipient_name
    FROM donation_requests dr
    JOIN blood_requests br ON dr.blood_request_id = br.id
    JOIN recipients r ON br.recipient_id = r.id
    JOIN users u ON r.user_id = u.id
    WHERE dr.donor_id = $donor_id
      AND dr.status = 'sent'
      AND br.admin_approved = 1
    ORDER BY br.created_at DESC
");
?>
<!DOCTYPE html>
<html>
<head>
<title>All Blood Requests</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<nav class="bg-white shadow px-6 py-4 flex justify-between">
  <h1 class="text-xl font-bold text-indigo-600">🩸 All Blood Requests</h1>
  <a href="dashboard.php" class="text-blue-600 font-bold">← Back</a>
</nav>

<div class="max-w-6xl mx-auto p-6 space-y-4">

<?php if ($requests->num_rows === 0): ?>
  <div class="bg-white p-6 rounded-xl text-center text-gray-500">
    No blood requests found.
  </div>
<?php endif; ?>

<?php while ($r = $requests->fetch_assoc()): ?>
  <div class="bg-white p-6 rounded-xl shadow">
    <h3 class="font-bold">Patient: <?= htmlspecialchars($r['patient_name']) ?></h3>
    <p class="text-sm text-gray-600">
      Requested by: <?= htmlspecialchars($r['recipient_name']) ?>
    </p>

    <p class="text-sm mt-2">
      🩸 <?= $r['blood_group'] ?> |
      <?= $r['units_required'] ?> units |
      <?= strtoupper($r['urgency_level']) ?>
    </p>

    <div class="mt-4">
      <a href="see-request.php?request_id=<?= $r['id'] ?>"
         class="px-4 py-2 bg-indigo-600 text-white rounded-lg">
        👁️ View Details
      </a>
    </div>
  </div>
<?php endwhile; ?>

</div>
</body>
</html>
