<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-top.php'); ?>
<style>
.custom_card{
    background: #c5cfca!important;
    border-radius: 10px!important;
    box-shadow: 0 10px 20px rgb(197 207 202 / 43%), 0 6px 6px rgb(197 207 202 / 36%)!important;
}
.hint-text {
    color:black !important;
}
  table thead{
    background: #c5cfca ;
  }
  table thead tr th {
    color: #4b4b4b !important;
    font-weight: bold !important;
  }
   #users-search-table{
    border-radius:10px !important;
}
#applicableTable_filter label input, #appliedTable_filter label input {
    border-radius: 10px !important;
} 
</style>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-bottom.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/menu.php'); ?>
<!-- START PAGE-CONTAINER -->
<div class="page-container ">
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/topbar.php'); ?>
  <!-- START PAGE CONTENT WRAPPER -->
  <div class="page-content-wrapper ">
    <!-- START PAGE CONTENT -->
    <div class="content ">
      <!-- START JUMBOTRON -->
      <div class="jumbotron" data-pages="parallax">
        <div class=" container-fluid sm-p-l-0 sm-p-r-0">
          <div class="inner">
            <!-- START BREADCRUMB -->
            <ol class="breadcrumb d-flex flex-wrap justify-content-between align-self-start">
              <?php $breadcrumbs = array_filter(explode("/", $_SERVER['REQUEST_URI']));
              for ($i = 1; $i <= count($breadcrumbs); $i++) {
                if (count($breadcrumbs) == $i) : $active = "active";
                  $crumb = explode("?", $breadcrumbs[$i]);
                  echo '<li class="breadcrumb-item ' . $active . '">' . $crumb[0] . '</li>';
                endif;
              }
              ?>
              <div>
                <?php if (isset($_SESSION['active_rr_session_id'])) { ?>
                  <button class="btn  btn-sm border-0" onclick="resetRRSession()" aria-label="" title="" data-toggle="tooltip" data-original-title="Reset Session"><i class="ti ti-arrows-exchange add_btn_form" style="font-size:24px;"></i></button>
                  <a href="/app/re-registrations/export" target="_blank" class="btn border-0  btn-sm" aria-label="" title="" data-toggle="tooltip" data-original-title="Download Re-registration"><i class="ti ti-download add_btn_form" style="font-size:24px"></i></a>
                <?php } ?>
              </div>
            </ol>
            <!-- END BREADCRUMB -->
          </div>
        </div>
      </div>
      <!-- END JUMBOTRON -->
      <!-- START CONTAINER FLUID -->
      <div class=" container-fluid">
        <!-- BEGIN PlACE PAGE CONTENT HERE -->
        <?php if (!isset($_SESSION['active_rr_session_id'])) { ?>
          <div class="row">
            <?php
            $examSessions = $conn->query("SELECT ID, Name FROM Exam_Sessions WHERE University_ID = " . $_SESSION['university_id'] . " AND (RR_Status = 1 OR RR_Last_Date IS NOT NULL)");
            while ($examSession = $examSessions->fetch_assoc()) { ?>
              <div class="col-md-3">
                <div class="card cursor-pointer custom_card" onclick="setRRSession(<?= $examSession['ID'] ?>, '<?= $examSession['Name'] ?>')">
                  <div class="card-body">
                    <p class="hint-text overline">EXAM SESSION</p>
                    <h3><?= $examSession['Name'] ?></h3>
                  </div>
                </div>
              </div>
            <?php }
            ?>
          </div>
        <?php } else { ?>
        <div class="card card-transparent">
          <div class="row clearfix">
            <div class="col-md-12">
              <h5 class="ml-2"><?= $_SESSION['active_rr_session_name'] ?></h5>
            </div>
          </div>
          <div class="row clearfix">
            <div class="col-md-12">
              
                <!-- Nav tabs -->
                <ul class="nav nav-tabs nav-tabs-linetriangle" data-init-reponsive-tabs="dropdownfx">
                  <li class="nav-item">
                    <a class="active" data-toggle="tab" data-target="#applicableList" href="#"><span>Applicable Students - <span id="applicableCount">0</span></span></a>
                  </li>
                  <li class="nav-item">
                    <a data-toggle="tab" data-target="#applied" href="#"><span>Applied - <span id="appliedCount">0</span></span></a>
                  </li>
                </ul>
                <!-- Tab panes -->
                <div class="tab-content">
                  <div class="tab-pane active p-3" id="applicableList">
                    <div class="row m-t-20">
                      <div class="col-lg-12">
                        <div class="table-bordered px-2 py-2">
                          <table class="table table-striped nowrap table-responsive" id="applicableTable">
                            <thead>
                              <tr>
                                <th>Student ID</th>
                                <th>Student Name</th>
                                <th>RR Sem</th>
                                <th>Enrollemnt No</th>
                                <th>Adm Session</th>
                                <th>Course</th>
                                <th>Owner</th>
                                <th data-orderable="false"></th>
                              </tr>
                            </thead>
                          </table>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="tab-pane" id="applied">
                    <div class="row">
                      <div class="col-lg-12">
                        <div class="table-bordered px-2 py-2">
                          <table class="table table-striped nowrap table-responsive" id="appliedTable">
                            <thead>
                              <tr>
                                <th>Student ID</th>
                                <th>Student Name</th>
                                <th>RR Sem</th>
                                <th>Enrollemnt No</th>
                                <th>Adm Session</th>
                                <th>Course</th>
                                <th>Owner</th>
                                <th data-orderable="false"></th>
                              </tr>
                            </thead>
                          </table>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php } ?>
        <!-- END PLACE PAGE CONTENT HERE -->
      </div>
      <!-- END CONTAINER FLUID -->
    </div>
    <!-- END PAGE CONTENT -->
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-top.php'); ?>
    <script>
      function setRRSession(id, name) {
        $.ajax({
          url: '/app/re-registrations/set-session',
          type: 'POST',
          data: {
            id,
            name
          },
          dataType: 'json',
          success: function(data) {
            if (data.status) {
              window.location.reload();
            } else {
              notification('danger', data.message);
            }
          }
        })
      }

      function resetRRSession() {
        $.ajax({
          url: '/app/re-registrations/reset-session',
          type: 'POST',
          dataType: 'json',
          success: function(data) {
            if (data.status) {
              window.location.reload();
            } else {
              notification('danger', data.message);
            }
          }
        })
      }
    </script>

    <script>
      var applicableTable = $('#applicableTable');
      var appliedTable = $('#appliedTable');
      var actionVisibility = <?= $_SESSION['show_action_in_active_rr'] == 0 ? 'false' : 'true' ?>;
      var applicableSettings = {
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'ajax': {
          'url': '/app/re-registrations/applicable-server',
          'type': 'POST',
          complete: function(xhr, responseText) {
            $('#applicableCount').html(xhr.responseJSON.iTotalDisplayRecords);
          }
        },
        'columns': [{
            data: "Unique_ID",width:"10%",
          },
          {
            data: "First_Name",width:"60%",
          },
          {
            data: "Duration",width:"10%",
          },
          {
            data: "Enrollment_No",width:"10%",
          },
          {
            data: "Admission_Session_ID",width:"10%",
          },
          {
            data: "Course_ID",width:"10%",
          },
          {
            data: "Added_For",width:"10%",
          },
          {
            data: "ID",width:"10%",
            "render": function(data, type, row) {
              return '<span class="cursor-pointer bold" onclick="applyRR(&#39;' + data + '&#39;)">Apply</span>';
            },
            visible: actionVisibility
          }
        ],
       "sDom": "<'row mt-3 w-100 p-0 m-0'<'col-sm-6 pr-0 pl-0 custon_text_start'l><'col-sm-6 pr-0 pl-0'f>><t><'row'<p i>>",
            "destroy": true,
            "scrollCollapse": true,
            "oLanguage": {
              "sInfo": "Showing <b>_START_ to _END_</b> of _TOTAL_ entries"
            },
        drawCallback: function(settings, json) {
          $('[data-toggle="tooltip"]').tooltip();
        },
        "aaSorting": []
      };

      var appliedSettings = {
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'ajax': {
          'url': '/app/re-registrations/applied-server',
          'type': 'POST',
          complete: function(xhr, responseText) {
            $('#appliedCount').html(xhr.responseJSON.iTotalDisplayRecords);
          }
        },
        'columns': [{
            data: "Unique_ID",width:"10%"
          },
          {
            data: "First_Name",width:"60%"
          },
          {
            data: "Duration",width:"10%"
          },
          {
            data: "Enrollment_No",width:"10%"
          },
          {
            data: "Admission_Session_ID",width:"10%"
          },
          {
            data: "Course_ID",width:"10%"
          },
          {
            data: "Added_For",width:"10%"
          },
          {
            data: "ID",width:"10%",
            "render": function(data, type, row) {
              return '<span class="cursor-pointer bold" onclick="cancelRR(&#39;' + data + '&#39;)">Cancel</span>';
            },
            visible: actionVisibility
          }
        ],
        "sDom": "<'row mt-3 w-100 p-0 m-0'<'col-sm-6 pr-0 pl-0 custon_text_start'l><'col-sm-6 pr-0 pl-0'f>><t><'row'<p i>>",
            "destroy": true,
            "scrollCollapse": true,
            "oLanguage": {
              "sInfo": "Showing <b>_START_ to _END_</b> of _TOTAL_ entries"
            },
        drawCallback: function(settings, json) {
          $('[data-toggle="tooltip"]').tooltip();
        },
        "aaSorting": []
      };

      applicableTable.dataTable(applicableSettings);
      appliedTable.dataTable(appliedSettings);
    </script>

    <?php if ($_SESSION['show_action_in_active_rr'] == 1) { ?>
      <script>
        function applyRR(id) {
          Swal.fire({
            title: 'Are you sure?',
            text: "You will be able to cancel before the last date!",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Apply',
            cancelButtonText: 'Close'
          }).then((result) => {
            if (result.isConfirmed) {
              $.ajax({
                url: '/app/re-registrations/apply',
                type: 'POST',
                data: {
                  id
                },
                dataType: 'json',
                success: function(data) {
                  if (data.status) {
                    notification('success', data.message);
                    $('.table').DataTable().ajax.reload(null, false);
                  } else {
                    notification('danger', data.message);
                  }
                }
              })
            } else {
              $('.table').DataTable().ajax.reload(null, false);
            }
          })
        }

        function cancelRR(id) {
          Swal.fire({
            title: 'Are you sure?',
            text: "You will be able to apply again before the last date!",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Cancel',
            cancelButtonText: 'Close'
          }).then((result) => {
            if (result.isConfirmed) {
              $.ajax({
                url: '/app/re-registrations/cancel',
                type: 'POST',
                data: {
                  id
                },
                dataType: 'json',
                success: function(data) {
                  if (data.status) {
                    notification('success', data.message);
                    $('.table').DataTable().ajax.reload(null, false);
                  } else {
                    notification('danger', data.message);
                  }
                }
              })
            } else {
              $('.table').DataTable().ajax.reload(null, false);
            }
          })
        }
      </script>
    <?php } ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>