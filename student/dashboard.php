<?php
require_once "../init.php";
check_login();
check_role('student');
include "../header.php";
?>

<h2>Student Dashboard</h2>
<ul>
    <li><a href="register.php">Register for Classes</a></li>
    <li><a href="drop.php">Drop Classes</a></li>
    <li><a href="view_grades.php">View Grades</a></li>
    <li><a href="profile.php">Edit Profile</a></li>
</ul>
