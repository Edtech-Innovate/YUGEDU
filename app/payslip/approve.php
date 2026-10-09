<?php
ini_set('display_errors', 1);
if ( isset($_GET['slip_id'])) {
    require '../../includes/db-config.php';
    header('Content-Type: application/json');
    session_start();

    $slip_id = mysqli_real_escape_string($conn, $_GET['slip_id']);
    $student_ids = mysqli_real_escape_string($conn, $_GET['student_id']);
    $check = $conn->query("SELECT * FROM pay_slips WHERE student_id in ($student_ids)");
    if ($check->num_rows > 0) {
        $update = $conn->query("UPDATE pay_slips SET status=1 WHERE student_id in ($student_ids)");
      
        if ($check->num_rows > 1) {
            $pay_slip_id = $check->fetch_assoc()['pay_slip_id'];
            $update = $conn->query("UPDATE pay_slip_generation SET status=1 WHERE id = $pay_slip_id");
        }
        if ($update) {
            echo json_encode(['pay_slip_status' => 1, 'status' => 200, 'message' => 'The student\'s pay slip has been approved successfully.']);
        } else {
            echo json_encode(['pay_slip_status' => 0, 'status' => 302, 'message' => 'Something went wrong!']);
        }
    } else {
        echo json_encode(['status' => 302, 'message' => 'This Pay slip does not exists!']);
    }
}
