<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$recipients = $conn->query("
    SELECT
        r.id AS recipient_id,
        u.full_name,
        u.email,
        r.location,
        r.total_requests,
        r.active_requests,
        r.completed_requests
    FROM recipients r
    JOIN users u ON r.user_id = u.id
    ORDER BY r.id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Recipients</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<script src="https://cdn.tailwindcss.com"></script>

<style>
.skeleton {
  background: linear-gradient(
    90deg,
    #fce7f3 25%,
    #fff1f2 37%,
    #fce7f3 63%
  );
  background-size: 400% 100%;
  animation: shimmer 1.4s ease infinite;
}
@keyframes shimmer {
  0% { background-position: 100% 0 }
  100% { background-position: -100% 0 }
}
</style>
</head>

<body class="bg-gradient-to-br from-rose-50 via-pink-50 to-slate-100 min-h-screen">

<!-- HEADER -->
<header class="bg-white/90 backdrop-blur border-b border-rose-100 sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
    <h1 class="text-2xl font-extrabold text-rose-700 flex items-center gap-2">
      👥 Recipients
    </h1>

    <a href="dashboard.php"
       class="text-sm font-semibold text-rose-600 hover:text-rose-700">
      ← Back to Dashboard
    </a>
  </div>
</header>

<main class="max-w-7xl mx-auto px-6 py-10">

<!-- GRID -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">

<?php if ($recipients->num_rows === 0): ?>
  <div class="col-span-full bg-white p-10 rounded-3xl text-center text-rose-400 shadow">
    😔 No recipients found
  </div>
<?php endif; ?>

<?php while ($r = $recipients->fetch_assoc()): ?>

<div class="bg-white/95 backdrop-blur border border-rose-100 rounded-3xl p-6 shadow
            hover:shadow-lg hover:-translate-y-1 transition-all duration-300">

  <!-- TOP -->
  <div class="flex items-center gap-4 mb-4">
    <div class="w-12 h-12 rounded-full bg-rose-100 flex items-center justify-center text-xl">
      🏥
    </div>
    <div>
      <h3 class="font-bold text-rose-800 text-lg">
        <?= htmlspecialchars($r['full_name']) ?>
      </h3>
      <p class="text-sm text-gray-500">
        <?= htmlspecialchars($r['email']) ?>
      </p>
    </div>
  </div>

  <!-- INFO -->
  <div class="text-sm text-gray-600 space-y-1 mb-5">
    <p>📍 <?= htmlspecialchars($r['location']) ?></p>
    <p>📊 Total Requests: <strong><?= $r['total_requests'] ?></strong></p>
    <p class="text-emerald-600">
      ✅ Completed: <?= $r['completed_requests'] ?>
    </p>
    <p class="text-amber-600">
      ⏳ Active: <?= $r['active_requests'] ?>
    </p>
  </div>

  <!-- ACTION -->
  <a href="recipient-requests.php?id=<?= $r['recipient_id'] ?>"
     class="block text-center bg-rose-500/90 text-white py-2.5 rounded-2xl
            font-bold hover:bg-rose-600 transition">
    📋 View Requests
  </a>

</div>

<?php endwhile; ?>

</div>

<!-- SKELETON FEEL -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 mt-10 opacity-30">
  <div class="h-48 rounded-3xl skeleton"></div>
  <div class="h-48 rounded-3xl skeleton"></div>
  <div class="h-48 rounded-3xl skeleton"></div>
</div>

</main>

</body>
</html>
