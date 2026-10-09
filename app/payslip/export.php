<?php
## Database configuration
include '../../includes/db-config.php';
session_start();

## Fetch records
$result_record = "SELECT bank,payment_mode,bank_transication_no,date_of_payment, pay_slips.status as slip_status,pay_slip_generation.status as genration_status, pay_slips.university_fee, pay_slip_generation.date_of_generation , CONCAT(Users.Name, ' (', Users.Code, ')') AS user_name,serial_no,CONCAT(TRIM(CONCAT(Students.First_Name, ' ', Students.Middle_Name, ' ', Students.Last_Name)), ' (', IF(Students.Unique_ID='' OR Students.Unique_ID IS NULL, RIGHT(CONCAT('000000', Students.ID), 6), Students.Unique_ID), ')') as Student_name, Sub_Courses.Name as sub_course_name FROM pay_slips left join pay_slip_generation on pay_slip_id = pay_slip_generation.id left join Students on Students.ID = pay_slips.student_id left join Users on Users.ID = Students.Added_By left join Sub_Courses on Sub_Courses.ID = Students.Sub_Course_ID where 1=1 and pay_slips.university_id = '" . $_SESSION['university_id'] . "'  group by student_id order by pay_slips.id ";
$empRecords = mysqli_query($conn, $result_record);
$data = array();

while ($row = mysqli_fetch_assoc($empRecords)) {

    $data[] = array(
        "slip_status" => $row['slip_status'],
        "bank_transication_no" => $row['bank_transication_no'],
        "genration_status" => $row['genration_status'],
        "Student_name" => $row["Student_name"],
        "serial_no" => $row['serial_no'],
        "user_name" => $row['user_name'],
         "bank" => $row['bank'],
        "sub_course_name" => $row['sub_course_name'],
        "university_fee" => $row['university_fee'],
         "payment_mode" => $row['payment_mode'],
        "date_of_payment" => date('d-m-Y', strtotime($row['date_of_payment'])),
        "date_of_generation" => date('d-m-Y', strtotime($row['date_of_generation'])),
    );
}

## Response
$response = array(
    "aaData" => $data
);

echo json_encode($response);
