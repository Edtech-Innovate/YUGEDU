<?php

if (isset($_FILES['file'])) {
  require '../../includes/db-config.php';
  require('../../extras/vendor/shuchkin/simplexlsxgen/src/SimpleXLSXGen.php');
  require('../../extras/vendor/nuovo/spreadsheet-reader/SpreadsheetReader.php');
  ini_set('max_execution_time', '300');
  session_start();
  $export_data = array();
  $header = array('Scheme','Course', 'Sub-Course', 'Semester', 'Subject Code', 'Subject Name', 'Type (Theory/Practical)', 'Credit', 'Minimum Marks', 'Maximum Marks','Paper Type', 'Remark');
  $export_data[] = $header;
  $mimes = [
    'application/vnd.ms-excel',
    'text/csv',
    'text/xls',
    'text/xlsx',
    'application/vnd.oasis.opendocument.spreadsheet',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
  ];

  if (in_array($_FILES["file"]["type"], $mimes)) {
    $uploadFilePath = basename($_FILES['file']['name']);
    move_uploaded_file($_FILES['file']['tmp_name'], $uploadFilePath);
    $reader = new SpreadsheetReader($uploadFilePath);
    $totalSheet = count($reader->sheets());
    for ($i = 0; $i < $totalSheet; $i++) {
      $reader->ChangeSheet($i);
      foreach ($reader as $row) {
        // Data
        $remark = [];
        $scheme = mysqli_real_escape_string($conn, trim($row[0]));
        $course = trim(mysqli_real_escape_string($conn, trim($row[1])));
        $sub_course = trim(mysqli_real_escape_string($conn, trim($row[2])));
        $semester = intval($row[3]);
        $subject_code = trim(mysqli_real_escape_string($conn, $row[4]));
        $subject_name = trim(mysqli_real_escape_string($conn, $row[5]));
        $paper_type = trim(mysqli_real_escape_string($conn, $row[6]));
        $credit = intval($row[7]);
        $min_marks = intval($row[8]);
        $max_marks = intval($row[9]);
        $exam_type = trim($row[10]);
        

        if ($min_marks > $max_marks) {
          $export_data[] = array_merge($row, ['Min Marks cannot be greater than Max Marks.']);
          continue;
        }

        $scheme = $conn->query("SELECT ID FROM Schemes WHERE University_ID = " . $_SESSION['university_id'] . " AND Name = '$scheme'");
        if ($scheme->num_rows == 0) {
          $export_data[] = array_merge($row, ['Scheme not found!']);
          continue;
        }

        // $scheme = $scheme->fetch_assoc();
        // $scheme_id = $scheme['ID'];
       
        $course = $conn->query("SELECT ID FROM Courses WHERE University_ID = " . $_SESSION['university_id'] . " AND (Name = '$course' OR Short_Name = '$course')");
        if ($course->num_rows == 0) {
          $export_data[] = array_merge($row, ['Course not found!']);
          continue;
        }

        $course_ids = array();
        while ($course_id = $course->fetch_assoc()) {
          $course_ids[] = $course_id['ID'];
        }
        $sub_course = $conn->query("SELECT ID, Course_ID FROM Sub_Courses WHERE University_ID = " . $_SESSION['university_id'] . " AND Name = '$sub_course' AND Course_ID IN (" . implode(',', $course_ids) . ")");
        if ($sub_course->num_rows == 0) {
            // print_r("SELECT ID, Course_ID FROM Sub_Courses WHERE University_ID = " . $_SESSION['university_id'] . " AND Name = 'Civil Engineering' AND Course_ID IN (" . implode(',', $course_ids) . ")");die;
          $export_data[] = array_merge($row, ['Sub-Course not found!']);
          continue;
        }

        $sub_course = $sub_course->fetch_assoc();
        $course_id = $sub_course['Course_ID'];
        $sub_course_id = $sub_course['ID'];
        
        if (empty($subject_code) ||   empty($subject_name) ||  empty($paper_type) ||  empty($credit)) {
            $export_data[] = array_merge($row, ['error' => 'Please enter all required column values.']);
            continue;
        }

        $check = $conn->query("SELECT ID FROM Syllabi WHERE University_ID = " . $_SESSION['university_id'] . " AND Course_ID = $course_id AND Sub_Course_ID = $sub_course_id  AND Code = '" . $subject_code . "'");
        if ($check->num_rows > 0) {
          $export_data[] = array_merge($row, ['Subject Code already exists!']);
          continue;
        }
        // echo "<pre>";
        // echo "INSERT INTO `Syllabi`(`University_ID`, `Course_ID`, `Sub_Course_ID`, `Semester`, `Code`, `Name`, `Paper_Type`, `Credit`, `Min_Marks`, `Max_Marks`) VALUES (" . $_SESSION['university_id'] . ", " . $course_id . ", " . $sub_course_id . ", $semester, '" . $subject_code . "', '" . $subject_name . "', '" . $paper_type . "', " . $credit . ", " . $min_marks . ", " . $max_marks . ")";
        $add = $conn->query("INSERT INTO `Syllabi`(`University_ID`, `Course_ID`, `Sub_Course_ID`, `Semester`, `Code`, `Name`, `Paper_Type`, `Credit`, `Min_Marks`, `Max_Marks`,`exam_type`) VALUES (" . $_SESSION['university_id'] . ", " . $course_id . ", " . $sub_course_id . ", $semester, '" . $subject_code . "', '" . $subject_name . "', '" . $paper_type . "', " . $credit . ", " . $min_marks . ", " . $max_marks . ",'".$exam_type."')");
        if ($add) {
          $export_data[] = array_merge($row, ['Subject added successfully!']);
        } else {
          $export_data[] = array_merge($row, ['Something went wrong!']);
        }
      }
    } 
    
    unlink($uploadFilePath);
    $xlsx = SimpleXLSXGen::fromArray($export_data)->downloadAs('Subjects Status ' . date('h m s') . '.xlsx');
  }
}
?>