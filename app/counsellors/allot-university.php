<?php
  if(isset($_GET['id'])){
    require '../../includes/db-config.php';
    session_start();
    $id = intval($_GET['id']);
    $alloted = array();
    $alloted_universities = $conn->query("SELECT University_ID FROM University_User WHERE `User_ID` = $id");
    while($alloted_university = $alloted_universities->fetch_assoc()){
      $alloted[] = $alloted_university['University_ID'];
    }
?>
  <!-- Modal -->
  <div class="modal-header clearfix text-left mb-4">
    <h5 class="font-weight-bold text-black">Allot <span class="semi-bold"></span>Vertical(s)</h5>
  </div>
  <div class="modal-body">

    <?php if(!empty($alloted)){ 
      print !empty($alloted) ? '<dt class="text-success mb-2">Alloted</dt>' : '' ?>
      <div class="row">
        <?php
          $alloted_query = !empty($alloted) ? " WHERE ID IN (".implode(',', $alloted).")" : "";
          $universities = $conn->query("SELECT ID, CONCAT(Universities.Short_Name, ' (', Universities.Vertical, ')') AS Name, Logo FROM Universities $alloted_query ORDER BY Short_Name ASC");
          while($university = $universities->fetch_assoc()){ ?>
            <div class="col-md-3 cursor-pointer" onclick="step2('<?=$id?>', '<?=$university['ID']?>', '<?=$university['Name']?>');">
              <div class="card">
                <div class="card-body">
                  <center>
                    <img src="<?=$university['Logo']?>" alt="logo" data-src="<?=$university['Logo']?>" data-src-retina="<?=$university['Logo']?>" height="70px">
                    <p class="bold mt-2"><?=$university['Name']?></p>
                  </center>
                </div>
                <?php
                    if($_SESSION['Role']=="University Head" || $_SESSION['Role']=="Administrator"){
                        ?>
                      <!--  <i class="ti ti-trash-x cursor-pointer add_btn_form h5" style="width:7%;" data-toggle="tooltip" data-placement="top" title="" onclick="remove( '<?=$university['ID']?>','<?=$id?>')" data-original-title="Delete"></i> -->
                        <?php
                    }
                ?>
              </div>
            </div>
        <?php }
        ?>
      </div>
    <?php } ?>


    <?php $not_alloted_query = !empty($alloted) ? " WHERE ID NOT IN (".implode(',', $alloted).")" : "";
      $universities = $conn->query("SELECT ID, CONCAT(Universities.Short_Name, ' (', Universities.Vertical, ')') AS Name, Logo FROM Universities $not_alloted_query ORDER BY Short_Name ASC");
      if($universities->num_rows>0){ ?>
        <dt class="text-primary <?php print !empty($alloted) ? 'mt-4' : '' ?> mb-2">Not Alloted</dt>
        <div class="row">
          <?php
            while($university = $universities->fetch_assoc()){ ?>
              <div class="col-md-3 cursor-pointer" onclick="step2('<?=$id?>', '<?=$university['ID']?>', '<?=$university['Name']?>');">
                <div class="card">
                  <div class="card-body">
                    <center>
                      <img src="<?=$university['Logo']?>" alt="logo" data-src="<?=$university['Logo']?>" data-src-retina="<?=$university['Logo']?>" height="70px">
                      <p class="bold mt-2"><?=$university['Name']?></p>
                    </center>
                  </div>
                </div>
              </div>
          <?php } ?>
        </div>  
    <?php } ?>
  </div>

<!-- Professional & Beautiful Warning Modal -->
<div class="modal fade" id="counsellorWarningModal" tabindex="-1" aria-labelledby="counsellorWarningModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Modal Header with Warning Accent -->
            <div class="modal-header bg-light border-0 px-4 py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-triangle-exclamation fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="counsellorWarningModalLabel">Action Required</h5>
                        <p class="text-muted small mb-0">Active centers mapping detected</p>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body px-4 py-4">
                <p class="text-secondary mb-3">
                    This counsellor cannot be unlinked because they are currently assigned to active centers. Please reassign or remove them from the following locations first:
                </p>
                
                <!-- Dynamic Center List Container -->
                <div class="p-3 bg-light rounded-3 border border-opacity-50">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fa-solid fa-building-columns text-primary fs-6"></i>
                        <span class="fw-semibold text-dark small text-uppercase tracking-wider">Associated Centers:</span>
                    </div>
                    <div id="modalCentersList" class="text-dark fw-medium ps-4">
                        <!-- Populated dynamically via AJAX response -->
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-light border-0 px-4 py-3">
                <button type="button" class="btn btn-dark px-4 rounded-pill fw-medium" data-bs-dismiss="modal">Got it</button>
            </div>
        </div>
    </div>
</div>


  <script>
    function step2(id, university_id, name){
      var modal = 'full';
      $.ajax({
        url:'/app/counsellors/step-2',
        type:'POST',
        data:{id:id, university_id:university_id, name:name},
        success: function(data) {
          $('#'+modal+'-modal-content').html(data);
        }
      })
    }
   function remove(uni, user) {
    $.ajax({
        url: "/app/counsellors/remove",
        type: "GET",
        data: { uni: uni, user: user },
        dataType: "json",
        success: function(response) {
            if (response.status === 200) {
                alert(response.message);
                location.reload(); 
            } else {
                // Populate just the center list HTML into the container, keeping modal layout intact
                $('#modalCentersList').html(response.centers_list);
                var warningModal = new bootstrap.Modal(document.getElementById('counsellorWarningModal'));
                warningModal.show();
            }
        },
        error: function() {
            alert('An unexpected error occurred while processing your request.');
        }
    });
}
    <?php if($_SESSION['Role']!='Administrator'){ ?> step2('<?=$id?>', '<?=$_SESSION["university_id"]?>', '<?=$_SESSION["university_name"]?>') <?php } ?>
  </script>
<?php } ?>
