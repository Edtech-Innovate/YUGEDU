<?php 

## Database configuration
include '../../includes/db-config.php';
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$course_name = "";
$durationsDropDown = getDuration();

function getDuration() {
    global $conn;
    global $course_name;
    $course_id = mysqli_real_escape_string($conn,$_REQUEST['course_id']);
    $option = '<option value="">Select Duration</option>';
  
        $duration = $conn->query("SELECT CONCAT(Sub_Courses.Name,'(',Courses.Short_Name,')') as Name  , MAX(CAST(TRIM('\"' FROM Sub_Courses.Min_Duration) AS int)) as `maxDuration` FROM Sub_Courses  LEFT JOIN Courses on Courses.ID = Sub_Courses.Course_ID WHERE 1=1 AND Sub_Courses.ID = '$course_id'");        
        $duration = mysqli_fetch_assoc($duration);
        $course_name = $duration['Name'];
        $maxDuration = $duration['maxDuration'];
        $option .= createOptionTag($option,$maxDuration,1);
  
    return $option;
}

function createOptionTag($option,$maxDuration,$currentDuration) {
    if($currentDuration <= $maxDuration) {
        $option .= '<option value="'.$currentDuration.'">'.$currentDuration.'</option>';
        return createOptionTag($option,$maxDuration,++$currentDuration);
    } else {
        return $option;
    }
}

?>

<div class="row" id="duration_center_<?=$_REQUEST['course_id']?>">
    <div class="col-md-12">
        <div class="form-group form-group-default">
            <label>Duration/Semester for <?=$course_name?></label>
            <select class="full-width" style="border: transparent;" id="duration_<?=$_REQUEST['course_id']?>" data-init-plugin="select2" name="duration_<?=$_REQUEST['course_id']?>[]" multiple onchange="getDurationSelectedData(this.id)">
                <?=$durationsDropDown?>
            </select>
        </div>
    </div>
</div>
