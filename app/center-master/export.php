<?php
   ini_set('error_reporting', E_ALL );
   ini_set('display_errors', 1 );
  session_start();
  require '../../includes/db-config.php';
  require ('../../extras/vendor/shuchkin/simplexlsxgen/src/SimpleXLSXGen.php');
  include '../../includes/helpers.php';

  if(isset($columnSortOrder)){
    $orderby = "ORDER BY $columnName $columnSortOrder";
  }else{
    $orderby = "ORDER BY Users.ID ASC";
  }
  
  $center_query = "";

  // Role-based center query handling: Show all centers for this university
  if ($_SESSION['Role'] == 'University Head') {
      $center_ids = [];
      $get_centers = $conn->query("SELECT DISTINCT User_ID FROM University_User WHERE University_ID = " . intval($_SESSION['university_id']));
      
      if ($get_centers->num_rows > 0) {
          while ($row_c = $get_centers->fetch_assoc()) {
              if(!empty($row_c['User_ID'])) {
                  $center_ids[] = intval($row_c['User_ID']);
              }
          }
      }
      
      if (!empty($center_ids)) {
          $center_ids_str = implode(',', $center_ids);
          $center_query .= " AND Users.ID IN ($center_ids_str)";
      } else {
          $center_query .= " AND Users.ID IN (0)";
      }
  } 
  elseif ($_SESSION['Role'] == 'Sub-Counsellor' || $_SESSION['Role'] == 'Counsellor') {
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
          $center_ids_str = implode(',', array_map('intval', $centers));
          $center_query .= " AND Users.ID IN ($center_ids_str) ";
      }
  }
  elseif ($_SESSION['Role'] != "Administrator") {
    $check_has_unique_center_code = $conn->query("SELECT Center_Suffix FROM Universities WHERE ID = ".$_SESSION['university_id']." AND Has_Unique_Center = 1");
    if($check_has_unique_center_code->num_rows > 0){
      $center_suffix = mysqli_fetch_assoc($check_has_unique_center_code);
      $center_suffix = $center_suffix['Center_Suffix'];
      $center_query = " AND Users.Code LIKE '$center_suffix%' AND Users.Is_Unique = 1";
    }else{
      $center_query = " AND Users.Is_Unique = 0";
    }
  }
  
  ## Search 
  $searchQuery = " ";
  if(isset($_GET['search'])){
    $searchValue = mysqli_real_escape_string($conn, $_GET['search']);
    if(!empty($searchValue)){
      $searchQuery = " AND (Users.Name like '%".$searchValue."%' OR Users.Code like '%".$searchValue."%' OR Users.Email like '%".$searchValue."%' OR Users.Mobile like '%" . $searchValue . "%')";
    }
  }
  
  ## Fetch records joining University_User and Universities directly
  $result_record = "SELECT Users.ID, Users.Name, Users.Short_Name, Users.Contact_Name, Users.CanCreateSubCenter, Users.Email, Users.Mobile, Users.Alternate_Mobile, Users.Code, Users.Address, Users.City, Users.District, Users.State, Users.Pincode, Users.Status, CAST(AES_DECRYPT(Users.Password, '60ZpqkOnqn0UQQ2MYTlJ') AS CHAR(50)) password, GROUP_CONCAT(CONCAT(Universities.Short_Name, ' (', Universities.Vertical, ')') SEPARATOR ', ') as Alloted_Universities FROM Users LEFT JOIN University_User ON Users.ID = University_User.User_ID LEFT JOIN Universities ON University_User.University_ID = Universities.ID WHERE Users.Role = 'Center' $center_query $searchQuery GROUP BY Users.ID $orderby";
  
  $empRecords = mysqli_query($conn, $result_record);
  
  $data[] = array('Code', 'Name', 'Short Name', 'Contact Name', 'Email', 'Mobile', 'Alternate Mobile', 'Address', 'City', 'District', 'State', 'Pincode', 'Password', 'Sub Center Access', 'Status', 'Universities');
  
  while ($row = mysqli_fetch_assoc($empRecords)) {
    // Conditional masking: Unmasked for Administrator and University Head, Masked for others
    if (!in_array($_SESSION['Role'], ['Administrator', 'University Head', 'Counsellor'])) {
        $row['Email'] = maskEmail($row['Email'] ?? '');
        $row['Mobile'] = maskPhone($row['Mobile'] ?? '');
        $row['Alternate_Mobile'] = maskPhone($row['Alternate_Mobile'] ?? '');
    } else {
        $row['Email'] = $row['Email'] ?? '';
        $row['Mobile'] = $row['Mobile'] ?? '';
        $row['Alternate_Mobile'] = $row['Alternate_Mobile'] ?? '';
    }
    
    $data[] = array(
      $row['Code'],
      $row['Name'],
      $row['Short_Name'],
      $row['Contact_Name'],
      $row['Email'],
      $row['Mobile'],
      $row['Alternate_Mobile'],
      $row['Address'],
      $row['City'],
      $row['District'],
      $row['State'],
      $row['Pincode'],
      $row['password'],
      $row['CanCreateSubCenter'] == 1 ? 'Yes' : 'No',
      $row["Status"] == 1 ? 'Active' : 'Inactive',
      $row['Alloted_Universities'] ?? ''
    );
  }

  $xlsx = SimpleXLSXGen::fromArray($data)->downloadAs('Center Master.xlsx');