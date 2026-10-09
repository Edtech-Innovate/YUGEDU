<?php if (isset($_GET['id'])) {
  session_start();
  require '../../../../includes/db-config.php';

  $id = intval($_GET['id']);
  $centerID = intval($_GET['id']);

  $searchQuery = '';
  $filterQueryUser = "";
  if (isset($_SESSION['filterByUser'])) {
    $filterQueryUser = $_SESSION['filterByUser'];
  }

  $adm_session = "";
  if (isset($_SESSION['adm_session'])) {
    $adm_session = $_SESSION['adm_session'];
  }

  $query = "";
  $searchQuery .= $adm_session . $filterQueryUser . $query;

  $studentsArrData = [];

  // Wallet invoices students
  $wallet_invoices = $conn->query("SELECT Students.ID AS stu_id, Wallet_Invoices.ID, Wallet_Payments.Transaction_ID, Wallet_Payments.Gateway_ID, Wallet_Invoices.Amount, Wallet_Invoices.Duration, Students.First_Name, Students.Middle_Name, Students.Last_Name, (RIGHT(CONCAT('000000', Students.ID), 6)) as Student_ID, Students.Unique_ID, Wallet_Invoices.Created_At , Students.Added_By, Students.Course_ID, Students.Sub_Course_ID FROM Wallet_Invoices LEFT JOIN Wallet_Payments ON Wallet_Invoices.Invoice_No = Wallet_Payments.Transaction_ID LEFT JOIN Students ON Wallet_Invoices.Student_ID = Students.ID WHERE Wallet_Invoices.`User_ID` = $id AND Wallet_Invoices.University_ID = " . $_SESSION['university_id'] . " AND Wallet_Payments.Status = 1 $query ORDER BY Students.ID DESC");

  while ($wallet = $wallet_invoices->fetch_assoc()) {
    $studentsArrData[] = $wallet;
  }

  // Invoice students
  $invoices = $conn->query("SELECT Students.ID AS stu_id, Invoices.ID, Payments.Transaction_ID, Payments.Gateway_ID, Invoices.Amount, Invoices.Duration, Students.First_Name, Students.Middle_Name, Students.Last_Name, (RIGHT(CONCAT('000000', Students.ID), 6)) as Student_ID, Students.Unique_ID, Invoices.Created_At , Students.Added_By, Students.Course_ID, Students.Sub_Course_ID FROM Invoices LEFT JOIN Payments ON Invoices.Invoice_No = Payments.Transaction_ID LEFT JOIN Students ON Invoices.Student_ID = Students.ID WHERE Invoices.`User_ID` = $id AND Invoices.University_ID = " . $_SESSION['university_id'] . " AND Payments.Status = 1 $query ORDER BY Students.ID DESC");

  while ($invoice = $invoices->fetch_assoc()) {
    $studentsArrData[] = $invoice;
  }

  if (count($studentsArrData) == 0) { ?>
    <div class="row">
      <div class="col-lg-12 text-center">
        No student(s) found!
      </div>
    </div>
  <?php } else { ?>
    <?php if ($_SESSION['Role'] == 'Operations') { ?>
      <div class="row m-b-20">
        <div class="col-md-12 d-flex justify-content-end">
          <div>
            <button type="button" class="btn btn-primary" onclick="paySlipFunc('payslip')">Pay Slip</button>
          </div>
        </div>
      </div>
    <?php } ?>

    <div class="row">
      <div class="col-lg-12">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <?php if ($_SESSION['Role'] == 'Operations') { ?>
                  <th></th>
                <?php } ?>
                <th>Processed On</th>
                <th>Particular</th>
                <th>Transaction ID</th>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Duration</th>
                <th>Paid</th>
                <?php if ($_SESSION['Role'] == 'Operations') { ?>
                  <th>Status</th>
                <?php } ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($studentsArrData as $student) {
                $student_name = array_filter([$student['First_Name'], $student['Middle_Name'], $student['Last_Name']]); ?>
                <tr id="<?= $student['stu_id'] ?>">
                  <?php if ($_SESSION['Role'] == 'Operations') {
                    // University fee
                    $university_fee_sql = $conn->query("SELECT university_fee FROM Sub_Courses WHERE ID = '" . $student['Sub_Course_ID'] . "' ");
                    $university_fee = $university_fee_sql->fetch_assoc()['university_fee'] ?? 0;

                    // Pay slip status check
                    $check_student_pay_slip = $conn->query("SELECT * FROM pay_slips WHERE student_id = " . intval($student['stu_id']));
                    $readonly = "";
                    $status = "";
                    if ($check_student_pay_slip->num_rows > 0) {
                      $student_pay_slip = $check_student_pay_slip->fetch_assoc();
                      $status = $student_pay_slip['status'];
                      $status = ($status == 0) ? '<span class="badge bg-warning text-dark">Pending</span>' : (($status == 1) ? '<span class="badge bg-success">Approved</span>' : "");
                      $readonly = "disabled";
                    }
                  ?>
                    <td>
                      <div class="form-check complete" style="margin-bottom: 0px;">
                        <input type="checkbox" <?= $readonly ?> class="student-checkbox" id="student-<?= $student['stu_id'] ?>"
                          name="student_id" value="<?= $student['stu_id'] ?>">
                        <label for="student-<?= $student['stu_id'] ?>" class="font-weight-bold"></label>
                        <input type="hidden" name="uni_fee[<?= $student['stu_id'] ?>]" value="<?= $university_fee ?>">
                      </div>
                    </td>
                  <?php } ?>
                  <td><?= date("d-m-Y", strtotime($student['Created_At'])) ?></td>
                  <td><?= $student['Gateway_ID'] ?></td>
                  <td><?= $student['Transaction_ID'] ?></td>
                  <td><b><?= !empty($student['Unique_ID']) ? $student['Unique_ID'] : $student['Student_ID'] ?></b></td>
                  <td><?= implode(" ", $student_name) ?></td>
                  <td><?= $student['Duration'] ?></td>
                  <td><?= "&#8377; " . abs($student['Amount']); ?></td>
                  <?php if ($_SESSION['Role'] == 'Operations') { ?>
                    <td><?= $status ?></td>
                  <?php } ?>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  <?php } ?>

  <script>
    function paySlipFunc(payslip) {
      var ids = [];
      var studentFees = {};
      $.each($("input[name='student_id']:checked"), function () {
        ids.push($(this).val());
        var studentId = $(this).val();
        var feeInput = $("input[name='uni_fee[" + studentId + "]']");
        if (feeInput.length > 0) {
          var fee = feeInput.val();
          studentFees[studentId] = fee;
        }
      });
      if (Object.keys(studentFees).length === 0) {
        notification('danger', 'Please select Student');
        return false;
      }
      Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Generate the payslip! '
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: '/app/payslip/generate-pay-slips',
            type: 'post',
            data: { studentFees },
            dataType: 'json',
            success: function (data) {
              $.each(data.stu_ids, function (index, studentId) {
                $("#student-" + studentId).prop("disabled", true);
                const statusCell = $("#" + studentId).find("td").last();
                statusCell.html('<span class="badge bg-warning text-dark">Pending</span>');
              });

              if (data.status == 200) {
                notification('success', data.message);
              } else {
                notification('danger', data.message);
              }
            }
          });
        }
      });
    }
  </script>

<?php } ?>
