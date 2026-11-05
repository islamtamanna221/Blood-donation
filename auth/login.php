<?php
session_start();
$role = $_GET['role'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
  <title><?= ucfirst($role) ?> Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">

<form method="POST" action="login_action.php"
      class="bg-white p-8 rounded-lg shadow-lg w-96">

  <h2 class="text-2xl font-bold mb-6 text-center">
    <?= ucfirst($role) ?> Login
  </h2>

  <?php if ($role === 'admin'): ?>
    <!-- ADMIN LOGIN -->
    <input type="number" name="admin_id" placeholder="Admin ID"
           class="w-full border p-2 mb-4" required>
  <?php else: ?>
    <!-- DONOR / RECIPIENT -->
    <input type="email" name="email" placeholder="Email"
           class="w-full border p-2 mb-4" required>
  <?php endif; ?>

  <!-- PASSWORD WITH EYE ICON -->
  <div class="relative mb-4">
    <input
      type="password"
      id="password"
      name="password"
      placeholder="Password"
      class="w-full border p-2 pr-10"
      required
    >

    <button
      type="button"
      onclick="togglePassword()"
      class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-600 cursor-pointer"
    >
      👁️
    </button>
  </div>

  <input type="hidden" name="role" value="<?= $role ?>">

  <button class="w-full bg-red-600 text-white py-2 rounded">
    Login
  </button>

</form>

<script>
function togglePassword() {
  const pass = document.getElementById("password");
  if (pass.type === "password") {
    pass.type = "text";   // show
  } else {
    pass.type = "password"; // hide
  }
}
</script>

</body>
</html>

