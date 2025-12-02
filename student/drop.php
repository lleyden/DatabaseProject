<?php
require_once "../init.php";
check_login();
check_role('student');

// ---------------- AJAX handler ----------------
if(isset($_GET['mode']) && $_GET['mode'] === 'sections' && isset($_GET['course_id'])){
    $course_id = $_GET['course_id'];
    $stmt = $conn->prepare("SELECT sec_id, semester, year FROM takes WHERE ID=? AND course_id=? ORDER BY year, semester, sec_id");
    $stmt->bind_param("ss", $_SESSION['ref_id'], $course_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $sections = [];
    while($row = $res->fetch_assoc()){
        $sections[] = $row;
    }
    header('Content-Type: application/json');
    echo json_encode($sections);
    exit; // Stop further output
}
// ---------------- END AJAX handler ----------------

include "../header.php";

// Handle dropping
if(isset($_POST['drop'])){
    $stmt = $conn->prepare("DELETE FROM takes WHERE ID=? AND course_id=? AND sec_id=? AND semester=? AND year=?");
    $stmt->bind_param("ssssi", $_SESSION['ref_id'], $_POST['course_id'], $_POST['sec_id'], $_POST['semester'], $_POST['year']);
    $stmt->execute();
    echo "<p id='dropMessage' style='color:red;'>Class dropped successfully!</p>";
}

// Fetch courses the student is currently taking
$stmt = $conn->prepare("SELECT DISTINCT course_id FROM takes WHERE ID=? ORDER BY course_id");
$stmt->bind_param("s", $_SESSION['ref_id']);
$stmt->execute();
$res = $stmt->get_result();
$course_list = [];
while($row = $res->fetch_assoc()){
    $course_list[] = $row['course_id'];
}
?>

<h2>Drop Classes</h2>

<form method="POST" id="dropForm" style="display:flex; gap:10px; align-items:center;">
    <label>Course:
        <select name="course_id" id="course_id" required>
            <option value="">Select Course</option>
            <?php foreach($course_list as $c) : ?>
                <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>Section:
        <select name="sec_id" id="sec_id" required>
            <option value="">Select Section</option>
        </select>
    </label>

    <label>Semester:
        <input type="text" name="semester" id="semester" readonly required>
    </label>

    <label>Year:
        <input type="number" name="year" id="year" readonly required>
    </label>

    <input type="submit" name="drop" value="Drop Class">
</form>

<script>
document.getElementById('course_id').addEventListener('change', function() {
    const course_id = this.value;
    const secSelect = document.getElementById('sec_id');
    const semesterInput = document.getElementById('semester');
    const yearInput = document.getElementById('year');

    // Clear old options
    secSelect.innerHTML = '<option value="">Select Section</option>';
    semesterInput.value = '';
    yearInput.value = '';

    if(course_id === '') return;

    fetch(`drop.php?mode=sections&course_id=${encodeURIComponent(course_id)}`)
        .then(response => response.json())
        .then(data => {
            data.forEach(section => {
                const option = document.createElement('option');
                option.value = section.sec_id;
                option.textContent = section.sec_id;
                option.dataset.semester = section.semester;
                option.dataset.year = section.year;
                secSelect.appendChild(option);
            });
        });
});

document.getElementById('sec_id').addEventListener('change', function() {
    const selected = this.selectedOptions[0];
    if(selected) {
        document.getElementById('semester').value = selected.dataset.semester;
        document.getElementById('year').value = selected.dataset.year;
    } else {
        document.getElementById('semester').value = '';
        document.getElementById('year').value = '';
    }
});

// Auto-hide success message after 3 seconds
window.onload = () => {
    const msg = document.getElementById('dropMessage');
    if(msg){
        setTimeout(() => msg.remove(), 3000);
    }
};
</script>
