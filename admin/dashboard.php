<?php
require_once "../init.php";
check_login();
check_role('admin');
include "../header.php";
?>

<h2>Admin Dashboard</h2>
<ul>
    <li><a href="manage_course.php">Manage Courses</a></li>
    <li><a href="manage_section.php">Manage Sections</a></li>
    <li><a href="manage_department.php">Manage Departments</a></li>
    <li><a href="manage_classroom.php">Manage Classrooms</a></li>
    <li><a href="manage_instructor.php">Manage Instructors</a></li>
    <li><a href="manage_student.php">Manage Students</a></li>
</ul>

<?php
include "../footer.php";
?>
