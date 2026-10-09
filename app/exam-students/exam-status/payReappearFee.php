<?php if (isset($_POST['ids']) && isset($_POST['amount']) && isset($_POST['center']) && isset($_POST['payfor'])) {
  $ids = intval($_POST['ids']);
  $amount = intval($_POST['amount']);
  $center = intval($_POST['center']);
  $payfor = intval($_POST['payfor']);
?>
  
<!-- Modal -->
<div class="modal-header clearfix text-left">
    <button aria-label="" type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="pg-icon">close</i>
    </button>
    <h5>Exam <span class="semi-bold">Re-Appear Fee</span></h5>
</div>
<form role="form" id="form-add-offline-payments" action="/app/wallet-payments/store_reAppear_amount" method="POST" enctype="multipart/form-data">
    <div class="modal-body"> 
        <div class="row">
        <div class="col-md-6">
            <div class="form-group form-group-default required">
            <label>Payable Amount</label>
            <input type="number" min="1" name="amount" id="amount" readonly value="<?= $amount ?>" class="form-control" placeholder="ex: 50000" required>
            </div>
        </div>
        </div>
    </div>
    <div class="modal-footer clearfix text-end">
        <div class="col-md-4 m-t-10 sm-m-t-10">
            <button aria-label="" type="submit" class="btn btn-primary btn-cons btn-animated from-left">
                <span>Pay</span>
                <span class="hidden-block"><i class="pg-icon">tick</i></span>
            </button>
        </div>
    </div>
</form>


<script>

$(function() {
    $('#form-add-offline-payments').validate({
    rules: {
        amount: {required: true},
    },
    highlight: function(element) {
        $(element).addClass('error');
        $(element).closest('.form-control').addClass('has-error');
    },
    unhighlight: function(element) {
        $(element).removeClass('error');
        $(element).closest('.form-control').removeClass('has-error');
    }
    });
});


$("#form-add-offline-payments").on("submit", function(e) {
    if ($('#form-add-offline-payments').valid()) {
        var formData = new FormData(this);
        formData.append('ids', '<?=$ids?>');
        formData.append('amount', '<?=$amount?>');
        formData.append('payfor','<?=$payfor?>');
        $.ajax({
            url: this.action,
            type: 'post',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function(data) {
                if (data.status == 200) {
                    $('.modal').modal('hide');
                    notification('success', data.message);
                    examCompleted.DataTable().ajax.reload(null,false);
                } else {
                    notification('danger',data.message);
                }
            }
        });
    e.preventDefault();
    }
});

</script>
<?php } ?>
