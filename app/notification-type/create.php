<style>

.topBody{
    margin: 1rem 1rem 1rem 2rem; 
}
</style>
<div class="topBody">
    <div class="d-flex justify-content-between align-items-center">
        <h5 class="font-weight-bold text-black">Add Notification Type</h5>
        <button aria-label="" type="button" class="close" style="margin-top: 0.6rem;" data-dismiss="modal" aria-hidden="true"><i class="pg-icon">close</i>
        </button>
    </div>
</div>
<form role="form" id="form-add-notification-type" action="/app/notification-type/store" method="POST" enctype="multipart/form-data">
    <div class="modal-body">
        <div class = "row">
            <div class = "col-md-12">
                <div class = "form-group form-group-default required">
                <label>Notification Type</label>
                <input class="full-width" style="border: transparent;" id="notification_type" name="notification_type"></input>
            </div>
        </div>
    </div>
    <div class="text-end">
        <div class="m-t-10 sm-m-t-10">
            <!--<button aria-label="" type="submit" class="btn btn-primary btn-cons btn-animated from-left">-->
            <!--    <span>Save</span>-->
            <!--    <span class="hidden-block"><i class="pg-icon">tick</i></span>-->
            <!--</button>-->
            <button aria-label="" type="button" data-dismiss="modal" class=" btn btn-secondary mr-2">
            <i class="ti ti-circle-dashed-x mr-2"></i> Close</button>
            <button aria-label="" type="submit" class="btn btn-primary ">
            <i class="ti ti-circle-check mr-2"></i> Save</button>
        </div>
    </div>
</form>

<script type = "text/javascript">

$(function(){
    $('#form-add-notification-type').validate({
        rules: {
            notification_type: {
                required:true
            },
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

$("#form-add-notification-type").on("submit", function(e){
    if($('#form-add-notification-type').valid()){
        $(':input[type="submit"]').prop('disabled', true);
        var formData = new FormData(this);
        $.ajax({
            url : this.action,
            type : 'post',
            data : formData,
            cache :false ,
            contentType : false,
            processData : false,
            dataType : "json",
            success: function(data) {
                if (data.status==200){
                    $('.modal').modal('hide');
                    notification('success', data.message);
                } else {
                    $(':input[type="submit"]').prop('disabled', false);
                    notification('danger', data.message);
                }
            }
        });
        e.preventDefault();
    }
});

</script>