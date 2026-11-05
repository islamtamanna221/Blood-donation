<?php
session_start();
include "../../config/db.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['email'];
    $pass  = $_POST['password'];

    $q = $conn->query("
        SELECT * FROM users
        WHERE email='$email' AND role='recipient'
    ");

    if ($q->num_rows !== 1) {
        $error = "❌ Invalid email or password";
    } else {
        $u = $q->fetch_assoc();

        if (!password_verify($pass, $u['password_hash'])) {
            $error = "❌ Invalid email or password";
        } else {
            // ✅ LOGIN SUCCESS
            $_SESSION['user_id'] = $u['id'];
            $_SESSION['role'] = 'recipient';

            // Get recipient_id
            $recipient_query = $conn->query("SELECT id FROM recipients WHERE user_id = {$u['id']}");
            if ($recipient_query->num_rows > 0) {
                $recipient = $recipient_query->fetch_assoc();
                $_SESSION['recipient_id'] = $recipient['id'];
            }

            // ✅ REDIRECT TO RECIPIENT DASHBOARD
            header("Location: dashboard.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Recipient Login</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">

<div class="bg-white p-8 rounded-xl shadow w-full max-w-md">

  <h2 class="text-2xl font-bold text-center mb-2">
    🏥 Recipient Login
  </h2>

  <p class="text-center text-sm text-gray-600 mb-6">
    Login to request blood
  </p>

  <?php if ($error): ?>
    <div class="mb-4 p-3 bg-red-100 text-red-700 rounded text-center">
      <?= $error ?>
    </div>
  <?php endif; ?>

  <form method="POST" class="space-y-4">

    <input
      type="email"
      name="email"
      placeholder="Email address"
      required
      class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-red-200"
    >

    <input
      type="password"
      name="password"
      placeholder="Password"
      required
      class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-red-200"
    >

    <button
      class="w-full bg-red-600 text-white py-2 rounded-lg font-semibold hover:bg-red-700 transition"
    >
      Login
    </button>

  </form>

  <p class="text-center text-sm text-gray-600 mt-4">
    New recipient?
    <a href="register.php" class="text-red-600 font-semibold hover:underline">
      Register here
    </a>
  </p>

</div>

</body>
</html>
