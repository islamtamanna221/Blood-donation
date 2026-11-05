<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

/* Blood requests pending approval */
$blood_requests = $conn->query("
    SELECT
        br.id,
        br.patient_name,
        br.blood_group,
        br.units_required,
        br.hospital_name,
        br.hospital_location,
        br.contact_phone,
        br.urgency_level,
        br.created_at,
        br.evidence_document,
        u.full_name AS recipient_name,
        d_u.full_name AS donor_name
    FROM blood_requests br
    JOIN recipients r ON br.recipient_id = r.id
    JOIN users u ON r.user_id = u.id
    JOIN donation_requests dr ON dr.blood_request_id = br.id
    JOIN donors d ON dr.donor_id = d.id
    JOIN users d_u ON d.user_id = d_u.id
    WHERE br.admin_approved = 0
      AND br.is_emergency = 0
      AND dr.status = 'pending_admin'
    ORDER BY br.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Blood Requests - Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="bg-slate-50 min-h-screen">

<!-- HEADER -->
<header class="bg-white border-b sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-6 py-3 flex justify-between items-center">
    <div class="flex items-center gap-3">
      <div class="bg-red-600 p-2 rounded-xl">
        <i data-lucide="droplet" class="text-white w-5 h-5"></i>
      </div>
      <span class="text-xl font-bold text-gray-800">Blood Requests - Admin</span>
    </div>

    <a href="dashboard.php"
       class="text-sm text-gray-500 hover:text-red-600 flex items-center gap-2">
      <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Dashboard
    </a>
  </div>
</header>

<!-- MAIN -->
<main class="max-w-7xl mx-auto p-6">

  <h1 class="text-2xl font-bold text-blue-700 mb-4">
    🩸 Pending Blood Requests
  </h1>

  <?php if ($blood_requests->num_rows === 0): ?>
    <div class="bg-white p-6 rounded-xl shadow text-center text-gray-500">
      No pending blood requests
    </div>
  <?php else: ?>
    <div class="space-y-4">
      <?php while ($br = $blood_requests->fetch_assoc()): ?>
        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6 shadow-sm">
          <div class="flex justify-between items-start">
            <div class="flex-1">
              <h3 class="font-bold text-gray-800 text-lg">
                Patient: <?= htmlspecialchars($br['patient_name']) ?>
              </h3>
              <p class="text-sm text-gray-600 mb-2">
                Requested by: <?= htmlspecialchars($br['recipient_name']) ?> → Donor: <?= htmlspecialchars($br['donor_name']) ?>
              </p>
              <div class="grid md:grid-cols-2 gap-2 text-sm text-gray-600 mb-3">
                <div>🩸 Blood Group: <span class="font-semibold text-red-600"><?= $br['blood_group'] ?></span></div>
                <div>🩸 Units Required: <span class="font-semibold"><?= $br['units_required'] ?></span></div>
                <div>🏥 Hospital: <span class="font-semibold"><?= htmlspecialchars($br['hospital_name']) ?></span></div>
                <div>📍 Location: <span class="font-semibold"><?= htmlspecialchars($br['hospital_location']) ?></span></div>
                <div>📞 Contact: <span class="font-semibold"><?= htmlspecialchars($br['contact_phone']) ?></span></div>
                <div>⚡ Urgency: <span class="font-semibold text-orange-600"><?= strtoupper($br['urgency_level']) ?></span></div>
                <div>⏰ Posted: <span class="font-semibold"><?= date('M d, Y H:i', strtotime($br['created_at'])) ?></span></div>
              </div>
              <?php if ($br['evidence_document']): ?>
                <div class="mb-3">
                  <a href="../../<?= htmlspecialchars($br['evidence_document']) ?>"
                     target="_blank"
                     class="inline-flex items-center gap-2 px-3 py-1 bg-blue-100 text-blue-700 rounded-lg text-sm hover:bg-blue-200">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                    View Evidence Document
                  </a>
                </div>
              <?php endif; ?>
            </div>

            <div class="ml-4 flex flex-col gap-2">
              <a href="approve-blood-request.php?id=<?= $br['id'] ?>"
                 class="inline-flex items-center gap-2 px-5 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold">
                <i data-lucide="check" class="w-4 h-4"></i>
                Approve Request
              </a>
              <a href="reject-blood-request.php?id=<?= $br['id'] ?>"
                 class="inline-flex items-center gap-2 px-5 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold">
                <i data-lucide="x" class="w-4 h-4"></i>
                Reject Request
              </a>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>

</main>

<script>
  lucide.createIcons();
</script>

</body>
</html>