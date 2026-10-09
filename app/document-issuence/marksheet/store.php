<?php
require '../../../includes/db-config.php';
require('../../../extras/vendor/shuchkin/simplexlsxgen/src/SimpleXLSXGen.php');
require('../../../extras/vendor/nuovo/spreadsheet-reader/SpreadsheetReader.php');

session_start();
$export_data = array();

if (isset($_FILES['file'])) {

    if ($_SESSION['university_id'] == 48) {
        $header = array('Student_ID','Enrollment_No', 'Marksheet No','Exam Session','Duration','Remark');
    } else {
        $header = array('Student_ID','Enrollment_No', 'Marksheet No','Exam Session','Duration','Remark');
    }
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
                
                $student_id = mysqli_real_escape_string($conn,$row[0]);
                if(checkEmptyData($row,$student_id,'Studetn Id')) continue;

                $enrollment_no = mysqli_real_escape_string($conn,$row[1]); 
                if(checkEmptyData($row,$enrollment_no,'Enrollment No')) continue;
                
                $marksheet_no = mysqli_real_escape_string($conn,$row[2]);
                if(checkEmptyData($row,$student_id,'Marksheet No')) continue;

                $exam_session = mysqli_real_escape_string($conn,$row[3]);
                if(checkEmptyData($row,$student_id,'Exam session')) continue;

                $duration = mysqli_real_escape_string($conn,$row[4]);
                if(checkEmptyData($row,$student_id,'Duration')) continue;

                if ($student_id == 'Student_ID') {
                    continue;
                }   

                $check = $conn->query("SELECT * FROM `MarkSheet_Entry` WHERE Enrollment_No = '$enrollment_no' AND Exam_Session = '$exam_session' AND Duration = '$duration'");
                if ($check->num_rows > 0 ) {
                    $export_data[] = array_merge($row, ['Marksheet already present for this session']);
                    continue;
                }

                $getCenter = $conn->query("SELECT Added_For FROM `Students` WHERE Enrollment_No = '$enrollment_no'");
                if($getCenter->num_rows > 0 ) {
                    $getCenter = mysqli_fetch_column($getCenter);
                }
                $add = $conn->query("INSERT INTO `MarkSheet_Entry`(`Enrollment_No`, `Marksheet_No`, `Exam_Session`, `Duration`,`Added_For`,`Docket_Id`) VALUES ('$enrollment_no','$marksheet_no','$exam_session','$duration','$getCenter',null)");

                if ($add) {
                    $export_data[] = array_merge($row, ['Marksheet added successfully!']);
                } else {
                    $export_data[] = array_merge($row, ['Something went wrong!']);
                }
            }
        }

        unlink($uploadFilePath);
        $xlsx = SimpleXLSXGen::fromArray($export_data)->downloadAs('Marksheet Entry Status ' . date('h m s') . '.xlsx');
    }
}

function checkEmptyData($row,$data,$message) {
    global $export_data;    
    if(empty($data)) {
        $export_data[] = array_merge($row, ["Data not insert,$message is empty"]);
        return true;
    } else {
        return false;
    }
}
?>