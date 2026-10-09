<!-- Modal -->
<div class="modal-header clearfix text-left">
  <button aria-label="" type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="pg-icon">close</i>
  </button>
  <h5 class="font-weight-bold text-black">Upload Subjects</h5>
</div>
<form role="form" id="form-upload" foemtarget="_blank" action="/app/subjects/store" method="POST" enctype="multipart/form-data">
  <div class="modal-body">
    
    <div class="row">
      <div class="col-md-12 text-end cursor-pointer text-end mb-3  d-flex justify-content-end " onclick="window.open('/app/samples/subjects');">
          <div class="add_btn_form" style="width: max-content !important;">
        <i class="uil uil-file-download-alt"></i><u><span class=" ml-1">Sample</span></u></div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-12">
          <div class="form-group form-group-default">
        <input name="file" type="file" accept="image/*, .pdf, .csv, .doc, .docx, application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" />
     </div>
      </div>
    </div>
  </div>
  <div class="modal-footer clearfix text-end">
    <div class="col-md-12 m-t-10 sm-m-t-10">
      <!--<button aria-label="" type="submit" id="submit-button" class="btn btn-primary btn-cons btn-animated from-left">-->
      <!--  <span>Upload</span>-->
      <!--  <span class="hidden-block">-->
      <!--    <i class="uil uil-upload"></i>-->
      <!--  </span>-->
      <!--</button>-->
       <div class="modal-footer clearfix text-end">
      <button aria-label="" type="button" data-dismiss="modal" class=" btn btn-secondary mr-2">
        <i class="ti ti-circle-dashed-x mr-2"></i> Close</button>
      <button aria-label="" type="submit" class="btn btn-primary ">
        <i class="ti ti-circle-check mr-2"></i> Upload</button>
  </div>
    </div>
  </div>
</form>

<script>

  $(function(){
    $('#form-upload').validate({
      rules: {
        file: {required:true},
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

  $('#submit-button').click(function() {
    $('.modal').modal('hide');
  });
</script>
