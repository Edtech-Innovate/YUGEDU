<link href="../../assets/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" media="screen" />
<link href="../../assets/plugins/bootstrap-tag/bootstrap-tagsinput.css" rel="stylesheet" type="text/css" />
<!-- Modal -->
<div class="modal-header clearfix text-left">
  <button aria-label="" type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="pg-icon">close</i>
  </button>
  <h5>Add <span class="semi-bold">Syllabus</span></h5>
</div>
<style>
  .modal-dialog.modal-lg {
    width: 100% !important;
  }

  .card-body {
    padding: 15px;
  }

  .card {
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.19), 0 6px 6px rgba(0, 0, 0, 0.23);
    border-radius: 2px !important;
    -webkit-border-radius: 2px;
    -moz-border-radius: 2px;
    -webkit-transition: all 0.2s ease;
    transition: all 0.2s ease;
    border: 1px solid transparent;
    position: relative;
    margin-bottom: 20px;
    width: 100%;
    word-wrap: none;
    background: #fff;
  }

  .col-md-6.semesterTab {
    padding-left: unset !important;
  }
</style>

<?php
require '../../includes/db-config.php';
session_start();
?>
<form role="form" id="form-add-sub-course" action="/app/syllabus/store" method="POST" enctype="multipart/form-data">
  <div class="modal-body">
    <div class="row">
      <div class="col-md-6">
        <div class="form-group form-group-default required">
          <label>University</label>
          <select class="full-width" style="border: transparent;" id="university_id" name="university_id"
            onchange="getDetails(this.value);">
            <option value="">Choose</option>
            <?php

            $university_query = $_SESSION['Role'] != 'Administrator' ? " AND ID =" . $_SESSION['university_id'] : '';
            $universities = $conn->query("SELECT ID, CONCAT(Universities.Short_Name, ' (', Universities.Vertical, ')') as Name FROM Universities WHERE ID IS NOT NULL $university_query");
            while ($university = $universities->fetch_assoc()) { ?>
              <option value="<?= $university['ID'] ?>"><?= $university['Name'] ?></option>
            <?php } ?>
          </select>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group form-group-default required">
          <label>Specialization</label>
          <select class="full-width" style="border: transparent;" id="course" name="course"
            onchange="getDuration(this.value)">
            <option value="">Choose</option>
          </select>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 skillTab">
        <div class="form-group form-group-default required">
          <label>Duration</label>
          <select class="full-width" style="border: transparent;" id="duration" name="duration"
            onchange="getSubject(this.value)">

          </select>
        </div>
      </div>
      <div class="col-md-6 subjectTab">
        <div class="form-group form-group-default required">
          <label>Subjects</label>
          <select class="full-width" style="border: transparent;" id="subject" name="subject">
          </select>
        </div>
      </div>
    </div>
    <!-- start kp -->
    <div class="row" style="float: right;">
        <div class="btn btn-outline-primary rounded add-more-unit mb-3"> + Add Unit</div>
    </div>
    <div class="after-add-more-units"></div>
    </div>
    <!-- end kp -->
  </div>
  <div class="modal-footer clearfix text-end">
    <div class="col-md-4 m-t-10 sm-m-t-10">
      <button aria-label="" type="submit" class="btn btn-primary btn-cons btn-animated from-left">
        <span>Save</span>
        <span class="hidden-block">
          <i class="pg-icon">tick</i>
        </span>
      </button>
    </div>
  </div>
</form>


<script type="text/javascript" src="../../assets/plugins/select2/js/select2.full.min.js"></script>
<script type="text/javascript">
  $(document).ready(function () {
    let unitCount = 0;
    let moduleCount = {};

    // Add Unit
    $(".add-more-unit").click(function () {
        const unitId = unitCount;
        moduleCount[unitId] = 0;

        let unitHtml = `
        <div class="unit-control card card-body mb-3">
            <h4 class="mt-0">Unit ${unitId+1}</h4>
            <div class="row">
                <div class="col-md-5">
                    <div class="form-group form-group-default required">
                        <label>Unit Name</label>
                        <input type="text" name="unit_name[]" class="form-control" placeholder="ex: Unit 1" required>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group form-group-default required">
                        <label>Unit Code</label>
                        <input type="text" name="unit_code[]" class="form-control" placeholder="ex: UNIT123" required>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-center">
                    <a class="remove-unit text-danger"><i class="uil uil-minus-circle icon-xs cursor-pointer"></i></a>
                </div>
            </div>
            <div id="module-wrapper-${unitId}"></div>
            <div class="btn btn-outline-primary mt-2 add-more-module" data-unit="${unitId}">+ Add Module</div>
        </div>`;

        $(".after-add-more-units").append(unitHtml);
        unitCount++;
    });

    // Remove Unit
    $("body").on("click", ".remove-unit", function () {
        $(this).closest(".unit-control").remove();
    });

    // Add Module
    $("body").on("click", ".add-more-module", function () {
        const unitId = $(this).data("unit");
        const moduleId = moduleCount[unitId];

        let moduleHtml = `
        <div class="module-control card card-body mb-2">
            <div class="row">
                <div class="col-md-10">
                    <div class="form-group form-group-default required">
                        <label>Module Name</label>
                        <input type="text" name="module_name[${unitId}][]" class="form-control" placeholder="ex: Module 1" required>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-center">
                    <a class="remove-module text-danger"><i class="uil uil-minus-circle icon-xs cursor-pointer"></i></a>
                </div>
            </div>
        </div>`;

        $(`#module-wrapper-${unitId}`).append(moduleHtml);
        moduleCount[unitId]++;
    });

    // Remove Module
    $("body").on("click", ".remove-module", function () {
        $(this).closest(".module-control").remove();
    });
});
</script>
<script>
  $(function () {
    $("#eligibilities").select2();
    $("#course_category").select2();
    // $(".skillTab,.subjectTab").hide();
  })

  function getDetails(id) {
    $(".skillTab").show();
    $("#duration").empty();
    $("#subject").empty();

    $.ajax({
      url: '/app/syllabus/courses?id=' + id,
      type: 'GET',
      success: function (data) {
        $('#course').html(data);
      }
    });
  }

  function getDuration(id) {
    var university_id = $("#university_id").val();
    $.ajax({
      url: '/app/syllabus/semester?id=' + id + '&university_id=' + university_id,
      type: 'GET',
      success: function (data) {
        $('.subjectTab').show();
        $('#duration').html(data);
      }
    });
  }

  function getSubject(duration) {
    var sub_course_id = $("#course").val();
    var university_id = $("#university_id").val();
    $.ajax({
      url: '/app/syllabus/subjects?id=' + sub_course_id + '&university_id=' + university_id + '&duration=' + duration,
      type: 'GET',
      success: function (data) {
        $('#subject').html(data);
      }
    });
  }

  $(function () {
    $('#form-add-sub-course').validate({
      rules: {
        name: {
          required: true
        },
        short_name: {
          required: true
        },
        university_id: {
          required: true
        },
        course: {
          required: true
        },
      },
      highlight: function (element) {
        $(element).addClass('error');
        $(element).closest('.form-control').addClass('has-error');
      },
      unhighlight: function (element) {
        $(element).removeClass('error');
        $(element).closest('.form-control').removeClass('has-error');
      }
    });
  })

  $("#form-add-sub-course").on("submit", function (e) {
    if ($('#form-add-sub-course').valid()) {
      // $(':input[type="submit"]').prop('disabled', true);
      var formData = new FormData(this);
      $.ajax({
        url: this.action,
        type: 'post',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (data) {
          if (data.status == 200) {
            // $('.modal').modal('hide');
            notification('success', data.message);
            $('#sub-courses-table').DataTable().ajax.reload(null, false);
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