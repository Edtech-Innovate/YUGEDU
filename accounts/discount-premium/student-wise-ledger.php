<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-top.php'); ?>
<style>
  table thead{
    background: #c5cfca ;
  }
  table thead tr th {
    color: #4b4b4b !important;
    font-weight: bold !important;
  }
  .modal-open .select2-container {
    z-index: 105 !important;
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
             
             
                <div class="" id="students">
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
          url: '/app/discount-premium/filter',
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
          url: '/app/discount-premium/students?id=' + id ,
          type: 'GET',
          success: function(data) {

            $("#students").html(data);

          }
        })
      }

  

      getCenterList('center');
    </script>
    <script>
    

    
      function getStudentListType(id, role,admission_session_id=null) {
        $.ajax({
          url: '/app/discount-premium/students?id=' + id + '&role=' + role+"&admission_session_id="+admission_session_id,
          type: 'GET',
          success: function(data) {

            $("#students").html(data);

          }
        })
      }
  
    </script>

    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>