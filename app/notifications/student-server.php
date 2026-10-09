<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
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
$university_id = mysqli_real_escape_string($conn,$_SESSION['university_id']);

$searchValue = mysqli_real_escape_string($conn,$_POST['search']['value']); // Search value
$orderby = (isset($columnSortOrder)) ? "ORDER BY $columnName $columnSortOrder" : "ORDER BY Notifications_Generated.ID ASC"; 
$searchQuery = !empty($searchValue) ?  " AND Notification_Heading.Name LIKE '%$searchValue%'" : "";
$filterQuery = ($_SESSION['Role'] == 'Student') ? studentSearchQuery() : centerSearchQuery();
$send_to = ($_SESSION['Role'] == 'Student') ? 'student' : 'center';

## Total number of records without filtering
//echo "SELECT COUNT(Notifications_Generated.ID) as allcount FROM `Notifications_Generated` WHERE Notifications_Generated.Status = '1' AND (Notifications_Generated.Send_To = '$send_to' OR Notifications_Generated.Send_To = 'all') AND Notifications_Generated.university_id = '$university_id' $filterQuery";
$all_count=$conn->query("SELECT COUNT(Notifications_Generated.ID) as allcount FROM `Notifications_Generated` WHERE Notifications_Generated.Status = '1' AND (Notifications_Generated.Send_To = '$send_to' OR Notifications_Generated.Send_To = 'all') AND Notifications_Generated.university_id = '$university_id' $filterQuery");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];

## Total number of record with filtering
$filter_count = $conn->query("SELECT COUNT(Notifications_Generated.ID) as filtered FROM `Notifications_Generated` LEFT JOIN Notification_Heading ON Notification_Heading.ID = Notifications_Generated.Heading WHERE Notifications_Generated.Status = '1' AND (Notifications_Generated.Send_To = '$send_to' OR Notifications_Generated.Send_To = 'all') AND Notifications_Generated.university_id = '$university_id' $filterQuery $searchQuery");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];

$data = [];
## Fetch records
$notificationRecord = $conn->query("SELECT Notifications_Generated.ID , Notification_Heading.Name as `heading` , JSON_UNQUOTE(JSON_EXTRACT(Notifications_Generated.published_on,'$[0].published')) AS `send_on` ,Notifications_Generated.Send_To as `send_to` , Notifications_Generated.Attachment as `document` FROM `Notifications_Generated` LEFT JOIN Notification_Heading ON Notification_Heading.ID = Notifications_Generated.Heading WHERE Notifications_Generated.Status = '1' AND (Notifications_Generated.Send_To = '$send_to' OR Notifications_Generated.Send_To = 'all') AND Notifications_Generated.university_id = '$university_id' $filterQuery $searchQuery $orderby LIMIT $row, $rowperpage");

while ($row = mysqli_fetch_assoc($notificationRecord)) {
    $data[] = array(
        'ID' => $row['ID'],
        'heading' => $row['heading'],
        'send_on' => $row['send_on'],
        'send_to' => $row['send_to'],
        'document' => $row['document']
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

function studentSearchQuery() : string {

    global $conn;
    $university_id = mysqli_real_escape_string($conn,$_SESSION['university_id']);
    $student_id = mysqli_real_escape_string($conn,$_SESSION['ID']);
    $sub_course_id = mysqli_real_escape_string($conn,$_SESSION['Sub_Course_ID']);
    $admission_session = mysqli_real_escape_string($conn,$_SESSION['Admission_Session_ID']);
    
    $scheme = $conn->query("SELECT Scheme as Scheme_ID FROM `Admission_Sessions` WHERE ID = '$admission_session'");
    $scheme_id = mysqli_fetch_column($scheme);
    $searchQuery = "";

    $searchQuery .= "AND IF(Notifications_Generated.student_id != '',JSON_CONTAINS(Notifications_Generated.student_id,'[\"{$student_id}\"]'),true)";
    // $searchQuery .= "AND IF(Notifications_Generated.scheme_id != '',JSON_CONTAINS(Notifications_Generated.scheme_id,'[\"{$scheme_id}\"]'),true)";
    $searchQuery .= "AND IF(Notifications_Generated.admissionSession_id != '',JSON_CONTAINS(Notifications_Generated.admissionSession_id,'[\"{$admission_session}\"]'),true)";
    $searchQuery .= "AND IF(Notifications_Generated.course_id != '',JSON_CONTAINS(Notifications_Generated.course_id,'[\"{$sub_course_id}\"]'),true)";
    
    $combine_duration = '';
 
        $duration = mysqli_real_escape_string($conn,$_SESSION['Duration']);
        $combine_duration = $duration;
    

    $searchQuery .= "AND IF(Notifications_Generated.duration != '',JSON_UNQUOTE(JSON_EXTRACT(Notifications_Generated.duration,'$.{$sub_course_id}')) LIKE '%{$combine_duration}%' OR JSON_UNQUOTE(JSON_EXTRACT(Notifications_Generated.duration, '$.{$sub_course_id}')) = 'All',true)";
    
    return $searchQuery;
}

function centerSearchQuery() : string {

    global $conn;
    $center_id = mysqli_real_escape_string($conn,$_SESSION['ID']);
    $searchQuery = "AND IF(Notifications_Generated.center_id != '',JSON_CONTAINS(Notifications_Generated.center_id,'[\"{$center_id}\"]'),true)";
    return $searchQuery;
}
?>