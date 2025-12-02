<?php
require_once "../init.php";
check_login();
check_role('instructor');
include "../header.php";

// Handle grade updates
if (isset($_POST['update_grade'])) {
    $stmt = $conn->prepare("UPDATE takes SET grade=? WHERE ID=? AND course_id=? AND sec_id=? AND semester=? AND year=?");
    $stmt->bind_param(
        "sssssi",
        $_POST['grade'],
        $_POST['student_id'],
        $_POST['course_id'],
        $_POST['sec_id'],
        $_POST['semester'],
        $_POST['year']
    );
    $stmt->execute();
}

// Fetch all courses taught by this instructor
$courses = $conn->prepare("
    SELECT DISTINCT course_id FROM teaches WHERE ID = ?
");
$courses->bind_param("s", $_SESSION['ref_id']);
$courses->execute();
$courses_result = $courses->get_result();
?>

<h2>Submit / Edit Grades</h2>

<form method="POST">
    <label>Course:</label>
    <select name="course_id" id="course_id" required onchange="this.form.submit()">
        <option value="">Select Course</option>
        <?php while($row = $courses_result->fetch_assoc()): ?>
            <option value="<?= $row['course_id'] ?>" <?= isset($_POST['course_id']) && $_POST['course_id'] == $row['course_id'] ? 'selected' : '' ?>>
                <?= $row['course_id'] ?>
            </option>
        <?php endwhile; ?>
    </select>

<?php
if (isset($_POST['course_id'])) {
    // Fetch sections, semesters, years for selected course
    $sections_stmt = $conn->prepare("
        SELECT sec_id, semester, year 
        FROM teaches 
        WHERE ID=? AND course_id=? 
        ORDER BY year DESC, semester DESC, sec_id
    ");
    $sections_stmt->bind_param("ss", $_SESSION['ref_id'], $_POST['course_id']);
    $sections_stmt->execute();
    $sections_result = $sections_stmt->get_result();
    $sections = [];
    while ($sec = $sections_result->fetch_assoc()) {
        $sections[] = $sec;
    }
    ?>
    <label>Section:</label>
    <select name="sec_id" required onchange="this.form.submit()">
        <option value="">Select Section</option>
        <?php foreach($sections as $sec): ?>
            <option value="<?= $sec['sec_id'] ?>" <?= isset($_POST['sec_id']) && $_POST['sec_id'] == $sec['sec_id'] ? 'selected' : '' ?>>
                <?= $sec['sec_id'] ?>
            </option>
        <?php endforeach; ?>
    </select>

    <?php if (isset($_POST['sec_id'])): 
        // Filter for selected section
        $filtered = array_filter($sections, fn($s) => $s['sec_id'] == $_POST['sec_id']);
        $sec_info = reset($filtered); ?>
        <label>Semester:</label>
        <select name="semester" required onchange="this.form.submit()">
            <option value="">Select Semester</option>
            <option value="<?= $sec_info['semester'] ?>" selected><?= $sec_info['semester'] ?></option>
        </select>

        <label>Year:</label>
        <select name="year" required onchange="this.form.submit()">
            <option value="">Select Year</option>
            <option value="<?= $sec_info['year'] ?>" selected><?= $sec_info['year'] ?></option>
        </select>
    <?php endif; ?>
<?php } ?>

<?php
// If all four fields are selected, show students and grade inputs
if (isset($_POST['course_id'], $_POST['sec_id'], $_POST['semester'], $_POST['year'])) {
    $students_stmt = $conn->prepare("
        SELECT s.ID, s.name, t.grade 
        FROM takes t
        JOIN student s ON t.ID = s.ID
        WHERE t.course_id=? AND t.sec_id=? AND t.semester=? AND t.year=?
        ORDER BY s.name
    ");
    $students_stmt->bind_param(
        "ssss",
        $_POST['course_id'],
        $_POST['sec_id'],
        $_POST['semester'],
        $_POST['year']
    );
    $students_stmt->execute();
    $students_result = $students_stmt->get_result();
    ?>

    <h3>Students in Section</h3>
    <table border="1" style="border-collapse:collapse;">
        <tr><th>Student ID</th><th>Name</th><th>Grade</th><th>Action</th></tr>
        <?php while ($row = $students_result->fetch_assoc()): ?>
        <tr>
            <form method="POST">
                <td><?= $row['ID'] ?></td>
                <td><?= $row['name'] ?></td>
                <td>
                    <input type="text" name="grade" value="<?= $row['grade'] ?>" placeholder="Enter grade">
                    <input type="hidden" name="student_id" value="<?= $row['ID'] ?>">
                    <input type="hidden" name="course_id" value="<?= $_POST['course_id'] ?>">
                    <input type="hidden" name="sec_id" value="<?= $_POST['sec_id'] ?>">
                    <input type="hidden" name="semester" value="<?= $_POST['semester'] ?>">
                    <input type="hidden" name="year" value="<?= $_POST['year'] ?>">
                </td>
                <td><input type="submit" name="update_grade" value="Update"></td>
            </form>
        </tr>
        <?php endwhile; ?>
    </table>

<?php } ?>
