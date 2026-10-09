<?php if (isset($_GET['id'])) {
  session_start();
  require '../../../../includes/db-config.php';

  $id = intval($_GET['id']);

  $students = $conn->query("SELECT Payments.Transaction_ID, Payments.Gateway_ID, Invoices.Amount, Invoices.Duration, Students.First_Name, Students.Middle_Name, Students.Last_Name, (RIGHT(CONCAT('000000', Students.ID), 6)) as Student_ID, Students.Unique_ID,  Invoices.Created_At FROM Invoices LEFT JOIN Payments ON Invoices.Invoice_No = Payments.Transaction_ID LEFT JOIN Students ON Invoices.Student_ID = Students.ID WHERE Invoices.`User_ID` = $id AND Invoices.University_ID = " . $_SESSION['university_id'] . " AND Payments.Status = 1");
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
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Processed On</th>
                <th>Particular</th>
                <th>Transaction ID</th>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Duration</th>
                <th>Paid</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($student = $students->fetch_assoc()) {
                $student_name = array_filter(array($student['First_Name'], $student['Middle_Name'], $student['Last_Name'])) ?>
                <tr>
                  <td><?= date("d-m-Y", strtotime($student['Created_At'])) ?></td>
                  <td><?= $student['Gateway_ID'] ?></td>
                  <td><?= $student['Transaction_ID'] ?></td>
                  <td><b><?php echo !empty($student['Unique_ID']) ? $student['Unique_ID'] : $student['Student_ID'] ?></b></td>
                  <td><?= implode(" ", $student_name) ?></td>
                  <td><?= $student['Duration'] ?></td>
                  <td><?= (-1) * $student['Amount'] ?></td>
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
