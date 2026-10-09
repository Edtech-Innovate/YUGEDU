<?php 

## Database configuration
include '../../includes/db-config.php';
session_start();

$notification_details = [];
if(isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
    $id = mysqli_real_escape_string($conn,$_REQUEST['id']);
    $notification = $conn->query("SELECT Notification_Heading.Name as `regarding` , Notifications_Generated.Content as `Message` , Notifications_Generated.Attachment as `attachment` , JSON_UNQUOTE(JSON_EXTRACT(Notifications_Generated.published_on,'$[0].published')) as `send_on` FROM `Notifications_Generated` LEFT JOIN Notification_Heading ON Notification_Heading.ID = Notifications_Generated.Heading WHERE Notifications_Generated.ID = '$id'");
    $notification_details = mysqli_fetch_assoc($notification);
} 

?>
<div class="card">
    <div class="card-header seperator d-flex justify-content-between">
        <h5 class="fw-bold mb-0">View Notification</h5>
    </div>
    <div class="card-body">
        <div class="d-flex justify-content-between mb-2" id="show-notification">
            <span>
                <span class="fw-bold">Regarding : </span> <?= ucfirst(strtolower($notification_details['regarding'])) ?>
            </span>
            <span class="me-auto"><span class="fw-bold">Date :</span>
                <?= date_format(date_create($notification_details['send_on']),"d-M-Y") ?>
            </span>
        </div>
        <p>
            <span class="fw-bold">Message: </span>
            <?= $notification_details['Message'] ?>
        </p>
        <?php if (!empty($notification_details['attachment'])) { ?>
            <a href="<?= $notification_details['attachment'] ?>" target="_blank" download="<?= $notification_details['regarding'] ?>" class="btn badge badge-success p-1 "><i class="uil uil-down-arrow  mt-1 mr-2"></i><span style="font-size:12px;">Download</span></a>
        <?php } ?>
    </div>
</div>