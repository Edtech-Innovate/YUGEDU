<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(isset($_POST['university_id']) && isset($_POST['id'])){
    require '../../includes/db-config.php';
    session_start();

    $id = intval($_POST['id']);
    $university_id = intval($_POST['university_id']);

    // Check if the user is assigned as a reporting manager/parent in University_User for this university
    $check_reporting = $conn->query("SELECT User_ID FROM University_User WHERE Reporting = $id AND University_ID = $university_id");
    if ($check_reporting && $check_reporting->num_rows > 0) {
        echo json_encode([
            'status' => 400, 
            'message' => 'This counsellor currently has active reporting users or assigned centers under them in this university. Please remove dependencies before deleting.'
        ]);
        exit;
    }

    // Proceed with deletions if no reporting records found
    $remove = $conn->query("DELETE FROM Fee_Variables WHERE Code = $id AND University_ID = $university_id");
    $remove = $conn->query("DELETE FROM Alloted_Center_To_SubCounsellor WHERE Code = $id AND University_ID = $university_id");
    $remove = $conn->query("DELETE FROM Alloted_Center_To_Counsellor WHERE Code = $id AND University_ID = $university_id");

    $is_vocational = $conn->query("SELECT ID FROM Universities WHERE ID = $university_id AND Sharing = 0");
    if($is_vocational && $is_vocational->num_rows > 0){
        $remove_For_sub_course = $conn->query("DELETE FROM User_Sub_Courses WHERE `User_ID` = $id AND University_ID = $university_id");
    }
    
    $remove = $conn->query("DELETE FROM University_User WHERE User_ID = $id AND University_ID = $university_id");

    if($remove){
        echo json_encode(['status' => 200, 'message' => 'Allotment removed successfully!']);
    } else {
        echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
    }
}