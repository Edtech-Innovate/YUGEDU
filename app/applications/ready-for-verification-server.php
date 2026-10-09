<?php
## Database configuration
include '../../includes/db-config.php';
session_start();
## Read value
$draw =$_POST['draw'];
$row =$_POST['start'];
$rowperpage =$_POST['length']; // Rows display per page
if (isset($_POST['order'])) {$columnIndex = $_POST['order'][0]['column']; // Column index$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
  $columnSortOrder =$_POST['order'][0]['dir']; // asc or desc
}

$searchValue = mysqli_real_escape_string($conn,$_POST['search']['value']); // Search value

if (isset($columnSortOrder)) {$orderby = "ORDER BY $columnName$columnSortOrder";
} else {
  $orderby = "ORDER BY Students.ID DESC";
}

if (isset($_SESSION['current_session'])) {
  if ($_SESSION['current_session'] == 'All') {$session_query = '';
  } else {
    $session_query = "AND Admission_Sessions.Name like '%" . $_SESSION['current_session'] . "%'";
  }
} else {
  $get_current_session =$conn->query("SELECT Name FROM Admission_Sessions WHERE Current_Status = 1 AND University_ID = '" . $_SESSION['university_id'] . "'");
  if ($get_current_session->num_rows > 0) {$gsc = mysqli_fetch_assoc($get_current_session);$session_query = "AND Admission_Sessions.Name like '%" . $gsc['Name'] . "%'";
  } else {
    $session_query = '';
  }
}

## NEW: Recursive function to fetch all subordinates downward from University_User table
function getAllSubordinates($conn, $managerId,$universityId) {
    $subordinates = array($managerId);
    $queue = array($managerId);
    
    while (!empty($queue)) {
        $currentId = array_shift($queue);
        $res =$conn->query("SELECT User_ID FROM University_User WHERE Reporting = " . intval($currentId) . " AND University_ID = " . intval($universityId));
        if ($res &&$res->num_rows > 0) {
            while ($row =$res->fetch_assoc()) {
                $subId =$row['User_ID'];
                if (!in_array($subId,$subordinates)) {
                    $subordinates[] =$subId;
                    $queue[] =$subId;
                }
            }
        }
    }
    return $subordinates;
}

// Build Role/Hierarchy query using University_User downward traversal if applicable
$role_query = "";
if (isset($_SESSION['ID']) && !in_array($_SESSION['Role'], ['Admin', 'University', 'University Head'])) {
    $subordinate_ids = getAllSubordinates($conn, $_SESSION['ID'],$_SESSION['university_id']);
    
    // Also include linked sub-centers if the user is a center
    if ($_SESSION['Role'] == 'Center') {
        foreach ($subordinate_ids as$c_id) {
            $sc_res =$conn->query("SELECT Sub_Center FROM Center_SubCenter WHERE Center = " . intval($c_id));
            if ($sc_res &&$sc_res->num_rows > 0) {
                while ($sc_row =$sc_res->fetch_assoc()) {
                    if (!in_array($sc_row['Sub_Center'],$subordinate_ids)) {
                        $subordinate_ids[] =$sc_row['Sub_Center'];
                    }
                }
            }
        }
    }
    
    $role_query = " AND Students.Added_For IN (" . implode(',', $subordinate_ids) . ")";
} else {
    $base_role_query = str_replace('{{ table }}', 'Students', isset($_SESSION['RoleQuery']) ?$_SESSION['RoleQuery'] : '');
    $role_query = str_replace('{{ column }}', 'Added_For',$base_role_query);
}

$step_query = "";
// if($_SESSION['Role']!='Sub-Center' &&$_SESSION['Role']!='Center'){
//    $step_query = " AND Step = 4";
// }


## Search 
$searchQuery = " ";
if ($searchValue != '') {
  if (strcasecmp($searchValue, 'completed') == 0) {$searchQuery = " AND Step = 4 ";
  } else {
$searchQuery = " AND (RIGHT(CONCAT('000000', Students.ID), 6) like '\%" . $searchValue . "%' OR Students.ID like '%" . $searchValue . "%' OR Students.Unique_ID like '%" . $searchValue . "%' OR Students.First_Name like '%" . $searchValue . "%' OR Students.Middle_Name like '%" . $searchValue . "%' OR Students.Last_Name like '%" . $searchValue . "%' OR CONCAT_WS(' ', Students.First_Name, Students.Middle_Name, Students.Last_Name) like '%" . $searchValue . "%' OR Admission_Sessions.Name like '%" . $searchValue . "%' OR Admission_Types.Name like '%" . $searchValue . "%' OR Students.Step like '%" . $searchValue . "%' OR Students.Father_Name like '%" . $searchValue . "%' OR Students.Email like '%" . $searchValue . "%' OR Students.Contact like '%" . $searchValue . "%' OR Sub_Courses.Short_Name like '%" . $searchValue . "%')";  }
}

$filterByDepartment = "";
if (isset($_SESSION['filterByDepartment'])) {
  $filterByDepartment =$_SESSION['filterByDepartment'];
}
$filterBySubCourse = "";
if (isset($_SESSION['filterBySubCourses'])) {
  $filterBySubCourse =$_SESSION['filterBySubCourses'];
}
$filterByDate = "";
if (isset($_SESSION['filterByDate'])) {
  $filterByDate =$_SESSION['filterByDate'];
}

$filterByProcessDate = "";
if (isset($_SESSION['filterByProcessDate'])) {
  $filterByProcessDate =$_SESSION['filterByProcessDate'];
}

$filterByStatus = "";
if (isset($_SESSION['filterByStatus'])) {
  $filterByStatus =$_SESSION['filterByStatus'];
}

$filterQueryUser = "";
if (isset($_SESSION['filterByUser'])) {
  $filterQueryUser =$_SESSION['filterByUser'];
}

## NEW: Vertical filter
$filterByVerticalType = "";
if (isset($_SESSION['filterByVerticalType'])) {
  $filterByVerticalType =$_SESSION['filterByVerticalType'];
}
$filterByDuration="";
if(isset($_SESSION['filterByDuration'])){
    $filterByDuration=$_SESSION['filterByDuration'];
}


if($_SESSION['Role']=="University Head"){
    $uniQuery = " AND Students.University_ID IN ( ".$_SESSION['all_university_ids'] . ")";
    
}else{
    $uniQuery = " AND Students.University_ID = ".$_SESSION['university_id'];
}

$searchQuery .=$filterByDepartment.$filterBySubCourse.$filterByProcessDate .$filterByDate.$filterByStatus.$filterQueryUser.$uniQuery. $filterByVerticalType.$filterByDuration;

## Total number of records without filtering
$all_count =$conn->query("SELECT COUNT(Students.ID) as allcount FROM Students LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID = Admission_Sessions.ID WHERE 1=1 AND Process_By_Center IS NOT NULL AND Step = 4 AND (Document_Verified IS NULL OR Payment_Received IS NULL) $role_query $step_query$session_query");
$records = mysqli_fetch_assoc($all_count);
$totalRecords =$records['allcount'];

## Total number of record with filtering
$filter_count =$conn->query("SELECT COUNT(Students.ID) as filtered FROM Students LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID=Admission_Sessions.ID LEFT JOIN Admission_Types ON Students.Admission_Type_ID=Admission_Types.ID LEFT JOIN Sub_Courses ON Students.Sub_Course_ID=Sub_Courses.ID LEFT JOIN Users ON Students.Added_For=Users.ID WHERE 1=1 AND Process_By_Center IS NOT NULL AND Step = 4 AND (Document_Verified IS NULL OR Payment_Received IS NULL) $searchQuery$role_query $step_query$session_query");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter =$records['filtered'];

## Fetch records
$result_record = "SELECT Student_Pendencies.ID as Pendency, UPPER(DATE_FORMAT(Students.DOB, '%d%b%Y')) as DOB, Students.Status, Students.ID, Students.Added_For, Students.Unique_ID, CONCAT(Students.First_Name, ' ', Students.Middle_Name, ' ', Students.Last_Name) as First_Name, Students.Father_Name, Students.Enrollment_No, Students.OA_Number, Students.Duration, Students.Step, Students.Process_By_Center, Students.Payment_Received, Students.Document_Verified, Students.Contact, Students.Processed_To_University, Admission_Sessions.`Name` as Adm_Session, Admission_Types.`Name` as Adm_Type, CONCAT(Courses.Short_Name, ' (', Sub_Courses.Name, ')') as Short_Name, Student_Documents.`Location`, Students.ID_Card, Students.Admit_Card, Students.Exam FROM Students LEFT JOIN Student_Pendencies ON Students.ID = Student_Pendencies.Student_ID AND Student_Pendencies.Status = 0 LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID = Admission_Sessions.ID LEFT JOIN Admission_Types ON Students.Admission_Type_ID = Admission_Types.ID LEFT JOIN Courses ON Students.Course_ID = Courses.ID LEFT JOIN Sub_Courses ON Students.Sub_Course_ID = Sub_Courses.ID LEFT JOIN Student_Documents ON Students.ID = Student_Documents.Student_ID AND Student_Documents.`Type` = 'Photo' WHERE 1=1 AND Process_By_Center IS NOT NULL AND Step = 4 AND (Document_Verified IS NULL OR Payment_Received IS NULL) $searchQuery $role_query$step_query $session_query$orderby LIMIT " . $row . "," . $rowperpage;
$empRecords = mysqli_query($conn, $result_record);$data = array();

while ($row = mysqli_fetch_assoc($empRecords)) {

  // Added_For
  if ($_SESSION['Role'] == 'Center') {
    $user =$conn->query("SELECT ID, Code, Name FROM Users WHERE ID = " . $row['Added_For'] . "");
  } else {
    $user =$conn->query("SELECT ID, Code, Name FROM Users WHERE ID = " . $row['Added_For'] . " AND Role = 'Center'");
    if ($user->num_rows == 0) {
      $user =$conn->query("SELECT Users.ID, Code, Name FROM Users LEFT JOIN Center_SubCenter ON Users.ID = Center_SubCenter.Center WHERE `Sub_Center` = " . $row['Added_For']);
    }
  }

  $user = mysqli_fetch_array($user);

  // RM (Recursive upward hierarchy lookup)
  $rm['Name'] = "";
  if (!empty($user)) {$current_user_id = $user['ID'];$university_id = intval($_SESSION['university_id']);$found_rm = false;

    while ($current_user_id && !$found_rm) {$rm_query = "SELECT University_User.Reporting, Users.ID as RM_ID, CONCAT(Users.Name, ' (', Users.Code, ')') as Name 
                   FROM University_User 
                   LEFT JOIN Users ON University_User.Reporting = Users.ID 
                   WHERE University_User.User_ID = " . intval($current_user_id) . " 
                   AND University_User.University_ID = " . $university_id;
      $rm_res = $conn->query($rm_query);
      
      if ($rm_res &&$rm_res->num_rows > 0) {
        $rm_row = mysqli_fetch_assoc($rm_res);
        if (!empty($rm_row['Name'])) {
          $rm =$rm_row;
          $found_rm = true;
        } elseif (!empty($rm_row['Reporting'])) {
          $current_user_id =$rm_row['Reporting'];
        } else {
          break;
        }
      } else {
        break;
      }
    }

    if (!$found_rm) {
      $rm =$user;
    }
  }

  $data[] = array(
    "Photo" => empty($row['Location']) ? '/assets/img/default-user.png' :$row['Location'],
    "First_Name" => $row['First_Name'],
    "Father_Name" => $row['Father_Name'],
    "Unique_ID" => empty($row['Unique_ID']) ? sprintf("%'.06d\n", $row['ID']) :$row['Unique_ID'],
    "Enrollment_No" => !empty($row['Enrollment_No']) ?$row['Enrollment_No'] : '',
    "OA_Number" => !empty($row['OA_Number']) ?$row['OA_Number'] : '',
    "Duration" => $row['Duration'],
    "Step" => $row['Step'],
    "Process_By_Center" => !empty($row['Process_By_Center']) ? date("d-m-Y", strtotime($row['Process_By_Center'])) : "1",
    "Payment_Received" => !empty($row['Payment_Received']) ? date("d-m-Y", strtotime($row['Payment_Received'])) : "1",
    "Document_Verified" => !empty($row['Document_Verified']) ? date("d-m-Y", strtotime($row['Document_Verified'])) : "1",
    "Processed_To_University" => !empty($row['Processed_To_University']) ? date("d-m-Y", strtotime($row['Processed_To_University'])) : '1',
    "Adm_Session" => $row['Adm_Session'],
    "Adm_Type" => $row['Adm_Type'],
    "Short_Name" => $row['Short_Name'],
    "Center_Code" => $user['Code'],
    "Center_Name" => $user['Name'],
    "RM" => $rm['Name'],
    "Status" => $row['Status'],
    "DOB" => $row['DOB'],
    "ID_Card" => $row['ID_Card'],
    "Admit_Card" => $row['Admit_Card'],
    "Exam" => $row['Exam'],
    "Pendency" => empty($row['Pendency']) ? 0 : (int)$row['Pendency'],
    "ID" => base64_encode($row['ID'] . 'W1Ebt1IhGN3ZOLplom9I'),
    "Contact" => $row['Contact'],
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