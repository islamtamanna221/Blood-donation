<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor') {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user_id'];

/* donor info */
$q = $conn->query("
    SELECT is_active, location, blood_group, last_donation_date
    FROM donors
    WHERE user_id = $uid
");
$donor = $q->fetch_assoc();

/* toggle status */
if (isset($_POST['status'])) {
    $status = $_POST['status'] === '1' ? 1 : 0;
    $conn->query("UPDATE donors SET is_active=$status WHERE user_id=$uid");
    header("Location: settings.php?success=1");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Donor Settings</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container py-5" style="max-width:900px">

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success">
  ✅ Settings updated successfully
</div>
<?php endif; ?>

<h2 class="mb-4"><i class="bi bi-gear"></i> Donor Settings</h2>

<!-- AVAILABILITY -->
<div class="card shadow-sm mb-4">
  <div class="card-body">
    <h5 class="mb-3">Availability Status</h5>

    <div class="d-flex justify-content-between align-items-center bg-light p-4 rounded">
      <div>
        <h6 class="mb-1">
          <?= $donor['is_active'] ? 'You are Active' : 'You are Inactive' ?>
        </h6>
        <small class="text-muted">
          <?= $donor['is_active']
              ? 'Recipients can send requests'
              : 'You will not receive requests' ?>
        </small>
      </div>

      <form method="POST" class="d-flex gap-2">
        <button name="status" value="1"
          class="btn <?= $donor['is_active'] ? 'btn-success' : 'btn-outline-success' ?>">
          Active
        </button>
        <button name="status" value="0"
          class="btn <?= !$donor['is_active'] ? 'btn-danger' : 'btn-outline-danger' ?>">
          Inactive
        </button>
      </form>
    </div>
  </div>
</div>

<!-- INFO -->
<div class="card shadow-sm">
  <div class="card-body">
    <h5 class="mb-3">Current Info</h5>

    <p><i class="bi bi-geo-alt"></i> Location: <?= $donor['location'] ?></p>
    <p><i class="bi bi-droplet"></i> Blood Group: <?= $donor['blood_group'] ?></p>
    <p><i class="bi bi-calendar"></i>
      Last Donation:
      <?= $donor['last_donation_date'] ?? 'N/A' ?>
    </p>
  </div>
</div>

<a href="dashboard.php" class="btn btn-secondary mt-4">
  ← Back to Dashboard
</a>

</div>
</body>
</html>
