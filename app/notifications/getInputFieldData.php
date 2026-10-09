<?php

require '../../includes/db-config.php';
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$stepsLog = '';
$data_field = file_get_contents('php://input'); // by this we get raw data
$stepsLog .= date(DATE_ATOM) . " :: Data Came as input :  $data_field  \n\n"; 
$data_field = json_decode($data_field,true);
$duration_filter = [];

foreach($data_field['selected_values'] as $key => $value) {
    if (!str_contains($key,'duration')) {
        $variableName = $key."_filter";
        global $$variableName;
        $$variableName = (!empty($value)) ? $value : "";
    } else {
        $id = (explode('_',$key))[1];
        $duration_filter[$id] = $value;
    }
    
}

$result_list = [];
foreach ($data_field['input_data_field'] as $value) {
    $function_name = "get". ucfirst($value);
    $result_list[$value] = call_user_func($function_name);
}

// saveLog($result_list);
echo json_encode($result_list);
function getScheme() : string {

    global $conn;
    global $stepsLog;
    $schemeQuery = "SELECT ID , Name FROM Schemes WHERE University_ID = '".$_SESSION['university_id']."'";
    $scheme = $conn->query($schemeQuery);
    $stepsLog .= date(DATE_ATOM) ." :: scheme query => " . $schemeQuery . " \n\n";
    $scheme = mysqli_fetch_all($scheme,MYSQLI_ASSOC);
    $scheme_details = array_column($scheme,'Name','ID');
    $option = '<option value="">Choose Scheme</option>';
    $option .= getOptionTagData($scheme_details,$option);
    return $option;
}

function getAdmissionSession() : string {
    
    global $conn;
    global $stepsLog;
    global $scheme_filter;
    $searchQuery = '';
    $searchQuery .= !empty($scheme_filter) ?  " AND Scheme IN ('".$scheme_filter."')" : "";
    $admissionSessionQuery = "SELECT ID, Name FROM `Admission_Sessions` WHERE University_ID = '".$_SESSION['university_id']."' $searchQuery";
    $admissionSession = $conn->query($admissionSessionQuery);
    $stepsLog .= date(DATE_ATOM) ." :: admission session query =>  $admissionSessionQuery \n\n";
    $admissionSession = mysqli_fetch_all($admissionSession,MYSQLI_ASSOC);
    $admissionSession_details = array_column($admissionSession,'Name','ID');
    $option = '<option value="">Choose Admission</option>';
    $option  .= getOptionTagData($admissionSession_details,$option);
    return $option;
}

/**
 *Sub-courses are depand on university and scheme_id
 */
function getCourse() : string {

    global $conn;
    global $stepsLog;
    global $scheme_filter;
    $searchQuery = '';
    $searchQuery .= !empty($scheme_filter) ?  " AND Scheme IN ('".$scheme_filter."')" : "";
    $coursesQuery = "SELECT Sub_Courses.ID,CONCAT(Sub_Courses.Name,'(',Courses.Short_Name,')') as Name FROM `Sub_Courses` LEFT JOIN Courses on Courses.ID = Sub_Courses.Course_ID  WHERE Sub_Courses.University_ID = '".$_SESSION['university_id']."' $searchQuery";
    $courses = $conn->query($coursesQuery);
    $stepsLog .=  date(DATE_ATOM) . " :: courses query => $coursesQuery \n\n";
    $option = '<option value="">Choose Course</option>';
    $courses = mysqli_fetch_all($courses,MYSQLI_ASSOC);
    $courses_details = array_column($courses,'Name','ID');
    $option .= getOptionTagData($courses_details,$option);
    return $option;
}

/**
 * Duration depend on University and sub-courses
 * Sub-courses filter is applied
 */
function getDuration() : string {

    global $conn;
    global $course_filter;
    $option = '<option value="">Select Duration</option>';
    
        $searchQuery = (!empty($course_filter)) ? " AND ID IN (".$course_filter.")" : "";
        
        $maxDuration = $conn->query("SELECT MAX(CAST(TRIM('\"' FROM Min_Duration) AS int)) FROM Sub_Courses WHERE 1=1 $searchQuery");        
        $maxDuration = mysqli_fetch_column($maxDuration);
        $option .= createOptionTag($option,$maxDuration,1);
    
    return $option;
}

/**
 * Student data depend on following 
 * 1) Scheme 2) Admission Session 3) Sub-courses 4) Duration
 */
function getStudent() : string {

    global $conn;
    global $stepsLog;
    global $scheme_filter;
    global $admissionSession_filter;
    global $course_filter;
    global $duration_filter;
    $searchQuery = '';
    // $searchQuery .= !empty($scheme_filter) ?  " AND Admission_Sessions.Scheme IN (".$scheme_filter.")" : "";
    $searchQuery .= !empty($admissionSession_filter) ? " AND Students.Admission_Session_ID IN (".$admissionSession_filter.")" : "";
    $searchQuery .= !empty($course_filter) ? " AND Students.Sub_Course_ID IN (".$course_filter.")" : "";
    //$searchQuery .= !empty($duration_filter) ? " AND Students.Duration IN (".$duration_filter.")" : ""; 
    if (!empty($duration_filter)) {
        $query = [];
        foreach ($duration_filter as $course_id => $value) {
            if (!empty($value)) {
            
                $query[] = "(Students.Sub_Course_ID = '$course_id' AND Students.Duration IN (". $value ."))";
                
            } else {
                $query[] = "(Students.Sub_Course_ID = '$course_id')";
            }
        }
        $searchQuery .= " AND (".  implode(' OR ',$query) ." )";
    }
    $option = '<option value="">Select Duration</option>';
    $studentsQuery = "SELECT Students.ID , CONCAT(Students.First_Name,' ',Students.Middle_Name,' ',Students.Last_Name,' ','(',Students.Enrollment_No,')') AS `Name` FROM `Students` LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID = Admission_Sessions.ID WHERE Students.University_ID = '".$_SESSION['university_id']."' AND CONCAT(Students.First_Name,' ',Students.Middle_Name,' ',Students.Last_Name,' ','(',Students.Enrollment_No,')') IS NOT NULL $searchQuery";
    $students = $conn->query($studentsQuery);
    
    $stepsLog .= date(DATE_ATOM) . " :: student query => " . $studentsQuery . " \n\n";
    if($students->num_rows > 0) {
        $students = mysqli_fetch_all($students,MYSQLI_ASSOC);
        $students_details = array_column($students,'Name','ID');
        $option .= getOptionTagData($students_details,$option);
    } 
    return $option;
}

function spitDurationAndCategory($duration_catogry,$course_id) {
    $pos = strpos($duration_catogry, '(');
    $duration = substr($duration_catogry, 0, $pos);
    $category = substr($duration_catogry, $pos + 1, -1);
    return "(Students.Course_Category = '$category' AND Students.Duration = '$duration' AND Students.Sub_Course_ID = '$course_id')";
}

function getCenter() : string {
    
    global $conn;
    $option = '<option value="">Select Center</option>';
    $centers = $conn->query("SELECT Users.ID as `ID` , Users.Name as `Name` FROM `Users` LEFT JOIN Alloted_Center_To_Counsellor ON Alloted_Center_To_Counsellor.Code = Users.ID WHERE Alloted_Center_To_Counsellor.University_ID = '".$_SESSION['university_id']."'");
    if ($centers->num_rows > 0) {
        $centers = mysqli_fetch_all($centers,MYSQLI_ASSOC);
        $center_details = array_column($centers,'Name','ID');
        $option .= getOptionTagData($center_details,$option);
    }
    return $option;
}

function getOptionTagData($optionTagData,$option) {
    foreach ($optionTagData as $key=>$value) {
        $option .= '<option value="'.$key.'">'.$value.'</option>';
    }
    return $option;
}

function createOptionTag($option,$maxDuration,$currentDuration) {
    if($currentDuration <= $maxDuration) {
        $option .= '<option value="'.$currentDuration.'">'.$currentDuration.'</option>';
        return createOptionTag($option,$maxDuration,++$currentDuration);
    } else {
        return $option;
    }
}

function saveLog($response) {
    global $stepsLog;
    $stepsLog .= " ============ End Of Script ================== \n\n";
    $pdf_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/notification_log/';
    $fh = fopen($pdf_dir . 'student_appear_' . date('y-m-d') . '.log' , 'a');
    fwrite($fh,$stepsLog);
    fclose($fh);
    echo json_encode($response);
}

?>