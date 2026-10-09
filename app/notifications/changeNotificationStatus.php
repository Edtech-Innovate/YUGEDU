<?php

## Database configuration
include '../../includes/db-config.php';
session_start();

if(isset($_REQUEST['id'])) {

    try{
        $notification_id = mysqli_real_escape_string($conn,$_REQUEST['id']);
        $notification_details = $conn->query("SELECT `Status`, `published_on` FROM `Notifications_Generated` WHERE ID = '$notification_id'");
        $notification_details = mysqli_fetch_assoc($notification_details);
        $status = $notification_details['Status'];
        $published_data =  (!empty($notification_details['published_on'])) ? json_decode($notification_details['published_on'],true) : null;
        if ($status == '0' && is_null($published_data)) {
            $published_data[] = array(
                "published" => date("d-m-Y") ,
                "un-published" => ""
            );
            $status = '1';
        } elseif ($status == '1') {
            $last_key = array_key_last($published_data);
            $published_data[$last_key]['un-published'] = date("d-m-Y");
            $status = '0';
        } elseif ($status == '0') {
            $last_key = array_key_last($published_data);
            $published_data[$last_key]['un-published'] = date("d-m-Y");
            $published_data[$last_key+1] = array(
                "published" => date("d-m-Y"),
                "un-published" => ""
            );
            $status = '1';
        }
        $published_data = json_encode($published_data);
        $updateQuery = $conn->query("UPDATE Notifications_Generated SET Status = '$status' , published_on = '$published_data' WHERE ID = '$notification_id'");
        if($updateQuery) {
            echo json_encode(['status' => 200 , 'message' => "Status Updated"]);    
        }    
    } catch (Error $e) {
        echo json_encode(['status' => 400 , 'message' => $e->getMessage()]);
    }
    
}
?>