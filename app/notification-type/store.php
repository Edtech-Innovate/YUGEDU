<?php
require '../../includes/db-config.php';
session_start();

if(isset($_POST['notification_type'])){

    $notification_type = mysqli_real_escape_string($conn,$_POST['notification_type']);

    $insert_query = "INSERT INTO `Notification_Heading`(`Name`) VALUES ('$notification_type')";
    $insert = $conn->query($insert_query);
    
    if ($insert){
        echo json_encode(['status'=>200, 'message'=>'Notification type added successlly!']);
    } else {
        echo json_encode(['status'=>400, 'message'=>'Something went wrong!']);
    }
}
?>
