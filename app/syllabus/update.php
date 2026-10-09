
<?php
if (
    isset($_POST['university_id']) && isset($_POST['course']) &&
    isset($_POST['duration']) && isset($_POST['subject'])
) {
    
    //  echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die(); 
    require '../../includes/db-config.php';
    session_start();
    
    ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


    $university_id = intval($_POST['university_id']);
$sub_course = intval($_POST['course']);
$subject_id = intval($_POST['subject']);
$duration = mysqli_real_escape_string($conn, $_POST['duration']);

$unit_name_arr = $_POST['unit_name'] ?? [];
$unit_code_arr = $_POST['unit_code'] ?? [];
$module_name_arr = $_POST['module_name'] ?? [];

$success = false;

// ✅ Update existing Units and Modules
if (!empty($unit_name_arr['uedit'])) {
    foreach ($unit_name_arr['uedit'] as $unit_key => $units) {
        foreach ($units as $unit_id => $unit_names) {
            $unit_name = mysqli_real_escape_string($conn, $unit_names[0]);
            $unit_code = mysqli_real_escape_string($conn, $unit_code_arr['uedit'][$unit_key][$unit_id][0] ?? '');

            $conn->query("UPDATE Chapter SET Name = '$unit_name', Code = '$unit_code' WHERE ID = $unit_id");

            // Modules under this unit
            if (!empty($module_name_arr['medit'][$unit_key])) {
                foreach ($module_name_arr['medit'][$unit_key] as $module_key => $modules) {
                    foreach ($modules as $module_id => $module_names) {
                        $module_name = mysqli_real_escape_string($conn, $module_names[0]);
                        $conn->query("UPDATE Chapter_Units SET Name = '$module_name' WHERE ID = $module_id");
                    }
                }
            }

            // Add new Modules under existing unit
            if (!empty($module_name_arr['madd'][$unit_key])) {
                foreach ($module_name_arr['madd'][$unit_key] as $module_index => $module_name) {
                    $module_clean = mysqli_real_escape_string($conn, $module_name);
                    $conn->query("INSERT INTO Chapter_Units (Name, Chapter_ID) VALUES ('$module_clean', $unit_id)");
                }
            }
        }
    }
    $success = true;
}

// ✅ Add new Units and Modules
if (!empty($unit_name_arr['uadd'])) {
    foreach ($unit_name_arr['uadd'] as $unit_key => $units) {
        foreach ($units as $unit_id => $unit_names) {
            $unit_name = mysqli_real_escape_string($conn, $unit_names);
            $unit_code = mysqli_real_escape_string($conn, $unit_code_arr['uadd'][$unit_key][$unit_id][0] ?? '');

            $conn->query("INSERT INTO Chapter (Name, Code, University_ID, Sub_Course_ID, Semester, Subject_ID)
                          VALUES ('$unit_name', '$unit_code', $university_id, $sub_course, '$duration', $subject_id)");
            $unit_last_id = $conn->insert_id;

            // Modules under new unit
            if (!empty($module_name_arr['madd'][$unit_key])) {
                foreach ($module_name_arr['madd'][$unit_key] as $module_index => $module_name) {
                    $module_clean = mysqli_real_escape_string($conn, $module_name);
                    $conn->query("INSERT INTO Chapter_Units (Name, Chapter_ID) VALUES ('$module_clean', $unit_last_id)");
                }
            }
        }
    }
    $success = true;
}

if ($success) {
    echo json_encode(['status' => 200, 'message' => 'Syllabus added/updated successfully!']);
} else {
    echo json_encode(['status' => 400, 'message' => 'No data was added or updated.']);
}
} else {
    echo json_encode(['status' => 400, 'message' => 'Missing required fields!']);
}

