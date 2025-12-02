<?php
require_once "../init.php";
check_login();
check_role('student');
include "../header.php";

// Fetch all courses for dropdown
$course_result = $conn->query("SELECT course_id, title FROM course ORDER BY course_id");
$courses = [];
while($row = $course_result->fetch_assoc()){
    $courses[] = $row;
}

$selected_course = '';
$sections = [];

if(isset($_POST['check'])){
    $selected_course = $_POST['course_id'];

    // Fetch all sections of the selected course
    $stmt = $conn->prepare("
        SELECT s.sec_id, s.semester, s.year, s.building, s.room_number, s.time_slot_id,
        i.name AS instructor_name
        FROM section s
        LEFT JOIN teaches t ON t.course_id = s.course_id AND t.sec_id = s.sec_id AND t.semester = s.semester AND t.year = s.year
        LEFT JOIN instructor i ON t.ID = i.ID
        WHERE s.course_id = ?
        ORDER BY s.year DESC, FIELD(s.semester,'Spring','Summer','Fall','Winter'), s.sec_id
    ");
    $stmt->bind_param("s", $selected_course);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()){
        $sections[] = $row;
    }
    $stmt->close();
}
?>

<h2>Check Section Information</h2>

<form method="POST">
    <label>Course:
        <select name="course_id" required>
            <option value="">-- Select Course --</option>
            <?php foreach($courses as $c): ?>
                <option value="<?= htmlspecialchars($c['course_id']) ?>" <?= $c['course_id'] === $selected_course ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['course_id'] . ' - ' . $c['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <input type="submit" name="check" value="Check Sections">
</form>

<?php if($selected_course && $sections): ?>
    <h3>Sections for <?= htmlspecialchars($selected_course) ?></h3>
    <table border="1">
        <tr>
            <th>Section ID</th>
            <th>Semester</th>
            <th>Year</th>
            <th>Building</th>
            <th>Room</th>
            <th>Time Slot</th>
            <th>Instructor</th>
        </tr>
        <?php foreach($sections as $sec): ?>
            <tr>
                <td><?= htmlspecialchars($sec['sec_id']) ?></td>
                <td><?= htmlspecialchars($sec['semester']) ?></td>
                <td><?= htmlspecialchars($sec['year']) ?></td>
                <td><?= htmlspecialchars($sec['building']) ?></td>
                <td><?= htmlspecialchars($sec['room_number']) ?></td>
                <td><?= htmlspecialchars($sec['time_slot_id']) ?></td>
                <td><?= htmlspecialchars($sec['instructor_name'] ?? 'TBA') ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php elseif($selected_course): ?>
    <p>No sections found for this course.</p>
<?php endif; ?>
