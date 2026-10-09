<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-top.php'); ?>
<link href="/assets/plugins/bootstrap-datepicker/css/datepicker3.css" rel="stylesheet" type="text/css" media="screen">
<link href="/assets/plugins/bootstrap-daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" media="screen">
<style>
     .dropdown-toggle::after {
     content: none !important
    }
   .btn-secondary:not(:disabled):not(.disabled).active, .btn-secondary:not(:disabled):not(.disabled):active, .show>.btn-secondary.dropdown-toggle {
    color: black;
    background-color: white;
    border-color: white;
    border: none;
     }
     [type=button]:not(:disabled), [type=reset]:not(:disabled), [type=submit]:not(:disabled), button:not(:disabled){
     border: none !important;
     }
    .dropdown-menu > a.dropdown-item:hover, .dropdown-menu > a.dropdown-item:focus, .dropdown-menu > a.dropdown-item:active {
    color: #212121;
    text-decoration: none;
    background-color: #c5cfca;
    border-radius: 0px !important;
    }
   .select2-container .select2-selection {
    border-radius: 7px !important;
    }
   #startDateFilter{
    border-right: none !important;
    border-top-left-radius: 7px !important;
    border-bottom-left-radius: 7px !important;
    padding-right: 0px !important;
    }
    #endDateFilter{
    border-left:none !important;
     border-top-right-radius: 7px !important;
    border-bottom-right-radius: 7px !important;
    padding-right:0px !important;
     }
    .input-group-addon{
     border-top: solid 1px #d0d0d0 !important;
    border-bottom: solid 1px #d0d0d0 !important;
    color: #d0d0d0 !important;
    padding-top: 5px;
    }
  .label-success {
    background-color: #407260;
    color: #fff;
   }
  .label-important, .label-danger {
    background-color: #be534b;
    color: #fff;
   }
    table thead{
    background: white !important ;
  }
  table thead tr th {
    color: black !important;
    font-weight: bold !important;
  }
  #payments-table thead{
       background: #c5cfca  !important;
  }
  #payments-table thead tr th {
      color: #4b4b4b !important;
    font-weight: bold !important;
  }
  #payments-search-table{
  
      border-radius:10px !important;
  }
</style>
<?php
unset($_SESSION['filterByUser']);
unset($_SESSION['filterByDate']);
?>
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
                if (count($breadcrumbs) == $i) : $active = "active";
                  $crumb = explode("?", $breadcrumbs[$i]);
                  echo '<li class="breadcrumb-item ' . $active . '">' . $crumb[0] . '</li>';
                endif;
              }
              ?>
              <div>
                <?php if ($_SESSION['Role'] == 'Center' || $_SESSION['Role'] == 'Sub-Center') { ?>
                  <a href="/accounts/center-ledger/student-wise-ledger" target="" class="btn btn-default border-0" aria-label="" title="" data-toggle="tooltip" data-original-title="Go On Ladger"> <i class="uil uil-arrow-left add_btn_form"></i></a>
                  <a class="btn border-0" aria-label="" title="" data-toggle="tooltip" data-original-title="Add Amount" onclick="add_wallet();"> <i class="uil uil-plus add_btn_form"></i></a>
                <?php } ?>
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
            <div class="row">
              <div class="col-md-12 d-flex justify-content-start align-items-center">
                <div class="col-md-3 m-b-10">
                  <div class="input-daterange input-group" id="datepicker-range">
                    <input type="text" class="input-sm form-control" placeholder="Select Date" id="startDateFilter" name="start" />
                    <div class="input-group-addon bg-white">to</div>
                    <input type="text" class="input-sm form-control" placeholder="Select Date" id="endDateFilter" onchange="addDateFilter()" name="end" />
                  </div>
                </div>
                <?php if ($_SESSION['Role'] != 'Sub-Center') { ?>
                  <div class="col-md-3 m-b-10">
                    <div class="form-group mb-0">
                      <select class="full-width" style="width:40px" data-init-plugin="select2" id="users" onchange="addFilter(this.value, 'users', 2)" data-placeholder="Choose User">

                      </select>
                    </div>
                  </div>
                  <div class="col-md-2 m-b-10">
                    <div class="form-group mb-0">
                      <select class="full-width" style="width:40px" data-init-plugin="select2" id="sub_center" onchange="addSubCenterFilter(this.value, 'users')" data-placeholder="Choose Sub Center">

                      </select>
                    </div>
                  </div>
                <?php } ?>
                <div class="col-md-3 m-b-10">
                  <a class="add_btn_form text-black" href="/app/wallet-payments/admission-history" style="color:black !important;opacity:unset !important;"><i class="ti ti-restore text-black"></i>Admission History</a>
                </div>
              </div>
            </div>
            <div class="pull-right">
              <div class="col-xs-12">
                <input type="text" id="payments-search-table" class="form-control pull-right" placeholder="Search">
              </div>
            </div>
            <div class="clearfix"></div>
          </div>
          <div class="card-body">
            <div class="table-bordered py-2 px-3">
              <table class="table table-hover nowrap table-responsive" id="payments-table">
                <thead>
                  <tr>
                    <th>File</th>
                    <th>Transaction ID</th>
                    <th>Gateway ID</th>
                    <th>Mode</th>
                    <th>Bank Name</th>
                    <th>Amount</th>
                    <th>Type</th>
                    <th>Payment By</th>
                    <th>Payment For</th>
                    <th>Date</th>
                    <th>Status</th>
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
    <script src="/assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js" type="text/javascript"></script>
    <script src="/assets/plugins/bootstrap-daterangepicker/daterangepicker.js"></script>
    <script type="text/javascript">
      $(function() {
        var role = "<?= $_SESSION['Role'] ?>";
        var showToAdminHeadAccountant = role == 'Administrator' || role == 'Accountant' ? true : false;
        var table = $('#payments-table');

        var settings = {
          'processing': true,
          'serverSide': true,
          'serverMethod': 'post',
          'ajax': {
            'url': '/app/wallet-payments/server'
          },
          'columns': [{
              data: "File",width:"15%" ,
              "render": function(data, type, row) {
                var file = row.File_Type != 'pdf' ? '<a href="' + data + '" target="_blank"><img src="' + data + '" height="20"></a>' : '<a href="' + data + '" target="_blank">PDF</a>';
                return file;
              }
            },
            {
              data: "Transaction_ID",width:"15%" ,
              "render": function(data, type, row) {
                return '<strong>' + data + '</strong>';
              }
            },
            {
              data: "Gateway_ID",width:"15%" 
            },
            {
              data: "Payment_Mode",width:"15%" 
            },
            {
              data: "Bank",width:"15%" 
            },
            {
              data: "Amount",width:"15%" 
            }, {
              data: "Type",width:"15%" 
            },
            {
              data: "Center_Name",width:"15%" ,
              "render": function(data, type, row) {
                return '<strong>' + data + ' (' + row.Center_Code + ')</strong>';
              }
            },
             {
              data: "Payment_For",
              render: function(data, type, row) {
                let label = '';
                switch (parseInt(data)) {
                  case 1:
                    label = '<span class="badge badge-info">Student Fee</span>';
                    break;
                  case 2:
                    label = '<span class="badge badge-primary">Authorization Fee</span>';
                    break;
                  case 3:
                    label = '<span class="badge badge-warning">Other</span>';
                    break;
                  default:
                    label = '<span class="badge badge-secondary">Student Fee</span>';
                }
                return label;
              }
            },
            {
              data: "Transaction_Date",width:"15%" 
            },
            {
              data: "Status",width:"15%" ,
              "render": function(data, type, row) {
                var status = '';
                var label_class = '';
                if (row.Type == "Online") {
                  status = data == 0 ? "Failled" : data == 1 ? "Added" : "Rejected";
                  label_class = data == 0 ? "danger" : data == 1 ? "success" : "danger";
                } else {
                  status = data == 0 ? "Pending" : data == 1 ? "Added" : "Rejected";
                  label_class = data == 0 ? "warning" : data == 1 ? "success" : "danger";
                }
                return '<span class="label label-' + label_class + '">' + status + '</span>';
              }
            },
            {
              data: "ID",width:"15%" ,
              "render": function(data, type, row) {
                var status_button = (role == 'Accountant' || role == 'Administrator') && row.Status == 0 ? '<i class="ti ti-circle-check add_btn_form h5 cursor-pointer mr-2" data-toggle="tooltip" data-original-title="Approve" title="" onclick="updatePaymentStatus(&#39;' + data + '&#39, &#39;1&#39;)"></i><i class="ti ti-playstation-x add_btn_form h5 cursor-pointer mr-2" data-toggle="tooltip" data-original-title="Reject" title="" onclick="updatePaymentStatus(&#39;' + data + '&#39, &#39;2&#39;)"></i>' : '';
                // var action_button = (role == 'Accountant' || role == 'Administrator') && row.Status != 1 ? '<i class="ti ti-edit-circle add_btn_form h5 cursor-pointer mr-2" title="Edit" onclick="edit(&#39;offline-payments&#39;, &#39;' + data + '&#39, &#39;lg&#39;)"></i><i class="ti ti-trash-x cursor-pointer add_btn_form h5" title="Delete" onclick="destroy(&#39;offline-payments&#39;, &#39;' + data + '&#39)"></i>' : '';
               var action_button = (role == 'Accountant' || role == 'Administrator') && row.Status != 1 ? '<i class="ti ti-trash-x cursor-pointer add_btn_form h5" title="Delete" onclick="destroy(&#39;offline-payments&#39;, &#39;' + data + '&#39)"></i>' : '';
                if (row.Type != "Online") {
                  return '<div class="button-list text-end my-2">' + status_button + action_button + '</div>';
                } else {
                  return '<div class="button-list text-end"></div>';
                }
              },
              visible: showToAdminHeadAccountant
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
          "iDisplayLength": 25,
          "drawCallback": function() {
            $('[data-toggle="tooltip"]').tooltip();
          }
        };

        table.dataTable(settings);

        // search box for table
        $('#payments-search-table').keyup(function() {
          table.fnFilter($(this).val());
        });
      })
    </script>

    <script type="text/javascript">
    function updatePaymentStatus(id, value) {
      Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'question',
        input: 'textarea', // 👈 remark input
        inputPlaceholder: 'Enter remark here...',
        inputAttributes: {
          'aria-label': 'Type your remark here'
        },
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes',
        preConfirm: (remark) => {
          if (!remark) {
            Swal.showValidationMessage('Remark is required');
          }
          return remark;
        }
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: '/app/wallet-payments/update-payment-status',
            type: 'POST',
            data: {
              id: id,
              value: value,
              remark: result.value // 👈 remark send kar rahe
            },
            dataType: 'json',
            success: function(data) {
              if (data.status == 200) {
                notification('success', data.message);
                $('#payments-table').DataTable().ajax.reload(null, false);
              } else {
                notification('danger', data.message);
              }
            }
          });
        }
      });
    }
    //   function updatePaymentStatus(id, value) {
    //     Swal.fire({
    //       title: 'Are you sure?',
    //       text: "You won't be able to revert this!",
    //       icon: 'question',
    //       showCancelButton: true,
    //       confirmButtonColor: '#3085d6',
    //       cancelButtonColor: '#d33',
    //       confirmButtonText: 'Yes'
    //     }).then((result) => {
    //       if (result.isConfirmed) {
    //         $.ajax({
    //           url: '/app/wallet-payments/update-payment-status',
    //           type: 'POST',
    //           data: {
    //             id,
    //             value
    //           },
    //           dataType: 'json',
    //           success: function(data) {
    //             if (data.status == 200) {
    //               notification('success', data.message);
    //               $('#payments-table').DataTable().ajax.reload(null, false);
    //             } else {
    //               notification('danger', data.message);
    //             }
    //           }
    //         })
    //       }
    //     })
    //   }
    </script>
    <script>
      $('#datepicker-range').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true,
        endDate: '0d'
      });
    </script>
    <script>
      if ($("#users").length > 0) {
        $("#users").select2({
          placeholder: 'Choose Center'
        })
        getCenterList('users');
      }

      function addFilter(id, by, page) {
        var wallet_payments = "wallet-payments";
        $.ajax({
          url: '/app/payments/filter',
          type: 'POST',
          data: {
            id,
            by,
            page,
            wallet_payments
          },
          dataType: 'json',
          success: function(data) {
            if (data.status) {
              $('.table').DataTable().ajax.reload(null, false);
              $("#sub_center").html(data.subCenterName);

            }
          }
        })
      }

      function addSubCenterFilter(id, by) {
        var wallet_payments = "wallet-payments";

        var page = 2;
        $.ajax({
          url: '/app/payments/filter',
          type: 'POST',
          data: {
            id,
            by,
            page,
            wallet_payments
          },
          dataType: 'json',
          success: function(data) {
        
            if (data.status) {
              $('.table').DataTable().ajax.reload(null, false);
            }

          }
        })
      }


      function addDateFilter() {
        var startDate = $("#startDateFilter").val();
        var endDate = $("#endDateFilter").val();
        var wallet_payments = "wallet-payments";
        if (startDate.length == 0 || endDate == 0) {
          return
        }
        var id = 0;
        var by = 'date';
        page = 2;
        $.ajax({
          url: '/app/payments/filter',
          type: 'POST',
          data: {
            id,
            by,
            startDate,
            endDate,
            page,
            wallet_payments

          },
          dataType: 'json',
          success: function(data) {
            if (data.status) {
              $('.table').DataTable().ajax.reload(null, false);
            }
          }
        })
      }
    </script>
    <script>
      function show_students(transaction_id) {
        console.log(transaction_id);
        modal = 'md';
        var sdsds = $('#sdsds_' + transaction_id).attr('data-value');
        $.ajax({
          url: '/app/online-payments/paid-students?ids=' + sdsds,
          type: 'GET',
          success: function(data) {
            $('#' + modal + '-modal-content').html(data);
            $('#' + modal + 'modal').modal('show');
          }
        })

      }
    </script>
    <script>
      function add_wallet() {
        modal = 'md';
        $.ajax({
          url: '/app/wallet-payments/create',
          type: 'GET',
          success: function(data) {
            $('#' + modal + '-modal-content').html(data);
            $('#' + modal + 'modal').modal('show');
          }
        })

      }
    </script>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>