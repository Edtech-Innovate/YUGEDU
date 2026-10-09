<?php
## Database configuration
include '../../includes/db-config.php';
session_start();
## Read value
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
if (isset($_POST['order'])) {
  $columnIndex = $_POST['order'][0]['column']; // Column index
  $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
  $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
}
$searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']); // Search value

if (isset($columnSortOrder)) {
  $orderby = "ORDER BY $columnName $columnSortOrder";
} else {
  $orderby = "ORDER BY pg.id desc";
}


## Search 
$searchQuery = " ";
if ($searchValue != '') {
  // $searchQuery = " AND (Universities.Name like '%".$searchValue."%' OR Universities.Short_Name like '%".$searchValue."%' OR Universities.Vertical like '%".$searchValue."%')";
}

## Total number of records without filtering
$all_count = $conn->query("  SELECT pg.*, ps.status as slip_status, ps.id as slip_id, GROUP_CONCAT(ps.student_id) AS student_ids, COUNT(ps.student_id) AS student_count FROM pay_slip_generation pg  LEFT JOIN   pay_slips ps ON ps.pay_slip_id = pg.id WHERE   1=1 and student_id IS not null  AND pg.university_id = '" . $_SESSION['university_id'] . "' GROUP BY  pg.id ");
$totalRecords = $all_count->num_rows;

## Total number of record with filtering
$filter_count = $conn->query("  SELECT pg.*, ps.status as slip_status, ps.id as slip_id, GROUP_CONCAT(ps.student_id) AS student_ids, COUNT(ps.student_id) AS student_count FROM pay_slip_generation pg  LEFT JOIN   pay_slips ps ON ps.pay_slip_id = pg.id WHERE   1=1 and student_id IS not null  AND pg.university_id = '" . $_SESSION['university_id'] . "' GROUP BY  pg.id  $searchQuery ");


$totalRecordwithFilter = $filter_count->num_rows;

## Fetch records
$result_record = "
  SELECT pg.*, ps.status as slip_status, ps.id as slip_id, GROUP_CONCAT(ps.student_id) AS student_ids, COUNT(ps.student_id) AS student_count FROM pay_slip_generation pg  LEFT JOIN   pay_slips ps ON ps.pay_slip_id = pg.id WHERE   1=1 and student_id IS not null  AND pg.university_id = '" . $_SESSION['university_id'] . "' GROUP BY  pg.id $orderby LIMIT " . intval($row) . "," . intval($rowperpage);


$empRecords = mysqli_query($conn, $result_record);
$data = array();

while ($row = mysqli_fetch_assoc($empRecords)) {
    $student_ids = $row['student_ids'];
    $stuIds = !empty($student_ids) ? explode(",", $student_ids) : [];

  $data[] = array(
    "id" => $row['id'],
    "slip_id"=>$row['slip_id'],
    "serial_no" => $row['serial_no'],
    "slip_status" => $row['slip_status'],
    "genration_status" => $row['status'],
    "bank" => $row['bank'],
    "payment_mode" => $row['payment_mode'],
    "bank_transication_no" => $row['bank_transication_no'],
    "uni_amount"=> $row['total_university_fee'],
    "university_fee" => "&#8377; " . number_format($row['total_university_fee'], 2),
    "student_count" => $row['student_count'],
    "student_ids" => $student_ids,
    "date_of_payment" => ($row['date_of_payment'] != '' || $row['date_of_payment'] != null) ? date('d-m-Y', strtotime($row['date_of_payment'])) : "",
    "date_of_generation" => date('d-m-Y', strtotime($row['date_of_generation'])),
  );
}

## Response
$response = array(
  "draw" => intval($draw),
  "iTotalRecords" => $totalRecords,
  "iTotalDisplayRecords" => $totalRecordwithFilter,
  "aaData" => $data
);

echo json_encode($response);
