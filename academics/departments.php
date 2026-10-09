<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-top.php'); ?>
<style>
   table thead{
    background: #c5cfca ;
  }
  table thead tr th {
    color: #4b4b4b !important;
    font-weight: bold !important;
  }
  #departments-search-table{
      border-radius:10px !important;
  }
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
        <div class=" container-fluid   sm-p-l-0 sm-p-r-0">
          <div class="inner">
            <!-- START BREADCRUMB -->
            <ol class="breadcrumb d-flex flex-wrap justify-content-between align-self-start">
              <?php $breadcrumbs = array_filter(explode("/", $_SERVER['REQUEST_URI']));
              for ($i = 1; $i <= count($breadcrumbs); $i++) {
                if (count($breadcrumbs) == $i) : $active = "active";
                  $crumb = explode("?", $breadcrumbs[$i]);
                  echo '<li class="breadcrumb-item ' . $active . '">' . $crumb[0] . '</li>';
                endif;
              }
              ?>
              <div>
                  <?php
                    if($_SESSION['Role']=="Administrator" || $_SESSION['Role'] == 'University Head')
                    { ?>
                        <button class="btn btn-link " aria-label="" title="" data-toggle="tooltip" data-original-title="Add Department" onclick="add('departments','md')"><i class="ti ti-copy-plus add_btn_form  add_btn_form" style="font-size:24px"></i></button>
              
                    <?php }
                  ?>
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
                <input type="text" id="departments-search-table" class="form-control pull-right" placeholder="Search">
              </div>
            </div>
            <div class="clearfix"></div>
          </div>
          <div class="card-body">
            <div class="table-bordered">
              <table class="table table-hover nowrap table-responsive" style="margin-top:0px !important" id="departments-table">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th data-orderable="false">Vertical</th>
                    <th data-orderable="false">Status</th>
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
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-top.php'); ?>
    <script type="text/javascript">
      $(function() {
        var role = '<?= $_SESSION['Role'] ?>';
        var show = (role == 'Administrator' || role == 'University Head') ? true : false;
        var table = $('#departments-table');

        var settings = {
          'processing': true,
          'serverSide': true,
          'serverMethod': 'post',
          'ajax': {
            'url': '/app/departments/server'
          },
          'columns': [{
              data: "Name",
              width: (role === "University Head" || role === "Academic Head" || role=== "Operations") ? "100%" : "40%"
            },
            {
              data: "University",width:"40%",
              visible: show
            },
            {
              data: "Status", width: (role === "University Head" || role === "Academic Head" || role=== "Operations") ? "100%" : "40%",
              "render": function(data, type, row) {
                var active = data == 1 ? 'Active' : 'Inactive';
                var checked = data == 1 ? 'checked' : '';
                return '<div class="form-check form-check-inline switch switch-lg success">\
                        <input onclick="changeStatus(&#39;Departments&#39;, &#39;' + row.ID + '&#39;)" type="checkbox" ' + checked + ' id="status-switch-' + row.ID + '">\
                        <label for="status-switch-' + row.ID + '">' + active + '</label>\
                      </div>';
              }
            },
            {
              data: "ID",width:"25%",
              "render": function(data, type, row) {
                return '<div class="button-list text-end">\
                <i class="ti ti-edit-circle add_btn_form h5 cursor-pointer" onclick="edit(&#39;departments&#39;, &#39;' + data + '&#39, &#39;md&#39;)" data-toggle="tooltip" title="Edit Department"></i>\
                <i class="ti ti-trash-x cursor-pointer add_btn_form h5" onclick="destroy(&#39;departments&#39;, &#39;' + data + '&#39)" data-toggle="tooltip" title="Delete Department"></i>\
              </div>'
              },
              visible:show
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
          "iDisplayLength": 10
        };

        table.dataTable(settings);

        // search box for table
        $('#departments-search-table').keyup(function() {
          table.fnFilter($(this).val());
        });
        $('#departments-table').on('draw.dt', function () {
        $('[data-toggle="tooltip"]').tooltip();
       });
      })
    </script>

    <script type="text/javascript">
      function changeColumnStatus(id, column) {
        $.ajax({
          url: '/app/departments/status',
          type: 'post',
          data: {
            id: id,
            column: column
          },
          dataType: 'json',
          success: function(data) {
            if (data.status == 200) {
              notification('success', data.message);
              $('#departments-table').DataTable().ajax.reload(null, false);
            } else {
              notification('danger', data.message);
              $('#departments-table').DataTable().ajax.reload(null, false);
            }
          }
        })
      }
    </script>

    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>
