<?php
if (isset($_POST['type'])) {
    ini_set('display_errors', 1);
    session_start();
    require '../../includes/db-config.php';
    $type = $_POST['type'];
 
    $sub_course_id = isset($_POST['sub_course_id']) ? $_POST['sub_course_id']: "";
    $duration = isset($_POST['duration']) ? $_POST['duration'] :  "";
    $subject_id = isset($_POST['subject_id']) ? $_POST['subject_id'] : "";
    $chapter_id = isset($_POST['chapter_id'])?$_POST['chapter_id'] : "";
    $topic_id = isset($_POST['topic_id'])?$_POST['topic_id'] : "";
    $unit_id = isset($_POST['unit_id'])?$_POST['unit_id'] : "";

    if ($type === "chapter") { // chapter option

        // $getChapter = $conn->query("SELECT ID, CONCAT(Name, ' (', Code, ')') AS ChapterName FROM Chapter WHERE Sub_Course_ID = $sub_course_id AND Semester = '$duration' AND Subject_ID = $subject_id");
         $getChapter = $conn->query("SELECT ID, CONCAT(Name, ' (', Code, ')') AS ChapterName FROM Chapter WHERE Sub_Course_ID = $sub_course_id AND Semester = '$duration' AND Subject_ID = $subject_id  
                      AND TRIM(Name) != ''  -- ignore blank names
");
        
        if ($getChapter->num_rows == 0) {
            echo '<option value="">No Unit Found! </option>';
            exit();
        }
        $html = '<option value="">Select Unit</option>';
        while ($row = $getChapter->fetch_assoc()) {
            $html = $html . '<option value="' . $row['ID'] . '">' . $row['ChapterName'] . '</option>';
        }
        echo $html;
    } else if ($type === "unit") { // unit option 
        
        $getUnit = $conn->query("SELECT ID, Name FROM Chapter_Units WHERE Chapter_ID = $chapter_id");
        if ($getUnit->num_rows == 0) {
            echo '<option value="">No Module Found! </option>';
          exit();
            
        }
        $html = '<option value="">Select Module</option>';
        while ($row = $getUnit->fetch_assoc()) {
            $html = $html . '<option value="' . $row['ID'] . '">' . $row['Name'] . '</option>';
        }
        echo $html;
    } else if ($type === "topic") { // topic option 

        $getUnit = $conn->query("SELECT ID, Name FROM Chapter_Units_Topics WHERE Chapter_ID = $chapter_id AND Unit_ID = $unit_id ");
        if ($getUnit->num_rows == 0) {
            echo '<option value="">No Topic Found! </option>';
        }
        $html = '<option value="">Select Topic</option>';
        while ($row = $getUnit->fetch_assoc()) {
            $html = $html . '<option value="' . $row['ID'] . '">' . $row['Name'] . '</option>';
        }
        echo $html;
    } else if ($type === "sub_topic") { // sub topic option 

        $getUnit = $conn->query("SELECT ID, Name FROM chapter_units_sub_topics WHERE Chapter_ID = $chapter_id AND Unit_ID = $unit_id ");
        if ($getUnit->num_rows == 0) {
            echo '<option value="">No Sub-Topic Found! </option>';
        }
        $html = '<option value="">Select  Sub-Topic</option>';
        while ($row = $getUnit->fetch_assoc()) {
            $html = $html . '<option value="' . $row['ID'] . '">' . $row['Name'] . '</option>';
        }
        echo $html;
    }

}

?>