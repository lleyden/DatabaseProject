<?php
require_once "../init.php";
check_login();
check_role('student');
include "../header.php";

$stmt = $conn->prepare("
    SELECT course_id, sec_id, semester, year, grade
    FROM takes
    WHERE ID = ?
    ORDER BY year DESC, FIELD(semester, 'Spring','Summer','Fall','Winter'), course_id
");
$stmt->bind_param("s", $_SESSION['ref_id']);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>Your Grades</h2>
<table border="1" cellpadding="5" cellspacing="0">
<tr>
    <th>Course</th>
    <th>Section</th>
    <th>Semester</th>
    <th>Year</th>
    <th>Grade</th>
</tr>
<?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($row['course_id']) ?></td>
    <td><?= htmlspecialchars($row['sec_id']) ?></td>
    <td><?= htmlspecialchars($row['semester']) ?></td>
    <td><?= htmlspecialchars($row['year']) ?></td>
    <td><?= htmlspecialchars($row['grade'] ?? '') ?></td>
</tr>
<?php endwhile; ?>
</table>
