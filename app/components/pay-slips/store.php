<?php
header('Content-Type: application/json');

if (isset($_POST['suffix'], $_POST['character'], $_POST['university_id'])) {
  require '../../../includes/db-config.php';

  $suffix = trim(mysqli_real_escape_string($conn, $_POST['suffix']));
  $character = trim(mysqli_real_escape_string($conn, $_POST['character']));
  $university_id = intval($_POST['university_id']);

  if ($suffix === '' || $character === '') {
    echo json_encode(['status' => 403, 'message' => 'All fields are mandatory!']);
    exit();
  }

  $query = "INSERT INTO `pay_slip_suffix` (`suffix`, `character`, `university_id`)  VALUES ('$suffix', '$character', $university_id)";
  if ($conn->query($query)) {
    echo json_encode(['status' => 200, 'message' => 'Pay Slip Serial No. generated successfully!']);
  } else {
    echo json_encode(['status' => 500, 'message' => 'Database error: ' . $conn->error]);
  }
} else {
  echo json_encode([
    'status' => 400,
    'message' => 'Invalid request! Missing required fields.'
  ]);
}

