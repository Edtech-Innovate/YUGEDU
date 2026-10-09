<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
require '../../includes/db-config.php';
require '../../includes/helpers.php';
session_start();

$url = "https://erpglocal.iitseducation.org";
$passFail = "PASS";

use setasign\Fpdi\PdfReader;
use setasign\Fpdi\Fpdi;

ob_end_clean();
require_once('../../extras/TCPDF/tcpdf.php');
require_once('../../extras/vendor/setasign/fpdf/fpdf.php');
require_once('../../extras/vendor/setasign/fpdi/src/autoload.php');
require '../../extras/vendor/autoload.php';
require('../../extras/vendor/shuchkin/simplexlsxgen/src/SimpleXLSXGen.php');
require('../../extras/vendor/nuovo/spreadsheet-reader/SpreadsheetReader.php');

// echo "<pre>"; print_r($_REQUEST); exit;
$check = '../../assets/img/forms/checked.png';
$sqlQuery = '';
if (isset($_POST['course_type_id']) && !empty($_POST['course_type_id'])) {
    $course_id = $_POST['course_type_id'];
    $sqlQuery .= "AND Students.Course_ID = '$course_id'";
}

if (isset($_POST['course_id']) && !empty($_POST['course_id'])) {
    $sub_course_id = $_POST['course_id'];
    $sqlQuery .= " AND Students.Sub_Course_ID = '$sub_course_id'";
}

if (isset($_POST['student_id']) && !empty($_POST['student_id'])) {
    $student_id_array = explode(",", $_POST['student_id']);
    foreach ($student_id_array as &$en_no) {
        $en_no = "'" . trim($en_no) . "'";
    }
    unset($en_no);
    $student_id = implode(",", $student_id_array);
    $sqlQuery .= " AND Students.Enrollment_No IN ($student_id)";
}

if (isset($_POST['category']) && !empty($_POST['category'])) {
    $sub_course_id = $_POST['category'];
    $sqlQuery .= " AND Students.Duration = '1'";
}


$pdf_dir = '../../uploads/marksheet/';
$export_data = [];
if ($_SESSION['university_id'] == 41) {
    $header = array('Enrollment_No', 'Course','Sub-Course','Duration','Remark');
} else {
    $header = array('Enrollment_No', 'Course','Sub-Course','Semester','Remark');
}
$export_data[] = $header;
$student = $conn->query("SELECT Students.*, Sub_Courses.Min_Duration as total_duration,Sub_Courses.Name as course, Courses.Name as program_Type FROM Students LEFT JOIN Sub_Courses ON Sub_Courses.ID = Students.Sub_Course_ID LEFT JOIN Courses ON Students.Course_ID = Courses.ID  WHERE Students.Enrollment_No IS NOT NULL $sqlQuery");

if ($student->num_rows > 0) {
    while ($row = $student->fetch_assoc()) {
        // echo "<pre>"; print_r($row);
        $students_result = $conn->query("SELECT Students.*, Sub_Courses.Min_Duration as total_duration, Modes.Name as mode, Sub_Courses.Name as course, Courses.Name as program_Type, Admission_Sessions.Name as Admission_Session,Admission_Sessions.Exam_Session, Admission_Types.Name as Admission_Type FROM Students LEFT JOIN Sub_Courses ON Sub_Courses.ID = Students.Sub_Course_ID LEFT JOIN Modes ON Students.University_ID = Modes.University_ID LEFT JOIN Courses ON Students.Course_ID = Courses.ID  LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID = Admission_Sessions.ID LEFT JOIN Admission_Types ON Students.Admission_Type_ID = Admission_Types.ID WHERE Students.Enrollment_No = '" . trim($row['Enrollment_No']) . "'");
        $data = [];
        // $data['remarks'] = "Pass";
        $data = $students_result->fetch_assoc();
        // echo "<pre>"; print_r($data);
        $typoArr = ["th", "st", "nd", "rd", "th", "th", "th", "th", "th"];
        $total_obt = 0;
        $total_max = 0;
        $durations_query = "";
        $min_val = 0;
        $temp_subjects = "";
        $sem_sql = '';
        $scheme_id = NULL;
        $semester = NULL;
        $due_val ='';
        list($month, $year) = explode('-', $data['Admission_Session']);
        $year = (strlen($year) > 2) ? date('y', strtotime("$year-01-01")) : $year;
        $adm_session = '20' . $year . '-' .'20'. $year+1;
        //  echo('<pre>');print_r($adm_session);die;
        if (isset($_POST['semester']) && !empty($_POST['semester'])) {
            list($scheme_id, $semester) = explode('|', $_POST['semester']);
            $data['Duration'] = $semester;
            $sem_sql = " AND Syllabi.Semester = '$semester' AND Syllabi.Scheme_ID = $scheme_id";
            $durations_query = " AND Syllabi.Semester = " . $semester;
        } else if ($data['University_ID'] == 20) {
            $sem_sql = " AND Semester = " . $data['Duration'];
            $durations_query = " AND Syllabi.Semester = " . $data['Duration'];
        }else if ($data['University_ID'] == 41) {
          $category = strtolower($data['Course_Category']);
          $duration = $data['Duration'];
          if ($category == 'certified' && ($duration == 6 || $duration == 11)) {
              $due_val = $duration . '/certified';
          } elseif ($category == 'certification') {
              $due_val = $duration . '/certification';
          } elseif ($category == 'advance_diploma' || $duration == '11/advance-diploma') {
              $due_val = '11/advanced';
          } elseif ($category == 'post_graduate') {
              $due_val = '24/post-graduate';
          }else{
            $due_val = $duration;
          }
           $sem_sql = " AND Semester = '" . $due_val . "'";
          $durations_query = " AND Syllabi.Semester = '" . $due_val . "'";
        }
    
 
        // $exam_date = $conn->query("SELECT m.exam_month,m.exam_year FROM marksheets AS m LEFT JOIN Syllabi  ON m.subject_id = Syllabi.ID WHERE m.enrollment_no = '" . $data['Enrollment_No'] . "' AND Syllabi.Course_ID = " . $data['Course_ID'] . "  AND  Syllabi.Sub_Course_ID = " . $data['Sub_Course_ID'] . " $sem_sql GROUP BY m.enrollment_no");
        // if ($exam_date->num_rows > 0) {
        //     $examArr = $exam_date->fetch_assoc();
        //     if (!empty($examArr['exam_month']) || !empty($examArr['exam_year'])) {
        //         $exam_month = ucwords($examArr['exam_month']) . '-' . $examArr['exam_year'];
        //         list($date_of_issue) = selectExamSessionAndDateOfIssue($data['Admission_Session'],$semester,'date');
        //     } else {
                
        //         //$exam_month = ucwords($data['Exam_Session']);
        //     }
        // }
        list($date_of_issue,$exam_month) = selectExamSessionAndDateOfIssue($data['Admission_Session'],$semester,'date&exam');
        $exam_month = "April-2025";
        // echo "<pre>";
        // print_r(selectExamSessionAndDateOfIssue($data['Admission_Session'],$semester,'date&exam')); die;
        
        $temp_subjects = $conn->query("SELECT Paper_Type,marksheets.exam_month,marksheets.exam_year, marksheets.obt_marks_ext, marksheets.obt_marks_int,marksheets.obt_marks,marksheets.status,marksheets.remarks,marksheets.Created_At,Syllabi.Code,Syllabi.Name as subject_name,Syllabi.Min_Marks, Syllabi.Max_Marks, Syllabi.Credit FROM marksheets LEFT JOIN Syllabi ON marksheets.subject_id = Syllabi.ID WHERE enrollment_no = '" . $data['Enrollment_No'] . "' AND Sub_Course_ID = '" . $row['Sub_Course_ID'] . "'  $sem_sql ORDER BY Syllabi.Code ASC");
        $data['marks'] = array();
        $temp_subject = [];
        $total_obt = 0;
        $total_max = 0;
        $resultPublishDay = "";
        // $o_int_marks = [];
        // IF here the data is came that means this sem result is came if not then this sem result not present
        if ($temp_subjects->num_rows > 0) {
            
            while ($temp_subject = $temp_subjects->fetch_assoc()) {

                if ($temp_subject != null) {
                    $resultPublishDay = date("d/m/Y", strtotime($temp_subject['Created_At']));
                    
                    $temp_subject['not_empty_obt_ext_marks'] = "";
                    if (!empty($temp_subject['obt_marks_ext'])) {
                        $temp_subject['not_empty_obt_ext_marks'] = $temp_subject['obt_marks_ext'];
                    }
                    
                    $obt_marks_ext = isset($temp_subject['obt_marks_ext']) ? $temp_subject['obt_marks_ext'] : 0;
                    $obt_marks_int = isset($temp_subject['obt_marks_int']) ? $temp_subject['obt_marks_int'] : 0;

                    $obt_marks_ext = ($temp_subject['obt_marks_ext'] == 'AB') ? 'AB' : $temp_subject['obt_marks_ext'];
                    $obt_marks_int = ($temp_subject['obt_marks_int'] == 'AB') ? 'AB' : $temp_subject['obt_marks_int'];

                    if ($obt_marks_ext != 'AB' && $obt_marks_int != 'AB') {
                        $total_obt = $total_obt + intval($obt_marks_ext) + intval($obt_marks_int);
                    } else {
                        $total_obt = (int) $total_obt + (int) $obt_marks_ext + (int) $obt_marks_int;
                    }
                    $temp_subject['remarks_status'] = "Pass";

                    if ($data['University_ID'] == 41) {
                        if ($total_obt < $min_val || $temp_subject['obt_marks_ext'] == 0 || $temp_subject['obt_marks_ext'] == 'AB') {
                            $temp_subject['remarks_status'] = "FAIL";
                        }
                        $total_max = $total_max + $temp_subject['Max_Marks'];

                        if ($temp_subject['obt_marks_ext'] == 'AB' && $temp_subject['obt_marks_ext'] == 'AB') {
                            $temp_subject['obt_marks'] = 'AB';

                        } else if ($temp_subject['obt_marks_ext'] == 'AB' && $temp_subject['obt_marks_int'] != 'AB') {
                            $obtintmarks = isset($temp_subject['obt_marks_int']) ? intval($temp_subject['obt_marks_int']) : 0;
                            $temp_subject['obt_marks'] = $obtintmarks + 0;

                        }  else if ($temp_subject['obt_marks_ext'] != 'AB' && $temp_subject['obt_marks_ext'] != 'AB') {
                            $temp_subject['obt_marks'] = intval($temp_subject['obt_marks_ext']) + intval($temp_subject['obt_marks_int']);
                        }

                    } else {
                        if ($total_obt <= $min_val || $temp_subject['obt_marks_ext'] == 0 || $temp_subject['obt_marks_ext'] == 'AB') {
                            $temp_subject['remarks_status'] = "FAIL";
                        }
                        $total_max = $total_max + $temp_subject['Max_Marks'];

                        if ($temp_subject['obt_marks_ext'] == 'AB' && $temp_subject['obt_marks_ext'] == 'AB') {
                            $temp_subject['obt_marks'] = 'AB';

                        } else if ($temp_subject['obt_marks_ext'] == 'AB' && $temp_subject['obt_marks_int'] != 'AB') {
                            $obtintmarks = isset($temp_subject['obt_marks_int']) ? intval($temp_subject['obt_marks_int']) : 0;
                            $temp_subject['obt_marks'] = $obtintmarks + 0;

                        } else if ($temp_subject['obt_marks_int'] == 'AB' && $temp_subject['obt_marks_ext'] != 'AB') {
                            $obtextmarks = isset($temp_subject['obt_marks_ext']) ? intval($temp_subject['obt_marks_ext']) : 0;
                            $temp_subject['obt_marks'] = $obtextmarks + 0;
                        } else if ($temp_subject['obt_marks_ext'] != 'AB' && $temp_subject['obt_marks_ext'] != 'AB') {
                            $temp_subject['obt_marks'] = intval($temp_subject['obt_marks_ext']) + intval($temp_subject['obt_marks_int']);
                        }
                    }
                    // end remarks_status

                    if ($temp_subject['Min_Marks'] == 40 && $temp_subject['Max_Marks'] == 60) {
                        $min_val = ($temp_subject['Min_Marks'] + $temp_subject['Max_Marks']) * 40 / 100;
                    } else if ($temp_subject['Max_Marks'] == 100 && $temp_subject['Min_Marks'] == 40) {
                        $min_val = $temp_subject['Min_Marks'];
                    } else if ($temp_subject['Max_Marks'] == 100 && $temp_subject['Min_Marks'] == 100) {
                        $min_val = ($temp_subject['Max_Marks']) * 40 / 100;
                    }
                    if ($temp_subject['Paper_Type'] == 'Practical' && $temp_subject['obt_marks_int'] == 0 && $temp_subject['Min_Marks'] == 0) {
                        $temp_subject['Min_Marks'] = '-';
                        $temp_subject['obt_marks_int'] = "-";
                        $temp_subject['remarks_status'] = "Pass";
                    }

                    $temp_subject['remarks'] = $temp_subject['obt_marks_ext']<$temp_subject['Min_Marks']?"Fail":"Pass";
                    $data['marks'][] = $temp_subject;
                    $data['allremarks'][] = $temp_subject['remarks_status'];
                    $obt_int[] = $temp_subject['obt_marks_int'];
                    $not_empty_ext[] = $temp_subject['not_empty_obt_ext_marks'];
                }
            }

            $percentage = '';
            if (in_array('FAIL', $data['allremarks'])) {
                $data['remarks'] = 'Fail';
                $percentage = '';
            } else {
                $data['remarks'] = 'Pass';
                if ($total_max !== 0) {
                    $percentage = ($total_obt / $total_max) * 100;
                    $percentage = number_format($percentage, 2) . "%";
                }
            }
            // print_r( $data['remarks']);die;
            $total_subject = totalUloadedSubjectsFunc($conn, $data['University_ID'], $data['ID'], $data['Duration'], "");
            $total_subject_count = count($total_subject); 
            
            if (($total_subject_count !== count($obt_int) || $total_subject_count !== count(array_filter($not_empty_ext))) && $data['University_ID'] == 20) {
                //   echo "<pre>"; print_r($data);
                  makeStatus($data,"Result Not Found");
            }else{
                makeStatus($data,"Result Found"); 
            
            }
            

            $data['total_max'] = $total_max;
            $data['total_obt'] = $total_obt;

            $marksWords = ucwords(strtolower(numberToWordFunc($total_obt)));
            $count = $temp_subjects->num_rows;
            $hours = '';
            $total_duration = '';
        
            if ($data['University_ID'] == 41) {
                $data['mode_type'] = "Duration";
                $data['university_name'] = "Arni School of Vocational And Skill";
                $total_duration = $data['Duration'];
                $data['Durations'] = $data['Duration'];
                
         $fullCourse = trim($data['course']); 
// Example: "Advanced Certified Skill Diploma in Psychosexual Counselling (11 Months)"

// Default values
$course_name = $fullCourse;
$skillduration = '';
$duration_text = '';

// 1️⃣ Extract course name and duration text (if parentheses exist)
if (preg_match('/^(.*?)\s*\((.*?)\)$/', $fullCourse, $matches)) {
    $course_name = trim($matches[1]);
    $skilldurtion = trim($matches[2]); // e.g. "11 Months"
}

// // 2️ Determine skill duration based on course name keywords
// if (stripos($course_name, '11') !== false && stripos($course_name, 'adv') !== false) {
//     $skillduration = '2'; // 11 + Adv
// } elseif (stripos($course_name, '11') !== false) {
//     $skillduration = '1'; // only 11
// } elseif (stripos($course_name, '6') !== false) {
//     $skillduration = '6';
// } elseif (stripos($course_name, '3') !== false) {
//     $skillduration = '3';
// } else {
//     // fallback if nothing matched
//     $skillduration = $duration_text;
// }

// // 3️⃣ Map skill duration to program details
// if ($skillduration == "3") {
//     $durations = "Certification Course";
//     $hours = 160;
//     $data['skillDurations'] = "3 Months";
// } elseif ($skillduration == "6") {
//     $durations = "Certified Skill Diploma";
//     $hours = 320;
//     $data['skillDurations'] = "6 Months";
// } elseif ($skillduration == "2") {
//     $durations = "Advanced Certification Skill Diploma";
//     $hours = 960;
//     $data['skillDurations'] = "11 Months";
// } elseif ($skillduration == "1") {
//     $durations = "Certified Skill Diploma";
//     $hours = 960;
//     $data['skillDurations'] = "11 Months";
// } else {
//     // fallback if duration doesn't match any known pattern
//     $durations = "Unknown Duration";
//     $hours = 'NA';
//     $data['skillDurations'] = $duration_text ?: 'NA';
// }
            //   echo('<pre>');print_r($data['skillDurations']);die;
            } else {
                $data['university_name'] = "Maya Devi School of Vocational And Skill";
                $data['mode_type'] = "Semester";
                $durations = "";
            }

            $data['duration_val'] = $durations;

            $durMonthYear = "";
            if ($data['mode'] == "Monthly") {
                $durMonthYear = " Months";
            } elseif ($data['mode'] == "Sem") {
                $durMonthYear = " Semester";
            } else {
                $durMonthYear = " Years";
            }

            // if ($data['University_ID'] == 41) {
            //     $data['durMonthYear'] = $data['Durations'] . $durMonthYear . '/ ' . $hours . "Hours";
            // } else {
            //     $data['durMonthYear'] = $data['Duration'] . $typoArr[$data['Duration']];
            // }

            $student_doc_query = "SELECT Location FROM Student_Documents WHERE Student_ID = '" . $data['ID'] . "' AND Type = 'Photo'";
            $student_doc = $conn->query($student_doc_query);
            $student_doc = $student_doc->fetch_assoc();
            $photo = $student_doc['Location'];
            $data['Photo'] = $url . $photo;

            // $pdf = new Fpdi();
            // $pdf->addPage();
            
            $pdf = new Fpdi();
            $templateFile = 'mduMarksheet.pdf';
            $pageCount = $pdf->setSourceFile($templateFile);
            $tplIdx = $pdf->importPage(1);
            $pdf->addPage('L');
            // $pdf->useTemplate($tplIdx, 0, 0, 210, 297); 
                $pdf->useTemplate($tplIdx, 0, 0, 297, 210);
            $fullCourse = $data['course']; // "Hospital Administration (11 Months)"
//   echo('<pre>');print_r($data['skillDurations']);die;
            if ($data['University_ID'] == 41) {
                $pdf->SetFont("times", 'B', 9);
                // $pdf->SetXY(15, 45);
                // $pdf->Cell(0, 0, 'Statement of Marks', 0, 0, 'C', 0);
                $pdf->SetXY(48, 43);
                 $pdf->SetFont("times", 'B', 9.5);
                $pdf->Cell(0, 0,  $course_name, 0, 0, 'C', 0);
                 $pdf->SetFont("times", 'B', 9);
                $pdf->SetXY(48, 47);
                $pdf->Cell(0, 0, 'SESSION : ' . $adm_session . '', 0, 0, 'C', 0);
                $pdf->SetXY(16.1, 50);
                $pdf->Cell(196, 5, strtoupper('Name : ') . strtoupper($data['First_Name']) . ' ' . strtoupper($data['Middle_Name']) . ' ' . strtoupper($data['Last_Name']), 0, 0, 'L', 0);
                $pdf->SetXY(212, 50);
                $pdf->Cell(70, 5, strtoupper('Enrollment No : ') . $data['Enrollment_No'], 0, 0, 'L', 0);
                $pdf->SetXY(16.1, 55);
                $pdf->Cell(196, 5, strtoupper('School : ') . ucwords($data['university_name']), 0, 0, 'L', 0);
                $pdf->SetXY(212, 55);
                $pdf->Cell(70, 5, strtoupper($data['mode_type'] . ' ' . ':' . ' ' . $skilldurtion), 0, 0, 'L', 0);

                // skill
                $pdf->SetFont('times', 'B', 9);
                $cellWidth = 20;
                $cellHeight = 10;
                $pdf->SetXY(16.1, 62.5);
                $pdf->MultiCell(27, 10, 'Subject Code', 'TLB', 'C');
                $pdf->SetXY(43, 62.5);
                $pdf->MultiCell(169.3, 10, 'Subject Name ', 'TLB', 'C');
                $pdf->SetXY(212.3, 62.5);
                $pdf->MultiCell(17.3, 5, 'Obtained Marks', 'TLB', 'C');
                $pdf->SetXY(230, 62.5);
                $pdf->MultiCell(15, 5, 'Min. Marks', 'TLB', 'C');
                $pdf->SetXY(245, 62.5);
                $pdf->MultiCell(14.8, 5, 'Max. Marks', 'TLB', 'C');
                $pdf->SetXY(260, 62.5);
                $pdf->MultiCell(22, 10, 'Remarks', 1, 'C');
                $pdf->SetXY(10, 62.5);
                $pdf->Ln();
                $pdf->SetFont('times', '', 9);
                $x_cor = 16;
                $pdf->SetX($x_cor);
                $remark_status = [];
                $total_get_marks = 0;
                // echo "<pre>";
                // print_r($data['marks']);
                // exit;
                foreach ($data['marks'] as $mark) {
                    $pdf->SetX($x_cor);
                    if (strlen(trim($mark['subject_name'])) > 30) {
                        $cellHeight = 20;
                    } else {
                        $cellHeight = 10;
                    }


                    $mark['subject_name'] = str_replace("\xC2\xA0", ' ', $mark['subject_name']);
                    $mark['subject_name'] = preg_replace('/\s+/', ' ', $mark['subject_name']);
                    $mark['subject_name'] = strtoupper(utf8_encode($mark['subject_name']));
                    // ECHO strlen($mark['subject_name'])."<br>".$mark['subject_name']."<br>";
                    // $mark['subject_name']='COMPUTERIZED HOSPITAL MANAGEMENT COMPUTERIZED HOSPITAL MANAGEMENT COMPUTERIZED HOSPITAL MANAGEMENT';
                    if (strlen($mark['subject_name']) > 80) {
                        $pdf->Cell(27, 8, $mark['Code'], 'LR', 0, 'L');
                        $nameParts = explode("\n", wordwrap(trim($mark['subject_name']), 80));
                      
                        $pdf->MultiCell(169.3, 3.5, $nameParts[0] . chr(10) . $nameParts[1], 'R', 0, 0, 'L');
                        $x = $pdf->GetX();
                        $y = $pdf->GetY();
                        $pdf->SetXY($x + 202.3, $y - 8);
                        $pdf->Cell($cellWidth-2.3, 8, $mark['obt_marks'], 'R', 0, 'C');
                        $pdf->Cell($cellWidth-5, 8, $mark['Min_Marks'], 'R', 0, 'C');
                        $pdf->Cell($cellWidth-5,8, $mark['Max_Marks'], 'R', 0, 'C');
                        $pdf->Cell(22, 8, $mark['remarks'], 'R', 0, 'C');
                    }else {
                        // echo "<pre>";
                        // echo $mark['subject_name'];
                        $pdf->Cell(27, 8, $mark['Code'], 'LR', 0, 'L');
                        $pdf->Cell(169.3, 8, trim($mark['subject_name']), 'R', 0, 'L');
                        
                        $pdf->Cell($cellWidth-2.3, 8, $mark['obt_marks'], 'R', 0, 'C');
                        $pdf->Cell($cellWidth-5,8, $mark['Min_Marks'], 'R', 0, 'C');
                        $pdf->Cell($cellWidth-5, 8, $mark['Max_Marks'], 'R', 0, 'C');
                        $pdf->Cell(22, 8, $mark['remarks'], 'R', 0, 'C');
                    }
                    $pdf->Ln();
                }
                $pdf->SetX($x_cor);
                      $pdf->Cell(27, 8, 'HA-103', 'LB', 0, 'L');
$pdf->Cell(169.3, 8, 'COMPUTERIZED HOSPITAL MANAGEMENT', 'LB', 0, 'L');
$pdf->Cell($cellWidth - 2.3, 8, '90', 'LB', 0, 'C');
$pdf->Cell($cellWidth - 5, 8, '80', 'LB', 0, 'C');
$pdf->Cell($cellWidth - 5, 8, '100', 'LB', 0, 'C');
$pdf->Cell(22, 8, 'pass', 'LBR', 0, 'C');




$pdf->Ln();
$pdf->SetFont('Arial', 'B', 9);
                      $pdf->SetX($x_cor);
                      $pdf->Cell(27, 8, '','LTR', 0, 'L');
                      $pdf->Cell(169.3, 8, 'Total (if Passed)', 'TR', 0, 'L');
                      $pdf->Cell(32.6, 8, $data['total_obt'] , 'TR', 0, 'C');
                    //   $pdf->Cell($cellWidth-5, 8, '', 0, 0, 'C');
                      $pdf->Cell($cellWidth-5, 8,  $data['total_max'], 'TR', 0, 'C');
                      $pdf->Cell(22, 8, $data['remarks'], 'TR', 0, 'C');
                      $pdf->Ln();
                      $pdf->SetX($x_cor);
                      $pdf->Cell(27, 8, '','LR', 0, 'L');
                      $pdf->Cell(169.3, 8, 'Total (if Passed)', 'R', 0, 'L');
                      $pdf->Cell(32.6, 8, $data['total_obt'] , 'R', 0, 'C');
                    //   $pdf->Cell($cellWidth-5, 8, '', 0, 0, 'C');
                      $pdf->Cell($cellWidth-5, 8,  $data['total_max'], 'R', 0, 'C');
                      $pdf->Cell(22, 8, $data['remarks'], 'R', 0, 'C');
                       $pdf->Ln();
                      $pdf->SetX($x_cor);
                      $pdf->Cell(27, 8, '','LBR', 0, 'L');
                      $pdf->Cell(169.3, 8, 'Total (if Passed)', 'BR', 0, 'L');
                      $pdf->Cell(32.6, 8, $data['total_obt'] , 'BR', 0, 'C');
                    //   $pdf->Cell($cellWidth-5, 8, '', 0, 0, 'C');
                      $pdf->Cell($cellWidth-5, 8,  $data['total_max'], 'BR', 0, 'C');
                      $pdf->Cell(22, 8, $data['remarks'], 'BR', 0, 'C');
                      
                    //   $pdf->SetFont('Arial', '', 10);
                    //   if($data['remarks'] =='Pass'){
                    //       $pdf->Image($check, 15, 237, 3, 3);
                    //       $pdf->SetFont('times', 'B', 12);
                    //       $pdf->SetXY(40, 228.6);
                    //       $pdf->MultiCell(40, 10, $data['remarks'].' ('.$percentage.')', 0, 'C');
                    //   }
                    //   else{
                        //   $pdf->Image($check, 25, 237, 3, 3);}
                        //   echo('<pre>');print_r($data['marks']);
                        // Filter subjects where remarks_status = 'Fail'
// $failedSubjects = array_filter($data['marks'], function($mark) {
//     return isset($mark['remarks']) && strtolower(trim($mark['remarks'])) === 'fail';
// });

// $failedcode = '';

// if (!empty($failedSubjects)) {
//     // Combine all failed codes into a single comma-separated string
//     $failedCodes = array_column($failedSubjects, 'Code');
//     $failedcode = implode(', ', $failedCodes);
// }
// // echo('<pre>');print_r($failedcode);
//                           $pdf->SetXY(40, 228.6);
//                           $pdf->MultiCell(40, 10, '('.$failedcode.')', 0, 'C');
//                       }
                      
                     
                     
                      
                    //   echo('<pre>');print_r($data);
                // if ($count > 8) {
                //     // echo "<pre>";
                //     // print_r($remark_status);

                //     //more thwn 9 subject
                //     $pdf->SetXY(16, 200.4);
                //     $pdf->SetFont('times', 'B', 9);
                //     $pdf->Cell(0, 0, 'Aggregate Marks', 0, 0, 'C', 0);
                //     $pdf->SetXY(16, 235.4);
                //     $pdf->Cell(65, 8, 'Marks', 'TL', 1, 'C', 0);
                //     $pdf->SetXY(81, 235.4);
                //     $pdf->Cell(35, 8, 'Grand Total', 'TL', 1, 'C', 0);
                //     $pdf->SetXY(116, 235.4);
                //     $pdf->Cell(35, 8, 'Result', 'LTB', 1, 'C', 0);
                //     $pdf->SetXY(151, 235.4);
                //     $pdf->Cell(42, 8, 'Percentage', 1, 1, 'C', 0);
                //     $pdf->SetFont('times', '', 9);
                //     $pdf->SetXY(16, 243.4);
                //     $pdf->Cell(65, 8, ' Obtained Mark', 'TL', 1, 'C', 0);
                //     $pdf->SetXY(81, 243.4);
                //     $pdf->Cell(35, 8, $data['total_obt'], 'TL', 1, 'C', 0);
                //     $pdf->SetXY(116, 243.6);
                //     $pdf->Cell(35, 8, $data['remarks'], 'LR', 1, 'C', 0);
                //     $pdf->SetXY(151, 243.6);
                //     $pdf->Cell(42, 8, $percentage, 'R', 1, 'C', 0);
                //     $pdf->SetXY(16, 251.3);
                //     $pdf->Cell(65, 8, 'Maximum Mark', 'TLB', 1, 'C', 0);
                //     $pdf->SetXY(81, 251.3);
                //     $pdf->Cell(35, 8, $data['total_max'], 'LBT', 1, 'C', 0);
                //     $pdf->SetXY(116, 251.3);
                //     $pdf->Cell(35, 8, '', 'LRB', 'LB', 'C', 0);
                //     $pdf->SetXY(151, 251.3);
                //     $pdf->Cell(42, 8, '', 'RB', 'RB', 'C', 0);
                // } else {

                //     //less then 9 subject
                //     $pdf->SetXY(16, 182);
                //     $pdf->SetFont('times', 'B', 9);
                //     $pdf->Cell(0, 0, 'Aggregate Marks', 0, 0, 'C', 0);
                //     $pdf->SetXY(16, 185);
                //     $pdf->Cell(65, 8, 'Marks', 'TL', 1, 'C', 0);
                //     $pdf->SetXY(81, 185);
                //     $pdf->Cell(35, 8, 'Grand Total', 'TL', 1, 'C', 0);
                //     $pdf->SetXY(116, 185);
                //     $pdf->Cell(35, 8, 'Result', 'LTB', 1, 'C', 0);
                //     $pdf->SetXY(151, 185);
                //     $pdf->Cell(42, 8, 'Percentage', 1, 1, 'C', 0);
                //     $pdf->SetFont('times', '', 9);
                //     $pdf->SetXY(16, 193);
                //     $pdf->Cell(65, 8, ' Obtained Mark', 'TL', 1, 'C', 0);
                //     $pdf->SetXY(81, 193);
                //     $pdf->Cell(35, 8, $data['total_obt'], 'TL', 1, 'C', 0);
                //     $pdf->SetXY(116, 193);
                //     $pdf->Cell(35, 8, $data['remarks'], 'TLR', 1, 'C', 0);
                //     $pdf->SetXY(151, 193);
                //     $pdf->Cell(42, 8, $percentage, 'TR', 1, 'C', 0);
                //     $pdf->SetXY(16, 201.4);
                //     $pdf->Cell(65, 8, 'Maximum Mark', 'TLB', 1, 'C', 0);
                //     $pdf->SetXY(81, 201.4);
                //     $pdf->Cell(35, 8, $data['total_max'], 'LBT', 1, 'C', 0);
                //     $pdf->SetXY(116, 201.4);
                //     $pdf->Cell(35, 8, '', 'LRB', 'LB', 'C', 0);
                //     $pdf->SetXY(151, 201.4);
                //     $pdf->Cell(42, 8, '', 'RB', 'RB', 'C', 0);
                // }
            } else {
                // Set the Grade and grade value
                $total_obt_grade_value = 0;
                $total_credit = 0;
                // echo "<pre>";
                // print_r($data['marks']);die;
                foreach($data['marks'] as $key=>$value) {
                    if ($value['remarks_status'] == 'FAIL') {
                        $data['marks'][$key]['grade'] = ($value['obt_marks'] == 'AB') ? 'S' : 'F';
                        $data['marks'][$key]['grade_value'] = '0';
                    } else {
                        $grandTotal = (int)$value['Min_Marks'] + (int)$value['Max_Marks'];
                        $student_obt_mark = $value['obt_marks'];
                        $student_obt_per = round(($student_obt_mark/$grandTotal) * 100,2); 
                        if($student_obt_per > 90 ) {
                            $data['marks'][$key]['grade'] = 'O';
                            $data['marks'][$key]['grade_value'] = '10';
                        } elseif ($student_obt_per > 80 && $student_obt_per <= 90) {
                            $data['marks'][$key]['grade'] = 'A+';
                            $data['marks'][$key]['grade_value'] = '9';
                        } elseif ($student_obt_per > 70 && $student_obt_per <= 80) {
                            $data['marks'][$key]['grade'] = 'A';
                            $data['marks'][$key]['grade_value'] = '8';
                        } elseif ($student_obt_per > 60 && $student_obt_per <= 70) {
                            $data['marks'][$key]['grade'] = 'B+';
                            $data['marks'][$key]['grade_value'] = '7';
                        } elseif ($student_obt_per > 55 && $student_obt_per <= 60) {
                            $data['marks'][$key]['grade'] = 'B';
                            $data['marks'][$key]['grade_value'] = '6';
                        } elseif ($student_obt_per > 50 && $student_obt_per <= 55) {
                            $data['marks'][$key]['grade'] = 'C';
                            $data['marks'][$key]['grade_value'] = '5';
                        } elseif ($student_obt_per >= 40 && $student_obt_per <= 50) {
                            $data['marks'][$key]['grade'] = 'P';
                            $data['marks'][$key]['grade_value'] = '4';
                        } else {
                            $data['marks'][$key]['grade'] = 'F';
                            $data['marks'][$key]['grade_value'] = '0';
                        }
                    }
                    $total_obt_grade_value += intval($data['marks'][$key]['grade_value']*$data['marks'][$key]['Credit']);
                    $total_credit += intval($data['marks'][$key]['Credit']);
                }
                $data['SGPA'] = number_format($total_obt_grade_value/$total_credit,2);
                // $total_course_dur = (json_decode($data['total_duration'],true))[0];
                $decoded_duration = json_decode($data['total_duration'], true);

                if (is_array($decoded_duration)) {
                    $total_course_dur = $decoded_duration[0] ?? null; // or provide default
                } else {
                    $total_course_dur = $decoded_duration;
                }
                $checkLastSem = ($total_course_dur == $data['Duration']) ? true : false;
                $sgpa_record = [];
                if ($checkLastSem) {
                    $sgpa_record = calculateCGPA($data['Enrollment_No'],$row['Sub_Course_ID'],$data['Duration']);
                    $sgpa_record[] = array(
                        'semester' =>  $data['Duration'], 
                        'sgpa' => $data['SGPA'] ,
                        'total_credit' => $total_credit
                    );
                } 
                 
                //echo "<pre>"; print_r($sgpa_record); exit;
                // echo "<pre>"; print_r($data); exit;
                if (isset($_REQUEST['marksheet_in_grade'])) {
                    setHeaderGrade($data,$exam_month);
                    $cellHeight = 10;
                    $pdf->SetXY(16, 99);
                    $pdf->SetFont('times', 'B', 9);
                    $pdf->MultiCell(35, $cellHeight, 'Course Code', 'TLB', 'C');
                    $pdf->SetXY(51, 99);
                    $pdf->MultiCell(102, $cellHeight, 'Course Title ', 'TLB', 'C');
                    $pdf->SetXY(153,99);
                    $pdf->MultiCell(20, $cellHeight, 'Credit', 'TLB', 'C');
                    $pdf->SetXY(173,99);
                    $pdf->MultiCell(20, $cellHeight, 'Grade', 'TLRB', 'C');
                    $pdf->Ln();
                    $pdf->SetFont('times', '', 9);
                    $x_cor = 16;
                    $y_cor = 109;
                    $pdf->SetX($x_cor);
                    $pdf->SetY($y_cor);
                    makeGradeMarksheet($data['marks'],$data['SGPA'],$x_cor,$sgpa_record,$data['remarks']);
                } else {
                    setHeaderPercentage($data,$exam_month);
                    // Header Part
                    $pdf->SetFont('times', 'B', 9);
                    $cellWidth = 25;
                    $cellHeight = 10;
                    $pdf->SetXY(16.1, 96);
                    $pdf->MultiCell(25, 10, 'Subject Code', 'TLB', 'C');
                    $pdf->SetXY(41, 96);
                    $pdf->MultiCell(101, 10, 'Subject Name ', 'TLB', 'C');
                    // $pdf->SetXY(117, 115);
                    // $pdf->MultiCell(25, 10, 'Internal', 'TL', 'C');
                    // $pdf->SetXY(142, 115);
                    // $pdf->MultiCell(25, 10, 'External', 'TL', 'C');
                    // $pdf->SetXY(167, 115);
                    // $pdf->MultiCell(26, 10, 'Total', 'TLR', 'C');
                    // $pdf->SetXY(16.1, 125);
                    // $pdf->MultiCell(25, 10, '', 'LB', 'C');
                    // $pdf->SetXY(41, 125);
                    // $pdf->MultiCell(101, 10, ' ', 'LB', 'C');
                    // $pdf->SetXY(117, 125);
                    // $pdf->MultiCell(12.6, 10, 'Obt', 'TBL', 'C');
                    // $pdf->SetXY(129.8, 125);
                    // $pdf->MultiCell(12, 10, 'Max', 'TBL', 'C');
                    $pdf->SetXY(135, 96);
                    $pdf->MultiCell(12.5, 10, 'Obt', 'TBL', 'C');
                    $pdf->SetXY(148, 96);
                    $pdf->MultiCell(12, 10, 'Max', 'TBL', 'C');
                    $pdf->SetXY(160, 96);
                    $pdf->MultiCell(14, 10, 'Min', 'TBL', 'C');
                    $pdf->SetXY(174, 96);
                    $pdf->MultiCell(19, 10, 'Remarks', 'TLBR', 'C');
                    $pdf->SetXY(10, 96);
                    $pdf->Ln();
                    $pdf->SetFont('times', '', 9);
                    $x_cor = 16;
                    $pdf->SetX($x_cor);
                    $remark_statuss = [];

                    makePercentageMarksheet($data['marks'],$x_cor);
                    
                    // Footer Part
                    $pdf->SetXY(16, 189);
                    $pdf->SetFont('times', 'B', 9);
                    $pdf->Cell(0, 0, 'Aggregate Marks', 0, 0, 'C', 0);

                    $pdf->SetXY(16, 192.2);
                    $pdf->Cell(65, 8, 'Marks', 'TL', 1, 'C', 0);
                    $pdf->SetXY(81, 192.2);
                    $pdf->Cell(35, 8, 'Grand Total', 'TL', 1, 'C', 0);
                    $pdf->SetXY(116, 192.2);
                    $pdf->Cell(35, 8, 'Result', 'LT', 1, 'C', 0);
                    $pdf->SetXY(151, 192.2);
                    $pdf->Cell(42, 8, 'Percentage', 'LTR', 1, 'C', 0);
                    $pdf->SetFont('times', '', 9);
                    $pdf->SetXY(16, 198.2);
                    $pdf->Cell(65, 8, ' Obtained Mark', 'TL', 1, 'C', 0);
                    $pdf->SetXY(81, 198.2);
                    $pdf->Cell(35, 8, $data['total_obt'], 'TL', 1, 'C', 0);
                    $pdf->SetXY(116, 198.2);
                    $pdf->Cell(35, 8, $data['remarks'], 'TLR', 1, 'C', 0);
                    $pdf->SetXY(151, 198.2);
                    $pdf->Cell(42, 8, $percentage, 'TR', 1, 'C', 0);


                    $pdf->SetXY(16, 206.6);
                    $pdf->Cell(65, 8, 'Maximum Mark', 'TLB', 1, 'C', 0);
                    $pdf->SetXY(81, 206.6);
                    $pdf->Cell(35, 8, $data['total_max'], 'LBT', 1, 'C', 0);
                    $pdf->SetXY(116, 206.6);
                    $pdf->Cell(35, 8, '', 'LRB', 'LB', 'C', 0);
                    $pdf->SetXY(151, 206.6);
                    $pdf->Cell(42, 8, '', 'RB', 'RB', 'C', 0);
                }
            }
            $pdf->SetXY(20, 212.5);
            
            // if($data['University_ID']==20){
            //   $pdf->Cell(0, 9.1, $date_of_issue, 0, 1, 'L', 0);  
            // }else{
            //   $pdf->Cell(0, 9.1, "Date:- ".date('d-m-Y'), 0, 1, 'L', 0);
            //     //  $pdf->Cell(0, 9.1, '04-06-2025', 0, 1, 'L', 0);
            // }
            
            $filename = $data['ID'] . "_" . time() . ".pdf";
            // print_r($pdf_dir . $filename);die;
            $templateFile = 'mduMarksheet.pdf';
            $pageCount = $pdf->setSourceFile($templateFile);
            $tplIdx = $pdf->importPage(2);
            $pdf->addPage('L');
            // $pdf->useTemplate($tplIdx, 0, 0, 210, 297); 
                $pdf->useTemplate($tplIdx, 0, 0, 297, 210);
            $pdf->Output($pdf_dir . $filename, "I");die;
        } else {
            makeStatus($data,"Result Not Found");
        }
    }

    $filename = 'marksheet_download_status' . date('h m s');
    SimpleXLSXGen::fromArray($export_data)->saveAs($pdf_dir . $filename . '.xlsx');

    $zip = new ZipArchive();
    $zip_file = $pdf_dir . 'Marksheets_' . time() . '.zip';
    if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        $files = glob($pdf_dir . '*.{pdf,xlsx}', GLOB_BRACE);
        foreach ($files as $file) {
            $zip->addFile($file, basename($file));
        }
        $zip->close();

        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename=' . basename($zip_file));
        header('Content-Length: ' . filesize($zip_file));
        readfile($zip_file);

        foreach ($files as $file) {
            unlink($file);
        }
        unlink($zip_file);
    } else {
        echo json_encode(['status' => 'error','message' => 'Failed to create ZIP file.']);
    }
} else {
    echo json_encode(['status' => 'error','message' => 'No record found!']);
}

function makeGradeMarksheet($marks_arr,$sgpa,$x_cor,$sgpa_record,$remark) {
    
    global $pdf;
    $cellHeight = 10;
    foreach ($marks_arr as $mark) {
        $pdf->SetX($x_cor);
        $mark['subject_name'] = str_replace("\xC2\xA0", ' ', $mark['subject_name']);
        $mark['subject_name'] = (preg_replace('/\s+/', ' ', $mark['subject_name']));
        if (strlen($mark['subject_name']) > 50) {
            $pdf->Cell(35, $cellHeight, $mark['Code'], 'LB', 0, 'C');
            $nameParts = explode("\n", wordwrap($mark['subject_name'], 50));
            $pdf->MultiCell(102, 5, " ". $nameParts[0] . chr(10) . " " .$nameParts[1], 'BL', 0, 0, 'L');
            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->SetXY(153,$y-10);
            $pdf->Cell(20, $cellHeight, $mark['Credit'], 'LB', 0, 'C');
            $pdf->Cell(20, $cellHeight, $mark['grade'], 'LBR', 0, 'C');
        } else {
            $pdf->Cell(35, $cellHeight, $mark['Code'], 'LB', 0, 'C');
            $pdf->Cell(102, $cellHeight," ". $mark['subject_name'], 'LB', 0, 'L');
            $pdf->Cell(20, $cellHeight, $mark['Credit'], 'LB', 0, 'C');
            $pdf->Cell(20, $cellHeight, $mark['grade'], 'LBR', 0, 'C');
        }
        $pdf->Ln();
    }
    $pdf->SetFont('times', 'B', 9);
    $y_cor = $pdf->GetY();
    $pdf->SetXY(153,$y_cor);
    $pdf->Cell(20,$cellHeight, 'SGPA:', 'LB', 0, 'C');
    $pdf->Cell(20,$cellHeight, $sgpa, 'LBR', 0, 'C');

    if (!empty($sgpa_record))  {
        $pdf->SetFont('times', '', 9);
        $y_cor = $pdf->GetY();
        $y_cor += 20;
        $pdf->SetXY(16,$y_cor);
        $a = 1;
        $total_sgpa_credit = 0;
        $grand_total_credit = 0;
        $total_sem = count($sgpa_record);
        
        foreach($sgpa_record as $sgpa_details) {
            $total_sgpa_credit += $sgpa_details['sgpa']*$sgpa_details['total_credit'];
            $grand_total_credit += intval($sgpa_details['total_credit']);
          	$sgpaTotal = number_format((float)$sgpa_details['sgpa'], 2, '.', '');
            $content = " SGPA of Semester " . intToRoman($sgpa_details['semester']) . ":" ;
            $width = $pdf->GetStringWidth($content);
            $pdf->Cell($width, $cellHeight, $content, 'LBT', 0, 'L');
          	$pdf->SetFont('times', 'B', 9);
            $newWidth = 59- $width;
          	$pdf->Cell($newWidth, $cellHeight, " $sgpaTotal", 'BTR', 0, 'L');
          	$pdf->SetFont('times', '', 9);
            if($a == 3) {
                $pdf->Ln();
                $pdf->SetX(16);
                $a = 0;
            }
            $a++;
        }
        $pdf->SetFont('times', 'B', 9);
        $pdf->SetX(17);
        $pdf->Cell(30, $cellHeight,"RESULT: " . strtoupper($remark) , '', 0, 'C');
        $cgpa = round(($total_sgpa_credit/$grand_total_credit),2);
        $pdf->SetX(134);
        $pdf->Cell(39, $cellHeight,"CGPA: ", 'LBRT', 0, 'R');
        $pdf->Cell(20, $cellHeight, $cgpa , 'LBRT', 0, 'C');
    }
}

function intToRoman($num) {
    $mapping = [10 => 'X',9 => 'IX',5 => 'V',4 => 'IV',1 => 'I'];
    $result = '';
    foreach ($mapping as $value => $roman) {
        while ($num >= $value) {
            $result .= $roman;
            $num -= $value;
        }
    }
    return $result;
}


function makePercentageMarksheet($marks_arr,$x_cor) {
    global $pdf;
    foreach ($marks_arr as $mark) {
        $pdf->SetX($x_cor);
        $cellHeight = (strlen($mark['subject_name']) > 55) ? 15 : 10;
        $remark_statuss[] = $mark['remarks'];
        $mark['subject_name'] = str_replace("\xC2\xA0", ' ', $mark['subject_name']);
        $mark['subject_name'] = preg_replace('/\s+/', ' ', $mark['subject_name']);
        $mark['subject_name'] = utf8_encode($mark['subject_name']);
        if (strlen($mark['subject_name']) > 55) {
            $pdf->SetFont('times', '', 9);
            $pdf->Cell(25, 10, $mark['Code'], 'BL', 0, 'L');
            $nameParts = explode("\n", wordwrap($mark['subject_name'], 55));
            $pdf->MultiCell(94, 5, $nameParts[0] . chr(10) . $nameParts[1], 'BL', 0, 0, 'L');
            //$pdf->MultiCell(101, 10,  strlen($mark['subject_name']), 'BL', 0, 0, 'L');
            // $pdf->MultiCell(76, 5, strtoupper($nameParts[0]) . chr(10) . strtoupper($nameParts[1]), 'BL', 0, 0, 'L');
            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->SetXY($x + 132, $y - 10);
            // $pdf->Cell(12.8, 10, $mark['obt_marks_int'], 'LB', 0, 'C');
            // $pdf->Cell(12.2, 10, $mark['Min_Marks'], 'LB', 0, 'C');
            $pdf->Cell(13, 10, $mark['obt_marks_ext'], 'BL', 0, 'C');
            $pdf->Cell(12, 10, $mark['Max_Marks'], 'BL', 0, 'C');
            $pdf->Cell(14, 10, $mark['Min_Marks'], 'BL', 0, 'C');
            $remark = $mark['obt_marks_ext']<$mark['Min_Marks']?"Fail":"Pass";
            $pdf->Cell(19, 10, $remark, 'BLR', 0, 'C');
        } else {
            $pdf->Cell(25, $cellHeight, $mark['Code'], 'LB', 0, 'L');
            // $pdf->Cell(76, $cellHeight, strtoupper($mark['subject_name']), 'LB', 0, 'L');
            $pdf->Cell(94, $cellHeight, $mark['subject_name'], 'LB', 0, 'L');
            // $pdf->Cell(12.8, 10, $mark['obt_marks_int'], 'LB', 0, 'C');
            // $pdf->Cell(12.2, 10, $mark['Min_Marks'], 'LB', 0, 'C');
            $pdf->Cell(13, 10, $mark['obt_marks_ext'], 'BL', 0, 'C');
            $pdf->Cell(12, 10, $mark['Max_Marks'], 'BL', 0, 'C');
            $pdf->Cell(14, 10, $mark['Min_Marks'], 'BL', 0, 'C');
            $remark = $mark['obt_marks_ext']<$mark['Min_Marks']?"Fail":"Pass";
            $pdf->Cell(19, 10, $remark, 'BLR', 0, 'C');
        }
        $pdf->Ln();
    }
}

function makeStatus($student_data,$message) {

    global $export_data;
    if ($student_data['University_ID'] == '41') {
        $export_data[] = array($student_data['Enrollment_No'],$student_data['program_Type'],$student_data['course'],$student_data['Duration'],$message);
    } else {
        list($scheme_id, $semester) = explode('|', $_POST['semester']);
        $export_data[] = array($student_data['Enrollment_No'],$student_data['program_Type'],$student_data['course'],$semester,$message);
    }
}

function calculateCGPA($enrol,$sub_course_id,$total_sem_dur) {
    
    global $conn;
    $sgpa_record = [];
    list($scheme_id, $semester) = explode('|', $_POST['semester']);
    $i = 1;
    while ($i < $total_sem_dur) {
        $subject_record = [];
        $sem_sql = " AND Syllabi.Semester = '$i' AND Syllabi.Scheme_ID = $scheme_id";
        $temp_subjects = $conn->query("SELECT Paper_Type,marksheets.obt_marks_ext, marksheets.obt_marks_int,marksheets.obt_marks,marksheets.status,marksheets.remarks,Syllabi.Name as subject_name,Syllabi.Min_Marks, Syllabi.Max_Marks, Syllabi.Credit FROM marksheets LEFT JOIN Syllabi ON marksheets.subject_id = Syllabi.ID WHERE enrollment_no = '$enrol' AND Sub_Course_ID = '$sub_course_id' $sem_sql ORDER BY Syllabi.Code ASC");    
        if ($temp_subjects->num_rows > 0 ) {
            $j = 0;
            while ($temp_subject = $temp_subjects->fetch_assoc()) {
                $subject_record[$j]['Min_Marks'] = $temp_subject['Min_Marks'];
                $subject_record[$j]['Max_Marks'] = $temp_subject['Max_Marks'];
                $subject_record[$j]['Credit'] = $temp_subject['Credit'];
                if ($temp_subject['obt_marks_ext'] == 'AB' && $temp_subject['obt_marks_ext'] == 'AB') {
                    $subject_record[$j]['obt_marks'] = 'AB';
                    $subject_record[$j]['remarks_status'] = 'FAIL';
                } else if ($temp_subject['obt_marks_ext'] == 'AB' && $temp_subject['obt_marks_int'] != 'AB') {
                    $subject_record[$j]['obt_marks'] = $temp_subject['obt_marks_int'];
                    $subject_record[$j]['remarks_status'] = 'FAIL';
                } else if ($temp_subject['obt_marks_int'] == 'AB' && $temp_subject['obt_marks_ext'] != 'AB') {
                    $subject_record[$j]['obt_marks'] = $temp_subject['obt_marks_int'];
                    $subject_record[$j]['remarks_status'] = 'FAIL';
                } else if ($temp_subject['obt_marks_ext'] != 'AB' && $temp_subject['obt_marks_ext'] != 'AB') {
                    $subject_record[$j]['obt_marks'] = intval($temp_subject['obt_marks_ext']) + intval($temp_subject['obt_marks_int']);
                    $subject_record[$j]['remarks_status'] = 'PASS';
                }
                $j++;
            }
            list($sgpa,$total_credit) = calculateSGPA($subject_record);
            $sgpa_record[] = array(
                'semester' => $i , 
                'sgpa' => $sgpa ,
                'total_credit' => $total_credit
            );
        }
        $i++;
    }
    return $sgpa_record;
}

function calculateSGPA($temp_subject) {

    $total_obt_grade_value = 0;
    $total_credit = 0;
    $grade_value = '';
    foreach($temp_subject as $key=>$value) {
        if ($value['remarks_status'] == 'FAIL') {
            $grade_value = '0';
        } else {
            $grandTotal = $value['Min_Marks'] + $value['Max_Marks'];
            $student_obt_mark = $value['obt_marks'];
            $student_obt_per = round(($student_obt_mark/$grandTotal) * 100,2); 
            $grade_value = match (true) {
                $student_obt_per > 90 => '10',
                $student_obt_per > 80 && $student_obt_per <= 90 => '9',
                $student_obt_per > 70 && $student_obt_per <= 80 => '8',
                $student_obt_per > 60 && $student_obt_per <= 70 => '7',
                $student_obt_per > 55 && $student_obt_per <= 60 => '6',
                $student_obt_per > 50 && $student_obt_per <= 55 => '5',
                $student_obt_per >= 40 && $student_obt_per <= 50 => '4',
                default => '0',
            };
        }
        $total_obt_grade_value += intval($grade_value)*intval($value['Credit']);
        $total_credit += intval($value['Credit']);
    }

    return [number_format($total_obt_grade_value/$total_credit,2),$total_credit];
    
}


function setHeaderGrade($data,$exam_month) {
  
    global $pdf;
    $pdf->SetFont("times", '', 9);
    $pdf->SetXY(15, 55);
    $stat = (isset($_REQUEST['marksheet_in_grade']) ? "Statement of Grades" : "Statement of Marks");
    $pdf->Cell(0, 0, strtoupper($stat) , 0, 0, 'C', 0);
  	$pdf->SetFont("times", 'B', 9);
    $pdf->SetXY(15, 30);
 
     $course_name =  (isset($_REQUEST['marksheet_in_grade']) ? strtoupper($data['course']) : $data['course']);
     $pdf->Cell(0, 0, $data['duration_val'] . '. ' . 'IN' . '  ' . $course_name , 0, 0, 'C', 0);
    $pdf->SetXY(15, 69);
 	$pdf->SetFont("times", '', 9);
  	$addmissionSession = explode('-',$data['Admission_Session']);
  	$addmissionYearFrom = 2000 + (int)$addmissionSession[1];
  	$addmissionYearTo = 3 + (int)$addmissionYearFrom;
    $pdf->Cell(0, 0, strtoupper('Session: ' . $data['Admission_Session'] . ''), 0, 0, 'C', 0);
  	$pdf->SetFont("times", 'B', 9);
    $pdf->SetXY(16.1,75);
    $pdf->Cell(13, 10, 'Name: ', 'LTB', 0, 'L', 0);
    $full_name = $data['First_Name'] . ' ' . $data['Middle_Name'] . ' ' . $data['Last_Name']; 
    checkWarpText(strtoupper($full_name),34,94);
    $pdf->SetXY(123,75);
    $pdf->Cell(70, 8, 'Enrollment No.: ' . $data['Enrollment_No'], 'LTR', 0, 'L', 0);
    // $pdf->SetXY(16.1,83);
    // $pdf->Cell(26, 10, 'Father Name : ', 'LTB', 0, 'L', 0);
    // checkWarpText(strtoupper($data['Father_Name']),33,94);
    // $pdf->SetXY(123,83);
    // $pdf->Cell(70, 10, $data['mode_type'] . ' ' . ':' . ' ' . $data['durMonthYear'], 1, 0, 'L', 0);
    $pdf->SetXY(16.1,85);
    $pdf->Cell(107, 10, 'School: ' .  $data['university_name'], 'LTB', 0, 'L', 0);
    $pdf->SetXY(123,85);
    $semInRoman = intToRoman((int)$data['durMonthYear']);
  	$examSession = explode('-',$exam_month);
  	$examMonth = date('F',strtotime($examSession[0]));
  	$examYear = 2000 + (int)$examSession[1];
    $pdf->Cell(70, 10, 'Semester: ' .$data['Admission_Session'] . ')', 1, 0, 'L', 0);
    $pdf->SetFont('times', 'B', 9);
}

function setHeaderPercentage($data,$exam_month) {

    global $pdf;
    $pdf->SetFont("times", '', 9);
    $pdf->SetXY(15, 55);
    $pdf->SetXY(15,47);
    $stat = (isset($_REQUEST['marksheet_in_grade']) ? "Statement of Grades" : "Statement of Marks");
    $pdf->Cell(0, 0, strtoupper($stat) , 0, 0, 'C', 0);
    $pdf->SetXY(15, 40.4);
    $pdf->Cell(0, 0, $data['duration_val'] . '' . '' . '' . $data['course'], 0, 0, 'C', 0);
    $pdf->SetXY(15, 54);
  	$addmissionSession = explode('-',$data['Admission_Session']);
  	$addmissionYearFrom = 2000 + (int)$addmissionSession[1];
  	$addmissionYearTo = 3 + (int)$addmissionYearFrom;
    $pdf->Cell(0, 0, strtoupper('Admission Session :' . $data['Admission_Session'] . ''), 0, 0, 'C', 0);
    $pdf->SetFont("times", 'B', 9);
    $pdf->SetXY(16.1, 69);
    $pdf->Cell(14, 8, 'Name : ', 'LTB', 0, 'L', 0);
    $full_name = $data['First_Name'] . ' ' . $data['Middle_Name'] . ' ' . $data['Last_Name']; 
    checkWarpText(strtoupper($full_name),33,94);
    $pdf->SetXY(123, 69);
    $pdf->Cell(70, 8, 'Enrollment No : ' . $data['Enrollment_No'], 'LTR', 0, 'L', 0);
    $pdf->SetXY(16.1, 77);
    $pdf->Cell(26, 8, 'Father Name : ', 'LTB', 0, 'L', 0);
    checkWarpText(strtoupper($data['Father_Name']),33,94);
    $pdf->SetXY(123, 77);
    $pdf->Cell(70, 8, "Duration" . ' ' . ':' . ' ' . "11 Month", 1, 0, 'L', 0);
    $pdf->SetXY(16.1, 85);
    $pdf->Cell(107, 8, 'School : ' . ' ' . $data['university_name'], 'LTB', 0, 'L', 0);
    $pdf->SetXY(123, 85);
  	$examSession = explode('-',$exam_month);
  	$examMonth = date('F',strtotime($examSession[0]));
  	$examYear = 2000 + (int)$examSession[1];
    $pdf->Cell(70, 8, 'Exam Session : ' . ' ' . ucwords("APRIL 2025"), 1, 0, 'L', 0);
    $pdf->SetFont('times', 'B', 9);
}

function checkWarpText($content,$content_length,$width) {

    global $pdf;
    if (strlen($content) > $content_length) {
        $nameParts = explode("\n", wordwrap($content, $content_length));
        $pdf->SetFont("times", '', 9);
        $pdf->MultiCell($width,4, $nameParts[0] . chr(10) . $nameParts[1], 'TB', 0, 0, 'L');
        $pdf->SetFont("times", '', 9);    
    } else {
        $pdf->Cell($width,8,$content, 'TB', 0, 'L', 0);    
    }
}

function selectExamSessionAndDateOfIssue($admission_session,$semester,$type) {
    list($month,$year) = explode('-',$admission_session);
    $month = ucwords(strtolower(substr($month,0,3)));
  	$year = intval($year);
    $year = (strlen($year) > 2) ? date('y', strtotime("$year-01-01")) : $year;
    $updated_adm_session = $month . '-' . $year;
    $list_details = array(
        [
            'admission_session' => 'Jul-21',
            'exam_session' => 'Dec-21',
            'semester' => '1' ,
            'date_of_issue' => '10-02-2022'
        ], [
            'admission_session' => 'Jul-21',
            'exam_session' => 'Jun-22',
            'semester' => '2' ,
            'date_of_issue' => '08-08-2022'
        ] , [
            'admission_session' => 'Jul-21',
            'exam_session' => 'Dec-22',
            'semester' => '3' ,
            'date_of_issue' => '15-02-2023'
        ], [
            'admission_session' => 'Jul-21',
            'exam_session' => 'Jun-23',
            'semester' => '4' ,
            'date_of_issue' => '22-07-2023'
        ] , [
            'admission_session' => 'Jul-21',
            'exam_session' => 'Dec-23',
            'semester' => '5' ,
            'date_of_issue' => '26-02-2024'
        ] , [
            'admission_session' => 'Jul-21',
            'exam_session' => 'Jun-24',
            'semester' => '6' ,
            'date_of_issue' => '19-10-2024'
        ] , [
            'admission_session' => 'Jul-22',
            'exam_session' => 'Dec-22',
            'semester' => '1' ,
            'date_of_issue' => '15-02-2023'
        ], [
            'admission_session' => 'Jul-22',
            'exam_session' => 'Jun-23',
            'semester' => '2' ,
            'date_of_issue' => '22-07-2023'
        ], [
            'admission_session' => 'Jul-22',
            'exam_session' => 'Dec-23',
            'semester' => '3' ,
            'date_of_issue' => '20-02-2024'
        ], [
            'admission_session' => 'Jul-22',
            'exam_session' => 'Jun-24',
            'semester' => '4' ,
            'date_of_issue' => '19-10-2024'
        ], [
            'admission_session' => 'Jul-23',
            'exam_session' => 'Dec-23',
            'semester' => '1' ,
            'date_of_issue' => '26-02-2024'
        ], [
            'admission_session' => 'Jul-23',
            'exam_session' => 'Jun-24',
            'semester' => '2' ,
            'date_of_issue' => '19-10-2024'
        ],
             [
            'admission_session' => 'Jul-23',
            'exam_session' => 'Dec-24',
            'semester' => '3' ,
            'date_of_issue' => date('d-m-Y')
        ],
        [
            'admission_session' => 'Jul-23',
            'exam_session' => 'Jun-24',
            'semester' => '4' ,
            'date_of_issue' => date('d-m-Y'),
        ],
        [
            'admission_session' => 'Jul-24',
            'exam_session' => 'Dec-24',
            'semester' => '1' ,
            'date_of_issue' => "15-05-2025",
        ],
        [
            'admission_session' => 'Jul-22',
            'exam_session' => 'Dec-24',
            'semester' => '5' ,
            'date_of_issue' => date('d-m-Y'),
        ],
        [
            'admission_session' => 'Jul-24',
            'exam_session' => 'Dec-24',
            'semester' => '5' ,
            'date_of_issue' => "15-05-2025",
        ],
        [
            'admission_session' => 'Jul-24',
            'exam_session' => 'Dec-25',
            'semester' => '5' ,
            'date_of_issue' => date('d-m-Y'),
        ]
    );
    
    $exam_session = ''; $date_of_issue = '';
    foreach ($list_details as $key => $value) {
        
        if($value['admission_session'] == $updated_adm_session && $value['semester'] == $semester) {
            $exam_session = $value['exam_session'];
            $date_of_issue = $value['date_of_issue'];
            break;
        }
    }
    
    return ($type == 'date&exam') ?  [$date_of_issue,$exam_session] : [$date_of_issue]; 
}