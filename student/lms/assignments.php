<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-top.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-bottom.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/menu.php'); ?>
<div class="page-container ">
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/topbar.php'); ?>

  <div class="page-content-wrapper ">
    <div class="content ">
           <div class="jumbotron" data-pages="parallax">
                <div class=" container-fluid sm-p-l-0 sm-p-r-0">
                    <div class="inner">
                        <!-- START BREADCRUMB -->
                        <ol class="breadcrumb d-flex flex-wrap justify-content-between align-self-start">
                            <?php $breadcrumbs = array_filter(explode("/", $_SERVER['REQUEST_URI']));
                            for ($i = 1; $i <= count($breadcrumbs); $i++) {
                                if (count($breadcrumbs) == $i) : $active = "active";
                                    $crumb = explode("?", $breadcrumbs[$i]);
                                    echo '<li class="breadcrumb-item font-weight-bold ' . $active . '">' . ucwords($crumb[0]) . '</li>';
                                endif;
                            }
                            ?>
                            <div>

                            </div>
                        </ol>
                        <!-- END BREADCRUMB -->
                    </div>
                </div>
            </div>
      <div class=" container-fluid">
        <div class="card card-transparent">
          <div class="card-header">
            <?php
            ?>
            <div class="pull-right">
              <div class="row">
                <div class="col-xs-7" style="margin-right: 10px;">
                  <input type="text" id="e-book-search-table" class="form-control pull-right p-2 fw-bold custom_search_section"
                    placeholder="Search">
                </div>

              </div>
            </div>
            <div class="clearfix"></div>
          </div>
          <div class="card-body">
            <div class="">
              <table class="table table-hover nowrap table-striped  table-responsive" id="student_table">
                <thead>
                  <tr>
                    <th>Subject Name</th>
                    <th>Subject Code</th>
                    <th>Assignment Name</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Total Marks</th>
                    <th>Obtained Marks</th>
                    <th>Reason</th>
                    <th>Teacher Status</th>
                    <th>Student Status</th>
                    <th>Teacher Assignment</th>
                    <!-- <th>Student Assignment</th> -->
                    <th>Action</th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- END PAGE CONTENT -->
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-top.php'); ?>
  <script type="text/javascript">
    $(function () {
      var role = '<?= $_SESSION['Role'] ?>';
      var show = role == 'Administrator' ? true : false;
      var table = $('#student_table');
      var settings = {
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'ajax': {
          'url': '/app/assignments/student'
        },
        'columns': [{
          data: "Name"
        },
        {
          data: "Code"
        },
        {
          data: "assignment_name"
        },
        {
          data: "start_date"
        },
        {
          data: "end_date"
        },
        {
          data: "marks"
        },
        {
          data: "obtained_mark"
        },
        {
          data: "remark"
        },
        {
          data: "status"
        },
        {
          data: "assignment_submission_status",
          render: function (data, type, row) {
            // var data = (data == "NOT SUBMITTED") ? "Not Submitted" : data;
            // alert(data);
            var color = (data == "SUBMITTED") ? "success" : "danger";
            return '<span class="badge p-2 badge-'+ color +'" style="font-weight: 500;">' + data.charAt(0).toUpperCase() + data.slice(1).toLowerCase() + '</span>';

          }
        },
        {
          data: "file_path",
          render: function (data, type, row) {
            var path = '../../uploads/assignments/';
            var file;
            if (row.uploadingIsActive === 0) {
              return "Not Available";
            }
            if (row.File_Type && row.File_Type.toLowerCase() === 'pdf' && row.uploadingIsActive == 1) {
              file = '<a href="' + path + data + '" class="btns btn-dangers btn-sm" download aria-label="Download Assignment" data-toggle="tooltip" data-placement="top" title="Download Assignment"  > <i class="uil uil-down-arrow custom_edit_button p-1"></i></a>';
            } else {
              file = '<a href="' + path + data + '"  class="btns btn-dangers btn-sm" download aria-label="Download Assignment" data-toggle="tooltip" data-placement="top" title="Download Assignment" > <i class="uil uil-down-arrow custom_edit_button p-1"></i></a>';
            }
            return file;
          }
        },
        // {
        //   data: "file_name",
        //   render: function(data, type, row) {
        //     var path = '../../uploads/assignments/';
        //     var file;
        //     if (row.File_Type && row.File_Type.toLowerCase() === 'pdf') {
        //       file = '<a href="' + path + data + '" class="btn btn-warning btn-sm" download>Assignment</a>';
        //     } else {
        //       file = '<a href="' + path + data + '" class="btn btn-warning btn-sm" download>Assignment</a>';
        //     }
        //     return file;
        //   }
        // },
        // {
        //   data: "file_name",
        //   render: function(data, type, row) {
        //     var uploadDir = '../../uploads/assignments/';
        //     var filePath = uploadDir + data;
        //     var button = '';
        //     if (row.status !== 'Rejected') {
        //       if (data && row.file_exists) {
        //         button += '<a href="' + filePath + '" class="btn btn-danger btn-sm" download>Download Assignment</a>';
        //       }
        //       button += '<button class="btn btn-primary btn-sm" data-toggle="modal" onclick=\'openUploadModal("' + row.id + '", "' + row.subject_id + '")\'>Upload Assignment</button>';
        //     } else {
        //       if (data && row.file_exists) {
        //         button += '<a href="' + filePath + '" class="btn btn-danger btn-sm" download>Download Updated Assignment</a>';
        //       }
        //       button += '<button class="btn btn-warning btn-sm" data-toggle="modal" onclick=\'openUploadModal("' + row.id + '", "' + row.subject_id + '")\'>Reupload Assignment</button>';
        //     }

        //     return button;
        //   }
        // },

        {
          data: "file_name",
          render: function (data, type, row) {
            var button = '';
            var fileLinks = '';
            if (row.uploadingIsActive === 0) {
              return "Date Over";
            } else if (row.assignment_submission_status == "NOT RESUBMITTED") {

              button = '<button class="badge badge-primary border-0 shadow-none p-2" data-toggle="modal" onclick=\'openUploadModal("' + row.id + '", "' + row.subject_id + '")\'>ReUpload Assignment</button>';

            } else if (row.assignment_submission_status == "RESUBMITTED" || row.assignment_submission_status.trim() == "SUBMITTED") {

              var files = data.split(',').map(file => encodeURIComponent(file.trim())).join(',');
              var zipLink = '/app/assignments/stu_zip_files.php?files=' + files + '&Name=' + encodeURIComponent(row.Name);
              fileLinks += '<a href="' + zipLink + '" class="btns btn-dangers btn-sm" download aria-label="Download Submitted Assignment" data-toggle="tooltip" data-placement="top" title="Download Submitted Assignment"  > <i class="uil uil-down-arrow custom_edit_button p-1"></i> </a> ';
              button = '<a class="btns btn-successs btn-sm" data-toggle="modal" onclick=\'openUploadModal("' + row.id + '", "' + row.subject_id + '")\' aria-label="Re-Upload Assignment" data-toggle="tooltip" data-placement="top" title="Re-Upload Assignment"   ><i class="uil uil-upload custom_edit_button p-1"></i> </a>'; //kp


            } else if (row.assignment_submission_status.trim() == "NOT SUBMITTED" && row.uploadingIsActive == 1) {

              button = '<a class="btns btn-primarys btn-sm" data-toggle="modal" onclick=\'openUploadModal("' + row.id + '", "' + row.subject_id + '")\' data-toggle="modal" aria-label="Upload Assignment" data-toggle="tooltip" data-placement="top" title="Upload Assignment"  > <i class="uil uil-upload custom_edit_button p-1"></i></a>';
            }
            return button + fileLinks;
          }
        },

        ],
        "sDom": "<t><'row'<p i>>",
        "destroy": true,
        "scrollCollapse": true,
        "oLanguage": {
          "sLengthMenu": "_MENU_ ",
          "sInfo": "Showing <b>_START_ to _END_</b> of _TOTAL_ entries"
        },
        "aaSorting": [],
        "iDisplayLength": 25,
      };
      table.dataTable(settings);
      // search box for table
      $('#e-book-search-table').keyup(function () {
        table.fnFilter($(this).val());
      });
    })
  </script>
  <script type="text/javascript">
    function openUploadModal(id, subject_id) {
      $.ajax({
        url: '/app/assignments/student-result-review',
        type: 'GET',
        data: {
          id,
          subject_id
        },
        success: function (data) {
          console.log(data);
          $('#md-modal-content').html(data);
          $('#mdmodal').modal('show');
        }
      });
    }
  </script>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>