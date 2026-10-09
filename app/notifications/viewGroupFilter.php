<?php
require '../../includes/db-config.php';
session_start();

if (!isset($_REQUEST['id'])) {
    exit(json_encode(['status' => 400 , 'message' => 'Notifiaction Id not present'])); 
}

$notification_id = mysqli_real_escape_string($conn,$_REQUEST['id']);
$notification_data = $conn->query("SELECT * FROM `Notifications_Generated` WHERE ID = '$notification_id'"); 
$notification_data = mysqli_fetch_assoc($notification_data);
if ($notification_data['Send_To'] == 'student') {
    $scheme_column = createColumnData($notification_data['scheme_id'],'Schemes','Name');
    $admissionSession_column = createColumnData($notification_data['admissionSession_id'],'Admission_Sessions','Name');
    $student_column = createColumnData($notification_data['student_id'],'Students',"Enrollment_No");
    $courseAndDuration_column = courseAndDurationData($notification_data['duration']);
} else {
    $center_column = createColumnData($notification_data['center_id'],'Users','Name');
}


function courseAndDurationData($courseDuration_data) {

    global $conn;
    $courseDuration = createBadge("For All");
    if(!empty($courseDuration_data)) {
        $courseDuration_arr = json_decode($courseDuration_data,true);
        $tdData = '<div>';
        foreach($courseDuration_arr as $course_id => $durations) {
            $row = '<div class = "row gap-1">'; 
            $courseName = getName($course_id,'Sub_Courses','Name');
            $row .= '<div class = "col-sm-6">'. createBadge($courseName) .'</div>';
            $row .= '<div> : </div>';
            $row .= '<div class = "col-sm-6">'. createBadge($durations) .'</div>';
            $row .= '</div>';
            $tdData .= $row;
            $tdData .= '<div>--------------------------</div>';
        }
        $tdData .= '</div>';
        $courseDuration = $tdData;
    }
    return $courseDuration;
}

function getName($id,$table,$column) {
    global $conn;
    $query = "SELECT $column FROM $table WHERE id = $id";
    $column_data = $conn->query($query);
    $column_data = mysqli_fetch_column($column_data);
    return $column_data;
}

function createColumnData($data,$table,$column) : string {
    global $conn;
    $columnValue = createBadge("For All");
    if(!empty($data)) {
        $ids = json_decode($data,true);
        $selectQuery = "SELECT $column FROM $table WHERE ID IN (". implode(',',$ids) .")";
        $column_data = $conn->query($selectQuery);
        $column_data = mysqli_fetch_all($column_data,MYSQLI_ASSOC);
        $column_names = array_column($column_data,$column);
        $column_names = array_map("createBadge",$column_names);
        $columnValue = implode("\n",$column_names);
    }
    return $columnValue;
}

function createBadge($value) {
    return '<div class="badge badge-primary mb-1 justify-content-center align-items-center">'. $value .'</div>';
}
?>

<style>
.topBody{
    margin: 1rem 1rem 1rem 2rem; 
}
</style>
<div class="topBody">
    <div class="d-flex justify-content-between align-items-center">
        <h5>Group Filter</h5>
        <button aria-label="" type="button" class="close" style="margin-top: 0.6rem;" data-dismiss="modal" aria-hidden="true"><i class="pg-icon">close</i>
        </button>
    </div>
</div>
<div class="modal-body">
    <table class="table">
        <thead class="thead-light">
            <?php if($notification_data['Send_To'] == 'student')  {  ?>
            <tr>
                <th class="col-sm-2">Scheme</th>
                <th class="col-sm-2">Admission Session</th>
                <th class="col-sm-5">Courses And Duration</th>
                <th class="col-sm-3">Student</th>
            </tr>
            <?php } else  { ?>
            <tr>
                <th class="col-sm-2">Center</th>
            </tr>
            <?php } ?>
        </thead>
        <tbody>
            <?php if($notification_data['Send_To'] == 'student')  {  ?>
            <tr>
                <td class="col-sm-2"><?=$scheme_column?></td>
                <td class="col-sm-2"><?=$admissionSession_column?></td>
                <td class="col-sm-2"><?=$courseAndDuration_column?></td>
                <td class="col-sm-2"><?=$student_column?></td>
            </tr>
            <?php } else  { ?>
            <tr>
                <th class="col-sm-2"><?=$center_column?></th>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>