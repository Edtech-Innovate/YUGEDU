<?php 
  if(isset($_GET['id'])){
    require '../../includes/db-config.php';

    $id = intval($_GET['id']);
    // Added Email to the SELECT query
    $user = $conn->query("SELECT Name, Email, vertical FROM Users WHERE ID = $id");
    $user = mysqli_fetch_assoc($user);
  }
?>

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

?>
<!-- Modal -->
<div class="modal-header clearfix text-left">
  <button aria-label="" type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="pg-icon">close</i>
  </button>
  <h5 class="font-weight-bold text-black">Edit <span class="semi-bold"></span>Sub-Center</h5>
</div>
<form role="form" id="form-edit-sub-centers" action="/app/sub-centers/update" method="POST" enctype="multipart/form-data">
  <div class="modal-body">
    <div class="row">
        <div class="col-md-12 pr-0 d-none">
          <div class="form-group form-group-default required">
            <label>Vertical</label>
            <select class="full-width" style="border: transparent;" name="vertical" id="vertical" <?=$disabled?>>
              <option value="1" <?php if($user['vertical']==1){ echo "selected";}else{echo "";}  ?>>Edtech</option>
              <option value="2" <?php if($user['vertical']==2){ echo "selected";}else{echo "";}  ?>>IITS</option>
              <option value="3" <?php if($user['vertical']==3){ echo "selected";}else{echo "";}  ?>>Rudra</option>
            </select>
          </div>
        </div>
      <div class="col-md-12 pl-0">
        <div class="form-group form-group-default required">
          <label>Name</label>
          <input type="text" name="name" class="form-control" placeholder="ex: John Doe" value="<?=$user['Name']?>" required>
        </div>
      </div>
      <!-- Added Email Field -->
      <div class="col-md-12 pl-0 mt-3">
        <div class="form-group form-group-default required">
          <label>Email</label>
          <input type="email" name="email" class="form-control" placeholder="ex: john@example.com" value="<?=$user['Email']?>" required>
        </div>
      </div>
      <!-- End Email Field -->
    </div>
  </div>
  <div class="modal-footer clearfix text-end">
    <div class="col-md-12 m-t-10 sm-m-t-10">
      <button aria-label="" type="button" data-dismiss="modal" class=" btn btn-secondary mr-2">
        <i class="ti ti-circle-dashed-x mr-2"></i> Close</button>
      <button aria-label="" type="submit" class="btn btn-primary ">
        <i class="ti ti-circle-check mr-2"></i> Update</button>
    </div>
  </div>
</form>

<script>
  $(function(){
    $('#form-edit-sub-centers').validate({
      rules: {
        name: {required: true},
        email: {required: true, email: true} // Added validation rules for email
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

  $("#form-edit-sub-centers").on("submit", function(e){
    e.preventDefault(); // Moved preventDefault up to prevent standard submission immediately
    if($('#form-edit-sub-centers').valid()){
      $(':input[type="submit"]').prop('disabled', true);
      var formData = new FormData(this);
      formData.append('id', '<?=$id?>');
      // FormData automatically captures input fields with a `name` attribute (like `name="email"`), 
      // so `email` is now natively included and sent to your `/app/sub-centers/update` endpoint.
      $.ajax({
          url: this.action,
          type: 'post',
          data: formData,
          cache:false,
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