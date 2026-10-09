<?php
require '../../../includes/db-config.php';
session_start();

$id = mysqli_real_escape_string($conn, $_POST['id']);
$discount = mysqli_real_escape_string($conn, $_POST['discount']);
$studentDuration = mysqli_real_escape_string($conn, $_POST['studentDuration']);
$university = mysqli_real_escape_string($conn, $_POST['University_ID']);

// Update discount
$ledgerQuery = $conn->query("
    UPDATE Student_Ledgers 
    SET Discount = '{$discount}'
    WHERE Student_ID = '{$id}'
      AND Status = 1
      AND Duration <= '{$studentDuration}'
");

// Response
if ($ledgerQuery) {
    echo json_encode(['status' => 200, 'message' => 'Discount updated successfully!']);
} else {
    echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
}
?>
