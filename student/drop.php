<?php
require_once "../init.php";
check_login();
check_role('student');
include "../header.php";

if(isset($_POST['drop'])){
    $stmt = $conn->prepare("DELETE FROM takes WHERE ID=? AND course_id=? AND sec_id=? AND semester=? AND year=?");
    $stmt->bind_param("ssssi", $_SESSION['ref_id'], $_POST['course_id'], $_POST['sec_id'], $_POST['semester'], $_POST['year']);
    $stmt->execute();
}
?>

<h2>Drop Classes</h2>
<form method="POST">
    Course ID: <input name="course_id" required>
    Section: <input name="sec_id" required>
    Semester: <input name="semester" required>
    Year: <input name="year" type="number" required>
    <input type="submit" name="drop" value="Drop Class">
</form>
