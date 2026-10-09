<?php
ini_set('display_errors', 1);
if (isset($_FILES['file'])) {
  require '../../includes/db-config.php';
  session_start();
  $delimiter = ",";
  $filename = "Date-Sheet Status".time().".csv";

  // Create a file pointer 
  $f = fopen('php://memory', 'w');

  $fields = array('Course', 'Sub-Course', 'Exam Session', 'Subject Code', 'Exam Date (dd-mm-yyyy)', 'Start Time', 'End Time', 'Semester', 'Remark');
  fputcsv($f, $fields, $delimiter);
	
  $allowed_extensions = array(
    'text/x-comma-separated-values',
    'text/comma-separated-values',
    'application/octet-stream',
    'application/vnd.ms-excel',
    'application/x-csv',
    'text/x-csv',
    'text/csv',
    'application/csv',
    'application/excel',
    'application/vnd.msexcel',
    'text/plain'
  );

  if (!empty($_FILES['file']['name']) && in_array($_FILES['file']['type'], $allowed_extensions)) {
    // Open uploaded CSV file with read-only mode
    $csvFile = fopen($_FILES['file']['tmp_name'], 'r');

    // Skip the first line
    fgetcsv($csvFile);

    while (($row = fgetcsv($csvFile, 10000, ",")) !== FALSE) {

      $course = $row[0];
      $sub_course = $row[1];
      $exam_session = $row[2];
      $paper_code = $row[3];
      $exam_date = $row[4];
      $start_time = $row[5];
      $end_time = $row[6];
      $duration = strtolower(trim($row[7]));

      $course = $conn->query("SELECT ID FROM Courses WHERE University_ID = " . $_SESSION['university_id'] . " AND (Name = '$course' OR Short_Name = '$course')");

      if ($course->num_rows == 0) {
        $export_data[] = array_merge($row, ['Course not found!']);
        continue;
      }
     
      $course_ids = array();
      while ($course_id = $course->fetch_assoc()) {
        $course_ids[] = $course_id['ID'];
      }
      $sub_course = $conn->query("SELECT ID, Course_ID FROM Sub_Courses WHERE University_ID = " . $_SESSION['university_id'] . " AND Name  = '$sub_course' AND Course_ID IN (" . implode(',', $course_ids) . ")");

      if ($sub_course->num_rows == 0) {
        $export_data[] = array_merge($row, ['Sub-Course not found!']);
        continue;
      }
      $sub_course = $sub_course->fetch_assoc();
      $course_id = $sub_course['Course_ID'];
      $sub_course_id = $sub_course['ID'];

      $exam_session = $conn->query("SELECT ID FROM Exam_Sessions WHERE Name = '$exam_session' AND University_ID = " . $_SESSION['university_id'] . "");
      if ($exam_session->num_rows == 0) {
        $row = array_merge($row, ['Exam Session not found!']);
        fputcsv($f, $row, $delimiter);
        continue;
      }

      $exam_session = $exam_session->fetch_assoc();
      $exam_session = $exam_session['ID'];
      $paper_code = $conn->query("SELECT ID FROM Syllabi WHERE Course_ID =  $course_id  AND Sub_Course_ID =  $sub_course_id AND Code = '$paper_code'  AND University_ID = " . $_SESSION['university_id'] . " AND Semester = '$duration'");
      if ($paper_code->num_rows == 0) {
        $row = array_merge($row, ['Paper Code not found!']);
        fputcsv($f, $row, $delimiter);
        continue;
      }

      $paper_code = $paper_code->fetch_assoc();
      // $paper_code = $paper_code['ID'];
      $syllabus_id = $paper_code['ID'];
      $exam_date = date("Y-m-d", strtotime($exam_date));
      $check = $conn->query("SELECT ID FROM Date_Sheets WHERE Exam_Session_ID = $exam_session AND Syllabus_ID = $syllabus_id AND University_ID = " . $_SESSION['university_id']);
      if ($check->num_rows == 0) {
        $add = $conn->query("INSERT INTO Date_Sheets (`Exam_Session_ID`, `Syllabus_ID`, `University_ID`, `Exam_Date`, `Start_Time`, `End_Time`,`Course_ID`, `Sub_Course_ID`) VALUES ($exam_session, $syllabus_id, " . $_SESSION['university_id'] . ", '$exam_date', '$start_time', '$end_time',$course_id,$sub_course_id)");
        if ($add) {
          $row = array_merge($row, ['Date Sheet added successfully!']);
        } else {
          $row = array_merge($row, ['Something went wrong!']);
        }
      } else {

        $row = array_merge($row, ['Date Sheet already exists!']);
      }

      fputcsv($f, $row, $delimiter);
    }

    // Close opened CSV file
    fclose($csvFile);
  } else {
    $row['Inavlid File'];
    fputcsv($f, $row, $delimiter);
  }

  fseek($f, 0);
  // Set headers to download file rather than displayed 
  header('Content-Type: text/csv');
  header('Content-Disposition: attachment; filename="' . $filename . '";');

  //output all remaining data on a file pointer 
  fpassthru($f);
}