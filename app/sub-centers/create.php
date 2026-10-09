<!-- Modal -->

<?php
session_start();
$role = $_SESSION['Role'];
$disabled ="";
$vertical = 1;
if($role!='Administrator')
{
    $disabled = "disabled";
    $vertical = $_SESSION['vertical'];
}

//print_r($_SESSION);die;

?>
<div class="modal-header clearfix text-left">
  <button aria-label="" type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="pg-icon">close</i>
  </button>
  <h5 class="font-weight-bold text-black">Add <span class="semi-bold">IGC</span></h5>
</div>
<form role="form" id="form-add-sub-centers" action="/app/sub-centers/store" method="POST" enctype="multipart/form-data">
  <div class="modal-body">

    <div class="row">
      <div class="col-md-12 pr-0 d-none">
        <div class="form-group form-group-default required">
          <label>Vertical</label>
          <select class="full-width" style="border: transparent; " name="vertical" id="vertical" <?=$disabled?>>
            <option value="1" <?php if($vertical==1){ echo "selected";}?> >Edtech</option>
            <option value="2" <?php if($vertical==2){ echo "selected";}?> >IITS</option>
            <option value="3" <?php if($vertical==3){ echo "selected";}?> >Rudra</option>
          </select>
        </div>
      </div>
      <div class="col-md-12 pl-0">
        <div class="form-group form-group-default required">
          <label>Regional Coordinator</label>
         <select class="full-width" style="border: transparent;" name="reporting">
  <?php 
    require '../../includes/db-config.php';
    // Note: session_start() is handled externally or at the top
    print $_SESSION['Role'] != 'Center' ? '<option value="">Choose</option>' : '';
    
    // Check if user is Administrator to handle university filtering correctly
    if ($_SESSION['Role'] == 'Administrator') {
        // For Administrators, select all centers that have a relation to university via University_User
        $query = "SELECT Users.ID, CONCAT(Users.Name, ' (', Users.Code, ')') as Name 
                  FROM Users 
                  JOIN University_User ON Users.ID = University_User.User_ID 
                  WHERE Users.Role = 'Center' 
                  AND Users.CanCreateSubCenter = 1 
                  GROUP BY Users.ID";
    } else {
        // For non-admin roles (e.g., Center)
        $query = "SELECT Users.ID, CONCAT(Users.Name, ' (', Users.Code, ')') as Name 
                  FROM Users 
                  LEFT JOIN University_User ON Users.ID = " . $_SESSION['ID'] . " 
                  WHERE Users.Role = 'Center' 
                  AND Users.CanCreateSubCenter = 1 
                  AND University_User.University_ID = " . $_SESSION['university_id'] . " 
                  GROUP BY Users.ID";
    }

    $centers = $conn->query($query);
    while($center = $centers->fetch_assoc()) { ?>
      <option value="<?=$center['ID']?>"><?=$center['Name']?></option>
  <?php } ?>
</select>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="form-group form-group-default required">
          <label>Name</label>
          <input type="text" name="name" class="form-control" placeholder="ex: John Doe" required>
        </div>
      </div>
    </div>

    <!-- Added Email Field -->
    <div class="row">
      <div class="col-md-12">
        <div class="form-group form-group-default required">
          <label>Email</label>
          <input type="email" name="email" class="form-control" placeholder="ex: john@example.com" required>
        </div>
      </div>
    </div>
    <!-- End Email Field -->

  </div>
  <div class="modal-footer clearfix text-end">
    <div class="col-md-12 m-t-10 sm-m-t-10">
      <button aria-label="" type="button" data-dismiss="modal" class=" btn btn-secondary mr-2">
        <i class="ti ti-circle-dashed-x mr-2"></i> Close</button>
      <button aria-label="" type="submit" class="btn btn-primary ">
        <i class="ti ti-circle-check mr-2"></i> Save</button>
    </div>
  </div>
</form>

<script>
  $(function(){
    $('#form-add-sub-centers').validate({
      rules: {
        name: { required: true },
        email: { required: true, email: true } // Added validation rules for email
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

  $("#form-add-sub-centers").on("submit", function(e){
    e.preventDefault(); // Moved preventDefault to the top of submission handler
    if($('#form-add-sub-centers').valid()){
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
              if(data.status==200){
                $('.modal').modal('hide');
                notification('success', data.message);
                $('#users-table').DataTable().ajax.reload(null, false);
              }else{
                $(':input[type="submit"]').prop('disabled', false);
                notification('danger', data.message);
              }
          }
      });
    }
  });
</script>