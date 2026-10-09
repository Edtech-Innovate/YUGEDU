<?php
## Database configuration
include '../../includes/db-config.php';
session_start();
ini_set('display_errors', 1);
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
  $orderby = "ORDER BY Users.ID ASC";
}


$session_query = '';
$counselloer_query = "";

function getCounselloerIds($conn, $role, $id)
{
  $userIds = [];
  if ($role == 'University Head') {
    // Not alloted to university and created by university head
    $res1 = $conn->query("SELECT ID from Users WHERE Role = 'Counsellor' AND Created_By = " . $id);
    if ($res1 && $res1->num_rows > 0) {
      $data1 = $res1->fetch_all(MYSQLI_ASSOC);
      $userIds = array_column($data1, 'ID');
    }

    // get the Counselloer who have alloted university 
    $res2 = $conn->query("SELECT User_ID FROM University_User WHERE Reporting = $id  group by User_ID");
    if ($res2 && $res2->num_rows > 0) {
      $data2 = $res2->fetch_all(MYSQLI_ASSOC);
      $allottedIds = array_column($data2, 'User_ID');
      $userIds = array_merge($userIds, $allottedIds);
    }
    $user_query = '';
    if (!empty($userIds)) {
      $userIds = array_unique($userIds);
      $counsellorStrId = implode(',', $userIds);
      $user_query = " AND Users.ID IN ($counsellorStrId)";
    }
  }

  $data = [
    'user_query' => $user_query,
    'userId_arr' => $userIds
  ];

  return $data;
}


if ($_SESSION['Role'] == 'University Head') {
  $counselloerIds = getCounselloerIds($conn, $_SESSION['Role'], $_SESSION['ID']);
  if (!empty($counselloerIds['user_query'])) {
    $counselloer_query .= $counselloerIds['user_query'];
  }
}


$university_query = '' . $counselloer_query;
if ($_SESSION['Role'] != 'Administrator' && $_SESSION['Role'] != 'University Head') {
  $university_query = " AND University_User.University_ID = " . $_SESSION['university_id'];
  $current_session = $conn->query("SELECT ID FROM Admission_Sessions WHERE University_ID = " . $_SESSION['university_id'] . " AND Current_Status = 1");
  if ($current_session->num_rows > 0) {
    $current_session = mysqli_fetch_assoc($current_session);
    $session_query = " AND Admission_Session_ID = " . $current_session['ID'];
  }
}

## Search 
$searchQuery = " ";
if ($searchValue != '') {
  $searchQuery = " AND (Users.Name like '%" . $searchValue . "%' OR Users.Code like '%" . $searchValue . "%' OR Users.Email like '%" . $searchValue . "%' OR Users.Mobile like '%" . $searchValue . "%')";
}

$verticalQuery = "";
if($_SESSION['Role']!="Administrator"){
    $verticalQuery = " and Users.vertical = ".$_SESSION['vertical']??1;
}

## Total number of records without filtering
$all_count = $conn->query("SELECT Users.ID   FROM Users LEFT JOIN University_User ON Users.ID = University_User.User_ID WHERE Role = 'Counsellor' $university_query $verticalQuery  GROUP BY Users.ID ");
// $records = mysqli_fetch_assoc($all_count);
$totalRecords = $all_count->num_rows;

## Total number of record with filtering
$filter_count = $conn->query("SELECT Users.ID FROM Users LEFT JOIN University_User ON Users.ID = University_User.User_ID WHERE Role = 'Counsellor' $university_query $verticalQuery $searchQuery GROUP BY Users.ID ");
// $records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $filter_count->num_rows;

## Fetch records
$result_record = "SELECT `ID`, `Name`, `Email`, `Mobile`, `Code`, `Status`, `Photo`, CAST(AES_DECRYPT(Password, '60ZpqkOnqn0UQQ2MYTlJ') AS CHAR(50)) password FROM Users LEFT JOIN University_User ON Users.ID = University_User.User_ID WHERE Role = 'Counsellor' $university_query $verticalQuery $searchQuery GROUP BY Users.ID $orderby LIMIT " . $row . "," . $rowperpage;
$empRecords = mysqli_query($conn, $result_record);
$data = array();

while ($row = mysqli_fetch_assoc($empRecords)) {
  $alloted = [];
  $alloted_universities = $conn->query("SELECT CONCAT(Universities.Short_Name, ' (', Universities.Vertical, ')') as University FROM University_User LEFT JOIN Universities ON University_User.University_ID = Universities.ID WHERE `User_ID` = " . $row['ID'] . "");
  if ($alloted_universities->num_rows > 0) {
    while ($alloted_university = $alloted_universities->fetch_assoc()) {
      $alloted[] = $alloted_university['University'];
    }
  }

  $data[] = array(
    "Photo" => $row['Photo'],
    "Name" => $row['Name'],
    "Email" => $row['Email'],
    "Mobile" => $row['Mobile'],
    "Code" => $row['Code'],
    "Password" => $row['password'],
    "University" => implode(', ', $alloted),
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
