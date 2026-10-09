<?php
if (isset($_GET['id'])) {
  session_start();
  require '../../includes/db-config.php';

  $searchQuery = '';
  $filterQueryUser = isset($_SESSION['filterByUser']) ? $_SESSION['filterByUser'] : '';
  $id = intval($_GET['id']);

  $adm_session = isset($_SESSION['adm_session']) ? $_SESSION['adm_session'] : "";

  $already = [];
  $already_ids = [];

  /** USER / CENTER CONDITIONS */
  if ($_SESSION['Role'] == 'Sub-Center') {
    $user_id = getCenterIdFunc($conn, $id);
    $userQuery = " AND Students.Added_By = $id AND `User_ID` = $user_id";
  } else {
    $userQuery = " AND `User_ID` = $id";
  }

  if ($_SESSION['Role'] == 'Sub-Center' || $_SESSION['Role'] == 'Center') {
    $center_id = " AND Students.Added_For = " . $_SESSION['ID'];
  } else {
    $center_id = "";
  }

  /** NORMAL INVOICES */
  $inv_query = "
        SELECT Student_ID, Invoices.Duration 
        FROM Invoices 
        LEFT JOIN Payments ON Invoices.Invoice_No = Payments.Transaction_ID AND Payments.Type = 1 
        LEFT JOIN Students ON Invoices.Student_ID = Students.ID  
        WHERE Invoices.University_ID = " . $_SESSION['university_id'] . " 
        AND Payments.Status != 2 $userQuery";

  $invoices = $conn->query($inv_query);
  while ($invoice = $invoices->fetch_assoc()) {
    $already[$invoice['Student_ID']] = $invoice['Duration'];
    $already_ids[] = $invoice['Student_ID'];
  }

  /** WALLET INVOICES */
  $wallet_query = "
        SELECT Student_ID, Wallet_Invoices.Duration 
        FROM Wallet_Invoices 
        LEFT JOIN Wallet_Payments ON Wallet_Invoices.Invoice_No = Wallet_Payments.Transaction_ID AND Wallet_Payments.Type = 3 
        LEFT JOIN Students ON Wallet_Invoices.Student_ID = Students.ID  
        WHERE `User_ID` = $id 
        AND Wallet_Invoices.University_ID = " . $_SESSION['university_id'] . " 
        AND Wallet_Payments.Status != 2 $userQuery";

  $wallets = $conn->query($wallet_query);
  while ($wallet = $wallets->fetch_assoc()) {
    $already[$wallet['Student_ID']] = $wallet['Duration'];
    $already_ids[] = $wallet['Student_ID'];
  }

  $query = empty($already_ids) ? "" : " AND Students.ID NOT IN (" . implode(',', $already_ids) . ")";
  $searchQuery .= $adm_session . $filterQueryUser . $query;

  /** FETCH STUDENTS */
  $student_query = "
        SELECT Students.ID,Students.Sub_Course_ID,Students.Admission_Session_ID,
               Students.University_ID,Students.Added_For,Students.Duration,
               First_Name,Middle_Name,Last_Name,Unique_ID,
               Admission_Sessions.Name AS Admission_Session
        FROM Students 
        LEFT JOIN Admission_Sessions 
               ON Students.Admission_Session_ID = Admission_Sessions.ID 
        WHERE Students.University_ID = " . $_SESSION['university_id'] . " 
        AND Step = 4 
        AND Process_By_Center IS NULL 
        $searchQuery 
        ORDER BY Students.ID DESC";

  $students = $conn->query($student_query);

  if ($students->num_rows == 0) {
    echo '<div class="row"><div class="col-lg-12 text-center">No student(s) found!</div></div>';
  } else {
?>

    <div class="row">
      <div class="col-md-12 d-flex justify-content-end">
        <?php if ($_SESSION['Role'] == 'Center' || $_SESSION['Role'] == 'Sub-Center') { ?>
          <div class="row m-b-20">
            <div class="col-md-12 d-flex justify-content-end">
              <button type="button" class="btn add_btn_form" onclick="pay('Offline')">Pay Offline</button>
              <button type="button" class="btn add_btn_form" onclick="pay('wallet')">Pay Wallet</button>
            </div>
          </div>
        <?php } ?>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-12">
        <div class="table-bordered px-3 py-2">
          <table class="table table-hover table-responsive" style="margin-top:0px !important; width:100% !important;">
            <thead>
              <tr>
                <th style="width:40%">Student ID</th>
                <th style="width:20%">Student Name</th>
                <th style="width:10%">Adm. Session</th>
                <th style="width:30%">Owner</th>
                <th style="width:10%">Course Fee</th>
                <th style="width:40%">Discount</th>
                <th style="width:40%">Premium</th>
                <th style="width:10%">Payable</th>
              </tr>
            </thead>

            <tbody>
              <?php
              while ($student = $students->fetch_assoc()) {

                if (
                  in_array($student['ID'], $already_ids) &&
                  $student['Duration'] == $already[$student['ID']]
                ) {
                  continue;
                }

                $student_name = array_filter([$student['First_Name'], $student['Middle_Name'], $student['Last_Name']]);

                $studentUniversityID = $student['University_ID'];
                $studentAddmissionID = $student['Admission_Session_ID'];
                $studentSubCourseId = $student['Sub_Course_ID'];

                /** GET SCHEME */
                $schemeData = $conn->query("SELECT Scheme FROM Admission_Sessions WHERE ID=$studentAddmissionID")->fetch_assoc();
                $schemeID = json_decode($schemeData['Scheme'], true)['schemes'][0];

                /** FEE STRUCTURE */
                $feeStructureID = $conn->query("
                        SELECT GROUP_CONCAT(ID) AS fs 
                        FROM Fee_Structures 
                        WHERE University_ID='$studentUniversityID'
                    ")->fetch_assoc()['fs'];

                $totalCourseFee = $conn->query("
                        SELECT SUM(Fee) AS totalFee 
                        FROM Fee_Constant 
                        WHERE Scheme_ID=$schemeID 
                        AND University_ID=$studentUniversityID 
                        AND Fee_Structure_ID IN($feeStructureID) 
                        AND Sub_Course_ID=$studentSubCourseId
                    ")->fetch_assoc()['totalFee'];

              ?>
                <tr>
                  <td><b><?= !empty($student['Unique_ID']) ? $student['Unique_ID'] : sprintf("%'.06d\n", $student['ID']) ?></b></td>
                  <td><?= implode(" ", $student_name) ?></td>
                  <td><?= $student['Admission_Session'] ?></td>
                  <td><?= getAddedBy($conn, $student['Added_For']) ?></td>
                  <td><?= $totalCourseFee ?></td>

                  <!-- Discount -->
                  <?php
                  $ledgers = $conn->query(
                    "
    SELECT * FROM Student_Ledgers 
    WHERE Student_ID = " . $student['ID'] . " 
      AND Status = 1 
      AND Duration <= " . $student['Duration']
                  );
                  $courseDiscountPremium = $ledgers->fetch_assoc();
                  ?>

                  <!-- Discount -->
                  <td>
                    <div class="d-flex justify-content-center flex-row align-items-center" style="width:120px">
                      <?php
                      if ($courseDiscountPremium && $courseDiscountPremium['Discount'] != 0) {
                        echo "₹" . $courseDiscountPremium['Discount'];
                      }
                      ?>
                      <i class="ti ti-edit-circle add_btn_form h6 cursor-pointer p-1 ml-2"
                        title="Add Discount"
                        onclick="addDiscountPremium('Discount', <?= $student['ID'] ?>)">
                      </i>
                    </div>
                  </td>

                  <!-- Premium -->
                  <td>
                    <div class="d-flex justify-content-center flex-row align-items-center" style="width:120px">
                      <?php
                      if ($courseDiscountPremium && $courseDiscountPremium['Premium'] != 0) {
                        echo "₹" . $courseDiscountPremium['Premium'];
                      }
                      ?>
                      <i class="ti ti-edit-circle add_btn_form h6 cursor-pointer p-1 ml-2"
                        title="Add Premium"
                        onclick="addDiscountPremium('Premium', <?= $student['ID'] ?>)">
                      </i>
                    </div>
                  </td>


                  <!-- Payable -->
                  <td>
                    <?php
                    $balance = 0;
                    $ledgers = $conn->query("SELECT * FROM Student_Ledgers WHERE Student_ID=" . $student['ID'] . " AND Status=1 AND Duration <= " . $student['Duration']);

                    while ($ledger = $ledgers->fetch_assoc()) {
                      $fees = json_decode($ledger['Fee'], true);
                      foreach ($fees as $value) {
                        $debit = $ledger['Type'] == 1 ? $value : 0;
                        $credit = $ledger['Type'] == 2 ? $value : 0;
                        $balance = ($balance + $credit) - $debit;
                      }
                    }

                    echo "₹ " . (-1 * $balance);
                    ?>
                  </td>
                </tr>

              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

<?php }
} ?>

<?php if ($_SESSION['Role'] == 'Accountant') { ?>
  <script>
    function addDiscountPremium(type, id) {
      $.ajax({
        url: '/app/discount-premium/' + type + '/create?id=' + id,
        type: 'GET',
        success: function(data) {
          $('#md-modal-content').html(data);
          $('#mdmodal').modal('show');
        }
      });
    }
  </script>
<?php } ?>