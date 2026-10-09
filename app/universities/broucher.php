<?php if(isset($_GET['id'])){
  require '../../includes/db-config.php'; 
  $student_id = $conn->query("SELECT broucher FROM Universities WHERE ID = '".$_GET['id']."'");
  $student_id = mysqli_fetch_assoc($student_id);
//   print_r($student_id);die;
?>
  <!-- Modal -->
  <div class="modal-header clearfix text-left">
    <button aria-label="" type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="pg-icon">close</i>
    </button>
    <h5>Broucher</h5>
  </div>
  <form role="form" id="form-add-student-id" action="/app/universities/store-broucher" method="POST" enctype="multipart/form-data">
    <div class="modal-body">
      <div class="row">
        <div class="col-md-12">
          <div class="form-group form-group-default required">
            <label>Upload</label>
            <input type="file" name="broucher" id="broucher" class="form-control" required>
          </div>
          <?php if($student_id['broucher']){
            ?>
            <a href="<?php echo $student_id['broucher']?>" target="_blank">View</a>
            <?php
          }?>
          
        </div>
      </div>
    </div>
    <div class="modal-footer clearfix text-end">
      <div class="col-md-4 m-t-10 sm-m-t-10">
        <button aria-label="" type="submit" class="btn btn-primary btn-cons btn-animated from-left">
          <span>Save</span>
          <span class="hidden-block">
            <i class="pg-icon">tick</i>
          </span>
        </button>
      </div>
    </div>
  </form>

  <script>
    $(function(){
      $('#form-add-student-id').validate({
        rules: {
          broucher: {required:true},
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

    function showStudentID(){
      var suffix = $('#suffix').val();
      var value = $('#character').val();
      if(value>=4 && value<=10 && suffix!=''){
        var character = 'X';
        $('#generated-student-id').html('<p>Student ID: <b>'+suffix+character.repeat(value)+'</b></p>');
      }else{
        $('#generated-student-id').html('');
      }
      
    }

    $("#form-add-student-id").on("submit", function(e){
      if($('#form-add-student-id').valid()){
        var formData = new FormData(this);
        formData.append('id', '<?=$_GET['id']?>');
        $.ajax({
          url: this.action,
          type: 'post',
          data: formData,
          cache:false,
          contentType: false,
          processData: false,
          dataType: "json",
          success: function(data) {
            $('.modal').modal('hide');
            $('#universities-table').DataTable().ajax.reload(null, false);
            if(data.status==200){
              notification('success', data.message);
            }else{
              notification('danger', data.message);
            }
          }
        });
        e.preventDefault();
      }
    });
  </script>
<?php } ?>
