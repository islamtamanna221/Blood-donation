<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$recipient_id = $_GET['id'];

$requests = $conn->query("
    SELECT
        br.id,
        br.patient_name,
        bg.group_name AS blood_group,
        br.units_required,
        br.hospital_name,
        br.area_name,
        br.urgency_level,
        br.created_at
    FROM blood_requests br
    LEFT JOIN blood_groups bg ON br.blood_group_id = bg.id
    WHERE br.recipient_id = $recipient_id
      AND br.admin_approved = 0
    ORDER BY br.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Recipient Blood Requests</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-slate-100 to-slate-200 min-h-screen">

<!-- HEADER -->
<header class="bg-white/80 backdrop-blur border-b sticky top-0 z-50">
  <div class="max-w-6xl mx-auto px-6 py-4 flex justify-between items-center">
    <h1 class="text-xl font-extrabold text-gray-800 flex items-center gap-2">
      🩸 Recipient Blood Requests
    </h1>

    <a href="dashboard.php"
       class="text-sm font-semibold text-gray-600 hover:text-red-600">
      ← Back to Dashboard
    </a>
  </div>
</header>

<main class="max-w-6xl mx-auto px-6 py-10">

<!-- EMPTY STATE -->
<?php if ($requests->num_rows === 0): ?>
  <div class="bg-white rounded-2xl p-10 text-center shadow">
    <p class="text-gray-500 text-lg">
      😔 No pending blood requests
    </p>
  </div>
<?php endif; ?>

<!-- REQUEST CARDS -->
<div class="space-y-6">
<?php while ($r = $requests->fetch_assoc()): ?>

<?php
  $urgencyColor = match($r['urgency_level']) {
    'high'   => 'bg-red-100 text-red-700',
    'medium' => 'bg-yellow-100 text-yellow-700',
    'low'    => 'bg-green-100 text-green-700',
    default  => 'bg-slate-100 text-slate-700'
  };
?>

<div class="bg-white/90 backdrop-blur border rounded-3xl p-6 shadow hover:shadow-lg transition">
  <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-6">

    <!-- LEFT INFO -->
    <div>
      <h3 class="text-lg font-bold text-gray-800">
        Patient: <?= htmlspecialchars($r['patient_name']) ?>
      </h3>

      <div class="flex flex-wrap gap-2 mt-3">
        <span class="px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">
          🩸 <?= $r['blood_group'] ?>
        </span>

        <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
          Units: <?= $r['units_required'] ?>
        </span>

        <span class="px-3 py-1 rounded-full text-xs font-bold <?= $urgencyColor ?>">
          <?= strtoupper($r['urgency_level']) ?> URGENCY
        </span>
      </div>

      <p class="mt-3 text-sm text-gray-600">
        🏥 <?= htmlspecialchars($r['hospital_name']) ?>
        <span class="text-gray-400">
          (<?= htmlspecialchars($r['area_name']) ?>)
        </span>
      </p>

      <p class="text-xs text-gray-400 mt-1">
        Requested on <?= $r['created_at'] ?>
      </p>
    </div>

    <!-- ACTION -->
    <div class="text-right">
      <a href="approve-recipient-request.php?id=<?= $r['id'] ?>"
         class="inline-flex items-center gap-2
                bg-emerald-600 text-white px-6 py-2.5 rounded-2xl
                text-sm font-bold hover:bg-emerald-700 hover:scale-105
                transition shadow">
        ✅ Approve Request
      </a>
    </div>

  </div>
</div>

<?php endwhile; ?>
</div>

</main>

</body>
</html>
