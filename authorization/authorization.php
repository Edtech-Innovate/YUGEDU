<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/includes/db-config.php';

/**
 * Check Authorization Fee Status
 * Payment_For = 2 → Authorization Fee
 */
$authPaid = false;

if (isset($_SESSION['Role'], $_SESSION['ID']) && $_SESSION['Role'] === 'Center') {

    $centerId = (int) $_SESSION['ID'];

    $check = $conn->query("
        SELECT ID
        FROM Wallets
        WHERE Added_By = {$centerId}
          AND Payment_For = 2
          AND Status = 1
        LIMIT 1
    ");

    if ($check && $check->num_rows > 0) {
        $authPaid = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Authorization Fee Status</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            background: #f4f6f9;
        }

        .page-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-status-box {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            padding: 40px;
            text-align: center;
            width: 100%;
            max-width: 420px;
        }

        h4 {
            margin-bottom: 15px;
            color: #333;
        }

        .status-badge {
            padding: 8px 18px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 15px;
        }

        .status-pending {
            background-color: #fd7e14;
            color: #fff;
        }

        .status-paid {
            background-color: #20c997;
            color: #fff;
        }

        .note-text {
            margin-top: 10px;
            font-size: 15px;
            color: #666;
        }

        .note-success {
            color: #198754;
        }

        .btn {
            display: inline-block;
            margin-top: 25px;
            padding: 12px 26px;
            border-radius: 25px;
            font-size: 15px;
            text-decoration: none;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: #0d6efd;
            color: #fff;
        }

        .btn-success {
            background: #198754;
            color: #fff;
        }

        .btn-secondary {
            background: #6c757d;
            color: #fff;
        }

        /* MODAL */
        .modal {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.6);
            display: none;
            align-items: center;
            justify-content: center;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: #fff;
            border-radius: 12px;
            width: 100%;
            max-width: 360px;
            padding: 25px;
            text-align: center;
            animation: scaleIn 0.2s ease;
        }

        @keyframes scaleIn {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 18px;
            font-weight: 600;
        }

        .close {
            font-size: 22px;
            cursor: pointer;
            border: none;
            background: none;
        }

        .modal-body a {
            display: block;
            margin: 12px 0;
        }
    </style>
</head>
<body>

<div class="page-container">
    <div class="card-status-box">

        <h4>Authorization Fee Status</h4>

        <?php if ($authPaid): ?>

            <span class="status-badge status-paid">Paid</span>

            <div class="note-text note-success">
                Your Authorization Fee has been successfully received.
            </div>

            <a href="/admissions/applications" class="btn btn-success">
                Go to Dashboard
            </a>

        <?php else: ?>

            <span class="status-badge status-pending">Pending</span>

            <div class="note-text">
                Authorization Fee is mandatory to activate your Center account.
            </div>

            <button class="btn btn-primary" onclick="openModal()">
                Pay Authorization Fee
            </button>

        <?php endif; ?>

    </div>
</div>

<!-- PAYMENT MODAL -->
<div class="modal" id="paymentModal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-title">Select Payment Mode</div>
            <button class="close" onclick="closeModal()">×</button>
        </div>

        <div class="modal-body">
            <!--<a href="/accounts/online-payments?payment_for=2" class="btn btn-success">-->
            <!--    Online Payment-->
            <!--</a>-->

            <!--<a href="/accounts/offline-payments?payment_for=2" class="btn btn-secondary">-->
            <!--    Offline Payment-->
            <!--</a>-->
             <a href="/accounts/online-payments" class="btn btn-success">
                Online Payment
            </a>

            <a href="/accounts/offline-payments" class="btn btn-secondary">
                Offline Payment
            </a>
        </div>
    </div>
</div>

<script>
    function openModal() {
        document.getElementById('paymentModal').classList.add('show');
    }
    function closeModal() {
        document.getElementById('paymentModal').classList.remove('show');
    }
</script>

</body>
</html>
