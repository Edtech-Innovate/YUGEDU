<?php

if (isset($_POST['suffix'], $_POST['character'], $_POST['university_id'])) {
  require '../../../includes/db-config.php';

  $suffix = trim(mysqli_real_escape_string($conn, $_POST['suffix']));
  $character = trim(mysqli_real_escape_string($conn, $_POST['character']));
  $university_id = intval($_POST['university_id']);
  $id = intval($_POST['id']);

  if ($suffix === '' || $character === '') {
    echo json_encode(['status' => 403, 'message' => 'All fields are mandatory!']);
    exit();
  }


  $add = $conn->query("UPDATE `pay_slip_suffix` SET `character` = '$character', `suffix` = '$suffix' WHERE id = $id");
  if ($add) {
    echo json_encode(['status' => 200, 'message' => 'Pay-slip updated successfully!']);
  } else {
    echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
  }
}
?>