<?php
// ini_set('display_errors', 1);
require '../../includes/db-config.php';

session_start();
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// echo "<pre>"; print_r($_POST);DIE;
$allowedExts = array("pdf");
$extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
$course_id = intval($_POST['course_id']);
$sub_course_id = intval($_POST['sub_course_id']);
$duration = intval($_POST['duration']);
$subject_id = intval($_POST['subject_id']);
$file_path = "";
$file_type = $extension;
$created_by = 1;
$created_at = date("Y-m-d:H:i:s");
$title = $_POST['ebook_name'];


$unit_id = isset($_POST['unit_id'])&&!empty($_POST['unit_id'])?$_POST['unit_id']:0;
$module_id = isset($_POST['module_id'])&&!empty($_POST['module_id'])?$_POST['module_id']:0;

if (isset($_FILES["file"]["name"]) && $_FILES["file"]["name"] != '') {
  if (in_array($extension, $allowedExts) && ($_FILES["file"]["error"] == 0)) {
    $temp = explode(".", $_FILES["file"]["name"]);
    $filename = $temp[0] . '_' . time() . '.' . end($temp);
    $path = "../../uploads/e-books/" . $filename;
    $file_path = "uploads/e-books/" . $filename;
    move_uploaded_file(
      $_FILES["file"]["tmp_name"],
      $path
    );
  } else {
    echo json_encode(['status' => 400, 'message' => 'Invalid file type!! ']);
    exit();
  }
} else {
  echo json_encode(['status' => 403, 'message' => 'File is mandatory.']);
  exit();
}
// echo "INSERT INTO `e_books`(chapter_id,unit_id,`course_id`,`sub_course_id`,`semester_id`, `subject_id`, `file_path`, `file_type`,`title`, `created_by`, `created_at`,`University_ID`) VALUES ('$unit_id', '$module_id','" . $course_id . "','" . $sub_course_id . "','" . $duration . "', '" . $subject_id . "', '" . $file_path . "', '" . $file_type . "','" . $title . "', '" . $created_by . "', '" . $created_at . "', '" . $_SESSION['university_id'] . "' )";die;
$add = $conn->query("INSERT INTO `e_books`(chapter_id,unit_id,`course_id`,`sub_course_id`,`semester_id`, `subject_id`, `file_path`, `file_type`,`title`, `created_by`, `created_at`,`University_ID`) VALUES ($unit_id, $module_id,'" . $course_id . "','" . $sub_course_id . "','" . $duration . "', '" . $subject_id . "', '" . $file_path . "', '" . $file_type . "','" . $title . "', '" . $created_by . "', '" . $created_at . "', '" . $_SESSION['university_id'] . "' ) ");

if ($add) {
  echo json_encode(['status' => 200, 'message' => "E-book uploaded succefully!!"]);
} else {
  echo json_encode(['status' => 400, 'message' => 'Something went to wrong!!']);
}


