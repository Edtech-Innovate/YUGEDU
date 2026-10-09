<?php

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
  $orderby = "ORDER BY Notifications_Generated.ID ASC";
}

## Search 

$searchQuery = '';
$searchQuery = ($searchValue != '') ? "AND (Notification_Heading.Name LIKE '%$searchValue%' OR Notifications_Generated.Send_To LIKE '%$searchValue%')" : "";

$searchQuery .= (isset($_REQUEST['headingFilter']) && !empty($_REQUEST['headingFilter'])) ?  " AND Notifications_Generated.Heading = '".$_REQUEST['headingFilter']."'" : "";

$searchQuery .= (isset($_REQUEST['sendTo']) && !empty($_REQUEST['sendTo'])) ? " AND Notifications_Generated.Send_To LIKE '%". $_REQUEST['sendTo'] ."%'" : "";

## Total number of records without filtering
$all_count=$conn->query("SELECT COUNT(Notifications_Generated.ID) as allcount FROM `Notifications_Generated` WHERE university_id = '".$_SESSION['university_id']."'");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];

## Total number of record with filtering
$filter_count = $conn->query("SELECT COUNT(Notifications_Generated.ID) as filtered FROM `Notifications_Generated` LEFT JOIN Notification_Heading ON Notification_Heading.ID = Notifications_Generated.Heading WHERE university_id = '".$_SESSION['university_id']."' $searchQuery");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];

$data = [];
## Fetch records
$notificationRecord = $conn->query("SELECT Notifications_Generated.* , Notification_Heading.Name as `notification_heading` FROM `Notifications_Generated` LEFT JOIN Notification_Heading ON Notification_Heading.ID = Notifications_Generated.Heading WHERE university_id = '".$_SESSION['university_id']."' $searchQuery $orderby LIMIT $row, $rowperpage");

while ($row = mysqli_fetch_assoc($notificationRecord)) {
  $published_on = '';
  if (!empty($row['published_on'])) {
    $published_data = json_decode($row['published_on'],true);
    $published_on = date_format(date_create($published_data[0]['published']),'d-M-Y');
  } else {
    $published_on = "Not Published"; 
  }
  $data[] = array(
    'ID' => $row['ID'],
    'Heading' => $row['notification_heading'],
    'Regarding' => ucfirst(strtolower($row['Heading'])),
    'Send_To' => ucfirst(strtolower($row['Send_To'])),
    'Content' => $row['Content'],
    'Attachment' => $row['Attachment'],
    'published_on' => $published_on,
    'status' => $row['Status'],
    'created_at' => date_format(date_create($row['Noticefication_Created_on']),'d-M-Y')
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