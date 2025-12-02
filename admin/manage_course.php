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

if(isset($_POST['update'])){
    $stmt = $conn->prepare("UPDATE course set title = ?, dept_name= ?, credits= ? where course_id = ?");
    $stmt->bind_param("ssis", $_POST['title'], $_POST['dept_name'], $_POST['credits'],$_POST['course_id']);
    $stmt->execute();
}

if(isset($_GET['del'])){
    $stmt = $conn->prepare("DELETE FROM course WHERE course_id=?");
    $stmt->bind_param("s", $_GET['del']);
    $stmt->execute();
}

$courses = $conn->query("SELECT * FROM course");
?>
<a href="dashboard.php">Return To Dashboard</a>
<h2>Courses</h2>
<table class="table">
    <tr>
        <th>ID</th>
        <th>Title</th>
        <th>Dept</th>
        <th>Credits</th>
        <th>Action</th>
    </tr>
    <form name="addRTecord" id="addRecord" method="POST"  action="<?=htmlspecialchars(basename($_SERVER["PHP_SELF"])); ?>">
    <tr>
        <td><input name="course_id" required></td>
        <td><input name="title" required></td>
        <td><input name="dept_name" required></td>
        <td><input name="credits" type="number" required></td>
        <td><input type="submit" name="add" value="Add"></td>
    </tr>
    </form>
    <?php while($row = $courses->fetch_assoc()): 
    if (isset($_GET["update"]) && $row['course_id'] == $_GET["update"]) {
    // this is if the user selects to update the record 
    ?>
    <form method="POST" name="updateRecord" id="updateRecord" action="<?=htmlspecialchars(basename($_SERVER["PHP_SELF"])); ?>">
    <tr>
        <td><?= $row['course_id'] ?><input name="course_id" type="hidden" value="<?= $row['course_id'] ?>"></td>
        <td><input name="title" required value="<?= $row['title'] ?>"></td>
        <td><input name="dept_name" required value="<?= $row['dept_name'] ?>"></td>
        <td><input name="credits" required value="<?= $row['credits'] ?>"></td>
        <td><input type="submit" name="update" value="Update"><td>
    </tr>
    </form>    
    <?php } else { ?>
    <tr>
        <td><?= $row['course_id'] ?></td>
        <td><?= $row['title'] ?></td>
        <td><?= $row['dept_name'] ?></td>
        <td><?= $row['credits'] ?></td>
        <td><a href="<?=htmlspecialchars(basename($_SERVER["PHP_SELF"])); ?>?del=<?= $row['course_id'] ?>"  style="color:red" title="Delete"><i class="bi bi-trash"></i></a> <a href="<?=htmlspecialchars(basename($_SERVER["PHP_SELF"])); ?>?update=<?= $row['course_id'] ?>" style="color:green" title="Update"><i class="bi bi-arrow-counterclockwise"></i></a></td>
    </tr>
    <?php } 
    endwhile; ?>
</table>
<?php
include "../footer.php";
?>
