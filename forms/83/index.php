<?php
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;

if (isset($_GET['student_id'])) {
  require '../../includes/db-config.php';
  session_start();

  if ($_SESSION['university_id'] != 83) {
    header('Location: /');
  }

  $id = mysqli_real_escape_string($conn, $_GET['student_id']);
  $id = base64_decode($id);
  $id = intval(str_replace('W1Ebt1IhGN3ZOLplom9I', '', $id));
  $student = $conn->query("SELECT Students.*, Courses.Name as Course,Courses.Short_Name as Course_Short_Name, Sub_Courses.Name as Sub_Course, Admission_Sessions.Name as Session, Admission_Types.Name as Type, Universities.Vertical, TIMESTAMPDIFF(YEAR, DOB, CURDATE()) AS Age FROM Students LEFT JOIN Universities ON Students.University_ID = Universities.ID LEFT JOIN Courses ON Students.Course_ID = Courses.ID LEFT JOIN Sub_Courses ON Students.Sub_Course_ID = Sub_Courses.ID LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID = Admission_Sessions.ID LEFT JOIN Admission_Types ON Students.Admission_Type_ID = Admission_Types.ID WHERE Students.ID = $id");
  $student = mysqli_fetch_assoc($student);
  $address = json_decode($student['Address'], true);


  require_once('../../extras/vendor/setasign/fpdf/fpdf.php');
  require_once('../../extras/vendor/setasign/fpdi/src/autoload.php');

  $pdf = new Fpdi();

  $pdf->SetTitle('Application Form');
  $pageCount = $pdf->setSourceFile('SUBHARTI APPLICATION FORM.pdf');
  $pdf->AddFont('Hondo', '', 'hondo.php');
  $pdf->SetFont('Hondo', '', 10);

  // Tick Image
  $check = '../../assets/img/forms/checked.png';

  // Extensions
  $file_extensions = array('.png', '.jpg', '.jpeg');

  //this folder will have there images.
  $path = "photos/";

  // Photo
  $student_photo = "";
  $photo = $conn->query("SELECT Location FROM Student_Documents WHERE Student_ID = $id AND Type = 'Photo'");
  if ($photo->num_rows > 0) {
    $photo = mysqli_fetch_assoc($photo);
    $photo = "../.." . $photo['Location'];
    $student_photo = base64_encode(file_get_contents($photo));
    $i = 0;
    $end = 3;
    while ($i < $end) {
      $data1 = base64_decode($student_photo);
      $filename1 = $id . "_Photo" . $file_extensions[$i]; //$file_extensions loops through the file extensions
      file_put_contents($path . $filename1, $data1); //we save our new images to the path above
      $i++;
    }
  } else {
    $photo = "";
  }

  // Signature
  $student_signature = "";
  $signature = $conn->query("SELECT Location FROM Student_Documents WHERE Student_ID = $id AND Type = 'Student Signature'");
  if ($signature->num_rows > 0) {
    $signature = mysqli_fetch_assoc($signature);
    $signature = "../.." . $signature['Location'];
    $student_signature = base64_encode(file_get_contents($signature));
    $i = 0;
    $end = 3;
    while ($i < $end) {
      $data2 = base64_decode($student_signature);
      $filename2 = $id . "_Signature" . $file_extensions[$i]; //$file_extensions loops through the file extensions
      file_put_contents($path . $filename2, $data2); //we save our new images to the path above
      $i++;
    }
  } else {
    $signature = "";
  }

  // Page 1
  $pageId = $pdf->importPage(1, PdfReader\PageBoundaries::MEDIA_BOX);
  $pdf->addPage();
  $pdf->useImportedPage($pageId, 0, 0, 210);

  // Photo
  if (filetype($photo) === 'file' && file_exists($photo)) {
    try {
      $filename = $id . "_Photo" . $file_extensions[0];
      $image = $path . $filename;
      $pdf->Image($image, 157, 35.2, 33, 37.5);
      $photo = $image;
    } catch (Exception $e) {
      try {
        $filename = $id . "_Photo" . $file_extensions[1];
        $image = $path . $filename;
        $pdf->Image($image, 157, 35.2, 33, 37.5);
        $photo = $image;
      } catch (Exception $e) {
        try {
          $filename = $id . "_Photo" . $file_extensions[2];
          $image = $path . $filename;
          $pdf->Image($image, 157, 35.2, 33, 37.5);
          $photo = $image;
        } catch (Exception $e) {
          echo 'Message: ' . $e->getMessage();
        }
      }
    }
  }
  if (filetype($signature) === 'file' && file_exists($signature)) {
    try {
      $filename = $id . "_Signature" . $file_extensions[0];
      $image = $path . $filename;
      $pdf->Image($image, 157, 72.5, 32, 9);
      $student_signature = $image;
    } catch (Exception $e) {
      try {
        $filename = $id . "_Signature" . $file_extensions[1];
        $image = $path . $filename;
        $pdf->Image($image, 157, 73.5, 32, 7);
        $student_signature = $image;
      } catch (Exception $e) {
        try {
          $filename = $id . "_Signature" . $file_extensions[2];
          $image = $path . $filename;
          $pdf->Image($image, 157, 73.5, 32, 7);
          $student_signature = $image;
        } catch (Exception $e) {
          echo 'Message: ' . $e->getMessage();
        }
      }
    }
  }
  
//   echo('<pre>');print_r($student);
  $session = $student['Session']; // jan-25
  list($month, $year) = explode('-', $session);
    $pdf->SetXY(74, 100);
  $pdf->Write(1, strtoupper($student['Course']." (". $student['Sub_Course'].")"));
   $pdf->SetXY(50.5, 15.5);
//   print_r($student);die;
  $pdf->Write(1, strtoupper($student['abc_id']));
   $pdf->SetXY(157.5, 15.5);
//   print_r($student);die;
  $pdf->Write(1, strtoupper($student['deb_id']));
   $pdf->SetXY(50.5, 26.5);
//   print_r($student);die;
  $pdf->Write(1, strtoupper($student['Unique_ID']));
  $pdf->SetXY(159.8, 26.7);
  $pdf->SetFont('Arial', 'B', 11); // B = Bold
//   $pdf->Write(1, '20'.$year);
 $pdf->Write(1,strtoupper($month).'-'. $year);
  $pdf->SetFont('Hondo', '', 10);
  
  // Form No.
//   $pdf->SetXY(75.5, 88);
    $y = 88;
    $x = 75.5;
    
    $enrollment = strtoupper($student['Enrollment_No']);
    
    for ($i = 0; $i < strlen($enrollment); $i++) {
        $pdf->SetXY($x, $y);
        $pdf->Write(1, $enrollment[$i]);
        $x += 6.1; // spacing
    }
  
//   $pdf->Write(1, implode("     ", str_split($student['Enrollment_No'])));
  
  $presentAddress = strtoupper($address['present_address'].' '.$address['present_district'].' '.$address['present_state']);
  
  // Student Name
  $student_name = $student['First_Name'] . ' ' . $student['Middle_Name'] . ' ' . $student['Last_Name'];
  $pdf->SetXY(16, 117);
  $pdf->Write(1, $student_name);
  
  $pdf->SetXY(16, 147);
  $pdf->Write(1, $student['Mother_Name']);
  
  $pdf->SetXY(16, 132);
  $pdf->Write(1, $student['Father_Name']);
  
  // Present Address with multi-line handling
  $x = 14;
  $y = 177.8;
  $maxWidth = 170;
  $lineHeight = 5;
  $pdf->SetFont('Arial', '', 10);
  $words = explode(' ', trim($presentAddress));
  $line1 = '';
  $line2 = '';
  
  foreach ($words as $word) {
    $testLine = $line1 . ($line1 ? ' ' : '') . $word;
    if ($pdf->GetStringWidth($testLine) <= $maxWidth) {
      $line1 = $testLine;
    } else {
      $line2 .= ($line2 ? ' ' : '') . $word;
    }
  }
  $pdf->SetXY($x+2, $y-2);
  $pdf->Write(5, $line1);
  
  if (!empty($line2)) {
    $pdf->SetXY($x-28, $y + $lineHeight);
    $pdf->Write(5, $line2);
  }
  
  // Course and Duration
//   $pdf->SetFont('Arial', '', 8);
//   $courseString = $student['Sub_Course'];
//   preg_match('/^(.*?)\s*\((.*?)\)$/', $courseString, $matches);
//   $courseName = trim($matches[1] ?? '');
//   $duration   = trim($matches[2] ?? '');
  
//   $pdf->SetXY(31.5, 65);
//   $pdf->Write(1, strtoupper($student['Sub_Course']));
//   $pdf->SetXY(131.5, 71.5);
//   $pdf->Write(1, strtoupper($student['Duration']));
  
//   $pdf->SetXY(160.5, 65);
//   $pdf->Write(1, strtoupper($duration));
  
  
  $pdf->SetXY(156, 190.8);
  $pdf->Write(1, implode("     ", str_split($address['present_pincode'])));
  
  // Contact Number with spacing
  $contact = $student['Contact']; // 8156902417
//   $spacedContact = implode('   ', str_split($contact));
  $pdf->SetXY(85, 205.3);
  $pdf->Write(1, $contact);
  
  $pdf->SetXY(129, 205.3);
  $pdf->Write(1, $student['Email']);
  
  if ($student['Gender'] == 'Male') {
    $pdf->Image($check, 53.2, 162, 3, 3);
  } else if ($student['Gender'] == 'Female') {
    $pdf->Image($check, 67.6, 162, 3, 3);
  }
  
  
//   $pdf->Image($check, 13, 204.5, 3, 3);
  
  // DOB
  $dob = date("d/m/Y", strtotime($student['DOB']));
  $dobChars = explode('/',$dob);
  $y = 163.3;
  $x = 145;
  foreach ($dobChars as $index => $char) {
    $pdf->SetXY($x, $y);
    $pdf->Write(1, $char);
    $x +=15;
  }
  
  
    $pageId = $pdf->importPage(2, PdfReader\PageBoundaries::MEDIA_BOX);
  $pdf->addPage();
  $pdf->useImportedPage($pageId, 0, 0, 210);
  
  
  $pdf->SetXY(15, 46.5);
  $pdf->Write(1, $student['Employement_Status']);
  
  $pdf->SetXY(15, 33);
  $pdf->Write(1, $student['Nationality']);
  
  // Gender checkbox
  
  
//   Category checkbox

  if ($student['Category'] == 'SC') {
    $pdf->Image($check, 142.3, 42.8, 3, 3);
  } else if ($student['Category'] == 'ST') {
    $pdf->Image($check, 153.8, 42.8, 3, 3);
  } else if ($student['Category'] == 'OBC') {
    $pdf->Image($check, 127.3, 42.8, 3, 3);
  } else if ($student['Category'] == 'General') {
    $pdf->Image($check, 109, 42.8, 3, 3);
  }else{
      $pdf->Image($check, 172, 42.8, 3, 3);
  }
  // Academics section
$academis = array('High School', 'Intermediate', 'Under Graduation', 'Post Graduation', 'Other');
$pdf->SetFont('Arial', '', 6);

// Manual Y positions for each academic level
$yPositions = array(
    'High School' => 72,
 'Intermediate' => 84.7,
 'Under Graduation' => 95.7,
    'Post Graduation' => 107.7,
 'Other' => 118.7
);

foreach ($academis as $academic) {

    $x = 13;
    $y = $yPositions[$academic];

    // Type mapping
    $type = ($academic === 'Under Graduation') ? 'UG' :
            (($academic === 'Post Graduation') ? 'PG' : $academic);

    $query = "
        SELECT 
            `Board/Institute` AS board,
            Year,
            Subject,
            Total_Marks
        FROM Student_Academics 
        WHERE Student_ID = $id AND Type = '$type'
    ";

    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {

        $row = $result->fetch_assoc();
// echo('<pre>');print_r($row);
        // Academic name
        $pdf->SetXY($x, $y);
        $pdf->Write(1, substr($academic, 0, 15));
        $x += 24;

        // Board / Institute
        $pdf->SetXY($x, $y);
        $pdf->Write(1, strlen($row['Subject']) > 44 ? substr($row['Subject'], 0, 43).'...' : $row['Subject']);
        
        $x += 48.5;

        // Year
        $pdf->SetXY($x, $y);
        $pdf->Write(1, $row['Year']);
        $x += 41;

        // Subject
        $pdf->SetFont('Arial', '', 5);
        $pdf->SetXY($x, $y);
        $pdf->Write(1, strlen($row['board']) > 20 ? substr($row['board'], 0, 18).'...' : $row['board']);
        $pdf->SetFont('Arial', '', 6);
        $x += 55;

        // Marks
        $pdf->SetXY($x, $y);
        $pdf->Write(1, substr($row['Total_Marks'], 0, 4));
    }
}
  
  // Page 2

  
  // Signature on page 2
  
  
//   $form_completion_date = date("d-m-Y", strtotime($student['form_completion_date']?$student['form_completion_date']:$student['Created_At']));
//   $pdf->SetFont('Arial', '', 9);
//   $pdf->SetXY(18, 265.7);
//   $pdf->Write(1, $form_completion_date);
  
  
       $clean_id = preg_replace('/[^A-Za-z0-9_\-]/', '', trim($student['Unique_ID']));

       if (empty($clean_id)) {
          $clean_id = 'unknown';
       }

      $pdf->Output('I', 'svsu_app_' . $clean_id . '.pdf');
}