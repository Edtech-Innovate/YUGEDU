<?php

if (isset($_POST['university_id']) && isset($_POST['course']) && isset($_POST['duration']) && isset($_POST['subject']) || isset($_POST['chapter_code']) && isset($_POST['unit_code'])) {
	require '../../includes/db-config.php';
	session_start();
	ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
		$university_id = intval($_POST['university_id']);
$sub_course = intval($_POST['course']);
$subject_id = intval($_POST['subject']);
$duration = mysqli_real_escape_string($conn, $_POST['duration']);

$unit_name_arr = is_array($_POST['unit_name']) ? array_filter($_POST['unit_name']) : [];
$unit_code_arr = is_array($_POST['unit_code']) ? array_filter($_POST['unit_code']) : [];
$module_name_arr = is_array($_POST['module_name']) ? $_POST['module_name'] : [];

$success = false;
foreach ($unit_name_arr as $unit_key => $unit_name) {
    $unit_code = $unit_code_arr[$unit_key] ?? '';

    // ✅ Insert Unit into Chapter table
    $addUnit = $conn->query("INSERT INTO Chapter (Name, Code, University_ID, Sub_Course_ID, Semester, Subject_ID) 
        VALUES ('$unit_name', '$unit_code', $university_id, $sub_course, '$duration', $subject_id)");

    if ($addUnit) {
        $success = true;
        $unit_id = $conn->insert_id;
        
        // ✅ Insert Modules under the Unit into Chapter_Units table
        if (!empty($module_name_arr[$unit_key])) {
            foreach ($module_name_arr[$unit_key] as $module_name) {
                $module_name = trim($module_name);
                if (!empty($module_name)) {
                    $conn->query("INSERT INTO Chapter_Units (Name, Chapter_ID) 
                        VALUES ('$module_name', $unit_id)");
                    $success = true;
                }
            }
        }
    }
}

// ✅ Final Response
if ($success) {
    echo json_encode(['status' => 200, 'message' => 'Syllabus added successfully!']);
} else {
    echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
}
}else
{
	echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
} 


