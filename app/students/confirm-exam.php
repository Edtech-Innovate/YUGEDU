<?php
error_reporting(1);
require '../../includes/db-config.php';

if (isset($_POST['confirm']) && isset($_POST['student_id'])) {
    $checked = $_POST['confirm'] == 'on' ? true : false ; 
    $student = $_POST['student_id'];
    $exam_session = date('M') . '-' . date('y'); 
    $checkStudent = $conn->query("SELECT * , CASE WHEN attempt2_session IS NULL THEN '1' WHEN attempt3_session IS NULL THEN '2' WHEN attempt4_session IS NULL THEN '3' ELSE 'Attempt_completed' END AS attempts FROM `Examination_Confirmation` WHERE Student_Id = '$student'");
    if ($checkStudent->num_rows > 0) {
        /**
         * 1) In this case if data is came that mean it is re-appaer case
         * 2) According to exam-form condition and till attempt 3rd completed student is came here
         * 3) Also need to check that student appear for different session after the re-appear fee payment. 
         */
        $student_record = mysqli_fetch_assoc($checkStudent);
        $previous_attempt = $student_record['attempts'];
        $previous_attemptSessionName = "attempt".$previous_attempt."_session";
        $previous_attemptSession = $student_record[$previous_attemptSessionName];
        if ($exam_session == $previous_attemptSession) {
            exit(showResponse(false,"Fill Form In Next Session \n Aleardy Appear In This Session"));
        }
        $updateColumnName = "attempt".($previous_attempt+1)."_session";
        $update = $conn->query("UPDATE Examination_Confirmation SET $updateColumnName = '$exam_session' , updated_at = CURRENT_TIMESTAMP WHERE Student_Id = '$student'");
        showResponse($update);
    } else {
        $add = $conn->query("INSERT INTO `Examination_Confirmation`( `Student_Id`, `Confirmation_Status`, `attempt1_session`,`updated_at`) VALUES ('$student', '$checked','$exam_session',CURRENT_TIMESTAMP)");
        showResponse($add);
    }
} else {
    exit(showResponse(false));
}

function showResponse($response,$message = 'Something went wrong!') {
    if ($response) {
        echo json_encode(['status' => 200, 'message' => 'Thank You for Confirming!']);
    } else {
        echo json_encode(['status' => 400, 'message' => $message]);
    }
}