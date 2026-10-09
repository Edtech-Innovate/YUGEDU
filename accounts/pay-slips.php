<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-top.php'); ?>
<style>
  .btn:hover {
    background-color: #2b303b !important;
  }

  .select2-container .select2-selection {
    border-radius: 10px;
    height: 48px !important;
    font-size: 17px;
    font-family: system-ui;
  }

  .btn:hover {
    /* background: #2b303b !important; */
    color: white !important;
    font-size: 14px !important;
  }

  .table-hover tbody tr:hover .custom_hover_dot {
    background-color: #d3eeff !important;
  }

  .btn:hover:not(.active) {
    background: #2b303b !important;
  }

  .breadcrumb a {
    font-size: 14px !important;
  }
</style>
<link href="/assets/plugins/bootstrap-datepicker/css/datepicker3.css" rel="stylesheet" type="text/css" media="screen">
<link href="/assets/plugins/bootstrap-daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css"
  media="screen">
<?php unset($_SESSION['filterByUser']);
unset($_SESSION['filterByDate']); ?>
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
              <?php
              $breadcrumbs = array_filter(explode("/", $_SERVER['REQUEST_URI']));
              for ($i = 1; $i <= count($breadcrumbs); $i++) {
                if (count($breadcrumbs) == $i):
                  $active = "active";
                  $crumb = explode("?", $breadcrumbs[$i]);
                  $breadcrumbText = str_replace("-", " ", $crumb[0]);
                  echo '<li class="breadcrumb-item ' . $active . '">' . ucwords(strtolower($breadcrumbText)) . '</li>';
                endif;
              }       
              ?>
              <div>
                <button class="btn add_btn_form border-0 text-white p-2" aria-label="" title="Download Pay Slips"
                  data-toggle="tooltip" data-original-title="Download Pay Slips" onclick="exportData('Pay-Slips')">
                  Download <i class="uil uil-down-arrow ml-2"></i></button>
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
            <!-- <div class="row">
              <div class="col-md-3 m-b-10">
                <div class="form-group">
                  <div class="input-daterange input-group" id="datepicker-range">
                    <input type="text" class="input-sm form-control custom_input_st_en" placeholder="Select Date"
                      id="startDateFilter" name="start" />
                    <div class="input-group-addon custom_input_st_en_to">to</div>
                    <input type="text" class="input-sm form-control custom_input_st_en1" placeholder="Select Date"
                      id="endDateFilter" onchange="addDateFilter()" name="end" />
                  </div>
                </div>
              </div>
              <?php if ($_SESSION['Role'] != 'Sub-Center') { ?>
                <div class="col-md-3 m-b-10">
                  <div class="form-group">
                    <select class="full-width" style="width:40px" data-init-plugin="select2" id="users"
                      onchange="addFilter(this.value, 'users', 2)" data-placeholder="Choose User">

                    </select>
                  </div>
                </div>
                <div class="col-md-2 m-b-10">
                  <div class="form-group">
                    <select class="full-width" style="width:40px" data-init-plugin="select2" id="sub_center"
                      onchange="addSubCenterFilter(this.value, 'users')" data-placeholder="Choose Sub Center">
                    </select>
                  </div>
                </div>
              <?php } ?>
              <div class="col-md-2 m-b-10"></div>
              <div class="col-md-2 m-b-10">
                <input type="text" id="payments-search-table" class="form-control pull-right custom_search_section"
                  placeholder="Search">

              </div>
            </div> -->
            <!-- <div class="pull-right">
              <div class="col-xs-12">

                <input type="text" id="payments-search-table" class="form-control pull-right" placeholder="Search">
              </div>
            </div>-->
            <div class="clearfix"></div>
          </div>
          <div class="card-body">
            <div class="">
              <table class="table table-hover nowrap table-responsive" id="payments-table">
                <thead>
                  <tr>
                    <th>Serial No.</th>
                    <th>University Fee</th>
                    <th>Student Count</th>
                    <th>Bank</th>
                    <th>Payment Mode</th>
                    <th>Transication No.</th>
                    <th>Date of Generation</th>
                    <th>Date of Payment</th>
                    <th>Fee Status</th>
                    <th>Action</th>
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
      function addWithdraw(url, modal) {
        $.ajax({
          url: '/app/' + url + '/withdraw-amount',
          type: 'GET',
          success: function (data) {
            $('#' + modal + '-modal-content').html(data);
            $('#' + modal + 'modal').modal('show');
          }
        })
      }
      $(function () {
        var role = "<?= $_SESSION['Role'] ?>";
        var showToAdminHeadAccountant = role == 'Administrator' || role == 'University Head' || role == 'Accountant' ? true : false;
        var table = $('#payments-table');

        var settings = {
          'processing': true,
          'serverSide': true,
          'serverMethod': 'post',
          'ajax': {
            'url': '/app/payslip/server'
          },
          'columns': [
            {
              data: "serial_no",
            },
            {
              data: "university_fee",
            },
            {
              data: "student_count",
              "render": function (data, type, row) {
                let viewBtn = `<a href="javascript:void(0);" title="View Students' Pay Slips" onclick="show_students('${row.student_ids}', '${row.id}');" class="d-inline-flex align-items-center text-primary fw-bold"> ${data}  </a>`;
                return viewBtn;
              }
            },
            {
              data: "bank",
            },
            {
              data: "payment_mode",
            },
            {
              data: "bank_transication_no",
            },
            {
              data: "date_of_generation",
            },
            {
              data: "date_of_payment",
            },
            {
              data: "slip_id",
              render: function (data, type, row) {
                var payBtn = ` <a href="javascript:void(0);" title="View Students' Pay Slips" onclick="show_students('${row.student_ids}', '${row.id}');" class="d-inline-flex align-items-center text-primary fw-bold">   <i class="uil uil-eye mr-2"></i>  </a>`;

                if (row.genration_status == 2) {
                  return '<span class="badge bg-success">Fee Paid</span>';
                } else {
                  return '<span class="badge bg-warning">Pending</span>';
                }
              }
            },
            {
              data: "slip_id",
              render: function (data, type, row) {
                var payBtn = ` <a href="javascript:void(0);" title="View Students' Pay Slips" onclick="show_students('${row.student_ids}', '${row.id}');" class="d-inline-flex align-items-center text-primary fw-bold">   <i class="uil uil-eye mr-2"></i>  </a>`;

                if (row.genration_status == 2) {
                  return payBtn;
                } else {
                  let rejectBtn = '<a type="button" title="Reject All Pay Slip" onclick="deleteAllPaySlip(\'' + row.slip_id + '\', \'' + row.student_ids + '\')">' +
                    '<i style="color:red" class="uil uil-times-circle mr-2"></i></a>';

                  let approveBtn = '<a type="button" title="Approve All Pay Slip" onclick="acceptAllPaySlip(\'' + row.slip_id + '\', \'' + row.student_ids + '\', \'' + row.uni_amount + '\')">' +
                    '<i style="color:green" class="uil uil-check-circle mr-1"></i></a>';

                  return payBtn + approveBtn + rejectBtn;
                }
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
          "iDisplayLength": 25,
          "drawCallback": function () {
            $('[data-toggle="tooltip"]').tooltip();
          }
        };

        table.dataTable(settings);

        // search box for table
        $('#payments-search-table').keyup(function () {
          table.fnFilter($(this).val());
        });
      })
    </script>

    <script type="text/javascript">

      function acceptAllPaySlip(slip_id, student_id, uni_amount) {


        $.ajax({
          url: '/app/payslip/approve?slip_id=' + slip_id + '&student_id=' + student_id,
        //   type: 'APPROVE',
              type: 'GET',
          dataType: 'json',
          success: function (data) {
            if (data.status == 200) {
              $('#' + slip_id + ' td:nth-child(6)').html('<span class="badge bg-success">Approved</span>');
              procceedUniFeeFunc(slip_id, student_id, uni_amount);
              notification('success', data.message);
            } else {
              notification('danger', data.message);
            }
          }
        })
      }
      function procceedUniFeeFunc(slip_id, student_id, uni_amount) {
        $.ajax({
          url: '/app/payslip/create-university-fee?slip_id=' + slip_id + '&student_id=' + student_id + '&uni_fee=' + uni_amount,
          type: 'GET',
          success: function (data) {
            $('#lg-modal-content').html(data);
            $('#lgmodal').modal('show');
          }
        })
      }



      function deleteAllPaySlip(slip_id, student_id) {
        Swal.fire({
          title: 'Are you sure?',
          text: "You won't be able to revert this!",
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Yes'
        }).then((result) => {
          if (result.isConfirmed) {
            $.ajax({
              url: '/app/payslip/destroy?slip_id=' + slip_id + '&student_id=' + student_id,
              type: 'DELETE',
              dataType: 'json',
              success: function (data) {
                if (data.status == 200) {
                  notification('success', data.message);
                  $('#payments-table').DataTable().ajax.reload(null, false);
                  $("#payslips-table tbody tr#" + slip_id).remove();

                } else {
                  notification('danger', data.message);
                  $("#payslips-table tbody tr#" + slip_id).remove();

                }
              }
            })
          }
        })
      }
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
      function exportData(filename) {
        let searchValue = '';
        // let searchValue = $('#payments-search-table').val()
        $.ajax({
          url: '/app/payslip/export',
          type: 'download',
          success: function (response) {
            var response = JSON.parse(response);
            if (response && response.aaData && response.aaData.length > 0) {
              let headers = ['Serial No.', 'Student Name', 'Sub-Course', 'User Name', 'Amount', 'Transaction ID'
                , 'Payment Mode', 'Bank Name', 'Date of Generation', 'Pay Slip Status', 'Date of Payment', 'University Fee Status'];

              let csvData = convertToCSV(response.aaData, headers);
              downloadCSV(csvData, filename + '.csv');
            } else {
              alert("No data available for export.");
            }
          },
          error: function () {
            alert("Error while fetching data.");
          }
        });
      }
      function convertToCSV(data, headers, type) {
        let csvRows = [headers.join(',')]; // Add headers to CSV
        data.forEach(row => {
         console.log(row);
          let user_name = row.Center_Name + '(' + row.Center_Code + ')';

          let slip_status = (row.slip_status == 0) ? "Pending" : row.slip_status == 1 ? " Slip Generated(accepted) " : "";

          let genration_status = (row.genration_status == 0 || row.genration_status == 1) ? "Pending" : row.genration_status == 2 ? "Paid University Fee " : "";

          csvRows.push([
            `"${row.serial_no}"`,
            `"${row.Student_name}"`,
            `"${row.sub_course_name}"`,
            `"${row.user_name}"`,
            `"${row.university_fee}"`,
            `"${row.bank_transication_no}"`,
            `"${row.payment_mode}"`,
             `"${row.bank}"`,
            `"${row.date_of_generation}"`,
            `"${slip_status}"`,
            `"${row.date_of_payment}"`,
            `"${genration_status}"`,
          ].join(','));
        });
        return csvRows.join('\n');
      }
      function downloadCSV(csvData, filename) {
        let blob = new Blob([csvData], { type: 'text/csv' });
        let link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = filename;
        link.click();
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
          success: function (data) {
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
          success: function (data) {

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
          success: function (data) {
            if (data.status) {
              $('.table').DataTable().ajax.reload(null, false);
            }
          }
        })
      }
    </script>
    <script>
      function show_students(student_ids, generation_id) {
        modal = 'lg';
        $.ajax({
          url: '/app/payslip/student-list?ids=' + student_ids + '&generation_id=' + generation_id,
          type: 'GET',
          success: function (data) {
            $('#' + modal + '-modal-content').html(data);
            $('#' + modal + 'modal').modal('show');
          }
        })

      }
    </script>

    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>