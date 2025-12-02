<?php
require_once "../init.php";
check_login();
check_role('student');
include "../header.php";

$stmt = $conn->prepare("SELECT course_id, sec_id, semester, year, grade FROM takes WHERE ID=?");
$stmt->bind_param("s", $_SESSION['ref_id']);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>Your Grades</h2>
<table border="1">
<tr><th>Course</th><th>Section</th><th>Semester</th><th>Year</th><th>Grade</th></tr>
<?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row['course_id'] ?></td>
    <td><?= $row['sec_id'] ?></td>
    <td><?= $row['semester'] ?></td>
    <td><?= $row['year'] ?></td>
    <td><?= $row['grade'] ?></td>
</tr>
<?php endwhile; ?>
</table>
