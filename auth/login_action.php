<?php
include "../config/db.php";

$role = $_POST['role'];

if ($role === 'admin') {

    $admin_id = $_POST['admin_id'];
    $password = $_POST['password'];

    
    $stmt = $conn->prepare(
        "SELECT * FROM admins WHERE id = ? AND password = ?"
    );
    $stmt->bind_param("is", $admin_id, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        // ✅ Login successful
        $_SESSION['admin_id'] = $admin_id;
        $_SESSION['role'] = 'admin';

       header("Location: /lab/weblab/send/src/admin/dashboard.php");
        exit;

    } else {
        echo "❌ Wrong Admin ID or Password";
        exit;
    }
}

echo "Invalid role";
