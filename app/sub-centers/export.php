<?php
  // ini_set('error_reporting', E_ALL );
  // ini_set('display_errors', 1 );
  session_start();
  require '../../includes/db-config.php';
  require ('../../extras/vendor/shuchkin/simplexlsxgen/src/SimpleXLSXGen.php');
  include '../../includes/helpers.php';

  $searchValue = "";
  if(isset($_GET['search'])){
    $searchValue = mysqli_real_escape_string($conn,$_GET['search']); // Search value
  }

  if(isset($columnSortOrder)){
    $orderby = "ORDER BY $columnName $columnSortOrder";
  }else{
    $orderby = "ORDER BY Users.ID ASC";
  }
  
  $university_query = '';
  $center_query = '';
  $counselloer_query = "";
  $sub_center_query = "";

  if($_SESSION['Role'] == 'University Head'){
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
          if(!empty($sc_row)){
            $sub_center_query = " AND Center IN(".$sc_row.")"; 
            $getcenter = $conn->query("SELECT GROUP_CONCAT(Sub_Center) as sub_center_ids FROM Center_SubCenter WHERE 1=1 $sub_center_query");
            $getcenter = $getcenter->fetch_assoc()['sub_center_ids'] ?? 0;
            $center_query .= " AND Users.ID IN (".$getcenter.")";
          } else {
            $center_query .= " AND Users.ID IN (0)";
          }
        } 
      } else {
        $center_query .= " AND Users.ID IN (0)";
      }
    } else {
      $center_query .= " AND Users.ID IN (0)";
    }
  } elseif($_SESSION['Role'] == 'Center'){
    $university_query = " AND Center_SubCenter.Center = ".$_SESSION['ID'];
  }
  
  ## Search 
  $searchQuery = " " . $center_query;
  if($searchValue != ''){
    $searchQuery .= " AND (Users.Name like '%".$searchValue."%' OR Users.Code like '%".$searchValue."%' OR Users.Email like '%".$searchValue."%' OR Users.Mobile like '%".$searchValue."%')";
  }

  $verticalQuery = "";
  if($_SESSION['Role'] != "Administrator"){
      $verticalQuery = " AND Users.vertical = ".($_SESSION['vertical'] ?? 1);
  }
  
  ## Fetch records with GROUP BY Users.ID to ensure unique results and avoid column ambiguity
  $result_record = "SELECT Users.`ID`, Users.`Name`, Users.`Email`, Users.`Mobile`, Users.`Code`, Users.`Status`, Users.`Photo`, CONCAT(U1.Name, ' (', U1.Code, ')') AS `Reporting`, CAST(AES_DECRYPT(Users.Password, '60ZpqkOnqn0UQQ2MYTlJ') AS CHAR(50)) password FROM Users LEFT JOIN University_User ON Users.ID = University_User.User_ID LEFT JOIN Center_SubCenter ON Users.ID = Center_SubCenter.Sub_Center LEFT JOIN Users as U1 ON Center_SubCenter.Center = U1.ID WHERE Users.Role = 'Sub-Center' $university_query $verticalQuery $searchQuery GROUP BY Users.ID $orderby";
  
  $empRecords = mysqli_query($conn, $result_record);
  $data[] = array('Code', 'Name', 'Email', 'Reporting User', 'Admissions', 'Password', 'Status', 'Universities');
  
  // Allowed roles to view unmasked email
  $unmask_roles = ['Administrator', 'University Head'];

  if($empRecords && mysqli_num_rows($empRecords) > 0){
    while ($row = mysqli_fetch_assoc($empRecords)) {
      
      // Fetch allotted universities for this sub-center
      $alloted = [];
      $alloted_universities = $conn->query("SELECT CONCAT(Universities.Short_Name, ' (', Universities.Vertical, ')') as University FROM University_User LEFT JOIN Universities ON University_User.University_ID = Universities.ID WHERE `User_ID` = ".$row['ID']."");
      if($alloted_universities && $alloted_universities->num_rows > 0){
        while($alloted_university = $alloted_universities->fetch_assoc()){
          $alloted[] = $alloted_university['University'];
        }
      }

      $admissions = $conn->query("SELECT COUNT(ID) as Applications FROM Students WHERE Added_For = ".$row['ID']."");
      $admissions = mysqli_fetch_assoc($admissions);
    
      $email = $row['Email'] ?? '';
      if (!in_array($_SESSION['Role'], $unmask_roles)) {
          $email = maskEmail($email);
      }

      $data[] = array(  
        $row['Code'],
        $row['Name'],
        $email,
        $row['Reporting'],
        (int)$admissions['Applications'],
        $row['password'],
        $row["Status"] == 1 ? 'Active' : 'Inactive',
        implode(', ', $alloted),
      );
    }
  }

  $xlsx = SimpleXLSXGen::fromArray( $data )->downloadAs('Sub-Centers.xlsx');