<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require '../../includes/db-config.php';
    session_start();
    $slip_id = mysqli_real_escape_string($conn, $_POST['slip_id']);
    $get_pay_gen_id = $conn->query("select pay_slip_id from pay_slips where id = $slip_id");
    $pay_slip_gen_id = $get_pay_gen_id->fetch_assoc()['pay_slip_id'];
    $student_ids = mysqli_real_escape_string($conn, $_POST['student_ids']);
    $bank_name = mysqli_real_escape_string($conn, $_POST['bank_name']) ?? 'NULL';
    $payment_type = mysqli_real_escape_string($conn, $_POST['payment_type'])?? 'NULL';
    $transaction_id = mysqli_real_escape_string($conn, $_POST['transaction_id'])?? 'NULL';
    $transaction_date = mysqli_real_escape_string($conn, $_POST['transaction_date'])?? 'NULL';
    $uni_fee = intval($_POST['amount']);

    $file = NULL;
    if ($payment_type != 'Cash') {
        if (isset($_FILES["file"]['tmp_name']) && $_FILES["file"]['tmp_name'] != '') {
            $file_folder = '../../uploads/payslips/';
             $allowed_file_extensions = array("jpeg", "jpg", "png", "gif", "JPG", "PNG", "JPEG", "pdf", "PDF");
            $file = mysqli_real_escape_string($conn, $_FILES["file"]['name']);
            $tmp_name = $_FILES["file"]["tmp_name"];
            $file_extension = pathinfo($file, PATHINFO_EXTENSION);
            $file = uniqid() . "." . $file_extension;
            if (in_array($file_extension, $allowed_file_extensions)) {
                if (!move_uploaded_file($tmp_name, $file_folder . $file)) {
                    echo json_encode(['status' => 503, 'message' => 'Unable to upload file!']);
                    exit();
                } else {
                    $file = str_replace('../..', '', $file_folder) . $file;
                }
            } else {
                echo json_encode(['status' => 302, 'message' => 'File should be Image or PDF!']);
                exit();
            }
        } else {
            echo json_encode(['status' => 400, 'message' => 'File is required!']);
            exit();
        }
    }
    $date_of_payment = date('Y-m-d H:i:s');
    $update = $conn->query("UPDATE pay_slip_generation SET bank ='$bank_name', payment_mode ='$payment_type', bank_transication_no='$transaction_id', date_of_payment='$date_of_payment',file='$file', status=2 where id =  $pay_slip_gen_id and university_id =" . $_SESSION['university_id']);
    if ($update) {
        echo json_encode(['status' => 200, 'message' => 'University Fee Paid  successfully!']);
    } else {
        echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
    }

}
