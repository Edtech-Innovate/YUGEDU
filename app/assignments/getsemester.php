<?php
ini_set('display_errors', 1);
require '../../includes/db-config.php';
session_start();
$subCourseId = intval($_POST['subCourseId']);
$subjectId = isset($_POST['ID']) ? intval($_POST['ID']) : '';
$university_id = isset($_POST['University_id']) ? intval($_POST['University_id']) : '';
$sql = "SELECT Name, Min_Duration FROM Sub_Courses WHERE ID = $subCourseId AND Status = 1";
$result = $conn->query($sql);

$html = '';

$html = '<option value="">Select Duration</option>';
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // if (is_string($row['Min_Duration']) == 1) {
        //     $duration = json_decode($row['Min_Duration'], true)[0]; 
        // } else {
            $duration = intval($row['Min_Duration']);
        // }
        for ($i = 1; $i <= $duration; $i++) {

            $html .= '<option value="' . $i . '" >' . $i . '</option>';
        }

    }
}
echo $html;