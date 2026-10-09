<?php 
require '../../includes/db-config.php';
session_start();
?>

<!-- Modal -->
<div class="modal-header clearfix text-left">
  <button aria-label="" type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="pg-icon">close</i>
  </button>
  <h5>Create<span class="semi-bold"> Certificate</span></h5>
</div>

  <form role="form" id="form-add-e-book" action="#" method="POST" enctype="multipart/form-data">
  <div class="modal-body">
    
    <div class="row">
        <div class="col-md-12">
            <div class="form-group form-group-default required">
            <label>Student</label>
            <select class="full-width" style="border: transparent;" id="student_id" name="student_id">
                <option value="">Select</option>
                <?php
                $programs = $conn->query("SELECT * FROM Students  where University_ID=41");
                while ($program = $programs->fetch_assoc()) { ?>
                <option value="<?=$program['ID']?>">
                    <?=$program['First_Name'].$program['Middle_Name'].$program['Last_Name'].' ('.$program['Unique_ID'].')'?>
                </option>
            <?php } ?>
            </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="form-group form-group-default required">
            <label>Background Template</label>
            <select class="full-width" style="border: transparent;" id="background_type" name="background_type">
                <option value="with_background">With Background</option>
                <option value="no_background">No Background</option>
            </select>
            </div>
        </div>
    </div>

  </div>

  <div class="modal-footer clearfix justify-content-center">
    <div class="col-md-4 m-t-10 sm-m-t-10">
      <a class="btn btn-primary btn-cons btn-animated from-left" href="#" onClick="view();">Save</a>
    </div>
  </div>
</form>

<script>
    // function view(){
    //     var student_id = $('#student_id').val();
    //     var background_type = $('#background_type').val();
        
    //     if(student_id==undefined || student_id==""){
    //         notification('danger', 'Please select student to proceed!');
    //         return false;
    //     }else{
    //         var request = $.ajax({
    //             url: "/app/certificates/certificate-view",
    //             type: "POST",
    //             data: {
    //                 student_id: student_id,
    //                 background_type: background_type
    //             },
    //             dataType: "json",
    //             success: function(data) {
    //                 if(data.status == 200) {
    //                     notification('success', data.message);
    //                     $('.modal').modal('hide');
    //                 } else {
    //                     notification('danger', data.message);
    //                 }
    //                 $('#e_books-table').DataTable().ajax.reload(null, false);
    //             },
    //             error: function(data) {
    //                 notification('danger', 'Server is not responding. Please try again later');
    //             }
    //         });
    //     }
    // }
function view(){
    var student_id = $('#student_id').val();
    var background_type = $('#background_type').val();
    
    if(student_id==undefined || student_id==""){
        notification('danger', 'Please select student to proceed!');
        return false;
    }else{
        var request = $.ajax({
            url: "/app/certificates/certificate-view",
            type: "POST",
            data: {
                student_id: student_id,
                background_type: background_type
            },
            dataType: "json",
            success: function(data) {
                if(data.status == 200) {
                    notification('success', data.message);
                    $('.modal').modal('hide');
                    
                    // Download the certificate file
                    window.open(data.file_url, '_blank');
                    
                } else {
                    notification('danger', data.message);
                }
                $('#e_books-table').DataTable().ajax.reload(null, false);
            },
            error: function(xhr, status, error) {
                notification('danger', 'Server is not responding. Please try again later');
                console.error('AJAX Error:', error);
            }
        });
    }
}
</script>