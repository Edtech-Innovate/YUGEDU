<?php
## Database configuration
include '../../includes/db-config.php';
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
    $orderby = "ORDER BY pay_slips.id desc";
}


## Search 
$searchQuery = " ";
if($searchValue != ''){
    // $searchQuery = " AND (Universities.Name like '%".$searchValue."%' OR Universities.Short_Name like '%".$searchValue."%' OR Universities.Vertical like '%".$searchValue."%')";
}

## Total number of records without filtering
$all_count=$conn->query("SELECT pay_slips.id FROM pay_slips left join pay_slip_generation on pay_slip_id = pay_slip_generation.id left join Students on Students.ID = pay_slips.student_id where 1=1 and pay_slips.university_id = '".$_SESSION['university_id']."' group by student_id");
// $records = mysqli_fetch_assoc($all_count);
$totalRecords = $all_count->num_rows;

## Total number of record with filtering
$filter_count = $conn->query("SELECT  pay_slips.id  FROM pay_slips left join pay_slip_generation on pay_slip_id = pay_slip_generation.id  left join Students on Students.ID = pay_slips.student_id where 1=1 and pay_slips.university_id = '".$_SESSION['university_id']."'  $searchQuery group by student_id");

// $records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $filter_count->num_rows;

## Fetch records
    $result_record = "SELECT pay_slips.university_fee, pay_slip_generation.date_of_generation , CONCAT(Users.Name, ' (', Users.Code, ')') AS user_name,serial_no,CONCAT(TRIM(CONCAT(Students.First_Name, ' ', Students.Middle_Name, ' ', Students.Last_Name)), ' (', IF(Students.Unique_ID='' OR Students.Unique_ID IS NULL, RIGHT(CONCAT('000000', Students.ID), 6), Students.Unique_ID), ')') as Unique_ID, Sub_Courses.Name as sub_course_name FROM pay_slips left join pay_slip_generation on pay_slip_id = pay_slip_generation.id left join Students on Students.ID = pay_slips.student_id left join Users on Users.ID = Students.Added_By left join Sub_Courses on Sub_Courses.ID = Students.Sub_Course_ID where 1=1 and pay_slips.university_id = '".$_SESSION['university_id']."' $searchQuery group by student_id $orderby  LIMIT ".$row.",".$rowperpage;
$empRecords = mysqli_query($conn, $result_record);
$data = array();

while ($row = mysqli_fetch_assoc($empRecords)) {

    $data[] = array( 
      "Unique_ID" => $row["Unique_ID"],
      "serial_no" => $row['serial_no'],
      "user_name" => $row['user_name'],
      "sub_course_name"=>$row['sub_course_name'],
      "university_fee"=> $row['university_fee'],
      "date_of_generation"=> date('d-m-Y', strtotime($row['date_of_generation'])),
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
