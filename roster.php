<?php
require_once "../init.php";
check_login();
check_role('instructor');
include "../header.php";

// Initialize filter variables
$filter_course = $_POST['course_id'] ?? '';
$filter_sec = $_POST['sec_id'] ?? '';
$filter_semester = $_POST['semester'] ?? '';
$filter_year = $_POST['year'] ?? '';
$see_all = isset($_POST['see_all']);

// Fetch courses taught by instructor for the first dropdown
$stmt_courses = $conn->prepare("SELECT DISTINCT course_id FROM teaches WHERE ID=?");
$stmt_courses->bind_param("s", $_SESSION['ref_id']);
$stmt_courses->execute();
$result_courses = $stmt_courses->get_result();
$courses = [];
while($row = $result_courses->fetch_assoc()){
    $courses[] = $row['course_id'];
}

// Fetch sections for selected course
$sections = [];
if($filter_course){
    $stmt_sections = $conn->prepare("SELECT DISTINCT sec_id, semester, year FROM teaches WHERE ID=? AND course_id=? ORDER BY year DESC, semester DESC, sec_id ASC");
    $stmt_sections->bind_param("ss", $_SESSION['ref_id'], $filter_course);
    $stmt_sections->execute();
    $result_sections = $stmt_sections->get_result();
    while($row = $result_sections->fetch_assoc()){
        $sections[] = $row;
    }
}

// Build main roster query
$query = "
SELECT s.ID, s.name, t.course_id, t.sec_id, t.semester, t.year, t.grade 
FROM takes t 
JOIN student s ON t.ID = s.ID
JOIN teaches ts 
  ON ts.course_id = t.course_id 
  AND ts.sec_id = t.sec_id 
  AND ts.semester = t.semester 
  AND ts.year = t.year
WHERE ts.ID = ?";
$params = [$_SESSION['ref_id']];
$types = "s";

if(!$see_all && $filter_course && $filter_sec && $filter_semester && $filter_year){
    $query .= " AND t.course_id=? AND t.sec_id=? AND t.semester=? AND t.year=?";
    $types .= "sssi";
    $params[] = $filter_course;
    $params[] = $filter_sec;
    $params[] = $filter_semester;
    $params[] = $filter_year;
}

$stmt_roster = $conn->prepare($query);
$stmt_roster->bind_param($types, ...$params);
$stmt_roster->execute();
$result_roster = $stmt_roster->get_result();
?>

<h2>Section Roster</h2>

<form method="POST">
    Course: 
    <select name="course_id" onchange="this.form.submit()">
        <option value="">Select Course</option>
        <?php foreach($courses as $c): ?>
            <option value="<?= $c ?>" <?= $filter_course==$c?'selected':'' ?>><?= $c ?></option>
        <?php endforeach; ?>
    </select>

    Section: 
    <select name="sec_id" onchange="this.form.submit()">
        <option value="">Select Section</option>
        <?php foreach($sections as $s): ?>
            <option value="<?= $s['sec_id'] ?>" <?= $filter_sec==$s['sec_id']?'selected':'' ?>><?= $s['sec_id'] ?></option>
        <?php endforeach; ?>
    </select>

    Semester: 
    <select name="semester" onchange="this.form.submit()">
        <option value="">Select Semester</option>
        <?php foreach($sections as $s): 
            if($s['sec_id']==$filter_sec): ?>
                <option value="<?= $s['semester'] ?>" <?= $filter_semester==$s['semester']?'selected':'' ?>><?= $s['semester'] ?></option>
        <?php endif; endforeach; ?>
    </select>

    Year: 
    <select name="year" onchange="this.form.submit()">
        <option value="">Select Year</option>
        <?php foreach($sections as $s): 
            if($s['sec_id']==$filter_sec && $s['semester']==$filter_semester): ?>
                <option value="<?= $s['year'] ?>" <?= $filter_year==$s['year']?'selected':'' ?>><?= $s['year'] ?></option>
        <?php endif; endforeach; ?>
    </select>

    <input type="submit" name="see_all" value="See All">
</form>

<table border="1">
<tr>
    <th>Student ID</th>
    <th>Name</th>
    <th>Course</th>
    <th>Section</th>
    <th>Semester</th>
    <th>Year</th>
    <th>Grade</th>
</tr>
<?php while($row = $result_roster->fetch_assoc()): ?>
<tr>
    <td><?= $row['ID'] ?></td>
    <td><?= $row['name'] ?></td>
    <td><?= $row['course_id'] ?></td>
    <td><?= $row['sec_id'] ?></td>
    <td><?= $row['semester'] ?></td>
    <td><?= $row['year'] ?></td>
    <td><?= $row['grade'] ?></td>
</tr>
<?php endwhile; ?>
</table>
