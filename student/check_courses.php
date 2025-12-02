<?php
require_once "../init.php";
check_login();
check_role('student');
include "../header.php";

// Fetch all semesters from section table (unique, sorted)
$semester_result = $conn->query("SELECT DISTINCT semester, year FROM section ORDER BY year DESC, FIELD(semester,'Spring','Summer','Fall','Winter')");
$semesters = [];
while($row = $semester_result->fetch_assoc()){
    $semesters[] = $row['semester'] . ' ' . $row['year'];
}

$selected_semester = '';
$courses = [];

if(isset($_POST['check'])){
    $selected_semester = $_POST['semester'];
    list($semester, $year) = explode(' ', $selected_semester);

    // Fetch all courses offered in the selected semester
    $stmt = $conn->prepare("
        SELECT s.course_id, s.sec_id, s.semester, s.year, c.title,
        CASE
            WHEN t.ID IS NOT NULL THEN 'Registered'
            ELSE 'Not Registered'
        END AS status
        FROM section s
        JOIN course c ON s.course_id = c.course_id
        LEFT JOIN takes t ON t.course_id = s.course_id AND t.sec_id = s.sec_id AND t.semester = s.semester AND t.year = s.year AND t.ID = ?
        WHERE s.semester = ? AND s.year = ?
        ORDER BY c.course_id
    ");
    $stmt->bind_param("sss", $_SESSION['ref_id'], $semester, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()){
        $courses[] = $row;
    }
    $stmt->close();
}
?>

<h2>Check Courses by Semester</h2>

<form method="POST">
    <label>Semester:
        <select name="semester" required>
            <option value="">-- Select Semester --</option>
            <?php foreach($semesters as $s): ?>
                <option value="<?= htmlspecialchars($s) ?>" <?= $s === $selected_semester ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <input type="submit" name="check" value="Check Courses">
</form>

<?php if($selected_semester && $courses): ?>
    <h3>Courses for <?= htmlspecialchars($selected_semester) ?></h3>
    <table border="1">
        <tr>
            <th>Course ID</th>
            <th>Section</th>
            <th>Title</th>
            <th>Semester</th>
            <th>Year</th>
            <th>Status</th>
        </tr>
        <?php foreach($courses as $course): ?>
            <tr>
                <td><?= htmlspecialchars($course['course_id']) ?></td>
                <td><?= htmlspecialchars($course['sec_id']) ?></td>
                <td><?= htmlspecialchars($course['title']) ?></td>
                <td><?= htmlspecialchars($course['semester']) ?></td>
                <td><?= htmlspecialchars($course['year']) ?></td>
                <td><?= htmlspecialchars($course['status']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php elseif($selected_semester): ?>
    <p>No courses found for this semester.</p>
<?php endif; ?>
