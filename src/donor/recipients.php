<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor') {
    header("Location: login.php");
    exit;
}

$uid = (int)$_SESSION['user_id'];

/* donor id বের করি */
$dq = $conn->query("SELECT id FROM donors WHERE user_id = $uid");
$donor = $dq->fetch_assoc();
$donor_id = (int)$donor['id'];

/* approved blood requests */
$requests = $conn->query("
    SELECT
        br.id AS request_id,
        br.patient_name,
        br.units_required,
        br.hospital_name,
        br.hospital_location,
        br.area_name,
        br.contact_phone,
        br.urgency_level,
        br.created_at,
        bg.group_name AS blood_group,
        u.full_name AS recipient_name
    FROM blood_requests br
    JOIN recipients r ON br.recipient_id = r.id
    JOIN users u ON r.user_id = u.id
    JOIN blood_groups bg ON br.blood_group_id = bg.id
    WHERE br.status = 'approved'
      AND br.admin_approved = 1
    ORDER BY br.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Approved Blood Requests</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<script src="https://cdn.tailwindcss.com"></script>

<style>
  /* soft animated background */
  .bg-pattern {
    background:
      radial-gradient(circle at 10% 20%, rgba(244,63,94,.12), transparent 40%),
      radial-gradient(circle at 90% 80%, rgba(14,165,233,.10), transparent 40%);
  }
  .card-float {
    transition: transform .25s ease, box-shadow .25s ease;
  }
  .card-float:hover {
    transform: translateY(-6px) scale(1.01);
  }
</style>
</head>

<body class="bg-pattern min-h-screen">

<!-- NAVBAR -->
<nav class="bg-white/90 backdrop-blur shadow sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
    <h1 class="text-xl md:text-2xl font-extrabold text-rose-600">
      🩸 Approved Blood Requests
    </h1>
    <a href="dashboard.php"
       class="text-sm font-bold text-blue-600 hover:text-blue-700">
      ← Back
    </a>
  </div>
</nav>

<!-- MAIN -->
<main class="max-w-7xl mx-auto px-6 py-10 space-y-8">

<?php if ($requests->num_rows === 0): ?>
  <div class="bg-white rounded-3xl p-10 text-center text-gray-500 shadow">
    😔 No approved requests available right now
  </div>
<?php endif; ?>

<?php while ($r = $requests->fetch_assoc()): ?>

<?php
  $urgencyColor = match($r['urgency_level']) {
    'high'   => 'bg-rose-100 text-rose-700',
    'medium' => 'bg-amber-100 text-amber-700',
    'low'    => 'bg-emerald-100 text-emerald-700',
    default  => 'bg-slate-100 text-slate-700'
  };
?>

<div class="bg-white/90 backdrop-blur border border-rose-100
            rounded-3xl p-6 shadow card-float">

  <!-- TOP -->
  <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4">

    <div>
      <h3 class="text-xl font-bold text-gray-800">
        Patient: <?= htmlspecialchars($r['patient_name']) ?>
      </h3>
      <p class="text-sm text-gray-500">
        Recipient: <?= htmlspecialchars($r['recipient_name']) ?>
      </p>
    </div>

    <span class="px-3 py-1 rounded-full text-xs font-bold <?= $urgencyColor ?>">
      <?= strtoupper($r['urgency_level']) ?> URGENCY
    </span>

  </div>

  <!-- TAGS -->
  <div class="flex flex-wrap gap-2 mt-4">
    <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700">
      🩸 <?= htmlspecialchars($r['blood_group']) ?>
    </span>
    <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
      <?= (int)$r['units_required'] ?> Units
    </span>
  </div>

  <!-- INFO -->
  <div class="mt-4 text-sm text-gray-600 space-y-1">
    <p>🏥 <?= htmlspecialchars($r['hospital_name']) ?></p>
    <p>
      📍 <?= htmlspecialchars($r['hospital_location']) ?>
      <span class="text-gray-400">
        (<?= htmlspecialchars($r['area_name']) ?>)
      </span>
    </p>
    <p>📞 <?= htmlspecialchars($r['contact_phone']) ?></p>
  </div>

  <!-- ACTION -->
  <form method="POST" action="handle-request-action.php"
        class="flex flex-col sm:flex-row gap-4 mt-6">

    <input type="hidden" name="blood_request_id" value="<?= $r['request_id'] ?>">
    <input type="hidden" name="donor_id" value="<?= $donor_id ?>">

    <button name="action" value="accepted"
            class="flex-1 inline-flex items-center justify-center gap-2
                   px-6 py-3 rounded-2xl font-bold
                   bg-emerald-600 text-white
                   hover:bg-emerald-700 hover:scale-[1.02]
                   transition shadow">
      ✅ Accept Request
    </button>

    <button name="action" value="rejected"
            class="flex-1 inline-flex items-center justify-center gap-2
                   px-6 py-3 rounded-2xl font-bold
                   bg-rose-600 text-white
                   hover:bg-rose-700 hover:scale-[1.02]
                   transition shadow">
      ❌ Reject Request
    </button>

  </form>

</div>

<?php endwhile; ?>

</main>

</body>
</html>
