<?php
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
        } elseif ($u['status'] === 'pending') {
            $error = "⏳ Admin approval pending";
        } else {
            $_SESSION['user_id'] = $u['id'];
            $_SESSION['role'] = 'donor';
            header("Location: ../donor/dashboard.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<body>

<h2>Donor Login</h2>
<p><?= $error ?></p>

<form method="POST">
  <input type="email" name="email" placeholder="Email" required><br>
  <input type="password" name="password" placeholder="Password" required><br>
  <button>Login</button>
</form>

</body>
</html>
