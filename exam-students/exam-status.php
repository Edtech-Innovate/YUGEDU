<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-top.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-bottom.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/menu.php'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<?php
function monthOptionTag(): string
{
    $monthOption = '<option value="">Select Month</option>';
    $i = 1;
    while ($i <= 12) {
        $month = date('M', mktime(0, 0, 0, $i, 1));
        $monthOption .= ($i == date('n')) ? '<option value="' . $i . '" selected >' . $month . '</option>' : '<option value="' . $i . '">' . $month . '</option>';
        $i++;
    }
    return $monthOption;
}

function yearOptionTag(): string
{

    $yearOption = '<option value="">Select Year</option>';
    $lastYear = date('Y', strtotime('-2 year'));
    $yearOption .= '<option value = "' . $lastYear . '">' . $lastYear . '</option>';
    $nextYear = date('Y', strtotime('-1 year'));
    $yearOption .= '<option value = "' . $nextYear . '">' . $nextYear . '</option>';
    $currentYear = date("Y");
    $yearOption .= '<option value = "' . $currentYear . '" selected>' . $currentYear . '</option>';
    return $yearOption;
}
function verticalTypeFunc()
{
    $verticalType = '<option value="">Select Vertical Type</option>';
    $verticalType .= '<option value="1">Edtech Innovate</option>';
    $verticalType .= '<option value="2">IITS LLP Paramedical</option>';
    $verticalType .= '<option value="3">Rudra</option>';
    return $verticalType;
}
?>
<!-- START PAGE-CONTAINER -->
<div class="page-container">
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
                            } ?>
                            <div class="col-md-2"></div>
                           
                            <div class="col-sm-2">
                                <select class="form-control" name="vartical" id="vartical"
                                    onchange="addFilter(this.value, 'vartical')">
                                    <?= verticalTypeFunc() ?>
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <select class="form-control" name="user" id="user" onchange="addFilter(this.value, 'users')">
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <select class="full-width" style="width:40px" data-init-plugin="select2" name = "sub_center" id="sub_center"
                                    onchange="addFilter(this.value, 'sub_center')" data-placeholder="Choose Sub Center">
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <div class="d-flex flex-row" style="gap:10px;">
                                    <select class="form-control" name="month" id="month">
                                        <?= monthOptionTag() ?>
                                    </select>
                                    <select class="form-control" name="year" id="year">
                                        <?= yearOptionTag() ?>
                                    </select>
                                </div>
                            </div>

                        </ol>
                        <!-- END BREADCRUMB -->
                    </div>
                </div>
            </div>
            <!-- END JUMBOTRON -->
            <!-- START CONTAINER FLUID -->
            <div class="container-fluid">
                <!-- BEGIN PlACE PAGE CONTENT HERE -->
                <div class="card card-transparent">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs nav-tabs-linetriangle" data-init-reponsive-tabs="dropdownfx">
                        <li class="nav-item">
                            <a class="active" data-toggle="tab" data-target="#ready_for_exam_form" href="#"><span>Ready
                                    For Exam Form Sub. - <span id="ready_for_exam_form_count">0</span></span></a>
                        </li>
                        <li class="nav-item">
                            <a data-toggle="tab" data-target="#exam_form_submitted" href="#"><span>Form Submitted
                                    Student - <span id="exam_form_submitted_count">0</span></span></a>
                        </li>
                        <li class="nav-item">
                            <a data-toggle="tab" data-target="#exam_completed" href="#"><span>Student Exam Status -
                                    <span id="exam_completed_count">0</span></span></a>
                        </li>
                    </ul>
                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div class="tab-pane active" id="ready_for_exam_form">
                            <div class="row d-flex justify-content-end">
                                <div class="col-md-2 d-flex justify-content-end">
                                    <button class="btn btn-link" aria-label="" title="" data-toggle="tooltip"
                                        data-original-title="Download Ready For Exam Data in CSV"
                                        onclick="exportData('readyForexam', 1,'Ready-For-Exam-Data')"> <i
                                            class="uil uil-down-arrow"></i>&nbsp;</button>
                                    <input type="text" id="ready_for_exam_form_entry-search"
                                        class="form-control pull-right" placeholder="Search">
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover nowrap" id="ready_for_exam_form_table">
                                    <thead>
                                        <tr>
                                            <th>Photo</th>
                                            <th>Student</th>
                                            <th>Student Id</th>
                                            <th>Enrollment No.</th>
                                            <th>Adm Session</th>
                                            <th>Exam Session</th>
                                            <th>No. Of Attempts</th>
                                            <th>Program Type</th>
                                            <th>Center Name</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane" id="exam_form_submitted">
                            <div class="row d-flex justify-content-end">
                                <div class="col-md-2 d-flex justify-content-start">
                                    <button class="btn btn-link" aria-label="" title="" data-toggle="tooltip"
                                        data-original-title="Download Form Submitted Students CSV"
                                        onclick="exportData('examFormSubmitted', 2,'Exam-Form-Submitted')"> <i
                                            class="uil uil-down-arrow"></i></button>
                                    <input type="text" id="exam_form_submitted_entry-search"
                                        class="form-control pull-right" placeholder="Search">
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover nowrap" id="exam_form_submitted_table">
                                    <thead>
                                        <tr>
                                            <th>Photo</th>
                                            <th>Student</th>
                                            <th>Student Id</th>
                                            <th>Enrollment No.</th>
                                            <th>Adm Session</th>
                                            <th>Exam Session</th>
                                            <th>No. Of Attempts</th>
                                            <th>Program Type</th>
                                            <th>Center Name</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane" id="exam_completed">
                            <div class="row d-flex justify-content-end">
                                <div class="col-md-2 d-flex justify-content-start">
                                    <button class="btn btn-link" aria-label="" title="" data-toggle="tooltip"
                                        data-original-title="Download Exam Completed CSV"
                                        onclick="exportData('examComplete', 3,'Exam-Completed')"> <i
                                            class="uil uil-down-arrow"></i></button>
                                    <input type="text" id="exam_completed_entry-search" class="form-control pull-right"
                                        placeholder="Search">
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover nowrap" id="exam_completed_table">
                                    <thead>
                                        <tr>
                                            <th>Photo</th>
                                            <th>Student</th>
                                            <th>Student Id</th>
                                            <th>Enrollment No.</th>
                                            <th>Adm Session</th>
                                            <th>Exam Session</th>
                                            <th>No. Of Attempts</th>
                                            <th>Exam Status</th>
                                            <th>Result Status</th>
                                            <th>Final Status</th>
                                            <th>Program Type</th>
                                            <th>Center Name</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
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

            $("#month").select2({
                placeholder: 'Month'
            })

            $("#year").select2({
                placeholder: 'Year'
            })

            $("#vartical").select2({
                placeholder: 'Select Vartical'
            });

            $("#user").select2({
                placeholder: 'Choose Center'
            });
            var readyForExamForm = $('#ready_for_exam_form_table');
            var examFormSubmitted = $('#exam_form_submitted_table');
            var examCompleted = $('#exam_completed_table');

            var readyForExamFormSetting = {
                'processing': true,
                'serverSide': true,
                'serverMethod': 'post',
                'ajax': {
                    'url': '/app/exam-students/exam-status/readyForExamForm-server',
                    'type': 'POST',
                    'data': function (d) {
                        d.month = $("#month").val();
                        d.year = $("#year").val();
                        d.pageType = 'readyForexam';
                        d.vertical = $("#vartical").val();
                        d.center = $("#user").val();
                        d.sub_center = $("#sub_center").val();
                    },
                    complete: function (xhr, responseText) {
                        $('#ready_for_exam_form_count').html(xhr.responseJSON.iTotalDisplayRecords);
                    }
                },
                'columns': [{
                    data: "Photo",
                    "render": function (data, type, row) {
                        return '<span class="thumbnail-wrapper d48 circular inline">\
                    <img src="' + data + '" alt="" data-src="' + data + '"\
                        data-src-retina="' + data + '" width="32" height="32">\
                    </span>';
                    }
                }, {
                    data: "First_Name",
                    "render": function (data, type, row) {
                        return '<span >' + row.First_Name + '</span>';
                    }
                }, {
                    data : "Unique_ID"
                },{
                    data: "Enrollment_No",
                }, {
                    data: "admission_session",
                }, {
                    data: "exam_session",
                }, {
                    data: "attempt",
                }, {
                    data: "Course",
                    "render": function (data, type, row) {
                        return '<span >' + data + '</span>\
                    </br><span >'+ row.Sub_Course + '</span>';
                    }
                }, {
                    data: "center_name",
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
                "drawCallback": function (settings) {
                    $('[data-toggle="tooltip"]').tooltip();
                },
            };

            var examFormSubmittedSetting = {
                'processing': true,
                'serverSide': true,
                'serverMethod': 'post',
                'ajax': {
                    'url': '/app/exam-students/exam-status/readyForExamForm-server',
                    'type': 'POST',
                    'data': function (d) {
                        d.month = $("#month").val();
                        d.year = $("#year").val();
                        d.pageType = 'examFormSubmitted';
                        d.vertical = $("#vartical").val();
                        d.center = $("#user").val();
                        d.sub_center = $("#sub_center").val();
                    },
                    complete: function (xhr, responseText) {
                        $('#exam_form_submitted_count').html(xhr.responseJSON.iTotalDisplayRecords);
                    }
                },
                'columns': [{
                    data: "Photo",
                    "render": function (data, type, row) {
                        return '<span class="thumbnail-wrapper d48 circular inline">\
                    <img src="' + data + '" alt="" data-src="' + data + '"\
                        data-src-retina="' + data + '" width="32" height="32">\
                    </span>';
                    }
                },
                {
                    data: "First_Name",
                    "render": function (data, type, row) {
                        return '<span >' + row.First_Name + '</span>';
                    }
                }, {
                    data : "Unique_ID"
                },{
                    data: "Enrollment_No",
                }, {
                    data: "admission_session",
                }, {
                    data: "exam_session",
                }, {
                    data: "attempt",
                }, {
                    data: "Course",
                    "render": function (data, type, row) {
                        return '<span >' + data + '</span>\
                    </br><span >'+ row.Sub_Course + '</span>';
                    }
                }, {
                    data: "center_name",
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
                "iDisplayLength": 10,
                "drawCallback": function (settings) {
                    $('[data-toggle="tooltip"]').tooltip();
                },
            };

            var examCompletedSetting = {
                'processing': true,
                'serverSide': true,
                'serverMethod': 'post',
                'ajax': {
                    'url': '/app/exam-students/exam-status/readyForExamForm-server',
                    'type': 'POST',
                    'data': function (d) {
                        d.month = $("#month").val();
                        d.year = $("#year").val();
                        d.pageType = 'examComplete';
                        d.vertical = $("#vartical").val();
                        d.center = $("#user").val();
                        d.sub_center = $("#sub_center").val();
                    },
                    complete: function (xhr, responseText) {
                        $('#exam_completed_count').html(xhr.responseJSON.iTotalDisplayRecords);
                    }
                },
                'columns': [{
                    data: "Photo",
                    "render": function (data, type, row) {
                        return '<span class="thumbnail-wrapper d48 circular inline">\
                    <img src="' + data + '" alt="" data-src="' + data + '"\
                        data-src-retina="' + data + '" width="32" height="32">\
                    </span>';
                    }
                },
                {
                    data: "First_Name",
                    "render": function (data, type, row) {
                        return '<span >' + row.First_Name + '</span>';
                    }
                }, {
                    data : "Unique_ID"
                },{
                    data: "Enrollment_No",
                },
                {
                    data: "admission_session",
                }, {
                    data: "exam_session",
                }, {
                    data: "attempt",
                }, {
                    data: "exam_status",
                    render: function (data, type, row) {
                        return (data == 'Attempt') ? '<span class="label label-success cursor-pointer">Appear</span>' : '<span class="label label-danger cursor-pointer">Not Appear</span>';
                    }
                }, {
                    data: "result_status",
                    render: function (data, type, row) {
                        let button = '';
                        if (data == 'Pass') {
                            button = '<span class="label label-success cursor-pointer"> ' + data + ' </span>';
                        } else if (data == 'Fail') {
                            button = '<span class="label label-danger cursor-pointer pl-2 pr-2"> ' + data + ' </span>';
                        } else if (data == 'result not found') {
                            button = '<span class="label label-warning cursor-pointer">Pending</span>';
                        }
                        return button;
                    }
                }, {
                    data: "final_status",
                    render: function (data, type, row) {
                        let button = '';
                        if (row.result_status == 'Pass') {
                            button = '<span class="label label-success cursor-pointer">Completed</span>';
                        } else if (row.result_status == 'Fail') {
                            let payfor = +row.attempt + 1; // Here, + is use as uniary operator which convert the string to number
                            if (row.allowforReAttemptOrNot == 'Allow') {
                                if (row.reappear_paymentStatus == 'unpaid') {
                                    <?php if ($_SESSION['Role'] == 'Sub-Center' || $_SESSION['Role'] == 'Center') { ?>
                                        let center_id = '<?= $_SESSION['ID'] ?>';
                                        button = '<button class="btn btn-primary bt-sm p-2" style ="font-size: smaller;" onclick = "checkCenterAmount(&#39;' + row.ID + '&#39;,&#39;' + center_id + '&#39;,&#39;' + payfor + '&#39;)">Re-Appear</button>';
                                    <?php } else { ?>
                                        button = '<button class="btn btn-primary bt-sm p-2" style ="font-size: smaller;" disabled >Re-Appear</button>';
                                    <?php } ?>
                                } else {
                                    button = '<span class="label label-success cursor-pointer">Eligible For Re-Attempt</span>';
                                }
                            } else {
                                button = '<span class="label label-danger cursor-pointer">No Attempt Left</span>';
                            }
                        } else if (row.result_status == 'result not found') {
                            button = '<span class="label label-warning cursor-pointer"> Running </span>';
                        }
                        return button;
                    }
                }, {
                    data: "Course",
                    "render": function (data, type, row) {
                        return '<span >' + data + '</span>\
                    </br><span >'+ row.Sub_Course + '</span>';
                    }
                }, {
                    data: "center_name",
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
                "iDisplayLength": 10,
                "drawCallback": function (settings) {
                    $('[data-toggle="tooltip"]').tooltip();
                },
            };

            $(document).ready(function () {
                readyForExamForm.dataTable(readyForExamFormSetting);
                examFormSubmitted.dataTable(examFormSubmittedSetting);
                examCompleted.dataTable(examCompletedSetting);
                // setTimeout(() => {
                //     examFormSubmitted.dataTable(examFormSubmittedSetting);
                // }, 150);
                // setTimeout(() => {
                //     examCompleted.dataTable(examCompletedSetting);
                // }, 150);
            })

            $('#ready_for_exam_form_entry-search').keyup(function () {
                readyForExamForm.fnFilter($(this).val());
            });

            $('#exam_form_submitted_entry-search').keyup(function () {
                examFormSubmitted.fnFilter($(this).val());
            });

            $('#exam_completed_entry-search').keyup(function () {
                examCompleted.fnFilter($(this).val());
            });

            $('#month,#year').on('change', function () {
                readyForExamForm.DataTable().ajax.reload(null, false);
                examFormSubmitted.DataTable().ajax.reload(null, false);
                examCompleted.DataTable().ajax.reload(null, false);
                // setTimeout(() => {
                //     examFormSubmitted.DataTable().ajax.reload(null, false);
                // }, 150);
                // setTimeout(() => {
                //     examCompleted.DataTable().ajax.reload(null, false);
                // }, 150);
            });

            function checkCenterAmount(ids, center, payfor) {
                $.ajax({
                    url: '/app/exam-students/exam-status/checkCenterBalance',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        ids,
                        center,
                        payfor
                    },
                    success: function (data) {
                        if (data.status) {
                            payReappearFee(data.ids, data.amount, center, payfor);
                        } else if (data.status == false) {
                            notification('danger', data.message);
                        } else {
                            notification('danger', data.message);
                        }
                    }
                });
            }

            function payReappearFee(ids, amount, center, payfor) {
                $.ajax({
                    url: '/app/exam-students/exam-status/payReappearFee',
                    type: 'post',
                    data: {
                        ids,
                        amount,
                        center,
                        payfor
                    },
                    success: function (data) {
                        $("#lg-modal-content").html(data);
                        $("#lgmodal").modal('show');
                    }
                });
            }

            function exportData(pageType, type, filename) {

                let searchValue = (type === 1)
                    ? $('#ready_for_exam_form_entry-search').val()
                    : (type === 2)
                        ? $('#exam_form_submitted').val()
                        : (type === 3)
                            ? $('#exam_completed_entry-search').val()
                            : '';

                $.ajax({
                    url: '/app/exam-students/exam-status/readyForExamForm-server',
                    type: 'POST',
                    data: {
                        month: $("#month").val(),
                        year: $("#year").val(),
                        pageType: pageType,
                        draw: 1,
                        start: 0,
                        'search[value]': searchValue
                    },
                    success: function (response) {
                        var response = JSON.parse(response);
                        if (response && response.aaData && response.aaData.length > 0) {
                            let headers = (type == 1 || type == 2) ? ['Student', 'Unique ID', 'Enrollment No.', 'Adm Session', 'Exam Session', 'No. Of Attempts', 'Program Type', 'Center Name'] : (type == 3) ? ['Student', 'Unique ID', 'Enrollment No.', 'Adm Session', 'Exam Session', 'No. Of Attempts', 'Exam Status', 'Result Status', 'Final Status', 'Program Type', 'Center Name'] : [];
                            let csvData = convertToCSV(response.aaData, headers, type);
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
                if (type === 1 || type === 2) {
                    data.forEach(row => {
                        csvRows.push([
                            `"${row.First_Name} (${row.Email})"`,
                            `"${row.Unique_ID}"`,
                            `"${row.Enrollment_No}"`,
                            `"${row.admission_session}"`,
                            `"${row.exam_session}"`,
                            `"${row.attempt}"`,
                            `"${row.Course} (${row.Sub_Course})"`,
                            `"${row.center_name}"`
                        ].join(','));
                    });
                }
                else if (type === 3) {
                    data.forEach(row => {

                        if (row.result_status === 'result not found') {
                            var final_status = 'Running';
                        } else if (row.result_status === 'Pass') {
                            var final_status = 'Completed';
                        } else if (row.result_status === 'Fail') {
                            if (row.allowforReAttemptOrNot === 'Allow') {
                                var final_status = (row.reappear_paymentStatus === 'unpaid')
                                    ? 'Re-Appear Eligible (Payment Pending)'
                                    : 'Eligible For Re-Attempt';
                            } else {
                                var final_status = 'No Attempt Left';
                            }
                        }

                        if (row.exam_status == 'Attempt') {
                            var exam_status = 'Appear';
                        } else {
                            var exam_status = 'Not Appear';
                        }

                        if (row.result_status == 'Pass') {
                            var result_status = "Pass";
                        } else if (row.result_status == 'Fail') {
                            var result_status = "Fail";
                        } else if (row.result_status == 'result not found') {
                            var result_status = "Pending";
                        }

                        csvRows.push([
                            `"${row.First_Name} (${row.Email})"`,
                            `"${row.Unique_ID}"`,
                            `"${row.Enrollment_No}"`,
                            `"${row.admission_session}"`,
                            `"${row.exam_session}"`,
                            `"${row.attempt}"`,
                            `"${exam_status}"`,
                            `"${result_status}"`,
                            `"${final_status}"`,
                            `"${row.Course} (${row.Sub_Course})"`,
                            `"${row.center_name}"`
                        ].join(','));
                    });

                }
                return csvRows.join('\n');
            }

            function downloadCSV(csvData, filename) {
                let blob = new Blob([csvData], { type: 'text/csv' });
                let link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = filename;
                link.click();
            }
        </script>

        <script type="text/javascript">
            function addFilter(id, by) {
                console.log("dvhfbjsbjvbjdbjvjvb");
                let url = ""; let optionTagId = ""; let executeAjax = true; 
                if (by == 'vartical') {
                    console.log("inside the vertical");
                    url = "/app/exam-students/exam-status/centerList";
                    optionTagId = "user";    
                } else if (by == 'users') { 
                    url = "/app/applications/filter";
                    optionTagId = "sub_center";  
                } else if (by == 'sub_center') {
                    executeAjax = false;
                }  
                if(executeAjax) {
                    $.ajax({
                        url: url, 
                        type: 'POST',
                        data: {id,by},
                        success: function (data) {
                            console.log("dshbdwhdc");
                            $("#"+optionTagId).html(data);
                        }
                    });
                }
                $('.table').DataTable().ajax.reload(null, false);
            }

            
            function addSubCenterFilter(id, by) {
                $.ajax({
                url: '/app/applications/filter',
                type: 'POST',
                data: {
                    id,
                    by
                },
                dataType: 'json',
                success: function (data) {

                    if (data.status) {
                    $('.table').DataTable().ajax.reload(null, false);
                    // $  ("#sub_center").html(data.subCenterName);
                    }

                }
                })
            }

        </script>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>