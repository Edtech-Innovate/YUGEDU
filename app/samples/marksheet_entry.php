<?php 

include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-top.php');
require ('../../extras/vendor/shuchkin/simplexlsxgen/src/SimpleXLSXGen.php');

if($_SESSION['university_id']=='48'){ 
    $header[] = array('Student_ID','Enrollment_No', 'Marksheet No','Exam Session','Duration');
}else{
    $header[] = array('Student_ID','Enrollment_No', 'Marksheet No','Exam Session','Duration');
}

$xlsx = SimpleXLSXGen::fromArray( $header )->downloadAs('Marksheet Entry Sheet Sample.xlsx');
?>