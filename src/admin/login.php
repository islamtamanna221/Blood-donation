<?php
session_start();
include "../../config/db.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 🔐 only password
    $password = $_POST['password'];

    // admins table check (id = 1)
    $q = $conn->query("
        SELECT *
        FROM admins
        WHERE id = 1
          AND password = '$password'
    ");

    if ($q && $q->num_rows === 1) {
        // session set
        $_SESSION['admin_id'] = 1;

        header("Location: dashboard.php");
        exit;
    } else {
        $error = "❌ Wrong admin password";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin Login</title>
</head>
<body>

<h2>Admin Login</h2>

<?php if ($error): ?>
  <p style="color:red"><?= $error ?></p>
<?php endif; ?>

<form method="POST">
  <input
    type="password"
    name="password"
    placeholder="Admin Password"
    required
  ><br><br>

  <button type="submit">Login</button>
</form>

</body>
</html>
