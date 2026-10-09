<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
## Database configuration
include '../../includes/db-config.php';
require('../../extras/vendor/shuchkin/simplexlsxgen/src/SimpleXLSXGen.php');
session_start();

## Read value
$data = [];
$stepsLog = "";

if (isset($_REQUEST['notification_id']) && !empty($_REQUEST['notification_id'])) {
    try {
        $notification_id = mysqli_real_escape_string($conn,$_REQUEST['notification_id']);
        $notification_data = $conn->query("SELECT * FROM `Notifications_Generated` WHERE ID = '$notification_id'");
        $notification_data = mysqli_fetch_assoc($notification_data);

        $send_to = strtolower(mysqli_real_escape_string($conn,$notification_data['Send_To']));
        $user_list = ($send_to == 'student') ? getAllNoitifyStudentList($notification_data) : getAllNoitifyCenterList($notification_data);
        $raw_data =  ($send_to == 'student') ? createAllStudentData($user_list) : createAllCenterData($user_list);
        $data = [$raw_data[0]];
        foreach (array_slice($raw_data, 1) as $row) {
            $data[] = array_values($row);
        }        
        $stepsLog .= date(DATE_ATOM) . "final Data : " . json_encode($data) . "\n\n";
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment; filename=Notification_UserList_" . date('YMd_his') . ".xlsx");
        header("Cache-Control: max-age=0");
        $xlsx = SimpleXLSXGen::fromArray($data);
        $xlsx->saveAs('php://output');
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['status' => 400, 'message' => $e->getMessage()]);
    } finally {
        saveLog();
    }
} 

function createAllStudentData($user_list) : array {

    global $conn;
    global $data;
    global $stepsLog;
    $stepsLog .= date(DATE_ATOM) . " :: Inside function => createAllStudentData \n\n";

    try {
        $header = array('Enrollment_No','Scheme Id','Admission Session','Duration','Sub-Courses','Center','Vertical');
        $data[] = $header;
        ## Fetch records
        $studentRecordQuery = "SELECT Students.ID as `id`, Students.Enrollment_No , Students.University_ID , TRIM(CONCAT(Students.First_Name,' ',Students.Middle_Name,' ',Students.Last_Name)) as `name` , Sub_Courses.Name AS `sub_course` , Students.Sub_Course_ID as `sub_course_id` , Students.Duration as `current_duration` , Admission_Sessions.Name as `admission_session`, Schemes.Name as `scheme` , Users.Name as `center_name` , IF(Users.Vertical_type = 1 , 'EDTech','IITS') AS `vertical` FROM `Students` LEFT JOIN Sub_Courses ON Sub_Courses.ID = Students.Sub_Course_ID LEFT JOIN Admission_Sessions ON Admission_Sessions.ID = Students.Admission_Session_ID LEFT JOIN Schemes ON Schemes.ID = Admission_Sessions.Scheme_ID LEFT JOIN Users ON Users.ID = Students.Added_For WHERE Students.ID IN (".implode(',',$user_list).")";
        $studentRecord = $conn->query($studentRecordQuery);
        $stepsLog .= date(DATE_ATOM) . " student record query => $studentRecordQuery \n\n"; 
        while ($row = mysqli_fetch_assoc($studentRecord)) {
            $data[] = array(
                'Enrollment_No' => trim($row['Enrollment_No']),
                'scheme' => trim($row['scheme']),
                'admission_session' => trim($row['admission_session']),
                'current_duration' => trim($row['current_duration']),
                'sub_course' => trim($row['sub_course']),
                'center_name' => trim($row['center_name']),
                'vertical' => trim($row['vertical'])
            );
        }
        return $data;
    } catch (Error $e) {
        $stepsLog .= date(DATE_ATOM) . " ::  error throw => " . json_encode(['status' => 400 , 'message' => $e->getMessage()]) . "\n\n"; 
        saveLog();
    }
}

function createAllCenterData($user_list) : array {

    global $conn;
    global $data;
    global $stepsLog;
    $stepsLog .= date(DATE_ATOM) . " :: Inside function => createAllCenterData \n\n";

    try {
        $header = array('Center Name','Code','Vertical');
        $data[] = $header;
        ## Fetch records
        $CenterRecordQuery = "SELECT ID , Name , Code , IF(Vertical_type = 1 , 'EDTech','IITS') as `vertical` FROM `Users` WHERE role IN ('Center','Sub-Center') AND ID IN (".implode(',',$user_list).")";
        $CenterRecord = $conn->query($CenterRecordQuery);
        $stepsLog .= date(DATE_ATOM) . " :: center record => $CenterRecordQuery . \n\n";
        while ($row = mysqli_fetch_assoc($CenterRecord)) {
            $data[] = array(
                'name' => $row['Name'],
                'code' => $row['Code'],
                'vertical' => $row['vertical'],  
            );
        }
        return $data;
    } catch (Error $e) {
        $stepsLog .= date(DATE_ATOM) . " ::  error throw => " . json_encode(['status' => 400 , 'message' => $e->getMessage()]) . "\n\n"; 
        saveLog();
    }
    
}

function getAllNoitifyStudentList($filter) : array {

    global $conn;
    global $stepsLog;
    $stepsLog .= date(DATE_ATOM) . " :: Inside function => getAllNoitifyStudentList \n\n"; 
    try {
        $scheme_id = !empty($filter['scheme_id']) ? json_decode($filter['scheme_id'],true) : '';
        $admissionSession_id = !empty($filter['admissionSession_id']) ? json_decode($filter['admissionSession_id'],true) : ''; 
        $course_id = !empty($filter['course_id']) ? json_decode($filter['course_id'],true) : ''; 
        $duration_course = !empty($filter['duration']) ? json_decode($filter['duration'],true) : '';
        $student_id = !empty($filter['student_id']) ? json_decode($filter['student_id'],true) : '';
        $university_id = $filter['university_id'];

        if ($student_id != '') {
            $stepsLog .= date(DATE_ATOM) . " :: Return data => ". json_encode($student_id) ." \n\n";
            return $student_id;
        }

        $searchQuery = '';
        $searchQuery .= !empty($scheme_id) ?  " AND Admission_Sessions.Scheme_ID IN (". implode(',',$scheme_id) .")" : "";
        $searchQuery .= !empty($admissionSession_id) ? " AND Students.Admission_Session_ID IN (". implode(',',$admissionSession_id) .")" : "";
        $searchQuery .= !empty($course_id) ? " AND Students.Sub_Course_ID IN (". implode(',',$course_id) .")" : "";
        if (!empty($duration_course)) {
            $query = [];
            foreach ($duration_course as $course_id => $value) {
                if ($value != 'All') {
                    if($university_id == '48') {
                        $duration = explode(",",$value);
                        $query_array = array_map(function($duration_catogry) use ($course_id) {
                            return spitDurationAndCategory($duration_catogry,$course_id);
                        },$duration);
                        $query = array_merge($query,$query_array);
                    } else {
                        $query[] = "(Students.Sub_Course_ID = '$course_id' AND Students.Duration IN (". $value ."))";
                    }
                } else {
                    $query[] = "(Students.Sub_Course_ID = '$course_id')";
                }
            }
            $searchQuery .= " AND (".  implode(' OR ',$query) ." )";
        }
        $studentQuery = "SELECT Students.ID as `ID` FROM `Students` LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID = Admission_Sessions.ID WHERE Students.University_ID = '$university_id' AND CONCAT(Students.First_Name,' ',Students.Middle_Name,' ',Students.Last_Name,' ','(',Students.Enrollment_No,')') IS NOT NULL $searchQuery";
        $students = $conn->query($studentQuery);
        $stepsLog .= date(DATE_ATOM) . " :: student query => $studentQuery \n\n";
        $students = mysqli_fetch_all($students,MYSQLI_ASSOC);
        $student_list = array_column($students,'ID');
        return $student_list;
    } catch (Error $e) {
        $stepsLog .= date(DATE_ATOM) . " ::  error throw => " . json_encode(['status' => 400 , 'message' => $e->getMessage()]) . "\n\n"; 
        saveLog();
    }
    
}

function spitDurationAndCategory($duration_catogry,$course_id) {
    $pos = strpos($duration_catogry, '(');
    $duration = substr($duration_catogry, 0, $pos);
    $category = substr($duration_catogry, $pos + 1, -1);
    return "(Students.Course_Category = '$category' AND Students.Duration = '$duration' AND Students.Sub_Course_ID = '$course_id')";
}

function getAllNoitifyCenterList($filter) : array {

    global $conn;
    global $stepsLog;
    $stepsLog .= date(DATE_ATOM). " :: Inside function => getAllNoitifyCenterList \n\n";
    $university_id = $filter['university_id'];
    $center_id = (!empty($filter['center_id'])) ? json_decode($filter['center_id'],true) : "" ;
    $searchQuery = (!empty($center_id)) ? " AND Users.ID IN (". implode(',',$center_id) .")" : ""; 
    $centerQuery = "SELECT Users.ID as `ID` FROM `Users` LEFT JOIN Alloted_Center_To_Counsellor ON Alloted_Center_To_Counsellor.Code = Users.ID WHERE Alloted_Center_To_Counsellor.University_ID = '$university_id' $searchQuery";
    $center = $conn->query($centerQuery);
    $stepsLog .= date(DATE_ATOM) . " :: center query => $centerQuery \n\n";
    $center = mysqli_fetch_all($center,MYSQLI_ASSOC);
    $center_list = array_column($center,'ID');
    return $center_list;
}

function saveLog() {
    global $stepsLog;
    $stepsLog .= " ============ End Of Script ================== \n\n";
    //$pdf_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/notification_log/';
    //$fh = fopen($pdf_dir . 'exportData_' . date('y-m-d') . '.log' , 'a');
    //fwrite($fh,$stepsLog);
    //fclose($fh);
    exit;
}
?>