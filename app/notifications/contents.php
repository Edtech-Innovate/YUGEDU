<?php

## Database configuration
include '../../includes/db-config.php';
session_start();

if(isset($_GET['id'])){
  $id = mysqli_real_escape_string($conn,$_GET['id']);
  $notification = $conn->query("SELECT Notifications_Generated.Content as `content` , Notification_Heading.Name as `heading` FROM `Notifications_Generated` LEFT JOIN Notification_Heading ON Notification_Heading.ID = Notifications_Generated.Heading WHERE Notifications_Generated.ID = $id");
  $notification = mysqli_fetch_assoc($notification);
  $content = nl2br($notification['content']);
  $heading = $notification['heading'];
}

?>
<!-- Modal -->
<style>
.topBody{
    margin: 1rem 1rem 0.6rem 2rem; 
}
</style>
<div class="topBody">
    <div class="d-flex justify-content-between align-items-center">
        <h5>Regarding : <?=$heading?></h5>
        <button aria-label="" type="button" class="close" style="margin-top: 0.6rem;" data-dismiss="modal" aria-hidden="true"><i class="pg-icon">close</i>
        </button>
    </div>
</div>
<div class="topBody">
  <p><?=$content?></p>
</div>
