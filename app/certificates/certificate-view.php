<?php 
ini_set('display_errors', 1); 
require '../../includes/db-config.php'; 
session_start(); 

$id = intval($_POST['student_id']); 
// $id='226';
$background_type = $_POST['background_type'] ?? 'with_background';

// Check if certificate already exists for this student 
$check_existing = $conn->query("SELECT * FROM certificates WHERE student_id = '" . $id . "'"); 
$certificate_exists = $check_existing->num_rows > 0;

if ($certificate_exists) { 
    $existing_cert = $check_existing->fetch_assoc();
    $old_file_path = '../../' . $existing_cert['file_path'];
    if (file_exists($old_file_path)) {
        unlink($old_file_path);
    }
} 

// Get student data
$students_temps_result = $conn->query("SELECT Students.*,Sub_Courses.Min_Duration as total_duration,Modes.Name as mode,Sub_Courses.Name as course,Courses.Name as program_Type FROM Students left join Sub_Courses on Sub_Courses.ID=Students.Sub_Course_ID left join Modes on Students.University_ID=Modes.University_ID left join Courses on Students.Course_ID=Courses.ID WHERE Students.ID = '" . $id . "' "); 
if ($students_temps_result->num_rows > 0) { 
    $students_temps = $students_temps_result->fetch_assoc(); 
} else { 
    echo json_encode(['status' => 400, 'message' => 'Student not found!']);
    exit; 
}

// Process data
$name = $students_temps['First_Name'] . " " . $students_temps['Middle_Name'] . " " . $students_temps['Last_Name']; 
$Enrol_no = $students_temps['Enrollment_No']; 

// Format name
$name = array_filter(explode(" ", $name)); 
$formattedName = ""; 
foreach ($name as $n) { 
    if (!empty($formattedName)) { 
        $formattedName .= ' '; 
    } 
    $firstLetter = substr($n, 0, 1); 
    $restLetters = substr($n, 1); 
    $formattedName .= strtoupper($firstLetter) . strtolower($restLetters); 
} 

// Extract course name and duration 
$courseFull = $students_temps['course']; 
preg_match('/\((.*?)\)/', $courseFull, $matches); 
$durationValue = isset($matches[1]) ? $matches[1] : ''; 
$courseName = trim(preg_replace('/\s*\(.*?\)\s*/', '', $courseFull)); 

use setasign\Fpdi\Fpdi; 
ob_end_clean(); 
require_once('../../extras/TCPDF/tcpdf.php'); 
require_once('../../extras/vendor/setasign/fpdf/fpdf.php'); 
require_once('../../extras/vendor/setasign/fpdi/src/autoload.php'); 

// Create new FPDI instance 
$pdf = new FPDI('L', 'mm', array(299, 210)); 

if ($background_type == 'with_background') { 
    $templateFile = '../../assets/img/certificate-new-blank.pdf'; 
    
    if (!file_exists($templateFile)) { 
        echo json_encode(['status' => 400, 'message' => 'Certificate template not found!']);
        exit; 
    } 
    
    // 🔹 Set portrait mode
    $pdf->AddPage('P');  // 'P' = Portrait, 'L' = Landscape
    
    // 🔹 Load template
    $pageCount = $pdf->setSourceFile($templateFile); 
    $tplIdx = $pdf->importPage(1); 

    // 🔹 Use template (portrait dimensions A4 = 210 × 297 mm)
    $pdf->useTemplate($tplIdx, 0, 0, 210, 300); 

    // $pdf->SetTextColor(6, 64, 101);
    $pdf->SetTextColor(0, 0, 0);
    
} else { 
    $pdf->AddPage('P'); // Portrait mode
    $pdf->SetTextColor(0, 0, 0);
} 

// COMMON COORDINATES
$pdf->SetFont('Arial', 'B', 12); 
$pdf->SetXY(10, 135); 
// $pdf->Cell(0, 0, $formattedName . " bearing Enrollment No " . $Enrol_no, 0, 1, 'C'); 
$pdf->Cell(0, 0, $formattedName . " bearing Enrollment no. " .'('. $Enrol_no.')', 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 11); 
// $pdf->SetXY(123, 145); 
// $pdf->Cell(0, 0, substr(strtoupper($courseName), 0, 67), 0, 1, 'L');
$courseText = strtoupper(trim($courseName));
$maxWidth = 80; // width for first line
$x = 98;
$y = 150.8;
$lineHeight = 14.5;

// Split words
$words = explode(' ', $courseText);
$line = '';
$lines = [];
$lineCount = 0;
// print_r($courseText);die;
foreach ($words as $word) {
    $testLine = $line . $word . ' ';
    $lineWidth = $pdf->GetStringWidth($testLine);
    
    if ($lineWidth > $maxWidth && $line !== '') {
        $lines[] = trim($line);
        $line = $word . ' ';
        $lineCount++;
        
        // Stop wrapping after first line
        if ($lineCount == 1) {
            break;
        }
    } else {
        $line = $testLine;
    }
  
}
//   print_r(explode(' ', implode(' ', $lines)));die;
// Remaining text goes fully into the second line
if(count($lines)>0){
    $remainingWords = array_slice($words, count(explode(' ', implode(' ', $lines))));
    $lines[] = trim(implode(' ', $remainingWords));    
}else{
    $lines[] = $line;
}

// print_r($lines);die;
// Print first line (centered)
if (!empty($lines[0])) {
    $pdf->SetXY($x, $y);
    $pdf->Cell($maxWidth, 0, $lines[0], 0, 1, 'L');
}

// Print second line (left aligned and wider)
if (!empty($lines[1])) {
    $y += $lineHeight;
    $pdf->SetXY($x - 77, $y); // adjust X for better alignment
    $pdf->Cell(120, 0, $lines[1], 0, 1, 'L');
}


$pdf->SetXY(44, 181.3); 
$pdf->Cell(0, 0, 'AY 2024 - 25', 0, 1, 'C'); 

$pdf->SetXY(73, 198); 
$pdf->Cell(0, 0, $durationValue, 0, 1, 'L'); 

// $pdf->SetXY(40, 180); 
// $pdf->Cell(0, 0, "02-05-2024", 0, 1, 'L'); 

// Save file 
$filename = $students_temps['Unique_ID'] . "_" . time() . ".pdf"; 
$file_path_save = '../../uploads/certificates/' . $filename; 

// Create directory if it doesn't exist
if (!is_dir('../../uploads/certificates/')) {
    mkdir('../../uploads/certificates/', 0777, true);
}

// Save PDF file
$pdf->Output($file_path_save, "F"); 
// $pdf->Output('certificate.pdf', "I"); 
// // Database operations
$student_id = $id; 
$enrollment_no = $students_temps['Enrollment_No']; 
$file_path = "uploads/certificates/" . $filename; 
$file_type = "pdf"; 
$status = 1; 
$created_by = $_SESSION['ID']; 
$updated_at = date("Y-m-d H:i:s");

if ($certificate_exists) {
    $update = $conn->query("UPDATE certificates SET 
                            file_path = '" . $file_path . "', 
                            file_type = '" . $file_type . "',
                            updated_at = '" . $updated_at . "',
                            created_by = '" . $created_by . "'
                            WHERE student_id = '" . $student_id . "'");
    
    if ($update) { 
        echo json_encode([
            'status' => 200, 
            'message' => "Certificate updated successfully!",
            'file_url' => '../' . $file_path,
            'file_name' => $filename
        ]);
    } else { 
        echo json_encode(['status' => 400, 'message' => 'Failed to update certificate!']);
    }
} else {
    $created_at = date("Y-m-d H:i:s");
    $add = $conn->query("INSERT INTO certificates(student_id, enrollment_no, file_path, file_type, status, created_by, created_at) VALUES ('" . $student_id . "', '" . $enrollment_no . "', '" . $file_path . "', '" . $file_type . "','" . $status . "', '" . $created_by . "', '" . $created_at . "' ) "); 

    if ($add) { 
        echo json_encode([
            'status' => 200, 
            'message' => "Certificate generated successfully!",
            'file_url' => '../' . $file_path,
            'file_name' => $filename
        ]);
    } else { 
        echo json_encode(['status' => 400, 'message' => 'Failed to create certificate!']);
    }
}