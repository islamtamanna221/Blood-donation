<?php
session_start();
include "../../config/db.php";

/* ===== AUTH ===== */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor') {
    header("Location: login.php");
    exit;
}

$uid = (int)$_SESSION['user_id'];

/* ===== GET DONOR ID ===== */
$donor_id = $conn->query("
    SELECT id FROM donors WHERE user_id = $uid
")->fetch_assoc()['id'];

/* ===== FETCH ALL APPROVED REQUESTS FOR THIS DONOR ===== */
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
      AND br.admin_approved = 1
      AND dr.status = 'sent'
    ORDER BY br.created_at DESC
");
?>
<!DOCTYPE html>
<html>
<head>
<title>All Blood Requests</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen">

<!-- NAVBAR -->
<nav class="bg-white shadow px-6 py-4 flex justify-between">
  <h1 class="text-xl font-bold text-indigo-600">🩸 All Blood Requests</h1>
  <a href="dashboard.php" class="text-blue-600 font-bold">← Back</a>
</nav>

<div class="max-w-6xl mx-auto p-6 space-y-5">

<?php if ($requests->num_rows === 0): ?>
  <div class="bg-white p-6 rounded-xl text-center text-gray-500 shadow">
    No blood requests found.
  </div>
<?php endif; ?>

<?php while ($r = $requests->fetch_assoc()): ?>
  <div class="bg-white p-6 rounded-2xl shadow border border-gray-200">

    <!-- HEADER -->
    <div class="flex justify-between items-start mb-3">
      <div>
        <h3 class="text-lg font-bold text-gray-800">
          Patient: <?= htmlspecialchars($r['patient_name']) ?>
        </h3>
        <p class="text-sm text-gray-600">
          Requested by: <?= htmlspecialchars($r['recipient_name']) ?>
        </p>
      </div>

      <span class="px-3 py-1 rounded-full text-sm font-semibold
        <?=
          $r['urgency_level'] === 'critical' ? 'bg-red-100 text-red-700' :
          ($r['urgency_level'] === 'high' ? 'bg-orange-100 text-orange-700' :
          ($r['urgency_level'] === 'medium' ? 'bg-yellow-100 text-yellow-700' :
          'bg-green-100 text-green-700'))
        ?>">
        <?= strtoupper($r['urgency_level']) ?>
      </span>
    </div>

    <!-- DETAILS GRID -->
    <div class="grid md:grid-cols-2 gap-3 text-sm text-gray-700">
      <div>🩸 <b>Blood Group:</b> <?= $r['blood_group'] ?></div>
      <div>🩸 <b>Units Required:</b> <?= $r['units_required'] ?></div>
      <div>🏥 <b>Hospital:</b> <?= htmlspecialchars($r['hospital_name']) ?></div>
      <div>📍 <b>Hospital Location:</b> <?= htmlspecialchars($r['hospital_location']) ?></div>
      <div>📞 <b>Contact Phone:</b> <?= htmlspecialchars($r['contact_phone']) ?></div>
      <div>📌 <b>Status:</b> <?= strtoupper($r['status']) ?></div>
    </div>

    <!-- FOOTER -->
    <div class="mt-4 flex justify-between items-center">
      <p class="text-xs text-gray-500">
        Requested on: <?= date('M d, Y h:i A', strtotime($r['created_at'])) ?>
      </p>

      <a href="see-request.php?request_id=<?= $r['id'] ?>"
         class="px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold">
        👁️ View Full Details
      </a>
    </div>

  </div>
<?php endwhile; ?>

</div>

</body>
</html>
