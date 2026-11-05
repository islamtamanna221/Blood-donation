<?php include "../config/db.php"; ?>
<h2>Donor Registration</h2>

<form method="POST">
    Blood Group: <input type="text" name="blood_group"><br><br>
    Location: <input type="text" name="location"><br><br>
    Age: <input type="number" name="age"><br><br>
    Weight: <input type="number" name="weight"><br><br>

    <button name="submit">Submit</button>
</form>

<?php
if (isset($_POST['submit'])) {
    $uid = $_SESSION['user_id'];

    $conn->query("INSERT INTO donors (user_id,blood_group,location,age,weight)
                  VALUES ('$uid','{$_POST['blood_group']}','{$_POST['location']}',
                          '{$_POST['age']}','{$_POST['weight']}')");

    echo "Registration submitted. Wait for admin approval.";
}
?>
