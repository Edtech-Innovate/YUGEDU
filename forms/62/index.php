<?php
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_GET['student_id'])) {
    require '../../includes/db-config.php';
    session_start();

    if ($_SESSION['university_id'] != 62) {
        header('Location: /');
        exit;
    }

    $id = mysqli_real_escape_string($conn, $_GET['student_id']);
    $id = base64_decode($id);
    $id = intval(str_replace('W1Ebt1IhGN3ZOLplom9I', '', $id));
    
    $student_query = $conn->query("SELECT Students.*, Courses.Name as Course, Courses.Short_Name as Course_Short_Name, Sub_Courses.Name as Sub_Course, Admission_Sessions.Name as Session, Admission_Types.Name as Type, Universities.Vertical, TIMESTAMPDIFF(YEAR, DOB, CURDATE()) AS Age FROM Students LEFT JOIN Universities ON Students.University_ID = Universities.ID LEFT JOIN Courses ON Students.Course_ID = Courses.ID LEFT JOIN Sub_Courses ON Students.Sub_Course_ID = Sub_Courses.ID LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID = Admission_Sessions.ID LEFT JOIN Admission_Types ON Students.Admission_Type_ID = Admission_Types.ID WHERE Students.ID = $id");
    
    $student = mysqli_fetch_assoc($student_query);
    if (!$student) {
        die("Student not found.");
    }
    
    $address = json_decode($student['Address'], true) ?? [];

    require_once('../../extras/vendor/setasign/fpdf/fpdf.php');
    require_once('../../extras/vendor/setasign/fpdi/src/autoload.php');

    $pdf = new Fpdi();

    $pdf->SetTitle('Application Form');
    $pageCount = $pdf->setSourceFile('sgvu-admissionform.pdf');
    $pdf->AddFont('Hondo', '', 'hondo.php');
    $pdf->SetFont('Hondo', '', 10);

    // Tick Image
    $check = '../../assets/img/forms/checked.png';

    // Extensions
    $file_extensions = array('.png', '.jpg', '.jpeg');

    // Folder for images
    $path = "photos/";

    // Safe string split helper function to prevent deprecation notices
    function safe_str_split($string) {
        return mb_str_split($string ?? '');
    }

    // Photo
    $student_photo = "";
    $photo_query = $conn->query("SELECT Location FROM Student_Documents WHERE Student_ID = $id AND Type = 'Photo'");
    if ($photo_query->num_rows > 0) {
        $photo_row = mysqli_fetch_assoc($photo_query);
        $photo = "../.." . $photo_row['Location'];
        if (file_exists($photo)) {
            $student_photo = base64_encode(file_get_contents($photo));
            $i = 0;
            $end = 3;
            while ($i < $end) {
                $data1 = base64_decode($student_photo);
                $filename1 = $id . "_Photo" . $file_extensions[$i];
                file_put_contents($path . $filename1, $data1);
                $i++;
            }
        }
    } else {
        $photo = "";
    }

    // Signature
    $student_signature = "";
    $signature_query = $conn->query("SELECT Location FROM Student_Documents WHERE Student_ID = $id AND Type = 'Student Signature'");
    if ($signature_query->num_rows > 0) {
        $signature_row = mysqli_fetch_assoc($signature_query);
        $signature = "../.." . $signature_row['Location'];
        if (file_exists($signature)) {
            $student_signature = base64_encode(file_get_contents($signature));
            $i = 0;
            $end = 3;
            while ($i < $end) {
                $data2 = base64_decode($student_signature);
                $filename2 = $id . "_Signature" . $file_extensions[$i];
                file_put_contents($path . $filename2, $data2);
                $i++;
            }
        }
    } else {
        $signature = "";
    }

    // Page 1
    $pageId = $pdf->importPage(1, PdfReader\PageBoundaries::MEDIA_BOX);
    $pdf->addPage();
    $pdf->useImportedPage($pageId, 0, 0, 210);

    // Photo
    if (!empty($photo) && filetype($photo) === 'file' && file_exists($photo)) {
        try {
            $filename = $id . "_Photo" . $file_extensions[0];
            $image = $path . $filename;
            $pdf->Image($image, 167, 70.5, 32, 9);
            $photo = $image;
        } catch (Exception $e) {
            try {
                $filename = $id . "_Photo" . $file_extensions[1];
                $image = $path . $filename;
                $pdf->Image($image, 165, 29.2, 33, 38.7);
                $photo = $image;
            } catch (Exception $e) {
                try {
                    $filename = $id . "_Photo" . $file_extensions[2];
                    $image = $path . $filename;
                    $pdf->Image($image, 165, 29.2, 33, 38.7);
                    $photo = $image;
                } catch (Exception $e) {
                    // Ignore or handle
                }
            }
        }
    }

    // Signature
    if (!empty($signature) && filetype($signature) === 'file' && file_exists($signature)) {
        try {
            $filename = $id . "_Signature" . $file_extensions[0];
            $image = $path . $filename;
            $pdf->Image($image, 167, 69, 32, 12);
            $student_signature = $image;
        } catch (Exception $e) {
            try {
                $filename = $id . "_Signature" . $file_extensions[1];
                $image = $path . $filename;
                $pdf->Image($image, 167, 69, 32, 12);
                $student_signature = $image;
            } catch (Exception $e) {
                try {
                    $filename = $id . "_Signature" . $file_extensions[2];
                    $image = $path . $filename;
                    $pdf->Image($image, 167, 69, 32, 12);
                    $student_signature = $image;
                } catch (Exception $e) {
                    // Ignore or handle
                }
            }
        }
    }

    $session = $student['Session'] ?? 'Jan-25';
    list($month, $year) = explode('-', $session);

    $x = 35.3;
    $y = 50.5;
    foreach (safe_str_split($student['Enrollment_No']) as $char) {
        $pdf->SetXY($x, $y);
        $pdf->Write(1, $char);
        $x += 3.9;
    }

    $pdf->SetXY(100, 61);
    $pdf->Write(1, $student['Course'] ?? '');
    
    $pdf->SetXY(38, 70);
    $pdf->Write(1, $student['Sub_Course'] ?? '');
    
    $pdf->SetXY(109, 34);
   if($year > 2000){
     $pdf->Write(1, strtoupper($month) . '-' . $year);
   }else{
     $pdf->Write(1, strtoupper($month) . '-' . '20' . $year);
   }
    
    $pdf->SetFont('Hondo', '', 10);

    $student_name = trim(($student['First_Name'] ?? '') . ' ' . ($student['Middle_Name'] ?? '') . ' ' . ($student['Last_Name'] ?? ''));
    
    $x = 37.3;
    $y = 97.5;
    foreach (safe_str_split($student_name) as $char) {
        $pdf->SetXY($x, $y);
        $pdf->Write(1, $char);
        $x += 3.9;
    }

    $x = 37.3;
    $y = 105.5;
    foreach (safe_str_split($student['Father_Name']) as $char) {
        $pdf->SetXY($x, $y);
        $pdf->Write(1, $char);
        $x += 3.9;
    }

    $x = 37.3;
    $y = 113.5;
    foreach (safe_str_split($student['Mother_Name']) as $char) {
        $pdf->SetXY($x, $y);
        $pdf->Write(1, $char);
        $x += 3.9;
    }

    if (!empty($student['Admission_Type_ID'])) {
        $admissionType_ID = $student['Admission_Type_ID'];
        $lateralquery = $conn->query("SELECT * FROM Admission_Types WHERE ID = $admissionType_ID");
        $lateryName = '';
        if ($lateralquery && $row = $lateralquery->fetch_assoc()) {
            $lateryName = $row['Name'];
        }
            
        if ($lateryName == 'Fresh') {
            $pdf->Image($check, 59, 77.3, 3, 3);
        } else {
            $pdf->Image($check, 45.8, 77.3, 3, 3);
        }    
    }

    if (($student['Gender'] ?? '') == 'Male') {
        $pdf->Image($check, 44, 120.9, 3, 3);
    } else if (($student['Gender'] ?? '') == 'Female') {
        $pdf->Image($check, 68.8, 120.9, 3, 3);
    }

    // Category
    $category = $student['Category'] ?? '';
    if ($category == 'SC') {
        $pdf->Image($check, 56.5, 202, 3, 3);
    } else if ($category == 'ST') {
        $pdf->Image($check, 69, 202, 3, 3);
    } else if ($category == 'OBC') {
        $pdf->Image($check, 83.5, 202, 3, 3);
    } else if ($category == 'General') {
        $pdf->Image($check, 43, 202, 3, 3);
    } else {
        $pdf->Image($check, 194, 202, 3, 3);
    }

    $nationality = $student['Nationality'] ?? '';
    if ($nationality == 'Indian') {
        $pdf->Image($check, 48.5, 194, 3, 3);
    } else if ($nationality == 'NRI') {
        $pdf->Image($check, 67, 194, 3, 3);
    }

    $employment_status = $student['Employement_Status'] ?? '';
    if ($employment_status == 'Employed') {
        $pdf->Image($check, 148.5, 202, 3, 3);
    } else if ($employment_status == 'Unemployed') {
        $pdf->Image($check, 174.5, 202, 3, 3);
    }

    $dob = !empty($student['DOB']) ? date("dmY", strtotime($student['DOB'])) : '01012000';
    $dobChars = str_split($dob);
    $xPositions = [121.2, 125.2, 132.5, 136.5, 144.5, 148.5, 152.5, 156.5];
    $y = 122.3;
    foreach ($dobChars as $index => $char) {
        $pdf->SetXY($xPositions[$index], $y);
        $pdf->Write(1, $char);
    }

    $presentAddress = strtoupper($address['present_address'] ?? '');
    $addressChars = safe_str_split($presentAddress);

    $lines = [
        ['startX' => 32,    'startY' => 136, 'maxChars' => 17, 'lineNum' => 1],
        ['startX' => 12.5,  'startY' => 143, 'maxChars' => 22, 'lineNum' => 2],  
        ['startX' => 12.5,  'startY' => 150, 'maxChars' => 22, 'lineNum' => 3],  
        ['startX' => 12.5,  'startY' => 157, 'maxChars' => 10, 'lineNum' => 4]
    ];

    $boxWidth = 3.9;
    $charIndex = 0;

    foreach ($lines as $line) {
        $x = $line['startX'];
        $y = $line['startY'];
        $maxChars = $line['maxChars'];
        
        for ($i = 0; $i < $maxChars && $charIndex < count($addressChars); $i++) {
            $pdf->SetXY($x, $y);
            $pdf->Write(1, $addressChars[$charIndex]);
            $x += $boxWidth;
            $charIndex++;
        }
        
        if ($charIndex >= count($addressChars)) {
            break;
        }
    }

    $x = 67.5;
    $y = 156.5;
    foreach (safe_str_split($address['present_pincode'] ?? '') as $char) {
        $pdf->SetXY($x, $y);
        $pdf->Write(1, $char);
        $x += 3.8;
    }

    $pdf->SetXY(18.5, 162);
    $pdf->Write(1, $address['present_city'] ?? '');
    
    $pdf->SetXY(61.5, 162);
    $pdf->Write(1, $address['present_state'] ?? '');
    
    $pdf->SetXY(61.5, 172);
    $pdf->Write(1, $student['Contact'] ?? '');
    
    $pdf->SetXY(22.5, 178);
    $pdf->Write(1, $student['Email'] ?? '');

    $academics = array('High School', 'Intermediate', 'Under Graduation', 'Post Graduation', 'Other');
    $pdf->SetFont('Arial', '', 6);

    $yPositions = array(
        'High School' => 240.7,
        'Intermediate' => 248.7,
        'Under Graduation' => 256.7,
        'Post Graduation' => 264.7,
        'Other' => 272.7
    );

    foreach ($academics as $academic) {
        $x = 22;
        $y = $yPositions[$academic];

        $type = ($academic === 'Under Graduation') ? 'UG' : (($academic === 'Post Graduation') ? 'PG' : $academic);

        $query = "SELECT `Board/Institute` AS board, Year, Subject, Total_Marks FROM Student_Academics WHERE Student_ID = $id AND Type = '$type'";
        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            $pdf->SetXY($x, $y);
            $pdf->Write(1, substr($academic, 0, 15));
            
            $x += 75;
            $pdf->SetXY($x, $y);
            $pdf->Write(1, $row['Year'] ?? '');
            
            $x += 27;
            $pdf->SetXY($x, $y);
            $pdf->Write(1, substr($row['Total_Marks'] ?? '', 0, 4));
            
            $x += 17.5;
            $pdf->SetXY($x, $y);
            $boardText = $row['board'] ?? '';
            $pdf->Write(1, strlen($boardText) > 20 ? substr($boardText, 0, 18) . '...' : $boardText);
        }
    }

    $pdf->Output('I', 'Application mdu.pdf');
}
?>