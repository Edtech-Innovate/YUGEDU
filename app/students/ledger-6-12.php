<?php
if (isset($_GET['id'])) {
  require '../../includes/db-config.php';
  session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
  $id = intval($_GET['id']);

  $heads = array();
  $fee_heads = $conn->query("SELECT ID, Name FROM Fee_Structures WHERE University_ID = " . $_SESSION['university_id']);
  while ($fee_head = $fee_heads->fetch_assoc()) {
    $heads[$fee_head['ID']] = $fee_head['Name'];
  }

  $student = $conn->query("SELECT Admission_Sessions.Name as Session, Admission_Types.Name as Admission_Type, Courses.Short_Name as Course, Sub_Courses.Name as Sub_Course, Students.Duration as Duration, Student_Documents.Location, Modes.Name as Mode FROM Students LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID = Admission_Sessions.ID LEFT JOIN Admission_Types ON Students.Admission_Type_ID = Admission_Types.ID LEFT JOIN Courses ON Students.Course_ID = Courses.ID LEFT JOIN Sub_Courses ON Students.Sub_Course_ID = Sub_Courses.ID LEFT JOIN Student_Documents ON Students.ID = Student_Documents.Student_ID AND Student_Documents.Type = 'Photo' LEFT JOIN Modes ON Sub_Courses.Mode_ID = Modes.ID WHERE Students.ID = $id");
  $student = $student->fetch_assoc();
?>
  <div class="row d-flex justify-content-center">
    <div class="col-md-4">
      <div class="card card-transparent">
        <div class="card-header bg-transparent text-center">
          <img class="profile_img" src="<?= $student['Location'] ?>" alt="">
          <h5><?= $student['Session'] ?> (<?= $student['Admission_Type'] ?>)</h5>
          <h6><?= $student['Course'] ?> (<?= $student['Sub_Course'] ?>)</h6>
        </div>
      </div>
    </div>
  </div>
  <div class="row" style="margin-bottom:20px">
    <div class="col-md-12 d-flex justify-content-end">
      <div>
        <?php if (isset($_SESSION['gateway'])) { ?>
          <button type="button" class="btn btn-primary" onclick="add('<?php echo $_SESSION['gateway'] == 1 ? 'easebuzz' : '' ?>', 'md')"> Pay Online</button>
        <?php } ?>
        <button class="btn btn-primary" onclick="add('offline-payments', 'lg')">Pay Offline</button>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover table-borderless">
              <thead>
                <tr>
                  <th><?= $student['Mode'] ?></th>
                  <th>Date</th>
                  <th>Particular</th>
                  <th>Source</th>
                  <th>Transaction ID</th>
                  <th class="text-right">Debit</th>
                  <th class="text-right">Credit</th>
                  <th class="text-right">Balance</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <?php
                  $balance = 0;
                  $ledgers = $conn->query("SELECT * FROM Student_Ledgers WHERE Student_Ledgers.Student_ID = $id AND Duration <= '" . $student['Duration'] . "' AND Status = 1 ORDER BY Student_Ledgers.Type, Student_Ledgers.Created_At");
                  while ($ledger = $ledgers->fetch_assoc()) {
                    $fees = json_decode($ledger['Fee'], true);
                    foreach ($fees as $key => $value) {
                      if (empty($value)) {
                        continue;
                      }
                  ?>
                      <td><?= $ledger['Duration'] ?></td>
                      <td><?= date("d-m-Y", strtotime($ledger['Created_At'])) ?></td>
                      <td><?php echo $key == "Paid" ? '' : ($key == "Late Fine" ? $key : $heads[$key]) ?></td>
                      <td><?= $ledger['Source'] ?></td>
                      <td><?= $ledger['Transaction_ID'] ?></td>
                      <td class="text-right"><?php echo $debit = $ledger['Type'] == 1 ? $value : 0 ?></td>
                      <td class="text-right"><?php echo $credit = $ledger['Type'] == 2 ? $value : 0 ?></td>
                      <td class="text-right">
                        <?php $balance = ($balance + $credit) - $debit;
                        if ($balance < 0) {
                          echo " <span style='color:red'>-" . -1 * ($balance) . "Dr</span>";
                        } elseif ($balance > 0) {
                          echo " <span style='color:green'>" . $balance . "Cr</span>";
                        } else {
                          echo $balance;
                        }
                        ?>
                      </td>
                </tr>
            <?php  }
                  }
            ?>
            </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php }
?>