<?php
require_once "../init.php";
check_login();
check_role('student');
include "../header.php";

if(isset($_POST['register'])){
    $stmt = $conn->prepare("INSERT INTO takes (ID, course_id, sec_id, semester, year) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $_SESSION['ref_id'], $_POST['course_id'], $_POST['sec_id'], $_POST['semester'], $_POST['year']);
    $stmt->execute();
}

$sections = $conn->query("SELECT * FROM section");
?>

<h2>Register for Classes</h2>
<form method="POST">
    Course ID: <input name="course_id" required>
    Section: <input name="sec_id" required>
    Semester: <input name="semester" required>
    Year: <input name="year" type="number" required>
    <input type="submit" name="register" value="Register">
</form>
