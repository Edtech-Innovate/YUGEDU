<?php
## Database configuration
include '../../includes/db-config.php';
session_start();
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
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
  $orderby = "ORDER BY Syllabi.ID DESC";
}



$filterByUsers = "";
if (isset($_SESSION['usersFilter'])) {
  $filterByUsers = $_SESSION['usersFilter'];
}

$filterBysubCourse = "";
if (isset($_SESSION['subCourseFilter'])) {
  $filterBysubCourse = $_SESSION['subCourseFilter'];
}

$filterByDuration = "";
if (isset($_SESSION['durationFilter'])) {
  $filterByDuration = $_SESSION['durationFilter'];
}

## Search 
$searchQuery = " ";
if($searchValue != ''){
  $searchQuery = " AND (Exam_Sessions.Name like '%".$searchValue."%' OR Syllabi.Name like '%".$searchValue."%' OR Syllabi.Code like '%".$searchValue."%' OR Syllabi.Paper_Type like '%".$searchValue."%' OR Sub_Courses.Name like '%".$searchValue."%')";
}

$filterByUniversity = " AND Date_Sheets.University_ID =".$_SESSION['university_id'];
$searchQuery .= $filterByUniversity. $filterBysubCourse.$filterByDuration .$filterByUsers;


## Total number of records without filtering
$all_count=$conn->query("SELECT COUNT(Date_Sheets.ID) as allcount FROM Date_Sheets LEFT JOIN Syllabi ON Date_Sheets.Syllabus_ID = Syllabi.ID LEFT JOIN Exam_Sessions ON Date_Sheets.Exam_Session_ID = Exam_Sessions.ID LEFT JOIN Sub_Courses on Syllabi.Sub_Course_ID =  Sub_Courses.ID  WHERE 1=1 $searchQuery");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];

## Total number of record with filtering
$filter_count = $conn->query("SELECT COUNT(Date_Sheets.ID) as filtered FROM Date_Sheets LEFT JOIN Syllabi ON Date_Sheets.Syllabus_ID = Syllabi.ID LEFT JOIN Exam_Sessions ON Date_Sheets.Exam_Session_ID = Exam_Sessions.ID LEFT JOIN Sub_Courses on Syllabi.Sub_Course_ID =  Sub_Courses.ID  WHERE 1= 1  $searchQuery");

$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];

## Fetch records
$result_record = "SELECT Date_Sheets.*, Exam_Sessions.Name as Exam_Session, Syllabi.Name as subject_name, Syllabi.Code,Syllabi.Semester,Sub_Courses.Name AS sub_course_name FROM Date_Sheets LEFT JOIN Syllabi ON Date_Sheets.Syllabus_ID = Syllabi.ID LEFT JOIN Exam_Sessions ON Date_Sheets.Exam_Session_ID = Exam_Sessions.ID LEFT JOIN Sub_Courses on Syllabi.Sub_Course_ID =  Sub_Courses.ID  WHERE  1= 1  $searchQuery  $orderby LIMIT ".$row.",".$rowperpage;

// $result_record = "SELECT Syllabi.ID,Syllabi.Semester, Syllabi.Name as subject_name, Syllabi.Code,Sub_Courses.Name AS sub_course_name, Syllabi.User_ID , Min_Marks, Max_Marks, Paper_Type, Credit FROM Syllabi LEFT JOIN Sub_Courses on Syllabi.Sub_Course_ID =  Sub_Courses.ID WHERE 1=1  $searchQuery  $orderby LIMIT ".$row.",".$rowperpage;
$empRecords = mysqli_query($conn, $result_record);
$data = array();
// echo $result_record; die;

while ($row = mysqli_fetch_assoc($empRecords)) {

    $data[] = array( 
      "ID"=> $row['ID'],
      "subject_name" => $row['subject_name'],
      "sub_course_name" => ucwords(strtolower($row['sub_course_name'])),
      "Exam_Session" => $row['Exam_Session'],
      "Code" => $row['Code'],
      "Semester"      => $row["Semester"],
      "exam_date"=> date("l, dS M, Y", strtotime($row['Exam_Date'])),
      "exam_time"=> date("h:i A", strtotime($row['Start_Time'])) . " to " . date("h:i A", strtotime($row['End_Time']))

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
