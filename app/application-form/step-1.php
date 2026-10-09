<?php
if (isset($_POST['center'])) {
    require '../../includes/db-config.php';
    session_start();
    
    // Error reporting for debugging
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    $id = array_key_exists('inserted_id', $_POST) ? intval($_POST['inserted_id']) : 0;

    // Required fields validation
    $center = intval($_POST['center']);
    $admission_session = intval($_POST['admission_session']);
    $admission_type = intval($_POST['admission_type']);
    $course = intval($_POST['course']);
    $sub_course = intval($_POST['sub_course']);
    $duration = intval($_POST['duration']);

    if (empty($center) || empty($admission_session) || empty($admission_type) || empty($course) || empty($sub_course) || empty($duration)) {
        exit(json_encode(['status' => 400, 'message' => 'All fields are required']));
    }

    // --- Nullable Fields Handling ---
    // If empty, maps to the raw keyword NULL. If filled, wraps securely in single quotes.
    $abcid = (!empty($_POST['abc_id'])) ? "'" . mysqli_real_escape_string($conn, $_POST['abc_id']) . "'" : "NULL";
    $debid = (!empty($_POST['deb_id'])) ? "'" . mysqli_real_escape_string($conn, $_POST['deb_id']) . "'" : "NULL";

    // --- Name Splitting & Formatting Logic ---
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $full_name = str_replace('  ', ' ', $full_name);
    $full_name = explode(' ', $full_name, 3);
    $count = count($full_name);

    if ($count == 2) {
        $first_name = strtoupper(trim($full_name[0]));
        $middle_name = "NULL"; 
        $last_name = "'" . mysqli_real_escape_string($conn, strtoupper(trim($full_name[1]))) . "'";
    } elseif ($count > 2) {
        $first_name = strtoupper(trim($full_name[0]));
        $middle_name = "'" . mysqli_real_escape_string($conn, strtoupper(trim($full_name[1]))) . "'";
        $last_name = "'" . mysqli_real_escape_string($conn, strtoupper(trim($full_name[2]))) . "'";
    } else {
        $first_name = strtoupper(trim($full_name[0]));
        $middle_name = "NULL"; 
        $last_name = "''"; // FIX: Empty string prevents 'Column cannot be null' database error
    }
    $first_name = "'" . mysqli_real_escape_string($conn, $first_name) . "'";

    // Session-based duration overrides
    if ($_SESSION['university_id'] == 20 && $admission_session == 70) {
        $duration = 6;
    }    
    if ($admission_session == 73) {
        $duration = 2;
    }        

    $father_name = mysqli_real_escape_string($conn, strtoupper($_POST['father_name']));
    $mother_name = mysqli_real_escape_string($conn, strtoupper($_POST['mother_name']));

    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $dob = date('Y-m-d', strtotime($dob));

    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $employment_status = mysqli_real_escape_string($conn, $_POST['employment_status']);
    $marital_status = mysqli_real_escape_string($conn, $_POST['marital_status']);
    $religion = mysqli_real_escape_string($conn, $_POST['religion']);
    $aadhar = mysqli_real_escape_string($conn, $_POST['aadhar']);
    $nationality = mysqli_real_escape_string($conn, $_POST['nationality']);

    // Initialize lead_id securely
    $lead_id = (!empty($_POST['lead_id'])) ? intval($_POST['lead_id']) : 'NULL';

    if (!empty($id)) {
        // --- UPDATE EXISTING STUDENT ---
        $previous_course_id = null;
        $should_update_ledger = false;

        $prevStudentQuery = $conn->query("SELECT Course_ID, Admission_Session_ID, Added_For, University_ID FROM Students WHERE ID = $id");
        if ($prevStudentQuery && $prevStudentQuery->num_rows > 0) {
            $prevData = $prevStudentQuery->fetch_assoc();
            $previous_course_id = $prevData['Course_ID'];

            if (
                (int)$prevData['Admission_Session_ID'] !== $admission_session ||
                (int)$prevData['Added_For']            !== $center ||
                (int)$prevData['Course_ID']            !== $course ||
                (int)$prevData['University_ID']        !== intval($_SESSION['university_id'])
            ) {
                $should_update_ledger = true;
            }
        }

        $add_student = $conn->query("UPDATE Students SET Added_For = '$center', deb_id = $debid, abc_id = $abcid, Admission_Type_ID = $admission_type, Admission_Session_ID = $admission_session, Course_ID = $course, Sub_Course_ID = $sub_course, Duration = $duration, First_Name = $first_name, Middle_Name = $middle_name, Last_Name = $last_name, Father_Name = '$father_name', Mother_Name = '$mother_name', DOB = '$dob', Aadhar_Number = '$aadhar', Category = '$category', Gender = '$gender', Nationality = '$nationality', Employement_Status = '$employment_status', Marital_Status = '$marital_status', Religion = '$religion' WHERE ID = $id");
        
        if ($add_student) {
            if ($should_update_ledger) {
                generateStudentLedger($conn, $id, $previous_course_id);
            }
            echo json_encode(['status' => 200, 'message' => 'Step 1 details saved successfully!', 'id' => $id]);
        } else {
            echo json_encode(['status' => 400, 'message' => 'Something went wrong during update!']);
        }
    } else {
        // --- INSERT NEW STUDENT ---
        $aadhar_check = $conn->query("SELECT ID FROM Students WHERE Aadhar_Number = '$aadhar' AND University_ID = " . intval($_SESSION['university_id']));
        if ($aadhar_check->num_rows > 0) {
            exit(json_encode(['status' => 400, 'message' => 'Aadhar number already exists!']));
        }

        $student_check = $conn->query("SELECT ID FROM Students WHERE First_Name = $first_name AND Father_Name = '$father_name' AND Mother_Name = '$mother_name' AND DOB = '$dob' AND University_ID = " . intval($_SESSION['university_id']) . " AND Course_ID = $course AND Added_For = $center");
        if ($student_check->num_rows > 0) {
            exit(json_encode(['status' => 400, 'message' => 'Student with same details already exists!']));
        }

        $added_by = $_SESSION['Role'] == 'Student' ? $center : intval($_SESSION['ID']);

        $add_student = $conn->query("INSERT INTO Students (Added_By, Added_For, Lead_Status_ID, University_ID, Admission_Type_ID, Admission_Session_ID, Course_ID, Sub_Course_ID, Duration, Admission_Duration, First_Name, Middle_Name, Last_Name, Father_Name, Mother_Name, DOB, Aadhar_Number, Category, Gender, Nationality, Employement_Status, Marital_Status, Religion, Step) VALUES(" . $added_by . ", $center, $lead_id, " . intval($_SESSION['university_id']) . ", $admission_type, $admission_session, $course, $sub_course, $duration, $duration, $first_name, $middle_name, $last_name, '$father_name', '$mother_name', '$dob', '$aadhar', '$category', '$gender', '$nationality', '$employment_status', '$marital_status', '$religion', 1)");
        
        if ($add_student) {
            $student_id = $conn->insert_id;
            
            if ($lead_id == 'NULL') {
                $has_unique_student_id = $conn->query("SELECT ID_Suffix, Max_Character FROM Universities WHERE ID = " . intval($_SESSION['university_id']) . " AND Has_Unique_StudentID = 1");
                if ($has_unique_student_id->num_rows > 0) {
                    $has_unique_student_id = $has_unique_student_id->fetch_assoc();
                    $suffix = $has_unique_student_id['ID_Suffix'];
                    $characters = $has_unique_student_id['Max_Character'];
                    $unique_id = generateStudentID($conn, $suffix, $characters, $_SESSION['university_id']);
                    
                    // Unified assignment for unique_id, abc_id, and deb_id
                    $conn->query("UPDATE Students SET Unique_ID = '$unique_id', abc_id = $abcid, deb_id = $debid WHERE ID = $student_id");
                }
            } else {
                $unique_id = $conn->query("SELECT Unique_ID FROM Lead_Status WHERE ID = $lead_id");
                $unique_id = $unique_id->fetch_assoc();
                
                // Unified assignment for unique_id, abc_id, and deb_id
                $conn->query("UPDATE Students SET Unique_ID = '" . $unique_id['Unique_ID'] . "', abc_id = $abcid, deb_id = $debid WHERE ID = $student_id");

                $final_stage = $conn->query("SELECT ID FROM Stages WHERE Name = 'Step 1 Completed'");
                if ($final_stage->num_rows > 0) {
                    $final_stage = $final_stage->fetch_assoc();
                    $final_stage = $final_stage['ID'];
                } else {
                    $conn->query("INSERT INTO Stages (`Name`, Is_Last) VALUES ('Step 1 Completed', 1)");
                    $final_stage = $conn->insert_id;
                }

                $conn->query("UPDATE Lead_Status SET Admission = 1, Stage_ID = $final_stage, Reason_ID = NULL WHERE ID = $lead_id");
            }
            generateStudentLedger($conn, $student_id);
            echo json_encode(['status' => 200, 'message' => 'Step 1 details saved successfully!', 'id' => $student_id]);
        } else {
            echo json_encode(['status' => 400, 'message' => 'Something went wrong during insertion!']);
        }
    }
}
?>