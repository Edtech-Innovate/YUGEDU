<?php if(isset($_GET['id'])){
  require '../../includes/db-config.php'; 
  $center_code = $conn->query("SELECT Center_Suffix FROM Universities WHERE ID = '".$_GET['id']."'");
  $center_code = mysqli_fetch_assoc($center_code);
  //die('heree');
?>
  <!-- Modal -->
  <div class="modal-header clearfix text-left">
    <button aria-label="" type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="pg-icon">close</i>
    </button>
    <h5>Center Code</h5>
  </div>
  <form role="form" id="form-add-center-code" action="/app/universities/store-center-code" method="POST">
    <div class="modal-body">
      <div class="row">
        <div class="col-md-12">
          <div class="form-group form-group-default required">
            <label>Starting with</label>
            <input type="text" name="suffix" id="suffix" value="<?php print !empty($center_code['Center_Suffix']) ? $center_code['Center_Suffix'] : '' ?>" class="form-control" placeholder="ex: XYZ" required>
          </div>
          <!-- Real-time error message container -->
          <label id="suffix-error-msg" class="error" style="display: none; color: #f55753; font-size: 12px; margin-bottom: 10px;"></label>
        </div>
      </div>
      
      <div class="row" id="generated-center-code">
        <?php 
          if(!empty($center_code['Center_Suffix'])): echo '<p>Student ID: <b>'.$center_code['Center_Suffix'].'XXXX</b></p>'; endif;
        ?>
      </div>
    </div>
    <div class="modal-footer clearfix text-end">
      <div class="col-md-4 m-t-10 sm-m-t-10">
        <button aria-label="" type="submit" id="save-btn" class="btn btn-primary btn-cons btn-animated from-left">
          <span>Save</span>
          <span class="hidden-block">
            <i class="pg-icon">tick</i>
          </span>
        </button>
      </div>
    </div>
  </form>

  <script>
    var isSuffixUnique = true;
    var currentUniversityId = '<?php echo $_GET['id']; ?>';
    var ajaxRequest = null; // Used to cancel previous ongoing ajax requests

    $(function(){
      $('#form-add-center-code').validate({
        rules: {
          suffix: {required:true},
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
    });

    // Real-time check using 'input' event for better responsiveness
    $('#suffix').on('input', function(){
      var suffix = $(this).val().trim();
       console.log(suffix);
      
      // Update preview text dynamically
      if(suffix != ''){
        var character = 'XXXX';
        $('#generated-center-code').html('<p>Center Code: <b>'+suffix+character+'</b></p>');
      } else {
        $('#generated-center-code').html('');
      }

      if(suffix.length > 0){
        //console.log('here');
        // Abort any previous pending ajax request
        if(ajaxRequest != null) {
          ajaxRequest.abort();
        }

        ajaxRequest = $.ajax({
          url: '/app/universities/check-suffix', 
          type: 'POST',
          data: { suffix: suffix, id: currentUniversityId },
          dataType: 'json',
          success: function(response) {
            if(response.exists) {
              $('#suffix-error-msg').text('Already found! This suffix is already in use by another university.').show();
              $('#suffix').addClass('error');
              $('#suffix').closest('.form-control').addClass('has-error');
              $('#save-btn').prop('disabled', true);
              isSuffixUnique = false;
            } else {
              $('#suffix-error-msg').hide();
              $('#suffix').removeClass('error');
              $('#suffix').closest('.form-control').removeClass('has-error');
              $('#save-btn').prop('disabled', false);
              isSuffixUnique = true;
            }
          }
        });
      } else {
        if(ajaxRequest != null) { ajaxRequest.abort(); }
        $('#suffix-error-msg').hide();
        $('#suffix').removeClass('error');
        $('#suffix').closest('.form-control').removeClass('has-error');
        $('#save-btn').prop('disabled', false);
        isSuffixUnique = true;
      }
    });

    $("#form-add-center-code").on("submit", function(e){
      e.preventDefault(); 
      
      if(!isSuffixUnique) {
        notification('danger', 'Please fix the duplicate center suffix before saving.');
        return;
      }

      if($('#form-add-center-code').valid()){
        var formData = new FormData(this);
        formData.append('id', currentUniversityId);
        $.ajax({
          url: this.action,
          type: 'post',
          data: formData,
          cache: false,
          contentType: false,
          processData: false,
          dataType: "json",
          success: function(data) {
            $('.modal').modal('hide');
            $('#universities-table').DataTable().ajax.reload(null, false);
            if(data.status == 200){
              notification('success', data.message);
            } else {
              notification('danger', data.message);
            }
          }
        });
      }
    });
  </script>
<?php } ?>