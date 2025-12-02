<?php
require_once "../init.php";
check_login();
check_role('student');
include "../header.php";

if(isset($_POST['update'])){
    $stmt = $conn->prepare("UPDATE student SET name=?, dept_name=? WHERE ID=?");
    $stmt->bind_param("ssi", $_POST['name'], $_POST['dept_name'], $_SESSION['ref_id']);
    $stmt->execute();
}

$stmt = $conn->prepare("SELECT name, dept_name FROM student WHERE ID=?");
$stmt->bind_param("s", $_SESSION['ref_id']);
$stmt->execute();
$stmt->bind_result($name, $dept);
$stmt->fetch();
?>

<h2>Edit Profile</h2>
<form method="POST">
    Name: <input name="name" value="<?= $name ?>" required>
    Department: <input name="dept_name" value="<?= $dept ?>" required>
    <input type="submit" name="update" value="Update">
</form>
