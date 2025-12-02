<?php
require_once "../init.php";
check_login();
check_role('instructor');

function grade_to_numeric($grade){
    $grades = [
        'A'=>4, 'A-'=>3.7, 'B+'=>3.3, 'B'=>3, 'B-'=>2.7,
        'C+'=>2.3, 'C'=>2, 'C-'=>1.7, 'D+'=>1.3, 'D'=>1, 'F'=>0
    ];
    return $grades[$grade] ?? null;
}

// ------------------- AJAX HANDLER -------------------
if(isset($_GET['action'])){
    $action = $_GET['action'];

    if($action === "avg_dept"){
        $dept = $_GET['dept'];
        $stmt = $conn->prepare("
            SELECT grade FROM takes t
            JOIN student s ON t.ID = s.ID
            WHERE s.dept_name = ?
        ");
        $stmt->bind_param("s",$dept);
        $stmt->execute();
        $result = $stmt->get_result();
        $total = 0; $count=0;
        while($row=$result->fetch_assoc()){
            $num = grade_to_numeric($row['grade']);
            if($num!==null){ $total+=$num; $count++; }
        }
        echo $count ? round($total/$count,2) : "N/A";
        exit();
    }

    if($action === "avg_class"){
        $course = $_GET['course'];
        $semesters = $_GET['semesters'];
        $semestersArr = explode(",", $semesters);
        $placeholders = implode(",", array_fill(0,count($semestersArr),"?"));
        $types = str_repeat("s", count($semestersArr));
        $stmt = $conn->prepare("
            SELECT grade FROM takes WHERE course_id=? AND semester IN ($placeholders)
        ");
        $stmt->bind_param("s".$types, $course, ...$semestersArr);
        $stmt->execute();
        $result = $stmt->get_result();
        $total=0; $count=0;
        while($row=$result->fetch_assoc()){
            $num = grade_to_numeric($row['grade']);
            if($num!==null){ $total+=$num; $count++; }
        }
        echo $count ? round($total/$count,2) : "N/A";
        exit();
    }

    if($action === "best_worst"){
        $semester = $_GET['semester'];
        $stmt = $conn->prepare("SELECT course_id, grade FROM takes WHERE semester=?");
        $stmt->bind_param("s",$semester);
        $stmt->execute();
        $result = $stmt->get_result();
        $grades = [];
        while($row=$result->fetch_assoc()){
            $num = grade_to_numeric($row['grade']);
            if($num!==null){ $grades[$row['course_id']][]=$num; }
        }
        $averages=[];
        foreach($grades as $c=>$arr){ $averages[$c]=array_sum($arr)/count($arr); }
        if(empty($averages)){ echo "No data"; }
        else{
            $best = array_keys($averages,max($averages))[0];
            $worst = array_keys($averages,min($averages))[0];
            echo "Best: $best, Worst: $worst";
        }
        exit();
    }

    if($action === "tot_students"){
        $dept = $_GET['dept'];
        $stmt = $conn->prepare("SELECT COUNT(*) FROM student WHERE dept_name=?");
        $stmt->bind_param("s",$dept);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        echo $count;
        exit();
    }

    if($action === "curr_students"){
        $dept = $_GET['dept'];
        $stmt = $conn->prepare("
            SELECT COUNT(DISTINCT t.ID) 
            FROM takes t
            JOIN student s ON t.ID=s.ID
            WHERE s.dept_name=?
        ");
        $stmt->bind_param("s",$dept);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        echo $count;
        exit();
    }

    if($action === "get_semesters"){
        $course = $_GET['course'];
        $res = $conn->query("SELECT DISTINCT semester FROM takes WHERE course_id='$course'");
        $semesters=[];
        while($row=$res->fetch_assoc()){ $semesters[]=$row['semester']; }
        echo json_encode($semesters);
        exit();
    }
}

// ------------------- NORMAL PAGE -------------------
include "../header.php";

// Dropdowns
$departments = $conn->query("SELECT dept_name FROM department");
$courses = $conn->query("SELECT course_id FROM course");
?>

<h2 style="font-weight:bold;">Instructor Analytics Portal</h2>

<h3>Average Grade by Department</h3>
<select id="avg_dept">
    <option value="">Select Dept</option>
    <?php while($row = $departments->fetch_assoc()): ?>
        <option value="<?= $row['dept_name'] ?>"><?= $row['dept_name'] ?></option>
    <?php endwhile; ?>
</select>
<button id="avg_dept_btn">Check</button>
<span id="avg_dept_result"></span>

<h3>Average Grade of a Class (Select Semesters)</h3>
<select id="avg_class_course">
    <option value="">Select Course</option>
    <?php
    $courses->data_seek(0);
    while($row=$courses->fetch_assoc()): ?>
        <option value="<?= $row['course_id'] ?>"><?= $row['course_id'] ?></option>
    <?php endwhile; ?>
</select>
<div id="avg_class_semesters"></div>
<button id="avg_class_btn">Check</button>
<span id="avg_class_result"></span>

<h3>Best and Worst Classes for Semester</h3>
<select id="bestworst_sem">
    <option value="">Select Semester</option>
    <option>Fall</option><option>Winter</option><option>Spring</option><option>Summer</option>
</select>
<button id="bestworst_btn">Check</button>
<span id="bestworst_result"></span>

<h3>Total Students by Department</h3>
<select id="tot_students_dept">
    <option value="">Select Dept</option>
    <?php
    $departments->data_seek(0);
    while($row=$departments->fetch_assoc()): ?>
        <option value="<?= $row['dept_name'] ?>"><?= $row['dept_name'] ?></option>
    <?php endwhile; ?>
</select>
<button id="tot_students_btn">Check</button>
<span id="tot_students_result"></span>

<h3>Currently Enrolled Students by Department</h3>
<select id="curr_students_dept">
    <option value="">Select Dept</option>
    <?php
    $departments->data_seek(0);
    while($row=$departments->fetch_assoc()): ?>
        <option value="<?= $row['dept_name'] ?>"><?= $row['dept_name'] ?></option>
    <?php endwhile; ?>
</select>
<button id="curr_students_btn">Check</button>
<span id="curr_students_result"></span>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function(){
    // Avg grade by dept
    $("#avg_dept_btn").click(function(){
        let dept = $("#avg_dept").val();
        if(!dept) return alert("Select department");
        $.get("<?= $_SERVER['PHP_SELF'] ?>",{action:"avg_dept", dept:dept},function(data){
            $("#avg_dept_result").text("Average grade: "+data);
        });
    });

    // Populate semesters checkboxes dynamically for selected course
    $("#avg_class_course").change(function(){
        let course = $(this).val();
        $("#avg_class_semesters").html("");
        if(course){
            $.get("<?= $_SERVER['PHP_SELF'] ?>",{action:"get_semesters", course:course},function(data){
                let semesters = JSON.parse(data);
                semesters.forEach(s=>{
                    $("#avg_class_semesters").append(
                        '<label><input type="checkbox" name="semesters" value="'+s+'"> '+s+'</label> '
                    );
                });
            });
        }
    });

    // Average grade for class
    $("#avg_class_btn").click(function(){
        let course = $("#avg_class_course").val();
        let semesters = [];
        $("#avg_class_semesters input:checked").each(function(){ semesters.push($(this).val()); });
        if(!course || semesters.length===0) return alert("Select course and at least one semester");
        $.get("<?= $_SERVER['PHP_SELF'] ?>",{action:"avg_class", course:course, semesters:semesters.join(",")},function(data){
            $("#avg_class_result").text("Average grade: "+data);
        });
    });

    // Best/worst classes
    $("#bestworst_btn").click(function(){
        let sem = $("#bestworst_sem").val();
        if(!sem) return alert("Select semester");
        $.get("<?= $_SERVER['PHP_SELF'] ?>",{action:"best_worst", semester:sem},function(data){
            $("#bestworst_result").text(data);
        });
    });

    // Total students
    $("#tot_students_btn").click(function(){
        let dept = $("#tot_students_dept").val();
        if(!dept) return alert("Select department");
        $.get("<?= $_SERVER['PHP_SELF'] ?>",{action:"tot_students", dept:dept},function(data){
            $("#tot_students_result").text(data);
        });
    });

    // Currently enrolled students
    $("#curr_students_btn").click(function(){
        let dept = $("#curr_students_dept").val();
        if(!dept) return alert("Select department");
        $.get("<?= $_SERVER['PHP_SELF'] ?>",{action:"curr_students", dept:dept},function(data){
            $("#curr_students_result").text(data);
        });
    });
});
</script>

<style>
h2 { font-weight:bold; margin-bottom:20px; }
h3 { font-weight:normal; margin-top:20px; }
select, button { margin-right:10px; margin-top:5px; margin-bottom:10px; }
span { margin-left:10px; font-weight:bold; }
label { margin-right:10px; }
</style>
