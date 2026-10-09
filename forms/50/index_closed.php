<?php
require '../../includes/db-config.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$student_id = $_GET['student_id'];
$id = base64_decode($student_id);
$id = intval(str_replace('W1Ebt1IhGN3ZOLplom9I', '', $id));

// $get_record = $conn->query("SELECT Students.*,   Admission_Sessions.Name AS Addmission_session,Courses.Name AS Course_name FROM Students LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID=Admission_Sessions.ID LEFT JOIN Students.Course_ID = Courses.ID  WHERE Students.ID= $id ");
$get_record = $conn->query("SELECT Students.*,Admission_Sessions.Name AS Admission_session, Courses.Name AS Course_name FROM Students LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID = Admission_Sessions.ID LEFT JOIN Courses ON Students.Course_ID = Courses.ID  WHERE Students.ID = $id");
$student = mysqli_fetch_assoc($get_record);



$student_name = $student['First_Name'] . ' ' . $student['Middle_Name'] . ' ' . $student['Last_Name'];
$dob = $student['DOB'];
list($year, $month, $day) = explode('-', $dob);
$address = json_decode($student['Address'], true);
// print_r( $address['present_city']);die;
$present_address = str_split($address['present_address'], 18);
$file_extensions = array('.png', '.jpg', '.jpeg');

//this folder will have there images.


use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;

ob_end_clean();

require_once('../../extras/TCPDF/tcpdf.php');
require_once('../../extras/vendor/setasign/fpdf/fpdf.php');
require_once('../../extras/vendor/setasign/fpdi/src/autoload.php');

$pdf = new Fpdi();
$pdf->AddPage();
$pdf->setSourceFile('../../forms/50/GKU ADMISSION FORM.pdf');
$tplId = $pdf->importPage(1);
$pdf->useTemplate($tplId, 0, 0, 210);

// Set a standard font (Arial in this case)
$pdf->SetFont('Arial', '', 12);

$path = "photos/";

// Photo
$student_photo = "";
$photo = "";
$photo = $conn->query("SELECT Location FROM Student_Documents WHERE Student_ID = $id AND Type = 'Photo'");
if ($photo->num_rows > 0) {
    $photo = mysqli_fetch_assoc($photo);
    // echo "<pre>";
    // print_r($photo);die;
    $photo = "../.." . $photo['Location'];
    // echo $photo['Location'];die;
    $student_photo = base64_encode(file_get_contents($photo));
    $i = 0;
    $end = 3;
    while ($i < $end) {
        $data1 = base64_decode($student_photo);

        $filename1 = $id . "_Photo" . $file_extensions[$i];
        file_put_contents($filename1, $data1); //we save our new images to the path above
        $i++;
    }
} else {
    $photo = "";
}

// Set position and add content
if (filetype($photo) === 'file' && file_exists($photo)) {
    try {
        $filename = $id . "_Photo" . $file_extensions[0];
        $image = $filename;
        $pdf->Image($image, 158, 23.6, 35.5, 38.9);
        $photo = $image;
    } catch (Exception $e) {
        try {
            $filename = $id . "_Photo" . $file_extensions[1];
            $image = $filename;
            $pdf->Image($image, 158, 23.6, 35.5, 38.9);
            $photo = $image;
        } catch (Exception $e) {
            try {
                $filename = $id . "_Photo" . $file_extensions[2];
                $image = $filename;
                $pdf->Image($image, 158, 23.6, 35.5, 38.9);
                $photo = $image;
            } catch (Exception $e) {
                echo 'Message: ' . $e->getMessage();
            }
        }
    }
}
$pdf->SetXY(89, 60);
$pdf->Cell(0, 0, $student['Admission_session'], 0, 0, 'L', 0);
$pdf->SetXY(27, 107.5);
$pdf->Cell(0, 0, $student_name, 0, 0, 'C', 0);
$pdf->SetXY(59, 82.5);
$pdf->Cell(0, 0, $student['Course_name'], 0, 0, 'L', 0);
$pdf->SetXY(56, 115.5);
$pdf->Cell(0, 0, $student['Father_Name'], 0, 0, 'L', 0);
$pdf->SetXY(58, 124.5);
$pdf->Cell(0, 0, $student['Mother_Name'], 0, 0, 'L', 0);
$pdf->SetXY(20, 133);
$pdf->Cell(0, 0, $year, 0, 0, 'C', 0);
$pdf->SetXY(86, 133);
$pdf->Cell(0, 0,  $month, 0, 0, 'L', 0);
$pdf->SetXY(65, 133);
$pdf->Cell(0, 0, $day, 0, 0, 'L', 0);
$pdf->SetXY(69, 141.5);
$pdf->Cell(0, 0, $student['Gender'], 0, 0, 'L', 0);
$pdf->SetXY(52, 150);
$pdf->Cell(0, 0, $student['Nationality'], 0, 0, 'L', 0);
$pdf->SetXY(40, 166.5);
$pdf->Cell(0, 0, $student['Category'], 0, 0, 'C', 0);
$pdf->SetXY(62, 175);
$pdf->Cell(0, 0, $student['Aadhar_Number'], 0, 0, 'L', 0);
$pdf->SetXY(48, 192);
$pdf->Cell(0, 0, $present_address[0], 0, 0, 'L', 0);
if (!empty($present_address[1])) {
    $pdf->SetXY(55, 200.5);
    $pdf->Cell(0, 0, $present_address[1], 0, 0, 'L', 0);
}

$pdf->SetXY(52, 209.5);
$pdf->Cell(0, 0, $address['present_city'], 0, 0, 'L', 0);
$pdf->SetXY(52, 217.5);
$pdf->Cell(0, 0, $address['present_state'], 0, 0, 'L', 0);
$pdf->SetXY(52, 225.5);
$pdf->Cell(0, 0, $address['present_pincode'], 0, 0, 'L', 0);
$pdf->SetXY(87, 234.5);
$pdf->Cell(0, 0, $student['Contact'], 0, 0, 'L', 0);
$pdf->SetXY(75, 242.5);
$pdf->Cell(0, 0, $student['Email'], 0, 0, 'L', 0);
$pdf->AddPage();
$tplId = $pdf->importPage(2);
$pdf->useTemplate($tplId, 0, 0, 210);
$pdf->SetFont('Arial', '', 10);
$doc = $conn->query("SELECT * FROM Student_Academics WHERE Student_Academics.Student_ID = $id");
$x_cor = 20;
$y_cor = 60;
$pdf->SetXY($x_cor, $y_cor);

while ($row = $doc->fetch_assoc()) {
    $pdf->SetX($x_cor);
    $cellHeight = 7;
    $pdf->Cell(18, $cellHeight, $row['Total_Marks'], 0, 0, 'L');
    $pdf->Cell(35, $cellHeight, $row['Board/Institute'], 0, 0, 'L');
    $pdf->Cell(17, $cellHeight, $row['Year'], 0, 0, 'C');
    $subjectY = $y_cor - 2;
    $subjectX = $x_cor + 70;
    
    if (strlen($row['Subject']) > 30) {
        $pdf->SetXY($subjectX, $subjectY + 3);
        $pdf->SetFont('Arial', '', 6);
        $nameParts = explode("\n", wordwrap($row['Subject'], 40));
        $pdf->MultiCell(80, 3, $nameParts[0] . (isset($nameParts[1]) ? chr(10) . $nameParts[1] : ''), 0, 'L');
    } else {
        $pdf->SetFont('Arial', '', 6);
        $pdf->Cell(71, 8, $row['Subject'], 0, 0, 'L');
    }
    $pdf->SetFont('Arial', '', 10);
    // echo('<pre>');print_r($row);die;
    $pdf->SetX($x_cor + 124);
    $pdf->Cell(15, $cellHeight, $row['Max_Marks'], 0, 0, 'L');
    $pdf->Cell(17, $cellHeight, $row['Marks_Obtained'], 0, 0, 'L');
    $pdf->Cell(15, $cellHeight, $row['Total_Marks'], 0, 0, 'L');
    $y_cor += $cellHeight;
    $pdf->SetXY($x_cor, $y_cor);
}
$pdf->SetXY(22, 209);
$pdf->Cell(0, 0, $student_name, 0, 0, 'L', 0);
$pdf->SetXY(29, 209);
$pdf->Cell(0, 0, $student['Father_Name'], 0, 0, 'C', 0);
$student_signature = "";
$signature = "";
$signature = $conn->query("SELECT Location FROM Student_Documents
WHERE Student_ID = $id AND Type = 'Student Signature'");
if ($signature->num_rows > 0) {
    $signature = mysqli_fetch_assoc($signature);
    $signature = "../.." . $signature['Location'];
    $student_signature = base64_encode(file_get_contents($signature));
    $i = 0;
    $end = 3;
    while ($i < $end) {
        $data2 = base64_decode($student_signature);
        $filename2 = $id . "_Student_Signature" . $file_extensions[$i];
       
        file_put_contents($filename2, $data2); 
        $i++;
    }
} else {
    $signature = "";
}
if (filetype($signature) === 'file' && file_exists($signature)) {
    try {
        $filename = $id . "_Student_Signature" . $file_extensions[0];
        $image = $filename;
        $pdf->Image($image, 125, 178, 30.2, 11.3);
        $student_signature = $image;
    } catch (Exception $e) {
        try {
            $filename = $id . "_Student_Signature" . $file_extensions[1];
            $image = $filename;
            $pdf->Image($image, 125, 178, 30.2, 11.3);
            $student_signature = $image;
        } catch (Exception $e) {
            try {
                $filename = $id . "_Signature" . $file_extensions[2];
                $image = $filename;
                $pdf->Image($image, 125, 178, 30.2, 11.3);
                $student_signature = $image;
            } catch (Exception $e) {
                echo 'Message: ' . $e->getMessage();
            }
        }
    }
}
if (filetype($signature) === 'file' && file_exists($signature)) {
    try {
        $filename = $id . "_Student_Signature" . $file_extensions[0];
        $image = $filename;
        $pdf->Image($image, 145, 246, 30.2, 11.3);
        $student_signature = $image;
    } catch (Exception $e) {
        try {
            $filename = $id . "_Student_Signature" . $file_extensions[1];
            $image = $filename;
            $pdf->Image($image, 145, 246, 30.2, 11.3);
            $student_signature = $image;
        } catch (Exception $e) {
            try {
                $filename = $id . "_Signature" . $file_extensions[2];
                $image = $filename;
                $pdf->Image($image, 145, 246, 30.2, 11.3);
                $student_signature = $image;
            } catch (Exception $e) {
                echo 'Message: ' . $e->getMessage();
            }
        }
    }
}




$parent_signature = "";
$p_signature = "";
$p_signature = $conn->query("SELECT Location FROM Student_Documents
WHERE Student_ID = $id AND Type = 'Parent Signature'");
if ($p_signature->num_rows > 0) {
    $p_signature = mysqli_fetch_assoc($p_signature);
    $p_signature = "../.." . $p_signature['Location'];

    $parent_signature = base64_encode(file_get_contents($p_signature));
    $i = 0;
    $end = 3;
    while ($i < $end) {
        $data2 = base64_decode($parent_signature);
        $filename2 = $id . "_parent_Signature" . $file_extensions[$i];
        //$file_extensions loops through the file extensions
        file_put_contents($filename2, $data2); //we save our new images to the path above
        $i++;
    }
} else {
    $p_signature = "";
}
if (filetype($p_signature) === 'file' && file_exists($p_signature)) {
    try {
        $filename = $id . "_parent_Signature" . $file_extensions[0];
        $image = $filename;
        $pdf->Image($image, 150, 259.5, 15.2, 6.3);
        $parent_signature = $image;
    } catch (Exception $e) {
        try {
            $filename = $id . "_parent_Signature" . $file_extensions[1];
            $image = $filename;
            $pdf->Image($image, 150, 259.5, 15.2, 6.3);
            $parent_signature = $image;
        } catch (Exception $e) {
            try {
                $filename = $id . "_Signature" . $file_extensions[2];
                $image = $filename;
                $pdf->Image($image, 150, 259.5, 15.2, 6.3);
                $parent_signature = $image;
            } catch (Exception $e) {
                echo 'Message: ' . $e->getMessage();
            }
        }
    }
}

$documents = $conn->query("SELECT Type, Location FROM Student_Documents WHERE Student_ID = $id and Type!='Student Signature' and Type!='Photo'");
      while ($document = $documents->fetch_assoc()) {
        $files = explode("|", $document['Location']);
        foreach ($files as $file) {
          $pdf->AddPage();
          $pdf->SetMargins(10, 10, 10);
          // print_r(pathinfo("../..".$file));die;
          // print_r(mime_content_type("../..".$file));die;
          if (!file_exists("../.." . $file)) {
            die('Error: File not found at ' . "../.." . $file);
          }
          // if (mime_content_type("../..".$file) !== 'image/jpeg') {
          //     die('Error: File is not a valid PNG.');
          // }

          $width = $document['Type'] == 'Photo' ? 30.5 : ($document['Type'] == 'Student Signature' ? 30.2 : 190);
          $height = $document['Type'] == 'Photo' ? 35.9 : ($document['Type'] == 'Student Signature' ? 11.3 : 270);

          $pdf->image("../.." . $file, 10, 10, $width, $height);
        }
      }

$pdf->Output('I', 'bvoc_student.pdf');
$i = 0;
$end = 3;
while ($i < $end) {
    // Delete Photos
    if (!empty($student_photo)) {
        $filename = $id . "_Photo" . $file_extensions[$i];
        //$file_extensions loops through the file extensions
        unlink($filename);
    }
    if (!empty($Parent_signature)) {
        $filename = $id . "_Parent_Signature" . $file_extensions[$i];
        //$file_extensions loops through the file extensions
        unlink($filename);
    }
    // Delete Signatures
    if (!empty($student_signature)) {
        $filename = $id . "_Student_Signature" . $file_extensions[$i];
        //$file_extensions loops through the file extensions
        unlink($filename);
    }    
    $i++;
}
