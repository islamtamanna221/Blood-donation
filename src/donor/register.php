<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "../../config/db.php";

$msg = "";

/* fetch areas */
$areas = [];
$areaRes = $conn->query("SELECT id, area_name, city FROM areas ORDER BY area_name");
while ($row = $areaRes->fetch_assoc()) {
    $areas[] = $row;
}

/* fetch blood groups */
$bloodGroups = [];
$bgRes = $conn->query("SELECT id, group_name FROM blood_groups ORDER BY group_name");
while ($bg = $bgRes->fetch_assoc()) {
    $bloodGroups[] = $bg;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {

    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $pass  = password_hash($_POST['password'], PASSWORD_DEFAULT);

    /* ✅ SAFE POST READ */
    $blood_group_id = isset($_POST['blood_group_id']) ? (int)$_POST['blood_group_id'] : 0;
    $area_id        = isset($_POST['area_id']) ? (int)$_POST['area_id'] : 0;
    $age            = $_POST['age'] ?? null;
    $wt             = $_POST['weight'] ?? null;

    $id_doc  = $_FILES['id_document'] ?? null;
    $med_doc = $_FILES['medical_report'] ?? null;

    if ($blood_group_id === 0 || $area_id === 0) {
        $msg = "❌ Please select blood group and area";
    } else {

        /* check email */
        $check = $conn->query("SELECT id FROM users WHERE email='$email'");
        if ($check->num_rows > 0) {
            $msg = "❌ Email already exists";
        } else {

            /* get area name */
            $areaRow = $conn->query("SELECT area_name FROM areas WHERE id=$area_id")->fetch_assoc();
            $area_name = $areaRow['area_name'];

            /* users */
            $conn->query("
                INSERT INTO users (full_name, email, password_hash, role, status)
                VALUES ('$name', '$email', '$pass', 'donor', 'pending')
            ");
            $user_id = $conn->insert_id;

            /* donors */
            $conn->query("
                INSERT INTO donors
                (user_id, blood_group_id, area_id, location, age, weight, is_active)
                VALUES
                ($user_id, $blood_group_id, $area_id, '$area_name', $age, $wt, 0)
            ");
            $donor_id = $conn->insert_id;

            /* upload directory */
            $uploadDir = "../../uploads/donor_docs/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $id_path  = null;
            $med_path = null;

            if (!empty($id_doc['name'])) {
                $id_path = $uploadDir . time() . "_id_" . basename($id_doc['name']);
                move_uploaded_file($id_doc['tmp_name'], $id_path);
            }

            if (!empty($med_doc['name'])) {
                $med_path = $uploadDir . time() . "_medical_" . basename($med_doc['name']);
                move_uploaded_file($med_doc['tmp_name'], $med_path);
            }

            /* donor_verifications */
            $stmt = $conn->prepare("
                INSERT INTO donor_verifications
                (donor_id, id_document, medical_report, status)
                VALUES (?, ?, ?, 'pending')
            ");
            $stmt->bind_param("iss", $donor_id, $id_path, $med_path);
            $stmt->execute();

            $msg = "⏳ Registration successful. Admin approval required.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Donor Registration</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-red-50 to-red-100 min-h-screen flex items-center justify-center p-4">

<div class="bg-white rounded-xl shadow-lg w-full max-w-xl p-8">

<h2 class="text-3xl font-bold text-center text-red-600 mb-4">
🩸 Donor Registration
</h2>

<?php if ($msg): ?>
<div class="mb-4 p-3 rounded bg-yellow-100 text-yellow-800 text-sm text-center">
<?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="space-y-4">

<input name="name" required placeholder="Full Name"
       class="w-full border rounded px-3 py-2">

<input type="email" name="email" required placeholder="Email"
       class="w-full border rounded px-3 py-2">

<input type="password" name="password" required placeholder="Password"
       class="w-full border rounded px-3 py-2">

<!-- BLOOD GROUP -->
<select name="blood_group_id" required
        class="w-full border rounded px-3 py-2 bg-white">
<option value="0">Select Blood Group</option>
<?php foreach ($bloodGroups as $bg): ?>
<option value="<?= $bg['id'] ?>">
<?= htmlspecialchars($bg['group_name']) ?>
</option>
<?php endforeach; ?>
</select>

<!-- AREA -->
<select name="area_id" required
        class="w-full border rounded px-3 py-2 bg-white">
<option value="0">Select Area</option>
<?php foreach ($areas as $a): ?>
<option value="<?= $a['id'] ?>">
<?= htmlspecialchars($a['area_name']) ?>, <?= htmlspecialchars($a['city']) ?>
</option>
<?php endforeach; ?>
</select>

<input type="number" name="age" placeholder="Age"
       class="w-full border rounded px-3 py-2">

<input type="number" name="weight" placeholder="Weight (kg)"
       class="w-full border rounded px-3 py-2">

<div>
<label class="text-sm font-semibold">ID Document</label>
<input type="file" name="id_document"
       class="w-full border rounded px-3 py-2 text-sm">
</div>

<div>
<label class="text-sm font-semibold">Medical Report</label>
<input type="file" name="medical_report"
       class="w-full border rounded px-3 py-2 text-sm">
</div>

<button class="w-full bg-red-600 text-white py-2 rounded font-semibold hover:bg-red-700">
Register
</button>

</form>

<hr class="my-6">

<p class="text-center text-sm text-gray-600">
Already registered?
<a href="login.php" class="text-green-600 font-semibold hover:underline">
Login here
</a>
</p>

</div>
</body>
</html>
