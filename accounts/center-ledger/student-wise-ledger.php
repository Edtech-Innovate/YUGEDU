<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-top.php'); ?>
<style>
  table thead{
    background: #c5cfca ;
  }
  table thead tr th {
    color: #4b4b4b !important;
    font-weight: bold !important;
  }
</style>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-bottom.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/menu.php'); 
  unset($_SESSION['adm_session']);
  unset($_SESSION['filterByUser']);
?>
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
              <li class="breadcrumb-item active">Center Ledger</li>
            </ol>
            <!-- END BREADCRUMB -->
          </div>
        </div>
      </div>
      <!-- END JUMBOTRON -->
      <!-- START CONTAINER FLUID -->
      <div class=" container-fluid">
        <!-- BEGIN PlACE PAGE CONTENT HERE -->
        <div class="card card-body">
        <?php if ($_SESSION['Role'] == 'Center' || $_SESSION['Role'] == 'Sub-Center') { ?>
          <input type="hidden" id="center" value="<?= $_SESSION['ID'] ?>">
          <div class="row d-flex justify-content-center">
            <div class="col-md-4">
              <div class="">
                <div class="">
                  <div class="form-group form-group-default required">
                    <label>Adm Session</label>
                    <select class="full-width" style="border: transparent;" data-init-plugin="select2" id="admission_session_id" onchange="getLedgerFilter('adm_session', this.value)">
                      <option value="">Select</option>
                      <?php $sessions = $conn->query("SELECT ID, Name FROM Admission_Sessions WHERE University_ID = " . $_SESSION['university_id']);
                      while ($session = $sessions->fetch_assoc()) {
                        echo '<option value="' . $session['ID'] . '">' . $session['Name'] . '</option>';
                      }
                      ?>
                    </select>
                  </div>
                </div>
              </div>
            </div>
            <!-- kp -->
            <?php if ($_SESSION['Role']!= 'Sub-Center') { ?>

            <div class="col-md-4">
                <div class="">
                  <div class="">
                    <div class="form-group form-group-default required">
                      <label>User Type</label>
                      <select class="full-width" style="border: transparent;" data-init-plugin="select2" id="center_user_type" onchange="getLedgerFilter(this.value,'users');">
                        <option value="">Select</option>
                        <option value="<?= $_SESSION['ID']?>">Self</option>
                        <?php $getSubCenter = $conn->query("SELECT Users.ID, Name, Code FROM `Center_SubCenter` LEFT JOIN Users ON Users.ID = Sub_Center WHERE Center =".$_SESSION['ID']." AND Role = 'Sub-Center'");
                        while ($subCenter = $getSubCenter->fetch_assoc()) {
                          echo '<option value="'. $subCenter['ID']. '">'. $subCenter['Name'].' ('. $subCenter['Code'].')'. '</option>';
                        }
                        ?>
                       
                      </select>
                    </div>
                  </div>
                </div>
              </div>

            <?php } ?>
            <!-- end kp -->
          </div>
        <?php } else { ?>
          <div class="row d-flex justify-content-center">
            <div class="col-md-4">
              <div class="">
                <div class="">
                  <div class="form-group form-group-default required">
                    <label>Centers</label>
                    <select class="full-width" style="border: transparent;" data-init-plugin="select2" id="center" onchange="getLedgerFilter(this.value, 'users')">
                      <option value="">Select</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="">
                <div class="">
                  <div class="form-group form-group-default required">
                    <label>Sub-Center</label>
                    <select class="full-width" style="border: transparent;" data-init-plugin="select2" id="sub_center" name="sub_center" onchange="getLedgerFilter(this.value, 'users')" >
                      <option value="">Select</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="">
                <div class="">
                  <div class="form-group form-group-default required">
                    <label>Adm Session</label>
                    <select class="full-width" style="border: transparent;" data-init-plugin="select2" id="admission_session_id" onchange="getLedgerFilter('adm_session', this.value)">
                      <option value="">Select</option>
                      <?php $sessions = $conn->query("SELECT ID, Name FROM Admission_Sessions WHERE University_ID = " . $_SESSION['university_id']);
                      while ($session = $sessions->fetch_assoc()) {
                        echo '<option value="' . $session['ID'] . '">' . $session['Name'] . '</option>';
                      }
                      ?>
                    </select>
                  </div>
                </div>
              </div>
            </div>
      

          </div>
        <?php } ?>
        <div class="row m-t-20">
          <div class="col-lg-12">
            <div class="card card-transparent">
              <!-- Nav tabs -->
              <ul class="nav nav-tabs nav-tabs-linetriangle" id="all-counter" data-init-reponsive-tabs="dropdownfx">
                <?php
                if ($_SESSION['Role'] == 'Center') {
                  // $active_class='';
                  $active_class = "active";
                } else {
                  $active_class = '';
                } ?>
                <li class="nav-item" id="counter_student">
                  <?php
                  $counter = array();
                  if ($_SESSION['ID']) {
                    $center_id = 'AND Students.Added_For = ' . $_SESSION['ID'] . '';
                  } else {
                    $center_id = '';
                  }

                  $students_count = $conn->query("SELECT ID FROM Students WHERE Students.University_ID = " . $_SESSION['university_id'] . " AND Step = 4 AND Process_By_Center IS NULL $center_id");

                  if ($students_count->num_rows == 0) {
                    $students_count = $conn->query("SELECT ID FROM Students WHERE Students.University_ID = " . $_SESSION['university_id'] . " AND Step = 4 AND Process_By_Center IS NULL AND Added_By = " . $_SESSION['ID'] . " $center_id");
                  }

                  while ($student = $students_count->fetch_assoc()) {
                    $invoices_created = $conn->query("SELECT ID FROM Invoices WHERE Student_ID = " . $student['ID'] . " AND User_ID = " . $_SESSION['ID'] . " AND University_ID = " . $_SESSION['university_id'] . "");
                    if ($invoices_created->num_rows != 0) {
                      $counter = [];
                    } else {
                      $counter[] = $student;
                    }
                  }
                  ?>
                  <a class="<?= $active_class  ?>" data-toggle="tab" data-target="#students" href="#"><span>Students</span>-<span id="applied_student_count">
                      <?= count($counter) ?>
                    </span></a>
                </li>
                <li class="nav-item" id="counter_pending">
                  <?php
                  $pending_counter = array();
                  $id = $_SESSION['ID'];
                  $added_for[] = $id;

                  $downlines = $conn->query("SELECT `User_ID` FROM University_User WHERE Reporting = $id");
                  while ($downline = $downlines->fetch_assoc()) {
                    $added_for[] = $downline['User_ID'];
                  }

                  $users = implode(",", array_filter($added_for));

                  $already = array();
                  $already_ids = array();
                  $invoices = $conn->query("SELECT Student_ID, Duration FROM Invoices LEFT JOIN Payments ON Invoices.Invoice_No = Payments.Transaction_ID AND Payments.Type = 1 WHERE `User_ID` = $id AND Invoices.University_ID = " . $_SESSION['university_id'] . " AND Payments.Status != 2");
                  while ($invoice = $invoices->fetch_assoc()) {
                    $already[$invoice['Student_ID']] = $invoice['Duration'];
                    $already_ids[] = $invoice['Student_ID'];
                  }

                  $query = empty($already_ids) ? " AND ID IS NULL" : " AND ID IN (" . implode(',', $already_ids) . ")";

                  $sessionQuery = "";
                  if (isset($_GET['admission_session_id']) && !empty($_GET['admission_session_id'])) {
                    $admission_session_id = intval($_GET['admission_session_id']);
                    $sessionQuery = " AND Students.Admission_Session_ID = " . $admission_session_id;
                  }

                  $students_count = $conn->query("SELECT ID FROM Students WHERE University_ID = " . $_SESSION['university_id'] . " AND Added_For IN ($users) AND Step = 4 $sessionQuery AND Process_By_Center IS NULL $query");
                  if ($students_count->num_rows == 0) {
                    $students_count = $conn->query("SELECT ID FROM Students WHERE University_ID = " . $_SESSION['university_id'] . " AND Added_By = $id AND Step = 4 $sessionQuery AND Process_By_Center IS NULL $query");
                  }
                  while ($student = $students_count->fetch_assoc()) {
                    $pending_counter[] = $student;
                  }
                  ?>
                  <a data-toggle="tab" data-target="#pending" href="#"><span>Pending</span>-<span id="pending_student_count">
                      <?= count($pending_counter) ?>
                    </span></a>
                </li>
                <li class="nav-item" id="counter_processed">
                  <?php
                  $processed_countrer = array();
                  if ($_SESSION['ID']) {
                    $center_id = 'AND Students.Added_For = ' . $_SESSION['ID'] . '';
                  } else {
                    $center_id = '';
                  }
                  $students_count = $conn->query("SELECT ID FROM Students WHERE Students.University_ID = " . $_SESSION['university_id'] . " AND Step = 4 AND Process_By_Center IS NOT NULL AND Payment_Received IS NULL $center_id");
                  if ($students_count->num_rows == 0) {
                    $students_count = $conn->query("SELECT ID FROM Students WHERE Students.University_ID = " . $_SESSION['university_id'] . " AND Step = 4 AND Process_By_Center IS NOT NULL AND Payment_Received IS NULL AND Added_By = " . $_SESSION['ID'] . " ");
                  }
                  while ($student = $students_count->fetch_assoc()) {
                    $processed_countrer[] = $student;
                  }
                  ?>
                  <a data-toggle="tab" data-target="#processed" href="#"><span>Processed</span>-<span id="processed_student_count">
                      0
                    </span></a>
                </li>
              </ul>
              <!-- Tab panes -->
              <div class="tab-content">
                <div class="tab-pane active" id="students">
                  <div class="row">
                    <div class="col-md-12 text-center">
                      Please select center!
                    </div>
                  </div>
                </div>
                <div class="tab-pane" id="pending">
                  <div class="row">
                    <div class="col-md-12 text-center">
                      Please select center!
                    </div>
                  </div>
                </div>
                <div class="tab-pane" id="processed">
                  <div class="row">
                    <div class="col-md-12 text-center">
                      Please select center!
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- END PLACE PAGE CONTENT HERE -->
        </div>
      </div>
      <!-- END CONTAINER FLUID -->
    </div>
    <!-- END PAGE CONTENT -->
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-top.php'); ?>

    <script>
      $(document).ready(function(){

        <?php if ($_SESSION['Role'] == 'Center' || $_SESSION['Role'] == 'Sub-Center') { ?>
        getLedgerFilter( '<?= $_SESSION['ID'] ?>','users');
       <?php } ?>
      })
      function getLedgerFilter(id, by){
        $.ajax({
          url: '/app/centers/ledgers/student-wise/filter',
          type: 'POST',
          data: {
            id: id, by: by
          },
          dataType: 'json',
          success: function (data) {
            var id = $("#center").val();
            var admission_session_id = $("#admission_session_id").val();
            $("#sub_center").html(data.subCenterName);
            getStudentList(id);
            getPendingList(id);
            getProcessedList(id);
            getCounter(id, 'all-count',admission_session_id);
          }
        })
      }

      function getStudentList(id) {
        $.ajax({
          url: '/app/centers/ledgers/student-wise/students?id=' + id ,
          type: 'GET',
          success: function(data) {

            $("#students").html(data);

          }
        })
      }

      function getCounter(id, status,admission_session_id) {
        $.ajax({
          url: '/app/centers/ledgers/student-wise/students-counter?id=' + id + '&count_status=' + status+'&admission_session_id='+admission_session_id,
          type: 'GET',
          success: function(data) {
            $("#all-counter").html(data);
          }
        })
      }

      function getPendingList(id) {
        $.ajax({
          url: '/app/centers/ledgers/student-wise/pending?id=' + id ,
          type: 'GET',
          success: function(data) {
            $("#pending").html(data);
          }
        })
      }

      function getProcessedList(id) {
        $.ajax({
          url: '/app/centers/ledgers/student-wise/processed?id=' + id ,
          type: 'GET',
          success: function(data) {

            $("#processed").html(data);
          }
        })
      }

      getCenterList('center');
    </script>
    <script>
      // function getCenterUserType(role) {
      //   var user_type = "<?= $_SESSION['Role'] ?>";
        
      //   <?php if ($_SESSION['Role'] == 'Sub-Center' || $_SESSION['Role']=="Center") { ?>
      //     var id = <?= $_SESSION['ID'] ?>;
      //   <?php } ?>

      //   var id = $("#center").val();
      //   var admission_session_id = $("#admission_session_id").val();
      //   getLedgerFilter(id, 'users')

      //   // alert(id);
      //   // getStudentListType(id, role,admission_session_id=null);
      //   // getPendingListType(id, role,admission_session_id=null);
      //   // getProcessedListType(id, role,admission_session_id=null);
      //   // getCounterType(id, 'all-count', role,admission_session_id=null);
      // }

    
      function getStudentListType(id, role,admission_session_id=null) {
        $.ajax({
          url: '/app/centers/ledgers/student-wise/students?id=' + id + '&role=' + role+"&admission_session_id="+admission_session_id,
          type: 'GET',
          success: function(data) {

            $("#students").html(data);

          }
        })
      }
      function getPendingListType(id, role,admission_session_id=null) {
        $.ajax({
          url: '/app/centers/ledgers/student-wise/pending?id=' + id + '&role=' + role+"&admission_session_id="+admission_session_id,
          type: 'GET',
          success: function(data) {

            $("#pending").html(data);

          }
        })
      }
      function getProcessedListType(id, role,admission_session_id=null) {
        $.ajax({
          url: '/app/centers/ledgers/student-wise/processed?id=' + id + '&role=' + role+"&admission_session_id="+admission_session_id,
          type: 'GET',
          success: function(data) {
            $("#processed").html(data);

          }
        })
      }

      function getCounterType(id, status, role,admission_session_id=null) {
        $.ajax({
          url: '/app/centers/ledgers/student-wise/students-counter?id=' + id + '&count_status=' + status + '&role=' + role+'&admission_session_id='+admission_session_id,
          type: 'GET',
          success: function(data) {
            $("#all-counter").html(data);
          }
        })
      }
    </script>

    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>