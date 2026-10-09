<?php

if (isset($_POST['stu_id']) && isset($_POST['assignment_id']) && isset($_POST['subject_id'])) {
    require $_SERVER['DOCUMENT_ROOT'] . '/includes/db-config.php';
    session_start();
    $stu_id = intval($_POST['stu_id']);
    $assignment_id = intval($_POST['assignment_id']);
    $subject_id = intval($_POST['subject_id']);
    $data = $conn->query("select enddate from student_assignment_end_date where student_id= '$stu_id' and assignment_id = '$assignment_id' and subject_id = '$subject_id'");
    $data = $data->fetch_assoc();

?>
<div class="modal-header clearfix text-left">
  <button aria-label="" type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="pg-icon">close</i>
  </button>
  <h5 class="text-black font-weight-bold">Edit End Date of Assignment</h5>
</div>
<div class="modal-body">
    <form role="form" id="editEndDateForm" method="post" action="/app/assignments/update-date.php" enctype="multipart/form-data">
        <input type="hidden" name="assignment_id" value="<?php echo $assignment_id; ?>">
        <input type="hidden" name="stu_id" value="<?php echo $stu_id; ?>">
        <input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">


        <!--<div class="mb-3">-->
        <!--    <h5 class="mb-3">Edit End Date of Assignment</h5>-->
        <!--</div>-->

        <div class="form-group form-group-default mb-3">
            <label for="enddate">End Date</label>
            <input type="date" class="form-control" id="enddate" value="<?= (!empty($data)) ? $data['enddate'] :""  ?>" name="enddate" required>
        </div>

        <div class="text-end mt-4">
            <!--<button type="button" class="btn btn-secondary me-2" data-dismiss="modal">Close</button>-->
            <!--<button type="submit" class="btn btn-primary">Update</button>-->
         <button aria-label="" type="button" data-dismiss="modal" class=" btn btn-secondary mr-2">
         <i class="ti ti-circle-dashed-x mr-2"></i> Close</button>
         <button aria-label="" type="submit" class="btn btn-primary ">
         <i class="ti ti-circle-check mr-2"></i> Update</button>
        </div>
    </form>
</div>

<script>
  $(document).ready(function () {
        var today = new Date().toISOString().split('T')[0];
        $('#enddate').attr('min', today);
    });
</script>
<script>

    $("#editEndDateForm").on("submit", function(e) {
    if ($('#editEndDateForm').valid()) {
      $(':input[type="submit"]').prop('disabled', true);
      var formData = new FormData(this);
      $.ajax({
        url: this.action,
        type: 'post',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(data) {
          if (data.status == 200) {
            $('.modal').modal('hide');
            notification('success', data.message);
            $('#courses-table').DataTable().ajax.reload(null, false);
          } else {
            $(':input[type="submit"]').prop('disabled', false);
            notification('danger', data.message);
          }
        }
      });
      e.preventDefault();
    }
  });
</script>
<?php } ?>