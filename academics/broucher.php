<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-top.php'); ?>
<style>
  table thead{
    background: #c5cfca ;
  }
  table thead tr th {
    color: #4b4b4b !important;
    font-weight: bold !important;
  }
  #universities-search-table{
      border-radius: 10px !important;
  }
</style>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-bottom.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/menu.php'); ?>
    <!-- START PAGE-CONTAINER -->
    <div class="page-container ">
    <?php include($_SERVER['DOCUMENT_ROOT'].'/includes/topbar.php'); ?>      
      <!-- START PAGE CONTENT WRAPPER -->
      <div class="page-content-wrapper ">
        <!-- START PAGE CONTENT -->
        <div class="content ">
          <!-- START JUMBOTRON -->
          <div class="jumbotron" data-pages="parallax">
            <div class=" container-fluid   sm-p-l-0 sm-p-r-0">
              <div class="inner">
                <!-- START BREADCRUMB -->
                <ol class="breadcrumb d-flex flex-wrap justify-content-between align-self-start">
                  <?php $breadcrumbs = array_filter(explode("/", $_SERVER['REQUEST_URI']));
                    for($i=1; $i<=count($breadcrumbs); $i++) {
                      if(count($breadcrumbs)==$i): $active = "active";
                        $crumb = explode("?", $breadcrumbs[$i]);
                        echo '<li class="breadcrumb-item '.$active.'">'.$crumb[0].'</li>';
                      endif;
                    }
                  ?>
                  <div class="text-end">
                   <div class="text-end">
                <!--<span class="text-muted bold cursor-pointer  "  aria-label="" title="" data-toggle="tooltip" data-original-title="Add Verticals" onclick="add('universities','lg')">-->
                <!--  <i class="ti ti-copy-plus add_btn_form" style="font-size:24px"></i>-->
                <!--  </span>-->
              </div>
                  </div>
                </ol>
                <!-- END BREADCRUMB -->
                
              </div>
            </div>
          </div>
          <!-- END JUMBOTRON -->
          <!-- START CONTAINER FLUID -->
          <div class=" container-fluid">
            <!-- BEGIN PlACE PAGE CONTENT HERE -->

            <div class="card card-transparent">
              <div class="card-header">
                <div class="pull-right">
                  <div class="col-xs-12">
                    <input type="text" id="universities-search-table" class="form-control pull-right" placeholder="Search">
                  </div>
                </div>
                <div class="clearfix"></div>
              </div>
              <div class="card-body">
                <div class="table-bordered">
                  <table class="table table-hover nowrap table-responsive mt-0" id="universities-table" style="margin-top:0px !important;">
                    <thead>
                      <tr>
                    <th data-orderable="false">Logo</th>
                    <th>Name</th>
                    <th>Vertical</th>
                    <th data-orderable="false">Action</th>
                  </tr>
                </thead>
                  </table>
                </div>
              </div>
            </div>

            <!-- END PLACE PAGE CONTENT HERE -->
          </div>
          <!-- END CONTAINER FLUID -->
        </div>
        <!-- END PAGE CONTENT -->
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-top.php'); ?>
<script type="text/javascript">
  $(function(){
    
      var table = $('#universities-table');

      var settings = {
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'ajax': {
          'url':'/app/universities/server'
        },
        'columns': [  
          { data: "Logo",width: "15%",
            "render": function(data, type, row){
              return '<img src="'+data+'" width="60px" />'
            }
          },
          { data: "Short_Name",width: "15%",},
          { data: "Vertical",width: "15%",},
          { data: "ID",width: "15%",
            "render": function(data, type, row){
                if(row.broucher!="" && row.broucher!=null){
                    return '<div class="button-list text-end">\
                        <a href="'+row.broucher+'"><i class="ti ti-download cursor-pointer add_btn_form h5" data-toggle="tooltip" title="Download Broucher"></i></a>\
                      </div>';
                }
              return "";
            }
          },
        ],
        "sDom": "<t><'row'<p i>>",
        "destroy": true,
        "scrollCollapse": true,
        "oLanguage": {
            "sLengthMenu": "_MENU_ ",
            "sInfo": "Showing <b>_START_ to _END_</b> of _TOTAL_ entries"
        },
        "aaSorting": [],
        "iDisplayLength": 25
      };

      table.dataTable(settings);

      // search box for table
      $('#universities-search-table').keyup(function() {
          table.fnFilter($(this).val());
      });
      $('#universities-table').on('draw.dt', function () {
      $('[data-toggle="tooltip"]').tooltip();
      });
    
  })
</script>

<script type="text/javascript">
  function changeColumnStatus(id, column) {
    $.ajax({
      url: '/app/universities/status',
      type: 'post',
      data:{ id:id, column:column },
      dataType:'json',
      success: function(data) {
        if(data.status==200){
          notification('success', data.message);
          $('#universities-table').DataTable().ajax.reload(null, false);
        }else{
          notification('danger', data.message);
          $('#universities-table').DataTable().ajax.reload(null, false);
        }
      }
    })
  }

  function addStudentID(id){
    $.ajax({
      url: '/app/universities/student-id?id='+id,
      type: 'GET',
      success: function(data) {
        $('#lg-modal-content').html(data);
        $('#lgmodal').modal('show');
      }
    })
  }
  function addBroucher(id){
    $.ajax({
      url: '/app/universities/broucher?id='+id,
      type: 'GET',
      success: function(data) {
        $('#lg-modal-content').html(data);
        $('#lgmodal').modal('show');
      }
    })
  }

  function addCenterCode(id){
    $.ajax({
      url: '/app/universities/center-code?id='+id,
      type: 'GET',
      success: function(data) {
        $('#lg-modal-content').html(data);
        $('#lgmodal').modal('show');
      }
    })
  }
</script>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-bottom.php'); ?>
        