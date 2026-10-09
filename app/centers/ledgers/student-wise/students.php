<?php if (isset($_GET['id'])) {
  session_start();
  require '../../../../includes/db-config.php';
//   error_reporting(-1);

  $searchQuery = '';
  $filterQueryUser = "";
  if (isset($_SESSION['filterByUser'])) {
    $filterQueryUser = $_SESSION['filterByUser'];
  }
  $id = intval($_GET['id']);

  $adm_session = "";
  if (isset($_SESSION['adm_session'])) {
    $adm_session = $_SESSION['adm_session'];
  }

  $already = array();
  $already_ids = array();
  $query = '';

  $user_id = NULL;
  if ($_SESSION['Role'] == 'Sub-Center') {
    $user_id = getCenterIdFunc($conn, $id);
    $userQuery = " AND Students.Added_By =$id AND `User_ID` = $user_id";
  } else {
    $userQuery = " AND `User_ID` = $id";
  }
  
//   $added_for[] = $id;
//   $downlines = $conn->query("SELECT `User_ID` FROM University_User WHERE Reporting = $id");
//   while ($downline = $downlines->fetch_assoc()) {
//     $added_for[] = $downline['User_ID'];
//   }
//   $users = implode(",", array_filter($added_for));

if ($_SESSION['Role'] == 'Sub-Center' || $_SESSION['Role'] == 'Center') {
    $center_id = 'AND Students.Added_For = ' . $_SESSION['ID'] . '';
  } else {
    $center_id = '';
  }
  
 $invoices = $conn->query("SELECT Student_ID, Invoices.Duration FROM Invoices LEFT JOIN Payments ON Invoices.Invoice_No = Payments.Transaction_ID AND Payments.Type = 1 LEFT JOIN Students ON Invoices.Student_ID = Students.ID  WHERE  Invoices.University_ID = " . $_SESSION['university_id'] . " AND Payments.Status != 2 $userQuery");
  while ($invoice = $invoices->fetch_assoc()) {
    $already[$invoice['Student_ID']] = $invoice['Duration'];
    $already_ids[] = $invoice['Student_ID'];
  }

  $invoices = $conn->query("SELECT Student_ID, Wallet_Invoices.Duration FROM Wallet_Invoices LEFT JOIN Wallet_Payments ON Wallet_Invoices.Invoice_No = Wallet_Payments.Transaction_ID AND Wallet_Payments.Type = 3 LEFT JOIN Students ON Wallet_Invoices.Student_ID = Students.ID  WHERE `User_ID` = $id AND Wallet_Invoices.University_ID = " . $_SESSION['university_id'] . " AND Wallet_Payments.Status != 2 $userQuery");
  while ($invoice = $invoices->fetch_assoc()) {
    $already[$invoice['Student_ID']] = $invoice['Duration'];
    $already_ids[] = $invoice['Student_ID'];
  }


  $query = empty($already_ids) ? " " : " AND Students.ID NOT IN (" . implode(',', $already_ids) . ")";
  $searchQuery .= $adm_session . $filterQueryUser . $query;
  $students = $conn->query("SELECT Students.ID,Students.Sub_Course_ID,Students.Admission_Session_ID,Students.University_ID,Students.Added_For, First_Name, Middle_Name, Last_Name, Unique_ID, Duration, Admission_Sessions.Name as Admission_Session FROM Students LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID = Admission_Sessions.ID WHERE Students.University_ID = " . $_SESSION['university_id'] . "  AND Step = 4 AND Process_By_Center IS NULL $searchQuery ORDER BY Students.ID DESC");
  if ($students->num_rows == 0) { ?>
    <div class="row">
      <div class="col-lg-12 text-center">
        No student(s) found!
      </div>
    </div>
  <?php } else {
    ?>

      <div class="row ">
        <div class="col-md-12 d-flex justify-content-end">
          <div>
            <?php if (isset($_SESSION['gateway'])) { ?>
              <!--<button type="button" class="btn btn-primary" onclick="pay('Online')"> Pay Online</button>-->
            <?php } ?>
            <?php if ($_SESSION['Role'] == 'Center' || $_SESSION['Role'] == 'Sub-Center') { ?>
            <div class="row m-b-20">
              <div class="col-md-12 d-flex justify-content-end">
                <div>
                  <!--<button type="button" class="btn add_btn_form" onclick="pay('Offline')">Pay Offline</button>-->
                  <button type="button" class="btn add_btn_form" onclick="pay('wallet')">Pay Wallet</button>
                </div>
              </div>
            </div>
          <?php } ?>
          </div>
        </div>
      </div>
    <div class="row">
      <div class="col-lg-12">
        <div class="table-bordered px-3 py-2">
          <table class="table table-hover table-responsive" style:"margin-top:0px !important;width:100% !important;">
            <thead>
              <tr>
              <th></th>
                <th style="width:10% !important">Student ID</th>
                <th style="width:40% !important">Student Name</th>
                <th style="width:10% !important">Adm. Session</th>
                <th style="width:20% !important">Owner</th>
                <th style="width:10% !important">Course Fee</th>
                <th style="width:10% !important">Payable</th>
                <!--<th></th>-->
              </tr>
            </thead>
            <tbody>
              <?php while ($student = $students->fetch_assoc()) {

                if (in_array($student['ID'], $already_ids) && $student['Duration'] == $already[$student['ID']]) {
                  continue;
                }
                $student_name = array_filter(array($student['First_Name'], $student['Middle_Name'], $student['Last_Name'])); ?>
                <tr>
                    <td>
                    <div class="form-check complete" style="margin-bottom: 0px;">
                      <input type="checkbox" class="student-checkbox" id="student-<?= $student['ID'] ?>" name="student_id"
                        value="<?= $student['ID'] ?>">
                      <label for="student-<?= $student['ID'] ?>" class="font-weight-bold"></label>
                    </div>
                  </td>
                  <td><b><?php echo !empty($student['Unique_ID']) ? $student['Unique_ID'] : sprintf("%'.06d\n", $student['ID']) ?></b></td>
                  <td><?= implode(" ", $student_name) ?></td>
                  <td><?= $student['Admission_Session'] ?></td>
                  <td><?= getAddedBy($conn, $student['Added_For']) ?></td>
             
                  <?php 
                    $studentUniversityID = $student['University_ID'];
                    $studentAddmissionID = $student['Admission_Session_ID'];
                    $studentSubCourseId = $student['Sub_Course_ID'];
                    $schemeData = $conn->query("select Scheme from Admission_Sessions where ID=$studentAddmissionID")->fetch_assoc();
                    $schemeID = json_decode($schemeData['Scheme'], true)['schemes'][0];
                    $feeStructure = $conn->query("select group_concat(ID) as feeStructure from Fee_Structures where University_ID = '$studentUniversityID'");
                    $feeStructureID = $feeStructure->fetch_assoc()['feeStructure'];
                    // print_r("select sum(Fee) as totalFee from Fee_Constant where Scheme_ID=$schemeID and University_ID=$studentUniversityID and Fee_Structure_ID=$feeStructureID and Sub_Course_ID=$studentSubCourseId");die;
                    $courseFee = $conn->query("select sum(Fee) as totalFee from Fee_Constant where Scheme_ID=$schemeID and University_ID=$studentUniversityID and Fee_Structure_ID in($feeStructureID) and Sub_Course_ID=$studentSubCourseId");
                    $totalCourseFee = $courseFee->fetch_assoc()['totalFee'];
                    $userSharingAmountQuery = $conn->query("SELECT * FROM `User_Sub_Courses` WHERE User_ID = '" . $student['Added_For'] . "' and Sub_Course_ID = $studentSubCourseId and Scheme_ID = $schemeID and Admission_Session_ID=$studentAddmissionID and University_ID=$studentUniversityID");
                  ?>
                  <td><?= $totalCourseFee ?></td>
                  <td>
                    <?php
                    $balance = 0;
                    $ledgers = $conn->query("SELECT * FROM Student_Ledgers WHERE Student_ID = " . $student['ID'] . " AND Status = 1 AND Duration <= " . $student['Duration']);
                    while ($ledger = $ledgers->fetch_assoc()) {
                      $fees = json_decode($ledger['Fee'], true);
                      foreach ($fees as $key => $value) {
                        $debit = $ledger['Type'] == 1 ? $value : 0;
                        $credit = $ledger['Type'] == 2 ? $value : 0;
                        $balance = ($balance + $credit) - $debit;
                      }
                    }
                    echo "&#8377; " . (-1) * $balance;
                    ?>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <?php if ($_SESSION['Role'] == 'Center' || $_SESSION['Role'] == 'Sub-Center') { ?>
      <script src="https://ebz-static.s3.ap-south-1.amazonaws.com/easecheckout/easebuzz-checkout.js"></script>
      <script type="text/javascript">
        function pay(by) {
          if ($('.student-checkbox').filter(':checked').length == 0) {
            notification('danger', 'Please select Student');
          } else {
            var center = '<?= $id ?>';
            var ids = [];
            $.each($("input[name='student_id']:checked"), function () {
              ids.push($(this).val());
            });

            $.ajax({
              url: '/app/centers/ledgers/payable-amount',
              type: 'POST',
              data: {
                ids,
                center
              },
              dataType: 'json',
              success: function (data) {
                if (data.status) {
                  if (by == 'Online') {
                    payOnline(ids, data.amount, center);
                  } else if (by == 'Offline') {
                    payOffline(ids, data.amount, center);
                  } else if (by == 'wallet') {
                    payWallet(ids, data.amount, center);
                  }
                } else {
                  notification('danger', data.message);
                }
              }
            })
          }
        }
        function payWallet(ids, amount, center) {
          var by = 'wallet';
          $.ajax({
            url: '/app/wallet-payments/create-multiple',
            type: 'post',
            data: {
              ids,
              amount,
              center,
              by
            },
            success: function (data) {
              $("#lg-modal-content").html(data);
              $("#lgmodal").modal('show');
            }
          });
        }
        function payOnline(ids, amount, center) {
          $.ajax({
            url: '/app/easebuzz/pay-multiple',
            type: 'post',
            data: {
              ids,
              amount
            },
            dataType: "json",
            success: function (data) {
              if (data.status == 1) {
                $('.modal').modal('hide');
                initiatePayment(data.data, center)
              } else {
                notification('danger', data.error);
              }
            }
          });
        }

        function payOffline(ids, amount, center) {
          $.ajax({
            url: '/app/offline-payments/create-multiple',
            type: 'post',
            data: {
              ids,
              amount,
              center
            },
            success: function (data) {
              $("#lg-modal-content").html(data);
              $("#lgmodal").modal('show');
            }
          });
        }

        function initiatePayment(data, center) {
          var easebuzzCheckout = new EasebuzzCheckout('<?= $_SESSION['access_key']??0 ?>', 'prod')
          var options = {
            access_key: data,
            dataType: 'json',
            onResponse: (response) => {
              updatePayment(response, center);
              if (response.status == 'success') {
                Swal.fire({
                  title: 'Thank You!',
                  text: "Your payment is successfull!",
                  icon: 'success',
                  showCancelButton: false,
                  confirmButtonColor: '#3085d6',
                  cancelButtonColor: '#d33',
                  confirmButtonText: 'OK'
                }).then((result) => {
                  if (result.isConfirmed) {
                    getLedger(center);
                  }
                })
              } else {
                Swal.fire(
                  'Payment Failed',
                  'Please try again!',
                  'error'
                )
              }
            },
            theme: "#272B35" // color hex
          }
          easebuzzCheckout.initiatePayment(options);
        }

        function updatePayment(response, center) {
          $.ajax({
            url: '/app/easebuzz/response',
            type: 'POST',
            data: {
              response
            },
            dataType: 'json',
            success: function (response) {
              if (response.status) {
                getLedger(center);
              } else {
                notification('danger', data.message);
              }
            },
            error: function (response) {
              console.error(response);
            }
          })
        }
      </script>
    <?php } ?>
  <?php }
}

?>