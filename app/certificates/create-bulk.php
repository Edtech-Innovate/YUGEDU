<?php
require '../../includes/db-config.php';
session_start();
?>

<!-- Modal -->
<div class="modal-header clearfix text-left">
  <button aria-label="" type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="pg-icon">close</i>
  </button>
  <h5>Create<span class="semi-bold"> Certificate</span></h5>
</div>

<form role="form" id="form-add-e-book" action="/app/certificates/download-bulk-certificate" method="POST" enctype="multipart/form-data">
  <div class="modal-body">
    <div class="row">
      <div class="col-md-6">
        <div class="form-group form-group-default required">
          <label>Program Type</label>
          <select required class="full-width" style="border: transparent;" id="course_type_id" name="course_type_id" onchange="getSubCourse(this.value);">
            <option value="">Select</option>
            <?php
            $programs = $conn->query("SELECT ID,Name,Short_Name FROM Courses WHERE University_ID = 41");
            while ($program = $programs->fetch_assoc()) { ?>
              <option value="<?= $program['ID'] ?>">
                <?= $program['Name'] . ' (' . $program['Short_Name'] . ')' ?>
              </option>
            <?php } ?>
          </select>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group form-group-default ">
          <label>Specialization/Course</label>
          <select class="full-width"  style="border: transparent;" id="sub_course_id" name="course_id" onchange="getSubjects(this.value);">
            <option value="">Select</option>
          </select>
        </div>
      </div>
      <div class="col-md-12">
        <div class="form-group form-group-default ">
          <label>Category</label>
          <select class="full-width" style="border: transparent;" id="category" name="category" onchange="getCategory(this.value) ">
            <option value="">Choose Category</option>
            <option value="3">3 Months</option>
            <option value="6">6 Months</option>
            <option value="11/certified">11 Months Certified</option>
            <option value="11/advance-diploma">11 Months Advance Diploma</option>
          </select>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="form-group form-group-default ">
          <label>Student</label>
          <input type="text" class="full-width" style="border: transparent;" id="student_id" name="student_id">
        </div>
      </div>
      <div class="col-md-12">
        <div class="form-group form-group-default required">
          <label>Background Template</label>
          <select class="full-width" style="border: transparent;" id="background_type" name="background_type">
            <option value="with_background">With Background</option>
            <option value="no_background">No Background</option>
          </select>
        </div>
      </div>
    </div>
  </div>
  <div class="modal-footer clearfix justify-content-center">
    <div class="col-md-4 m-t-10 sm-m-t-10">
      <input type="submit" class="btn btn-primary btn-cons btn-animated from-left" value="Save">
    </div>
  </div>
</form>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Include Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
  // Fix Select2 inside Bootstrap modal
  $('#sub_course_id').select2({
    placeholder: "Select Course",
    allowClear: true,
    width: '100%',
    dropdownParent: $('#form-add-e-book') // ✅ CRITICAL FIX
  });
});
</script>

<script>
  function getSubCourse(course_id) {
    $.ajax({
      url: '/app/certificates/get-subcourse?course_id=' + course_id,
      type: 'GET',
      success: function(data) {
        $('#sub_course_id').html(data);
      }
    });
  }
</script>