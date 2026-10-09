<?php if (isset($_GET['id'])) {
  require '../../../includes/db-config.php';
  $paySlipArr = $conn->query("SELECT id, university_id,`suffix`, `character` FROM pay_slip_suffix WHERE id = '" . $_GET['id'] . "'");
  $paySlipArr = mysqli_fetch_assoc($paySlipArr);
  ?>
  <!-- Modal -->
  <div class="modal-header clearfix text-left">
    <button aria-label="" type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="pg-icon">close</i>
    </button>
    <h6>Edit <span class="semi-bold">Pay Slip Serial No.</span></h6>
  </div>
  <form role="form" id="form-edit-pay-slips" action="/app/components/pay-slips/update" method="POST">
    <div class="modal-body">
      <div class="row">
        <div class="col-md-12">
          <div class="form-group form-group-default required">
            <label>Starting with</label>
            <input type="text" name="suffix" id="suffix" onkeyup="showStudentID()"
              value="<?php print !empty($paySlipArr['suffix']) ? $paySlipArr['suffix'] : '' ?>" class="form-control"
              placeholder="ex: XYZ" required>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <div class="form-group form-group-default required">
            <label>No. of Characters</label>
            <input type="number" min="4" max="10" onkeyup="showStudentID()" name="character" id="character"
              value="<?php print !empty($paySlipArr['character']) ? $paySlipArr['character'] : '' ?>" class="form-control"
              placeholder="ex: 5" required>
          </div>
        </div>
      </div>

      <div class="row" id="generated-student-id">
        <?php
        if (!empty($paySlipArr['suffix'])):
          echo '<p>Serial No.: <b>' . $paySlipArr['suffix'] . str_repeat('X', $paySlipArr['character']) . '</b></p>';
        endif;
        ?>
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
    $(function () {
      $('#form-edit-pay-slips').validate({
        rules: {
          suffix: { required: true },
          character: { required: true, max: 10, min: 4 },
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
    function showStudentID() {
      var suffix = $('#suffix').val();
      var value = $('#character').val();
      if (value >= 4 && value <= 10 && suffix != '') {
        var character = 'X';
        $('#generated-student-id').html('<p>Serial No.: <b>' + suffix + character.repeat(value) + '</b></p>');
      } else {
        $('#generated-student-id').html('');
      }

    }
    $("#form-edit-pay-slips").on("submit", function (e) {
      if ($('#form-edit-pay-slips').valid()) {
        $(':input[type="submit"]').prop('disabled', true);
        var formData = new FormData(this);
        formData.append('university_id', '<?= $paySlipArr['university_id'] ?>');
        formData.append('id', '<?= $paySlipArr['id'] ?>');
        $.ajax({
          url: this.action,
          type: 'post',
          data: formData,
          cache: false,
          contentType: false,
          processData: false,
          dataType: "json",
          success: function (data) {
            if (data.status == 200) {
              $('.modal').modal('hide');
              notification('success', data.message);
              $('#tablePaySlips').DataTable().ajax.reload(null, false);
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
<?php } ?>