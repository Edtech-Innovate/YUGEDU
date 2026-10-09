<?php
   ini_set('error_reporting', E_ALL );
   ini_set('display_errors', 1 );
   session_start();
   require '../../includes/db-config.php';
   require ('../../extras/vendor/shuchkin/simplexlsxgen/src/SimpleXLSXGen.php');
   include '../../includes/helpers.php';

   $univ_id_val = intval($_SESSION['university_id']);

   // Fetch Current University Name from Universities table
   $current_univ_name = '';
   $univ_query = $conn->query("SELECT Short_Name FROM Universities WHERE ID = $univ_id_val LIMIT 1");
   if($univ_query && $univ_query->num_rows > 0){
     $current_univ_name = $univ_query->fetch_assoc()['Short_Name'];
   }

   $searchValue = "";
   if(isset($_GET['search'])){
     $searchValue = mysqli_real_escape_string($conn, $_GET['search']); // Search value
   }
   
   // Whitelist sorting parameters to prevent SQL Injection
   $allowed_sorts = ['Code' => 'Users.Code', 'Name' => 'Users.Name', 'Email' => 'Users.Email', 'Status' => 'Users.Status', 'ID' => 'Users.ID'];
   $sort_col = $allowed_sorts[$_GET['sort'] ?? ''] ?? 'Users.ID';
   $sort_dir = (strtoupper($_GET['dir'] ?? '') === 'ASC') ? 'ASC' : 'DESC';
   $orderby = "ORDER BY {$sort_col} {$sort_dir}";
  
   $center_query = "";
   if($_SESSION['Role'] != "Administrator"){
     $check_has_unique_center_code = $conn->query("SELECT Center_Suffix FROM Universities WHERE ID = $univ_id_val AND Has_Unique_Center = 1");
     if($check_has_unique_center_code && $check_has_unique_center_code->num_rows > 0){
       $center_suffix = mysqli_fetch_assoc($check_has_unique_center_code);
       $center_suffix = mysqli_real_escape_string($conn, $center_suffix['Center_Suffix']);
       $center_query = " AND Users.Code LIKE '$center_suffix%' AND Users.Is_Unique = 1";
     }else{
       $center_query = " AND Users.Is_Unique = 0";
     }
   }

   // Role-based center fetching identical to the DataTable script
   if ($_SESSION['Role'] == 'Sub-Counsellor' || $_SESSION['Role'] == 'Counsellor') {
     $center_ids = [];
     $user_ids = [];
     $getcenterids = $conn->query("SELECT User_ID FROM University_User WHERE Reporting = " . intval($_SESSION['ID']));
     if ($getcenterids && $getcenterids->num_rows > 0) {
         $centerids = $getcenterids->fetch_all(MYSQLI_ASSOC);
         $center_ids = array_column($centerids, 'User_ID');
     }
     $getnotalloteduser = $conn->query("SELECT Users.ID FROM Users LEFT JOIN University_User ON Users.ID = University_User.User_ID WHERE Users.Role = 'Center' AND University_User.User_ID IS NULL");
     if ($getnotalloteduser && $getnotalloteduser->num_rows > 0) {
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
   elseif ($_SESSION['Role'] == 'University Head') {
     $center_ids = [];
     $getcounselloer = $conn->query("SELECT User_ID FROM University_User WHERE Reporting = " . intval($_SESSION['ID']));
     if ($getcounselloer && $getcounselloer->num_rows > 0) {
         $centerids = $getcounselloer->fetch_all(MYSQLI_ASSOC);
         $center_ids = array_column($centerids, 'User_ID');
       if (!empty($center_ids)) {
         $center_ids_str = implode(',', array_map('intval', $center_ids)); 
         $counselloer_query = " AND Reporting IN ($center_ids_str) ";
         $getcounselloer = $conn->query("SELECT GROUP_CONCAT(University_User.User_ID) AS sc_ids FROM Users LEFT JOIN University_User ON Users.ID = University_User.User_ID WHERE 1=1 AND Role = 'Center' $counselloer_query");
         if ($getcounselloer && $getcounselloer->num_rows > 0) {
           $sc_row = $getcounselloer->fetch_assoc()['sc_ids'];
           if (!empty($sc_row)) {
             $center_query .= " AND Users.ID IN(".$sc_row.")";
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

   ## Fetch records including Email, Mobile, and all allotted universities
   $result_record = "SELECT Users.ID, Users.Name, Users.Contact_Name, Users.Email, Users.Mobile, Users.Code, Users.Status, CAST(AES_DECRYPT(Users.Password, '60ZpqkOnqn0UQQ2MYTlJ') AS CHAR(50)) password, RM.Name as RMName, RM.Code as RMCode, GROUP_CONCAT(DISTINCT CONCAT(UnivTable.Short_Name, ' (', UnivTable.Vertical, ')') SEPARATOR ', ') as Alloted_Universities FROM Users LEFT JOIN University_User ON Users.ID = University_User.User_ID LEFT JOIN Universities as UnivTable ON University_User.University_ID = UnivTable.ID LEFT JOIN University_User as UU_Filter ON Users.ID = UU_Filter.User_ID LEFT JOIN Users as RM ON University_User.Reporting = RM.ID WHERE Users.Role = 'Center' AND UU_Filter.University_ID = $univ_id_val $center_query $searchQuery GROUP BY Users.ID $orderby";
   
   $empRecords = mysqli_query($conn, $result_record);

   // Fee Structure scoped university-wise
   $center_fee_structures = array();
   $fee_structures = $conn->query("SELECT ID, Name FROM Fee_Structures WHERE University_ID = $univ_id_val AND (Sharing = 1 OR Is_Constant = 0)");
   if($fee_structures){
     while($fee_structure = $fee_structures->fetch_assoc()){
       $center_fee_structures[$fee_structure['ID']] = $fee_structure['Name'];
     }
   }

   // Define Header
   $header_fees = array_merge(
        array('Code', 'Name', 'Contact Name', 'Email', 'Mobile', 'Admissions', 'RM', 'Status', 'Admission Session'),
        array_values($center_fee_structures),
        array('Current University', 'Universities')
   );
   $final_data[] = $header_fees;

   // Allowed roles to view unmasked details if needed
   $unmask_roles = ['Administrator', 'University Head', 'Counsellor', 'Sub-Counsellor', 'Center'];

   if($empRecords && mysqli_num_rows($empRecords) > 0){
     while ($row = mysqli_fetch_assoc($empRecords)) {

        // Admission Count Setup
        $alloted_centers = array($row['ID']);
        $added_for = $alloted_centers;
        $sub_centers = $conn->query("SELECT Users.ID FROM Users LEFT JOIN University_User ON Users.ID = University_User.User_ID WHERE Users.Role = 'Sub-Center' AND University_User.Reporting = ".intval($row['ID'])."");
        
        $alloted_sub_centers = array();
        if($sub_centers && $sub_centers->num_rows > 0){
          while($sub_center = $sub_centers->fetch_assoc()){
            $alloted_sub_centers[] = $sub_center['ID'];
          }
          $added_for = array_filter(array_merge($alloted_centers, $alloted_sub_centers));
        }

        $added_for_str = !empty($added_for) ? implode(',', array_map('intval', $added_for)) : '0';

        // Fetch all distinct Admission Sessions assigned/active for this user under this university
        $sessions_query = $conn->query("SELECT DISTINCT Admission_Session_ID FROM Fee_Variables WHERE Code = '".mysqli_real_escape_string($conn, $row['ID'])."' AND University_ID = $univ_id_val UNION SELECT DISTINCT ID as Admission_Session_ID FROM Admission_Sessions WHERE University_ID = $univ_id_val AND Current_Status = 1");
        
        $session_ids = array();
        if($sessions_query && $sessions_query->num_rows > 0){
            while($s_row = $sessions_query->fetch_assoc()){
                if(!empty($s_row['Admission_Session_ID'])){
                    $session_ids[] = intval($s_row['Admission_Session_ID']);
                }
            }
        }
        
        // Fallback to current or latest session if none detected via variables
        if(empty($session_ids)){
            $current_session = $conn->query("SELECT ID FROM Admission_Sessions WHERE University_ID = $univ_id_val AND Current_Status = 1 LIMIT 1");
            if($current_session && $current_session->num_rows > 0){
                $session_ids[] = intval($current_session->fetch_assoc()['ID']);
            } else {
                $latest_session = $conn->query("SELECT ID FROM Admission_Sessions WHERE University_ID = $univ_id_val ORDER BY ID DESC LIMIT 1");
                if($latest_session && $latest_session->num_rows > 0){
                    $session_ids[] = intval($latest_session->fetch_assoc()['ID']);
                }
            }
        }

        // Loop through each session found to generate row per session cleanly if multiple exist
        foreach($session_ids as $session_id){
            // Fetch Session Name
            $session_name_val = '';
            $s_name_q = $conn->query("SELECT Name FROM Admission_Sessions WHERE ID = $session_id LIMIT 1");
            if($s_name_q && $s_name_q->num_rows > 0){
                $session_name_val = $s_name_q->fetch_assoc()['Name'];
            }

            // Get Admissions Count for this specific session
            $admissions_count = 0;
            $admissions = $conn->query("SELECT COUNT(ID) as Applications FROM Students WHERE Added_For IN ($added_for_str) AND Step = 4 AND Admission_Session_ID = $session_id");
            if($admissions){
                $admissions_row = mysqli_fetch_assoc($admissions);
                $admissions_count = (int)$admissions_row['Applications'];
            }

            $rm_string = (!empty($row['RMName'])) ? $row['RMName'].' ('.$row['RMCode'].')' : '';

            // Handle masking for Email & Mobile
            $email = $row['Email'] ?? '';
            $mobile = $row['Mobile'] ?? '';
            if (!in_array($_SESSION['Role'], $unmask_roles)) {
                $email = stringToSecret($email);
                $mobile = stringToSecret($mobile);
            }

            $base_data = array(  
              $row['Code'],
              $row['Name'],
              $row['Contact_Name'],
              $email,
              $mobile,
              $admissions_count,
              $rm_string,
              $row["Status"] == 1 ? 'Active' : 'Inactive',
              $session_name_val
            );

            // Fetch Fee Variables row scoped to this session
            $safe_code = mysqli_real_escape_string($conn, $row['ID']);
            $variables = $conn->query("SELECT Fee FROM Fee_Variables WHERE Code = '$safe_code' AND University_ID = $univ_id_val AND Admission_Session_ID = $session_id LIMIT 1");
            
            $fee_json_data = array();
            $raw_fee_single = '';
            if($variables && $variables->num_rows > 0){
                $var_row = $variables->fetch_assoc();
                $raw_fee = $var_row['Fee'];
                $decoded = json_decode($raw_fee, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $fee_json_data = $decoded;
                } else {
                    $raw_fee_single = $raw_fee;
                }
            }

            // Map individual fee variables per column
            $fees_data = array();
            foreach($center_fee_structures as $id => $center_fee_structure){
              $fee_val = '0%'; 
              if(isset($fee_json_data[$id])) {
                  $val = $fee_json_data[$id];
                  $fee_val = is_numeric($val) ? $val . '%' : $val;
              } elseif(!empty($raw_fee_single)) {
                  $fee_val = is_numeric($raw_fee_single) ? $raw_fee_single . '%' : $raw_fee_single;
              }
              $fees_data[] = $fee_val;
            }

            $uni_string = $row['Alloted_Universities'] ?? '';
            
            $row_data = array_merge($base_data, $fees_data, array($current_univ_name, $uni_string));
            $final_data[] = $row_data;
        }
     }
   }

   $xlsx = SimpleXLSXGen::fromArray($final_data)->downloadAs('Centers.xlsx');