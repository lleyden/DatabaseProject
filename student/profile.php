<?php
require_once "../init.php";
check_login();
check_role('student');
include "../header.php";

// Handle profile update
if(isset($_POST['update'])){
    $stmt = $conn->prepare("UPDATE student SET name=?, dept_name=? WHERE ID=?");
    $stmt->bind_param("sss", $_POST['name'], $_POST['dept_name'], $_SESSION['ref_id']);
    $stmt->execute();
    // Refresh values after update
    $name = $_POST['name'];
    $dept = $_POST['dept_name'];
}

// Get current student info
$stmt = $conn->prepare("SELECT name, dept_name FROM student WHERE ID=?");
$stmt->bind_param("s", $_SESSION['ref_id']);
$stmt->execute();
$stmt->bind_result($name, $dept);
$stmt->fetch();
$stmt->close();

// Get list of all departments
$dept_result = $conn->query("SELECT dept_name FROM department ORDER BY dept_name");
$departments = [];
while($row = $dept_result->fetch_assoc()){
    $departments[] = $row['dept_name'];
}
?>

<h2>Edit Profile</h2>
<form method="POST">
    <label>Name: <input name="name" value="<?= htmlspecialchars($name) ?>" required></label><br><br>
    <label>Department: 
        <select name="dept_name" required>
            <option value="">-- Select Department --</option>
            <?php foreach($departments as $d): ?>
                <option value="<?= htmlspecialchars($d) ?>" <?= $d === $dept ? 'selected' : '' ?>><?= htmlspecialchars($d) ?></option>
            <?php endforeach; ?>
        </select>
    </label><br><br>
    <input type="submit" name="update" value="Update">
</form>
