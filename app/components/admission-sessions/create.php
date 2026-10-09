<?php if(isset($_GET['university_id'])){
  require '../../../includes/db-config.php';
?>
  <!-- Modal -->
  <link href="/assets/plugins/bootstrap-datepicker/css/datepicker3.css" rel="stylesheet" type="text/css" media="screen">
  <div class="modal-header clearfix text-left">
    <button aria-label="" type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="pg-icon">close</i>
    </button>
    <h6 class="text-black font-weight-bold">Add <span class="semi-bold">Admission Session</span></h6>
  </div>
  <form role="form" id="form-add-admission-sessions" action="/app/components/admission-sessions/store" method="POST">
    <div class="modal-body">
      <div class="row">
        <div class="col-md-6">
          <div class="form-group form-group-default form-group-default-select required">
            <label>Month</label>
            <select name="month" class="form-control">
              <option value="">Select Month</option>
              <option value="Jan">Jan</option>
              <option value="Feb">Feb</option>
              <option value="Mar">Mar</option>
              <option value="Apr">Apr</option>
              <option value="May">May</option>
              <option value="Jun">Jun</option>
              <option value="Jul">Jul</option>
              <option value="Aug">Aug</option>
              <option value="Sep">Sep</option>
              <option value="Oct">Oct</option>
              <option value="Nov">Nov</option>
              <option value="Dec">Dec</option>
            </select>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group form-group-default form-group-default-select required">
            <label>Year</label>
            <select name="year" class="form-control">
              <option value="">Select Year</option>
              <?php
              for ($year = 2015; $year <= 2035; $year++) {
                echo "<option value='$year'>$year</option>";
              }
              ?>
            </select>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <div class="form-group form-group-default required">
            <label>Scheme</label>
            <select class="full-width" style="border: transparent;" onchange="getSchemeDate()" id="scheme" name="scheme[]" multiple>
              <option value="">Choose</option>
              <?php
                $schemes = $conn->query("SELECT ID, Schemes.Name FROM Schemes WHERE Schemes.Status = 1 AND University_ID = ".$_GET['university_id']."");
                while($scheme = $schemes->fetch_assoc()) { ?>
                  <option value="<?=$scheme['ID']?>"><?=$scheme['Name']?></option>
              <?php } ?>
            </select>
          </div>
        </div>
      </div>
      <div id="dates">
        
      </div>
    </div>
    <div class="modal-footer clearfix text-end">
      <div class="col-md-12 m-t-10 sm-m-t-10">
        <!--<button aria-label="" type="submit" class="btn btn-primary btn-cons btn-animated from-left">-->
        <!--  <span>Save</span>-->
        <!--  <span class="hidden-block">-->
        <!--    <i class="pg-icon">tick</i>-->
        <!--  </span>-->
        <!--</button>-->
        <button aria-label="" type="button" data-dismiss="modal" class=" btn btn-secondary mr-2">
        <i class="ti ti-circle-dashed-x mr-2"></i> Close</button>
        <button aria-label="" type="submit" class="btn btn-primary ">
        <i class="ti ti-circle-check mr-2"></i> Save</button>
      </div>
    </div>
  </form>
  <script type="text/javascript" src="/assets/plugins/jquery-inputmask/jquery.inputmask.min.js"></script>
  <script src="/assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js" type="text/javascript"></script>
  <script>
    $(function(){
      $("#scheme").select2({
        placeholder: 'Choose',
        allowClear: true
      })

      $('#form-add-admission-sessions').validate({
        rules: {
          month: {
            required: true
          },
          year: {
            required: true
          },
          exam_session: {required:true},
          'scheme[]': {required:true},
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

    function getSchemeDate(){
      $("#dates").html('');
      var schemeIds = $('#scheme').val();
      var names = $('#scheme option:selected').toArray().map(item => item.text)
      for(var i = 0; i < schemeIds.length; i++){
        $("#dates").append('<div class="row"><div class="col-md-12 d-flex justify-content-between">\
          <div class="m-t-10"><b>'+names[i]+'</b></div>\
          <div>\
            <div class="form-group form-group-default required">\
              <label>Start Date</label>\
              <input type="text" name="start_date['+schemeIds[i]+']" id="start_date_'+i+'" class="form-control" placeholder="ex: dd-mm-yyyy" required>\
            </div>\
          </div>\
        </div></div>');

        $("#start_date_"+i).mask("99-99-9999");
        $('#start_date_'+i).datepicker({
          format: 'dd-mm-yyyy',
          autoclose: true
        });
      }
    }

    $("#form-add-admission-sessions").on("submit", function(e){
      if($('#form-add-admission-sessions').valid()){
        $(':input[type="submit"]').prop('disabled', true);
        var formData = new FormData(this);
        formData.append('university_id', '<?=$_GET['university_id']?>');
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
              $('#tableAdmissionSessions').DataTable().ajax.reload(null, false);
            }else{
              $(':input[type="submit"]').prop('disabled', false);
              notification('danger', data.message);
            }
          }
        });
        e.preventDefault();
      }
    });
  </script>
<?php } ?>
