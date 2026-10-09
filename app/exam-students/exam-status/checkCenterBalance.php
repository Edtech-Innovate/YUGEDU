<?php
include '../../../includes/db-config.php';
require '../../../includes/helpers.php';
session_start();

if (isset($_POST['ids']) && isset($_POST['center'])) {  
    $university_id = $_SESSION['university_id'];
    $center = intval($_POST['center']);
    $ids = mysqli_real_escape_string($conn,$_POST['ids']);
    $amount = '250';

    $walletAmounts = $conn->query("SELECT sum(Amount) as total_amt FROM Wallets WHERE Added_By = " . $_SESSION['ID'] . " AND University_ID='$university_id' AND Status = 1");
    $walletAmounts = $walletAmounts->fetch_assoc();
    $debited_amount = 0;
    $debit_amts = $conn->query("SELECT sum(Amount) as debit_amt FROM Wallet_Payments WHERE Added_By = " . $_SESSION['ID'] . " AND Type = 3");
    if ($debit_amts->num_rows > 0) {
        $debit_amt = $debit_amts->fetch_assoc();
        $debited_amount = $debit_amt['debit_amt'];
    }

    $walletAmount = $walletAmounts['total_amt'] - $debited_amount;
    if ($walletAmount < $amount) {
        $conn->close();
        exit(json_encode(['status' => false, 'message' => 'Wallet balance insufficient!']));
    }
    echo json_encode(['status' => true, 'amount' => $amount, 'ids' => $ids]);
}
?>