<?php
session_start();
include "../../config/db.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['email'];
    $pass  = $_POST['password'];

    $q = $conn->query("
        SELECT * FROM users
        WHERE email='$email' AND role='donor'
    ");

    if ($q->num_rows !== 1) {
        $error = "❌ Invalid login";
    } else {
        $u = $q->fetch_assoc();

        if (!password_verify($pass, $u['password_hash'])) {
            $error = "❌ Invalid login";
        } elseif ($u['status'] !== 'approved') {
            $error = "⏳ Admin approval pending";
        } else {
            $_SESSION['user_id'] = $u['id'];
            $_SESSION['role'] = 'donor';

            // Get donor_id
            $donor_query = $conn->query("SELECT id FROM donors WHERE user_id = {$u['id']}");
            if ($donor_query->num_rows > 0) {
                $donor = $donor_query->fetch_assoc();
                $_SESSION['donor_id'] = $donor['id'];
            }

            header("Location: dashboard.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Donor Login</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-green-50 to-green-100 min-h-screen flex items-center justify-center p-4">

<div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-8">

  <h2 class="text-3xl font-bold text-center text-green-600 mb-2">
    🔐 Donor Login
  </h2>

  <p class="text-center text-sm text-gray-600 mb-6">
    Login after admin approval
  </p>

  <?php if ($error): ?>
    <div class="mb-4 p-3 rounded bg-red-100 text-red-700 text-sm text-center">
      <?= $error ?>
    </div>
  <?php endif; ?>

  <form method="POST" class="space-y-4">

    <input
      type="email"
      name="email"
      placeholder="Email address"
      required
      class="w-full border rounded-lg px-4 py-2
             focus:outline-none focus:ring focus:ring-green-200">

    <input
      type="password"
      name="password"
      placeholder="Password"
      required
      class="w-full border rounded-lg px-4 py-2
             focus:outline-none focus:ring focus:ring-green-200">

    <button
      class="w-full bg-green-600 text-white py-2 rounded-lg
             font-semibold hover:bg-green-700 transition">
      Login
    </button>
  </form>

  <div class="mt-6 text-center text-sm text-gray-600">
    New donor?
    <a href="register.php"
       class="text-red-600 font-semibold hover:underline">
      Register here
    </a>
  </div>

</div>

</body>
</html>
