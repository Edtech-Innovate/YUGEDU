<?php
if (empty($_POST['studentFees'])) {
    echo json_encode(['status' => 300, 'message' => 'Please select student!']);
    exit();
}

if (isset($_POST['studentFees'])) {
    require_once('../../includes/db-config.php');
    session_start();
    $uni_id = $_SESSION['university_id'];


    $studentFees = is_array($_POST['studentFees']) ? array_filter($_POST['studentFees'], function ($val) {
        return $val !== null && $val !== '' && $val != 0;
    }) : [];

    $stu_ids = [];
    $pay_slip = false; 

    if (!empty($studentFees)) {
        $serial_no = generateSerialNoFunc($conn, $uni_id);
        $date_of_generation = date('Y-m-d H:i:s');
        $total_uni_fee = array_sum($_POST['studentFees']); 

        $generate_pay_slip = $conn->query("INSERT INTO pay_slip_generation (`serial_no`, `status`, date_of_generation, total_university_fee, university_id) VALUES ('$serial_no', 0, '$date_of_generation', '$total_uni_fee', $uni_id)");

        if ($generate_pay_slip === true) {
            $pay_slip_id = $conn->insert_id;

            foreach ($_POST['studentFees'] as $student_id => $university_fee) {

                if ($university_fee === '' || $university_fee === null || $university_fee == 0) {
                    $get_name = $conn->query("select CONCAT(TRIM(CONCAT(Students.First_Name, ' ', Students.Middle_Name, ' ', Students.Last_Name)), ' (', IF(Students.Unique_ID='' OR Students.Unique_ID IS NULL, RIGHT(CONCAT('000000', Students.ID), 6), Students.Unique_ID), ')') as Student_name from Students where ID = $student_id");
                    $stu_name = $get_name->fetch_assoc()['Student_name'];
                    echo json_encode([
                        'status' => 'error',
                        'message' => "University fee is blank or zero for student ID: $stu_name"
                    ]);
                    exit();
                }
                

                $stu_ids[] = $student_id;
                $user_id = getUserId($conn, $student_id);
                $check = $conn->query("SELECT * FROM pay_slips WHERE student_id = $student_id");
                if ($check->num_rows == 0) {
                    $pay_slip = $conn->query("INSERT INTO pay_slips (student_id, user_id, pay_slip_id, university_fee, university_id, `status`) 
                        VALUES ($student_id, $user_id, $pay_slip_id, '$university_fee', $uni_id, 0)");
                }
            }
        }
    }

    if ($pay_slip) {
        echo json_encode([
            'status' => 200,
            'stu_ids' => $stu_ids,
            'message' => 'Pay slip generation completed'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to generate pay slip or University fee is blank or zero'
        ]);
    }
}

function generateSerialNoFunc($conn, $uni_id)
{
    $get_suffix_sql = $conn->query("SELECT * FROM pay_slip_suffix WHERE university_id = $uni_id");

    if ($get_suffix_sql->num_rows > 0) {
        $get_suffix = $get_suffix_sql->fetch_assoc();
        $suffix = $get_suffix['suffix'];
        $character = intval($get_suffix['character']);
        $randomLength = $character;
        if ($randomLength > 0) {
            $min = pow(10, $randomLength - 1);
            $max = pow(10, $randomLength) - 1;
            $randomNumber = rand($min, $max);
            $serialNo = $suffix . $randomNumber;
            return $serialNo;
        } else {
            return $suffix;
        }
    } else {
        return 'STD' . rand(1000, 9999);
    }
}


function getUserId($conn, $student_id)
{
    $get_user = $conn->query("select Added_By from Students where ID = $student_id");
    $user_id = $get_user->fetch_assoc()['Added_By'];
    return $user_id;
}