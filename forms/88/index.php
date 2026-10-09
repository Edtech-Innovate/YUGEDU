<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;

if (isset($_GET['student_id'])) {
  require '../../includes/db-config.php';
  session_start();

  if ($_SESSION['university_id'] != 88) {
    header('Location: /ams/');
  }

  $id = mysqli_real_escape_string($conn, $_GET['student_id']);
  $id = base64_decode($id);
  $id = intval(str_replace('W1Ebt1IhGN3ZOLplom9I', '', $id));
  $student = $conn->query("SELECT Students.*, Courses.Name as Course,Courses.Short_Name as Course_Short_Name, Sub_Courses.Name as Sub_Course, Admission_Sessions.Name as Session, Admission_Types.Name as Type, Universities.Vertical, TIMESTAMPDIFF(YEAR, DOB, CURDATE()) AS Age FROM Students LEFT JOIN Universities ON Students.University_ID = Universities.ID LEFT JOIN Courses ON Students.Course_ID = Courses.ID LEFT JOIN Sub_Courses ON Students.Sub_Course_ID = Sub_Courses.ID LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID = Admission_Sessions.ID LEFT JOIN Admission_Types ON Students.Admission_Type_ID = Admission_Types.ID WHERE Students.ID = $id");
  $student = mysqli_fetch_assoc($student);
  $address = json_decode($student['Address'], true);
  
  $sessionString = $student['Session'] ?? 'Jul-2022'; // e.g., "Jul-2022"
    
    // Extract the 4-digit year
    preg_match('/\d{4}/', $sessionString, $matches);
    $fullYear = isset($matches[0]) ? (int)$matches[0] : (int)date('Y');
    
    // Get last 2 digits for start year (e.g., "22") and end year (e.g., "23")
    $startSession = substr((string)$fullYear, -2);
    $endSession = substr((string)($fullYear + 1), -2);
 
  



  require_once('../../extras/vendor/setasign/fpdf/fpdf.php');
  require_once('../../extras/vendor/setasign/fpdi/src/autoload.php');

$pdf = new FPDI();
$pdf->SetTitle('Application Form');
$pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(false, 0);

$pageCount = $pdf->setSourceFile('sabarmati.pdf');
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

  // Parent Signature
  // FIX 1: uses its own filename suffix ("_ParentSignature") so it no longer
  //        overwrites the student signature files saved above.
  // FIX 2: removed the leftover debug echo/print_r/die that was killing the
  //        whole script before the PDF could ever be generated.
  $parent_signature = "";
  $ParentSignature = $conn->query("SELECT Location FROM Student_Documents WHERE Student_ID = $id AND Type = 'Parent Signature'");

  if ($ParentSignature->num_rows > 0) {
    $row = mysqli_fetch_assoc($ParentSignature);
    $ParentSignaturePath = "../.." . $row['Location'];

    if (file_exists($ParentSignaturePath)) {
      $parent_signature = base64_encode(file_get_contents($ParentSignaturePath));

      $i = 0;
      $end = 3;
      while ($i < $end) {
        $data3 = base64_decode($parent_signature);
        $filename3 = $id . "_ParentSignature" . $file_extensions[$i];
        file_put_contents($path . $filename3, $data3);
        $i++;
      }
      $parent_signature = $ParentSignaturePath; // keep source path for filetype()/file_exists() check below
    } else {
      $parent_signature = "";
    }
  } else {
    $parent_signature = "";
  }

  // Page 1
  $pageId = $pdf->importPage(1, PdfReader\PageBoundaries::MEDIA_BOX);
  $pdf->addPage();
  $pdf->useImportedPage($pageId, 0, 0, 210);
 // $startSession
    
  $pdf->SetXY(64, 77.0);
  $pdf->Write(1, $startSession);
  
  $pdf->SetXY(84, 77.0);
  $pdf->Write(1, $endSession);

  // Photo
  if (filetype($photo) === 'file' && file_exists($photo)) {
    try {
      $filename = $id . "_Photo" . $file_extensions[0];
      $image = $path . $filename;
      $pdf->Image($image, 160.7, 87.5, 43, 43.5);
      $photo = $image;
    } catch (Exception $e) {
      try {
        $filename = $id . "_Photo" . $file_extensions[1];
        $image = $path . $filename;
        $pdf->Image($image, 160.7, 87.5, 43, 43.5);
        $photo = $image;
      } catch (Exception $e) {
        try {
          $filename = $id . "_Photo" . $file_extensions[2];
          $image = $path . $filename;
          $pdf->Image($image, 160.7, 87.5, 43, 43.5);
          $photo = $image;
        } catch (Exception $e) {
          echo 'Message: ' . $e->getMessage();
        }
      }
    }
  }
  
  //Student's Signature
     if (filetype($signature) === 'file' && file_exists($signature)) {
    try {
      $filename = $id . "_Signature" . $file_extensions[0];
      $image = $path . $filename;
      $pdf->Image($image, 160.7, 130.5, 43, 9);
      $student_signature = $image;
    } catch (Exception $e) {
      try {
        $filename = $id . "_Signature" . $file_extensions[1];
        $image = $path . $filename;
        $pdf->Image($image, 160.7, 130.5, 43, 9);
        $student_signature = $image;
      } catch (Exception $e) {
        try {
          $filename = $id . "_Signature" . $file_extensions[2];
          $image = $path . $filename;
          $pdf->Image($image,  160.7, 130.5, 43, 9);
          $student_signature = $image;
        } catch (Exception $e) {
          echo 'Message: ' . $e->getMessage();
        }
      }
    }
  }
  
  $pdf->SetXY(35, 137.8);
  $pdf->Write(1, $student['Enrollment_No']);
 // print_r($student['Enrollment_No']);die;
  $student_name = trim($student['First_Name'] . ' ' . $student['Middle_Name'] . ' ' . $student['Last_Name']);
    
    // Starting coordinates
    $startX = 17.0; 
    $startY = 160.9;
    
    // Define the spacing/width between each character block
    $charWidth = 6.4; 
    
    $student_nameChars = str_split($student_name);
    
    foreach ($student_nameChars as $index => $char) {
        // Calculate X position dynamically for every character
        $currentX = $startX + ($index * $charWidth);
        
        $pdf->SetXY($currentX, $startY);
        $pdf->Write(1, $char);
    }
 // $pdf->Write(1, $student_name);
     $courseString = $student['Sub_Course'];
  preg_match('/^(.*?)\s*\((.*?)\)$/', $courseString, $matches);
  $courseName = trim($matches[1] ?? '');
  $duration   = trim($matches[2] ?? '');
//   echo('<pre>');print_r($student);die;
 $pdf->SetXY(98, 67.7);
$pdf->MultiCell(120, 4, strtoupper($student['Course'].' ('.$student['Sub_Course'].')'), 0, 'L');
  $dob = date("dmY", strtotime($student['DOB']));
  $dobChars = str_split($dob);
  $xPositions = [
    45,
    51,
    63,
    69,
    83,
    89,
    95,
    101
  ];
  $y = 206.9;
  foreach ($dobChars as $index => $char) {
    $pdf->SetXY($xPositions[$index], $y);
    $pdf->Write(1, $char);
  }
  //$student['Gender'] = 'Male';
   if ($student['Gender'] == 'Male') {
     $pdf->Image($check, 52.0, 224.0, 3, 3);
  
  } else if ($student['Gender'] == 'Female') {
     $pdf->Image($check, 77.9, 224.0, 3, 3);
  }
    $pdf->SetXY(113.7, 269.0);
  $pdf->Write(1, strtoupper($student['Nationality']));
  //$student['Category'] = 'PH';
    if ($student['Category'] == 'SC') {
    $pdf->Image($check, 66.0, 236.5, 2, 2);
  } else if ($student['Category'] == 'ST') {
    $pdf->Image($check, 84.5, 236.5, 2, 2);
  } else if ($student['Category'] == 'OBC') {
    $pdf->Image($check, 104.5, 236.5, 2, 2);
  } else if ($student['Category'] == 'General') {
    $pdf->Image($check, 47.5, 236.5, 2, 2);     
  }
  else if ($student['Category'] == 'EWS') {
    $pdf->Image($check, 125.7, 236.5, 2, 2);    
  }
  else if ($student['Category'] == 'PH') {
    $pdf->Image($check, 147.2, 236.5, 2, 2);    
  }
//   echo('<pre>');print_r($student);die;
  /* if ($student['Marital_Status'] == 'Unmarried') {
      $pdf->Image($check, 121.4, 135.5, 2, 2);
     
  
  } else {
      $pdf->Image($check, 142.4, 135.5, 2, 2);
  }*/
  
   $pdf->SetXY(54, 257.2);
    $pdf->Write(1, $student['Marital_Status']);
 // print_r($student);die;
     $pdf->SetXY(44.7, 269.3);
     $pdf->Write(1, $student['Religion']);
     $pdf->SetXY(46.7, 213);
     $placeOfBirth=strtoupper($presentAddress.' '.$address['present_city'].' '.$address['present_district'].' '.$address['present_state']);
     $pdf->MultiCell(151, 7,$placeOfBirth , 0, 'L');
     //$pdf->SetXY(26.7, 154);
     //$pdf->Write(1, $student['Aadhar_Number']);
     //$pdf->SetXY(108, 154);
    // $pdf->Write(1, $student['abc_id']);
    //  $pdf->SetXY(26.7, 169.5);
     $father_name = trim($student['Father_Name']);
    
    // Starting coordinates for Father's Name
    $startX = 17.0; 
    $startY = 179.5;
    
    // Define the spacing/width between each character block
    $charWidth = 6.4; 
    
    $father_nameChars = str_split($father_name);
    
    foreach ($father_nameChars as $index => $char) {
        // Calculate X position dynamically for every character
        $currentX = $startX + ($index * $charWidth);
        
        $pdf->SetXY($currentX, $startY);
        $pdf->Write(1, $char);
    }
$mother_name = trim($student['Mother_Name']);
    
    // Starting coordinates for Mother's Name
    $startX = 17.0; 
    $startY = 193.5;
    
    // Define the spacing/width between each character block
    $charWidth = 6.4; 
    
    $mother_nameChars = str_split($mother_name);
    
    foreach ($mother_nameChars as $index => $char) {
        // Calculate X position dynamically for every character
        $currentX = $startX + ($index * $charWidth);
        
        $pdf->SetXY($currentX, $startY);
        $pdf->Write(1, $char);
    }
     /*$presentAddress = preg_replace('/\s+/', ' ', strtoupper(trim($address['present_address'])));
 $pdf->SetXY(26.7, 193);
 $newaddress=strtoupper($presentAddress.' '.$address['present_city'].' '.$address['present_district'].' '.$address['present_state']);
$pdf->MultiCell(151, 7,$newaddress , 0, 'L');
  $pdf->SetXY(137.7, 210);
     $pdf->Write(1, $address['present_pincode']);*/
    //   $pdf->SetXY(26.7, 154);
    //  $pdf->Write(1, $student['Contact']);


  $pageId = $pdf->importPage(2, PdfReader\PageBoundaries::MEDIA_BOX);
  $pdf->addPage();
  $pdf->useImportedPage($pageId, 0, 0, 210);
   
$x = 67.0;   // starting X
$y = 125.2;  
$cellWidth = 11.5; // Define a fixed width for each digit box

foreach (str_split($student['Contact']) as $digit) {
    $pdf->SetXY($x, $y);
    
    // Using Cell() with a fixed width, height, text, border (0), alignment ('C' for center), and fill (0)
    $pdf->Cell($cellWidth, 5, $digit, 0, 0, 'C');

    $x += $cellWidth; // Move X forward by the exact cell width
}
      $pdf->SetXY(66.7, 135.8);
     $pdf->Write(1, $student['Email']);
  

// $address['present_address'] = preg_replace('/\s+/', ' ', strtoupper(trim($address['present_address'])));

// $startX = 16.0;   // Starting X
// $startY = 83.3;   // Starting Y
// $cellWidth = 3.0; // Width for each character cell
// $lineHeight = 5.0; // Height of each row/cell
// $maxCharsPerRow = 30; // Maximum characters per row

// $x = $startX;
// $y = $startY;
// $charCount = 0;

// foreach (str_split($address['present_address']) as $char) {
//     // If max characters per row is reached, move to the next line
//     if ($charCount >= $maxCharsPerRow) {
//         $x = $startX;          // Reset X to starting position
//         $y += $lineHeight;     // Move Y down to the next row
//         $charCount = 0;        // Reset character count for the new row
//     }

//     $pdf->SetXY($x, $y);
//     $pdf->Cell($cellWidth, $lineHeight, $char, 0, 0, 'C');

//     $x += $cellWidth; // Move X forward by the exact cell width
//     $charCount++;
// }
// $address['present_city'] = strtoupper($address['present_city']);
// $address['present_district'] = strtoupper($address['present_district']);
// $address['present_state']  = strtoupper($address['present_state']);
$fulladdress = trim($address['present_address'] . ' ' . 
               $address['present_city'] . ' ' . 
               $address['present_district'] . ' ' . 
               $address['present_state']);

$x = 15.5;
$y = 83;
$cellWidth = 4.95;
$letterSpacing = 1;
$maxWidth = 190;
$lineHeight = 5.2;
$startX = 20;

foreach (mb_str_split($fulladdress) as $char) {

    if ($char === ' ') {
        continue; // skip spaces completely — no box, no advance
    }

    $pdf->SetXY($x, $y);
    $pdf->Cell($cellWidth, $lineHeight, $char, 0, 0, 'C');

    $x += $cellWidth + $letterSpacing;

    if ($x >= $maxWidth) {
        $y += $lineHeight;
        $x = $startX;
    }
}

  
 $x = 156.7;  // starting X for pincode
$y = 100;    // starting Y for pincode
$cellWidth = 6.3; // match the spacing/box width used previously

foreach (str_split($address['present_pincode']) as $digit) {
    $pdf->SetXY($x, $y);
    
    // Renders each digit inside a fixed-width cell to keep spacing identical
    $pdf->Cell($cellWidth, 5, $digit, 0, 0, 'C');

    $x += $cellWidth; // Move X forward by the exact cell width
}
   
//      // Academics section
/*$academis = array('High School', 'Intermediate', 'Under Graduation', 'Post Graduation', 'Other');
$pdf->SetFont('Arial', '', 6);

// Manual Y positions for each academic level 78.7
$yPositions = array(
    'High School' => 78.7,
 'Intermediate' => 88.7,
 'Under Graduation' => 98.7,
    'Post Graduation' => 108.7,
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
        $x += 78;

        // Board / Institute
        $pdf->SetXY($x, $y-6);
        $pdf->MultiCell(30, 4,  strlen($row['board']) > 32 ? substr($row['board'], 0, 32).'...' : $row['board'], 0,  'L');
        $x += 36.5;

        // Year
        $pdf->SetXY($x, $y-3);
        $pdf->Write(1, $row['Year']);
        $x += 42;

        // Marks
        $pdf->SetXY($x, $y-3);
        $pdf->Write(1, substr($row['Total_Marks'], 0, 4));
    }
}

  // Student Signature on page 2
  /*if (filetype($signature) === 'file' && file_exists($signature)) {
    try {
      $filename = $id . "_Signature" . $file_extensions[0];
      $image = $path . $filename;
      $pdf->Image($image, 26.7, 170.5, 32, 9);
      $student_signature = $image;
    } catch (Exception $e) {
      try {
        $filename = $id . "_Signature" . $file_extensions[1];
        $image = $path . $filename;
        $pdf->Image($image, 26.7, 170.5, 32, 9);
        $student_signature = $image;
      } catch (Exception $e) {
        try {
          $filename = $id . "_Signature" . $file_extensions[2];
          $image = $path . $filename;
          $pdf->Image($image,  26.7, 170.5, 32, 9);
          $student_signature = $image;
        } catch (Exception $e) {
          echo 'Message: ' . $e->getMessage();
        }
      }
    }
  }*/

  // Parent Signature on page 2
  // NOTE: placeholder position (100, 265.5, 32, 9) placed to the right of the
  // student signature box — adjust X/Y/width/height to match your template.
  /*if (!empty($parent_signature) && filetype($parent_signature) === 'file' && file_exists($parent_signature)) {
    try {
      $filename = $id . "_ParentSignature" . $file_extensions[0];
      $image = $path . $filename;
      $pdf->Image($image, 140, 265.5, 32, 9);
    } catch (Exception $e) {
      try {
        $filename = $id . "_ParentSignature" . $file_extensions[1];
        $image = $path . $filename;
        $pdf->Image($image, 140, 265.5, 32, 9);
      } catch (Exception $e) {
        try {
          $filename = $id . "_ParentSignature" . $file_extensions[2];
          $image = $path . $filename;
          $pdf->Image($image, 140, 265.5, 32, 9);
        } catch (Exception $e) {
          echo 'Message: ' . $e->getMessage();
        }
      }
    }
  }*/

  // Cleanup temp image files
  // FIX: previously unlink() looked in the wrong directory (missing $path
  // prefix) and searched for "_Student_Signature" when the files were
  // actually saved as "_Signature", so nothing ever got deleted. Also added
  // cleanup for the new "_ParentSignature" files.
  $i = 0;
  $end = 3;
  while ($i < $end) {
    // Delete Photos
    if (!empty($student_photo)) {
      $filename = $path . $id . "_Photo" . $file_extensions[$i];
      if (file_exists($filename)) {
        unlink($filename);
      }
    }

    // Delete Student Signatures
    if (!empty($student_signature)) {
      $filename = $path . $id . "_Signature" . $file_extensions[$i];
      if (file_exists($filename)) {
        unlink($filename);
      }
    }

    // Delete Parent Signatures
    if (!empty($parent_signature)) {
      $filename = $path . $id . "_ParentSignature" . $file_extensions[$i];
      if (file_exists($filename)) {
        unlink($filename);
      }
    }
    $i++;
  }

      $documents = $conn->query("SELECT Type, Location FROM Student_Documents WHERE Student_ID = $id AND Type NOT IN ('Photo', 'Student Signature','Parent Signature')");
      while ($document = $documents->fetch_assoc()) {
        $files = explode("|", $document['Location']);
        foreach ($files as $file) {
          $pdf->AddPage();
          $pdf->SetMargins(10, 10, 10);
          if (!file_exists("../.." . $file)) {
            die('Error: File not found at ' . "../.." . $file);
          }

          $width = $document['Type'] == 'Photo' ? 30.5 : ($document['Type'] == 'Student Signature' ? 30.2 : 190);
          $height = $document['Type'] == 'Photo' ? 35.9 : ($document['Type'] == 'Student Signature' ? 11.3 : 270);

          $pdf->image("../.." . $file, 10, 10, $width, $height);
        }
      }

  
  $pdf->Output('I', 'Application MIU.pdf');
}