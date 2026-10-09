<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-top.php'); ?>
<link rel="stylesheet" href="/assets/css/new-style.css" />
<style>
.table>thead>tr>th {
  padding-left: 18px !important;
  color: black !important;
  font-weight: 900 !important;
}

.card-header {
  border-bottom: 1px solid rgba(0, 0, 0, .125) !important;
}

.card .card-header {
  padding: 4px 7px 4px 20px !important;
  min-height: 48px !important;
}

.card-body {
  padding: 1.25rem !important;
}
  .badge-success:hover{
    background-color: #19ad79 !important;}
</style>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-bottom.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/menu.php'); ?>
<!-- START PAGE-CONTAINER -->
<div class="page-container ">
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/topbar.php'); ?>
<!-- START PAGE CONTENT WRAPPER -->
<div class="page-content-wrapper ">
  <!-- START PAGE CONTENT -->
  <div class="content ">
    <!-- START JUMBOTRON -->
    <div class="jumbotron" data-pages="parallax">
      <div class=" container-fluid sm-p-l-0 sm-p-r-0">
        <div class="inner">
          <!-- START BREADCRUMB -->
          <ol class="breadcrumb d-flex flex-wrap justify-content-between align-self-start">
            <?php $breadcrumbs = array_filter(explode("/", $_SERVER['REQUEST_URI']));
            for ($i = 1; $i <= count($breadcrumbs); $i++) {
              if (count($breadcrumbs) == $i):
                $active = "active";
                $crumb = explode("?", $breadcrumbs[$i]);
                echo '<li class="breadcrumb-item ' . $active . '">' . $crumb[0] . '</li>';
              endif;
            }
            ?>
            <div></div>
          </ol>
          <!-- END BREADCRUMB -->
        </div>
      </div>
    </div>
    <!-- END JUMBOTRON -->
    <!-- START CONTAINER FLUID -->
    <div class=" container-fluid">
      <!-- BEGIN PlACE PAGE CONTENT HERE -->
      <div class="row">
        <div class="col-md-6">
          <div class="card custom-card">
            <div class="card-header">
              <div class="pull-right">
                <div class="col-xs-12">
                  <input type="text" id="notification-search" class="form-control pull-right custom_search_section my-2" placeholder="Search">
                </div>
              </div>
              <div class="clearfix"></div>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-hover nowrap" id="notification_table">
                  <thead>
                    <tr>
                      <th>Regarding</th>
                      <th>Sent To</th>
                      <th>Sent On</th>
                      <th>Content</th>
                      <th>Attachment</th>
                    </tr>
                  </thead>
                </table>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div id = "view_notification_box"></div>
        </div>
      </div>
      <!-- END PLACE PAGE CONTENT HERE -->
    </div>
    <!-- END CONTAINER FLUID -->
  </div>
  <!-- END PAGE CONTENT -->
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-top.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>

<script type="text/javascript">

var table = $('#notification_table');

var settings = {
  'processing': true,
  'serverSide': true,
  'serverMethod': 'post',
  'ajax': {
    'url': '/app/notifications/student-server', 
    'type': 'POST',
  },
  'columns': [{
      data: "heading",
    },{
      data: "send_to" ,
    },{
      data : "send_on" ,
    },{
      data: "Content", 
      render : function(data, type, row) {
        return '<button type="btn" class = "custom_pwp_btn p-1 px-2" onclick="viewNotification('+row.ID+');"><i class="ri-eye-fill"></i> view</button>';
      }
    },{
      data : "document" , 
      render : function(data,type,row) {
        return '<a href="'+data+'" target="_blank" download"  class="btn badge badge-success p-1 "><i class="uil uil-down-arrow  mt-1 mr-2"></i><span style="font-size:12px;">Download</span></a>';
      } 
    }
  ],
  "sDom": "<t><'row'<p i>>",
  "destroy": true,
  "scrollCollapse": true,
  "oLanguage": {
    "sLengthMenu": "_MENU_ ",
    "sInfo": "Showing <b>_START_ to _END_</b> of _TOTAL_ entries"
  },
  "aaSorting": [],
  "iDisplayLength": 5,
  "drawCallback": function( settings ) {
    $('[data-toggle="tooltip"]').tooltip();
  },
};

$(document).ready(function(){
  table.dataTable(settings);
});

$('#notification-search').keyup(function() {
  table.fnFilter($(this).val());
});

function viewNotification(id) {
  $.ajax({
    url: '/app/notifications/viewNotification',
    type: 'POST',
    data : {id},
    success: function (data) {
      document.getElementById("view_notification_box").innerHTML = data;
    }
  })
}
</script>