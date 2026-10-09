    <?php
    ini_set('display_errors', 1);
    ## Database configuration
    include '../../../includes/db-config.php';
    session_start();
    
    $stepsLog = '';
    $student_adm_exam = [];
    $student_ids = [];
    $givenExamSession = '';
    
    ## Read value
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length'] ?? "all"; // Rows display per page
    $totalRecords = 0;
    $totalRecordwithFilter = 0;
    $data = [];
    
    if (isset($_REQUEST['month']) && isset($_REQUEST['year'])) {
    
      $month = mysqli_real_escape_string($conn, $_REQUEST['month']);
      $year = mysqli_real_escape_string($conn, $_REQUEST['year']);
      $month = (strlen($month) == '1') ? '0' . $month : $month;
      $stepsLog .= date(DATE_ATOM) . " :: month => $month year => $year  pageType => " . $_REQUEST['pageType'] . " \n\n";
      $monthName = date('M', mktime(0, 0, 0, $month, 10));
      $yearCom = $year - 2000;
      $givenExamSession = $monthName . '-' . $yearCom;
      $student_list = examAppearListOfStudent($month, $year);
    // echo('<pre>');print_r($student_list);die;
      if (!empty($student_list)) {
        studentDetails($student_list);
        $stepsLog .= date(DATE_ATOM) . "data get from studentDetails function \n\n student_ids => " . json_encode($student_ids) . "\n\n student_adm_exam => " . json_encode($student_adm_exam) . "\n\n";
        if (!empty($student_ids)) {
    
          if (isset($_POST['order'])) {
            $columnIndex = $_POST['order'][0]['column']; // Column index
            $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
            $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
          }
          $searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']); // Search value
    
          if (isset($columnSortOrder)) {
            $orderby = "ORDER BY $columnName $columnSortOrder";
          } else {
            $orderby = "ORDER BY Students.ID ASC";
          }
    
          ## Search 
          $searchQuery = "";
          if ($searchValue != '') {
            $searchQuery = " AND (Students.First_Name LIKE '%$searchValue%' OR Students.Middle_Name LIKE '%$searchValue%' OR Students.Last_Name LIKE '%$searchValue%' OR Students.Email LIKE '%$searchValue%' OR Students.Enrollment_No LIKE '%$searchValue%' OR Sub_Courses.Name LIKE '%$searchValue%' OR Courses.Name LIKE '%$searchValue%' OR Users.Name LIKE '%$searchValue%')";
          }
    
          if (isset($_REQUEST['vertical']) ) {
            if ($_REQUEST['vertical'] == '0') {
              $verticalTypeId = mysqli_real_escape_string($conn,$_REQUEST['vertical']);
              $searchQuery .= " AND Users.Vertical = '$verticalTypeId'";
            } else if (!empty($_REQUEST['vertical'])) {
              $verticalTypeId = mysqli_real_escape_string($conn,$_REQUEST['vertical']);
              $searchQuery .= " AND Users.Vertical = '$verticalTypeId'";
            }
          }
    
          $center_search = "";
          if (isset($_REQUEST['center']) && !empty($_REQUEST['center'])) {
            $center_id = mysqli_real_escape_string($conn,$_REQUEST['center']);
            $allSubCenterId = getAllSubCenter($center_id);
            if (!empty($allSubCenterId)) {
              $center_search = " AND (Students.Added_For = '$center_id' OR Students.Added_For IN ($allSubCenterId))";
            } else {
              $center_search = " AND Students.Added_For = '$center_id' ";
            }
          }
    
          if (isset($_REQUEST['sub_center']) && !empty($_REQUEST['sub_center'])) {
            $sub_center = mysqli_real_escape_string($conn,$_REQUEST['sub_center']);
            $searchQuery .= " AND Students.Added_For = '$sub_center'";
          } else {
            $searchQuery .= $center_search;
          }
          
          ## Total number of records without filtering
          $all_count = $conn->query("SELECT COUNT(ID) as `allcount` FROM `Students` WHERE ID IN (" . implode(',', $student_ids) . ") ");
          $records = mysqli_fetch_assoc($all_count);
          $totalRecords = $records['allcount'];
          if ($rowperpage == "all") {
            $rowperpage = $totalRecords;
          }
    
          ## Total number of record with filtering
          //echo "SELECT COUNT(Students.ID) as `filtered` FROM `Students` LEFT JOIN Sub_Courses ON Sub_Courses.ID = Students.Sub_Course_ID LEFT JOIN Users ON Users.ID = Students.Added_For LEFT JOIN Courses ON Courses.ID = Students.Course_ID WHERE Students.ID IN (" . implode(',', $student_ids) . ") $searchQuery";
          $filter_count_query = "SELECT COUNT(Students.ID) as `filtered` FROM `Students` LEFT JOIN Sub_Courses ON Sub_Courses.ID = Students.Sub_Course_ID LEFT JOIN Users ON Users.ID = Students.Added_For LEFT JOIN Courses ON Courses.ID = Students.Course_ID WHERE Students.ID IN (" . implode(',', $student_ids) . ") $searchQuery";
          $filter_count = $conn->query($filter_count_query);
          $stepsLog .= date(DATE_ATOM) . "filter count query => $filter_count_query \n\n";
          $records = mysqli_fetch_assoc($filter_count);
          $totalRecordwithFilter = $records['filtered'];
    
          ## Fetch records
          $exam_student_query = "SELECT Students.* , Users.Name as `center_name`, Users.Code , Courses.Name as `course` ,Sub_Courses.Name as `sub_course_name` FROM `Students` LEFT JOIN Sub_Courses ON Sub_Courses.ID = Students.Sub_Course_ID LEFT JOIN Users ON Users.ID = Students.Added_For LEFT JOIN Courses ON Courses.ID = Students.Course_ID WHERE Students.ID IN (" . implode(',', $student_ids) . ") $searchQuery $orderby LIMIT $row , $rowperpage";
          $stepsLog .= date(DATE_ATOM) . "exam student query => $exam_student_query \n\n";
          //echo $exam_student_query;
          $exam_student = $conn->query($exam_student_query);
          while ($row = mysqli_fetch_assoc($exam_student)) {
            //   echo('<pre>');print_r($student_adm_exam[$row['ID']]['exam_session']);die;
            list($exam_confermation_data, $reappear_paymentStatus, $checkAllowforReAttemptOrNot) = checkExamRecord($row['ID'], $student_adm_exam[$row['ID']]['attempts'], $student_adm_exam[$row['ID']]['exam_session']);
        //   echo('<pre>');print_r($student_adm_exam[$row['ID']]['exam_session']);die;
            $data[] = array(
              "ID" => $row['ID'],
              "Photo" => empty($row['Location']) ? '/assets/img/default-user.png' : $row['Location'],
              "First_Name" => $row['First_Name'] . ' ' . $row['Middle_Name'] . ' ' . $row['Last_Name'],
              "Email" => $row['Email'],
              "Enrollment_No" => $row['Enrollment_No'],
              "Unique_ID" => $row['Unique_ID'],
              "Course" => $row['course'],
              "center_name" => $row['center_name'].'('.$row['Code'].')',
              "Sub_Course" => $row['sub_course_name'],
              "admission_session" => $student_adm_exam[$row['ID']]['admission_session'],
              "exam_session" => $student_adm_exam[$row['ID']]['exam_session'],
              'attempt' => $student_adm_exam[$row['ID']]['attempts'],
              'exam_status' => isset($student_adm_exam[$row['ID']]['exam_status']) ? $student_adm_exam[$row['ID']]['exam_status'] : 'None',
              'result_status' => isset($student_adm_exam[$row['ID']]['result_status']) ? $student_adm_exam[$row['ID']]['result_status'] : 'None',
              "attempt1_session" => !empty($exam_confermation_data) ? $exam_confermation_data['attempt1_session'] : "Not Attempt ",
              "attempt1_status" => !empty($exam_confermation_data) ? $exam_confermation_data['attempt1_status'] : "Not Attempt",
              "attempt2_payment" => !empty($exam_confermation_data) ? $exam_confermation_data['attempt2_payment'] : "Not Attempt",
              "attempt2_session" => !empty($exam_confermation_data) ? $exam_confermation_data['attempt2_session'] : "Not Attempt",
              "attempt2_status" => !empty($exam_confermation_data) ? $exam_confermation_data['attempt2_status'] : "Not Attempt",
              "attempt3_payment" => !empty($exam_confermation_data) ? $exam_confermation_data['attempt3_payment'] : "Not Attempt",
              "attempt3_session" => !empty($exam_confermation_data) ? $exam_confermation_data['attempt3_session'] : "Not Attempt",
              "attempt3_status" => !empty($exam_confermation_data) ? $exam_confermation_data['attempt3_status'] : "Not Attempt",
              "attempt4_payment" => !empty($exam_confermation_data) ? $exam_confermation_data['attempt4_payment'] : "Not Attempt",
              "attempt4_session" => !empty($exam_confermation_data) ? $exam_confermation_data['attempt4_session'] : "Not Attempt",
              "attempt4_status" => !empty($exam_confermation_data) ? $exam_confermation_data['attempt4_status'] : "Not Attempt",
              "reappear_paymentStatus" => $reappear_paymentStatus,
              "allowforReAttemptOrNot" => $checkAllowforReAttemptOrNot
              
            );
          }
        //   echo('<pre>');print_r($data);die;
        }
      } else {
        $stepsLog .= date(DATE_ATOM) . "No student present in this Session \n\n Student list => " . json_encode($student_list) . "\n\n";
      }
    }
    
    
    ## Response
    $response = array(
      "draw" => intval($draw),
      "iTotalRecords" => $totalRecords,
      "iTotalDisplayRecords" => $totalRecordwithFilter,
      "aaData" => $data
    );
    
    saveLog($response);
    
    function getAdmissionMonthAndYear(): array
    {
      global $conn;
      global $stepsLog;
      $adm_month_year = [];
      $stepsLog .= date(DATE_ATOM) . " :: query => SELECT ID , Name FROM Admission_Sessions \n\n";
      $admission_names = $conn->query("SELECT ID , Name FROM Admission_Sessions");
      if ($admission_names->num_rows > 0) {
        while ($admission_name = mysqli_fetch_assoc($admission_names)) {
          list($month, $year) = explode('-', $admission_name['Name']);
          $month = ucwords(strtolower(substr($month, 0, 3)));
          $date = DateTime::createFromFormat('M', $month);
          $monthNumber = $date ? $date->format('n') : null;
          $year = (strlen($year) > 2) ? date('y', strtotime("$year-01-01")) : $year;
          $adm_month_year[$admission_name['ID']] = ['month' => $monthNumber, 'month_name' => $month, 'year' => $year];
        }
      }
      return $adm_month_year;
    }
    
    function examAppearListOfStudent($month, $year): array
    {
      global $conn;
      global $stepsLog;
      global $givenExamSession;
      $student_list = [];
      $adm_month_year = getAdmissionMonthAndYear();
      $role_query = '';
      if ($_SESSION['Role'] == 'Sub-Center' || $_SESSION['Role'] == 'Center') {
        $role_query = str_replace('{{ table }}', 'Students', isset($_SESSION['RoleQuery']) ? $_SESSION['RoleQuery'] : '');
        $role_query = str_replace('{{ column }}', 'Added_For', $role_query);
      }
      /**
       * In this list 4 type of Student Data is came 
       * 1) Those student who's exam session is this month and year
       * 2) Student those are not fill the form in last exam session like: Dec-24 Student not fill the form those student details also need to show (Only check till 4 month)
       * 3) Student fill the form but not appaer in the exam (But, only show there record once center pay the re-appear fee)
       * 4) Student appear in the exam but fail (But, only show there record once center pay the re-appear fee)
       */
    
      $student_record_query = "SELECT ID,Enrollment_No,Admission_Session_ID,Duration,Unique_ID FROM `Students` WHERE University_ID = '41' $role_query";
      $stepsLog .= date(DATE_ATOM) . " student_record => $student_record_query \n\n";
      $student_records = $conn->query($student_record_query);
      if ($student_records->num_rows > 0) {
        $i = 0;
        while ($student_record = mysqli_fetch_assoc($student_records)) {
            // echo('<pre>');print_r($student_record);die;
          if (array_key_exists($student_record['Admission_Session_ID'], $adm_month_year)) {
            $duration = preg_match("/\//", $student_record['Duration']) ? (explode('/', $student_record['Duration']))[0] : $student_record['Duration'];
            $studentExamSession = date_create(date('y-m-d', strtotime($adm_month_year[$student_record['Admission_Session_ID']]['year'] . "-" . $adm_month_year[$student_record['Admission_Session_ID']]['month'] . "-01")));
            date_modify($studentExamSession, "+" . ((int)$duration - 1) . "Month");
            
            // All student belong from this given session 
            if (date_create(date('y-m-d', strtotime($year . '-' . $month . '-01'))) == $studentExamSession) {
              $student_list[$i] = array(
                'id@admission@exam@attempts@uniqueId' => $student_record['ID'] . '@' . $adm_month_year[$student_record['Admission_Session_ID']]['month_name'] . '-' . $adm_month_year[$student_record['Admission_Session_ID']]['year'] . '@' . date_format($studentExamSession, 'M') . '-' . date_format($studentExamSession, 'y') . '@1@' . $student_record['Unique_ID'],
              );
              $i++;
            } elseif (checkStudentExistFromLastFourSession(date_format($studentExamSession, 'y-m-d'), $month, $year)) {
              // Student belong from last 4 session
              $checkFillExamFormOrNot_query = "SELECT * , CASE WHEN attempt2_session IS NULL THEN '1' WHEN attempt3_session IS NULL THEN '2' WHEN attempt4_session IS NULL THEN '3' ELSE 'Attempt_completed' END AS attempts FROM `Examination_Confirmation` WHERE Student_Id = '" . $student_record['ID'] . "'";
              $checkFillExamFormOrNot = $conn->query($checkFillExamFormOrNot_query);
              //$stepsLog .= date(DATE_ATOM) . " :: checkFillExamFormOrNot_query => $checkFillExamFormOrNot_query \n\n";
              if ($checkFillExamFormOrNot->num_rows > 0) {
                $appearExamRecord = mysqli_fetch_assoc($checkFillExamFormOrNot);
                $currentAttempt = 'not match';
                for ($j = 1; $j <= 4; $j++) {
                  if ($appearExamRecord["attempt{$j}_session"] == $givenExamSession) {
                    $currentAttempt = $j;
                    break;
                  }
                }
                if ($currentAttempt == 'not match') {
                  $attempt = $appearExamRecord['attempts'];
                  if ((is_null($appearExamRecord['attempt' . $attempt . '_status']) || empty($appearExamRecord['attempt' . $attempt . '_status'])) && $attempt > 1) {
                    $attempt = $attempt - 1;
                  }
                  $attemptStatus = $appearExamRecord['attempt' . $attempt . '_status'];
                  if ($attemptStatus == '2' || $attemptStatus == '3') {
                    $paymentStatus = $appearExamRecord['attempt' . ($attempt + 1) . '_payment'];
                    if (!empty($paymentStatus) || !is_null($paymentStatus)) {
                      $student_list[$i] = array(
                        'id@admission@exam@attempts@uniqueId' => $student_record['ID'] . '@' . $adm_month_year[$student_record['Admission_Session_ID']]['month_name'] . '-' . $adm_month_year[$student_record['Admission_Session_ID']]['year'] . '@' . date_format($studentExamSession, 'M') . '-' . date_format($studentExamSession, 'y') . '@' . ($attempt + 1) . '@' . $student_record['Unique_ID'],
                      );
                      $i++;
                    }
                  }
                } else {
                  $student_list[$i] = array(
                    'id@admission@exam@attempts@uniqueId' => $student_record['ID'] . '@' . $adm_month_year[$student_record['Admission_Session_ID']]['month_name'] . '-' . $adm_month_year[$student_record['Admission_Session_ID']]['year'] . '@' . date_format($studentExamSession, 'M') . '-' . date_format($studentExamSession, 'y') . '@' . $currentAttempt . '@' . $student_record['Unique_ID'],
                  );
                  $i++;
                }
              } else {
                //not fill the form
                // $student_list[$i] = array(
                //   'id@admission@exam@attempts@uniqueId' => $student_record['ID'] . '@' . $adm_month_year[$student_record['Admission_Session_ID']]['month_name'] . '-' . $adm_month_year[$student_record['Admission_Session_ID']]['year'] . '@' . date_format($studentExamSession, 'M') . '-' . date_format($studentExamSession, 'y') . '@1@' . $student_record['Unique_ID'],
                // );
                $i++;
              }
            }
          } else {
            $stepsLog .= date(DATE_ATOM) . " not came inside array key exist => ". json_encode($student_record) ." \n\n";
          }
        }
      }
      return $student_list;
    }
    
    function checkStudentExistFromLastFourSession($studentExamSession, $month, $year)
    {
    
      $rangeEnd = date_create(date('y-m-d', strtotime($year . '-' . $month . '-01')));
      $rangeStart = date_create(date('y-m-d', strtotime($year . '-' . $month . '-01')));
      date_modify($rangeStart, "-3 Month");
    
      $checkDate = DateTime::createFromFormat('y-m-d', $studentExamSession);
      $startDate = DateTime::createFromFormat('y-m-d', date_format($rangeStart, 'y-m-d'));
      $endDate = DateTime::createFromFormat('y-m-d', date_format($rangeEnd, 'y-m-d'));
    
      return ($checkDate >= $startDate && $checkDate <= $endDate) ? true : false;
    }
    
    function studentDetails($student_list)
    {
    
      global $stepsLog;
      global $student_ids;
      global $student_adm_exam;
      foreach ($student_list as $value) {
        $student_info = explode('@', $value['id@admission@exam@attempts@uniqueId']);
        $student_ids[] = $student_info[0];
        $student_adm_exam[$student_info[0]] = array(
          'admission_session' => $student_info[1],
          'exam_session' => $student_info[2],
          'attempts' => $student_info[3],
          "uniqueId" => $student_info[4]
        );
      }
  
      $stepsLog .= date(DATE_ATOM) . " student_ids => " . json_encode($student_ids) . "\n\n student_adm_exam => " . json_encode($student_adm_exam) . "\n\n";
      if (isset($_REQUEST['pageType']) && ($_REQUEST['pageType'] == 'examFormSubmitted' || $_REQUEST['pageType'] == 'examComplete')) {
        examFormSubmittedStudentList();
        $stepsLog .= date(DATE_ATOM) . " data from examFormSubmittedStudentList : student_ids " . json_encode($student_ids) . "\n\n student_adm_exam => " . json_encode($student_adm_exam) . "\n\n";
      }
      if (isset($_REQUEST['pageType']) && $_REQUEST['pageType'] == 'examComplete') {
        checkExamCompletedAndNotCompletedStudentList();
        $stepsLog .= date(DATE_ATOM) . " data from checkExamCompletedAndNotCompletedStudentList : student_ids " . json_encode($student_ids) . "\n\n student_adm_exam => " . json_encode($student_adm_exam) . "\n\n";
      }
    }
    
    
    function examFormSubmittedStudentList()
    {
    
      global $conn;
      global $stepsLog;
      global $student_ids;
      global $student_adm_exam;
      global $givenExamSession;
      $examFromSubmitted_query = "SELECT * FROM `Examination_Confirmation` WHERE Student_Id IN (" . implode(',', $student_ids) . ")";
      $examFromSubmitted = $conn->query($examFromSubmitted_query);
      $stepsLog .= date(DATE_ATOM) . "examFormSubmitted query => $examFromSubmitted_query \n\n";
      if ($examFromSubmitted->num_rows > 0) {
        $examFromSubmitted = mysqli_fetch_all($examFromSubmitted, MYSQLI_ASSOC);
        $student_details = array_column($examFromSubmitted, 'Student_Id');
        foreach ($student_adm_exam as $key => $value) {
          if (!in_array($key, $student_details)) {
            unset($student_adm_exam[$key]);
            $studentIdKey = array_search($key, $student_ids);
            unset($student_ids[$studentIdKey]);
          } else {
            $attempt = $student_adm_exam[$key]['attempts'];
            $examFromSubmitted_key = array_search($key, $student_details);
            if ($examFromSubmitted[$examFromSubmitted_key]["attempt{$attempt}_session"] == $givenExamSession) {
              continue;
            } else {
              unset($student_adm_exam[$key]);
              $studentIdKey = array_search($key, $student_ids);
              unset($student_ids[$studentIdKey]);
            }
          }
        }
      } else {
        $student_ids = [];
        $student_adm_exam = [];
      }
    }
    
    function checkExamCompletedAndNotCompletedStudentList()
    {
    
      /**
       * Work required in function 
       * 1) Get to know on the enrollment number basis student is appear and not appear 
       * 2) Exam appear and not appear status also need to update in  Examination_Confirmation table 
       * 3) Need to check the result from the marksheet table and on the basis of marksheet update the response
       * 4) One thing to remmber if student appear and in that atteempt there result status is uploaded then in that 
       * case further that attempt status is not update 
       * 
       */
      global $conn;
      global $stepsLog;
      global $student_ids;
      global $student_adm_exam;
      checkStudentExamStatus();
      $stepsLog .= date(DATE_ATOM) . " :: Exam Status Check => " . json_encode($student_adm_exam) . "\n\n";
      foreach ($student_adm_exam as $key => $value) {
        if (!isset($value['result_status'])) {
          $student_adm_exam[$key]['result_status'] = ($student_adm_exam[$key]['exam_status'] == 'Attempt') ? checkResult($key) : 'Fail';
          checkAndUpdateExamAttemptStatus($student_adm_exam[$key], $key);
        }
      }
    }
    
    function checkStudentExamStatus()
    {
    
      global $conn;
      global $stepsLog;
      global $student_ids;
      global $student_adm_exam;
      $year = $_REQUEST['year'];
      $date = DateTime::createFromFormat('!m', $_REQUEST['month']);
      $monthName = $date->format('F');
      $examSession = strtoupper($monthName) . " " . $year;
      $stepsLog .= date(DATE_ATOM) . " :: Inside Check Student Exam Status Exam Session => " . $examSession . "\n\n";
    
      $uniqueIds = array_column($student_adm_exam, 'uniqueId');
      $ids = array_keys($student_adm_exam);
      $studnetIdsAndUniqueIds = array_combine($ids, $uniqueIds);
      $stepsLog .= date(DATE_ATOM) . " :: Before uniqueIds => " . json_encode($uniqueIds) . "\n\n";
      //By this loop we filter the data from those who's response already know
      foreach ($studnetIdsAndUniqueIds as $key => $value) {
        $attempt = $student_adm_exam[$key]['attempts'];
        $columnName = "attempt" . $attempt . "_status";
        $checkStatus = $conn->query("SELECT $columnName FROM `Examination_Confirmation` WHERE Student_Id = '$key'");
        $checkStatus = mysqli_fetch_column($checkStatus);
        if (!empty($checkStatus) || !is_null($checkStatus)) {
          if ($checkStatus != '4') {
            $student_adm_exam[$key]['exam_status'] = ($checkStatus == '3') ? "Not Attempt" : "Attempt";
            $student_adm_exam[$key]['result_status'] = ($checkStatus == '2' || $checkStatus == '3') ? "Fail" : "Pass";
          } else {
            continue;
          }
          $studentIdKey = array_search($value, $uniqueIds);
          unset($uniqueIds[$studentIdKey]);
        }
      }
      $stepsLog .= date(DATE_ATOM) . " :: After uniqueIds => " . json_encode($uniqueIds) . "\n\n";
      if (!empty($uniqueIds)) {
        $responses = getExamApiResponse($uniqueIds);
        $stepsLog .= date(DATE_ATOM) . " :: Response of API => " . json_encode($responses) . "\n\n";
        foreach ($student_adm_exam as $id => $studentInfo) {
          if (!isset($studentInfo['exam_status'])) {
            $uniqueId = $studentInfo['uniqueId'];
            $response = json_decode($responses[$uniqueId], true);
            $message = '';
            if (empty($response['data'])) {
              unset($student_adm_exam[$id]);
              $studentIdKey = array_search($id, $student_ids);
              unset($student_ids[$studentIdKey]);
            } else if (!empty($response['data'])) {
              $numberofpaper = 0;
              $sub_attempt = 0;
              foreach ($response['data'] as $key => $value) {
                if ($examSession == $value['ExaminationName']) {
                  $numberofpaper++;
                  if ($value['Status']) {
                    $sub_attempt++;
                    break;
                  }
                }
              }
              if ($numberofpaper > 0) {
                $message = ($sub_attempt > 0) ? 'Attempt' : 'Not Attempt';
              } else {
                $stepsLog .= date(DATE_ATOM) . " :: Exam Not Coduct => " . $id . "\n\n";
                unset($student_adm_exam[$id]);
                $studentIdKey = array_search($id, $student_ids);
                unset($student_ids[$studentIdKey]);
                continue;
              }
              $student_adm_exam[$id]['exam_status'] = $message;
            }
          }
        }
      }
    }
    
   
    // function getExamApiResponse($uniqueIds)
    // {
    // //  echo('<pre>');print_r($uniqueIds);die;
    //   $responses = [];
    //   $multiCurl = curl_multi_init();
    //   $curlHandles = [];
    
    //   foreach ($uniqueIds as $id) {
    //     $url = 'https://arni.exam-portal.in/api/answer-sheets/list?uniqueId=' . $id;
    //     $curl = curl_init();
    
    //     curl_setopt_array($curl, array(
    //       CURLOPT_URL => $url,
    //       CURLOPT_RETURNTRANSFER => true,
    //       CURLOPT_ENCODING => '',
    //       CURLOPT_MAXREDIRS => 10,
    //       CURLOPT_TIMEOUT => 0,
    //       CURLOPT_FOLLOWLOCATION => true,
    //       CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //       CURLOPT_CUSTOMREQUEST => 'POST',
    //       CURLOPT_HTTPHEADER => array(
    //         'Content-Type: application/json',
    //       ),
    //     ));
    
    //     curl_multi_add_handle($multiCurl, $curl);
    //     $curlHandles[$id] = $curl;
    //   }
    
    //   // Execute all requests concurrently
    //   do {
    //     $status = curl_multi_exec($multiCurl, $active);
    //   } while ($active && $status == CURLM_OK);
    
    //   // Collect responses
    //   foreach ($curlHandles as $id => $curl) {
    //     $responses[$id] = curl_multi_getcontent($curl);
    //     curl_multi_remove_handle($multiCurl, $curl);
    //     curl_close($curl);
    //   }
    //   curl_multi_close($multiCurl);
    
    //   return $responses;
    // }
   function getExamApiResponse($uniqueIds)
{

  $responses = [];
  $multiCurl = curl_multi_init();
  $curlHandles = [];

  foreach ($uniqueIds as $id) {
    $url = 'https://arni.exam-portal.in/api/answer-sheets/list?uniqueId=' . $id;
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
      ),
    ));

    curl_multi_add_handle($multiCurl, $curl);
    $curlHandles[$id] = $curl;
  }

  // Execute all requests concurrently
  do {
    $status = curl_multi_exec($multiCurl, $active);
  } while ($active && $status == CURLM_OK);

  // Collect responses
  foreach ($curlHandles as $id => $curl) {
    $responses[$id] = curl_multi_getcontent($curl);
    curl_multi_remove_handle($multiCurl, $curl);
    curl_close($curl);
  }
  curl_multi_close($multiCurl);

  return $responses;
}

    
    function checkResult($student_id)
    {
    
      global $stepsLog;
      global $conn;
      global $givenExamSession;
      $student_data_query = "SELECT ID, Duration , Enrollment_No , Sub_Course_ID FROM `Students` WHERE ID = '$student_id'";
      $student_data = $conn->query($student_data_query);
      $stepsLog .= date(DATE_ATOM) . "student_data_query => $student_data_query \n\n";
      $student_data = mysqli_fetch_assoc($student_data);
      $temp_subject_query = "SELECT Paper_Type , marksheets.obt_marks_ext , marksheets.obt_marks_int , marksheets.obt_marks , marksheets.status , marksheets.remarks , Syllabi.Min_Marks, Syllabi.Max_Marks FROM marksheets LEFT JOIN Syllabi ON marksheets.subject_id = Syllabi.ID WHERE marksheets.enrollment_no = '" . $student_data['Enrollment_No'] . "' AND Syllabi.Sub_Course_ID = '" . $student_data['Sub_Course_ID'] . "' AND CONCAT(CONCAT(UPPER(SUBSTRING(marksheets.exam_month, 1, 1)), LOWER(SUBSTRING(marksheets.exam_month, 2, 2))),'-',IF(CHAR_LENGTH(marksheets.exam_year) = '4' ,SUBSTRING(marksheets.exam_year,3,2) , SUBSTRING(marksheets.exam_year,1,2))) = '$givenExamSession'";
      $temp_subjects = $conn->query($temp_subject_query);
      $stepsLog .= date(DATE_ATOM) . " temp_subject query => $temp_subject_query \n\n";
      if ($temp_subjects->num_rows > 0) {
        $remark_status = 'Pass';
        while ($temp_subject = mysqli_fetch_assoc($temp_subjects)) {
          if (checkResultStatus($temp_subject) == 'Fail') {
            $remark_status = 'Fail';
            break;
          }
        }
        return $remark_status;
      } else {
        return "result not found";
      }
    }
    
    function checkResultStatus($temp_subject)
    {
      $total_obt = 0;
      if ($temp_subject['Min_Marks'] == 40 && $temp_subject['Max_Marks'] == 60) {
        $min_val = ($temp_subject['Min_Marks'] + $temp_subject['Max_Marks']) * 40 / 100;
      } else if ($temp_subject['Max_Marks'] == 100 && $temp_subject['Min_Marks'] == 40) {
        $min_val = $temp_subject['Min_Marks'];
      } else if ($temp_subject['Max_Marks'] == 100 && $temp_subject['Min_Marks'] == 100) {
        $min_val = ($temp_subject['Max_Marks']) * 40 / 100;
      }
    
      $obt_marks_ext = ($temp_subject['obt_marks_ext'] == 'AB') ? 'AB' : $temp_subject['obt_marks_ext'];
      $obt_marks_int = ($temp_subject['obt_marks_int'] == 'AB') ? 'AB' : $temp_subject['obt_marks_int'];
    
      if ($obt_marks_ext != 'AB' && $obt_marks_int != 'AB') {
        $total_obt = $total_obt + intval($obt_marks_ext) + intval($obt_marks_int);
      } else {
        $total_obt = (int) $total_obt + (int) $obt_marks_ext + (int) $obt_marks_int;
      }
      return ($total_obt < $min_val || $temp_subject['obt_marks_ext'] == 0 || $temp_subject['obt_marks_ext'] == 'AB') ? 'Fail' : 'Pass';
    }
    
    
    function checkAndUpdateExamAttemptStatus($student_status, $student_id)
    {
    
      global $stepsLog;
      global $conn;
      $attempt = $student_status['attempts'];
      $columnIndex = 'attempt' . $attempt . '_status';
      $checkStatus_query = "SELECT $columnIndex FROM `Examination_Confirmation` WHERE Student_Id = '$student_id'";
      $checkStatus = $conn->query($checkStatus_query);
      $stepsLog .= date(DATE_ATOM) . "checkstatus query => $checkStatus_query \n\n";
      $checkStatus = mysqli_fetch_column($checkStatus);
      $stepsLog .= date(DATE_ATOM) . " check status value =>  $checkStatus \n\n";
      if (empty($checkStatus) || $checkStatus == '4') {
        $update_status = '';
        if ($student_status['exam_status'] == 'Not Attempt') {
          $update_status = '3';
        } else {
        
          $update_status = match ($student_status['result_status']) {
            'Pass' => '1',
            'Fail' => '2',
            'result not found' => '4',
          };
        }
        $update_status_query = "UPDATE Examination_Confirmation SET $columnIndex = '$update_status' WHERE Student_Id = '$student_id'";
        $stepsLog .= date(DATE_ATOM) . " update_status_query => $update_status_query \n\n";
        $update_status = $conn->query($update_status_query);
      }
    }
    
    function checkExamRecord($id, $attempt, $examSession)
    {
    
      global $conn;
      global $stepsLog;
      global $givenExamSession;
      $exam_record = [];
      $reappear_paymentStatus = '';
      $lastAttemptMonthAndYear = calculateMonth($examSession, '3', 'checkExamSession');
      $nextAttemptMonthAndYear = calculateMonth($givenExamSession, '1', 'checkExamSession');
      $checkAllowforReAttemptOrNot = ($attempt == '4') ? "Not Allow" : checkAllowforReAttemptOrNot($lastAttemptMonthAndYear, $nextAttemptMonthAndYear);
      $checkExam = $conn->query("SELECT * FROM `Examination_Confirmation` WHERE Student_Id = '$id'");
      if ($checkExam->num_rows > 0) {
        $checkExam = mysqli_fetch_assoc($checkExam);
        $exam_record = $checkExam;
        if ($attempt < 4) {
          $payment_status = "attempt" . intval($attempt + 1) . "_payment";
          $reappear_paymentStatus = (is_null($exam_record[$payment_status]) || empty($exam_record[$payment_status])) ? 'unpaid' : 'paid';
        } else {
          $reappear_paymentStatus = 'unpaid';
        }
      }
      return [$exam_record, $reappear_paymentStatus, $checkAllowforReAttemptOrNot];
    }
    
    function calculateMonth($firstAttemptExamSession, $monthsToAdd, $type = null): string
    {
      list($firstAttemptMonth, $firstAttemptYear) = explode('-', $firstAttemptExamSession);
      $months = [
        "Jan" => 1,
        "Feb" => 2,
        "Mar" => 3,
        "Apr" => 4,
        "May" => 5,
        "Jun" => 6,
        "Jul" => 7,
        "Aug" => 8,
        "Sep" => 9,
        "Oct" => 10,
        "Nov" => 11,
        "Dec" => 12
      ];
      $firstAttemptMonthNumber = $months[$firstAttemptMonth];
      $totalMonth = $firstAttemptMonthNumber + $monthsToAdd;
      $lastAttemptMonthNumber = ($totalMonth - 1) % 12 + 1;
      $lastAttemptMonthName = array_search($lastAttemptMonthNumber, $months);
      $yearsToAdd = intdiv($totalMonth - 1, 12);
      $firstAttemptYear += 2000;
      $lastAttemptYear = $firstAttemptYear + $yearsToAdd;
      $lastDayOfAttempt = date_create(date("Y-m-t", strtotime("$lastAttemptMonthName $lastAttemptYear")));
      if ($type == 'checkExamSession') {
        return $lastAttemptMonthName . '-' . $lastAttemptYear;
      } else {
        return (date_create(date('y-m-d')) > $lastDayOfAttempt) ? "Attempt Over" : "Attempt left";
      }
    }
    
    function checkAllowforReAttemptOrNot($lastAttempt, $nextAttempt)
    {
    
      $lastAttemptDate = DateTime::createFromFormat('M-Y', $lastAttempt);
      $nextAttemptDate = DateTime::createFromFormat('M-Y', $nextAttempt);
      return ($nextAttemptDate > $lastAttemptDate) ? "Not Allow" : "Allow";
    }
    
    function getAllSubCenter($center_id) {
    
      global $conn;
      $subCenters = $conn->query("SELECT Sub_Center FROM `Center_SubCenter` WHERE Center = '$center_id'");
      $subCenters = mysqli_fetch_all($subCenters,MYSQLI_ASSOC);
      $subCenters = implode(',',array_column($subCenters,'Sub_Center'));
      return $subCenters;
    }
    
    function saveLog($response)
    {
      global $stepsLog;
      $stepsLog .= date(DATE_ATOM) . " response => " . json_encode($response) . "\n\n";
      $stepsLog .= " ============ End Of Script ================== \n\n";
      //$pdf_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/exam_system_log/';
      //$fh = fopen($pdf_dir . 'student_appear_' . date('y-m-d') . '.log' , 'a');
      //fwrite($fh,$stepsLog);
      //fclose($fh);
      echo json_encode($response);
    }