<?php
require_once "../init.php";
check_login();
check_role('instructor');
include "../header.php";
?>

<h2>Instructor Dashboard</h2>
<ul>
    <li><a href="roster.php">View Section Roster</a></li>
    <li><a href="grade.php">Assign/Change Grades</a></li>
    <li><a href="advisee.php">View Advisee Students</a></li>
 <li><a href="instructor_analytics.php">View iunstructor Analytics</a></li>

</ul>
