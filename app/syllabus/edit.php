<?php
if (isset($_GET['subject_id']) && isset($_GET['duration']) && isset($_GET['sub_course_id']) && isset($_GET['uni_id'])) {
  require '../../includes/db-config.php';
  session_start();


  $subject_id = intval($_GET['subject_id']);
  $sub_course_id = intval($_GET['sub_course_id']);
  $uni_id = intval($_GET['uni_id']);
  $duration = mysqli_real_escape_string($conn, $_GET['duration']);

  $chapterSql = $conn->query("SELECT * FROM Chapter WHERE Subject_ID = $subject_id AND University_ID =$uni_id AND  Sub_Course_ID=$sub_course_id AND Semester = '" . $duration . "'");
  if ($chapterSql->num_rows > 0) {
  }
  // print_r($sub_course);die;
?>
  <link href="../../assets/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" media="screen" />
  <link href="../../assets/plugins/bootstrap-tag/bootstrap-tagsinput.css" rel="stylesheet" type="text/css" />
  <!-- Modal -->
  <div class="modal-header clearfix text-left">
    <button aria-label="" type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="pg-icon">close</i>
    </button>
    <h5 class="text-black font-weight-bold">Edit <span class="semi-bold">Syllabus</span></h5>
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

  ?>
  <form role="form" id="form-add-sub-course" action="/app/syllabus/update" method="POST" enctype="multipart/form-data">
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

                <option value="<?= $university['ID'] ?>" <?php if ($uni_id == $university['ID']) {
                                                            echo "selected";
                                                          } else {
                                                            echo "";
                                                          } ?>><?= $university['Name'] ?></option>
              <?php } ?>
            </select>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group form-group-default required">
            <label>Specialization</label>
            <select class="full-width" style="border: transparent;" id="course_id" name="course"
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
            <select class="full-width" style="border: transparent;" id="duration_id" name="duration"
              onchange="getSubject(this.value)">

            </select>
          </div>
        </div>
        <div class="col-md-6 subjectTab">
          <div class="form-group form-group-default required">
            <label>Subjects</label>
            <select class="full-width" style="border: transparent;" id="subject_id" name="subject">
            </select>
          </div>
        </div>
      </div>
      <!-- start kp -->
      <div class="row" style="float: right;">
        <div class="btn add_btn_form  rounded add-more mb-3"><i class="ti ti-circle-plus"></i> Add Chapter
          <!-- <i class=" uil uil-plus-circle icon-xs cursor-pointer mt-1 ms-0 pe-2"></i> -->
        </div>
      </div>

      <?php if ($chapterSql->num_rows > 0) {
        $i = 1;
        while ($unit = $chapterSql->fetch_assoc()) { // Chapters are now Units
          $modulesql = $conn->query("SELECT * FROM Chapter_Units WHERE Chapter_ID = '{$unit['ID']}'");
      ?>
          <div class="after-add-more-edit">
            <div class="row control1 card card-body shadow-none mb-3">
              <h4 class="mt-0">Chapter  <?= $i ?></h4>
              <div class="control-group input-group" style="margin-top:10px">
                <div class="col-md-5">
                  <div class="form-group form-group-default required">
                    <label>Chapter  Name</label>
                    <input type="text"
                      name="unit_name[uedit][unit_<?= $i ?>][<?= $unit['ID'] ?>][]"
                      value="<?= $unit['Name'] ?>"
                      class="form-control" >
                  </div>
                </div>
                <div class="col-md-5">
                  <div class="form-group form-group-default required">
                    <label>Chapter  Code</label>
                    <input type="number"
                      name="unit_code[uedit][unit_<?= $i ?>][<?= $unit['ID'] ?>][]"
                      value="<?= $unit['Code'] ?>"
                      class="form-control" >
                  </div>
                </div>
                <div class="col-md-2">
                  <a class="remove" type="button" data-id="<?= $unit['ID'] ?>"><i class="ti ti-copy-minus add_btn_form icon-xs cursor-pointer"></i></a>
                </div>
              </div>

              <div class="module-section" id="module-section-<?= $i ?>">
                <div class="row">
                  <div class="col-md-2">
                    <div class="btn add_btn_form  rounded add-more-module mb-3"
                      data-unit_id="<?= $unit['ID'] ?>" data-unit="<?= $i ?>">
                      <i class="ti ti-circle-plus"></i> Add Unit
                    </div>
                  </div>
                </div>
              </div>

              <div class="after-add-more-module-edit" id="after-add-more-module-<?= $i ?>">
                <?php
                if ($modulesql->num_rows > 0) {
                  $m = 1;
                  while ($module = $modulesql->fetch_assoc()) {
                ?>
                    <div class="module-section module-control card card-body shadow-sm mb-3">
                      <h4 class="mt-0">Unit  <?= $m ?></h4>
                      <div class="row">
                        <div class="col-md-10">
                          <div class="form-group form-group-default required">
                            <label>Unit  Name</label>
                            <input type="text"
                              name="module_name[medit][unit_<?= $i ?>][module_<?= $m ?>][<?= $module['ID'] ?>][]"
                              value="<?= $module['Name'] ?>" class="form-control" >
                          </div>
                        </div>
                        <div class="col-md-2">
                          <a class="remove-module" type="button" data-id="<?= $module['ID'] ?>"><i class="ti ti-copy-minus add_btn_form icon-xs cursor-pointer"></i></a>
                        </div>
                      </div>
                    </div>
                <?php
                    $m++;
                  }
                }
                ?>
              </div>
            </div>
          </div>
      <?php $i++;
        }
      } ?>
      <div class="after-add-more">
      </div>
      <!-- end kp -->
    </div>
    <div class="modal-footer clearfix text-end">
      <div class="col-md-12 m-t-10 sm-m-t-10">
        <!--<button aria-label="" type="submit" class="btn btn-primary btn-cons btn-animated from-left">-->
        <!--  <span>Save</span>-->
        <!--  <span class="hidden-block">-->
        <!--    <i class="pg-icon">tick</i>-->
        <!--  </span>-->
        <!--</button>-->
        <button aria-label="" type="button" data-dismiss="modal" class=" btn btn-secondary mr-2">
          <i class="ti ti-circle-dashed-x mr-2"></i> Close</button>
        <button aria-label="" type="submit" class="btn btn-primary ">
          <i class="ti ti-circle-check mr-2"></i> Save</button>
      </div>
    </div>
  </form>


  <script type="text/javascript" src="../../assets/plugins/select2/js/select2.full.min.js"></script>

  <script>
    $(document).ready(function() {
      // Count existing Units and Modules for continuation
      let unitCount = $(".control1").length + 1;
      let moduleCount = {};

      $(".control1").each(function(i) {
        const unitId = i + 1;
        const $unit = $(this);
        const moduleEls = $unit.find(".module-control");
        moduleCount[unitId] = moduleEls.length || 0;
      });

      // Add new Unit
      $(".add-more").click(function() {
        const unit = unitCount++;
        moduleCount[unit] = 0;

        const html = `
            <div class="after-add-more-edit">
                <div class="row control1 card card-body shadow-none mb-3">
                    <h4 class="mt-0">Chapter  ${unit}</h4>
                    <div class="control-group input-group" style="margin-top:10px">
                        <div class="col-md-5">
                            <div class="form-group form-group-default required">
                                <label>Chapter  Name</label>
                                <input type="text" name="unit_name[uadd][unit_${unit}][]" class="form-control" >
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group form-group-default required">
                                <label>Chapter Code</label>
                                <input type="number" name="unit_code[uadd][unit_${unit}][]" class="form-control" >
                            </div>
                        </div>
                        <div class="col-md-2">
                            <a class="remove" type="button">
                                <i class="ti ti-copy-minus add_btn_form icon-xs cursor-pointer"></i>
                            </a>
                        </div>
                    </div>

                    <div class="module-section" id="module-section-${unit}">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="btn add_btn_form rounded add-more-module mb-3"
                                    data-unit="${unit}">
                                    <i class="ti ti-circle-plus"></i> Add Unit
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="after-add-more-module-edit" id="after-add-more-module-${unit}">
                    </div>
                </div>
            </div>`;
        $(".after-add-more").append(html);
      });

      // Remove Unit
      $("body").on("click", ".remove", function() {
        $(this).closest(".control1").remove();
      });

      // Add new Module
      $("body").on("click", ".add-more-module", function() {
        const unit = $(this).data("unit");
        const module = ++moduleCount[unit];

        const html = `
            <div class="module-section module-control card card-body shadow-sm mb-3">
                <h4 class="mt-0">Unit ${module}</h4>
                <div class="row">
                    <div class="col-md-10">
                        <div class="form-group form-group-default required">
                            <label>Unit Name</label>
                            <input type="text" name="module_name[madd][unit_${unit}][]" class="form-control" >
                        </div>
                    </div>
                    <div class="col-md-2">
                        <a class="remove-module" type="button">
                            <i class="ti ti-copy-minus add_btn_form icon-xs cursor-pointer"></i>
                        </a>
                    </div>
                </div>
            </div>`;
        $(`#after-add-more-module-${unit}`).append(html);
      });

      // Remove Module
      $("body").on("click", ".remove-module", function() {
        $(this).closest(".module-control").remove();
      });
    });
  </script>
  <script>
    $(function() {
      $("#course_id, #university_id, #duration_id, #subject_id").select2({
        searchable: true,
        dropdownParent: $('#lg-modal-content')
      });

      $("#eligibilities").select2();
      $("#course_category").select2();
      // $(".skillTab,.subjectTab").hide();
      getDetails('<?= $uni_id ?>');
      getDuration('<?= $sub_course_id ?>');

    })

    function getDetails(id) {
      $(".skillTab").show();
      $("#duration_id").empty();
      $("#subject_id").empty();

      $.ajax({
        url: '/app/syllabus/courses?id=' + id,
        type: 'GET',
        success: function(data) {

          $('#course_id').html(data);
          $('#course_id').val(<?= $sub_course_id ?>);
          getSubject('<?= $duration ?>');
        }
      });
    }

    function getDuration(id) {
      var university_id = $("#university_id").val();
      $.ajax({
        url: '/app/syllabus/semester?id=' + id + '&university_id=' + university_id,
        type: 'GET',
        success: function(data) {
          $('.subjectTab').show();

          $('#duration_id').html(data);
          $('#duration_id').val('<?= $duration ?>');


        }
      });
    }

    function getSubject(duration) {
      var sub_course_id = $("#course_id").val();
      var university_id = $("#university_id").val();
      $.ajax({
        url: '/app/syllabus/subjects?id=' + sub_course_id + '&university_id=' + university_id + '&duration=' + duration,
        type: 'GET',
        success: function(data) {
          $('#subject_id').html(data);
          $('#subject_id').val('<?= $subject_id ?>');
        }
      });
    }

    $(function() {
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
        highlight: function(element) {
          $(element).addClass('error');
          $(element).closest('.form-control').addClass('has-error');
        },
        unhighlight: function(element) {
          $(element).removeClass('error');
          $(element).closest('.form-control').removeClass('has-error');
        }
      });
    })

    $("#form-add-sub-course").on("submit", function(e) {
      if ($('#form-add-sub-course').valid()) {
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

    function removeDiv(id, type) {
      $.ajax({
        url: "/app/syllabus/destroy",
        type: "GET",
        data: {
          id: id,
          type: type
        },
        dataType: 'json',
        success: function(data) {
          console.log(data.status);
          if (data.status == 200) {
            notification('success', data.message);
          } else {
            notification('danger', data.message);
          }
        }
      })
    }
  </script>
<?php } ?>