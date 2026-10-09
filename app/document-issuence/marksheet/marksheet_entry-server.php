<?php
## Database configuration
include '../../../includes/db-config.php';
session_start();
## Read value
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
if(isset($_POST['order'])){
  $columnIndex = $_POST['order'][0]['column']; // Column index
  $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
  $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
}
$searchValue = mysqli_real_escape_string($conn,$_POST['search']['value']); // Search value

if(isset($columnSortOrder)){
  $orderby = "ORDER BY $columnName $columnSortOrder";
}else{
  $orderby = "ORDER BY MarkSheet_Entry.ID ASC";
}

## Search 
$searchQuery = "";
if($searchValue != '') {
  $searchQuery = " MarkSheet_Entry.Enrollment_No LIKE '%$searchValue%' OR MarkSheet_Entry.Marksheet_No LIKE '%$searchValue%' OR MarkSheet_Entry.Duration LIKE '%$searchValue%'";
}

## Total number of records without filtering
$all_count=$conn->query("SELECT COUNT(ID) as allcount FROM `MarkSheet_Entry`");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];

## Total number of record with filtering
$filter_count = $conn->query("SELECT COUNT(ID) as filtered FROM `MarkSheet_Entry` where 1=1 $searchQuery");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];

$data = [];
## Fetch records
$university_id = $_SESSION['university_id'];
// print_r($_SESSION);die;
$marksheetRecords = $conn->query("SELECT MarkSheet_Entry.* , MarkSheet_Entry.Duration as 'duration_concat' , Students.Unique_ID as `Student_id`  FROM `MarkSheet_Entry` LEFT JOIN Students ON Students.Enrollment_No = MarkSheet_Entry.Enrollment_No where 1=1 and Students.University_ID='$university_id' $searchQuery $orderby LIMIT $row, $rowperpage");

while ($row = mysqli_fetch_assoc($marksheetRecords)) {
  $data[] = array(
    'ID' => $row['ID'],
    'Enrollment_No' => $row['Enrollment_No'],
    'Student_id' => $row['Student_id'],
    "Marksheet_No" => $row['Marksheet_No'],
    "Exam_session" => $row['Exam_Session'],
    "Duration" => $row['duration_concat'],
    "Docket_Id" => $row['Docket_Id'],
    "Dispatch_status" =>$row['Dispatch_status']
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

?>