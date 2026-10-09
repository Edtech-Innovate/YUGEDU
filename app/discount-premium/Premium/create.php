<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_GET['id'])) {

  require '../../../includes/db-config.php';
  session_start();
  $id = mysqli_real_escape_string($conn, $_GET['id']);

  $studentDuration = 1;
  $balance = 0;
  $Course_Fee = 0;
  $Course_Fees = 0;
  $discount = 0;
  $premium = 0;
  $University_ID = 0;

  // Fetch Student Info
  $studentQuery = $conn->query("
      SELECT 
        Students.First_Name,
        Students.University_ID,
        Students.Duration,
        Students.Sub_Course_ID,
        Students.Admission_Session_ID,
        Students.Middle_Name,
        Students.Last_Name,
        Sub_Courses.Name AS Sub_Course_Name,
        Courses.Name AS Course_Name
      FROM Students
      LEFT JOIN Courses ON Students.Course_ID = Courses.ID
      LEFT JOIN Sub_Courses ON Students.Sub_Course_ID = Sub_Courses.ID
      WHERE Students.ID = $id
  ");

  $student = mysqli_fetch_assoc($studentQuery);

  $studentName = trim($student['First_Name'] . ' ' . $student['Middle_Name'] . ' ' . $student['Last_Name']);
  $course = $student['Course_Name'] . ' (' . $student['Sub_Course_Name'] . ')';

  // Base Query
  $ledgerQuery = $conn->query("
      SELECT *
      FROM Student_Ledgers
      WHERE Student_ID = '{$id}'
        AND Status = 1
        AND Duration <= '{$student['Duration']}'
  ");

  while ($ledger = $ledgerQuery->fetch_assoc()) {

      $University_ID = $ledger['University_ID'];
      $premium = floatval($ledger['Premium']);
      $discount = floatval($ledger['Discount']);

      // Decode Fee Into Array
      $fees = json_decode($ledger['Fee'], true);
      if (!is_array($fees)) continue;

      foreach ($fees as $value) {
          $debit  = ($ledger['Type'] == 1) ? $value : 0;
          $credit = ($ledger['Type'] == 2) ? $value : 0;
          $balance = ($balance + $credit) - $debit;
      }

      // Numeric course fee
      $courseFeeNumeric = (-1 * $balance);
// echo('<pre>');print_r($premium);die;
      // Apply formula
      $Course_Fees = ($courseFeeNumeric + $premium) - $discount;
//   echo('<pre>');print_r($premium);die;
      // Formatted course fee
      $Course_Fee = "₹ " . number_format($courseFeeNumeric, 2);

      $studentDuration = $ledger['Duration'];
  }
?>

<!-- Modal -->
<div class="modal-header clearfix text-left mb-4">
    <button aria-label="" type="button" class="close" data-dismiss="modal" aria-hidden="true">
        <i class="pg-icon">close</i>
    </button>
    <h5>Course Premium</h5>
</div>

<form role="form" id="form-Premium" action="/app/discount-premium/Premium/store" method="POST">
    <div class="modal-body">

        <div class="row">
            <div class="col-md-12">
                <h3><?= $studentName ?></h3>
                <p class="text-black"><?= $course ?></p>
                <p class="text-black">Duration: <?= $studentDuration ?></p>
                <p class="text-black">Total Fees: <b><?= $Course_Fees ?></b></p>
                <p class="text-black">Current Premium: <b><?= $premium ?></b></p>
            </div>
        </div>

        <div class="form-group mt-2">
            <label>Premium Amount</label>
            <input type="number" name="Premium" class="form-control" value="<?= $premium ?>">
        </div>

        <input type="hidden" name="studentDuration" value="<?= $studentDuration ?>">
        <input type="hidden" name="University_ID" value="<?= $University_ID ?>">
        <input type="hidden" name="id" value="<?= $id ?>">

    </div>

    <div class="modal-footer flex justify-content-between">

        <!--<div class="m-t-10 sm-m-t-10">-->
            <?php if ($premium > 0) { ?>
                <!--<button type="button" onclick="deletePremium('<?= $id ?>')" class="btn btn-danger btn-cons btn-animated from-left">-->
                <!--    <span>Delete</span>-->
                <!--    <span class="hidden-block"><i class="uil uil-trash"></i></span>-->
                <!--</button>-->
            <?php } ?>
        <!--</div>-->

        <div class="m-t-10 sm-m-t-10">
            <button type="submit" class="btn btn-primary btn-cons btn-animated from-left">
                <span>Update</span>
                <span class="hidden-block"><i class="pg-icon">tick</i></span>
            </button>
        </div>

    </div>
</form>

<script type="text/javascript">
$("#form-Premium").on("submit", function(e) {
    e.preventDefault();

    var formData = new FormData(this);

    $.ajax({
        url: this.action,
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(data) {
            if (data.status == 200) {
                $('.modal').modal('hide');
                notification('success', data.message);
                $('.table').DataTable().ajax.reload(null, false);
            } else {
                notification('danger', data.message);
            }
        }
    });
});

function deletePremium(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "Premium will be set to 0",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "/app/discount-premium/Premium/destroy?id=" + id,
                type: 'DELETE',
                dataType: 'json',
                success: function(data) {
                    if (data.status == 200) {
                        notification('success', data.message);
                        $('.modal').modal('hide');
                        $('.table').DataTable().ajax.reload(null, false);
                    } else {
                        notification('danger', data.message);
                    }
                }
            });
        }
    })
}
</script>

<?php } ?>
