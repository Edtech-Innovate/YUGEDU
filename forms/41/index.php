<?php
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;

if (isset($_GET['student_id'])) {
  require '../../includes/db-config.php';
  session_start();

  if ($_SESSION['university_id'] != 41) {
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
  $pageCount = $pdf->setSourceFile('MDU_Admission Form.pdf');
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
      $pdf->Image($image, 174, 46.2, 24, 28.7);
      $photo = $image;
    } catch (Exception $e) {
      try {
        $filename = $id . "_Photo" . $file_extensions[1];
        $image = $path . $filename;
        $pdf->Image($image, 174, 46.2, 24, 28.7);
        $photo = $image;
      } catch (Exception $e) {
        try {
          $filename = $id . "_Photo" . $file_extensions[2];
          $image = $path . $filename;
          $pdf->Image($image, 174, 46.2, 24, 28.7);
          $photo = $image;
        } catch (Exception $e) {
          echo 'Message: ' . $e->getMessage();
        }
      }
    }
  }
  $session = $student['Session']; // jan-25

list($month, $year) = explode('-', $session);

  
  $pdf->SetXY(137.8, 48.7);
  $pdf->SetFont('Arial', 'B', 11); // B = Bold

  $pdf->Write(1,strtoupper($month).'-'. '20'.$year);
  $pdf->SetFont('Hondo', '', 10);
  // Form No.
   $pdf->SetXY(37.5, 71.8);
   $pdf->Write(1, $student['Unique_ID']);
    $presentAddress = strtoupper($address['present_address']);
  // Course
  
  $student_name = $student['First_Name'] . ' ' . $student['Middle_Name'] . ' ' . $student['Last_Name'];
   $pdf->SetXY(69.5, 79.2);
   $pdf->Write(1, $student_name);
    $pdf->SetXY(36, 97.8);
   $pdf->Write(1, $student['Mother_Name']);
    $pdf->SetXY(36, 90.8);
  $pdf->Write(1, $student['Father_Name']);
//   $pdf->SetXY(42, 104.8);
//   $pdf->Write(1, $presentAddress);
// $presentAddress=$presentAddress.' '.$presentAddress;
$x = 40;
$y = 104.8;
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
$pdf->SetFont('Arial', '', 8);
$courseString = $student['Sub_Course'];

preg_match('/^(.*?)\s*\((.*?)\)$/', $courseString, $matches);

$courseName = trim($matches[1] ?? '');
$duration   = trim($matches[2] ?? '');
  $pdf->SetXY(31.5, 65);
//   $pdf->Write(1, strtoupper($student['Course_Short_Name']) . ' (' . ucwords($student['Sub_Course']) . ')');
 $pdf->Write(1, strtoupper($courseName));
  $pdf->SetXY(125.5, 72);
 $pdf->Write(1, strtoupper($duration));
 $pdf->SetFont('Arial', '', 10);
 $pdf->SetXY(156, 111.8);
  $pdf->Write(1, $address['present_district']);
  $pdf->SetXY(20, 118.8);
  $pdf->Write(1, $address['present_state']);
  $pdf->SetXY(85, 118.8);
  $pdf->Write(1, $address['present_pincode']);
  $contact = $student['Contact']; // 8156902417
$spacedContact = implode('   ', str_split($contact));
   $pdf->SetXY(41, 156.3);
  $pdf->Write(1, $spacedContact);
  $pdf->SetXY(114, 154.8);
  $pdf->Write(1, $student['Email']);
    $pdf->SetXY(25, 162.5);
  $pdf->Write(1, $student['Religion']);
   $pdf->SetXY(30, 170);
  $pdf->Write(1, $student['Nationality']);
 
// $pdf->SetXY(40, 104.8);
// $pdf->MultiCell(170, 5, $presentAddress);
//   echo('<pre>');print_r($student);die;
//   $x = 12;
//   $y = 84;
//   foreach (mb_str_split($student_name) as $char) {
//     $pdf->SetXY($x, $y);
//     $pdf->Write(1, $char);
//     $x += 6.2;
//   }
//   $mother_name = $student['Mother_Name'];
//   $x = 12;
//   $y = 93.5;
//   foreach (mb_str_split($mother_name) as $char) {
//     $pdf->SetXY($x, $y);
//     $pdf->Write(1, $char);
//     $x += 6.2;
//   }
//   $father_name = $student['Father_Name'];
//   $x = 12;
//   $y = 103;
//   foreach (mb_str_split($father_name) as $char) {
//     $pdf->SetXY($x, $y);
//     $pdf->Write(1, $char);
//     $x += 6.2;
//   }
//   // $pdf->SetXY(30, 165.9);
//   //   $pdf->Write(1, $student['Gender']);

  if ($student['Gender'] == 'Male') {
    $pdf->Image($check, 29.6, 183.4, 3, 3);
  } else if ($student['Gender'] == 'Female') {
     $pdf->Image($check, 45.6, 183.4, 3, 3);
  }
//   // Category
  if ($student['Category'] == 'SC') {
    $pdf->Image($check, 45.8, 176, 3, 3);
  }    else if ($student['Category'] == 'ST') {
    $pdf->Image($check, 59.8, 176, 3, 3);
  }  else if ($student['Category'] == 'OBC') {
    $pdf->Image($check, 73.8, 176, 3, 3);
  } else if ($student['Category'] == 'General') {
    $pdf->Image($check, 29.8, 176, 3, 3);
  } 
  $pdf->Image($check, 13, 204.5, 3, 3);

//   if ($student['Nationality'] == 'Indian') {
//     $pdf->Image($check, 98, 112, 3, 3);
//   } else if ($student['Nationality'] == 'NRI') {
//     $pdf->Image($check, 110.5, 112, 3, 3);
//   }

  $dob = date("dmY", strtotime($student['DOB']));
  $dobChars = str_split($dob);
  $xPositions = [
    105,
    110,
    120,
    125,
    135,
    140,
    145,
    150
  ];
  $y = 184.3;
  foreach ($dobChars as $index => $char) {
    $pdf->SetXY($xPositions[$index], $y);
    $pdf->Write(1, $char);
  }
//   if ($student['Religion'] == 'Hindu') {
//     $pdf->Image($check, 45, 121.7, 3, 3);
//   } else if ($student['Religion'] == 'Muslim') {
//     $pdf->Image($check, 68, 121.7, 3, 3);
//   } else if ($student['Religion'] == 'Sikh') {
//     $pdf->Image($check, 91, 121.7, 3, 3);
//   } else if ($student['Religion'] == 'Christian') {
//     $pdf->Image($check, 115.5, 121.7, 3, 3);
//   } else if ($student['Religion'] == 'Jain') {
//     // $pdf->Image($check, 135.5, 121.7, 3, 3);
//     $pdf->SetXY(155, 122.7);
//     $pdf->Write(1, $student['Religion']);
//   }
//   // echo('<pre>');print_r($student['Marital_Status']);die;

//   if ($student['Marital_Status'] == 'Married') {
//     $pdf->Image($check, 145, 135, 3, 3);
//   } else if ($student['Marital_Status'] == 'Unmarried') {
//     $pdf->Image($check, 175, 135, 3, 3);
//   }

//   $address = json_decode($student['Address'], true);
//   $presentAddress = strtoupper($address['present_address']);
//   $addressChars = str_split($presentAddress);
//   $startX = 12;
//   $startY1 = 146.5;
//   $startY2 = 151.8;
//   $boxWidth = 6.2;
//   foreach ($addressChars as $i => $char) {
//     if ($i < 30) {
//       $x = $startX + ($i * $boxWidth);
//       $pdf->SetXY($x, $startY1);
//     } else if ($i < 60) {
//       $x = $startX + (($i - 30) * $boxWidth);
//       $pdf->SetXY($x, $startY2);
//     } else {
//       break;
//     }
//     $pdf->Write(1, $char);
//   }

//   $x = 25;
//   $y = 156.3;
//   foreach (mb_str_split($address['present_city']) as $char) {
//     $pdf->SetXY($x, $y);
//     $pdf->Write(1, $char);
//     $x += 6.2;
//   }
//   $x = 25;
//   $y = 161.3;
//   foreach (mb_str_split($address['present_district']) as $char) {
//     $pdf->SetXY($x, $y);
//     $pdf->Write(1, $char);
//     $x += 6.2;
//   }
//   $x = 25;
//   $y = 166.1;
//   foreach (mb_str_split($address['present_state']) as $char) {
//     $pdf->SetXY($x, $y);
//     $pdf->Write(1, $char);
//     $x += 6.2;
//   }
//   $x = 113;
//   $y = 161.3;
//   foreach (mb_str_split($address['present_pincode']) as $char) {
//     $pdf->SetXY($x, $y);
//     $pdf->Write(1, $char);
//     $x += 6.2;
//   }
//   // Student Mobile No
//   // $pdf->SetXY(44, 180.2);
//   // $pdf->Write(1, $student['Contact']);
//   $x = 37.4;
//   $y = 179.6;
//   foreach (mb_str_split($student['Contact']) as $char) {
//     $pdf->SetXY($x, $y);
//     $pdf->Write(1, $char);
//     $x += 6.2;
//   }
//   $x = 37.4;
//   $y = 179.6;
//   foreach (mb_str_split($student['Contact']) as $char) {
//     $pdf->SetXY($x, $y);
//     $pdf->Write(1, $char);
//     $x += 6.2;
//   }
//   // print_r($student['Alternate_Contact']);die;
//   $x = 137;
//   $y = 179.6;
//   foreach (mb_str_split($student['Alternate_Contact']) as $char) {
//     $pdf->SetXY($x, $y);
//     $pdf->Write(1, $char);
//     $x += 6.2;
//   }
//   // Alternate
//   // $pdf->SetXY(134, 228.2);
//   // $pdf->Write(1, $student['Alternate_Contact']);

//   // Email
//   // $pdf->SetXY(34, 249.2);
//   // $pdf->Write(1, $student['Email']);

//   $x = 12.4;
//   $y = 190;
//   foreach (mb_str_split(strtoupper($student['Email'])) as $char) {
//     $pdf->SetXY($x, $y);
//     $pdf->Write(1, $char);
//     $x += 6.2;
//   }
//   $x = 44;
//   $y = 196.8;
//   foreach (mb_str_split(strtoupper($student['Aadhar_Number'])) as $char) {
//     $pdf->SetXY($x, $y);
//     $pdf->Write(1, $char);
//     $x += 6.2;
//   }
//   $academis = array('High School', 'Intermediate', 'Under Graduation', 'Post Graduation', 'Other');
//   $pdf->SetFont('Arial', '', 6);
//   $y = '215';
//   foreach ($academis as $academic) {
//     $x = '44';

//     // Details
//     $type = $academic == 'Under Graduation' ? 'UG' : ($academic == 'Post Graduation' ? 'PG' : $academic);
//     $data = $conn->query("SELECT * FROM Student_Academics WHERE Student_ID = $id AND Type = '$type'");
//     if ($data->num_rows > 0) {

//       $data = mysqli_fetch_assoc($data);

//       $pdf->SetXY($x, $y);
//       // $pdf->Write(1, substr($data['Board/Institute'], 0, 19));
//       $pdf->Write(1, strlen($data['Board/Institute']) > 20 ? substr($data['Board/Institute'], 0, 18) . '...' : $data['Board/Institute']);


//       $x += 43;
//       $pdf->SetXY($x, $y);
//       $pdf->Write(1, substr($data['Year'], 0, 15));
//       $pdf->SetFont('Arial', '', 5);
//       $x += 39;
//       $pdf->SetXY($x, $y);
//       $pdf->Write(1, strlen($data['Subject']) > 44 ? substr($data['Subject'], 0, 43) . '...' : $data['Subject']);
//       $pdf->SetFont('Arial', '', 6);
//       $x += 60;
//       $pdf->SetXY($x, $y);
//       $pdf->Write(1, substr($data['Total_Marks'], 0, 4));
//     }
//     $y += 4.7;
//   }
$academis = array('High School', 'Intermediate', 'Under Graduation', 'Post Graduation', 'Other');
$pdf->SetFont('Arial', '', 6);

// Manual Y positions for each academic level
$yPositions = array(
    'High School' => 233.7,
 'Intermediate' => 241.7,
 'Under Graduation' => 248.7,
    'Post Graduation' => 248.7,
 'Other' => 256.7
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
        $x += 44;

        // Board / Institute
        $pdf->SetXY($x, $y);
        $pdf->Write(1, strlen($row['board']) > 20 ? substr($row['board'], 0, 18).'...' : $row['board']);
        $x += 29.5;

        // Year
        $pdf->SetXY($x, $y);
        $pdf->Write(1, $row['Year']);
        $x += 41;

        // Subject
        $pdf->SetFont('Arial', '', 5);
        $pdf->SetXY($x, $y);
        $pdf->Write(1, strlen($row['Subject']) > 44 ? substr($row['Subject'], 0, 43).'...' : $row['Subject']);
        $pdf->SetFont('Arial', '', 6);
        $x += 55;

        // Marks
        $pdf->SetXY($x, $y);
        $pdf->Write(1, substr($row['Total_Marks'], 0, 4));
    }
}


//   if (filetype($signature) === 'file' && file_exists($signature)) {
//     try {
//       $filename = $id . "_Signature" . $file_extensions[0];
//       $image = $path . $filename;
//       $pdf->Image($image, 163, 257.5, 32, 9);
//       $student_signature = $image;
//     } catch (Exception $e) {
//       try {
//         $filename = $id . "_Signature" . $file_extensions[1];
//         $image = $path . $filename;
//         $pdf->Image($image, 163, 257.5, 32, 9);
//         $student_signature = $image;
//       } catch (Exception $e) {
//         try {
//           $filename = $id . "_Signature" . $file_extensions[2];
//           $image = $path . $filename;
//           $pdf->Image($image, 163, 257.5, 32, 9);
//           $student_signature = $image;
//         } catch (Exception $e) {
//           echo 'Message: ' . $e->getMessage();
//         }
//       }
//     }
//   }
  // if (filetype($signature) === 'file' && file_exists($signature)) {
  //   try {
  //     $filename = $id . "_Signature" . $file_extensions[0];
  //     $image = $path . $filename;
  //     $pdf->Image($image, 163, 275.5, 32, 9);
  //     $student_signature = $image;
  //   } catch (Exception $e) {
  //     try {
  //       $filename = $id . "_Signature" . $file_extensions[1];
  //       $image = $path . $filename;
  //       $pdf->Image($image, 163, 275.5, 32, 9);
  //       $student_signature = $image;
  //     } catch (Exception $e) {
  //       try {
  //         $filename = $id . "_Signature" . $file_extensions[2];
  //         $image = $path . $filename;
  //         $pdf->Image($image, 163, 275.5, 32, 9);
  //         $student_signature = $image;
  //       } catch (Exception $e) {
  //         echo 'Message: ' . $e->getMessage();
  //       }
  //     }
  //   }
  // }
    // $form_completion_date = date("d-m-Y", strtotime($student['form_completion_date']?$student['form_completion_date']:$student['Created_At']));
  // echo('<pre>');print_r($form_completion_date);die;
//   $pdf->SetFont('Arial', '', 9);

//   $pdf->SetXY(18, 265.7);
//   $pdf->Write(1, $form_completion_date);
  //  $pdf->SetXY(18, 276);
  // $pdf->Write(1, $form_completion_date);
//   $pageId = $pdf->importPage(2, PdfReader\PageBoundaries::MEDIA_BOX);
//   $pdf->addPage();
//   $pdf->useImportedPage($pageId, 0, 0, 210);
  //  echo('<pre>');print_r($student);die;
   $pageId = $pdf->importPage(2, PdfReader\PageBoundaries::MEDIA_BOX);
  $pdf->addPage();
  $pdf->useImportedPage($pageId, 0, 0, 210);
  if (filetype($signature) === 'file' && file_exists($signature)) {
    try {
      $filename = $id . "_Signature" . $file_extensions[0];
      $image = $path . $filename;
      $pdf->Image($image, 42, 207.5, 32, 9);
      $student_signature = $image;
    } catch (Exception $e) {
      try {
        $filename = $id . "_Signature" . $file_extensions[1];
        $image = $path . $filename;
        $pdf->Image($image, 42, 207.5, 32, 9);
        $student_signature = $image;
      } catch (Exception $e) {
        try {
          $filename = $id . "_Signature" . $file_extensions[2];
          $image = $path . $filename;
          $pdf->Image($image, 42, 207.5, 32, 9);
          $student_signature = $image;
        } catch (Exception $e) {
          echo 'Message: ' . $e->getMessage();
        }
      }
    }
  }
  $pdf->Output('I', 'Application mdu.pdf');
}
