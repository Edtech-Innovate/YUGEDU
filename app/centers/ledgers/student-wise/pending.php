<?php if (isset($_GET['id'])) {
  session_start();
  require '../../../../includes/db-config.php';

  $id = intval($_GET['id']);
  $searchQuery = '';
  $filterQueryUser = "";
  if (isset($_SESSION['filterByUser'])) {
    $filterQueryUser = $_SESSION['filterByUser'];
  }

  $adm_session = "";
  if (isset($_SESSION['adm_session'])) {
    $adm_session = $_SESSION['adm_session'];
  }

  $searchQuery .= $adm_session . $filterQueryUser;


  $students = $conn->query("SELECT Students.ID,Students.Added_For, Students.First_Name, Students.Middle_Name, Students.Last_Name, Students.Unique_ID, Students.Duration, i.Student_ID, i.Duration FROM Students LEFT JOIN Invoices AS i ON Students.ID = i.Student_ID LEFT JOIN Payments AS p  ON i.Invoice_No = p.Transaction_ID  WHERE Students.University_ID = " . $_SESSION['university_id'] . "  AND Students.Step = 4 AND p.Status = 0 AND i.Student_ID IS NOT NULL $searchQuery ORDER BY `i`.`Created_At` DESC ");
  if ($students->num_rows == 0) { ?>
    <div class="row">
      <div class="col-lg-12 text-center">
        No student(s) found!
      </div>
    </div>
  <?php } else {
  ?>
    <div class="row">
      <div class="col-lg-12">
        <div class="table-bordered px-3 py-2">
          <table class="table table-hover table-responsive">
            <thead>
              <tr>
                <th style="width:10% !impoortant;">Student ID</th>
                <th style="width:40% !impoortant;">Student Name</th>
                <th style="width:40% !impoortant;">Added By</th>
                <th style="width:10% !impoortant;">Payable</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($student = $students->fetch_assoc()) {
                $student_name = array_filter(array($student['First_Name'], $student['Middle_Name'], $student['Last_Name'])) ?>
                <tr>
 <td><b><?php echo !empty($student['Unique_ID']) ? $student['Unique_ID'] : sprintf("%'.06d\n", $student['ID']) ?></b></td>
                  <td><?= implode(" ", $student_name) ?></td>
                  <td><?= getAddedBy($conn, $student['Added_For']) ?></td>
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
<?php }
}

?>
