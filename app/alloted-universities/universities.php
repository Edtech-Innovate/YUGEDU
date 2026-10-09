<?php
    session_start();
    require '../../includes/db-config.php';
    $id = $_SESSION['Role'] == 'Administrator' ? 0 : $_SESSION['ID'];
    $alloted = array();
    if(!empty($id)){
      if($_SESSION['Role'] == 'Center'){
        $alloted_universities = $conn->query("SELECT University_ID FROM Alloted_Center_To_Counsellor WHERE `Code` = $id");
        while($alloted_university = $alloted_universities->fetch_assoc()){
          $alloted[] = $alloted_university['University_ID'];
        }
      }else{
        $alloted_universities = $conn->query("SELECT University_ID FROM University_User WHERE `User_ID` = $id");
        while($alloted_university = $alloted_universities->fetch_assoc()){
          $alloted[] = $alloted_university['University_ID'];
        }
      }
    }
?>
  <!-- Modal Header -->
  <div class="modal-header clearfix text-left border-0 pb-2">
    <button aria-label="Close" type="button" class="close" data-dismiss="modal" aria-hidden="true">
      <i class="pg-icon">close</i>
    </button>
    <h5 class="modal-title font-weight-bold tracking-tight text-secondary">Select <span class="semi-bold">Vertical</span></h5>
  </div>
  
  <!-- Modal Body -->
  <div class="modal-body pt-3">
    <!-- Responsive Grid Matrix: 4 cols on desktop, 2 cols on tablet, 1 col on mobile -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4">
      <?php
        $alloted_query = !empty($alloted) ? " WHERE ID IN (".implode(',', $alloted).")" : "";
        
        // Added ORDER BY Universities.Short_Name ASC for alphabetical sorting
        $universities = $conn->query("SELECT ID, CONCAT(Universities.Short_Name, ' (', Universities.Vertical, ')') as Name, Logo, Has_Unique_Center, Is_B2C, Course_Allotment, Has_LMS FROM Universities $alloted_query ORDER BY Universities.Short_Name ASC");
        
        while($university = $universities->fetch_assoc()){
            if($_SESSION['Designation'] == "University"){
                if( $university['ID']==41 || $university['ID']==20){
                    ?>
                    <div class="col mb-4">
                      <div class="card h-100 border rounded shadow-sm text-center p-3 cursor-pointer university-card" 
                           onclick="setSessionUniversity('<?=$university['ID']?>', '<?=$university['Name']?>', '<?=$university['Logo']?>', '<?=$university['Has_Unique_Center']?>', '<?=$university['Is_B2C']?>', '<?=$university['Course_Allotment']?>', '<?=$university['Has_LMS']?>');">
                        <div class="d-flex align-items-center justify-content-center image-wrapper">
                          <img src="<?=$university['Logo']?>" alt="logo" data-src="<?=$university['Logo']?>" data-src-retina="<?=$university['Logo']?>">
                        </div>
                        <p class="font-weight-bold text-dark m-0 mt-3 small text-uppercase tracking-wider university-title"><?=$university['Name']?></p>
                      </div>
                    </div>
                    <?php   
                }
            }else{
                ?>
                <div class="col mb-4">
                  <div class="card h-100 border rounded shadow-sm text-center p-3 cursor-pointer university-card" 
                       onclick="setSessionUniversity('<?=$university['ID']?>', '<?=$university['Name']?>', '<?=$university['Logo']?>', '<?=$university['Has_Unique_Center']?>', '<?=$university['Is_B2C']?>', '<?=$university['Course_Allotment']?>', '<?=$university['Has_LMS']?>');">
                    <div class="d-flex align-items-center justify-content-center image-wrapper">
                      <img src="<?=$university['Logo']?>" alt="logo" data-src="<?=$university['Logo']?>" data-src-retina="<?=$university['Logo']?>">
                    </div>
                    <p class="font-weight-bold text-dark m-0 mt-3 small text-uppercase tracking-wider university-title"><?=$university['Name']?></p>
                  </div>
                </div>
                <?php
            }
        ?>
      <?php } ?>
    </div>
  </div>

  <!-- Dynamic Instructions Modal for Error 403 -->
  <div class="modal fade" id="assignmentStepsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px !important; margin: 1.75rem auto;">
      <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; background: #ffffff;">
        <div class="modal-header border-0 pb-0 text-center d-block position-relative">
          <button aria-label="Close" type="button" class="close position-absolute" data-dismiss="modal" aria-hidden="true" style="right: 20px; top: 20px;">
            <i class="pg-icon">close</i>
          </button>
          <div class="mx-auto my-3 d-flex align-items-center justify-content-center rounded-circle" style="width: 56px; height: 56px; background-color: #fee2e2; color: #dc2626;">
             <i class="pg-icon" style="font-size: 24px;">info</i>
          </div>
          <h4 class="modal-title font-weight-bold text-dark px-3">University Access Required</h4>
          <p class="text-muted small px-3 mt-1">This university isn't linked to your profile yet. Please assign it using the following steps:</p>
        </div>
        <div class="modal-body py-4 px-4">
          <div class="d-flex flex-column gap-3">
            <div class="d-flex align-items-start mb-3">
              <span class="badge badge-primary rounded-circle d-flex align-items-center justify-content-center font-weight-bold mr-3" style="width: 26px; height: 26px; background-color: #4f46e5; min-width: 26px; color:#fff;">1</span>
              <div>
                <h6 class="font-weight-bold m-0 text-dark" style="font-size: 0.95rem;">Navigate to Users</h6>
                <p class="text-muted small m-0">Go to the main sidebar and select the <strong>Users</strong> section.</p>
              </div>
            </div>
            <div class="d-flex align-items-start mb-3">
              <span class="badge badge-primary rounded-circle d-flex align-items-center justify-content-center font-weight-bold mr-3" style="width: 26px; height: 26px; background-color: #4f46e5; min-width: 26px; color:#fff;">2</span>
              <div>
                <h6 class="font-weight-bold m-0 text-dark" style="font-size: 0.95rem;">Open Vertical Head Profiles</h6>
                <p class="text-muted small m-0">Locate and enter the <strong>Vertical Head</strong> sub-menu management area.</p>
              </div>
            </div>
            <div class="d-flex align-items-start">
              <span class="badge badge-primary rounded-circle d-flex align-items-center justify-content-center font-weight-bold mr-3" style="width: 26px; height: 26px; background-color: #4f46e5; min-width: 26px; color:#fff;">3</span>
              <div>
                <h6 class="font-weight-bold m-0 text-dark" style="font-size: 0.95rem;">Link the University</h6>
                <p class="text-muted small m-0">Edit the respective account and assign the missing university parameters to the target vertical head.</p>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0 justify-content-center pb-4">
          <button type="button" class="btn btn-primary font-weight-bold px-4 py-2" data-dismiss="modal" style="background-color: #4f46e5; border-color: #4f46e5; border-radius: 6px; color: #fff;">Got it, thanks</button>
        </div>
      </div>
    </div>
  </div>

  <!-- UI & Premium Layout Styling -->
  <style>
    @media (min-width: 992px) {
      body .modal-dialog:not(#assignmentStepsModal .modal-dialog) {
        max-width: 80% !important;
        width: 80% !important;
      }
    }

    .cursor-pointer {
      cursor: pointer;
    }
    .university-card {
      background: #ffffff;
      border: 1px solid #e2e8f0 !important;
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .university-card:hover {
      transform: translateY(-4px);
      border-color: #4f46e5 !important;
      box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.1), 0 4px 6px -2px rgba(79, 70, 229, 0.05) !important;
    }
    .image-wrapper {
      height: 60px; 
      width: 100%;
      overflow: hidden;
    }
    .image-wrapper img {
      max-height: 100%;
      max-width: 100%;
      object-fit: contain;
    }
    .university-title {
      font-size: 0.75rem;
      letter-spacing: 0.05em;
      color: #334155 !important;
      line-height: 1.4;
    }
  </style>

  <script type="text/javascript">
    function showInstructionsModal() {
      // Moves the modal out of the AJAX container to the page <body> so it doesn't get hidden accidentally
      var modalNode = $('#assignmentStepsModal').detach();
      $('body').append(modalNode);
      
      // Shows the instructions modal
      $('#assignmentStepsModal').modal('show');
    }

    function setSessionUniversity(id, name, logo, unique_center, is_b2c, is_vocational, has_lms){
      $.ajax({
        url: '/app/login/change-university',
        type: 'POST',
        data:{id, name, logo, unique_center, is_b2c, is_vocational, has_lms},
        dataType: 'json',
        success: function(data) {
          if(data.status == 200){
            $('.modal').modal('hide');
            notification('success', data.message);
            window.location.reload(true);
          } else if(data.status == 403) {
            $('.modal').modal('hide');
            setTimeout(function(){
              showInstructionsModal();
            }, 400);
          } else {
            notification('error', data.message || 'An error occurred.');
          }
        },
        error: function(xhr) {
          if(xhr.status == 403) {
            $('.modal').modal('hide');
            setTimeout(function(){
              showInstructionsModal();
            }, 400);
          } else {
            notification('error', 'Something went wrong.');
          }
        }
      })
    }
  </script>