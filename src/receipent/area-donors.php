<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'recipient') {
    header("Location: login.php");
    exit;
}

$recipient_id = (int)$_SESSION['recipient_id'];

/* recipient area */
$rq = $conn->query("
    SELECT area_id, area_name
    FROM recipients r
    JOIN users u ON r.user_id = u.id
    JOIN blood_requests br ON br.recipient_id = r.id
    WHERE r.id = $recipient_id
    LIMIT 1
");
$recipient = $rq->fetch_assoc();

$area_id = (int)$recipient['area_id'];
$area_name = $recipient['area_name'];

/*
  Verified donors
  Same area
  Accepted donation_requests
*/
$donors = $conn->query("
    SELECT DISTINCT
        d.id AS donor_id,
        u.full_name,
        u.email,
        bg.group_name AS blood_group,
        d.total_donations,
        d.last_donation_date
    FROM donation_request_actions dra
    JOIN donors d ON dra.donor_id = d.id
    JOIN users u ON d.user_id = u.id
    JOIN donor_verifications dv ON dv.donor_id = d.id
    JOIN blood_groups bg ON d.blood_group_id = bg.id
    JOIN blood_requests br ON dra.patient_id = br.id
    WHERE dra.action = 'accepted'
      AND dv.status = 'approved'
      AND d.area_id = $area_id
      AND br.recipient_id = $recipient_id
    ORDER BY d.total_donations DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Area Donors</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen">

<!-- HEADER -->
<nav class="bg-white shadow px-6 py-4 flex justify-between items-center">
  <h1 class="text-xl font-bold text-purple-700">
    📍 Area Donors (<?= htmlspecialchars($area_name) ?>)
  </h1>

  <a href="dashboard.php"
     class="text-sm text-blue-600 font-bold">
    ← Back to Dashboard
  </a>
</nav>

<!-- CONTENT -->
<div class="max-w-6xl mx-auto p-6 space-y-4">

<?php if ($donors->num_rows === 0): ?>
  <div class="bg-white p-6 rounded-xl shadow text-center text-gray-500">
    No verified donors from your area have accepted yet.
  </div>
<?php endif; ?>

<?php while ($d = $donors->fetch_assoc()): ?>

  <div class="bg-white border border-purple-200 rounded-2xl p-6 shadow-sm">
    <div class="flex justify-between items-start">
      <div>
        <h3 class="text-lg font-bold text-gray-800">
          <?= htmlspecialchars($d['full_name']) ?>
        </h3>

        <p class="text-sm text-gray-600">
          🩸 Blood Group: <?= htmlspecialchars($d['blood_group']) ?>
        </p>

        <p class="text-sm text-gray-600">
          📧 <?= htmlspecialchars($d['email']) ?>
        </p>

        <p class="text-xs text-gray-500 mt-1">
          Total Donations: <?= (int)$d['total_donations'] ?>
        </p>

        <p class="text-xs text-gray-500">
          Last Donation: <?= $d['last_donation_date'] ?: 'Not yet donated' ?>
        </p>
      </div>

      <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-bold rounded">
        ACCEPTED
      </span>
    </div>
  </div>

<?php endwhile; ?>

</div>

</body>
</html>
