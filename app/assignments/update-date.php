<?php


if (isset($_POST['subject_id']) && isset($_POST['stu_id']) && isset($_POST['enddate']) && isset($_POST['assignment_id'])) {
    require '../../includes/db-config.php';
    session_start();
    $stu_id = intval($_POST['stu_id']);
    $assignment_id = intval($_POST['assignment_id']);
    $subject_id = intval($_POST['subject_id']);
    $enddate = $_POST['enddate'];
    $check = $conn->query("select * from student_assignment_end_date where student_id= '$stu_id' and assignment_id = '$assignment_id' and subject_id = '$subject_id'");
    if ($check->num_rows > 0) {
        $add = $conn->query("update student_assignment_end_date set enddate = '$enddate' where student_id= '$stu_id' and assignment_id = '$assignment_id' and subject_id = '$subject_id'");
    } else {
        $add = $conn->query("INSERT INTO `student_assignment_end_date`(`student_id`, `assignment_id`, `subject_id`, `enddate`) VALUES ('$stu_id', '$assignment_id', $subject_id, '$enddate')");
    }

    if ($add) {
        echo json_encode(['status' => 200, 'message' => 'Assignment end date has been successfully extended.']);
    } else {
        echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
    }
} else {
    echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
}
?>