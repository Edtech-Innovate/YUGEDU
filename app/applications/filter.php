<?php
if (isset($_POST['id']) && isset($_POST['by'])) {
    session_start();
    require '../../includes/db-config.php';
    //  echo('<pre>');print_r($_POST);die;
    $by = $_POST['by'];
    $id = intval($_POST['id']);
    $sub_center_name = "";
    $option ='';
    if ($by == 'departments') {
        $courseIds = $conn->query("SELECT GROUP_CONCAT(ID) as ID FROM Courses WHERE Course_Type_ID = $id AND University_ID = " . $_SESSION['university_id']);
        
        if ($courseIds->num_rows > 0) {
            $courseIds = $courseIds->fetch_assoc();
            // print_r($courseIds);die;
            $courseIds = $courseIds['ID'];
            
            $_SESSION['filterByDepartment'] = !empty($courseIds) ? " AND Students.Course_ID IN ($courseIds)" : " AND Students.ID IS NULL";
        } else {
            $_SESSION['filterByDepartment'] = " AND Students.ID IS NULL";
        }
    } elseif ($by == 'sub_courses') {
        $_SESSION['filterBySubCourses'] = " AND Students.Sub_Course_ID = $id";
    } elseif ($by == 'date') {
        $startDate = date("Y-m-d 00:00:00", strtotime($_POST['startDate']));
        $endDate = date("Y-m-d 23:59:59", strtotime($_POST['endDate']));
        $_SESSION['filterByDate'] = " AND Students.Created_At BETWEEN '$startDate' AND '$endDate'";
    } elseif ($by == 'processdate') {
        $startProcessDate = date("Y-m-d 00:00:00", strtotime($_POST['startProcessDate']));
        $endProcessDate = date("Y-m-d 23:59:59", strtotime($_POST['endProcessDate']));
        $_SESSION['filterByProcessDate'] = " AND Students.Process_By_Center BETWEEN '$startDate' AND '$endDate'";
    } elseif ($by == 'application_status') {
        if ($id == 1) {
            $_SESSION['filterByStatus'] = " AND Document_Verified IS NOT NULL ";
        } elseif ($id == 2) {
            $_SESSION['filterByStatus'] = " AND Payment_Received IS NOT NULL ";
        } elseif ($id == 3) {
            $_SESSION['filterByStatus'] = " AND Document_Verified IS NOT NULL AND Payment_Received IS NOT NULL ";
        }
    } elseif ($by == "users") {
        $check = $conn->query("SELECT ID, Role, CanCreateSubCenter FROM Users WHERE ID = $id");
        if ($check->num_rows > 0) {
            $user = $check->fetch_assoc();
            // echo "<pre>"; print_r($user);die;
            $role_query = " AND Students.Added_For = $id";

            if (strtolower($user['Role']) == 'center') { // center
                // if ($user['CanCreateSubCenter'] == 1) {
                    $subCenter = $conn->query("SELECT * FROM Center_SubCenter WHERE Center=$id");
                    if ($subCenter->num_rows > 0) {
                        while ($row = $subCenter->fetch_assoc()) {
                            $sub_center_name .= $row['Sub_Center'] . ", ";
                        }
                        $sub_center_name = rtrim($sub_center_name, ", "); // sub_center_ids

                        // start sub_center option 
                        $getSubCourseName = $conn->query("SELECT ID, Name FROM Users WHERE ID IN ($sub_center_name) AND Status = 1");
                        if ($getSubCourseName->num_rows == 0) {
                            $option = "<option value=''>No Sub-Center Found</option>";
                        }
                        $option = "<option value=''>Choose Sub-Center</option>";
                        while ($row = $getSubCourseName->fetch_assoc()) {
                            $option .= "<option value='" . $row['ID'] . "'>" . ucwords(strtolower($row['Name'])) . "</option>";
                        }
                         // end sub center option 
                         
                         $centersubcenterids = $sub_center_name.','.$id; // center and sub-center ids

                    // }
                }

                
              if(!empty($centersubcenterids)){
              $role_query = " AND Students.Added_For IN ($centersubcenterids)";
              }

            }
            $_SESSION['filterByUser'] = $role_query;
        }
    }
    
    elseif ($by == 'vartical_type') {
        $center_id_arr = [];

        $vartical_type_sql = $conn->query("SELECT ID FROM Users WHERE Vertical='$id' AND Status=1");
        while ($row = $vartical_type_sql->fetch_array()) {
            $center_id_arr[] = $row['ID'];
        }

        if (!empty($center_id_arr)) {
            $center_ids = implode(',', $center_id_arr);
            $_SESSION['filterByVerticalType'] = " AND Students.Added_For IN ($center_ids)";
        } else {
            // If no matching users found, make filter return 0 records
            $_SESSION['filterByVerticalType'] = " AND Students.ID IS NULL";
        }

        unset($_SESSION['filterByUser']);
        
    }elseif($by == 'duration'){
    $university_id = $_SESSION['university_id'];
    $durationWhere = "";

    if ($id == "3" || $id == "6") {
        $durationWhere = "  AND Sub_Courses.Name LIKE '%$id%'";
    } else if ($id == "1") {
        // echo('hello');die;
        $durationWhere = "  AND Sub_Courses.Name LIKE '%11%' AND Sub_Courses.Name LIKE '%Adv%'";
    } else if ($id == "2") {   
        $durationWhere = "   AND Sub_Courses.Name LIKE '%11%' AND Sub_Courses.Name not LIKE '%Adv%' ";
    }
    $_SESSION['filterByDuration']=$durationWhere;
    // echo('<pre>');print_r($durationWhere);die;
    // $durationQuery=$conn->query("SELECT * FROM Sub_Courses $durationWhere");
    
    // while($duration = $durationQuery->fetch_assoc()){
    //     echo('<pre>');
    //     print_r($duration);
    // }
}
    // elseif($by=='duration'){
    //  $university_id = $_SESSION['university_id'];
    //  $durationWhere = "";
    //  if ($id == "3" || $id == "6") {
    //  $durationWhere = "WHERE University_ID = '$university_id' AND NAME LIKE '%$id%'";
    //  } elseif ($id == "11adv") {
    //  $durationWhere = "WHERE University_ID = '$university_id' AND NAME LIKE '%11%' AND NAME LIKE '%Adv%'";
    //  } else() {
    //  $durationWhere = "WHERE University_ID = '$university_id' AND NAME LIKE '%$id%'";
    //  }
    // echo("SELECT * FROM Sub_Courses $durationWhere");die;
    //  while($duration=$durationQuery->fetch_assoc()){
    //  echo('<pre>');print_r($duration);}
    // }
    echo json_encode(['status' => true, 'subCenterName' => $option]);
}
