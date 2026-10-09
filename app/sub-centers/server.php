<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
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
  $orderby = "ORDER BY Users.ID ASC";
}

$university_query  = '';
if($_SESSION['Role']=='University Head'){
 // $university_query = " AND University_User.University_ID = ".$_SESSION['university_id'];
}elseif($_SESSION['Role']=='Center'){
  $university_query = " AND Center_SubCenter.Center = ".$_SESSION['ID'];
}
$center_query = '';
$counselloer_query = "";
$sub_center_query ="";
if ($_SESSION['Role'] == 'University Head') {
  
  $center_ids = [];
  $getcounselloer = $conn->query("SELECT User_ID FROM University_User WHERE Reporting = " . intval($_SESSION['ID']));
  if ($getcounselloer->num_rows > 0) {
      $centerids = $getcounselloer->fetch_all(MYSQLI_ASSOC);
      $center_ids = array_column($centerids, 'User_ID');
    if (!empty($center_ids)) {
      $center_ids_str = implode(',', array_map('intval', $center_ids)); 
      $counselloer_query = " AND Reporting IN ($center_ids_str) ";
      $getcounselloer = $conn->query("SELECT GROUP_CONCAT(University_User.User_ID) AS sc_ids FROM Users LEFT JOIN University_User ON Users.ID = University_User.User_ID WHERE 1=1 AND Role = 'Center' $counselloer_query");
      if ($getcounselloer->num_rows > 0) {
        $sc_row = $getcounselloer->fetch_assoc()['sc_ids'];
        $sub_center_query =" AND Center IN(".$sc_row.")"; 
        $getcenter = $conn->query("SELECT GROUP_CONCAT(Sub_Center) as sub_center_ids FROM Center_SubCenter WHERE 1=1 $sub_center_query");
        $getcenter = $getcenter->fetch_assoc()['sub_center_ids']??0;
        $center_query.=" AND Users.ID IN (".$getcenter.")";
      } 
    }
  }
}

## Search 
 $searchQuery = " ". $center_query;
if($searchValue != ''){
  $searchQuery .= " AND (Users.Name like '%".$searchValue."%' OR Users.Code like '%".$searchValue."%' OR Users.Email like '%".$searchValue."%' OR Users.Mobile like '%".$searchValue."%')";
}

$verticalQuery = "";
if($_SESSION['Role']!="Administrator"){
    $verticalQuery = " AND Users.vertical = ".($_SESSION['vertical'] ?? 1);
}

## Total number of records without filtering (Using COUNT(DISTINCT) to prevent duplicates)
$all_count= $conn->query("SELECT COUNT(DISTINCT Users.ID) as allcount FROM Users LEFT JOIN Center_SubCenter ON Users.ID = Center_SubCenter.Sub_Center LEFT JOIN University_User ON Users.ID = University_User.User_ID WHERE Role = 'Sub-Center' $university_query $verticalQuery");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];

## Total number of record with filtering
$filter_count = $conn->query("SELECT COUNT(DISTINCT Users.ID) as filtered FROM Users LEFT JOIN Center_SubCenter ON Users.ID = Center_SubCenter.Sub_Center  LEFT JOIN University_User ON Users.ID = University_User.User_ID LEFT JOIN Users as U1 ON Center_SubCenter.Center = U1.ID WHERE Users.Role = 'Sub-Center' $university_query $verticalQuery $searchQuery");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];

## Fetch records with GROUP BY Users.ID to ensure unique results
$result_record = "SELECT Users.`ID`, Users.`Name`, Users.`Email`, Users.`Mobile`, Users.`Code`, Users.`Status`, Users.`Photo`, CONCAT(U1.Name, ' (', U1.Code, ')') AS `Reporting`, CAST(AES_DECRYPT(Users.Password, '60ZpqkOnqn0UQQ2MYTlJ') AS CHAR(50)) password FROM Users LEFT JOIN Center_SubCenter ON Users.ID = Center_SubCenter.Sub_Center LEFT JOIN University_User ON Users.ID = University_User.User_ID LEFT JOIN Users as U1 ON Center_SubCenter.Center = U1.ID WHERE Users.Role = 'Sub-Center' $university_query $verticalQuery $searchQuery GROUP BY Users.ID $orderby LIMIT ".$row.",".$rowperpage;
$empRecords = mysqli_query($conn, $result_record);
$data = array();

while ($row = mysqli_fetch_assoc($empRecords)) {
  
  $admissions = $conn->query("SELECT COUNT(ID) as Applications FROM Students WHERE Added_For = ".$row['ID']."");
  $admissions = mysqli_fetch_assoc($admissions);

  $data[] = array( 
    "Photo"=> $row['Photo'],
    "Name" => $row['Name'],
    "Email" => $row['Email'],
    "Mobile" => $row['Mobile'],
    "Code" => $row['Code'],
    "Reporting" => $row['Reporting'],
    "Admission" => $admissions['Applications'],
    "Password" => $row['password'],
    "Status"  => $row["Status"],
    "ID"      => $row["ID"],
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