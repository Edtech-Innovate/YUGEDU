<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/includes/db-config.php';

$authPaid = false;
$role     = strtolower(trim($_SESSION['Role'] ?? ''));
$centerId = (int)($_SESSION['ID'] ?? 0);

if ($role !== 'center' || $centerId === 0) {
    header("Location: /login");
    exit;
}

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Authorization Fee Status</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- jQuery & Bootstrap 5 -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>

<style>
.page-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f4f6f9;
}
.card-status-box {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.08);
    padding: 40px;
    text-align: center;
    max-width: 420px;
    width: 100%;
}
.status-badge {
    padding: 8px 18px;
    border-radius: 20px;
    font-weight: 600;
    display: inline-block;
    margin-bottom: 15px;
}
.status-pending { background: #fd7e14; color: #fff; }
.status-paid { background: #20c997; color: #fff; }
.btn { margin-top: 25px; padding: 12px 26px; border-radius: 25px; font-size: 15px; border: none; cursor: pointer; }
.btn-primary { background: #0d6efd; color: #fff; }
.btn-success { background: #198754; color: #fff; }
</style>
</head>
<body>

<div class="page-container">
    <div class="card-status-box">
        <h3>Authorization Fee Status</h3>

        <?php if ($authPaid): ?>
            <span class="status-badge status-paid">Paid</span>
            <p style="color:#198754">Authorization Fee received successfully.</p>
            <a href="/admissions/applications" class="btn btn-success">Go to Dashboard</a>
        <?php else: ?>
            <span class="status-badge status-pending">Pending</span>
            <p>Authorization Fee is mandatory to activate your Center account.</p>
            <button type="button" class="btn btn-primary" id="payAuthBtn">Pay Authorization Fee</button>
        <?php endif; ?>
    </div>
</div>

<!-- Bootstrap Large Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body" id="paymentModalContent">
        <!-- Wallet form will be loaded here via AJAX -->
        <div class="text-center py-5">Loading...</div>
      </div>
    </div>
  </div>
</div>

// <script>
// $(document).ready(function() {
//     $('#payAuthBtn').on('click', function() {
//         var modalEl = new bootstrap.Modal(document.getElementById('paymentModal'));
//         modalEl.show();

//         // Load the wallet/payment page via AJAX
//         $.ajax({
//             url: '/app/wallet-payments/create', // Your wallet form page
//             type: 'GET',
//             success: function(data) {
//                 $('#paymentModalContent').html(data);
//             },
//             error: function() {
//                 $('#paymentModalContent').html('<div class="text-danger text-center py-5">Unable to load the form. Please try again.</div>');
//             }
//         });
//     });
// });
// </script>
<script>
$(document).ready(function() {
    $('#payAuthBtn').on('click', function() {
        var modalEl = new bootstrap.Modal(document.getElementById('paymentModal'));
        modalEl.show();

        // Load the wallet/payment page via AJAX
        $.ajax({
            url: '/app/wallet-payments/create', // Your wallet form page
            type: 'GET',
            success: function(data) {
                $('#paymentModalContent').html(data);

                // Attach submit handler for the loaded form
                $('#form-add-offline-payments, #form-add-online-payments').on('submit', function(e) {
                    e.preventDefault();
                    var form = $(this);
                    if (form.valid && form.valid()) { // if using jQuery validate
                        var formData = new FormData(this);
                        $.ajax({
                            url: form.attr('action'),
                            type: 'POST',
                            data: formData,
                            cache: false,
                            contentType: false,
                            processData: false,
                            dataType: 'json',
                            success: function(response) {
                                if (response.status == 200 || response.status == 1) {
                                    modalEl.hide(); // Close the modal
                                    location.reload(); // Reload the page
                                } else {
                                    alert(response.message || 'Payment failed!');
                                }
                            },
                            error: function() {
                                alert('An error occurred. Please try again.');
                            }
                        });
                    }
                });
            },
            error: function() {
                $('#paymentModalContent').html('<div class="text-danger text-center py-5">Unable to load the form. Please try again.</div>');
            }
        });
    });
});
</script>


</body>
</html>
