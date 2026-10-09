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
  $orderby = "ORDER BY Users.ID DESC, University_User.Level DESC";
}

// Session Query
$session_query = "";
$current_session = $conn->query("SELECT ID FROM Admission_Sessions WHERE University_ID = ".$_SESSION['university_id']." AND Current_Status = 1");
if($current_session->num_rows>0){
  $current_session = mysqli_fetch_assoc($current_session);
  $session_query = " AND Admission_Session_ID = ".$current_session['ID'];
}

$center_query = "";
if($_SESSION['Role']!="Administrator"){
  $check_has_unique_center_code = $conn->query("SELECT Center_Suffix FROM Universities WHERE ID = ".$_SESSION['university_id']." AND Has_Unique_Center = 1");
  if($check_has_unique_center_code->num_rows>0){
    $center_suffix = mysqli_fetch_assoc($check_has_unique_center_code);
    $center_suffix = $center_suffix['Center_Suffix'];
    $center_query = " AND Users.Code LIKE '$center_suffix%' AND Users.Is_Unique = 1";
  }else{
    $center_query = " AND Users.Is_Unique = 0";
  }
}

if ($_SESSION['Role'] == 'Sub-Counsellor' || $_SESSION['Role'] == 'Counsellor') {
  $center_ids = [];
  $user_ids = [];
  $getcenterids = $conn->query("SELECT User_ID FROM University_User WHERE Reporting = " . intval($_SESSION['ID']));
  if ($getcenterids->num_rows > 0) {
      $centerids = $getcenterids->fetch_all(MYSQLI_ASSOC);
      $center_ids = array_column($centerids, 'User_ID');
  }
  $getnotalloteduser = $conn->query("SELECT Users.ID FROM Users LEFT JOIN University_User ON Users.ID = University_User.User_ID WHERE Users.Role = 'Center' AND University_User.User_ID IS NULL");
  if ($getnotalloteduser->num_rows > 0) {
      while ($dataArr = $getnotalloteduser->fetch_assoc()) {
          $user_ids[] = $dataArr['ID'];
      }
  }
  $centers = array_merge($center_ids, $user_ids);
  if (!empty($centers)) {
      $center_ids_str = implode(',', array_map('intval', $centers)); // Secure against SQL injection
      $center_query .= " AND Users.ID IN ($center_ids_str) ";
  }
}

$counselloer_query = "";
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
        if (!empty($sc_row)) {
          $center_query.=" AND Users.ID IN(".$sc_row.")";
        }
      } 
    }
  }
}

## Search 
$searchQuery = " ";
if($searchValue != ''){
  $searchQuery = " AND (Users.Name like '%".$searchValue."%' OR Users.Code like '%".$searchValue."%' OR Users.Email like '%".$searchValue."%' OR Users.Mobile like '%".$searchValue."%')";
}

## Total number of records without filtering
$all_count= $conn->query("SELECT Users.ID FROM Users LEFT JOIN University_User ON Users.ID = University_User.User_ID LEFT JOIN Users as RM ON University_User.Reporting = RM.ID WHERE  Users.Role = 'Center' AND University_ID = ".$_SESSION['university_id']."  $center_query  GROUP BY Users.ID");
$totalRecords = $all_count->num_rows;

## Total number of record with filtering
$filter_count = $conn->query("SELECT Users.ID  FROM Users LEFT JOIN University_User ON Users.ID = University_User.User_ID LEFT JOIN Users as RM ON University_User.Reporting = RM.ID WHERE  Users.Role = 'Center' AND University_ID = ".$_SESSION['university_id']." $center_query $searchQuery GROUP BY Users.ID");
$totalRecordwithFilter = $filter_count->num_rows;

## Fetch records
$result_record = "SELECT Users.ID, Users.Name, Users.CanCreateSubCenter, Users.Email, Users.Mobile, Users.Code, Users.Status, Users.Photo, CAST(AES_DECRYPT(Users.Password, '60ZpqkOnqn0UQQ2MYTlJ') AS CHAR(50)) password, University_User.Reporting, University_User.`Level`, RM.Name as RMName, RM.Code as RMCode FROM Users LEFT JOIN University_User ON Users.ID = University_User.User_ID LEFT JOIN Users as RM ON University_User.Reporting = RM.ID WHERE Users.Role = 'Center' AND University_User.`Level` = (SELECT MAX(`Level`) FROM University_User WHERE University_User.User_ID = Users.ID AND University_User.University_ID = ".$_SESSION['university_id'].") AND University_ID = ".$_SESSION['university_id']." $center_query $searchQuery GROUP BY User_ID $orderby LIMIT ".$row.",".$rowperpage;
$empRecords = mysqli_query($conn, $result_record);
$data = array();

// Allowed roles to view unmasked details
$unmask_roles = ['Administrator', 'University Head', 'Counsellor', 'Sub-Counsellor', 'Center'];

while ($row = mysqli_fetch_assoc($empRecords)) {

  // Admission Count
  $alloted_centers = array($row['ID']);
  $added_for = $alloted_centers;
  $sub_centers = $conn->query("SELECT ID FROM Users LEFT JOIN University_User ON Users.ID = University_User.User_ID WHERE Users.Role = 'Sub-Center' AND Reporting = ".$row['ID']."");
  if($sub_centers->num_rows>0){
    $alloted_sub_centers = array();
    while($sub_center = $sub_centers->fetch_assoc()){
      $alloted_sub_centers[] = $sub_center['ID'];
    }
    $added_for = array_filter(array_merge($alloted_centers, $alloted_sub_centers));
  }

  $admissions = $conn->query("SELECT COUNT(ID) as Applications FROM Students WHERE Added_For IN (".implode(',', $added_for).") AND Step = 4 $session_query");
  $admissions = mysqli_fetch_assoc($admissions);
  
  // Handle conditional masking for Email and Mobile
  $email = $row['Email'] ?? '';
  $mobile = $row['Mobile'] ?? '';
  if (!in_array($_SESSION['Role'], $unmask_roles)) {
      $email = stringToSecret($email);
      $mobile = stringToSecret($mobile);
  }

  $data[] = array(  
    "Photo"=> $row['Photo'],
    "Name" => $row['Name'],
    "Email" => $email,
    "Mobile" => $mobile,
    "Code" => $row['Code'],
    "Password" => $row['password'],
    "Admission" => $admissions['Applications'],
    "RM" => $row['RMName'].' ('.$row['RMCode'].')',
    "CanCreateSubCenter" => $row['CanCreateSubCenter'],
    "Status" => $row["Status"],
    "ID" => $row["ID"],
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