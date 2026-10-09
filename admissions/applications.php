<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-top.php'); ?>
<style>
/* Base Styles */
.table-responsive {
    min-height: 160px;
    width: 100%;
    margin-bottom: 15px;
    overflow-y: hidden;
    -ms-overflow-style: -ms-autohide-scrollbar;
}

.tooltip-inner {
    white-space: pre-wrap;
    max-width: 100% !important;
    text-align: left !important;
    word-break: break-word;
}

.dropdown-toggle::after {
    content: none !important;
}

.btn-secondary:not(:disabled):not(.disabled).active,
.btn-secondary:not(:disabled):not(.disabled):active,
.show > .btn-secondary.dropdown-toggle {
    color: black;
    background-color: white;
    border-color: white;
    border: none;
}

[type=button]:not(:disabled),
[type=reset]:not(:disabled),
[type=submit]:not(:disabled),
button:not(:disabled) {
    border: none !important;
}

.dropdown-menu > a.dropdown-item:hover,
.dropdown-menu > a.dropdown-item:focus,
.dropdown-menu > a.dropdown-item:active {
    color: #212121;
    text-decoration: none;
    background-color: #c5cfca;
    border-radius: 0px !important;
}

.select2-container {
    width: 100% !important;
}

.select2-container .select2-selection {
    border-radius: 7px !important;
}

#startDateFilter {
    border-right: none !important;
    border-top-left-radius: 7px !important;
    border-bottom-left-radius: 7px !important;
    padding-right: 0px !important;
}

#endDateFilter {
    border-left: none !important;
    border-top-right-radius: 7px !important;
    border-bottom-right-radius: 7px !important;
    padding-right: 0px !important;
}

.input-group-addon {
    border-top: solid 1px #d0d0d0 !important;
    border-bottom: solid 1px #d0d0d0 !important;
    color: #d0d0d0 !important;
    padding-top: 5px;
}

.label-success {
    background-color: #407260;
    color: #fff;
}

.label-important,
.label-danger {
    background-color: #be534b;
    color: #fff;
}

table thead {
    background: white !important;
}

table thead tr th {
    color: black !important;
    font-weight: bold !important;
    white-space: nowrap;
}

#application-table thead,
#not-processed-table thead,
#ready-for-verification-table thead,
#verified-table thead,
#enrolled-table thead {
    background: #c5cfca !important;
}

#application-table thead tr th,
#not-processed-table thead tr th,
#ready-for-verification-table thead tr th,
#verified-table thead tr th,
#enrolled-table thead tr th {
    color: #4b4b4b !important;
    font-weight: bold !important;
}

#application-table_filter label input,
#not-processed-table_filter label input,
#ready-for-verification-table_filter label input,
#verified-table_filter label input,
#enrolled-table_filter label input {
    border-radius: 10px !important;
}

/* ==========================================================================
   Mobile & Tablet Responsiveness
   ========================================================================== */

@media screen and (max-width: 767px) {
    /* Smooth horizontal touch scrolling for data tables */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    /* Prevent text wrapping issues inside table cells on small screens */
    table td, table th {
        white-space: nowrap;
        font-size: 14px;
    }

    /* Make DataTables search inputs stretch to full screen width */
    .dataTables_filter {
        text-align: left !important;
        float: none !important;
    }

    .dataTables_filter label {
        width: 100%;
        display: flex;
        align-items: center;
    }

    #application-table_filter label input,
    #not-processed-table_filter label input,
    #ready-for-verification-table_filter label input,
    #verified-table_filter label input,
    #enrolled-table_filter label input {
        width: 100% !important;
        margin-left: 8px !important;
    }

    /* Responsive adjustments for date filter inputs */
    .input-group {
        flex-wrap: wrap;
    }

    #startDateFilter, 
    #endDateFilter {
        border-radius: 7px !important;
        border: 1px solid #d0d0d0 !important;
        margin-bottom: 8px;
        width: 100%;
    }

    .input-group-addon {
        display: none !important; /* Hide separator on small stacked screens */
    }

    /* Standardize dropdown menu positioning on narrow screens */
    .dropdown-menu {
        width: 100%;
        box-sizing: border-box;
    }
}
</style>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-bottom.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/menu.php');
unset($_SESSION['current_session']);
unset($_SESSION['filterByDepartment']);
unset($_SESSION['filterBySubCourses']);
unset($_SESSION['filterByDate']);
unset($_SESSION['filterByProcessDate']);
unset($_SESSION['filterByStatus']);
unset($_SESSION['filterByUser']);
unset($_SESSION['filterByExamStatus']); //sf
unset($_SESSION['filterByVerticalType']);//sf
unset($_SESSION['filterByDuration']);
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
              <?php $breadcrumbs = array_filter(explode("/", $_SERVER['REQUEST_URI']));
              for ($i = 1; $i <= count($breadcrumbs); $i++) {
                if (count($breadcrumbs) == $i):
                  $active = "active";
                  $crumb = explode("?", $breadcrumbs[$i]);
                  echo '<li class="breadcrumb-item ' . $active . '">' . $crumb[0] . '</li>';
                endif;
              }
              ?>
              <div>
                <?php if ($_SESSION['Role'] == 'Administrator' || $_SESSION['Role'] == 'University Head' || $_SESSION['Role'] == 'Counsellor' || $_SESSION['Role'] == 'Center') { ?>
                  <button class="btn btn-link add_btn_form" aria-label="" title="" data-toggle="tooltip"
                    data-original-title="Upload OA, Enrollment AND Roll No." onclick="uploadOAEnrollRoll()"><i class="ti ti-upload" style="font-size:24px;"></i></button>
                <?php } ?>
                <button class="btn btn-link add_btn_form" aria-label="" title="" data-toggle="tooltip" data-original-title="Download Excel"
                  onclick="exportData()"> <i class="ti ti-download" style="font-size:24px;"></i></button>
                  <button class="btn btn-link add_btn_form" aria-label="" title="" data-toggle="tooltip"
            data-original-title="Download Documents" onclick="exportSelectedDocument()"> <i class="ti ti-download" style="font-size:24px;"></i></button>
                <?php if ($_SESSION['Role'] !== 'University Head') { ?>
                <button class="btn btn-link " aria-label="" title="" data-toggle="tooltip"
                  data-original-title="Add Student" onclick="window.open('/admissions/application-form');"><i class="ti ti-copy-plus add_btn_form" style="font-size:24px"></i></button>
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
        <?php if (isset($_SESSION['university_id'])) { ?>
          <div class="card card-transparent">
            <div class="card-header">
              <div class="d-flex justify-content-start">

                <div class="col-md-2">
                  <div class="form-group">
                    <select class="full-width" style="width:40px" data-init-plugin="select2" id="sessions"
                      onchange="changeSession(this.value)">
                        <option>Select Session</option>
                      <option value="All">All</option>
                      <?php
                      $role_query = "";
                      if ($_SESSION['Role'] == 'Center' || $_SESSION['Role'] == 'Sub-Center') {
                        $role_query = str_replace('{{ table }}', 'Students', $_SESSION['RoleQuery']);
                        $role_query = str_replace('{{ column }}', 'Added_For', $role_query);
                      }
                      $sessions = $conn->query("SELECT Admission_Sessions.ID,Admission_Sessions.Name,Admission_Sessions.Current_Status FROM Admission_Sessions LEFT JOIN Students ON Admission_Sessions.ID = Students.Admission_Session_ID WHERE Admission_Sessions.University_ID = '" . $_SESSION['university_id'] . "' $role_query GROUP BY Name ORDER BY Admission_Sessions.ID ASC");
                      while ($session = mysqli_fetch_assoc($sessions)) { ?>
                        <option value="<?= $session['Name'] ?>" <?php print $session['Current_Status'] == 1 ? 'selected' : '' ?>><?= $session['Name'] ?></option>
                      <?php } ?>
                    </select>
                  </div>
                </div>
                <!-- add filter -->
                <?php if($_SESSION['university_id']=="41"){ ?>
                <div class="col-md-2 ">
                  <div class="form-group">
                    <select class="full-width" style="width:40px" data-init-plugin="select2" id="duration"
                      onchange="addFilter(this.value, 'duration');">
                      <option value="">Choose Duration</option>
                      <option value="3">3 Months</option>
                      <option value="6">6 Months</option>
                      <option value="1">11 Months Adv Certification</option>
                      <option value="2">11 Months Certified</option>
                    </select>
                  </div>
                </div>
                <?php }else{ ?>
                <div class="col-md-2 ">
                  <div class="form-group">
                    <select class="full-width" style="width:40px" data-init-plugin="select2" id="departments"
                      onchange="addFilter(this.value, 'departments');">
                      <option value="">Choose Types</option>
                      <?php $departments = $conn->query("SELECT ID, Name FROM Course_Types WHERE University_ID = " . $_SESSION['university_id']);
                      while ($department = $departments->fetch_assoc()) {
                        echo '<option value="' . $department['ID'] . '">' . $department['Name'] . '</option>';
                      }
                      ?>
                    </select>
                  </div>
                </div>
                <?php } ?>
                <div class="col-md-2 ">
                  <div class="form-group">
                    <select class="full-width" style="width:40px" data-init-plugin="select2" id="sub_courses"
                      onchange="addFilter(this.value, 'sub_courses')" data-placeholder="Choose Sub-Course">
                      <option value="">Choose Sub-Course</option>
                      <?php $programs = $conn->query("SELECT Sub_Courses.ID, CONCAT(Courses.Short_Name, ' (', Sub_Courses.Name, ')') as Name FROM Students LEFT JOIN Sub_Courses ON Students.Sub_Course_ID = Sub_Courses.ID LEFT JOIN Courses ON Sub_Courses.Course_ID = Courses.ID WHERE Students.University_ID = " . $_SESSION['university_id'] . " $role_query GROUP BY Students.Sub_Course_ID");
                      while ($program = $programs->fetch_assoc()) {
                        echo '<option value="' . $program['ID'] . '">' . $program['Name'] . '</option>';
                      }
                      ?>
                    </select>
                  </div>
                </div>
                <div class="col-md-4 m-b-10">
                  <div class="input-daterange input-group" id="datepicker-range">
                    <input type="text" class="input-sm form-control" placeholder="Select Date" id="startDateFilter"
                      name="start" />
                    <div class="input-group-addon">to</div>
                    <input type="text" class="input-sm form-control" placeholder="Select Date" id="endDateFilter"
                      onchange="addDateFilter()" name="end" />
                  </div>
                </div>
                <div class="col-md-2 m-b-10">
                  <div class="form-group">
                    <select class="full-width" style="width:40px" data-init-plugin="select2" id="application_status"
                      onchange="addFilter(this.value, 'application_status')" data-placeholder="Choose App. Status">
                      <option value="">Application Status</option>
                      <option value="1">Document Verified</option>
                      <option value="2">Payment Verified</option>
                      <option value="3">Both Verified</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="d-flex justify-content-start">
                  
                <?php if ($_SESSION['Role'] != 'Sub-Center' && $_SESSION['Role'] != 'Center') { ?>
                  <!-- vertical type filter -->
                  <!--<div class="col-md-2 m-b-10">-->
                  <!--  <div class="form-group">-->
                  <!--    <select class="full-width" style="width:40px" data-init-plugin="select2" id="vartical_type"-->
                  <!--      onchange="addFilterVerticalType(this.value,'users', 'vartical_type')"-->
                  <!--      data-placeholder="Choose Vertical Type">-->
                  <!--      <option value="">Vertical Type</option>-->
                  <!--      <option value="1">Edtech</option>-->
                  <!--      <option value="2">IITS</option>-->
                  <!--      <option value="3">Rudra</option>-->
                  <!--    </select>-->
                  <!--  </div>-->
                  <!--</div>-->
                  
                <?php } ?>
                <?php
                $allowedRoles = ["Administrator", "Academic Head", "Counsellor", "University Head", "Operations", "Accountant", "Sub-Counsellor"];
                if (in_array($_SESSION['Role'], $allowedRoles)) {
                  ?>
                  <div class="col-md-2 m-b-10">
                    <div class="form-group">
                      <select class="full-width" style="border: transparent;" data-init-plugin="select2" name="center"
                        id="center" onchange="addFilter(this.value, 'users')">
                        <option value="">Select Center</option>
                      </select>
                    </div>
                  </div>
                <?php } ?>
                <?php
                $userArr = array("Academic Head", "Accountant", "Administrator", "Counsellor", "Operations", "Sub-Counsellor", "University Head");
                if ($_SESSION['CanCreateSubCenter'] == 1 || in_array($_SESSION['Role'], $userArr)) { ?>
                  <div class="col-md-2 m-b-10">
                    <div class="form-group">
                      <select class="full-width" style="width:40px" data-init-plugin="select2" id="sub_center"
                        onchange="addSubCenterFilter(this.value, 'users')" data-placeholder="Choose Sub Center">

                      </select>
                    </div>
                  </div>
                <?php } ?>
                <div class="col-md-4 m-b-10">
                  <div class="input-daterange input-group" id="datepicker-range1">
                    <input type="text" class="input-sm form-control" placeholder="Select Fee Received Date" id="startProcessDateFilter"
                      name="start" />
                    <div class="input-group-addon">to</div>
                    <input type="text" class="input-sm form-control" placeholder="Select Fee Received Date" id="endProcessDateFilter"
                      onchange="addProcessDateFilter()" name="end" />
                  </div>
                </div>
              </div>

              <!-- end filter -->
            </div>
            <div class="clearfix"></div>
          </div>
          <div class="card-body">
            <div class="card card-transparent">
              <!-- Nav tabs -->
              <ul class="nav nav-tabs nav-tabs-linetriangle" data-init-reponsive-tabs="dropdownfx">
                <li class="nav-item">
                  <a class="active" data-toggle="tab" data-target="#applications" href="#"><span>All Applications - <span
                        id="application_count">0</span></span></a>
                </li>
                <li class="nav-item">
                  <a data-toggle="tab" data-target="#not_processed" href="#"><span>Not Processed - <span
                        id="not_processed_count">0</span></span></a>
                </li>
                <li class="nav-item">
                  <a data-toggle="tab" data-target="#ready_for_verification" href="#"><span>Ready for Verification - <span
                        id="ready_for_verification_count">0</span></span></a>
                </li>
                <li class="nav-item">
                  <a data-toggle="tab" data-target="#verified" href="#"><span>Verified - <span
                        id="verified_count">0</span></span></a>
                </li>
                <li class="nav-item">
                    <a data-toggle="tab" data-target="#proccessed_to_university" href="#"><span>Proccessed to University -
                        <span id="processed_to_university_count">0</span></span></a>
                  </li>
                <li class="nav-item">
                  <a data-toggle="tab" data-target="#enrolled" href="#"><span>Enrolled - <span
                        id="enrolled_count">0</span></span></a>
                </li>
              </ul>
              <!-- Tab panes -->
              <div class="tab-content">
                <div class="tab-pane active" id="applications">
                  <!--<div class="row d-flex justify-content-end">-->
                  <!--  <div class="col-md-2">-->
                  <!--    <input type="text" id="application-search-table" class="form-control pull-right"-->
                  <!--      placeholder="Search">-->
                  <!--  </div>-->
                  <!--</div>-->
                  <div class="table-bordered">
                    <table class="table table-hover nowrap table-responsive" style="margin-top:0px !important" id="application-table">
                      <thead>
                        <tr>
                          <th data-orderable="false"></th>
                          <th>Photo</th>
                          <th>Student ID</th>
                          <th>Status</th>
                          <th>Fee Received on</th>
                          <th>Document Verification</th>
                          <th>Payment Verification</th>
                          <th>Processed to University</th>
                          <th>Enrollment No.</th>
                          <th>
                            <?php print $_SESSION['university_id'] == '83' ? 'OA No.' : ($_SESSION['university_id'] == 14 ? 'SID Number' : 'OA No.') ?>
                          </th>
                          <th>Adm Session</th>
                          <th>Adm Type</th>
                          <th>Pendency</th>
                          <th>Student Name</th>
                          <th>Father Name</th>
                          <th>Program</th>
                          <th>
                            <?php $alloted_modes = [];
                            if (isset($_SESSION['university_id'])) {
                              $modes = $conn->query("SELECT Name FROM Modes WHERE University_ID = " . $_SESSION['university_id']);
                              while ($mode = $modes->fetch_assoc()) {
                                $alloted_modes[] = $mode['Name'];
                              }
                              echo implode('/', $alloted_modes);
                            } else {
                              echo 'Modes';
                            }
                            ?>
                          </th>
                          <th>Login</th>
                          <th>Conatct</th>
                          <th>ID Card</th>
                          <th>Admit Card</th>
                          <th>Exam</th>
                          <th>DOB</th>
                          <th>Code</th>
                          <th>Center</th>
                          <th>RM</th>
                        </tr>
                      </thead>
                    </table>
                  </div>
                </div>
                <div class="tab-pane" id="not_processed">
                  <!--<div class="row d-flex justify-content-end">-->
                  <!--  <div class="col-md-2">-->
                  <!--    <input type="text" id="not-processed-search-table" class="form-control pull-right"-->
                  <!--      placeholder="Search">-->
                  <!--  </div>-->
                  <!--</div>-->
                  <div class="table-bordered">
                    <table class="table table-hover table-responsive nowrap" style="margin-top:0px !important" id="not-processed-table">
                      <thead>
                        <tr>
                          <th data-orderable="false"></th>
                          <th>Photo</th>
                          <th>Student ID</th>
                          <th>Status</th>
                          <th>Fee Received on</th>
                          <th>Adm Session</th>
                          <th>Adm Type</th>
                          <th>Pendency</th>
                          <th>Student Name</th>
                          <th>Father Name</th>
                          <th>Program</th>
                          <th>
                            <?php $alloted_modes = [];
                            if (isset($_SESSION['university_id'])) {
                              $modes = $conn->query("SELECT Name FROM Modes WHERE University_ID = " . $_SESSION['university_id']);
                              while ($mode = $modes->fetch_assoc()) {
                                $alloted_modes[] = $mode['Name'];
                              }
                              echo implode('/', $alloted_modes);
                            } else {
                              echo 'Modes';
                            }
                            ?>
                          </th>
                          <th>Login</th>
                          <th>Conatct</th>                          
                          <th>ID Card</th>
                          <th>Admit Card</th>
                          <th>Exam</th>
                          <th>DOB</th>
                          <th>Code</th>
                          <th>Center</th>
                          <th>RM</th>
                        </tr>
                      </thead>
                    </table>
                  </div>
                </div>
                <div class="tab-pane" id="ready_for_verification">
                  <div class="row d-flex justify-content-end">
                    <!--<div class="col-md-2">-->
                    <!--  <input type="text" id="ready-for-verification-search-table" class="form-control pull-right"-->
                    <!--    placeholder="Search">-->
                    <!--</div>-->
                  </div>
                  <div class="table-bordered">
                    <table class="table table-hover nowrap table-responsive " style="margin-top:0px !important;" id="ready-for-verification-table">
                      <thead>
                        <tr>
                          <th data-orderable="false"></th>
                          <th>Photo</th>
                          <th>Student ID</th>
                          <th>Status</th>
                          <th>Fee Received on</th>
                          <th>Document Verification</th>
                          <th>Payment Verification</th>
                          <th>Enrollment No.</th>
                          <th>
                            <?php print $_SESSION['university_id'] == '83' ? 'OA No.' : ($_SESSION['university_id'] == 14 ? 'SID Number' : 'OA No.') ?>
                          </th>
                          <th>Adm Session</th>
                          <th>Adm Type</th>
                          <th>Pendency</th>
                          <th>Student Name</th>
                          <th>Father Name</th>
                          <th>Program</th>
                          <th>
                            <?php $alloted_modes = [];
                            if (isset($_SESSION['university_id'])) {
                              $modes = $conn->query("SELECT Name FROM Modes WHERE University_ID = " . $_SESSION['university_id']);
                              while ($mode = $modes->fetch_assoc()) {
                                $alloted_modes[] = $mode['Name'];
                              }
                              echo implode('/', $alloted_modes);
                            } else {
                              echo 'Modes';
                            }
                            ?>
                          </th>
                          <th>Login</th>
                          <th>Conatct</th>
                          <th>ID Card</th>
                          <th>Admit Card</th>
                          <th>Exam</th>
                          <th>DOB</th>
                          <th>Code</th>
                          <th>Center</th>
                          <th>RM</th>
                        </tr>
                      </thead>
                    </table>
                  </div>
                </div>
                <div class="tab-pane" id="verified">
                  <!--<div class="row d-flex justify-content-end">-->
                  <!--  <div class="col-md-2">-->
                  <!--    <input type="text" id="verified-search-table" class="form-control pull-right" placeholder="Search">-->
                  <!--  </div>-->
                  <!--</div>-->
                  <div class="table-bordered">
                    <table class="table table-hover nowrap table-responsive  mt-0" style="mmargin-top:0px !important;" id="verified-table">
                      <thead>
                        <tr>
                          <th data-orderable="false"></th>
                          <th>Photo</th>
                          <th>Student ID</th>
                          <th>Status</th>
                          <th>Fee Received on</th>
                          <th>Document Verification</th>
                          <th>Payment Verification</th>
                          <th>Processed to University</th>
                          <th>Enrollment No.</th>
                          <th>
                            <?php print $_SESSION['university_id'] == '83' ? 'OA No.' : ($_SESSION['university_id'] == 14 ? 'SID Number' : 'OA No.') ?>
                          </th>
                          <th>Adm Session</th>
                          <th>Adm Type</th>
                          <th>Pendency</th>
                          <th>Student Name</th>
                          <th>Father Name</th>
                          <th>Program</th>
                          <th>
                            <?php $alloted_modes = [];
                            if (isset($_SESSION['university_id'])) {
                              $modes = $conn->query("SELECT Name FROM Modes WHERE University_ID = " . $_SESSION['university_id']);
                              while ($mode = $modes->fetch_assoc()) {
                                $alloted_modes[] = $mode['Name'];
                              }
                              echo implode('/', $alloted_modes);
                            } else {
                              echo 'Modes';
                            }
                            ?>
                          </th>
                          <th>Login</th>
                          <th>Conatct</th>
                          <th>ID Card</th>
                          <th>Admit Card</th>
                          <th>Exam</th>
                          <th>DOB</th>
                          <th>Code</th>
                          <th>Center</th>
                          <th>RM</th>
                        </tr>
                      </thead>
                    </table>
                  </div>
                </div>
                       <div class="tab-pane" id="proccessed_to_university">
                    <div class="row d-flex justify-content-end">
                      <div class="col-md-2">
                        <input type="text" id="proccessed-to-university-search-table" class="form-control pull-right custom_search_section"
                          placeholder="Search">
                      </div>
                    </div>
                    <div class="">
                      <table class="table table-hover nowrap table-responsive" id="proccessed-to-university-table">
                        <thead>
                          <tr>
                            <th data-orderable="false"></th>
                            <th>Photo</th>
                            <th>Student</th>
                            <th>Status</th>
                            <th>Process by Center</th>
                            <th>Document Verification</th>
                            <th>Payment Verification</th>
                            <th>Processed to University</th>
                            <th>Enrollment No.</th>
                            <th>ABC ID</th>
                            <th>
                              <?php print $_SESSION['university_id'] == '83' ? 'OA No.' : ($_SESSION['university_id'] == 14 ? 'SID Number' : 'Roll Number') ?>
                            </th>
                            <th>Adm Session</th>
                            <th>Created At</th>
                            <th>Adm Type</th>
                            <th>Pendency</th>
                            <th>Student Name</th>
                            <th>Father Name</th>
                            <th>Program</th>
                            <?php if ($_SESSION['university_id'] == 48) { ?>
                              <th>Course Category</th>
                            <?php } ?>
                            <th>
                              <?php $alloted_modes = [];
                              if (isset($_SESSION['university_id'])) {
                                $modes = $conn->query("SELECT Name FROM Modes WHERE University_ID = " . $_SESSION['university_id']);
                                while ($mode = $modes->fetch_assoc()) {
                                  $alloted_modes[] = $mode['Name'];
                                }
                                echo implode('/', $alloted_modes);
                              } else {
                                echo 'Modes';
                              }
                              ?>
                            </th>
                            <th>Login</th>
                            <th>Conatct</th>
                            <th>ID Card</th>
                            <th>Admit Card</th>
                            <th>Exam</th>
                            <th>DOB</th>
                            <th>Code</th>
                            <th>Center</th>
                            <th>RM</th>
                          </tr>
                        </thead>
                      </table>
                    </div>
                  </div>
                <div class="tab-pane" id="enrolled">
                  <!--<div class="row d-flex justify-content-end">-->
                  <!--  <div class="col-md-2">-->
                  <!--    <input type="text" id="enrolled-search-table" class="form-control pull-right" placeholder="Search">-->
                  <!--  </div>-->
                  <!--</div>-->
                  <div class="table-bordered">
                    <table class="table table-hover nowrap table-responsive mt-0" id="enrolled-table">
                      <thead>
                        <tr>
                          <th data-orderable="false"></th>
                          <th>Photo</th>
                          <th>Student ID</th>
                          <th>Status</th>
                          <th>Fee Received on</th>
                          <th>Document Verification</th>
                          <th>Payment Verification</th>
                          <th>Processed to University</th>
                          <th>Enrollment No.</th>
                          <th>
                            <?php print $_SESSION['university_id'] == '83' ? 'OA No.' : ($_SESSION['university_id'] == 14 ? 'SID Number' : 'OA No.') ?>
                          </th>
                          <th>Adm Session</th>
                          <th>Adm Type</th>
                          <th>Pendency</th>
                          <th>Student Name</th>
                          <th>Father Name</th>
                          <th>Program</th>
                          <th>
                            <?php $alloted_modes = [];
                            if (isset($_SESSION['university_id'])) {
                              $modes = $conn->query("SELECT Name FROM Modes WHERE University_ID = " . $_SESSION['university_id']);
                              while ($mode = $modes->fetch_assoc()) {
                                $alloted_modes[] = $mode['Name'];
                              }
                              echo implode('/', $alloted_modes);
                            } else {
                              echo 'Modes';
                            }
                            ?>
                          </th>
                          <th>Login</th>
                          <th>Conatct</th>
                          <th>ID Card</th>
                          <th>Admit Card</th>
                          <th>Exam</th>
                          <th>DOB</th>
                          <th>Code</th>
                          <th>Center</th>
                          <th>RM</th>
                        </tr>
                      </thead>
                    </table>
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

  <div class="modal fade slide-up" id="reportmodal" style="z-index:9999" tabindex="-1" role="dialog"
    data-keyboard="false" data-backdrop="static" aria-hidden="false">
    <div class="modal-dialog modal-md">
      <div class="modal-content-wrapper">
        <div class="modal-content" id="report-modal-content">
        </div>
      </div>
    </div>
  </div>

  <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-top.php'); ?>
  <script src="/assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js" type="text/javascript"></script>
  <script src="/assets/plugins/bootstrap-daterangepicker/daterangepicker.js"></script>
  <script>
    $('#datepicker-range').datepicker({
      format: 'dd-mm-yyyy',
      autoclose: true,
      endDate: '0d'
    });
    $('#datepicker-range1').datepicker({
      format: 'dd-mm-yyyy',
      autoclose: true,
      endDate: '0d'
    });
  </script>

  <?php if ($_SESSION['Role'] == 'Administrator' && !isset($_SESSION['university_id'])) { ?>
    <script type="text/javascript">
      changeUniversity();
    </script>
  <?php } ?>

  <script type="text/javascript">
    $(function () {
        // console.log('');
      var role = '<?php echo $_SESSION['Designation']; ?>';
      //console.log(role);
      var showInhouse = role == 'Administartor' || role == 'University Head' || role == 'Counsellor' ? true : false;
      var counsellor = role == 'Counsellor' ? true : false;
      var is_accountant = role == 'Accountant' ? true : false;
      var is_operations = ['Operations', 'Administartor', 'University Head'].includes(role) ? true : false;
      var hasStudentLogin = '<?php echo isset($_SESSION['has_lms']) && $_SESSION['has_lms'] == 1 ? false : false; ?>';
      var showStatus = false;
      var applicationTable = $('#application-table');
      var notProcessedTable = $('#not-processed-table');
      var readyForVerificationTable = $('#ready-for-verification-table');
      var verifiedTable = $('#verified-table');
      var processedToUniversityTable = $('#proccessed-to-university-table');
      var enrolledTable = $('#enrolled-table');
      var is_university = "<?php echo $_SESSION['Designation']=="University"?true:false; ?>";
      var is_oa_required = "<?php echo $_SESSION['university_id']=="8300"?true:true; ?>"
      var applicationSettings = {
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'ajax': {
          'url': '/app/applications/application-server',
          'type': 'POST',
          complete: function (xhr, responseText) {
            $('#application_count').html(xhr.responseJSON.iTotalDisplayRecords);
          }
        },
        'columns': [
        // {
        //   data: "ID",
        //   "render": function (data, type, row) {
        //     var edit = showInhouse || row.Step < 4 ? '<a href="/admissions/application-form?id=' + data + '"><i class="uil uil-edit mr-1" title="Edit Application Form"></i></a>' : '';
        //     var deleted = showInhouse ? '<i class="uil uil-trash mr-1 cursor-pointer" title="Delete Application Form" onclick="destroy(&#39;application-form&#39;, &#39;' + data + '&#39;)"></i>' : '';
        //     var print = row.Step == 4 ? '<i class="uil uil-print mr-1 cursor-pointer" title="Print Application Form" onclick="printForm(&#39;' + data + '&#39;)"></i>' : '';
        //     //var print  = "";
        //     var proccessedByCenter = row.Process_By_Center != 1 ? "Not Proccessed" : row.Process_By_Center
        //     var documentVerified = row.Document_Verified == 1 ? "Not Verified" : row.Document_Verified
        //     var proccessedToUniversity = row.Processed_To_University == 1 ? "Not Proccessed" : row.Processed_To_University
        //     var paymentVerified = row.Payment_Received == 1 ? "Not Verified" : row.Payment_Received
        //     var info = row.Step == 4 ? '<i class="uil uil-info-circle cursor-pointer" data-html="true" data-toggle="tooltip" data-placement="top" title="Proccessed By Center: <strong>' + proccessedByCenter + '</strong>&#013;&#010;Document Verified: <strong>' + documentVerified + '</strong>&#013;&#010;Payment Verified: <strong>' + paymentVerified + '</strong>&#013;&#010;Proccessed to University: <strong>' + proccessedToUniversity + '</strong>"></i>' : '';
        //     return print + edit + deleted + info;
        //   }
        // },
         {
            data: "ID",width:"15%",
            render: function(data, type, row) {
                   /* if(counsellor == 1){
                    //  console.log(showInhouse);
              var edit = '<a class="dropdown-item" href="/admissions/application-form?id=' + data + '"><i class="uil uil-edit mr-1"></i> Edit</a>';
                    } else if (row.Process_By_Center != 1 && !showInhouse) {
                     var edit="";
                    //  console.log("step 1");
                 }else if(showInhouse && row.Document_Verified == 1){
                    //  console.log(showInhouse);
              var edit = showInhouse?
                '<a class="dropdown-item" href="/admissions/application-form?id=' + data + '"><i class="uil uil-edit mr-1"></i> Edit</a>' :
                '';}else{
                    // console.log("step 3");
                    var edit = showInhouse || row.Document_Verified == 1 ?
                '<a class="dropdown-item" href="/admissions/application-form?id=' + data + '"><i class="uil uil-edit mr-1"></i> Edit</a>' :
                '';
                }*/
              var edit = showInhouse || row.Step < 4 ? '<a href="/admissions/application-form?id=' + data + '"><i class="uil uil-edit mr-1" title="Edit"></i>Edit</a>' : '';
                
                // console.log(row.Process_By_Center);
              var deleted = showInhouse  ?
                '<a class="dropdown-item " href="javascript:void(0);" onclick="destroy(\'application-form\', \'' + data + '\')"><i class="uil uil-trash mr-1"></i> Delete</a>' :
                '';
           // console.log(deleted+"#############################3333333");
              var print = row.Step == 4 ?
                  //console.log(data);
                '<a class="dropdown-item" href="javascript:void(0);" onclick="printForm(\'' + data + '\')"><i class="uil uil-print mr-1"></i> Print</a>' :
                '';

              var proccessedByCenter = row.Process_By_Center != 1 ? "Not Proccessed" : row.Process_By_Center;
              var documentVerified = row.Document_Verified == 1 ? "Not Verified" : row.Document_Verified;
              var proccessedToUniversity = row.Processed_To_University == 1 ? "Not Proccessed" : row.Processed_To_University;
              var paymentVerified = row.Payment_Received == 1 ? "Not Verified" : row.Payment_Received;

              var info = row.Step == 4 ?
                '<a class="dropdown-item" href="javascript:void(0);" data-toggle="tooltip" data-html="true" data-placement="top" title="Processed By Center: <strong>' + proccessedByCenter +
                '</strong><br>Document Verified: <strong>' + documentVerified +
                '</strong><br>Payment Verified: <strong>' + paymentVerified +
                '</strong><br>Processed to University: <strong>' + proccessedToUniversity +
                '</strong>"><i class="uil uil-info-circle mr-1"></i> Info</a>' :
                '';
                
                if(is_university){
                    return `
                <div class="dropdown">
                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenu${data}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="uil uil-ellipsis-v"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu${data}">
                ${print}${info}${deleted}
                </div>
                </div>
                 `;
                }
                
              return `
                <div class="dropdown">
                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenu${data}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="uil uil-ellipsis-v"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu${data}">
                ${print}${info}${edit}${deleted}
                </div>
                </div>
                 `;
            }
          },
        {
          data: "Photo",width:"15%",
          "render": function (data, type, row) {
            return '<span class="thumbnail-wrapper d48 circular inline">\
              <img src="' + data + '" alt="" data-src="' + data + '"\
                data-src-retina="' + data + '" width="32" height="32">\
            </span>';
          }
        },
        {
          data: "Unique_ID",width:"15%",
          "render": function (data, type, row) {
            return '<span class="cursor-pointer" title="Click to export documents" onclick="exportDocuments(&#39;' + row.ID + '&#39;)"><strong>' + data + '</strong></span>';
          }
        },
        {
          data: "Step",width:"15%",
          "render": function (data, type, row) {
            var label_class = data < 4 ? 'label-important' : 'label-success';
            var status = data < 4 ? 'In Draft @ Step ' + data : 'Completed';
            return '<sapn class="label ' + label_class + '">' + status + '</sapn>';
          }
        },
        {
          data: "Process_By_Center",width:"15%",
          "render": function (data, type, row) {
            if (data == 1 && row.Step == 4) {
              return '<div class="text-center"><span class="label label-danger">Not Received</span></div>';
            } else {
              var show = row.Step == 4 ? '<div class="text-center"><span class="label label-success">' + data + '</span></div>' : '';
              return show;
            }
          }
        },
        {
          data: "Document_Verified",width:"15%",
          "render": function (data, type, row) {
            if (row.Pendency_Status == 2) {
              if (!showInhouse) {
                return '<div class="text-center label label-primary"><span class="cursor-pointer"><strong>In Review</strong></span></div>'
              } else {
                return is_operations ? '<div class="text-center label label-warning"><span class="cursor-pointer" onclick="verifyDocument(&#39;' + row.ID + '&#39;)"><strong>Re-Review</strong></span></div>' : '<div class="text-center label label-warning"><span><strong>Re-Review</strong></span></div>'
              }
            } else if (row.Pendency != 0) {
              if (!showInhouse) {
                return '<div class="text-center label label-primary"><span class="cursor-pointer" onclick="uploadPendency(&#39;' + row.ID + '&#39;)"><strong>Pendency</strong></span></div>'
              } else {
                return is_operations ? '<div class="text-center label label-primary"><span class="cursor-pointer" onclick="reportPendency(&#39;' + row.ID + '&#39;)"><strong>Pendency</strong></span></div>' : '<div class="text-center label label-primary"><span><strong>Pendency</strong></span></div>'
              }
            } else {
                console.log(is_university);
              if (data == 1) {
                var show = (is_operations || is_university) && row.Process_By_Center != 1 ? '<div class="text-center label label-primary"><span class="cursor-pointer" onclick="verifyDocument(&#39;' + row.ID + '&#39;)"><strong>Review</strong></span></div>' : row.Step == 4 && row.Process_By_Center != 1 ? '<div class="text-center label label-warning"><strong>Pending</strong></div>' : '';
                return show;
              } else {
                var show = row.Step == 4 && row.Process_By_Center != 1 ? '<div class="text-center"><span class="label label-success">Verified at ' + data + '</span></div>' : '';
                return show;
              }
            }
          }
        },
        {
          data: "Payment_Received",width:"15%",
          "render": function (data, type, row) {
            if (data == 1 && row.Step == 4 && row.Process_By_Center != 1) {
              var show = is_accountant ? '<div class="text-center label label-primary"><span class="cursor-pointer" onclick="verifyPayment(&#39;' + row.ID + '&#39;)"><strong>Review</strong></span></div>' : '<center><span class="label label-warning">Pending</span></center>';
              return show;
            } else if (row.Process_By_Center != 1) {
              var show = row.Step == 4 ? '<div class="text-center"><span class="label label-success">Verified on ' + data + '</span></div>' : '';
              return show;
            } else {
              return '';
            }
          },
          visible: false
        },
        {
          data: "Processed_To_University",width:"15%",
          "render": function (data, type, row) {
            if (data == 1) {
              var show = showInhouse && row.Document_Verified != 1 && row.Payment_Received != 1 ? '<div class="form-check complete mt-2">\
                <input type="checkbox" id="processed-to-university-' + row.ID + '" onclick="processedToUniversity(&#39;' + row.ID + '&#39;)">\
                <label for="processed-to-university-' + row.ID + '">Mark as Processed</label>\
              </div>' : "";
              return show;
            } else {
              var show = row.Step == 4 ? '<div class="text-center"><span class="label label-success">Processed on ' + data + '</span></div>' : '';
              return show;
            }
          },
          visible: false
        },
        {
          data: "Enrollment_No",width:"15%",
          "render": function (data, type, row) {
            var edit="";
            // if (role !== 'Counsellor' && role !== 'Sub-Counsellor') {
            var edit = showInhouse && row.Document_Verified != 1 ? '<i class="ti ti-edit-circle add_btn_form h6 cursor-pointer p-1 ml-2" title="Add Enrollment No." onclick="addEnrollment(&#39;' + row.ID + '&#39;)">' : '';
            // }
            return data + edit;
          }
        },
        {
          data: "OA_Number",width:"15%",
          "render": function (data, type, row) {
             var edit="";
             console.log(role);
            if (role == 'University Head' || role == 'Counsellor' || role == 'Administartor') {
            var edit = showInhouse || is_oa_required ? '<i class="ti ti-edit-circle add_btn_form h6 cursor-pointer p-1 ml-2" title="Add OA No" onclick="addOANumber(&#39;' + row.ID + '&#39;)">' : '';
            }
            return data + edit;
          },visible:is_oa_required
        },
        {
          data: "Adm_Session",width:"15%",
        },
        {
          data: "Adm_Type",width:"15%",
        },
        {
          data: "Adm_Type",width:"15%",
          "render": function (data, type, row) {
            return '<span onclick="reportPendnency(' + row.ID + ')"><strong>Report</strong><span>';
          },
          visible: false,
        },
        {
          data: "First_Name",width:"15%",
          "render": function (data, type, row) {
            return '<strong>' + data + '</strong>';
          }
        },
        {
          data: "Father_Name",width:"15%",
        },
        {
          data: "Short_Name",width:"15%",
        },
        {
          data: "Duration",width:"15%",
        },
        {
          data: "Status",width:"15%",
          "render": function (data, type, row) {
            var active = data == 1 ? 'Active' : 'Inactive';
            if (row.Step == 4 && showInhouse) {
              var checked = data == 1 ? 'checked' : '';
              if (role !== 'Counsellor' && role !== 'Sub-Counsellor') {
              return '<div class="form-check form-check-inline switch switch-lg success">\
                <input onclick="changeStatus(\'Students\', &#39;' + row.ID + '&#39;);" type="checkbox" ' + checked + ' id="student-status-switch-' + row.ID + '">\
                <label for="student-status-switch-' + row.ID + '">' + active + '</label>\
              </div>';}else{
                  return '<label for="student-status-switch-' + row.ID + '">' + active + '</label>';
              }
            } else {
              return active;
            }
          },
          visible: hasStudentLogin
        },
          {
          data: "Contact",width:"15%",
        },
        {
          data: "ID_Card",width:"15%",
          "render": function (data, type, row) {
            var active = data == 1 ? 'Active' : 'Inactive';
            if (row.Step == 4 && showInhouse && row.Enrollment_No.length > 0) {
              var checked = data == 1 ? 'checked' : '';
              if (role !== 'Counsellor' && role !== 'Sub-Counsellor') {
              return '<div class="form-check form-check-inline switch switch-lg success">\
                <input onclick="changeStatus(\'Students\', &#39;' + row.ID + '&#39;, \'ID_Card\');" type="checkbox" ' + checked + ' id="student-id-card-switch-' + row.ID + '">\
                <label for="student-id-card-switch-' + row.ID + '">' + active + '</label>\
              </div>';}else{
                  return '<label for="student-id-card-switch-' + row.ID + '">' + active + '</label>';
              }
            } else {
              return active;
            }
          },
          visible: hasStudentLogin
        },
        {
          data: "Admit_Card",width:"15%",
          "render": function (data, type, row) {
            var active = data == 1 ? 'Active' : 'Inactive';
            if (row.Step == 4 && showInhouse && row.Enrollment_No.length > 0) {
              var checked = data == 1 ? 'checked' : '';
              if (role !== 'Counsellor' && role !== 'Sub-Counsellor') {
              return '<div class="form-check form-check-inline switch switch-lg success">\
                <input onclick="changeStatus(\'Students\', &#39;' + row.ID + '&#39;, \'Admit_Card\');" type="checkbox" ' + checked + ' id="student-admit-card-switch-' + row.ID + '">\
                <label for="student-admit-card-switch-' + row.ID + '">' + active + '</label>\
              </div>';}else{
                  return '<label for="student-admit-card-switch-' + row.ID + '">' + active + '</label>';
              }
            } else {
              return active;
            }
          },
          visible: hasStudentLogin
        },
        {
          data: "Exam",width:"15%",
          "render": function (data, type, row) {
            var active = data == 1 ? 'Active' : 'Inactive';
            if (row.Step == 4 && showInhouse && row.Enrollment_No.length > 0) {
              var checked = data == 1 ? 'checked' : '';
              if (role !== 'Counsellor' && role !== 'Sub-Counsellor') {
              return '<div class="form-check form-check-inline switch switch-lg success">\
                <input onclick="changeStatus(\'Students\', &#39;' + row.ID + '&#39;, \'Exam\');" type="checkbox" ' + checked + ' id="student-exam-switch-' + row.ID + '">\
                <label for="student-exam-switch-' + row.ID + '">' + active + '</label>\
              </div>';}else{
                  return '<label for="student-exam-switch-' + row.ID + '">' + active + '</label>';
              }
            } else {
              return active;
            }
          },
          visible: hasStudentLogin
        },
        {
          data: "DOB",width:"15%",
        },
        {
          data: "Center_Code",width:"15%",
        },
        {
          data: "Center_Name",width:"15%",
        },
        {
          data: "RM",width:"15%",
          visible: ['Center', 'Sub-Center','University'].includes(role) ? false : true
        }
        ],
       "sDom": "<'row pt-3 px-3 w-100 p-0 m-0'<'col-sm-6 pr-0 pl-0 custon_text_start'l><'col-sm-6 pr-0 pl-0'f>><t><'row'<p i>>",
            "destroy": true,
            "scrollCollapse": true,
            "oLanguage": {
              "sInfo": "Showing <b>_START_ to _END_</b> of _TOTAL_ entries"
            },
        drawCallback: function (settings, json) {
          $('[data-toggle="tooltip"]').tooltip();
        },
        "aaSorting": []
      };

      var notProcessedSettings = {
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'ajax': {
          'url': '/app/applications/not-processed-server',
          'type': 'POST',
          complete: function (xhr, responseText) {
            $('#not_processed_count').html(xhr.responseJSON.iTotalDisplayRecords);
          }
        },
        'columns': [
        // {
        //   data: "ID",
        //   "render": function (data, type, row) {
        //     var edit = showInhouse || row.Step < 4 ? '<a href="/admissions/application-form?id=' + data + '"><i class="uil uil-edit mr-1" title="Edit Application Form"></i></a>' : '';
        //     var deleted = showInhouse ? '<i class="uil uil-trash mr-1 cursor-pointer" title="Delete Application Form" onclick="destroy(&#39;application-form&#39;, &#39;' + data + '&#39;)"></i>' : '';
        //      var print = row.Step == 4 ? '<i class="uil uil-print mr-1 cursor-pointer" title="Print Application Form" onclick="printForm(&#39;' + data + '&#39;)"></i>' : '';
        //   // var print  = "";
        //     var proccessedByCenter = row.Process_By_Center != 1 ? "Not Proccessed" : row.Process_By_Center
        //     var documentVerified = row.Document_Verified == 1 ? "Not Verified" : row.Document_Verified
        //     var proccessedToUniversity = row.Processed_To_University == 1 ? "Not Proccessed" : row.Processed_To_University
        //     var paymentVerified = row.Payment_Received == 1 ? "Not Verified" : row.Payment_Received
        //     var info = row.Step == 4 ? '<i class="uil uil-info-circle cursor-pointer" data-html="true" data-toggle="tooltip" data-placement="top" title="Proccessed By Center: <strong>' + proccessedByCenter + '</strong>&#013;&#010;Document Verified: <strong>' + documentVerified + '</strong>&#013;&#010;Payment Verified: <strong>' + paymentVerified + '</strong>&#013;&#010;Proccessed to University: <strong>' + proccessedToUniversity + '</strong>"></i>' : '';
        //     return print + edit + deleted + info;
        //   }
        // },
          {
         data: "ID",width:"15%",
         render: function (data, type, row) {
                if (row.Process_By_Center !== 1) {
                     var edit="";
                 }else{
         var edit = (showInhouse || row.Document_Verified == 1 )
         ? '<a class="dropdown-item" href="/admissions/application-form?id=' + data + '"><i class="uil uil-edit mr-1"></i> Edit</a>'
         : '';}
         var deleted = showInhouse
         ? '<a class="dropdown-item " href="javascript:void(0);" onclick="destroy(\'application-form\', \'' + data + '\')"><i class="uil uil-trash mr-1"></i> Delete</a>'
         : '';
         var print = row.Step == 4
         ? '<a class="dropdown-item" href="javascript:void(0);" onclick="printForm(\'' + data + '\')"><i class="uil uil-print mr-1"></i> Print</a>'
         : '';
         var proccessedByCenter = row.Process_By_Center != 1 ? "Not Proccessed" : row.Process_By_Center;
         var documentVerified = row.Document_Verified == 1 ? "Not Verified" : row.Document_Verified;
         var proccessedToUniversity = row.Processed_To_University == 1 ? "Not Proccessed" : row.Processed_To_University;
         var paymentVerified = row.Payment_Received == 1 ? "Not Verified" : row.Payment_Received;
         var info = row.Step == 4
         ? '<a class="dropdown-item" href="javascript:void(0);" data-toggle="tooltip" data-html="true" data-placement="top" title="Processed By Center: <strong>' + proccessedByCenter +
        '</strong><br>Document Verified: <strong>' + documentVerified +
        '</strong><br>Payment Verified: <strong>' + paymentVerified +
        '</strong><br>Processed to University: <strong>' + proccessedToUniversity +
        '</strong>"><i class="uil uil-info-circle mr-1"></i> Info</a>'
        : '';
        return `<div class="dropdown">
        <button class="btn btn-sm btn-light dropdown-toggle" type="button" id="dropdownMenu_${data}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <i class="uil uil-ellipsis-v"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu_${data}">
          ${edit}${info}${print}${deleted}
         </div>
        </div>`;}},
        {
          data: "Photo",width:"15%",
          "render": function (data, type, row) {
            return '<span class="thumbnail-wrapper d48 circular inline">\
              <img src="' + data + '" alt="" data-src="' + data + '"\
                data-src-retina="' + data + '" width="32" height="32">\
            </span>';
          }
        },
        {
          data: "Unique_ID",width:"15%",
          "render": function (data, type, row) {
            return '<span class="cursor-pointer" title="Click to export documents" onclick="exportDocuments(&#39;' + row.ID + '&#39;)"><strong>' + data + '</strong></span>';
          }
        },
        {
          data: "Step",width:"15%",
          "render": function (data, type, row) {
            var label_class = data < 4 ? 'label-important' : 'label-success';
            var status = data < 4 ? 'In Draft @ Step ' + data : 'Completed';
            return '<sapn class="label ' + label_class + '">' + status + '</sapn>';
          }
        },
        {
          data: "Process_By_Center",width:"15%",
          "render": function (data, type, row) {
            if (data == 1 && row.Step == 4) {
              return '<div class="text-center"><span class="label label-danger">Not Received</span></div>';
            } else {
              var show = row.Step == 4 ? '<div class="text-center"><span class="label label-success">' + data + '</span></div>' : '';
              return show;
            }
          }
        },
        {
          data: "Adm_Session",width:"15%",
        },
        {
          data: "Adm_Type",width:"15%",
        },
        {
          data: "Adm_Type",width:"15%",
          "render": function (data, type, row) {
            return '<span onclick="reportPendnency(' + row.ID + ')"><strong>Report</strong><span>';
          },
          visible: false,
        },
        {
          data: "First_Name",width:"15%",
          "render": function (data, type, row) {
            return '<strong>' + data + '</strong>';
          }
        },
        {
          data: "Father_Name",width:"15%",
        },
        {
          data: "Short_Name",width:"15%",
        },
        {
          data: "Duration",width:"15%",
        },
        {
          data: "Status",width:"15%",
          "render": function (data, type, row) {
            var active = data == 1 ? 'Active' : 'Inactive';
            if (row.Step == 4 && showInhouse) {
              var checked = data == 1 ? 'checked' : '';
              if (role !== 'Counsellor' && role !== 'Sub-Counsellor') {
              return '<div class="form-check form-check-inline switch switch-lg success">\
                <input onclick="changeStatus(\'Students\', &#39;' + row.ID + '&#39;);" type="checkbox" ' + checked + ' id="student-status-switch-' + row.ID + '">\
                <label for="student-status-switch-' + row.ID + '">' + active + '</label>\
              </div>';}else{
                  return '<label for="student-status-switch-' + row.ID + '">' + active + '</label>';
              }
            } else {
              return active;
            }
          },
          visible: hasStudentLogin || showStatus
        },
          {
          data: "Contact",width:"15%",
        },
        {
          data: "ID_Card",width:"15%",
          "render": function (data, type, row) {
            var active = data == 1 ? 'Active' : 'Inactive';
            if (row.Step == 4 && showInhouse && row.Enrollment_No.length > 0) {
              var checked = data == 1 ? 'checked' : '';
               if (role !== 'Counsellor' && role !== 'Sub-Counsellor') {
              return '<div class="form-check form-check-inline switch switch-lg success">\
                <input onclick="changeStatus(\'Students\', &#39;' + row.ID + '&#39;, \'ID_Card\');" type="checkbox" ' + checked + ' id="student-id-card-switch-' + row.ID + '">\
                <label for="student-id-card-switch-' + row.ID + '">' + active + '</label>\
              </div>';}else{
                 return '<label for="student-id-card-switch-' + row.ID + '">' + active + '</label>';
              }
            } else {
              return active;
            }
          },
          visible: hasStudentLogin
        },
        {
          data: "Admit_Card",width:"15%",
          "render": function (data, type, row) {
            var active = data == 1 ? 'Active' : 'Inactive';
            if (row.Step == 4 && showInhouse && row.Enrollment_No.length > 0) {
              var checked = data == 1 ? 'checked' : '';
              if (role !== 'Counsellor' && role !== 'Sub-Counsellor') {
              return '<div class="form-check form-check-inline switch switch-lg success">\
                <input onclick="changeStatus(\'Students\', &#39;' + row.ID + '&#39;, \'Admit_Card\');" type="checkbox" ' + checked + ' id="student-admit-card-switch-' + row.ID + '">\
                <label for="student-admit-card-switch-' + row.ID + '">' + active + '</label>\
              </div>';}else{
                  return '<label for="student-admit-card-switch-' + row.ID + '">' + active + '</label>';
              }
            } else {
              return active;
            }
          },
          visible: hasStudentLogin
        },
        {
          data: "Exam",width:"15%",
          "render": function (data, type, row) {
            var active = data == 1 ? 'Active' : 'Inactive';
            if (row.Step == 4 && showInhouse && row.Enrollment_No.length > 0) {
              var checked = data == 1 ? 'checked' : '';
              if (role !== 'Counsellor' && role !== 'Sub-Counsellor') {
              return '<div class="form-check form-check-inline switch switch-lg success">\
                <input onclick="changeStatus(\'Students\', &#39;' + row.ID + '&#39;, \'Exam\');" type="checkbox" ' + checked + ' id="student-exam-switch-' + row.ID + '">\
                <label for="student-exam-switch-' + row.ID + '">' + active + '</label>\
              </div>';}else{
                  return '<label for="student-exam-switch-' + row.ID + '">' + active + '</label>';
              }
            } else {
              return active;
            }
          },
          visible: hasStudentLogin
        },
        {
          data: "DOB",width:"15%",
        },
        {
          data: "Center_Code",width:"15%",
        },
        {
          data: "Center_Name",width:"15%",
        },
        {
          data: "RM",width:"15%",
          visible: ['Center', 'Sub-Center','University'].includes(role) ? false : true
        }
        ],
           "sDom": "<'row pt-3 px-3 w-100 p-0 m-0'<'col-sm-6 pr-0 pl-0 custon_text_start'l><'col-sm-6 pr-0 pl-0'f>><t><'row'<p i>>",
            "destroy": true,
            "scrollCollapse": true,
            "oLanguage": {
              "sInfo": "Showing <b>_START_ to _END_</b> of _TOTAL_ entries"
            },
        drawCallback: function (settings, json) {
          $('[data-toggle="tooltip"]').tooltip();
        },
        "aaSorting": []
      };

      var readyForVerificationSettings = {
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'ajax': {
          'url': '/app/applications/ready-for-verification-server',
          'type': 'POST',
          complete: function (xhr, responseText) {
            $('#ready_for_verification_count').html(xhr.responseJSON.iTotalDisplayRecords);
          }
        },
        'columns': [
        //     {
        //   data: "ID",
        //   "render": function (data, type, row) {
        //     var edit = showInhouse || row.Step < 4 ? '<a href="/admissions/application-form?id=' + data + '"><i class="uil uil-edit mr-1" title="Edit Application Form"></i></a>' : '';
        //     var deleted = showInhouse ? '<i class="uil uil-trash mr-1 cursor-pointer" title="Delete Application Form" onclick="destroy(&#39;application-form&#39;, &#39;' + data + '&#39;)"></i>' : '';
        //      var print = row.Step == 4 ? '<i class="uil uil-print mr-1 cursor-pointer" title="Print Application Form" onclick="printForm(&#39;' + data + '&#39;)"></i>' : '';
        //     //var print  = "";
        //     var proccessedByCenter = row.Process_By_Center != 1 ? "Not Proccessed" : row.Process_By_Center
        //     var documentVerified = row.Document_Verified == 1 ? "Not Verified" : row.Document_Verified
        //     var proccessedToUniversity = row.Processed_To_University == 1 ? "Not Proccessed" : row.Processed_To_University
        //     var paymentVerified = row.Payment_Received == 1 ? "Not Verified" : row.Payment_Received
        //     var info = row.Step == 4 ? '<i class="uil uil-info-circle cursor-pointer" data-html="true" data-toggle="tooltip" data-placement="top" title="Proccessed By Center: <strong>' + proccessedByCenter + '</strong>&#013;&#010;Document Verified: <strong>' + documentVerified + '</strong>&#013;&#010;Payment Verified: <strong>' + paymentVerified + '</strong>&#013;&#010;Proccessed to University: <strong>' + proccessedToUniversity + '</strong>"></i>' : '';
        //     return print + edit + deleted + info;
        //   }
        // },
        {
         data: "ID",width:"15%",
         render: function (data, type, row) {
                console.log(row);
                if (row.Process_By_Center !== 1 && !showInhouse) {
                     var edit="";
                 }else if(showInhouse && row.Pendency == 0  ){
                     var edit = (showInhouse)
         ? '<a class="dropdown-item" href="/admissions/application-form?id=' + data + '"><i class="uil uil-edit mr-1"></i> Edit</a>'
         : '';
                 }
                 else{
         var edit = (showInhouse || row.Document_Verified == 1 )
         ? '<a class="dropdown-item" href="/admissions/application-form?id=' + data + '"><i class="uil uil-edit mr-1"></i> Edit</a>'
         : '';}
         var deleted = showInhouse
         ? '<a class="dropdown-item " href="javascript:void(0);" onclick="destroy(\'application-form\', \'" + data + "\')"><i class="uil uil-trash mr-1"></i> Delete</a>'
         : '';
         var print = row.Step == 4
         ? '<a class="dropdown-item" href="javascript:void(0);" onclick="printForm(\'' + data + '\')"><i class="uil uil-print mr-1"></i> Print</a>'
         : '';
         var proccessedByCenter = row.Process_By_Center != 1 ? "Not Proccessed" : row.Process_By_Center;
         var documentVerified = row.Document_Verified == 1 ? "Not Verified" : row.Document_Verified;
         var proccessedToUniversity = row.Processed_To_University == 1 ? "Not Proccessed" : row.Processed_To_University;
         var paymentVerified = row.Payment_Received == 1 ? "Not Verified" : row.Payment_Received;
         var info = row.Step == 4
         ? '<a class="dropdown-item" href="javascript:void(0);" data-toggle="tooltip" data-html="true" data-placement="top" title="Proccessed By Center: <strong>' + proccessedByCenter +
        '</strong><br>Document Verified: <strong>' + documentVerified +
        '</strong><br>Payment Verified: <strong>' + paymentVerified +
        '</strong><br>Proccessed to University: <strong>' + proccessedToUniversity +
        '</strong>"><i class="uil uil-info-circle mr-1"></i> Info</a>'
         : '';
        return `<div class="dropdown">
                <button class="btn btn-sm btn-light dropdown-toggle" type="button" id="dropdownMenu_${data}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                 <i class="uil uil-ellipsis-v"></i>
                </button>
               <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu_${data}">
               ${edit}${info}${print}${deleted}
               </div>
               </div>`;}},
        {
          data: "Photo",width:"15%",
          "render": function (data, type, row) {
            return '<span class="thumbnail-wrapper d48 circular inline">\
              <img src="' + data + '" alt="" data-src="' + data + '"\
                data-src-retina="' + data + '" width="32" height="32">\
            </span>';
          }
        },
        {
          data: "Unique_ID",width:"15%",
          "render": function (data, type, row) {
            return '<span class="cursor-pointer" title="Click to export documents" onclick="exportDocuments(&#39;' + row.ID + '&#39;)"><strong>' + data + '</strong></span>';
          }
        },
        {
          data: "Step",width:"15%",
          "render": function (data, type, row) {
            var label_class = data < 4 ? 'label-important' : 'label-success';
            var status = data < 4 ? 'In Draft @ Step ' + data : 'Completed';
            return '<sapn class="label ' + label_class + '">' + status + '</sapn>';
          }
        },
        {
          data: "Process_By_Center",width:"15%",
          "render": function (data, type, row) {
            if (data == 1 && row.Step == 4) {
              return '<div class="text-center"><span class="label label-danger">Not Received</span></div>';
            } else {
              var show = row.Step == 4 ? '<div class="text-center"><span class="label label-success">' + data + '</span></div>' : '';
              return show;
            }
          }
        },
        {
          data: "Document_Verified",width:"15%",
          "render": function (data, type, row) {
            if (row.Pendency_Status == 2) {
              if (!showInhouse) {
                return '<div class="text-center label label-primary"><span class="cursor-pointer"><strong>In Review</strong></span></div>'
              } else {
                return is_operations ? '<div class="text-center label label-warning"><span class="cursor-pointer" onclick="verifyDocument(&#39;' + row.ID + '&#39;)"><strong>Re-Review</strong></span></div>' : '<div class="text-center label label-warning"><span><strong>Re-Review</strong></span></div>'
              }
            } else if (row.Pendency != 0) {
              if (!showInhouse) {
                return '<div class="text-center label label-primary"><span class="cursor-pointer" onclick="uploadPendency(&#39;' + row.ID + '&#39;)"><strong>Pendency</strong></span></div>'
              } else {
                return is_operations ? '<div class="text-center label label-primary"><span class="cursor-pointer" onclick="reportPendency(&#39;' + row.ID + '&#39;)"><strong>Pendency</strong></span></div>' : '<div class="text-center label label-primary"><span><strong>Pendency</strong></span></div>'
              }
            } else {
              if (data == 1) {
                var show = is_operations && row.Process_By_Center != 1 ? '<div class="text-center label label-primary"><span class="cursor-pointer" onclick="verifyDocument(&#39;' + row.ID + '&#39;)"><strong>Review</strong></span></div>' : row.Step == 4 && row.Process_By_Center != 1 ? '<div class="text-center label label-warning"><strong>Pending</strong></div>' : '';
                return show;
              } else {
                var show = row.Step == 4 && row.Process_By_Center != 1 ? '<div class="text-center"><span class="label label-success">Verified at ' + data + '</span></div>' : '';
                return show;
              }
            }
          }
        },
        {
          data: "Payment_Received",width:"15%",
          "render": function (data, type, row) {
            if (data == 1 && row.Step == 4 && row.Process_By_Center != 1) {
              var show = is_accountant ? '<div class="text-center label label-primary"><span class="cursor-pointer" onclick="verifyPayment(&#39;' + row.ID + '&#39;)"><strong>Review</strong></span></div>' : '<center><span class="label label-warning">Pending</span></center>';
              return show;
            } else {
              var show = row.Step == 4 ? '<div class="text-center"><span class="label label-success">Verified on ' + data + '</span></div>' : '';
              return show;
            }
          },
          visible: false
        },
        {
          data: "Enrollment_No",width:"15%",
          "render": function (data, type, row) {
            var edit ="";
            if (role !== 'Counsellor' && role !== 'Sub-Counsellor') {
            var edit = showInhouse && row.Processed_To_University != 1 ? '<i class="ti ti-edit-circle add_btn_form h6 cursor-pointer p-1 ml-2" title="Add Enrollment No." onclick="addEnrollment(&#39;' + row.ID + '&#39;)">' : '';
           } return data + edit;
          }
        },
        {
          data: "OA_Number",width:"15%",
          "render": function (data, type, row) {
            var edit ="";
            if (role == 'University Head' || role == 'Counsellor' || role == 'Administartor') {
            var edit = showInhouse || is_oa_required ? '<i class="ti ti-edit-circle add_btn_form h6 cursor-pointer p-1 ml-2" title="Add OA No" onclick="addOANumber(&#39;' + row.ID + '&#39;)">' : '';
            }
            return data + edit;
          },visible:is_oa_required
        },
        {
          data: "Adm_Session",width:"15%",
        },
        {
          data: "Adm_Type",width:"15%",
        },
        {
          data: "Adm_Type",width:"15%",
          "render": function (data, type, row) {
            return '<span onclick="reportPendnency(' + row.ID + ')"><strong>Report</strong><span>';
          },
          visible: false,
        },
        {
          data: "First_Name",width:"15%",
          "render": function (data, type, row) {
            return '<strong>' + data + '</strong>';
          }
        },
        {
          data: "Father_Name",width:"15%",
        },
        {
          data: "Short_Name",width:"15%",
        },
        {
          data: "Duration",width:"15%",
        },
        {
          data: "Status",width:"15%",
          "render": function (data, type, row) {
            var active = data == 1 ? 'Active' : 'Inactive';
            if (row.Step == 4 && showInhouse) {
              var checked = data == 1 ? 'checked' : '';
              if (role !== 'Counsellor' && role !== 'Sub-Counsellor') {
              return '<div class="form-check form-check-inline switch switch-lg success">\
                <input onclick="changeStatus(\'Students\', &#39;' + row.ID + '&#39;);" type="checkbox" ' + checked + ' id="student-status-switch-' + row.ID + '">\
                <label for="student-status-switch-' + row.ID + '">' + active + '</label>\
              </div>';}else{
                  return '<label for="student-status-switch-' + row.ID + '">' + active + '</label>';
              }
            } else {
              return active;
            }
          },
          visible: hasStudentLogin || showStatus
        },
          {
          data: "Contact",width:"15%",
        },
        {
          data: "ID_Card",width:"15%",
          "render": function (data, type, row) {
            var active = data == 1 ? 'Active' : 'Inactive';
            if (row.Step == 4 && showInhouse && row.Enrollment_No.length > 0) {
              var checked = data == 1 ? 'checked' : '';
              if (role !== 'Counsellor' && role !== 'Sub-Counsellor') {
              return '<div class="form-check form-check-inline switch switch-lg success">\
                <input onclick="changeStatus(\'Students\', &#39;' + row.ID + '&#39;, \'ID_Card\');" type="checkbox" ' + checked + ' id="student-id-card-switch-' + row.ID + '">\
                <label for="student-id-card-switch-' + row.ID + '">' + active + '</label>\
              </div>';}else{
                  return '<label for="student-id-card-switch-' + row.ID + '">' + active + '</label>';
              }
            } else {
              return active;
            }
          },
          visible: hasStudentLogin
        },
        {
          data: "Admit_Card",width:"15%",
          "render": function (data, type, row) {
            var active = data == 1 ? 'Active' : 'Inactive';
            if (row.Step == 4 && showInhouse && row.Enrollment_No.length > 0) {
              var checked = data == 1 ? 'checked' : '';
              if (role !== 'Counsellor' && role !== 'Sub-Counsellor') {
              return '<div class="form-check form-check-inline switch switch-lg success">\
                <input onclick="changeStatus(\'Students\', &#39;' + row.ID + '&#39;, \'Admit_Card\');" type="checkbox" ' + checked + ' id="student-admit-card-switch-' + row.ID + '">\
                <label for="student-admit-card-switch-' + row.ID + '">' + active + '</label>\
              </div>';}else{
                  return '<label for="student-admit-card-switch-' + row.ID + '">' + active + '</label>';
              }
            } else {
              return active;
            }
          },
          visible: hasStudentLogin
        },
        {
          data: "Exam",width:"15%",
          "render": function (data, type, row) {
            var active = data == 1 ? 'Active' : 'Inactive';
            if (row.Step == 4 && showInhouse && row.Enrollment_No.length > 0) {
              var checked = data == 1 ? 'checked' : '';
              if (role !== 'Counsellor' && role !== 'Sub-Counsellor') {
              return '<div class="form-check form-check-inline switch switch-lg success">\
                <input onclick="changeStatus(\'Students\', &#39;' + row.ID + '&#39;, \'Exam\');" type="checkbox" ' + checked + ' id="student-exam-switch-' + row.ID + '">\
                <label for="student-exam-switch-' + row.ID + '">' + active + '</label>\
              </div>';}else{
                 return '<label for="student-exam-switch-' + row.ID + '">' + active + '</label>'; 
              }
            } else {
              return active;
            }
          },
          visible: hasStudentLogin
        },
        {
          data: "DOB",width:"15%",
        },
        {
          data: "Center_Code",width:"15%",
        },
        {
          data: "Center_Name",width:"15%",
        },
        {
          data: "RM",width:"15%",
          visible: ['Center', 'Sub-Center','University'].includes(role) ? false : true
        }
        ],
         "sDom": "<'row pt-3 px-3 w-100 p-0 m-0'<'col-sm-6 pr-0 pl-0 custon_text_start'l><'col-sm-6 pr-0 pl-0'f>><t><'row'<p i>>",
            "destroy": true,
            "scrollCollapse": true,
            "oLanguage": {
              "sInfo": "Showing <b>_START_ to _END_</b> of _TOTAL_ entries"
            },
        drawCallback: function (settings, json) {
          $('[data-toggle="tooltip"]').tooltip();
        },
        "aaSorting": []
      };

      var verifiedSettings = {
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'ajax': {
          'url': '/app/applications/verified-server',
          'type': 'POST',
          complete: function (xhr, responseText) {
            $('#verified_count').html(xhr.responseJSON.iTotalDisplayRecords);
          }
        },
        'columns': [
        //     {
        //   data: "ID",
        //   "render": function (data, type, row) {
        //     var edit = showInhouse || row.Step < 4 ? '<a href="/admissions/application-form?id=' + data + '"><i class="uil uil-edit mr-1" title="Edit Application Form"></i></a>' : '';
        //     var deleted = showInhouse ? '<i class="uil uil-trash mr-1 cursor-pointer" title="Delete Application Form" onclick="destroy(&#39;application-form&#39;, &#39;' + data + '&#39;)"></i>' : '';
        //     var print = row.Step == 4 ? '<i class="uil uil-print mr-1 cursor-pointer" title="Print Application Form" onclick="printForm(&#39;' + data + '&#39;)"></i>' : '';
        //     //var print  = "";
        //     var proccessedByCenter = row.Process_By_Center != 1 ? "Not Proccessed" : row.Process_By_Center
        //     var documentVerified = row.Document_Verified == 1 ? "Not Verified" : row.Document_Verified
        //     var proccessedToUniversity = row.Processed_To_University == 1 ? "Not Proccessed" : row.Processed_To_University
        //     var paymentVerified = row.Payment_Received == 1 ? "Not Verified" : row.Payment_Received
        //     var info = row.Step == 4 ? '<i class="uil uil-info-circle cursor-pointer" data-html="true" data-toggle="tooltip" data-placement="top" title="Proccessed By Center: <strong>' + proccessedByCenter + '</strong>&#013;&#010;Document Verified: <strong>' + documentVerified + '</strong>&#013;&#010;Payment Verified: <strong>' + paymentVerified + '</strong>&#013;&#010;Proccessed to University: <strong>' + proccessedToUniversity + '</strong>"></i>' : '';
        //     return print + edit + deleted + info;
        //   }
        // },
       {
  data: "ID",width:"15%",
  render: function (data, type, row) {
         if (row.Process_By_Center !== 1) {
                     var edit="";
                 }else{
    var edit = (showInhouse || row.Document_Verified == 1 )
      ? '<a class="dropdown-item" href="/admissions/application-form?id=' + data + '"><i class="uil uil-edit mr-1"></i> Edit</a>'
      : '';}

    var deleted = showInhouse
      ? '<a class="dropdown-item text-danger" href="javascript:void(0);"  onclick="destroy(\'application-form\', \'" + data + "\')"><i class="uil uil-trash mr-1"></i> Delete</a>'
      : '';

    var print = row.Step == 4
      ? '<a class="dropdown-item" href="javascript:void(0);" onclick="printForm(\'' + data + '\')"><i class="uil uil-print mr-1"></i> Print</a>'
      : '';

    var proccessedByCenter = row.Process_By_Center != 1 ? "Not Proccessed" : row.Process_By_Center;
    var documentVerified = row.Document_Verified == 1 ? "Not Verified" : row.Document_Verified;
    var proccessedToUniversity = row.Processed_To_University == 1 ? "Not Proccessed" : row.Processed_To_University;
    var paymentVerified = row.Payment_Received == 1 ? "Not Verified" : row.Payment_Received;

    var info = row.Step == 4
      ? '<a class="dropdown-item" href="javascript:void(0);" data-toggle="tooltip" data-html="true" data-placement="top" title="Proccessed By Center: <strong>' + proccessedByCenter +'</strong><br>Document Verified: <strong>' + documentVerified +'</strong><br>Payment Verified: <strong>' + paymentVerified +'</strong><br>Proccessed to University: <strong>' + proccessedToUniversity +'</strong>"><i class="uil uil-info-circle mr-1"></i> Info</a>': '';

    return `
      <div class="dropdown">
        <button class="btn btn-sm btn-light dropdown-toggle" type="button" id="dropdownMenu_${data}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <i class="uil uil-ellipsis-v"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu_${data}">
          ${edit}${info}${print}${deleted}
        </div>
      </div>
    `;
  }
},
        {
          data: "Photo",width:"15%",
          "render": function (data, type, row) {
            return '<span class="thumbnail-wrapper d48 circular inline">\
              <img src="' + data + '" alt="" data-src="' + data + '"\
                data-src-retina="' + data + '" width="32" height="32">\
            </span>';
          }
        },
        {
          data: "Unique_ID",width:"15%",
          "render": function (data, type, row) {
            return '<span class="cursor-pointer" title="Click to export documents" onclick="exportDocuments(&#39;' + row.ID + '&#39;)"><strong>' + data + '</strong></span>';
          }
        },
        {
          data: "Step",width:"15%",
          "render": function (data, type, row) {
            var label_class = data < 4 ? 'label-important' : 'label-success';
            var status = data < 4 ? 'In Draft @ Step ' + data : 'Completed';
            return '<sapn class="label ' + label_class + '">' + status + '</sapn>';
          }
        },
        {
          data: "Process_By_Center",width:"15%",
          "render": function (data, type, row) {
            if (data == 1 && row.Step == 4) {
              return '<div class="text-center"><span class="label label-danger">Not Received</span></div>';
            } else {
              var show = row.Step == 4 ? '<div class="text-center"><span class="label label-success">' + data + '</span></div>' : '';
              return show;
            }
          }
        },
        {
          data: "Document_Verified",width:"15%",
          "render": function (data, type, row) {
            if (row.Pendency_Status == 2) {
              if (!showInhouse) {
                return '<div class="text-center label label-primary"><span class="cursor-pointer"><strong>In Review</strong></span></div>'
              } else {
                return is_operations ? '<div class="text-center label label-warning"><span class="cursor-pointer" onclick="verifyDocument(&#39;' + row.ID + '&#39;)"><strong>Re-Review</strong></span></div>' : '<div class="text-center label label-warning"><span><strong>Re-Review</strong></span></div>'
              }
            } else if (row.Pendency != 0) {
              if (!showInhouse) {
                return '<div class="text-center label label-primary"><span class="cursor-pointer" onclick="uploadPendency(&#39;' + row.ID + '&#39;)"><strong>Pendency</strong></span></div>'
              } else {
                return is_operations ? '<div class="text-center label label-primary"><span class="cursor-pointer" onclick="reportPendency(&#39;' + row.ID + '&#39;)"><strong>Pendency</strong></span></div>' : '<div class="text-center label label-primary"><span><strong>Pendency</strong></span></div>'
              }
            } else {
              if (data == 1) {
                var show = is_operations && row.Process_By_Center != 1 ? '<div class="text-center label label-primary"><span class="cursor-pointer" onclick="verifyDocument(&#39;' + row.ID + '&#39;)"><strong>Review</strong></span></div>' : row.Step == 4 && row.Process_By_Center != 1 ? '<div class="text-center label label-warning"><strong>Pending</strong></div>' : '';
                return show;
              } else {
                var show = row.Step == 4 && row.Process_By_Center != 1 ? '<div class="text-center"><span class="label label-success">Verified at ' + data + '</span></div>' : '';
                return show;
              }
            }
          }
        },
        {
          data: "Payment_Received",width:"15%",
          "render": function (data, type, row) {
            if (data == 1 && row.Step == 4 && row.Process_By_Center != 1) {
              var show = is_accountant ? '<div class="text-center"><span class="cursor-pointer" onclick="verifyPayment(&#39;' + row.ID + '&#39;)"><strong>Review</strong></span></div>' : '<center><span class="label label-primary">Pending</span></center>';
              return show;
            } else {
              var show = row.Step == 4 ? '<div class="text-center label label-success"><span class="label label-primary">Verified on ' + data + '</span></div>' : '';
              return show;
            }
          },
          visible: false
        },
        {
          data: "Processed_To_University",width:"15%",
          "render": function (data, type, row) {
            if (data == 1) {
              var show = showInhouse && row.Document_Verified != 1 && row.Payment_Received != 1 ? '<div class="form-check complete mt-2">\
                <input type="checkbox" id="processed-to-university-' + row.ID + '" onclick="processedToUniversity(&#39;' + row.ID + '&#39;)">\
                <label for="processed-to-university-' + row.ID + '">Mark as Processed</label>\
              </div>' : "";
              return show;
            } else {
              var show = row.Step == 4 ? '<div class="text-center"><span class="label label-success">Processed on ' + data + '</span></div>' : '';
              return show;
            }
          },
          visible: false
        },
        {
          data: "Enrollment_No",width:"15%",
          "render": function (data, type, row) {
            var edit = "";
            if (role !== 'Counsellor' && role !== 'Sub-Counsellor') {
            var edit = showInhouse && row.Processed_To_University != 1 ? '<i class="ti ti-edit-circle add_btn_form h6 cursor-pointer p-1 ml-2" title="Add Enrollment No." onclick="addEnrollment(&#39;' + row.ID + '&#39;)">' : '';
            }
            return data + edit;
          }
        },
        {
          data: "OA_Number",width:"15%",
          "render": function (data, type, row) {
            var edit = "";
            if (role == 'University Head' || role == 'Counsellor' || role == 'Administartor') {
            var edit = showInhouse || is_oa_required ? '<i class="ti ti-edit-circle add_btn_form h6 cursor-pointer p-1 ml-2" title="Add OA No" onclick="addOANumber(&#39;' + row.ID + '&#39;)">' : '';
            }
            return data + edit;
          },visible:is_oa_required
        },
        {
          data: "Adm_Session",width:"15%",
        },
        {
          data: "Adm_Type",width:"15%",
        },
        {
          data: "Adm_Type",width:"15%",
          "render": function (data, type, row) {
            return '<span onclick="reportPendnency(' + row.ID + ')"><strong>Report</strong><span>';
          },
          visible: false,
        },
        {
          data: "First_Name",width:"15%",
          "render": function (data, type, row) {
            return '<strong>' + data + '</strong>';
          }
        },
        {
          data: "Father_Name",width:"15%",
        },
        {
          data: "Short_Name",width:"15%",
        },
        {
          data: "Duration",width:"15%",
        },
        {
          data: "Status",width:"15%",
          "render": function (data, type, row) {
            var active = data == 1 ? 'Active' : 'Inactive';
            if (row.Step == 4 && showInhouse) {
              var checked = data == 1 ? 'checked' : '';
              if (role !== 'Counsellor' && role !== 'Sub-Counsellor') {
              return '<div class="form-check form-check-inline switch switch-lg success">\
                <input onclick="changeStatus(\'Students\', &#39;' + row.ID + '&#39;);" type="checkbox" ' + checked + ' id="student-status-switch-' + row.ID + '">\
                <label for="student-status-switch-' + row.ID + '">' + active + '</label>\
              </div>';}else{
              return '<label for="student-status-switch-' + row.ID + '">' + active + '</label>';   
              }
            } else {
              return active;
            }
          },
          visible: hasStudentLogin || showStatus
        },
          {
          data: "Contact",width:"15%",
        },
        {
          data: "ID_Card",width:"15%",
          "render": function (data, type, row) {
            var active = data == 1 ? 'Active' : 'Inactive';
            if (row.Step == 4 && showInhouse && row.Enrollment_No.length > 0) {
              var checked = data == 1 ? 'checked' : '';
              if (role !== 'Counsellor' && role !== 'Sub-Counsellor') {
              return '<div class="form-check form-check-inline switch switch-lg success">\
                <input onclick="changeStatus(\'Students\', &#39;' + row.ID + '&#39;, \'ID_Card\');" type="checkbox" ' + checked + ' id="student-id-card-switch-' + row.ID + '">\
                <label for="student-id-card-switch-' + row.ID + '">' + active + '</label>\
              </div>';}else{
               return '<label for="student-id-card-switch-' + row.ID + '">' + active + '</label>'  
              }
            } else {
              return active;
            }
          },
          visible: hasStudentLogin
        },
        {
          data: "Admit_Card",width:"15%",
          "render": function (data, type, row) {
            var active = data == 1 ? 'Active' : 'Inactive';
            if (row.Step == 4 && showInhouse && row.Enrollment_No.length > 0) {
              var checked = data == 1 ? 'checked' : '';
               if (role !== 'Counsellor' && role !== 'Sub-Counsellor') {
              return '<div class="form-check form-check-inline switch switch-lg success">\
                <input onclick="changeStatus(\'Students\', &#39;' + row.ID + '&#39;, \'Admit_Card\');" type="checkbox" ' + checked + ' id="student-admit-card-switch-' + row.ID + '">\
                <label for="student-admit-card-switch-' + row.ID + '">' + active + '</label>\
              </div>';}else{
                return '<label for="student-admit-card-switch-' + row.ID + '">' + active + '</label>'; 
              }
            } else {
              return active;
            }
          },
          visible: hasStudentLogin
        },
        {
          data: "Exam",width:"15%",
          "render": function (data, type, row) {
            var active = data == 1 ? 'Active' : 'Inactive';
            if (row.Step == 4 && showInhouse && row.Enrollment_No.length > 0) {
              var checked = data == 1 ? 'checked' : '';
               if (role !== 'Counsellor' && role !== 'Sub-Counsellor') {
              return '<div class="form-check form-check-inline switch switch-lg success">\
                <input onclick="changeStatus(\'Students\', &#39;' + row.ID + '&#39;, \'Exam\');" type="checkbox" ' + checked + ' id="student-exam-switch-' + row.ID + '">\
                <label for="student-exam-switch-' + row.ID + '">' + active + '</label>\
              </div>';}else{
                  return '<label for="student-exam-switch-' + row.ID + '">' + active + '</label>';
              }
            } else {
              return active;
            }
          },
          visible: hasStudentLogin
        },
        {
          data: "DOB",width:"15%",
        },
        {
          data: "Center_Code",width:"15%",
        },
        {
          data: "Center_Name",width:"15%",
        },
        {
          data: "RM",width:"15%",
          visible: ['Center', 'Sub-Center','University'].includes(role) ? false : true
        }
        ],
        "sDom": "<'row pt-3 px-3 w-100 p-0 m-0'<'col-sm-6 pr-0 pl-0 custon_text_start'l><'col-sm-6 pr-0 pl-0'f>><t><'row'<p i>>",
            "destroy": true,
            "scrollCollapse": true,
            "oLanguage": {
              "sInfo": "Showing <b>_START_ to _END_</b> of _TOTAL_ entries"
            },
        drawCallback: function (settings, json) {
          $('[data-toggle="tooltip"]').tooltip();
        },
        "aaSorting": []
      };

      var processedToUniversitySettings = {
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'ajax': {
          'url': '/app/applications/processed-to-university-server',
          'type': 'POST',
          complete: function (xhr, responseText) {
            $('#processed_to_university_count').html(xhr.responseJSON.iTotalDisplayRecords);
          }
        },
        'columns': [
        // {
        //   data: "ID",
        //   "render": function (data, type, row) {
        //     var edit = showInhouse || row.Step < 4 ? '<a href="/admissions/application-form?id=' + data + '"><i class="uil uil-edit mr-1" title="Edit Application Form"></i></a>' : '';
        //     var deleted = showInhouse ? '<i class="uil uil-trash mr-1 cursor-pointer" title="Delete Application Form" onclick="destroy(&#39;application-form&#39;, &#39;' + data + '&#39;)"></i>' : '';
        //     var print = row.Step == 4 ? '<i class="uil uil-print mr-1 cursor-pointer" title="Print Application Form" onclick="printForm(&#39;' + data + '&#39;)"></i>' : '';
        //     //var print  = "";
        //     var proccessedByCenter = row.Process_By_Center != 1 ? "Not Proccessed" : row.Process_By_Center
        //     var documentVerified = row.Document_Verified == 1 ? "Not Verified" : row.Document_Verified
        //     var proccessedToUniversity = row.Processed_To_University == 1 ? "Not Proccessed" : row.Processed_To_University
        //     var paymentVerified = row.Payment_Received == 1 ? "Not Verified" : row.Payment_Received
        //     var info = row.Step == 4 ? '<i class="uil uil-info-circle cursor-pointer" data-html="true" data-toggle="tooltip" data-placement="top" title="Proccessed By Center: <strong>' + proccessedByCenter + '</strong>&#013;&#010;Document Verified: <strong>' + documentVerified + '</strong>&#013;&#010;Payment Verified: <strong>' + paymentVerified + '</strong>&#013;&#010;Proccessed to University: <strong>' + proccessedToUniversity + '</strong>"></i>' : '';
        //     return print + edit + deleted + info;
        //   }
        // },
         {
         data: "ID",width:"15%",
         render: function (data, type, row) {
                if (row.Process_By_Center !== 1) {
                     var edit="";
                 }else{
         var edit = (showInhouse || row.Document_Verified == 1 )
         ? '<a class="dropdown-item" href="/admissions/application-form?id=' + data + '"><i class="uil uil-edit mr-1"></i> Edit</a>'
         : '';}
         var deleted = showInhouse
         ? '<a class="dropdown-item " href="javascript:void(0);" onclick="destroy(\'application-form\', \'" + data + "\')"><i class="uil uil-trash mr-1"></i> Delete</a>'
         : '';
         var print = row.Step == 4
         ? '<a class="dropdown-item" href="javascript:void(0);" onclick="printForm(\'' + data + '\')"><i class="uil uil-print mr-1"></i> Print</a>'
         : '';
         var proccessedByCenter = row.Process_By_Center != 1 ? "Not Proccessed" : row.Process_By_Center;
         var documentVerified = row.Document_Verified == 1 ? "Not Verified" : row.Document_Verified;
         var proccessedToUniversity = row.Processed_To_University == 1 ? "Not Proccessed" : row.Processed_To_University;
         var paymentVerified = row.Payment_Received == 1 ? "Not Verified" : row.Payment_Received;
         var info = row.Step == 4
         ? '<a class="dropdown-item" href="javascript:void(0);" data-toggle="tooltip" data-html="true" data-placement="top" title="Proccessed By Center: <strong>' + proccessedByCenter +
         '</strong><br>Document Verified: <strong>' + documentVerified +
        '</strong><br>Payment Verified: <strong>' + paymentVerified +
        '</strong><br>Proccessed to University: <strong>' + proccessedToUniversity +
        '</strong>"><i class="uil uil-info-circle mr-1"></i> Info</a>'
        : '';
        return `
        <div class="dropdown">
        <button class="btn btn-sm btn-light dropdown-toggle" type="button" id="dropdownMenu_${data}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <i class="uil uil-ellipsis-v"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu_${data}">
          ${edit}${info}${print}${deleted}
        </div>
      </div>
       `;
        }
        },
        {
          data: "Photo",width:"15%",
          "render": function (data, type, row) {
            return '<span class="thumbnail-wrapper d48 circular inline">\
              <img src="' + data + '" alt="" data-src="' + data + '"\
                data-src-retina="' + data + '" width="32" height="32">\
            </span>';
          }
        },
        {
          data: "Unique_ID",width:"15%",
          "render": function (data, type, row) {
            return '<span class="cursor-pointer" title="Click to export documents" onclick="exportDocuments(&#39;' + row.ID + '&#39;)"><strong>' + data + '</strong></span>';
          }
        },
        {
          data: "Step",width:"15%",
          "render": function (data, type, row) {
            var label_class = data < 4 ? 'label-important' : 'label-success';
            var status = data < 4 ? 'In Draft @ Step ' + data : 'Completed';
            return '<sapn class="label ' + label_class + '">' + status + '</sapn>';
          }
        },
        {
          data: "Process_By_Center",width:"15%",
          "render": function (data, type, row) {
            if (data == 1 && row.Step == 4) {
              return '<div class="text-center"><span class="label label-danger">Not Received</span></div>';
            } else {
              var show = row.Step == 4 ? '<div class="text-center"><span class="label label-success">' + data + '</span></div>' : '';
              return show;
            }
          }
        },
        {
          data: "Document_Verified",width:"15%",
          "render": function (data, type, row) {
            if (row.Pendency_Status == 2) {
              if (!showInhouse) {
                return '<div class="text-center label label-primary"><span class="cursor-pointer"><strong>In Review</strong></span></div>'
              } else {
                return is_operations ? '<div class="text-center label label-warning"><span class="cursor-pointer" onclick="verifyDocument(&#39;' + row.ID + '&#39;)"><strong>Re-Review</strong></span></div>' : '<div class="text-center label label-warning"><span><strong>Re-Review</strong></span></div>'
              }
            } else if (row.Pendency != 0) {
              if (!showInhouse) {
                return '<div class="text-center label label-primary"><span class="cursor-pointer" onclick="uploadPendency(&#39;' + row.ID + '&#39;)"><strong>Pendency</strong></span></div>'
              } else {
                return is_operations ? '<div class="text-center label label-primary"><span class="cursor-pointer" onclick="reportPendency(&#39;' + row.ID + '&#39;)"><strong>Pendency</strong></span></div>' : '<div class="text-center label label-primary"><span><strong>Pendency</strong></span></div>'
              }
            } else {
              if (data == 1) {
                var show = is_operations && row.Process_By_Center != 1 ? '<div class="text-center"><span class="cursor-pointer" onclick="verifyDocument(&#39;' + row.ID + '&#39;)"><strong>Review</strong></span></div>' : row.Step == 4 && row.Process_By_Center != 1 ? '<div class="text-center label label-warning"><strong>Pending</strong></div>' : '';
                return show;
              } else {
                var show = row.Step == 4 && row.Process_By_Center != 1 ? '<div class="text-center"><span class="label label-success">Verified at ' + data + '</span></div>' : '';
                return show;
              }
            }
          }
        },
        {
          data: "Payment_Received",width:"15%",
          "render": function (data, type, row) {
            if (data == 1 && row.Step == 4 && row.Process_By_Center != 1) {
              var show = is_accountant ? '<div class="text-center"><span class="cursor-pointer" onclick="verifyPayment(&#39;' + row.ID + '&#39;)"><strong>Review</strong></span></div>' : '<center><span class="label label-warning">Pending</span></center>';
              return show;
            } else if (row.Process_By_Center != 1) {
              var show = row.Step == 4 ? '<div class="text-center"><span class="label label-success">Verified on ' + data + '</span></div>' : '';
              return show;
            } else {
              return '';
            }
          },
          visible: false
        },
        {
          data: "Processed_To_University",width:"15%",
          "render": function (data, type, row) {
            if (data == 1) {
              var show = showInhouse && row.Document_Verified != 1 && row.Payment_Received != 1 ? '<div class="form-check complete mt-2">\
                <input type="checkbox" id="processed-to-university-' + row.ID + '" onclick="processedToUniversity(&#39;' + row.ID + '&#39;)">\
                <label for="processed-to-university-' + row.ID + '">Mark as Processed</label>\
              </div>' : "";
              return show;
            } else {
              var show = row.Step == 4 ? '<div class="text-center"><span class="label label-success">Processed on ' + data + '</span></div>' : '';
              return show;
            }
          },
          visible: false
        },
        {
          data: "Enrollment_No",width:"15%",
          "render": function (data, type, row) {
            var edit="";
            if (role !== 'Counsellor' && role !== 'Sub-Counsellor') {
            var edit = showInhouse && row.Processed_To_University != 1 ? '<i class="ti ti-edit-circle add_btn_form h6 cursor-pointer p-1 ml-2" title="Add Enrollment No." onclick="addEnrollment(&#39;' + row.ID + '&#39;)">' : '';
            }
            return data + edit;
          }
        },
        {
          data: "OA_Number",width:"15%",
         
          
          "render": function (data, type, row) {
                var edit="";
              if (role == 'University Head' || role == 'Counsellor' || role == 'Administartor') {
            var edit = showInhouse || is_oa_required ? '<i class="ti ti-edit-circle add_btn_form h6 cursor-pointer p-1 ml-2" title="Add OA No" onclick="addOANumber(&#39;' + row.ID + '&#39;)">' : '';
          } 
           return data + edit;
          
               
           },visible:is_oa_required
        },
        {
          data: "Adm_Session",width:"15%",
        },
        {
          data: "Adm_Type",width:"15%",
        },
        {
          data: "Adm_Type",width:"15%",
          "render": function (data, type, row) {
            return '<span onclick="reportPendnency(' + row.ID + ')"><strong>Report</strong><span>';
          },
          visible: false,
        },
        {
          data: "First_Name",width:"15%",
          "render": function (data, type, row) {
            return '<strong>' + data + '</strong>';
          }
        },
        {
          data: "Father_Name",width:"15%",
        },
        {
          data: "Short_Name",width:"15%",
        },
        {
          data: "Duration",width:"15%",
        },
        {
          data: "Status",width:"15%",
          "render": function (data, type, row) {
            var active = data == 1 ? 'Active' : 'Inactive';
            if (row.Step == 4 && showInhouse) {
              var checked = data == 1 ? 'checked' : '';
              if (role !== 'Counsellor' && role !== 'Sub-Counsellor') {
              return '<div class="form-check form-check-inline switch switch-lg success">\
                <input onclick="changeStatus(\'Students\', &#39;' + row.ID + '&#39;);" type="checkbox" ' + checked + ' id="student-status-switch-' + row.ID + '">\
                <label for="student-status-switch-' + row.ID + '">' + active + '</label>\
              </div>';}else{
                  return '<label for="student-status-switch-' + row.ID + '">' + active + '</label>';
              }
            } else {
              return active;
            }
          },
          visible: hasStudentLogin || showStatus
        },
        {
          data: "ID_Card",width:"15%",
          "render": function (data, type, row) {
            var active = data == 1 ? 'Active' : 'Inactive';
            if (row.Step == 4 && showInhouse && row.Enrollment_No.length > 0) {
              var checked = data == 1 ? 'checked' : '';
               if (role !== 'Counsellor' && role !== 'Sub-Counsellor') {
              return '<div class="form-check form-check-inline switch switch-lg success">\
                <input onclick="changeStatus(\'Students\', &#39;' + row.ID + '&#39;, \'ID_Card\');" type="checkbox" ' + checked + ' id="student-id-card-switch-' + row.ID + '">\
                <label for="student-id-card-switch-' + row.ID + '">' + active + '</label>\
              </div>';}else{
                 return  '<label for="student-id-card-switch-' + row.ID + '">' + active + '</label>'; 
              }
            } else {
              return active;
            }
          },
          visible: hasStudentLogin
        },
          {
          data: "Contact",width:"15%",
        },
        {
          data: "Admit_Card",width:"15%",
          "render": function (data, type, row) {
            var active = data == 1 ? 'Active' : 'Inactive';
            if (row.Step == 4 && showInhouse && row.Enrollment_No.length > 0) {
              var checked = data == 1 ? 'checked' : '';
              if (role !== 'Counsellor' && role !== 'Sub-Counsellor') {
              return '<div class="form-check form-check-inline switch switch-lg success">\
                <input onclick="changeStatus(\'Students\', &#39;' + row.ID + '&#39;, \'Admit_Card\');" type="checkbox" ' + checked + ' id="student-admit-card-switch-' + row.ID + '">\
                <label for="student-admit-card-switch-' + row.ID + '">' + active + '</label>\
              </div>';}else{
                return '<label for="student-admit-card-switch-' + row.ID + '">' + active + '</label>';  
              }
            } else {
              return active;
            }
          },
          visible: hasStudentLogin
        },
        {
          data: "Exam",width:"15%",
          "render": function (data, type, row) {
            var active = data == 1 ? 'Active' : 'Inactive';
            if (row.Step == 4 && showInhouse && row.Enrollment_No.length > 0) {
              var checked = data == 1 ? 'checked' : '';
              if (role !== 'Counsellor' && role !== 'Sub-Counsellor') {
              return '<div class="form-check form-check-inline switch switch-lg success">\
                <input onclick="changeStatus(\'Students\', &#39;' + row.ID + '&#39;, \'Exam\');" type="checkbox" ' + checked + ' id="student-exam-switch-' + row.ID + '">\
                <label for="student-exam-switch-' + row.ID + '">' + active + '</label>\
              </div>';}else{
                  return '<label for="student-exam-switch-' + row.ID + '">' + active + '</label>';
              }
            } else {
              return active;
            }
          },
          visible: hasStudentLogin
        },
        {
          data: "DOB",width:"15%",
        },
        {
          data: "Center_Code",width:"15%",
        },
        {
          data: "Center_Name",width:"15%",
        },
        {
          data: "RM",width:"15%",
          visible: ['Center', 'Sub-Center','University'].includes(role) ? false : true
        }
        ],
         "sDom": "<'row pt-3 px-3 w-100 p-0 m-0'<'col-sm-6 pr-0 pl-0 custon_text_start'l><'col-sm-6 pr-0 pl-0'f>><t><'row'<p i>>",
            "destroy": true,
            "scrollCollapse": true,
            "oLanguage": {
              "sInfo": "Showing <b>_START_ to _END_</b> of _TOTAL_ entries"
            },
        drawCallback: function (settings, json) {
          $('[data-toggle="tooltip"]').tooltip();
        },
        "aaSorting": []
      };

      var enrolledSettings = {
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'ajax': {
          'url': '/app/applications/enrolled-server',
          'type': 'POST',
          complete: function (xhr, responseText) {
            $('#enrolled_count').html(xhr.responseJSON.iTotalDisplayRecords);
          }
        },
        'columns': [
        //     {
        //   data: "ID",
        //   "render": function (data, type, row) {
        //     var edit = showInhouse || row.Step < 4 ? '<a href="/admissions/application-form?id=' + data + '"><i class="uil uil-edit mr-1" title="Edit Application Form"></i></a>' : '';
        //     var deleted = showInhouse ? '<i class="uil uil-trash mr-1 cursor-pointer" title="Delete Application Form" onclick="destroy(&#39;application-form&#39;, &#39;' + data + '&#39;)"></i>' : '';
        //     var print = row.Step == 4 ? '<i class="uil uil-print mr-1 cursor-pointer" title="Print Application Form" onclick="printForm(&#39;' + data + '&#39;)"></i>' : '';
        //     //var print  = "";
        //     var proccessedByCenter = row.Process_By_Center != 1 ? "Not Proccessed" : row.Process_By_Center
        //     var documentVerified = row.Document_Verified == 1 ? "Not Verified" : row.Document_Verified
        //     var proccessedToUniversity = row.Processed_To_University == 1 ? "Not Proccessed" : row.Processed_To_University
        //     var paymentVerified = row.Payment_Received == 1 ? "Not Verified" : row.Payment_Received
        //     var info = row.Step == 4 ? '<i class="uil uil-info-circle cursor-pointer" data-html="true" data-toggle="tooltip" data-placement="top" title="Proccessed By Center: <strong>' + proccessedByCenter + '</strong>&#013;&#010;Document Verified: <strong>' + documentVerified + '</strong>&#013;&#010;Payment Verified: <strong>' + paymentVerified + '</strong>&#013;&#010;Proccessed to University: <strong>' + proccessedToUniversity + '</strong>"></i>' : '';
        //     return print + edit + deleted + info;
        //   }
        // },
        {
         data: "ID",width:"15%",
         render: function (data, type, row) {
                if (row.Process_By_Center !== 1) {
                     var edit="";
                 }else{
         var edit = (showInhouse || row.Document_Verified == 1 )
         ? '<a class="dropdown-item" href="/admissions/application-form?id=' + data + '"><i class="uil uil-edit mr-1"></i> Edit</a>'
         : '';}
         var deleted = showInhouse
         ? '<a class="dropdown-item " href="javascript:void(0);" onclick="destroy(\'application-form\', \'" + data + "\')"><i class="uil uil-trash mr-1"></i> Delete</a>'
         : '';
         var print = row.Step == 4
         ? '<a class="dropdown-item" href="javascript:void(0);" onclick="printForm(\'' + data + '\')"><i class="uil uil-print mr-1"></i> Print</a>'
         : '';
         var proccessedByCenter = row.Process_By_Center != 1 ? "Not Proccessed" : row.Process_By_Center;
         var documentVerified = row.Document_Verified == 1 ? "Not Verified" : row.Document_Verified;
         var proccessedToUniversity = row.Processed_To_University == 1 ? "Not Proccessed" : row.Processed_To_University;
         var paymentVerified = row.Payment_Received == 1 ? "Not Verified" : row.Payment_Received;
         var info = row.Step == 4
         ? '<a class="dropdown-item" href="javascript:void(0);" data-toggle="tooltip" data-html="true" data-placement="top" title="Proccessed By Center: <strong>' + proccessedByCenter +
         '</strong><br>Document Verified: <strong>' + documentVerified +
        '</strong><br>Payment Verified: <strong>' + paymentVerified +
        '</strong><br>Proccessed to University: <strong>' + proccessedToUniversity +
        '</strong>"><i class="uil uil-info-circle mr-1"></i> Info</a>'
        : '';
        return `
        <div class="dropdown">
        <button class="btn btn-sm btn-light dropdown-toggle" type="button" id="dropdownMenu_${data}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="uil uil-ellipsis-v"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu_${data}">
          ${edit}${info}${print}${deleted}
        </div>
        </div>
         `;
         }
         },
        {
          data: "Photo",width:"15%",
          "render": function (data, type, row) {
            return '<span class="thumbnail-wrapper d48 circular inline">\
              <img src="' + data + '" alt="" data-src="' + data + '"\
                data-src-retina="' + data + '" width="32" height="32">\
            </span>';
          }
        },
        {
          data: "Unique_ID",width:"15%",
          "render": function (data, type, row) {
            return '<span class="cursor-pointer" title="Click to export documents" onclick="exportDocuments(&#39;' + row.ID + '&#39;)"><strong>' + data + '</strong></span>';
          }
        },
        {
          data: "Step",width:"15%",
          "render": function (data, type, row) {
            var label_class = data < 4 ? 'label-important' : 'label-success';
            var status = data < 4 ? 'In Draft @ Step ' + data : 'Completed';
            return '<sapn class="label ' + label_class + '">' + status + '</sapn>';
          }
        },
        {
          data: "Process_By_Center",width:"15%",
          "render": function (data, type, row) {
            if (data == 1 && row.Step == 4) {
              return '<div class="text-center"><span class="label label-danger">Not Received</span></div>';
            } else {
              var show = row.Step == 4 ? '<div class="text-center"><span class="label label-success">' + data + '</span></div>' : '';
              return show;
            }
          }
        },
        {
          data: "Document_Verified",width:"15%",
          "render": function (data, type, row) {
            if (row.Pendency_Status == 2) {
              if (!showInhouse) {
                return '<div class="text-center label label-primary"><span class="cursor-pointer"><strong>In Review</strong></span></div>'
              } else {
                return is_operations ? '<div class="text-center label label-warning"><span class="cursor-pointer" onclick="verifyDocument(&#39;' + row.ID + '&#39;)"><strong>Re-Review</strong></span></div>' : '<div class="text-center  label label-warning"><span><strong>Re-Review</strong></span></div>'
              }
            } else if (row.Pendency != 0) {
              if (!showInhouse) {
                return '<div class="text-center label label-primary"><span class="cursor-pointer" onclick="uploadPendency(&#39;' + row.ID + '&#39;)"><strong>Pendency</strong></span></div>'
              } else {
                return is_operations ? '<div class="text-center label label-primary"><span class="cursor-pointer" onclick="reportPendency(&#39;' + row.ID + '&#39;)"><strong>Pendency</strong></span></div>' : '<div class="text-center label label-primary"><span><strong>Pendency</strong></span></div>'
              }
            } else {
              if (data == 1) {
                var show = is_operations && row.Process_By_Center != 1 ? '<div class="text-center"><span class="cursor-pointer" onclick="verifyDocument(&#39;' + row.ID + '&#39;)"><strong>Review</strong></span></div>' : row.Step == 4 && row.Process_By_Center != 1 ? '<div class="text-center label label-warning"><strong>Pending</strong></div>' : '';
                return show;
              } else {
                var show = row.Step == 4 && row.Process_By_Center != 1 ? '<div class="text-center"><span class="label label-success">Verified at ' + data + '</span></div>' : '';
                return show;
              }
            }
          }
        },
        {
          data: "Payment_Received",width:"15%",
          "render": function (data, type, row) {
            if (data == 1 && row.Step == 4 && row.Process_By_Center != 1) {
              var show = is_accountant ? '<div class="text-center"><span class="cursor-pointer" onclick="verifyPayment(&#39;' + row.ID + '&#39;)"><strong>Review</strong></span></div>' : '<center><span class="label label-primary">Pending</span></center>';
              return show;
            } else if (row.Process_By_Center != 1) {
              var show = row.Step == 4 ? '<div class="text-center"><span class="label label-success">Verified on ' + data + '</span></div>' : '';
              return show;
            } else {
              return '';
            }
          },
          visible: false
        },
        {
          data: "Processed_To_University",width:"15%",
          "render": function (data, type, row) {
            if (data == 1) {
              var show = showInhouse && row.Document_Verified != 1 && row.Payment_Received != 1 ? '<div class="form-check complete mt-2">\
                <input type="checkbox" id="processed-to-university-' + row.ID + '" onclick="processedToUniversity(&#39;' + row.ID + '&#39;)">\
                <label for="processed-to-university-' + row.ID + '">Mark as Processed</label>\
              </div>' : "";
              return show;
            } else {
              var show = row.Step == 4 ? '<div class="text-center"><span class="label label-success">Processed on ' + data + '</span></div>' : '';
              return show;
            }
          },
          visible: false
        },
        {
          data: "Enrollment_No",width:"15%",
          "render": function (data, type, row) {
            var edit = "";
            if (role !== 'Counsellor' && role !== 'Sub-Counsellor') {
            var edit = showInhouse && row.Processed_To_University != 1 ? '<i class="ti ti-edit-circle add_btn_form h6 cursor-pointer p-1 ml-2" title="Add Enrollment No." onclick="addEnrollment(&#39;' + row.ID + '&#39;)">' : '';
            }
            return data + edit;
          }
        },
        {
          data: "OA_Number",width:"15%",
          "render": function (data, type, row) {
            var edit = "";
            if (role == 'University Head' || role == 'Counsellor' || role == 'Administartor') {
            var edit = showInhouse || is_oa_required ? '<i class="ti ti-edit-circle add_btn_form h6 cursor-pointer p-1 ml-2" title="Add OA No." onclick="addOANumber(&#39;' + row.ID + '&#39;)">' : '';
            }
            return data + edit;
          },visible:is_oa_required
        },
        {
          data: "Adm_Session",width:"15%",
        },
        {
          data: "Adm_Type",width:"15%",
        },
        {
          data: "Adm_Type",width:"15%",
          "render": function (data, type, row) {
            return '<span onclick="reportPendnency(' + row.ID + ')"><strong>Report</strong><span>';
          },
          visible: false,
        },
        {
          data: "First_Name",width:"15%",
          "render": function (data, type, row) {
            return '<strong>' + data + '</strong>';
          }
        },
        {
          data: "Father_Name",width:"15%",
        },
        {
          data: "Short_Name",width:"15%",
        },
        {
          data: "Duration",width:"15%",
        },
        {
          data: "Status",width:"15%",
          "render": function (data, type, row) {
            var active = data == 1 ? 'Active' : 'Inactive';
            if (row.Step == 4 && showInhouse) {
              var checked = data == 1 ? 'checked' : '';
              if (role !== 'Counsellor' && role !== 'Sub-Counsellor') {
              return '<div class="form-check form-check-inline switch switch-lg success">\
                <input onclick="changeStatus(\'Students\', &#39;' + row.ID + '&#39;);" type="checkbox" ' + checked + ' id="student-status-switch-' + row.ID + '">\
                <label for="student-status-switch-' + row.ID + '">' + active + '</label>\
              </div>';}else{
                  return '<label for="student-status-switch-' + row.ID + '">' + active + '</label>';
              }
            } else {
              return active;
            }
          },
          visible: hasStudentLogin || showStatus
        },
          {
          data: "Contact",width:"15%",
        },
        {
          data: "ID_Card",width:"15%",
          "render": function (data, type, row) {
            var active = data == 1 ? 'Active' : 'Inactive';
            if (row.Step == 4 && showInhouse && row.Enrollment_No.length > 0) {
              var checked = data == 1 ? 'checked' : '';
              if (role !== 'Counsellor' && role !== 'Sub-Counsellor') {
              return '<div class="form-check form-check-inline switch switch-lg success">\
                <input onclick="changeStatus(\'Students\', &#39;' + row.ID + '&#39;, \'ID_Card\');" type="checkbox" ' + checked + ' id="student-id-card-switch-' + row.ID + '">\
                <label for="student-id-card-switch-' + row.ID + '">' + active + '</label>\
              </div>';}else{
                  return '<label for="student-id-card-switch-' + row.ID + '">' + active + '</label>';
              }
            } else {
              return active;
            }
          },
          visible: hasStudentLogin
        },
        {
          data: "Admit_Card",width:"15%",
          "render": function (data, type, row) {
            var active = data == 1 ? 'Active' : 'Inactive';
            if (row.Step == 4 && showInhouse && row.Enrollment_No.length > 0) {
              var checked = data == 1 ? 'checked' : '';
              if (role !== 'Counsellor' && role !== 'Sub-Counsellor') {
              return '<div class="form-check form-check-inline switch switch-lg success">\
                <input onclick="changeStatus(\'Students\', &#39;' + row.ID + '&#39;, \'Admit_Card\');" type="checkbox" ' + checked + ' id="student-admit-card-switch-' + row.ID + '">\
                <label for="student-admit-card-switch-' + row.ID + '">' + active + '</label>\
              </div>';}else{
                  return '<label for="student-admit-card-switch-' + row.ID + '">' + active + '</label>';
              }
            } else {
              return active;
            }
          },
          visible: hasStudentLogin
        },
        {
          data: "Exam",width:"15%",
          "render": function (data, type, row) {
            var active = data == 1 ? 'Active' : 'Inactive';
            if (row.Step == 4 && showInhouse && row.Enrollment_No.length > 0) {
              var checked = data == 1 ? 'checked' : '';
              if (role !== 'Counsellor' && role !== 'Sub-Counsellor') {
              return '<div class="form-check form-check-inline switch switch-lg success">\
                <input onclick="changeStatus(\'Students\', &#39;' + row.ID + '&#39;, \'Exam\');" type="checkbox" ' + checked + ' id="student-exam-switch-' + row.ID + '">\
                <label for="student-exam-switch-' + row.ID + '">' + active + '</label>\
              </div>';
                  
              }else{
                  return '<label for="student-exam-switch-' + row.ID + '">' + active + '</label>';
              }
            } else {
              return active;
            }
          },
          visible: hasStudentLogin
        },
        {
          data: "DOB",width:"15%",
        },
        {
          data: "Center_Code",width:"15%",
        },
        {
          data: "Center_Name",width:"15%",
        },
        {
          data: "RM",width:"15%",
          visible: ['Center', 'Sub-Center','University'].includes(role) ? false : true
        }
        ],
        "sDom": "<'row pt-3 px-3 w-100 p-0 m-0'<'col-sm-6 pr-0 pl-0 custon_text_start'l><'col-sm-6 pr-0 pl-0'f>><t><'row'<p i>>",
            "destroy": true,
            "scrollCollapse": true,
            "oLanguage": {
              "sInfo": "Showing <b>_START_ to _END_</b> of _TOTAL_ entries"
            },
        drawCallback: function (settings, json) {
          $('[data-toggle="tooltip"]').tooltip();
        },
        "aaSorting": []
      };

      applicationTable.dataTable(applicationSettings);
      notProcessedTable.dataTable(notProcessedSettings);
      readyForVerificationTable.dataTable(readyForVerificationSettings);
      verifiedTable.dataTable(verifiedSettings);
      processedToUniversityTable.dataTable(processedToUniversitySettings);
      enrolledTable.dataTable(enrolledSettings);

      // search box for table
      $('#application-search-table').keyup(function () {
        applicationTable.fnFilter($(this).val());
      });

      $('#not-processed-search-table').keyup(function () {
        notProcessedTable.fnFilter($(this).val());
      });

      $('#ready-for-verification-search-table').keyup(function () {
        readyForVerificationTable.fnFilter($(this).val());
      });

      $('#document-verified-search-table').keyup(function () {
        documentVerifiedTable.fnFilter($(this).val());
      });

      $('#processed-to-university-search-table').keyup(function () {
        processedToUniversityTable.fnFilter($(this).val());
      });

      $('#enrolled-search-table').keyup(function () {
        enrolledTable.fnFilter($(this).val());
      });


    })
  </script>

  <script type="text/javascript">
    function changeSession(value) {
      $('input[type=search]').val('');
      updateSession();
    }

    function updateSession() {
      var session_id = $('#sessions').val();
      $.ajax({
        url: '/app/applications/change-session',
        data: {
          session_id: session_id
        },
        type: 'POST',
        success: function (data) {
          $('.table').DataTable().ajax.reload(null, false);
        }
      })
    }
  </script>

  <script type="text/javascript">
    function addEnrollment(id) {
      $.ajax({
        url: '/app/applications/enrollment/create?id=' + id,
        type: 'GET',
        success: function (data) {
          $('#md-modal-content').html(data);
          $('#mdmodal').modal('show');
        }
      })
    }

    function addOANumber(id) {
      $.ajax({
        url: '/app/applications/oa-number/create?id=' + id,
        type: 'GET',
        success: function (data) {
          $('#md-modal-content').html(data);
          $('#mdmodal').modal('show');
        }
      })
    }
  </script>

  <script type="text/javascript">
    function exportData() {
    //   var search = $('#application-table').val();
    //   var url = search.length > 0 ? "?search=" + search : "";
    //   console.log(url);return false;
      
      
        //var search = $('#application-search-table').val();
        var search = $('#application-table').val();
        //console.log(search, "sandip");
        var steps_found = $('.nav-tabs').find('li a.active').attr('data-target');
        var steps_found = steps_found.substring(1, steps_found.length);
        var url = search.length > 0 ? "?steps_found=" + steps_found + "&search=" + search : "?steps_found=" + steps_found;
        //var url = search.length > 0 ? "?search=" + search : "";
        window.open('/app/applications/export' + url);
        // window.open('/app/applications/kp' + url);
    }

    function exportDocuments(id) {
      $.ajax({
        url: '/app/applications/document?id=' + id,
        type: 'GET',
        success: function (data) {
          $('#md-modal-content').html(data);
          $('#mdmodal').modal('show');
        }
      })
    }

    function exportZip(id) {
      window.open('/app/applications/zip?id=' + id);
    }

    function exportPdf(id) {
      window.open('/app/applications/pdf?id=' + id);
    }
  </script>

  <script type="text/javascript">
    function uploadOAEnrollRoll() {
      $.ajax({
        url: '/app/applications/uploads/create_oa_enroll_roll',
        type: 'GET',
        success: function (data) {
          $('#md-modal-content').html(data);
          $('#mdmodal').modal('show');
        }
      })
    }
  </script>
  <script type="text/javascript">
    function printForm(id) {
      window.open('/forms/<?= $_SESSION['university_id'] ?>/index?student_id=' + id);
    }
  </script>
  <script type="text/javascript">
    function processByCenter(id) {
      Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Process'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: "/app/applications/process-by-center",
            type: 'POST',
            dataType: 'json',
            data: {
              id: id
            },
            success: function (data) {
              if (data.status == 200) {
                notification('success', data.message);
                $('.table').DataTable().ajax.reload(null, false);
              } else {
                notification('danger', data.message);
                $('.table').DataTable().ajax.reload(null, false);
              }
            }
          });
        } else {
          $('.table').DataTable().ajax.reload(null, false);
        }
      })
    }

    function processedToUniversity(id) {
      Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Process.'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: "/app/applications/processed-to-university",
            type: 'POST',
            dataType: 'json',
            data: {
              id: id
            },
            success: function (data) {
              if (data.status == 200) {
                notification('success', data.message);
                $('.table').DataTable().ajax.reload(null, false);
              } else {
                notification('danger', data.message);
                $('.table').DataTable().ajax.reload(null, false);
              }
            }
          });
        } else {
          $('.table').DataTable().ajax.reload(null, false);
        }
      })
    }

    function verifyPayment(id) {
      $.ajax({
        url: '/app/applications/review-payment?id=' + id,
        type: 'GET',
        success: function (data) {
          $("#lg-modal-content").html(data);
          $("#lgmodal").modal('show');
        }
      })
    }

    function verifyDocument(id) {
      $.ajax({
        url: '/app/applications/review-documents?id=' + id,
        type: 'GET',
        success: function (data) {
          $('#full-modal-content').html(data);
          $('#fullmodal').modal('show');
        }
      })
    }

    function reportPendency(id) {
      $.ajax({
        url: '/app/pendencies/create?id=' + id,
        type: 'GET',
        success: function (data) {
          $('#report-modal-content').html(data);
          $('#reportmodal').modal('show');
        }
      })
    }

    function uploadPendency(id) {
      $(".modal").modal('hide');
      $.ajax({
        url: '/app/pendencies/edit?id=' + id,
        type: 'GET',
        success: function (data) {
          $("#lg-modal-content").html(data);
          $("#lgmodal").modal('show');
        }
      })
    }
  </script>
  <script type="text/javascript">
    $(document).ready(function () {
        var universit_id= <?= $_SESSION['university_id'] ?>;
        if(universit_id == "41"){
            $("#departments").select2({
        placeholder: "Choose Duration",
      }) 
        }else{
      $("#departments").select2({
        placeholder: "Choose Course Type",
      })
        }
      $("#center").select2({
        placeholder: "Choose Center",
      })
      $("#sub_courses").select2({
        placeholder: "Choose Sub-Courses",
      })
      getCenter('<?php $_SESSION['university_id'] ?>');
      <?php if ($_SESSION['Role'] == "Center" && $_SESSION['CanCreateSubCenter'] == 1) { ?>
        addFilter('<?= $_SESSION["ID"]; ?>', 'users');
      <?php } ?>
    })

    function getCenter() {
      let university_id = '<?= $_SESSION['university_id'] ?>';
      $.ajax({
        url: '/app/application-form/center-form?university_id=' + university_id,
        type: 'GET',
        success: function (data) {
          $('#center').html(data);
          $('#center').val(<?php echo !empty($id) ? $student['Added_For'] : (isset($_GET['lead_id']) ? $lead['User_ID'] : (isset($_SESSION['lead_id']) ? $lead['Added_For'] : '')) ?>);
        }
      })
    }

    function addFilter(id, by) {
      $.ajax({
        url: '/app/applications/filter',
        type: 'POST',
        data: {
          id,
          by
        },
        dataType: 'json',
        success: function (data) {
          if (data.status) {
            $('.table').DataTable().ajax.reload(null, false);
            $("#sub_center").html(data.subCenterName);
            if (by == "users") {
              var subcourse_id = $("#sub_center").val();
              addSubCenterFilter(subcourse_id, by);
            }

            if ('<?= $_SESSION['Role'] ?>' == 'Administrator') {
              // $(".sub_center").html(data.subCenterName);

            }
          }
        }
      })
    }
    function addSubCenterFilter(id, by) {
      $.ajax({
        url: '/app/applications/filter',
        type: 'POST',
        data: {
          id,
          by
        },
        dataType: 'json',
        success: function (data) {

          if (data.status) {
            $('.table').DataTable().ajax.reload(null, false);
            // $  ("#sub_center").html(data.subCenterName);
          }

        }
      })
    }

    function addDateFilter() {
      var startDate = $("#startDateFilter").val();
      var endDate = $("#endDateFilter").val();
      if (startDate.length == 0 || endDate == 0) {
        return
      }
      var id = 0;
      var by = 'date';
      $.ajax({
        url: '/app/applications/filter',
        type: 'POST',
        data: {
          id,
          by,
          startDate,
          endDate
        },
        dataType: 'json',
        success: function (data) {
          if (data.status) {
            $('.table').DataTable().ajax.reload(null, false);
          }
        }
      })
    }
    function addProcessDateFilter() {
      var startProcessDate = $("#startProcessDateFilter").val();
      var endProcessDate = $("#endProcessDateFilter").val();
      if (startProcessDate.length == 0 || endProcessDate == 0) {
        return
      }
      var id = 0;
      var by = 'processdate';
      $.ajax({
        url: '/app/applications/filter',
        type: 'POST',
        data: {
          id,
          by,
          startProcessDate,
          endProcessDate
        },
        dataType: 'json',
        success: function (data) {
          if (data.status) {
            $('.table').DataTable().ajax.reload(null, false);
          }
        }
      })
    }
    
    function getCenterListVerticalType(id, by, vertical_type) {
      $.ajax({
        url: '/app/students/vertical-list?vertical_type=' + vertical_type + '&type=' + by + '&id=' + id,
        type: 'GET',
        success: function(data) {
          // inject into center dropdown
          $("#center").html(data);
        }
      })
    }

    function addFilterVerticalType(id, by, vertical_type) {
      addFilter(id, 'vartical_type');
      getCenterListVerticalType(id, by, vertical_type);
    }
    
    function exportSelectedDocument() {
      var search = $('#application-table').val();
      var searchQuery = search.length > 0 ? "?search=" + search : "";
      $.ajax({
        url: '/app/applications/documents/create' + searchQuery,
        type: 'GET',
        success: function(data) {
          $('#md-modal-content').html(data);
          $('#mdmodal').modal('show');
        }
      })
    }

  </script>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>