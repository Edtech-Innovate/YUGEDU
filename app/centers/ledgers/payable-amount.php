<?php
if (isset($_POST['ids']) && isset($_POST['center'])) {
  require '../../../includes/db-config.php';
  session_start();

  $center = intval($_POST['center']);
  $ids = is_array($_POST['ids']) ? array_filter($_POST['ids']) : [];
  $university_id = $_SESSION['university_id'];
  // print_r($university_id);die;
  if (empty($ids)) {
    exit(json_encode(['status' => false, 'message' => 'Please select student!']));
  }

  $invoice_no = strtoupper(uniqid('IN'));

  $balance = array();

  foreach ($ids as $id) {
    $duration = $conn->query("SELECT Duration FROM Students WHERE ID = $id");
    $duration = $duration->fetch_assoc();
    $duration = $duration['Duration'];

    $balance[] = balanceAmount($conn, $id, $duration);
  }
    
  $amount = array_sum($balance);
  $totalAmount = $amount < 0 ? (-1) * $amount : $amount;

     if ($amount!='') {
        $walletAmounts = $conn->query("SELECT sum(Amount) as total_amt FROM Wallets WHERE Added_By = " . $_SESSION['ID'] . " AND University_ID='$university_id' AND Status = 1");
        $walletAmounts = $walletAmounts->fetch_assoc();
        // print_r($walletAmounts);die;
        $debited_amount = 0;
        $debit_amts = $conn->query("SELECT sum(Amount) as debit_amt FROM Wallet_Payments WHERE Added_By = " . $_SESSION['ID'] . " AND Type = 3");
        if ($debit_amts->num_rows > 0) {
          $debit_amt = $debit_amts->fetch_assoc();
          $debited_amount = $debit_amt['debit_amt'];
        }
    
        $walletAmount = $walletAmounts['total_amt'] - $debited_amount;
        
        if ($walletAmount < $totalAmount) {
          $conn->close();
          exit(json_encode(['status' => false, 'message' => 'Insufficient Wallet Balance!']));
        }
      }
// print_r( $walletAmount);die;
  echo json_encode(['status' => true, 'amount' => $totalAmount]);
}
