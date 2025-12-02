<?php
require_once "../init.php";
check_login();
check_role('admin');
include "../header.php";

if(isset($_POST['add'])){
    $stmt = $conn->prepare("INSERT INTO course (course_id, title, dept_name, credits) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $_POST['course_id'], $_POST['title'], $_POST['dept_name'], $_POST['credits']);
    $stmt->execute();
}

$courses = $conn->query("SELECT * FROM course");
?>

<h2>Courses</h2>
<form method="POST">
    ID: <input name="course_id" required>
    Title: <input name="title" required>
    Dept: <input name="dept_name" required>
    Credits: <input name="credits" type="number" required>
    <input type="submit" name="add" value="Add">
</form>

<table border="1">
<tr><th>ID</th><th>Title</th><th>Dept</th><th>Credits</th></tr>
<?php while($row = $courses->fetch_assoc()): ?>
<tr>
    <td><?= $row['course_id'] ?></td>
    <td><?= $row['title'] ?></td>
    <td><?= $row['dept_name'] ?></td>
    <td><?= $row['credits'] ?></td>
</tr>
<?php endwhile; ?>
</table>
<?php
include "../footer.php";
?>