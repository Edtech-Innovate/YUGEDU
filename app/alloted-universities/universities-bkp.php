<?php
    session_start();
    require '../../includes/db-config.php';
    $id = $_SESSION['Role'] == 'Administrator' ? 0 : $_SESSION['ID'];
    $alloted = array();
    
    if(!empty($id)){
      if($_SESSION['Role'] == 'Counsellor'){
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
<style>
   .custom_university {
   width: 1111px !important;
    margin: 0 auto; /* center */
}

@media (max-width: 900px) {
    .custom_university {
        width:100% !important;
        max-width: 600px !important;
    }
}

</style>
  <!-- Modal -->
  <div class="modal-header clearfix text-left mb-4 custom_university">
    <button aria-label="" type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="pg-icon">close</i>
    </button>
    <h5>Select <span class="semi-bold"></span>Vertical (Alloted)</h5>
  </div>
  <div class="modal-body custom_university">
    <div class="row">
      <?php
        $alloted_query = !empty($alloted) ? " AND ID IN (".implode(',', $alloted).")" : "";
        $not_alloted_query = !empty($alloted) ? " AND ID NOT IN (".implode(',', $alloted).")" : "";
        $universities = $conn->query("SELECT ID, CONCAT(Universities.Short_Name) as Name, Universities.Vertical, Logo, Has_Unique_Center, Is_B2C, Course_Allotment, Has_LMS FROM Universities where status=1 $alloted_query order by Universities.Name ASC");
        $notalloteduniversities = $conn->query("SELECT ID, CONCAT(Universities.Short_Name) as Name, Universities.Vertical, Logo, Has_Unique_Center, Is_B2C, Course_Allotment, Has_LMS FROM Universities where status=1 $not_alloted_query order by Universities.Name ASC");
        while($university = $universities->fetch_assoc()){
            if($_SESSION['Designation'] == "University"){
                if( $university['ID']==41 || $university['ID']==20){
                    ?>
                     <div class="col-lg-4 col-md-6 col-sm-12 cursor-pointer" onclick="setSessionUniversity('<?=$university['ID']?>', '<?=$university['Name']?>', '<?=$university['Logo']?>', '<?=$university['Has_Unique_Center']?>', '<?=$university['Is_B2C']?>', '<?=$university['Course_Allotment']?>', '<?=$university['Has_LMS']?>');">
            <center>
              <img src="<?=$university['Logo']?>" alt="logo" data-src="<?=$university['Logo']?>" data-src-retina="<?=$university['Logo']?>" style="max-width:100%;" height="70px">
              <p class="bold mt-2" style="word-break: break-word;"><?= ucwords(strtolower($university['Name'])) ?> (<?=ucwords(strtolower($university['Vertical']))?>)</p>
            </center>
          </div>
                    <?php   
                }
            }else{
                ?>
                     <div class="col-lg-4 col-md-6 col-sm-12 cursor-pointer" onclick="setSessionUniversity('<?=$university['ID']?>', '<?=$university['Name']?>', '<?=$university['Logo']?>', '<?=$university['Has_Unique_Center']?>', '<?=$university['Is_B2C']?>', '<?=$university['Course_Allotment']?>', '<?=$university['Has_LMS']?>');">
            <center>
              <img src="<?=$university['Logo']?>" alt="logo" data-src="<?=$university['Logo']?>" data-src-retina="<?=$university['Logo']?>" style="max-width:100%;" height="70px">
              <p class="bold mt-2"><?= ucwords(strtolower($university['Name'])) ?>  (<?=ucwords(strtolower($university['Vertical']))?>)</p>
            </center>
          </div>
                <?php
            }
        ?>
      <?php }
      ?>
    </div>
    <?php
        if($id!=0){
            ?>
                 <hr>
    <div class="row">
        <h5>Not Alloted</h5>
    </div>
    <div class="row">
        
         <?php
        $alloted_query = !empty($alloted) ? " AND ID IN (".implode(',', $alloted).")" : "";
        $not_alloted_query = !empty($alloted) ? " AND ID NOT IN (".implode(',', $alloted).")" : "";
        $universities = $conn->query("SELECT ID, CONCAT(Universities.Short_Name) as Name, Universities.Vertical, Logo, Has_Unique_Center, Is_B2C, Course_Allotment, Has_LMS FROM Universities where status=1 $alloted_query order by Universities.Name ASC");
        $notalloteduniversities = $conn->query("SELECT ID, CONCAT(Universities.Short_Name) as Name, Universities.Vertical, Logo, Has_Unique_Center, Is_B2C, Course_Allotment, Has_LMS FROM Universities where status=1 $not_alloted_query order by Universities.Name ASC");
        while($university = $notalloteduniversities->fetch_assoc()){
            if($_SESSION['Designation'] == "University"){
                if( $university['ID']==41 || $university['ID']==20){
                    ?>
                     <div class="col-lg-4 col-md-6 col-sm-12 cursor-pointer" >
            <center>
              <img src="<?=$university['Logo']?>" alt="logo" data-src="<?=$university['Logo']?>" data-src-retina="<?=$university['Logo']?>" style="max-width:100%;" height="70px">
              <p class="bold mt-2" style="word-break: break-word;"><?= ucwords(strtolower($university['Name'])) ?> (<?=ucwords(strtolower($university['Vertical']))?>)</p>
            </center>
          </div>
                    <?php   
                }
            }else{
                ?>
                     <div class="col-lg-4 col-md-6 col-sm-12 cursor-pointer" >
            <center>
              <img src="<?=$university['Logo']?>" alt="logo" data-src="<?=$university['Logo']?>" data-src-retina="<?=$university['Logo']?>" style="max-width:100%;" height="70px">
              <p class="bold mt-2"><?= ucwords(strtolower($university['Name'])) ?>  (<?=ucwords(strtolower($university['Vertical']))?>)</p>
            </center>
          </div>
                <?php
            }
        ?>
      <?php }
      ?>
    </div>
            <?php
        }
    
    ?>
  </div>

  <script type="text/javascript">
    function setSessionUniversity(id, name, logo, unique_center, is_b2c, is_vocational, has_lms){
      $.ajax({
        url: '/app/login/change-university',
        type: 'POST',
        data:{id, name, logo, unique_center, is_b2c, is_vocational, has_lms},
        dataType: 'json',
        success: function(data) {
          if(data.status==200){
            $('.modal').modal('hide');
            notification('success', data.message);
            window.location.reload(true);
          }
        }
      })
    }
  </script>
