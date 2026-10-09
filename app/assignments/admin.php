<?php
## Database configuration

include '../../includes/db-config.php';
session_start();
## Read value
$draw = $_POST['draw'];
// print_r($draw);
$row = $_POST['start'];
$rowperpage = $_POST['length'];
if (isset($_POST['order'])) {
  $columnIndex = $_POST['order'][0]['column'];
  $columnName = $_POST['columns'][$columnIndex]['data'];
  $columnSortOrder = $_POST['order'][0]['dir'];
}

if (isset($_SESSION['current_session'])) {
  if ($_SESSION['current_session'] == 'All') {
    $session_query = '';
  } else {
    $session_query = "AND Admission_Sessions.Name like '%" . $_SESSION['current_session'] . "%'";
  }
} else {
  $get_current_session = $conn->query("SELECT Name FROM Admission_Sessions WHERE Current_Status = 1 AND University_ID = '" . $_SESSION['university_id'] . "'");
  if ($get_current_session->num_rows > 0) {
    $gsc = mysqli_fetch_assoc($get_current_session);
    $session_query = "AND Admission_Sessions.Name like '%" . $gsc['Name'] . "%'";
  } else {
    $session_query = '';
  }
}

$filterByprogram ="";
// echo "<pre>"; print_r($_SESSION);die;
if(isset($_SESSION['assign_sub_course_ID'])){
  $filterByprogram = " AND  student_assignment.sub_course_id = ".$_SESSION['assign_sub_course_ID'];
}




$searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']);
if (isset($columnSortOrder)) {
  $orderby = "ORDER BY $columnName $columnSortOrder";
} else {
  $orderby = "ORDER BY student_assignment.Assignment_id ASC";
}
// Admin Query
$query = "";
## Search 
$searchQuery = " ";
if ($searchValue != '') {
  $searchQuery = " AND (Syllabi.Name like '%" . $searchValue . "%' OR Sub_Courses.Name like '%" . $searchValue . "%' OR Courses.Name like '%" . $searchValue . "%')";
}
$uniQuery ="";
$uniQuery = " AND Sub_Courses.University_ID =". $_SESSION['university_id'];

## Total number of records without filtering
$all_count = $conn->query("SELECT student_assignment.Assignment_id  FROM 
student_assignment
LEFT JOIN 
Courses ON student_assignment.course_id = Courses.ID
LEFT JOIN 
Sub_Courses ON student_assignment.sub_course_id = Sub_Courses.ID
LEFT JOIN 
Syllabi ON student_assignment.subject_id = Syllabi.ID 
LEFT JOIN
 Admission_Sessions ON student_assignment.adm_session = Admission_Sessions.ID 
 where student_assignment.Assignment_id != 2 $uniQuery  $query");
//$records = mysqli_fetch_assoc($all_count);
//$totalRecords = $records['allcount'];
$totalRecords = $all_count->num_rows;
## Total number of record with filtering
$filter_count = $conn->query("SELECT student_assignment.Assignment_id FROM 
student_assignment
LEFT JOIN 
Courses ON student_assignment.course_id = Courses.ID
LEFT JOIN 
Sub_Courses ON student_assignment.sub_course_id = Sub_Courses.ID
LEFT JOIN 
Syllabi ON student_assignment.subject_id = Syllabi.ID 
LEFT JOIN
 Admission_Sessions ON student_assignment.adm_session = Admission_Sessions.ID 
 where student_assignment.Assignment_id!=2 $searchQuery $uniQuery  $session_query  $filterByprogram  $query");
//$records = mysqli_fetch_assoc($filter_count);
//$totalRecordwithFilter = $records['filtered'];
$totalRecordwithFilter = $filter_count->num_rows;


## Fetch records
$result_record = "SELECT 
Admission_Sessions.Name as adm_session, 
Courses.Short_Name AS course_name, 
Sub_Courses.Name AS sub_course_name, 
Syllabi.Name AS subject_name,
student_assignment.semester,
student_assignment.assignment_name,
student_assignment.start_date,
student_assignment.end_date ,
student_assignment.created_by ,
student_assignment.marks,
student_assignment.updated_date ,
student_assignment.created_date,
student_assignment.file_path,
student_assignment.Assignment_id 
FROM 
student_assignment
LEFT JOIN 
Courses ON student_assignment.course_id = Courses.ID
LEFT JOIN 
Sub_Courses ON student_assignment.sub_course_id = Sub_Courses.ID
LEFT JOIN 
Syllabi ON student_assignment.subject_id = Syllabi.ID 
LEFT JOIN
 Admission_Sessions ON student_assignment.adm_session = Admission_Sessions.ID 
 where student_assignment.Assignment_id !=2 $searchQuery $session_query  $filterByprogram $query $uniQuery  $orderby LIMIT " . $row . "," . $rowperpage;

// echo $result_record; die;
// print_r($result_record);die;
$results = mysqli_query($conn, $result_record);
$data = array();
while ($row = mysqli_fetch_assoc($results)) {
  $data[] = array(
    "course_name" => $row["course_name"],
    "adm_session" => $row["adm_session"],
    "sub_course_name" => $row["sub_course_name"].' ('.$row['course_name'].')',
    "subject_name" => $row["subject_name"],
    "semester" => $row["semester"],
    "assignment_name" => $row["assignment_name"],
    "start_date" => $row["start_date"],
    "end_date" => $row["end_date"],
    "created_by" => $row["created_by"],
    "marks" => $row["marks"],
    "updated_date" => $row["updated_date"],
    "created_date" => $row["created_date"],
    "file_path" => $row["file_path"],
    "Assignment_id" => $row["Assignment_id"],
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
