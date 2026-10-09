<?php
ini_set('display_errors', 1);
session_start();

require '../../includes/db-config.php';
require '../../extras/vendor/shuchkin/simplexlsxgen/src/SimpleXLSXGen.php';

$university_id = (int)($_SESSION['university_id'] ?? 0);
$role = $_SESSION['Role'] ?? '';

$search_value = '';
if (isset($_GET['search'])) {
    $search_value = mysqli_real_escape_string($conn, $_GET['search']);
}

// Session Query Handling
if (isset($_SESSION['current_session'])) {
    if ($_SESSION['current_session'] == 'All') {
        $session_query = '';
    } else {
        $current_session = mysqli_real_escape_string(
            $conn,
            $_SESSION['current_session']
        );
        $session_query = " AND Admission_Sessions.Name LIKE '%$current_session%'";
    }
} else {
    $get_current_session = $conn->query("
        SELECT Name
        FROM Admission_Sessions
        WHERE Current_Status = 1
          AND University_ID = $university_id
    ");

    if ($get_current_session && $get_current_session->num_rows > 0) {
        $gsc = $get_current_session->fetch_assoc();
        $current_session = mysqli_real_escape_string($conn, $gsc['Name']);
        $session_query = " AND Admission_Sessions.Name LIKE '%$current_session%'";
    } else {
        $session_query = '';
    }
}

// Role Query Handling
$role_query = str_replace(
    '{{ table }}',
    'Students',
    $_SESSION['RoleQuery'] ?? ''
);
$role_query = str_replace('{{ column }}', 'Added_For', $role_query);

// Step Query Handling
$step_query = '';

if (isset($_GET['steps_found'])) {
    $stepsfound = mysqli_real_escape_string($conn, $_GET['steps_found']);

    if ($stepsfound == 'not_processed') {
        $step_query = ' AND Students.Step = 4 AND Students.Process_By_Center IS NULL';
    } elseif ($stepsfound == 'ready_for_verification') {
        $step_query = ' AND Students.Process_By_Center IS NOT NULL AND (Students.Document_Verified IS NULL OR Students.Payment_Received IS NULL)';
    } elseif ($stepsfound == 'verified') {
        $step_query = ' AND Students.Document_Verified IS NOT NULL AND Students.Processed_To_University IS NULL';
    } elseif ($stepsfound == 'proccessed_to_university') {
        $step_query = ' AND Students.Processed_To_University IS NOT NULL AND Students.Enrollment_No IS NULL';
    } elseif ($stepsfound == 'enrolled') {
        $step_query = ' AND Students.Enrollment_No IS NOT NULL';
    }
}

// Base Headers
$header = array(
    'Student_ID',
    'Enrollment_No',
    'OA_Number',
    'Step',
    'Added On',
    'Processed By Center',
    'Processed To University',
    'Student Name',
    'Father Name',
    'Mother Name',
    'Adm Type',
    'Session',
    'Duration',
    'Mode',
    'Course',
    'Sub Course',
    'Short Name',
    'Email',
    'Contact',
    'Alternate Email',
    'Alternate Contact',
    'Aadhar Number',
    'DOB',
    'Employement Status',
    'Gender',
    'Category',
    'Address',
    'City',
    'District',
    'State',
    'Pincode',
    'Nationality',
    'High School',
    'Subject',
    'Year',
    'Board/Institute',
    'Marks Obtained',
    'Maximum Marks',
    'Total marks%',
    'Intermediate',
    'Subject',
    'Year',
    'Board/Institute',
    'Marks Obtained',
    'Maximum Marks',
    'Total marks%',
    'UG',
    'Subject',
    'Year',
    'Board/Institute',
    'Marks Obtained',
    'Maximum Marks',
    'Total marks%',
    'PG',
    'Subject',
    'Year',
    'Board/Institute',
    'Marks Obtained',
    'Maximum Marks',
    'Total marks%',
    'Other',
    'Subject',
    'Year',
    'Board/Institute',
    'Marks Obtained',
    'Maximum Marks',
    'Total marks%',
    'Code',
    'Center Name',
    'RM',
    'Export Documents'
);

// Get Fee Structures Once
$fee_structure_list = array();

if ($role != 'Sub-Center') {
    $fee_structures = $conn->query("
        SELECT ID, Name
        FROM Fee_Structures
        WHERE University_ID = $university_id
        ORDER BY Fee_Applicable_ID
    ");

    if ($fee_structures) {
        while ($fee_structure = $fee_structures->fetch_assoc()) {
            $fee_structure_list[] = $fee_structure;
        }
    }

    // Required Fee Column Order
    $header[] = 'Actual Course Fee';
    $header[] = 'Center Fee Sharing(in %)';

    foreach ($fee_structure_list as $fee_structure) {
        $header[] = $fee_structure['Name'];
    }

    $header[] = 'Total Fee(With Sharing)';

    if (in_array($role, array('Administrator', 'Center'), true)) {
        $header[] = 'Total Fee(Without Sharing)';
    }

    if ($role == 'Administrator') {
        $header[] = 'Our Total Fee';
        $header[] = 'Our Fee Sharing(in %)';
        $header[] = 'University Fee Course(Current)';
    }

    $header[] = 'ABC ID';
    $header[] = 'DEB ID';
}

// Search Query Construction
$searchQuery = '';

if ($search_value != '') {
    if (strpos($search_value, '=') !== false) {
        $search = explode('=', $search_value, 2);
        $searchBy = trim($search[0]);

        $values = isset($search[1]) && $search[1] !== ''
            ? array_values(array_filter(explode(' ', $search[1]), 'strlen'))
            : array();

        if (!empty($values)) {
            $student_id_column = (($_SESSION['student_id'] ?? 0) == 1)
                ? 'Students.Unique_ID'
                : "RIGHT(CONCAT('000000', Students.ID), 6)";

            $column = '';

            if (strcasecmp($searchBy, 'student id') == 0) {
                $column = $student_id_column;
            } elseif (strcasecmp($searchBy, 'enrollment') == 0) {
                $column = 'Students.Enrollment_No';
            } elseif (strcasecmp($searchBy, 'oa number') == 0) {
                $column = 'Students.OA_Number';
            }

            if ($column != '') {
                $safe_values = array();

                foreach ($values as $value) {
                    $safe_values[] = "'" .
                        mysqli_real_escape_string($conn, $value) . "'";
                }

                $searchQuery = " AND $column IN (" .
                    implode(',', $safe_values) . ")";
            }
        }
    } elseif (strcasecmp($search_value, 'completed') == 0) {
        $searchQuery = ' AND Students.Step = 4';
    } else {
        $searchQuery = "
            AND (
                Students.ID LIKE '%$search_value%'
                OR Students.Unique_ID LIKE '%$search_value%'
                OR Students.First_Name LIKE '%$search_value%'
                OR Students.Middle_Name LIKE '%$search_value%'
                OR Students.Last_Name LIKE '%$search_value%'
                OR Admission_Sessions.Name LIKE '%$search_value%'
                OR Admission_Types.Name LIKE '%$search_value%'
                OR Students.Step LIKE '%$search_value%'
                OR Students.Father_Name LIKE '%$search_value%'
                OR Students.Email LIKE '%$search_value%'
                OR Students.Contact LIKE '%$search_value%'
                OR Sub_Courses.Short_Name LIKE '%$search_value%'
            )
        ";
    }
}

// Existing Filters
$searchQuery .=
    ($_SESSION['filterByDepartment'] ?? '') .
    ($_SESSION['filterByUser'] ?? '') .
    ($_SESSION['filterByDate'] ?? '') .
    ($_SESSION['filterBySubCourses'] ?? '') .
    ($_SESSION['filterByStatus'] ?? '') .
    ($_SESSION['filterByVerticalType'] ?? '');

// Fetch Student Records
$result_record = "
    SELECT
        Students.ID,
        Students.Unique_ID,
        Students.Enrollment_No,
        Students.OA_Number,
        Students.Step,
        Students.Created_At,
        Students.Process_By_Center,
        Students.Processed_To_University,
        CONCAT(
            Students.First_Name,
            IF(Students.Middle_Name != '', CONCAT(' ', Students.Middle_Name), ''),
            ' ',
            Students.Last_Name
        ) AS Name,
        Students.Father_Name,
        Students.Mother_Name,
        Admission_Types.Name AS Adm_Type,
        Admission_Sessions.Name AS Session,
        Students.Duration,
        Modes.Name AS Mode,
        Courses.Name AS Course,
        Sub_Courses.Name AS Sub_Course,
        Sub_Courses.Short_Name AS Short_Name,
        Students.Email,
        Students.Contact,
        Students.Alternate_Email,
        Students.Alternate_Contact,
        Students.Aadhar_Number,
        Students.DOB,
        Students.Employement_Status,
        Students.Gender,
        Students.Category,
        REPLACE(JSON_EXTRACT(Students.Address, '$.present_address'), '\"', '') AS Address,
        REPLACE(JSON_EXTRACT(Students.Address, '$.present_city'), '\"', '') AS City,
        REPLACE(JSON_EXTRACT(Students.Address, '$.present_district'), '\"', '') AS District,
        REPLACE(JSON_EXTRACT(Students.Address, '$.present_state'), '\"', '') AS State,
        REPLACE(JSON_EXTRACT(Students.Address, '$.present_pincode'), '\"', '') AS Pincode,
        Students.Nationality,
        Students.Added_For,
        Students.abc_id,
        Students.deb_id,
        Students.Sub_Course_ID AS subcourseid,
        Students.Admission_Session_ID AS admsess
    FROM Students
    LEFT JOIN Admission_Types
        ON Students.Admission_Type_ID = Admission_Types.ID
    LEFT JOIN Admission_Sessions
        ON Students.Admission_Session_ID = Admission_Sessions.ID
    LEFT JOIN Modes
        ON Students.Mode_ID = Modes.ID
    LEFT JOIN Courses
        ON Students.Course_ID = Courses.ID
    LEFT JOIN Sub_Courses
        ON Students.Sub_Course_ID = Sub_Courses.ID
    WHERE Students.University_ID = $university_id
        $searchQuery
        $role_query
        $step_query
        $session_query
    ORDER BY Students.ID DESC
";

$empRecords = mysqli_query($conn, $result_record);

if (!$empRecords) {
    die('Student query failed: ' . mysqli_error($conn));
}

$data = array();
$data[] = $header;

// Process Students
while ($row = mysqli_fetch_row($empRecords)) {
    $abcID = $row[34] ?? '';
    $debID = $row[35] ?? '';
    $userid = (int)($row[33] ?? 0);
    $studentid = (int)$row[0];
    $subcourseid = (int)($row[36] ?? 0);
    $student_duration = $row[13] ?? '';

    unset($row[36], $row[37]);

    // Center / Sub-Center Lookup
    if ($role == 'Center') {
        $user1 = $conn->query("
            SELECT ID, Code, Name
            FROM Users
            WHERE ID = $userid
        ");
    } else {
        $user1 = $conn->query("
            SELECT ID, Code, Name
            FROM Users
            WHERE ID = $userid
              AND Role = 'Center'
        ");

        if ($user1 && $user1->num_rows == 0) {
            $user1 = $conn->query("
                SELECT Users.ID, Users.Code, Users.Name
                FROM Users
                LEFT JOIN Center_SubCenter
                    ON Users.ID = Center_SubCenter.Center
                WHERE Center_SubCenter.Sub_Center = $userid
            ");
        }
    }

    if ($user1 && $user1->num_rows > 0) {
        $user = $user1->fetch_assoc();
    } else {
        $user = array(
            'Name' => '',
            'Code' => '',
            'ID' => 0
        );
    }

    // Reporting Manager
    $rm = array('Name' => '');

    if (!empty($user['ID']) || ($_SESSION['Designation'] ?? '') != 'University') {
        $reporting_user_id = (int)$user['ID'];

        $rm_query = $conn->query("
            SELECT CONCAT(Users.Name, ' (', Users.Code, ')') AS Name
            FROM University_User
            LEFT JOIN Users
                ON University_User.Reporting = Users.ID
               AND University_User.University_ID = $university_id
            WHERE University_User.User_ID = $reporting_user_id
              AND University_User.University_ID = $university_id
        ");

        if ($rm_query && $rm_query->num_rows > 0) {
            $rm = $rm_query->fetch_assoc();
        } else {
            $rm = $user;
        }
    }

    unset($row[34], $row[35]);

    // Academic Records
    foreach (array('High School', 'Intermediate', 'UG', 'PG', 'Other') as $academic_type) {
        $type_safe = mysqli_real_escape_string($conn, $academic_type);

        $academics = $conn->query("
            SELECT Type, Subject, `Year`, `Board/Institute`,
                   Marks_Obtained, Max_Marks, Total_Marks
            FROM Student_Academics
            WHERE Student_ID = $studentid
              AND Type = '$type_safe'
            LIMIT 1
        ");

        if ($academics && $academics->num_rows > 0) {
            $academic = $academics->fetch_row();
        } else {
            $academic = array($academic_type, '', '', '', '', '', '');
        }

        $row = array_merge($row, $academic);
    }

    $row[] = $user['Code'];
    $row[] = $user['Name'];
    $row[] = $rm['Name'] ?? '';

    // Format Dates
    $row[4] = $row[4] ?: '';
    $row[5] = !empty($row[5]) ? date('d-m-Y H:i A', strtotime($row[5])) : '';
    $row[6] = !empty($row[6]) ? date('d-m-Y H:i A', strtotime($row[6])) : '';

    // Export Documents
    $encode = base64_encode($row[0] . 'W1Ebt1IhGN3ZOLplom9I');

    $row[] = '<i><a href="https://' . $_SERVER['HTTP_HOST'] .
        '/app/applications/zip?id=' . $encode . '">Click Here</a></i>';

    // Fee Calculations: All Except Sub-Center
    if ($role != 'Sub-Center') {
        $duration_safe = mysqli_real_escape_string(
            $conn,
            (string)$student_duration
        );

        $ledgers_query = $conn->query("
            SELECT Fee, Fee_Without_Sharing
            FROM Student_Ledgers
            WHERE Student_ID = $studentid
              AND Status = 1
              AND Type = 1
              AND Duration = '$duration_safe'
        ");

        $total_calculated_fee = 0;
        $total_without_sharing = 0;
        $combined_fees = array();

        if ($ledgers_query && $ledgers_query->num_rows > 0) {
            while ($ledger_row = $ledgers_query->fetch_assoc()) {
                $fee_arr = json_decode($ledger_row['Fee'], true);

                if (is_array($fee_arr)) {
                    foreach ($fee_arr as $fee_key => $fee_val) {
                        $combined_fees[$fee_key] =
                            ($combined_fees[$fee_key] ?? 0) + (float)$fee_val;

                        $total_calculated_fee += (float)$fee_val;
                    }
                }

                $fee_arr_without_sharing = json_decode(
                    $ledger_row['Fee_Without_Sharing'],
                    true
                );

                if (is_array($fee_arr_without_sharing)) {
                    foreach ($fee_arr_without_sharing as $fee_val) {
                        $total_without_sharing += (float)$fee_val;
                    }
                }
            }
        }

        // Actual Course Fee from Fee_Constant
        $actual_course_fee = 0;

        if ($subcourseid > 0) {
            $actual_fee_query = $conn->query("
                SELECT Fee
                FROM Fee_Constant
                WHERE Sub_Course_ID = $subcourseid
                  AND University_ID = $university_id
                LIMIT 1
            ");

            if ($actual_fee_query && $actual_fee_query->num_rows > 0) {
                $actual_fee_row = $actual_fee_query->fetch_assoc();
                $actual_course_fee = $actual_fee_row['Fee'] ?? 0;
            }
        }

        // Center Fee Sharing
        $feeSharing = 0;

        $totalFee = $conn->query("
            SELECT Fee
            FROM Fee_Variables
            WHERE Code = $userid
              AND University_ID = $university_id
            LIMIT 1
        ");

        if ($totalFee && $totalFee->num_rows > 0) {
            $feeVarRow = $totalFee->fetch_assoc();

            if (!empty($feeVarRow['Fee'])) {
                $feearr = json_decode($feeVarRow['Fee'], true);

                if (is_array($feearr) && !empty($feearr)) {
                    $feeSharing = array_values($feearr)[0] ?? 0;
                }
            }
        }

        // IMPORTANT: Append values in the exact header order
        $row[] = $actual_course_fee;
        $row[] = $feeSharing;

        // Individual Fee Structure Columns
        foreach ($fee_structure_list as $fee_structure) {
            $fee_id = $fee_structure['ID'];
            $row[] = $combined_fees[$fee_id] ?? 0;
        }

        // Total Fee With Sharing
        $row[] = $total_calculated_fee;

        // Total Fee Without Sharing: Administrator and Center
        if (in_array($role, array('Administrator', 'Center'), true)) {
            $row[] = $total_without_sharing;
        }

        // Administrator-Only Columns
        if ($role == 'Administrator') {
            $row[] = $total_without_sharing - $total_calculated_fee;
            $row[] = 100 - (float)$feeSharing;

            // University Fee Course (Current)
            $uni_fee_val = 0;

            if ($subcourseid > 0) {
                $sub_course_query = $conn->query("
                    SELECT university_fee
                    FROM Sub_Courses
                    WHERE ID = $subcourseid
                    LIMIT 1
                ");

                if ($sub_course_query && $sub_course_query->num_rows > 0) {
                    $sub_course_row = $sub_course_query->fetch_assoc();
                    $uni_fee_val = $sub_course_row['university_fee'] ?? 0;
                }
            }

            $row[] = $uni_fee_val;
        }
    }

    // Remove Added_For
    unset($row[33]);

    // Student ID / Enrollment Number
    if (!empty($row[1])) {
        $row[0] = $row[1];
    } else {
        $row[0] = '<b>' . sprintf("%'.06d\n", $row[0]) . '</b>';
    }

    unset($row[1]);

    // Append ABC ID and DEB ID
    $row[] = $abcID;
    $row[] = $debID;

    $data[] = $row;
}

// Export Excel
$xlsx = SimpleXLSXGen::fromArray($data)->downloadAs('Students.xlsx');
?>