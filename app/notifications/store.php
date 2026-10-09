<?php
require '../../includes/db-config.php';
session_start();
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

if(isset($_POST['send_to']) && isset($_POST['heading']) && isset($_POST['content'])){
  try {
    $heading = mysqli_real_escape_string($conn, $_POST['heading']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $send_to = mysqli_real_escape_string($conn, $_POST['send_to']);
    $scheme = (isset($_POST['scheme']) && !empty($_POST['scheme'])) ? json_encode($_POST['scheme']) : null;
    $course = (isset($_POST['course']) && !empty($_POST['course'])) ? json_encode($_POST['course']) : null;

    $duration = null;
    if (isset($_POST['course']) && !empty($_POST['course'])) {
      // By this only selcted duration is came 
      $duration = [];
      foreach ($_REQUEST as $key => $value) {
        if (strpos($key,'duration') !== false) {
          $duration_courseId = (explode("_",$key))[1]; 
          $duration[$duration_courseId] = implode(',',$_REQUEST[$key]);
        }
      }
      // But if any course no duration is selected in that case directly select put all
      foreach( $_POST['course'] as $value) {
        if (!array_key_exists($value, $duration)) {
          $duration[$value] = "All";
        }
      }
      $duration = json_encode($duration);
    }
    $admissionSession = (isset($_POST['admissionSession']) && !empty($_POST['admissionSession'])) ? json_encode($_POST['admissionSession']) : null;  
    $student = (isset($_POST['student']) && !empty($_POST['student'])) ? json_encode($_POST['student']) : null;
    $center = (isset($_POST['center']) && !empty($_POST['center'])) ? json_encode($_POST['center']) : NULL;
    $notification_created_date = date('Y-m-d');
   
    if (isset($_FILES["file"]["name"]) && $_FILES["file"]["name"]!='') {
      $temp = explode(".", $_FILES["file"]["name"]);
      $filename = round(microtime(true)) .'-'.$send_to.'.'.end($temp);
      $tempname = $_FILES["file"]["tmp_name"];
      $folder = "../../uploads/notifications/".$filename; 
      if (is_uploaded_file($tempname)){ 
        move_uploaded_file($tempname,$folder);
        $filename = "/uploads/notifications/".$filename;
      } else {
        echo json_encode(['status'=>400, 'message'=>'Unable to save file!']);
        exit();
      }
    } else {
      $filename = "/assets/img/default-user.png";
    }

    $params = [];
    if ($send_to == 'student') {
      $params = [
        'scheme_id' => $scheme , 
        'admissionSession_id' => $admissionSession,
        'course_id' => $course ,
        'duration' =>  $duration ,
        'student_id' => $student ,
        'university_id' => $_SESSION['university_id']
      ];
    } else {
      $params = [
        'center' => $center ,
        'university_id' => $_SESSION['university_id']
      ];
    }
    //sendNotificationMail($params,$send_to);

    $insert_query = "INSERT INTO `Notifications_Generated`(`Heading`,`Content`, `Send_To`,`university_id`, `Noticefication_Created_on`, `scheme_id`, `admissionSession_id`, `course_id`, `duration`, `student_id`,`center_id`,`Attachment`) VALUES('$heading','$content','$send_to','". $_SESSION['university_id'] ."','$notification_created_date','$scheme','$admissionSession','$course','$duration','$student',NULL,'$filename')";
    $insert = $conn->query($insert_query);
    showResponse($insert,'added');
  } catch (Error $e) {
    showResponse(false,$e->getMessage());
  }
}

function sendNotificationMail($params, $send_to) {
  $user_list = ($send_to == 'student') ? getAllNoitifyStudentList($params) : getAllNoitifyCenterList($params);
  $url = "http://glocal.local/app/notifications/createQueue";
  
  try {
    $request = json_encode([
      'user_list' => $user_list,
      'user' => $send_to
    ]);

    $opt = [
      'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n",
        'content' => $request,
        'timeout' => 60
      ]
    ];

    $context = stream_context_create($opt);
    $response = @file_get_contents($url, false, $context);

    print_r($response);

    if ($response === false) {
      $error = error_get_last();
      throw new Exception("HTTP request failed: " . $error['message']);
    }


    return $response;
  } catch (Exception $e) {
    showResponse(false, $e->getMessage());
    die;
  }
}



function getAllNoitifyStudentList($filter) : array {

  global $conn; 
  $scheme_id = !empty($filter['scheme_id']) ? json_decode($filter['scheme_id'],true) : '';
  $admissionSession_id = !empty($filter['admissionSession_id']) ? json_decode($filter['admissionSession_id'],true) : ''; 
  $course_id = !empty($filter['course_id']) ? json_decode($filter['course_id'],true) : ''; 
  $duration_course = !empty($filter['duration']) ? json_decode($filter['duration'],true) : '';
  $student_id = !empty($filter['student_id']) ? json_decode($filter['student_id'],true) : '';
  $university_id = $filter['university_id'];

  if ($student_id != '') {
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
  $students = mysqli_fetch_all($students,MYSQLI_ASSOC);
  $student_list = array_column($students,'ID');
  return $student_list;
  
}

function spitDurationAndCategory($duration_catogry,$course_id) {
  $pos = strpos($duration_catogry, '(');
  $duration = substr($duration_catogry, 0, $pos);
  $category = substr($duration_catogry, $pos + 1, -1);
  return "(Students.Course_Category = '$category' AND Students.Duration = '$duration' AND Students.Sub_Course_ID = '$course_id')";
}

function getAllNoitifyCenterList($filter) : array {

  global $conn;
  $university_id = $filter['university_id'];
  $center_ids = !empty($filter['center']) ? json_decode($filter['center'],true) : "";
  if (!empty($center_ids)) {
    return $center_ids;
  }
  $centerQuery = "SELECT Users.ID as `ID` FROM `Users` LEFT JOIN Alloted_Center_To_Counsellor ON Alloted_Center_To_Counsellor.Code = Users.ID WHERE Alloted_Center_To_Counsellor.University_ID = '$university_id'";
  $center = $conn->query($centerQuery);
  $center = mysqli_fetch_all($center,MYSQLI_ASSOC);
  $center_list = array_column($center,'ID');
  return $center_list;
}

function showResponse($response, $message = "Something went wrong!") {
  if ($response) {
    echo json_encode(['status'=>200, 'message'=>"Notification $message successfully!"]);
  }  else {
    echo json_encode(['status'=>400, 'message'=> $message]);
  }
}
?>
