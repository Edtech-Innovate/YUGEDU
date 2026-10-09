<?php
require '../../includes/db-config.php';
session_start();

/* DEBUG (keep ON until confirmed working) */
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_GET['id'])) {
    die('ID missing');
}

$id = (int)$_GET['id'];

/* FETCH STATUS FROM center_verfiy1 */
$sql = "SELECT status FROM center_verfiy1 WHERE id = $id LIMIT 1";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die(mysqli_error($conn));
}

$currentStatus = 0;
if ($row = mysqli_fetch_assoc($result)) {
    $currentStatus = (int)$row['status'];
}

$statuses = [
    0 => 'Pending',
    1 => 'Approved',
    2 => 'Rejected'
];
?>

<div class="modal-header bg-light">
    <h5 class="modal-title text-primary">
        Manage Application Status
    </h5>
    <button type="button" class="close" data-dismiss="modal">&times;</button>
</div>

<div class="modal-body">
<form id="statusForm">
    <input type="hidden" name="id" value="<?= $id ?>">

    <div class="form-group">
        <label>Select Status</label>
        <select name="status" class="form-control">
            <?php foreach ($statuses as $key => $label): ?>
                <option value="<?= $key ?>" <?= $key === $currentStatus ? 'selected' : '' ?>>
                    <?= $label ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="text-right">
        <button class="btn btn-sm btn-primary">
            Update Status
        </button>
    </div>
</form>

<div id="loader" style="display:none;text-align:center">
    <div class="spinner-border text-primary"></div>
</div>
</div>

<script>
$('#statusForm').on('submit', function(e){
    e.preventDefault();

    $('#loader').show();

    $.ajax({
        url: "/app/center-verify/status",
        type: "POST",
        data: $(this).serialize(),
        dataType: "json",
        success: function(res){
            $('#loader').hide();
            if(res.status === 200){
                notification('success', res.message);
                $('#lgmodal').modal('hide');
                $('#users-table').DataTable().ajax.reload(null,false);
            } else {
                notification('danger', res.message);
            }
        },
        error: function(){
            $('#loader').hide();
            notification('danger', 'Request failed');
        }
    });
});
</script>
