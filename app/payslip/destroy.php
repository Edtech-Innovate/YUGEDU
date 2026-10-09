<?php

if ($_SERVER['REQUEST_METHOD'] == 'DELETE' && isset($_GET['slip_id'])) {
  require '../../includes/db-config.php';
  header('Content-Type: application/json');

  // $slip_id = mysqli_real_escape_string($conn, $_GET['slip_id']);
   $student_id = mysqli_real_escape_string($conn, $_GET['student_id']);

  $check = $conn->query("SELECT * FROM pay_slips WHERE student_id in ($student_id)");
  if ($check->num_rows > 0) {
    $delete = $conn->query("DELETE FROM pay_slips WHERE student_id in ($student_id)");
     if ($check->num_rows > 1) {
        $pay_slip_id = $check->fetch_assoc()['pay_slip_id'];
        $delete = $conn->query("DELETE FROM pay_slip_generation WHERE id =  $pay_slip_id");
    }
    if ($delete) {
      echo json_encode(['status' => 200, 'message' => 'This Student Pay Slip has been rejected successfully!']);
    } else {
      echo json_encode(['status' => 302, 'message' => 'Something went wrong!']);
    }
  } else {
    echo json_encode(['status' => 302, 'message' => 'This Pay slip does not exists!']);
  }
}
