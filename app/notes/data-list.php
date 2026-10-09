<?php
## Database configuration
include '../../includes/db-config.php';
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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
  $orderby = "ORDER BY notes.id DESC";
}




// Admin Query
$query = "";
//$query = " AND sub_courses.University_ID = ".$_SESSION['university_id'];

## Search 
$searchQuery = " ";
if($searchValue != ''){
  $searchQuery = " AND (Syllabi.Name like '%".$searchValue."%' OR Sub_Courses.Name like '%".$searchValue."%' OR Sub_Courses.Short_Name like '%".$searchValue."%')";
}

$facultyQuery = "";
if($_SESSION['Role']=='Faculty')
{
    $facultyQuery = "AND Syllabi.Faculty_ID=".$_SESSION['ID'];
}
## Total number of records without filtering
$all_count=$conn->query("SELECT COUNT(id) as allcount FROM notes WHERE notes.status!=2 $query");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];

## Total number of record with filtering
$filter_count = $conn->query("SELECT COUNT(notes.id) as filtered FROM notes LEFT JOIN Sub_Courses ON Sub_Courses.ID = notes.course_id LEFT JOIN Syllabi ON Syllabi.ID = notes.subject_id WHERE notes.status!=2 $searchQuery $facultyQuery $query");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];

## Fetch records
$result_record = "SELECT notes.`id`, notes.`file_type`,notes.`title`, Sub_Courses.`Name` as course_name, Sub_Courses.`Short_Name` as course_short_name,Syllabi.`Code`, Syllabi.`Name` as subject_name, notes.`status` FROM notes LEFT JOIN Sub_Courses ON Sub_Courses.ID = notes.course_id LEFT JOIN Syllabi ON Syllabi.ID = notes.subject_id WHERE notes.status !=2 $facultyQuery  $searchQuery $query $orderby LIMIT ".$row.",".$rowperpage;


$results = mysqli_query($conn, $result_record);
$data = array();
while ($row = mysqli_fetch_assoc($results)) {

    $data[] = array( 
      "course_name" => $row["course_name"],
      "subject_name" => $row["subject_name"]."(".$row["Code"].")",
      "file_type" => $row["file_type"],
      "title"=>$row['title'],
      "status" => $row["status"],
      "ID" => $row["id"],
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
