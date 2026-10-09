<?php
  if(isset($_GET['university_id']) && isset($_GET['session_id']) && isset($_GET['admission_type_id']) && isset($_GET['center'])){
    require '../../includes/db-config.php';
    session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
    $university_id = intval($_GET['university_id']);
    $session_id = intval($_GET['session_id']);
    $admission_type_id = intval($_GET['admission_type_id']);
    $user_id = intval($_GET['center']);

    if(empty($admission_type_id)){
      echo '<option value="">Please configure Admission-Type</option>';
      exit();
    }

    if(!empty($_GET['form'])){
      $status_query = "";
    }else{
      $status_query = " AND Status = 1";
    }

    $scheme = $conn->query("SELECT Scheme FROM Admission_Sessions WHERE ID = $session_id");
    if($scheme->num_rows==0){
      echo '<option value="">Please configure Scheme</option>';
      exit();
    }

    $scheme = mysqli_fetch_assoc($scheme);
    $schemes = json_decode($scheme['Scheme'], true);
   
    $scheme_id = 0;
    foreach($schemes['dates'] as $schemeId=>$date){
      if(date("Y-m-d")>=$date){
        $scheme_id = $schemeId;
      }
    }
    
    // Scheme - SubCourses
    $allotedSubCoursesOnScheme = array();
    $schemeSubCourses = $conn->query("SELECT Sub_Course_ID FROM Scheme_Sub_Courses WHERE Scheme_ID = $scheme_id");
    while($schemeSubCourse = $schemeSubCourses->fetch_assoc()){
      $allotedSubCoursesOnScheme[] = $schemeSubCourse['Sub_Course_ID'];
    }

    $is_vocational = $conn->query("SELECT ID FROM Universities WHERE Course_Allotment = 1 AND ID = $university_id");
    if($is_vocational->num_rows>0){

      $subCourseIds = array();
      $sub_courses = $conn->query("SELECT Sub_Course_ID FROM User_Sub_Courses WHERE User_ID = $user_id AND University_ID = $university_id");
      while($sub_course = $sub_courses->fetch_assoc()){
        $subCourseIds[] = $sub_course['Sub_Course_ID'];
      }
      
    //   print_r($subCourseIds);
    //   print_r($allotedSubCoursesOnScheme);

      $subCourseIds = array_intersect($subCourseIds, $allotedSubCoursesOnScheme);
      
      $ids = array();
      $sub_courses = $conn->query("SELECT ID, Admission_Type FROM Sub_Courses WHERE ID IN (".implode(",", $subCourseIds).")");
      while($sub_course = $sub_courses->fetch_assoc()){
        $admissionTypes = json_decode($sub_course['Admission_Type'], true);
        if(in_array($admission_type_id, array_keys($admissionTypes))){
          $ids[] = $sub_course['ID'];
        }
      }

      $course_ids = $conn->query("SELECT DISTINCT Course_ID as Course_ID FROM Sub_Courses WHERE ID IN (".implode(",", $ids).") AND University_ID = $university_id");
    }else{
      $ids = array();
      $sub_courses = $conn->query("SELECT ID, Admission_Type FROM Sub_Courses WHERE University_ID = $university_id");
      while($sub_course = $sub_courses->fetch_assoc()){
        $admissionTypes = json_decode($sub_course['Admission_Type'], true);
        if(in_array($admission_type_id, array_keys($admissionTypes))){
          $ids[] = $sub_course['ID'];
        }
      }

      $course_ids = $conn->query("SELECT Course_ID FROM Sub_Courses WHERE ID IN (".implode(",", $ids).") AND University_ID = $university_id $status_query GROUP BY Course_ID");
    }
    
    if($course_ids->num_rows==0){
      echo '<option value="">Please configure Academics</option>';
      exit();
    }

    $ids = array();
    while($course_id = $course_ids->fetch_assoc()){
      $ids[] = $course_id['Course_ID'];
    }
    
    $courses = $conn->query("SELECT Courses.ID, Courses.Name FROM Courses WHERE ID IN (".implode(',', $ids).")");
    //print_r($courses->fetch_assoc());die;
    while($course = $courses->fetch_assoc()){
        /*if($_REQUEST['university_id']==85 || $_REQUEST['university_id']==68){
            if($_REQUEST['session_id']== 52){
                // print_r(strpos($course['Name'],"Diploma"));
               if (strpos($course['Name'], 'Diploma') !== false) {
                    continue;
                } 
            }
         
        }*/
      echo '<option value="'.$course['ID'].'">'.$course['Name'].'</option>';  
    }
  }
