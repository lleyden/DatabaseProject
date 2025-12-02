<?php
require_once "../init.php";
check_login();
check_role('instructor');
include "../header.php";

$stmt = $conn->prepare("SELECT s.ID, s.name FROM student s JOIN advisor a ON s.ID = a.s_ID WHERE a.i_ID=?");
$stmt->bind_param("s", $_SESSION['ref_id']);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>Advisee Students</h2>
<table border="1">
<tr><th>Student ID</th><th>Name</th></tr>
<?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row['ID'] ?></td>
    <td><?= $row['name'] ?></td>
</tr>
<?php endwhile; ?>
</table>
