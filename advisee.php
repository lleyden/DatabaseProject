<?php
require_once "../init.php";
check_login();
check_role('instructor');
include "../header.php";

// Handle assigning a student
if (isset($_POST['assign'])) {
    $stmt = $conn->prepare("INSERT INTO advisor (s_ID, i_ID) VALUES (?, ?)");
    $stmt->bind_param("ss", $_POST['student_id'], $_SESSION['ref_id']);
    $stmt->execute();
}

// Fetch students without advisors
$students = $conn->query("
    SELECT ID, name 
    FROM student 
    WHERE ID NOT IN (SELECT s_ID FROM advisor)
    ORDER BY name
");
?>

<h2>Assign Students</h2>

<form method="POST">
    <label>Student:</label>
    <select name="student_id" required>
        <option value="">Select Student</option>
        <?php while ($row = $students->fetch_assoc()): ?>
            <option value="<?= $row['ID'] ?>"><?= $row['name'] ?> (<?= $row['ID'] ?>)</option>
        <?php endwhile; ?>
    </select>
    <input type="submit" name="assign" value="Assign Student">
</form>

<h3>Current Students</h3>
<table border="1" style="border-collapse:collapse;">
<tr><th>Student ID</th><th>Name</th></tr>
<?php
$current = $conn->prepare("
    SELECT s.ID, s.name 
    FROM student s
    JOIN advisor a ON s.ID = a.s_ID
    WHERE a.i_ID = ?
");
$current->bind_param("s", $_SESSION['ref_id']);
$current->execute();
$current_result = $current->get_result();
while ($row = $current_result->fetch_assoc()): ?>
<tr>
    <td><?= $row['ID'] ?></td>
    <td><?= $row['name'] ?></td>
</tr>
<?php endwhile; ?>
</table>
