<?php
  // ini_set('error_reporting', E_ALL );
  ini_set('display_errors', 1 );
  session_start();
  require '../../includes/db-config.php';
  require ('../../extras/vendor/shuchkin/simplexlsxgen/src/SimpleXLSXGen.php');

  $search_value = "";
  if(isset($_GET['search'])){
    $search_value = mysqli_real_escape_string($conn,$_GET['search']); // Search value
  }
    // echo('<pre>');print_r($_GET['steps_found']);die;
  if(isset($_SESSION['current_session'])){
    if($_SESSION['current_session']=='All'){
      $session_query = '';
    }else{
      $session_query = "AND Admission_Sessions.Name like '%".$_SESSION['current_session']."%'";
    }
  }else{
    $get_current_session = $conn->query("SELECT Name FROM Admission_Sessions WHERE Current_Status = 1 AND University_ID = '".$_SESSION['university_id']."'");
    if($get_current_session->num_rows>0){
      $gsc = mysqli_fetch_assoc($get_current_session);
      $session_query = "AND Admission_Sessions.Name like '%".$gsc['Name']."%'";
    }else{
      $session_query = '';
    }
  }
  
  $role_query = str_replace('{{ table }}', 'Students', $_SESSION['RoleQuery']);
  $role_query = str_replace('{{ column }}', 'Added_For', $role_query);
  
 $step_query = "";
if (isset($_GET['steps_found'])) {
  $stepsfound = mysqli_real_escape_string($conn, $_GET['steps_found']); // Search value
  if ($stepsfound == "applications") {
    $step_query = "";
  } elseif ($stepsfound == "not_processed") {
    $step_query = " AND Step = 4 AND Process_By_Center IS NULL";
  } elseif ($stepsfound == "ready_for_verification") {
    $step_query = " AND Process_By_Center IS NOT NULL AND (Document_Verified IS NULL OR Payment_Received IS NULL)";
  } elseif ($stepsfound == "verified") {
    $step_query = " AND Document_Verified IS NOT NULL AND Processed_To_University IS NULL";
  } elseif ($stepsfound == "proccessed_to_university") {
    $step_query = "AND 	Processed_To_University IS NOT NULL AND Enrollment_No IS NULL";
  } elseif ($stepsfound == "enrolled") {
    $step_query = "AND Enrollment_No IS NOT NULL";
  }
}
// echo('<pre>');print_r($step_query);die;
//   if($_SESSION['Role']!='Sub-Center' || $_SESSION['Role']!='Center'){
//      $step_query = " AND Step = 4";
//   }
  
  $header = array('Student_ID', 'Enrollment_No', 'OA_Number', 'Step', 'Added On', 'Processed By Center',
  'Processed To University', 'Student Name', 'Father Name', 'Mother Name', 'Adm Type', 'Session', 'Duration',
  'Mode', 'Course', 'Sub Course', 'Short Name', 'Email', 'Contact', 'Alternate Email', 'Alternate Contact',
  'Aadhar Number', 'DOB', 'Employement Status', 'Gender', 'Category', 'Address', 'City', 'District', 'State',
  'Pincode', 'Nationality', 'High School', 'Subject', 'Year', 'Board/Institute', 'Marks Obtained',
  'Maximum Marks', 'Total marks%', 'Intermediate', 'Subject', 'Year', 'Board/Institute', 'Marks Obtained',
  'Maximum Marks', 'Total marks%', 'UG', 'Subject', 'Year', 'Board/Institute', 'Marks Obtained',
  'Maximum Marks', 'Total marks%', 'PG', 'Subject', 'Year', 'Board/Institute', 'Marks Obtained',
  'Maximum Marks', 'Total marks%', 'Other', 'Subject', 'Year', 'Board/Institute', 'Marks Obtained',
  'Maximum Marks', 'Total marks%', 'Code', 'Center Name', 'RM', 'Export Documents');
  if($_SESSION['Role']!='Sub-Center'){
    $fee_structures = $conn->query("SELECT ID, Name, Sharing FROM Fee_Structures WHERE University_ID = ".$_SESSION['university_id']." ORDER BY Fee_Applicable_ID");
    while($fee_structure = $fee_structures->fetch_assoc()){
      array_push($header, $fee_structure['Name']);
      if($fee_structure['Sharing']==1){
        //array_push($header, $fee_structure['Name']." %");
      }
    }
    array_push($header, "Total (Sharing)");
    array_push($header, "ABC ID");
    array_push($header, "DEB ID");
    // array_push($header, "Fee Sharing (%)");
  }else{
    unset($row[5]);
    unset($row[68]);
    unset($row[69]);
    unset($row[70]);
  }

  ## Search 
//   $searchQuery = " ";
//   if($search_value != ''){
//     if(strcasecmp($searchValue, 'completed')==0){
//       $searchQuery = " AND Step = 4 ";
//     }else{
//       $searchQuery = " AND (Students.ID like '%".$search_value."%' OR Students.First_Name like '%".$search_value."%' OR Students.Middle_Name like '%".$search_value."%' OR Students.Last_Name like '%".$search_value."%' OR Admission_Sessions.Name like '%".$search_value."%' OR Admission_Types.Name like '%".$search_value."%' OR Students.Step like '%".$search_value."%' OR Students.Father_Name like '%".$search_value."%' OR Students.Email like '%".$search_value."%' OR Students.Contact like '%".$search_value."%' OR Sub_Courses.Short_Name like '%".$search_value."%')";
//     }
//   }
$searchQuery = " ";
if ($search_value != '') {
  if (!empty(strpos($search_value, "="))) {
    $search = explode("=", $search_value);
    $searchBy = trim($search[0]);
    $values = array_key_exists(1, $search) && !empty($search[1]) ? explode(" ", $search[1]) : array();
    $values = array_filter($values);
    if (!empty($values)) {
      $student_id_column = $_SESSION['student_id'] == 1 ? 'Students.Unique_ID' : "RIGHT(CONCAT('000000', Students.ID), 6)";
      $column = strcasecmp($searchBy, 'student id') == 0 ? $student_id_column : (strcasecmp($searchBy, 'enrollment') == 0 ? 'Students.Enrollment_No' : (strcasecmp($searchBy, 'oa number') == 0 ? 'OA_Number' : ''));
      if (!empty($column)) {
        $values = "'" . implode("','", $values) . "'";
        $searchQuery = " AND $column IN ($values)";
      }
    }
  } elseif (strcasecmp($search_value, 'completed') == 0) {
    $searchQuery = " AND Step = 4 ";
  } else {
    $searchQuery = " AND (Students.ID like '%" . $search_value . "%' OR Students.Unique_ID like '%" . $search_value . "%' OR  Students.First_Name like '%" . $search_value . "%' OR Students.Middle_Name like '%" . $search_value . "%' OR Students.Last_Name like '%" . $search_value . "%' OR Admission_Sessions.Name like '%" . $search_value . "%' OR Admission_Types.Name like '%" . $search_value . "%' OR Students.Step like '%" . $search_value . "%' OR Students.Father_Name like '%" . $search_value . "%' OR Students.Email like '%" . $search_value . "%' OR Students.Contact like '%" . $search_value . "%' OR Sub_Courses.Short_Name like '%" . $search_value . "%')";
  }
}


$filterQueryUser = "";
if (isset($_SESSION['filterByUser'])) {
  $filterQueryUser = $_SESSION['filterByUser'];
}

$filterByDepartment = "";
if (isset($_SESSION['filterByDepartment'])) {
  $filterByDepartment = $_SESSION['filterByDepartment'];
}

$filterByDate = "";
if (isset($_SESSION['filterByDate'])) {
  $filterByDate = $_SESSION['filterByDate'];
}

$filterBySubCourse = "";
if (isset($_SESSION['filterBySubCourses'])) {
  $filterBySubCourse = $_SESSION['filterBySubCourses'];
}

$filterByStatus = "";
if (isset($_SESSION['filterByStatus'])) {
  $filterByStatus = $_SESSION['filterByStatus'];
}


$filterByVertical = "";
if ( isset($_SESSION['filterByVerticalType'])) {
  $filterByVertical = $_SESSION['filterByVerticalType'];
}


$searchQuery .= $filterByDepartment . $filterQueryUser . $filterByDate . $filterBySubCourse . $filterByStatus.$filterByVertical;
  ## Fetch records
  $result_record = "SELECT Students.ID, Students.Unique_ID, Students.Enrollment_No, Students.OA_Number,
  Students.Step, Students.Created_At, Students.Process_By_Center, Students.Processed_To_University, 
  CONCAT(Students.First_Name, IF(Students.Middle_Name!='', CONCAT(' ', Students.Middle_Name), ''), ' ',
  Students.Last_Name) as Name, Students.Father_Name, Students.Mother_Name, Admission_Types.`Name` as Adm_Type,
  Admission_Sessions.`Name` as Session, Students.Duration, Modes.`Name` as Mode, Courses.`Name` as Course,
  Sub_Courses.`Name` AS Sub_Course, Sub_Courses.Short_Name as Short_Name, Students.Email, Students.Contact,
  Students.Alternate_Email, Students.Alternate_Contact, Students.Aadhar_Number, Students.DOB,
  Students.Employement_Status, Students.Gender, Students.Category, REPLACE(JSON_EXTRACT(Students.Address,
  '$.present_address'), '\"', '') as Address, REPLACE(JSON_EXTRACT(Students.Address, '$.present_city'), '\"', '') as City,
  REPLACE(JSON_EXTRACT(Students.Address, '$.present_district'), '\"', '') as District,
  REPLACE(JSON_EXTRACT(Students.Address, '$.present_state'), '\"', '') as State,
  REPLACE(JSON_EXTRACT(Students.Address, '$.present_pincode'), '\"', '') as Pincode,
  Students.Nationality, Students.Added_For,Students.abc_id,Students.deb_id,Students.Sub_Course_ID as subcourseid,Students.Admission_Session_ID as admsess FROM Students LEFT JOIN Admission_Types ON
  Students.Admission_Type_ID = Admission_Types.ID LEFT JOIN Admission_Sessions ON
  Students.Admission_Session_ID = Admission_Sessions.ID LEFT JOIN Modes ON Students.Mode_ID = Modes.ID 
  LEFT JOIN Courses ON Students.Course_ID = Courses.ID LEFT JOIN Sub_Courses ON 
  Students.Sub_Course_ID = Sub_Courses.ID WHERE Students.University_ID = ".$_SESSION['university_id']." $searchQuery $role_query $step_query  $session_query ORDER BY ID DESC";
//   echo($result_record);die;

  $empRecords = mysqli_query($conn, $result_record);
   //echo "<pre>"; print_r($empRecords);die;
  $data[] = $header;
  
  while ($row = mysqli_fetch_row($empRecords)) {
    //  echo('<pre>');print_r($row);die;
     
     $abcID = isset($row[34]) ? $row[34] :'';
     $debID = isset($row[35]) ? $row[35] :'';
     $userid = $row[33];
     $courseid = $row[36];
     $sessionid = $row[37];
     $studentid = $row[0];
     $studentsession = $row[13];
     unset($row[36]);
     unset($row[37]);
    // Added_For
    if($_SESSION['Role']=='Center'){
      $user1 = $conn->query("SELECT ID, Code, Name FROM Users WHERE ID = ".$row[33]."");
    }else{
      $user1 = $conn->query("SELECT ID, Code, Name FROM Users WHERE ID = ".$row[33]." AND Role = 'Center'");
      if($user1->num_rows==0){
        $user1 = $conn->query("SELECT Users.ID, Code, Name FROM Users LEFT JOIN Center_SubCenter ON Users.ID = Center_SubCenter.Center WHERE `Sub_Center` = ".$row[33]);
      }
    }
    
    if($user1->num_rows>0){
      $user = mysqli_fetch_array($user1);
    }else{
      $user['Name'] = "";
      $user['Code'] = "";
      $user['ID'] = 0;
    }

    // RM
     $rm['Name'] = "";
      if (!empty($user) || $_SESSION['Designation']!="University") {
        // RM
        $rm = $conn->query("SELECT CONCAT(Users.Name, ' (', Users.Code, ')') as Name FROM University_User LEFT JOIN Users ON University_User.Reporting = Users.ID AND University_User.University_ID = " . $_SESSION['university_id'] . " WHERE University_User.`User_ID` = " . $user['ID'] . " AND University_User.University_ID = " . $_SESSION['university_id']);
        if ($rm->num_rows > 0) {
          $rm = mysqli_fetch_array($rm);
        } else {
          $rm = $user;
        }
      }
       unset($row[34]);
       unset($row[35]);
    // Academics
    $courses = array('High School', 'Intermediate', 'UG', 'PG', 'Other');
    foreach($courses as $course){
      $academics = $conn->query("SELECT Type, Subject, `Year`, `Board/Institute`, Marks_Obtained, Max_Marks, Total_Marks FROM Student_Academics WHERE Student_ID = $row[0] AND Type = '$course'");
      if($academics->num_rows>0){
        $academic = mysqli_fetch_row($academics);
      }else{
        $academic = array($course, '', '', '', '', '', '');
      }
      $row = array_merge($row, $academic);
    }
    

    array_push($row, $user['Code']);
    array_push($row, $user['Name']);
    array_push($row, $rm['Name']);
    //$row[4] = date("d-m-Y H:i A", strtotime($row[4]));
    $row[4] = !empty($row[4]) ? $row[4] : "";
    $row[5] = !empty($row[5]) ? date("d-m-Y H:i A", strtotime($row[5])) : "";
    $row[6] = !empty($row[6]) ? date("d-m-Y H:i A", strtotime($row[6])) : "";
    $encode = base64_encode($row[0]."W1Ebt1IhGN3ZOLplom9I");
    array_push($row, '<i><a href="https://'.$_SERVER['HTTP_HOST'].'/app/applications/zip?id='.$encode.'">Click Here</a></i>');
    if($_SESSION['Role']!='Sub-Center'){
      $student_fee = $conn->query("SELECT Fee FROM Student_Ledgers WHERE Student_ID = $row[0] LIMIT 1");
      if($student_fee->num_rows>0){
        $student_fee = $student_fee->fetch_assoc();
        $student_fee_val = json_decode($student_fee['Fee'], true);
        $student_fee = json_decode($student_fee['Fee'], true);
        $fee_structures = $conn->query("SELECT ID, Sharing FROM Fee_Structures WHERE University_ID = ".$_SESSION['university_id']." ORDER BY Fee_Applicable_ID");
        while($fee_structure = $fee_structures->fetch_assoc()){
          array_push($row, $student_fee[$fee_structure['ID']]??"");
        //   if($fee_structure['Sharing']==1){
        //     //   Fee_Structure_ID = ".$fee_structure['ID']." AND
        //     $sharing = $conn->query("SELECT Fee FROM Fee_Variables WHERE Code = ".$user['ID']." AND University_ID = ".$_SESSION['university_id']."");
        //     if($sharing->num_rows>0){
        //       $sharing = $sharing->fetch_assoc();
              
        //       array_push($row, $sharing['Fee']);
        //     }else{
        //       array_push($row, 0);
        //     }
        //   } else {
        //       array_push($row, 0);
        //   }
        }
        // print_r($student_fee);
        array_push($row, array_sum($student_fee));
        // print_r($row);die;
        //$totalFee = $conn->query("select * from Fee_Variables where Code=$userid and University_ID=".$_SESSION['university_id']." ");
        // print_r();die;
       // $feearr = json_decode($totalFee->fetch_assoc()['Fee'],true);
        //$feeSharing =array_values($feearr)[0]??0;
        $totalFee = $conn->query("SELECT * FROM Fee_Variables WHERE Code = $userid AND University_ID = ".$_SESSION['university_id']);
        
        $feeSharing = 0;
        if ($totalFee && $totalFee->num_rows > 0) {
            $feeVarRow = $totalFee->fetch_assoc();
            if (!empty($feeVarRow['Fee'])) {
                $feearr = json_decode($feeVarRow['Fee'], true);
                if (is_array($feearr) && !empty($feearr)) {
                    $feeSharing = array_values($feearr)[0] ?? 0;
                }
            }
        }
        // print_r();die;
      } else {
              array_push($row, 0);
              array_push($row, 0);
              array_push($row, 0);
              $feeSharing = 0;
          }
    }
    //unset($row[32]);
    unset($row[33]);
    if($_SESSION['Role']=='Sub-Center'){
      unset($row[5]);
      unset($row[68]);
      unset($row[69]);
      unset($row[70]);
    }

    if(!empty($row[1])){
      $row[0] = $row[1];
    }else{
      $row[0] = '<b>'.sprintf("%'.06d\n", $row[0]).'</b>';
    }

    unset($row[1]);
    
    if(!in_array('Course Duration',$header)){
        array_push($header,'Course Duration');
    }
    // echo '<pre>';
    // print_r($row[16]);
$course_name = $row[16];
$skillduration = '';
$duration_text = '';
    // // 2️ Determine skill duration based on course name keywords
if (stripos($course_name, '11') !== false && stripos($course_name, 'adv') !== false) {
    $skillduration = '2'; // 11 + Adv
} elseif (stripos($course_name, '11') !== false) {
    $skillduration = '1'; // only 11
} elseif (stripos($course_name, '6') !== false) {
    $skillduration = '6';
} elseif (stripos($course_name, '3') !== false) {
    $skillduration = '3';
} else {
    // fallback if nothing matched
    $skillduration = $duration_text;
}

// // 3️ Map skill duration to program details
if ($skillduration == "3") {
    $durations = "Certification Course";
    $hours = 160;
    $data1['skillDurations'] = "3/Certification";
} elseif ($skillduration == "6") {
    $durations = "Certified Skill Diploma";
    $hours = 320;
    $data1['skillDurations'] = "6/Certified";
} elseif ($skillduration == "2") {
    $durations = "Advanced Certification Skill Diploma";
    $hours = 960;
    $data1['skillDurations'] = "11/Advanced-Certified";
} elseif ($skillduration == "1") {
    $durations = "Certified Skill Diploma";
    $hours = 960;
    $data1['skillDurations'] = "11/Certified";
} else {
    // fallback if duration doesn't match any known pattern
    $durations = "Unknown Duration";
    $hours = 'NA';
    $data1['skillDurations'] = $duration_text ?: 'NA';
}
    //array_push($row,$data1['skillDurations']);
    
     //echo "<pre>";print_r($row);die;
     
     //unset($row[34]);
     //unset($row[35]);
    
     array_push($row,$abcID);
     array_push($row,$debID);
        if(!in_array('Fee Sharing',$header)){
            array_push($header,'Fee Sharing');
        }
        // array_push($row,100-$feeSharing);
//   echo "<pre>";print_r($row);die;
        // $ledgers = $conn->query("SELECT * FROM Student_Ledgers WHERE Student_ID = $studentid AND Status = 1 AND Duration = $studentsession and type=1");
        // $feesdata = $ledgers->fetch_assoc();
        // $feearr = json_decode($feesdata['Fee'],true);
        // $totalPaybleFee = array_sum($feearr??[]);
        // array_push($row,$totalPaybleFee);
    
    $data[] = $row;
    
  }
//   die;
  $xlsx = SimpleXLSXGen::fromArray( $data )->downloadAs('Students.xlsx');
