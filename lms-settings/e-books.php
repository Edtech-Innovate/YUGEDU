<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-top.php'); ?>
<style>
  #e-book-search-table{
   border-radius: 10px !important;   
  }
  table thead{
    background: #c5cfca ;
  }
  table thead tr th {
    color: black !important;
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
                 if (count($breadcrumbs) == $i) : $active = "active";
                   $crumb = explode("?", $breadcrumbs[$i]);
                   echo '<li class="breadcrumb-item ' . $active . '">' . $crumb[0] . '</li>';
                 endif;
               }
              ?>
              <div>
                   <button class="btn border-0 shadow-none "  data-toggle="tooltip" data-original-title="Add E-books" onclick="add('e-books','lg')"> <i class="ti ti-copy-plus add_btn_form" style="font-size:24px;"></i></button>
               
              </div>
            </ol>
            
          </div> 
        </div>
      </div>
     
      <div class=" container-fluid">
        <div class="card card-transparent">
          <div class="card-header">
            
            <?php 
            //   $breadcrumbs = array_filter(explode("/", $_SERVER['REQUEST_URI']));
            //   for ($i = 1; $i <= count($breadcrumbs); $i++) {
            //     if (count($breadcrumbs) == $i) : $active = "active";
            //       $crumb = explode("?", $breadcrumbs[$i]);
            //       echo ucfirst($crumb[0]);
            //     endif;
            //   }
              ?>

            <div class="pull-right">
              <div class="row">
                <div class="col-xs-12" style="margin-right: 10px;">
                  <input type="text" id="e-book-search-table" class="form-control pull-right p-2 fw-bold" placeholder="Search">
                </div>
                <!--<div class="col-xs-5" style="margin-right: 10px;">-->
                <!--  <button class="btn btn-primary p-2 "  data-toggle="tooltip" data-original-title="Add E-books" onclick="add('e-books','lg')"> <i class="uil uil-plus-circle"></i>Add</button>-->
                <!--</div>-->
              </div>
            </div>
            <div class="clearfix"></div>
          </div>
          
          <div class="card-body">
            <div class="table-bordered px-3 py-2">
              <table class="table table-hover nowrap table-responsive" style="margint-top:0px !important;" id="e_books-table">
                <thead>

                <tr>
                  <th>Subject</th>
                  <th>Duration</th>
                  <th>Course</th>
                  <th>Sub-Course</th>
                  <th>Type</th>
                  <th>Status</th>
                  <th>Action</th>
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
                    'url': '/app/e-books/data-list'
                },
                'columns': [
                    {
                        data: "subject_name",width:"30%"
                    },
                    {
                        data: "duration",width:"10%"
                    },
                    {
                        data: "course_name",width:"20%"
                    },
                    {
                        data: "sub_course_name",width:"20%"
                    },
                    {
                        data: "file_type",width:"10%"
                    },
                    {
                      data: "status",width:"10%",
                      "render": function(data, type, row) {
                        var active = data == 1 ? 'Active' : 'Inactive';
                        var checked = data == 1 ? 'checked' : '';
                        return '<div class="form-check form-check-inline switch switch-lg success">\
                                <input onclick="changeStatus('+"'e_books'"+', '+row.ID+','+"'status'"+')" type="checkbox" ' + checked + ' id="status-switch-' + row.ID + '">\
                                <label for="status-switch-' + row.ID + '">' + active + '</label>\
                              </div>';
                      }
                    },
                    {
                      data: "ID",width:"10%",
                      "render": function(data, type, row) {
                        return '<div class="button-list">\
                          <i class="ti ti-edit-circle add_btn_form h5 cursor-pointer" title="Edit" onclick="edit(&#39;e-books&#39;, &#39;' + data + '&#39, &#39;lg&#39;)"></i>\
                        <i class="ti ti-trash-x cursor-pointer add_btn_form h5" onclick="destroy(&#39;e-books&#39;, &#39;' + data + '&#39)"></i>\
                      </div>'
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
