<?php
require_once "../init.php";
check_login();
check_role('instructor');
include "../header.php";

$stmt = $conn->prepare("SELECT s.ID, s.name, t.course_id, t.sec_id, t.semester, t.year, t.grade 
FROM takes t 
JOIN student s ON t.ID = s.ID
JOIN teaches ts ON ts.course_id = t.course_id AND ts.sec_id = t.sec_id AND ts.semester = t.semester AND ts.year = t.year
WHERE ts.ID = ?");
$stmt->bind_param("s", $_SESSION['ref_id']);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>Section Roster</h2>
<table border="1">
<tr><th>Student ID</th><th>Name</th><th>Course</th><th>Section</th><th>Semester</th><th>Year</th><th>Grade</th></tr>
<?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row['ID'] ?></td>
    <td><?= $row['name'] ?></td>
    <td><?= $row['course_id'] ?></td>
    <td><?= $row['sec_id'] ?></td>
    <td><?= $row['semester'] ?></td>
    <td><?= $row['year'] ?></td>
    <td><?= $row['grade'] ?></td>
</tr>
<?php endwhile; ?>
</table>
