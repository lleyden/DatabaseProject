<?php
require_once "../init.php";
check_login();
check_role('instructor');
include "../header.php";

if(isset($_POST['update'])){
    $stmt = $conn->prepare("UPDATE takes SET grade=? WHERE ID=? AND course_id=? AND sec_id=? AND semester=? AND year=?");
    $stmt->bind_param("ssssssi", $_POST['grade'], $_POST['student_id'], $_POST['course_id'], $_POST['sec_id'], $_POST['semester'], $_POST['year']);
    $stmt->execute();
}
?>
<h2>Assign/Change Grades</h2>
<form method="POST">
    Student ID: <input name="student_id" required>
    Course ID: <input name="course_id" required>
    Section: <input name="sec_id" required>
    Semester: <input name="semester" required>
    Year: <input name="year" type="number" required>
    Grade: <input name="grade" required>
    <input type="submit" name="update" value="Update Grade">
</form>
