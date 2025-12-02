<?php
require_once "../init.php";
check_login();
check_role('student');
include "../header.php";

// Fetch advisor(s) for the logged-in student
$stmt = $conn->prepare("
    SELECT i.ID AS advisor_id, i.name AS advisor_name, i.dept_name AS advisor_dept
    FROM advisor a
    JOIN instructor i ON a.i_ID = i.ID
    WHERE a.s_ID = ?
");
$stmt->bind_param("s", $_SESSION['ref_id']);
$stmt->execute();
$result = $stmt->get_result();

$advisors = [];
while($row = $result->fetch_assoc()){
    $advisors[] = $row;
}
$stmt->close();
?>

<h2>Your Advisor(s)</h2>

<?php if($advisors): ?>
    <table border="1">
        <tr>
            <th>Advisor ID</th>
            <th>Advisor Name</th>
            <th>Department</th>
        </tr>
        <?php foreach($advisors as $adv): ?>
            <tr>
                <td><?= htmlspecialchars($adv['advisor_id']) ?></td>
                <td><?= htmlspecialchars($adv['advisor_name']) ?></td>
                <td><?= htmlspecialchars($adv['advisor_dept']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p>You do not have an assigned advisor yet.</p>
<?php endif; ?>
