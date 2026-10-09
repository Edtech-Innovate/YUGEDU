<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-top.php'); ?>
<style>
  table thead{
    background: #c5cfca ;
  }
  table thead tr th {
    color: black !important;
  }
  #e-book-search-table{
      border-radius: 10px !important;
  }
</style>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-bottom.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/menu.php'); ?>

<div class="page-container ">
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/topbar.php'); ?>
  
  <div class="page-content-wrapper ">
    <div class="content ">
      <div class="jumbotron" data-pages="parallax">
        <div class=" container-fluid sm-p-l-0 sm-p-r-0">
          <div class="inner">
            <ol class="breadcrumb d-flex flex-wrap justify-content-between align-self-start">
              <?php
              $breadcrumbs = array_filter(explode("/", $_SERVER['REQUEST_URI']));
              for ($i = 1; $i <= count($breadcrumbs); $i++) {
                if (count($breadcrumbs) == $i) :
                  $active = "active";
                  $crumb = explode("?", $breadcrumbs[$i]);
                  $breadcrumbText = str_replace("-", " ", $crumb[0]);
                  echo '<li class="breadcrumb-item ' . $active . '">' . ucwords(strtolower($breadcrumbText)) . '</li>';
                endif;
              }
              ?>
              <button class="btn border-0 shadow-none" data-toggle="tooltip" data-original-title="Add Notes" onclick="add('notes','lg')"><i class="ti ti-copy-plus add_btn_form" style="font-size: 24px!important;"></i></button>

            </ol>

          </div>
        </div>
      </div>
     
      <div class=" container-fluid">
        <div class="card card-transparent">
          <div class="card-header">
            
           

            <div class="pull-right">
              <div class="row">
                <div class="col-xs-7" style="margin-right: 10px;">
                  <input type="text" id="e-book-search-table" class="form-control pull-right p-2 fw-bold " placeholder="Search">
                </div>
                <!--<div class="col-xs-5" style="margin-right: 10px;">
                  <button class="btn btn-primary p-2 "  data-toggle="tooltip" data-original-title="Add Notes" onclick="add('Notes','lg')"> <i class="uil uil-plus-circle"></i>Add</button>
                </div>-->
              </div>
            </div>
            <div class="clearfix"></div>
          </div>
          
          <div class="card-body">
            <div class="table-bordered px-3 py-2">
              <table class="table table-hover nowrap table-responsive" style="margin-top:0px !important;" id="e_books-table">
                <thead>

                <tr>
                  <th>Course</th>
                  <th>Subject</th>
                  <th>Type</th>
                  <th>Status</th>
                  <!--<th>Action</th>-->
                </tr>
                </thead>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- END PAGE CONTENT -->
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-top.php'); ?>
   

    <script type="text/javascript">
        $(function() {
            var role = '<?= $_SESSION['Role'] ?>';
            var show = role == 'Administrator' ? true : false;
            var table = $('#e_books-table');

            var settings = {
                'processing': true,
                'serverSide': true,
                'serverMethod': 'post',
                'ajax': {
                    'url': '/app/notes/data-list'
                },
                'columns': [{
                        data: "course_name",width: "35%",
                    },
                    {
                        data: "subject_name",width: "35%",
                    },
                    {
                        data: "file_type",width: "35%",
                    },
                    {
                       data: "status",
                       width: "35%",
                       "render": function(data, type, row) {
                       console.log(row);
                       var active = data == 1 ?
                      '<span class="font-weight-bold">Active</span>' :
                      '<span class="font-weight-bold">Inactive</span>';
                      var checked = data == 1 ? 'checked' : '';
                      return '<div class="form-check form-check-inline switch switch-lg success">\
                     <input onclick="changeStatus(' + "'notes'" + ', ' + row.ID + ',' + status + ')" type="checkbox" ' + checked + ' id="status-switch-' + row.ID + '">\
                     <label for="status-switch-' + row.ID + '">' + active + '</label>\
                     </div>';
                     }
                     },
                    // {
                    //   data: "ID",
                    //   "render": function(data, type, row) {
                    //     //   <i class="uil uil-edit icon-xs cursor-pointer" onclick="edit('+"'e_books'"+', '+data+','+"'status'"+',2)"></i>\
                    
                    // //     return '<div class="button-list text-end">\
                    // //         <i class="uil uil-trash icon-xs cursor-pointer" onclick="changeStatus('+"'e_books'"+', '+data+','+"'status'"+',2)"></i>\
                    // //   </div>'
                    //   },
                    //   visible:false
                     
                    // },
                ],
                "sDom": "<t><'row'<p i>>",
                "destroy": true,
                "scrollCollapse": true,
                "oLanguage": {
                    "sLengthMenu": "_MENU_ ",
                    "sInfo": "Showing <b>_START_ to _END_</b> of _TOTAL_ entries"
                },
                "aaSorting": [],
                "iDisplayLength": 10,
            };
            table.dataTable(settings);
            // search box for table
            $('#e-book-search-table').keyup(function() {
              table.fnFilter($(this).val());
            });
          })
    </script>

<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>
