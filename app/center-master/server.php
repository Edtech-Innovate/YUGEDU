<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

## Database configuration
include '../../includes/db-config.php';
session_start();

## Read value
$draw = $_POST['draw'] ?? 1;
$row = $_POST['start'] ?? 0;
$rowperpage = $_POST['length'] ?? 10; // Rows display per page

if (isset($_POST['order'])) {
  $columnIndex = $_POST['order'][0]['column']; // Column index
  $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
  $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
}
$searchValue = isset($_POST['search']['value']) ? mysqli_real_escape_string($conn, $_POST['search']['value']) : ''; // Search value

if (isset($columnSortOrder) && !empty($columnName)) {
  $orderby = "ORDER BY $columnName $columnSortOrder";
} else {
  $orderby = "ORDER BY Users.ID DESC";
}

$center_query = "";

// Role-based center query handling
if ($_SESSION['Role'] == 'University Head') {
    // University Head should see ALL centers (allotted or not), just like Administrator.
} 
elseif ($_SESSION['Role'] == 'Sub-Counsellor' || $_SESSION['Role'] == 'Counsellor') {
    $center_ids = [];
    $user_ids = [];
    $session_id = intval($_SESSION['ID']);
    
    // 1. Get User_IDs from University_User where Reporting matches session ID
    $getcenterids = $conn->query("SELECT DISTINCT User_ID FROM University_User WHERE Reporting = $session_id AND User_ID IS NOT NULL");
    if ($getcenterids && $getcenterids->num_rows > 0) {
        while ($row_c = $getcenterids->fetch_assoc()) {
            if (!empty($row_c['User_ID'])) {
                $center_ids[] = intval($row_c['User_ID']);
            }
        }
    }

    // 2. Grab centers where Users.Created_By matches the session ID directly in Users table
    $get_created_by = $conn->query("SELECT ID FROM Users WHERE Role = 'Center' AND Created_By = $session_id");
    if ($get_created_by && $get_created_by->num_rows > 0) {
        while ($row_cb = $get_created_by->fetch_assoc()) {
            $user_ids[] = intval($row_cb['ID']);
        }
    }

    // 3. Get unallotted users (centers not present in University_User)
    $getnotalloteduser = $conn->query("SELECT Users.ID FROM Users LEFT JOIN University_User ON Users.ID = University_User.User_ID WHERE Users.Role = 'Center' AND University_User.User_ID IS NULL");
    if ($getnotalloteduser && $getnotalloteduser->num_rows > 0) {
        while ($dataArr = $getnotalloteduser->fetch_assoc()) {
            $user_ids[] = intval($dataArr['ID']);
        }
    }

    // Merge all arrays and keep unique IDs
    $centers = array_unique(array_merge($center_ids, $user_ids));
    
    if (!empty($centers)) {
        $center_ids_str = implode(',', array_map('intval', $centers));
        $center_query .= " AND Users.ID IN ($center_ids_str) ";
    } else {
        $center_query .= " AND Users.ID IN (0)";
    }
}
elseif ($_SESSION['Role'] !== "Administrator" && $_SESSION['Role'] !== "University Head") {
  $check_has_unique_center_code = $conn->query("SELECT Center_Suffix FROM Universities WHERE ID = " . intval($_SESSION['university_id']) . " AND Has_Unique_Center = 1");
  if ($check_has_unique_center_code && $check_has_unique_center_code->num_rows > 0) {
    $center_suffix = mysqli_fetch_assoc($check_has_unique_center_code);
    $center_suffix = $center_suffix['Center_Suffix'];
    $center_query .= " AND Code LIKE '$center_suffix%' AND Users.Is_Unique = 1";
  } else {
    $center_query .= " AND Users.Is_Unique = 0";
  }
}

## Search 
$searchQuery = " ";
if ($searchValue != '') {
  $searchQuery = " AND (Users.Name like '%" . $searchValue . "%' OR Users.Code like '%" . $searchValue . "%' OR Users.Email like '%" . $searchValue . "%' OR Users.Mobile like '%" . $searchValue . "%' OR CONCAT(Reporting.Name, ' (', Reporting.Code, ')') LIKE '%" . $searchValue . "%')";
}

$verticalQuery = "";
if ($_SESSION['Role'] != "Administrator" && $_SESSION['Role'] != "University Head") {
    $verticalQuery = " AND Users.vertical = " . ($_SESSION['vertical'] ?? 1);
}

## Total number of records without filtering
$all_count = $conn->query("SELECT COUNT(ID) as allcount FROM Users WHERE Role = 'Center' $center_query $verticalQuery");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];

## Total number of record with filtering
$filter_count = $conn->query("SELECT COUNT(Users.ID) as filtered FROM Users LEFT JOIN Users as Reporting on Users.Created_By = Reporting.ID WHERE Users.Role = 'Center' $center_query $verticalQuery $searchQuery");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];

## Fetch records
$result_record = "SELECT Users.ID, Users.Name, Users.CanCreateSubCenter, Users.Email, Users.Code, Users.Status, Users.Photo, CAST(AES_DECRYPT(Users.Password, '60ZpqkOnqn0UQQ2MYTlJ') AS CHAR(50)) password, CONCAT(Reporting.Name, ' (', Reporting.Code, ')') as Created_By FROM Users LEFT JOIN Users as Reporting on Users.Created_By = Reporting.ID WHERE Users.Role = 'Center' $center_query $verticalQuery $searchQuery $orderby LIMIT " . intval($row) . "," . intval($rowperpage);
$empRecords = mysqli_query($conn, $result_record);
$data = array();
$university_id = $_SESSION['university_id'] ?? 0;

while ($row = mysqli_fetch_assoc($empRecords)) {
  // credit amount
  $credit_query = $conn->query("SELECT SUM(Amount) AS totalamount FROM Wallets WHERE Added_By = '" . $row['ID'] . "' AND University_ID='$university_id' AND Status=1 AND Payment_For = 1 ");
  $creditamount = mysqli_fetch_assoc($credit_query);
  $total_credit_amount = isset($creditamount['totalamount']) ? $creditamount['totalamount'] : 0;
  
  // debit amount
  $debit_query = $conn->query("SELECT SUM(Amount) AS totalamount FROM Wallet_Payments WHERE Added_By = '" . $row['ID'] . "' AND University_ID='$university_id' AND Status=1");
  $debit_amount = mysqli_fetch_assoc($debit_query);
  $total_debit_amount = isset($debit_amount['totalamount']) ? $debit_amount['totalamount'] : 0;
  
  $current_amount = $total_credit_amount - $total_debit_amount;
  
  $data[] = array(
    "Photo" => $row['Photo'],
    "Name" => $row['Name'],
    "Email" => $row['Email'],
    "Code" => $row['Code'],
    "Password" => $row['password'],
    "CanCreateSubCenter" => $row['CanCreateSubCenter'],
    "Status" => $row["Status"],
    "Created_By" => $row['Created_By'],
    "ID" => $row["ID"],
    'totalAmount' => $current_amount
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