<?php
if (isset($_POST['amount']) && isset($_POST['ids'])) {
  require '../../includes/db-config.php';
  //   include '../../includes/helpers.php';
  session_start();

  $allowed_file_extensions = array("jpeg", "jpg", "png", "gif", "JPG", "PNG", "JPEG", "pdf", "PDF");
  $file_folder = '../../uploads/offline-payments/';

  $ids = mysqli_real_escape_string($conn, $_POST['ids']);
  $ids = explode("|", $ids);

  $transaction_id = strtoupper(strtolower(uniqid()));
  $file = $transaction_id;
  $payment_type = "Wallet";
  $bank_name = "Wallet";
  $gateway_id = $transaction_id;
  $amount = mysqli_real_escape_string($conn, $_POST['amount']);
  $transaction_date = $transaction_date = date("Y-m-d");
  $student_id = isset($_POST['student_id']) ? mysqli_real_escape_string($conn, $_POST['student_id']) : '';

  $check = $conn->query("SELECT ID FROM Wallet_Payments WHERE Transaction_ID = '$gateway_id' AND Type = 3 AND Payment_Mode != 'Cash'");
  if ($check->num_rows > 0) {
    echo json_encode(['status' => 400, 'message' => 'Transaction ID already exists!']);
    exit();
  }


  $amount_update = 0;
  $amount_check = $conn->query("SELECT sum(Amount) as total_amount FROM Wallets WHERE Added_By = " . $_SESSION['ID'] . " AND University_ID = " . $_SESSION['university_id'] . " AND Payment_For = 1 AND Status = 1 ");
  if ($amount_check->num_rows > 0) {
    $amount_check = $amount_check->fetch_assoc();
    $amount_update = $amount_check['total_amount'];
  } else {
    echo json_encode(['status' => 400, 'message' => 'Please recharge wallet first!']);
    exit();
  }
  
  //echo "<pre>";print_r($amount_update);die;

  if ($amount_update == 0) {
    echo json_encode(['status' => 400, 'message' => 'Please recharge wallet first!']);
    exit();
  }

  if ($amount_update < $amount) {
    echo json_encode(['status' => 400, 'message' => 'Please recharge wallet first!']);
    exit();
  }

  // GET center id
  if ($_SESSION['Role'] == 'Sub-Center') {
    $subcenterId = $_SESSION['ID'];
    $center_id = getCenterIdFunc($conn, $subcenterId);
  } else if ($_SESSION['Role'] == "Center") {
    $center_id = $_SESSION['ID'];
  }

  foreach ($ids as $id) {

    $student = $conn->query("SELECT Role, Duration, Admission_Session_ID,Course_ID, Sub_Course_ID,University_ID FROM Students LEFT JOIN Users ON Students.Added_For  = Users.ID WHERE Students.ID = $id");
    $student = $student->fetch_assoc();
    $duration = $student['Duration'];
    $balance = balanceAmount($conn, $id, $duration);
    $add_amount = abs($balance);

    $added_for_column = ", `Added_For`";
    $student_id = base64_decode($id);
    $student_ids = intval(str_replace('W1Ebt1IhGN3ZOLplom9I', '', $student_id));
    $added_for_value = "," . $student_ids;

    $studentUniversityID = $student['University_ID'];
    $studentAddmissionID = $student['Admission_Session_ID'];
    $studentSubCourseId = $student['Sub_Course_ID'];

    $schemeData = $conn->query("select Scheme from Admission_Sessions where ID=$studentAddmissionID")->fetch_assoc();
    $schemeID = json_decode($schemeData['Scheme'], true)['schemes'][0];

    $feeStructure = $conn->query("select group_concat(ID) as feeStructure from Fee_Structures where University_ID = '$studentUniversityID'");
    $feeStructureID = $feeStructure->fetch_assoc()['feeStructure']; // 
    $totalCourseFee = 0;
    $courseFee = $conn->query("select sum(Fee) as totalFee from Fee_Constant where Scheme_ID=$schemeID and University_ID=$studentUniversityID and Fee_Structure_ID IN ($feeStructureID) and Sub_Course_ID=$studentSubCourseId");
    $totalCourseFee = $courseFee->fetch_assoc()['totalFee']; // total course fee
    $centerDiscount = 0;
    $discountForCenter = $conn->query("select Fee from Fee_Variables where Scheme_ID=$schemeID and University_ID=$studentUniversityID  and Code= $center_id");
    $totalDiscountForCenter = $discountForCenter->fetch_assoc()['Fee'];
    $centerDiscount = json_decode($totalDiscountForCenter, TRUE)[$feeStructureID];// discount amount

    $centerShare = 100 - (int) $centerDiscount;
    $center_course_fee = $totalCourseFee * $centerShare / 100;


    // Late Fee
    $startDate = date("Y-m-d");
    $lateFees = $conn->query("SELECT End_Date, Fee, Exception, Admission_Session, Name FROM Late_Fees WHERE University_ID = " . $_SESSION['university_id'] . " AND Start_Date <= '$startDate' AND Status = 1 AND For_Students = 'Fresh' AND IsLateFee = 1 ORDER BY ID DESC");
    while ($lateFee = $lateFees->fetch_assoc()) {
      if (!empty($lateFee['End_Date']) && $lateFee['End_Date'] < $startDate) {
        continue;
      }

      $exceptions = !empty($lateFee['Exception']) ? json_decode($lateFee['Exception'], true) : array();
      if (!empty($exceptions) && in_array($_SESSION['Code'], $exceptions)) {
        continue;
      }

      $admissionSessions = json_decode($lateFee['Admission_Session'], true);
      if (!in_array($student['Admission_Session_ID'], $admissionSessions)) {
        continue;
      }

      $add = $conn->query("INSERT INTO Student_Ledgers (Date, Student_ID, Duration, University_ID, Type, Fee, Fee_Without_Sharing, Source, Status) VALUES ('$startDate', $id, '" . $duration . "', " . $_SESSION['university_id'] . ", 1, '" . $lateFee['Fee'] . "', '" . $lateFee['Fee'] . "', '{$lateFee['Name']}', 1)");
      $balance += $lateFee['Fee'];
      break;
    }

    if ($_SESSION['Role'] == 'Sub-Center') {

      $fee = $conn->query("SELECT  Fee FROM Student_Ledgers LEFT JOIN Students ON Student_Ledgers.Student_ID = Students.ID WHERE Student_Ledgers.Student_ID = $id AND Student_Ledgers.Duration = Students.Duration AND Student_Ledgers.Type = 1 AND Source IS NULL");
      if ($fee->num_rows == 0) {
        continue;
      }
      $fee = $fee->fetch_assoc();
      $sub_center_amount = 0;
      $sub_center_amount_arr = json_decode($fee['Fee'], true);
      if (!empty($sub_center_amount_arr) && is_array($sub_center_amount_arr)) {
        $sub_center_amount = array_sum(array_filter($sub_center_amount_arr)); // subcenter fee
      }
      $center_wallet_amount = $sub_center_amount - $center_course_fee; // center wallet amount 
      $center_wallet_amount = abs($center_wallet_amount);
      $payment_type = "Settelment By Sub-Center";

      $add_wallet = $conn->query("INSERT INTO Wallets (Type, Transaction_Date, Transaction_ID, Gateway_ID, Bank, Amount, Payment_Mode, Added_By,  File, University_ID $added_for_column, Status) VALUES (1, '$transaction_date', '$transaction_id', '$gateway_id', '$bank_name', '$center_wallet_amount', '$payment_type',  " . $center_id . ", '$file', " . $_SESSION['university_id'] . " $added_for_value, 1)");
    }

    if ($_SESSION['Role'] == "Center") {
      $center_id = $_SESSION['ID'];
      $payment_type = "Settelment By Center";
      if ($student['Role'] == "Sub-Center") {
        $add_amount = abs($balance) - $center_course_fee;
      }
      if ($student['Role'] == "Sub-Center" && $add_amount > 0) {
        $add_wallet = $conn->query("INSERT INTO Wallets (Type, Transaction_Date, Transaction_ID, Gateway_ID, Bank, Amount, Payment_Mode, Added_By,  File, University_ID $added_for_column, Status) VALUES (1, '$transaction_date', '$transaction_id', '$gateway_id', '$bank_name', '$add_amount', '$payment_type',  " . $_SESSION['ID'] . ", '$file', " . $_SESSION['university_id'] . " $added_for_value, 1)");
      }
    }

    $add = $conn->query("INSERT INTO Wallet_Invoices (`User_ID`, `Student_ID`, `Duration`, `University_ID`, `Invoice_No`, `Amount`) VALUES (" . $_SESSION['ID'] . ", $id, '$duration', " . $_SESSION['university_id'] . ", '$transaction_id', '".abs($balance)."')");
    $conn->query("UPDATE Students SET Process_By_Center = now(), Payment_Received = now() WHERE ID = $id ");
  }


  if ($add) {
    $payment_type = 'Student Fee';
    $add = $conn->query("INSERT INTO Wallet_Payments (Type, Status, Transaction_Date, Transaction_ID, Gateway_ID, Bank, Amount, Payment_Mode, Added_By, File, University_ID) VALUES (3, 1, '$transaction_date', '$transaction_id', '$gateway_id', '$bank_name', '$amount', '$payment_type', " . $_SESSION['ID'] . ", '$file', " . $_SESSION['university_id'] . ")");
    $last_insert_id = $conn->insert_id;
    // NEW KP
    $getPayment = $conn->query("SELECT Wallet_Payments.*,Wallet_Invoices.Student_ID  FROM Wallet_Payments LEFT JOIN Wallet_Invoices ON  Wallet_Payments.Transaction_ID = Wallet_Invoices.Invoice_No WHERE Wallet_Payments.Type = 3 AND Wallet_Payments.ID  = $last_insert_id");
    $payment = $getPayment->fetch_assoc();
    $student_id = $payment['Student_ID'];

    $students = $conn->query("SELECT Student_ID, Duration, Amount, University_ID, Created_At FROM Wallet_Invoices WHERE Invoice_No = '" . $payment['Transaction_ID'] . "'");
    while ($student = $students->fetch_assoc()) {
      $add = $conn->query("INSERT INTO Student_Ledgers (Student_ID, Duration, Date, University_ID, Type, Source, Transaction_ID, Fee, Status) VALUES (" . $student['Student_ID'] . ", '" . $student['Duration'] . "', '" . date("Y-m-d", strtotime($payment['Transaction_Date'])) . "', " . $student['University_ID'] . ", 3, 'Wallet', '" . $payment['Transaction_ID'] . "', '" . json_encode(['Paid' => (-1) * $student['Amount']]) . "', 1)");
      $update = $conn->query("UPDATE Students SET Payment_Received = '" . $student['Created_At'] . "', Process_By_Center = '" . $student['Created_At'] . "' WHERE ID = " . $student['Student_ID']);
      if (!$add) {
        exit(json_encode(['status' => 400, 'message' => 'Something went wrong!']));
      }
    }

    // END KP 

    if ($add) {
      echo json_encode(['status' => 200, 'message' => 'Payment added successfully!']);
    } else {
      echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
    }
  } else {
    echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
  }
}
