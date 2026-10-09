<?php
require '../../includes/db-config.php';
session_start();
if (!empty($_POST)) {
    $id = strval($_POST['id']);
    $by = strval($_POST['by']);
    if ($by == 'users') {
        $user_id = $conn->query("SELECT Code FROM Users WHERE ID = '$id'");
        if ($user_id->num_rows > 0) {
            $codeArr = $user_id->fetch_assoc();
            $center_id = json_encode($codeArr['Code'], JSON_UNESCAPED_SLASHES);
            // $_SESSION['usersFilter'] = " AND JSON_CONTAINS(Syllabi.User_ID, '$center_id') ";
        }
    } else if ($by == 'course' && !empty($id) ) {
        $_SESSION['courseFilter'] = " AND Syllabi.Course_ID = " . $id;
    } else if ($by == 'sub_course' && !empty($id)) {
        $_SESSION['subCourseFilter'] = " AND Syllabi.Sub_Course_ID = " . $id;
    } else if ($by == 'duration' && !empty($id)) {
        $_SESSION['durationFilter'] = " AND Syllabi.Semester = '" . $id . "'";
    }

    echo json_encode(['status' => true]);
} else {
    echo json_encode(['status' => false]);
}
