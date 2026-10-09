<?php

## Database configuration
include '../../includes/db-config.php';
session_start();

$data_field = file_get_contents('php://input'); // by this we get raw data
$data_field = json_decode($data_field,true);
$result_list = [];
foreach ($data_field as $value) {
    $function_name = "notification". ucfirst($value);
    $result_list[$value] = call_user_func($function_name);
}

echo json_encode($result_list);

function notificationHeading() : string {
    global $conn;

    $option = '<option value = "">Choose Heading</option>';
    $headings = $conn->query("SELECT ID,Name FROM `Notification_Heading`");
    $headings = mysqli_fetch_all($headings,MYSQLI_ASSOC);
    $headings_details = array_column($headings,'Name','ID');
    foreach($headings_details as $key=>$value) {
        $option .= '<option value = "'.$key.'">'.$value.'</option>';
    }
    return $option;
}

function notificationUser() : string {
    $option = '<option value="">Choose User</option>';
    $option .= '<option value="student">Student</option>';
    $option .= '<option value="center">Center</option>';
    $option .= '<option value="all">All</option>'; 
    return $option;
}
?>