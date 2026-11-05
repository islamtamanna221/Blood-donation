<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor') {
    header("Location: login.php");
    exit;
}

$uid = (int)$_SESSION['user_id'];

/* ================= DONOR INFO ================= */
$q = $conn->query("
    SELECT 
        u.full_name,
        u.email,
        COALESCE(bg.group_name, d.blood_group) AS blood_group,
        d.blood_group_id,
        d.location,
        d.is_active,
        d.total_donations,
        d.last_donation_date,
        d.id AS donor_id
    FROM users u
    JOIN donors d ON d.user_id = u.id
    LEFT JOIN blood_groups bg ON d.blood_group_id = bg.id
    WHERE u.id = $uid
");
$donor = $q->fetch_assoc();

if (!$donor) {
    echo 'Donor record not found';
    exit;
}

$donor_id = (int)$donor['donor_id'];

/* ================= 3-MONTH COOLDOWN CALCULATION ================= */
$can_donate = true;
$next_eligible_date = null;
$remaining_seconds = 0;

if (!empty($donor['last_donation_date'])) {
    $last_date_ts = strtotime($donor['last_donation_date']);
    // Calculate date 3 months from last donation
    $next_date_ts = strtotime('+3 months', $last_date_ts);
    $current_ts = time();

    if ($current_ts < $next_date_ts) {
        $can_donate = false;
        $next_eligible_date = date('Y-m-d H:i:s', $next_date_ts);
        $remaining_seconds = $next_date_ts - $current_ts;
    }
}

/* ================= MARK AS COMPLETE ================= */
if (isset($_POST['mark_complete'])) {

    // Security Check: Prevent submission if cooldown is active
    if (!$can_donate) {
        echo "<script>alert('You must wait 3 months between donations.'); window.location.href='dashboard.php';</script>";
        exit;
    }

    // temporarily static – later dynamic from request
    $blood_request_id = 5;

    try {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $conn->begin_transaction();

        $stmt = $conn->prepare("
            INSERT INTO donations 
                (donor_id, blood_request_id, donation_date, status)
            VALUES 
                (?, ?, NOW(), 'completed')
        ");
        $stmt->bind_param("ii", $donor_id, $blood_request_id);
        $stmt->execute();

        $stmt = $conn->prepare("
            UPDATE donors
            SET 
                total_donations = total_donations + 1,
                last_donation_date = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param("i", $donor_id);
        $stmt->execute();

        $conn->commit();
        header("Location: dashboard.php?completed=1");
        exit;

    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        die("DB Error: " . $e->getMessage());
    }
}

/* ================= MATCHING APPROVED REQUESTS ================= */
$bg_id = (int)$donor['blood_group_id'];

$matchingRequests = $conn->query("
    SELECT 
        br.id AS request_id,
        br.patient_name, 
        br.units_required, 
        br.hospital_name, 
        br.hospital_location,
        br.area_name,
        br.urgency_level,
        br.created_at,
        u.full_name AS recipient_name
    FROM blood_requests br
    JOIN recipients r ON br.recipient_id = r.id
    JOIN users u ON r.user_id = u.id
    WHERE br.admin_approved = 1 
      AND br.status = 'approved'
      AND br.blood_group_id = $bg_id
    ORDER BY br.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Donor Dashboard | Orion</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@latest"></script>

<style>
.bg-medical{
  background: 
    radial-gradient(circle at 15% 20%, rgba(244,63,94,.12), transparent 40%),
    radial-gradient(circle at 85% 80%, rgba(14,165,233,.12), transparent 40%),
    linear-gradient(to bottom right, #fff1f2, #f8fafc, #f1f5f9);
}
.float-card{transition:.25s}
.float-card:hover{transform:translateY(-6px)}
</style>
</head>

<body class="bg-medical min-h-screen">

<nav class="bg-white/90 backdrop-blur border-b sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-6 h-16 flex justify-between items-center">
    <div class="flex items-center gap-3">
      <div class="bg-red-500 p-3 rounded-xl">
        <i data-lucide="droplet" class="text-white w-5 h-5"></i>
      </div>
      <span class="text-2xl font-bold text-red-600">Orion</span>
    </div>

    <div class="flex items-center gap-4">
      <span class="px-3 py-1 rounded-full text-xs font-bold 
        <?= $donor['is_active'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
        <?= $donor['is_active'] ? 'ACTIVE' : 'INACTIVE' ?>
      </span>

      <a href="../../auth/logout.php" class="flex items-center gap-2 text-sm">
        <i data-lucide="log-out" class="w-4 h-4"></i> Logout
      </a>
    </div>
  </div>
</nav>

<main class="max-w-7xl mx-auto px-6 py-12">

<?php if (isset($_GET['completed'])): ?>
<div class="bg-green-100 text-green-700 p-4 rounded-xl mb-8 border border-green-200 shadow-sm flex items-center gap-2">
  <i data-lucide="check-circle" class="w-5 h-5"></i>
  Donation recorded! You have entered the recovery period.
</div>
<?php endif; ?>

<h1 class="text-4xl font-bold mb-2">
  Welcome back, <?= htmlspecialchars($donor['full_name']) ?> 👋
</h1>
<p class="text-gray-600 mb-10"><?= htmlspecialchars($donor['location']) ?></p>

<div class="grid md:grid-cols-3 gap-6 mb-16">
  <div class="bg-white p-6 rounded-2xl shadow float-card">
    <p class="text-sm text-gray-500">Blood Group</p>
    <p class="text-4xl font-bold text-red-600"><?= $donor['blood_group'] ?></p>
  </div>

  <div class="bg-white p-6 rounded-2xl shadow float-card">
    <p class="text-sm text-gray-500">Total Donations</p>
    <p class="text-4xl font-bold text-green-600"><?= $donor['total_donations'] ?></p>
  </div>

  <div class="bg-white p-6 rounded-2xl shadow float-card">
    <p class="text-sm text-gray-500">Last Donation</p>
    <p class="font-semibold text-lg"><?= $donor['last_donation_date'] ? date('M d, Y', strtotime($donor['last_donation_date'])) : 'Not yet donated' ?></p>
  </div>
</div>

<section class="mb-20">
    <div class="bg-white rounded-3xl shadow p-8 relative overflow-hidden">
        
        <?php if ($can_donate): ?>
            <div class="relative z-10">
                <h3 class="text-2xl font-bold mb-2 text-gray-900">Record a New Donation</h3>
                <p class="text-gray-600 mb-6">
                    Have you successfully donated blood recently? Mark it here to update your records and start your recovery timer.
                </p>
                <form method="POST">
                    <button type="submit" name="mark_complete" 
                            onclick="return confirm('Are you sure you donated? This will start a 3-month cooldown.');"
                            class="bg-red-600 text-white px-8 py-4 rounded-xl font-bold hover:bg-red-700 transition flex items-center gap-2 shadow-lg shadow-red-200">
                        <i data-lucide="check" class="w-5 h-5"></i> Mark Donation as Completed
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="relative z-10">
                <div class="flex flex-col md:flex-row gap-8 items-center">
                    <div class="flex-1">
                        <h3 class="text-2xl font-bold mb-2 text-amber-600 flex items-center gap-2">
                            <i data-lucide="hourglass" class="w-6 h-6"></i> Recovery Period Active
                        </h3>
                        <p class="text-gray-600 mb-4">
                            Thank you for being a hero! To ensure your health, you must wait 3 months between donations.
                        </p>
                        <div class="text-sm text-gray-500 bg-gray-50 p-3 rounded-lg inline-block border">
                            Next eligible date: <strong class="text-gray-800"><?= date('F j, Y', strtotime($next_eligible_date)) ?></strong>
                        </div>
                    </div>

                    <div class="bg-amber-50 border border-amber-100 p-6 rounded-2xl text-center min-w-[300px]">
                        <p class="text-amber-800 font-semibold mb-2 text-sm uppercase tracking-wider">Next Donation In</p>
                        <div class="grid grid-cols-4 gap-2 text-amber-900" id="timer-display">
                            <div><span class="text-2xl font-bold" id="d-day">00</span><br><span class="text-xs">Days</span></div>
                            <div><span class="text-2xl font-bold" id="d-hour">00</span><br><span class="text-xs">Hrs</span></div>
                            <div><span class="text-2xl font-bold" id="d-min">00</span><br><span class="text-xs">Min</span></div>
                            <div><span class="text-2xl font-bold" id="d-sec">00</span><br><span class="text-xs">Sec</span></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <script>
                // Get remaining seconds from PHP
                let remainingTime = <?= $remaining_seconds ?>;

                function updateTimer() {
                    if (remainingTime <= 0) {
                        location.reload(); // Reload page when timer hits 0 to enable button
                        return;
                    }

                    const days = Math.floor(remainingTime / (24 * 3600));
                    const hours = Math.floor((remainingTime % (24 * 3600)) / 3600);
                    const minutes = Math.floor((remainingTime % 3600) / 60);
                    const seconds = Math.floor(remainingTime % 60);

                    document.getElementById('d-day').innerText = days;
                    document.getElementById('d-hour').innerText = hours.toString().padStart(2, '0');
                    document.getElementById('d-min').innerText = minutes.toString().padStart(2, '0');
                    document.getElementById('d-sec').innerText = seconds.toString().padStart(2, '0');

                    remainingTime--;
                }

                setInterval(updateTimer, 1000);
                updateTimer(); // Run immediately
            </script>
        <?php endif; ?>
    </div>
</section>

<!-- <div class="bg-gradient-to-r from-slate-800 to-slate-900 p-8 rounded-3xl text-white mb-20">
  <h2 class="text-3xl font-bold mb-2">View All Blood Requests</h2>
  <p class="mb-4 text-slate-300">Browse the full list of patients needing help.</p>
  <a href="recipients.php" class="bg-white text-slate-900 px-6 py-3 rounded-xl font-bold hover:bg-slate-100 transition">
    See Blood Requests
  </a>
</div> -->

<section class="mb-20">
  <h2 class="text-3xl font-extrabold text-gray-900 mb-6">
    Blood Requests Matching Your Group 🩸
  </h2>

  <?php if ($matchingRequests->num_rows === 0): ?>
    <div class="bg-white rounded-2xl p-6 shadow text-gray-500">
      No approved blood requests match your blood group right now.
    </div>
  <?php endif; ?>

  <div class="grid md:grid-cols-2 gap-6">
  <?php while ($r = $matchingRequests->fetch_assoc()): ?>
    <a href="recipients.php?request_id=<?= $r['request_id'] ?>" 
       class="block bg-white rounded-3xl p-6 shadow float-card 
              hover:ring-2 hover:ring-rose-400 transition">
      
      <div class="flex justify-between items-start mb-2">
        <div>
          <h3 class="text-xl font-bold text-gray-800">
            Patient: <?= htmlspecialchars($r['patient_name']) ?>
          </h3>
          <p class="text-sm text-gray-500">
            Requested by <?= htmlspecialchars($r['recipient_name']) ?>
          </p>
        </div>

        <span class="px-3 py-1 rounded-full text-xs font-bold 
          <?= $r['urgency_level'] === 'high' 
              ? 'bg-red-100 text-red-700' 
              : 'bg-yellow-100 text-yellow-700' ?>">
          <?= strtoupper($r['urgency_level']) ?>
        </span>
      </div>

      <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm text-gray-600">
        <p>🩸 <strong><?= $r['units_required'] ?></strong> Units Required</p>
        <p>🏥 <?= htmlspecialchars($r['hospital_name']) ?></p>
        <p>📍 <?= htmlspecialchars($r['hospital_location']) ?> 
           (<?= htmlspecialchars($r['area_name']) ?>)</p>
        <p>🕒 <?= date('M d, Y', strtotime($r['created_at'])) ?></p>
      </div>

      <div class="mt-5 text-rose-600 font-bold">
        View Details & Respond →
      </div>
    </a>
  <?php endwhile; ?>
  </div>
</section>

<div class="bg-white p-8 rounded-3xl shadow flex justify-between items-center">
  <div>
    <h3 class="text-xl font-bold">Manage Availability</h3>
    <p class="text-gray-600 text-sm">Control whether recipients can see you.</p>
  </div>
  <a href="settings.php" class="bg-blue-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-blue-700 transition">
    Settings
  </a>
</div>

</main>

<script>lucide.createIcons();</script>
</body>
</html>