<!-- Modal -->
<?php if (isset($_GET['generation_id']) && isset($_GET['ids'])) { ?>
    <style>
        div#lg-modal-content {
            width: 1000px !important;
        }
    </style>
<div class="modal-header clearfix text-left">
    <button aria-label="Close" type="button" class="close" data-dismiss="modal" aria-hidden="true">
        <i class="pg-icon">close</i>
    </button>
    <div class="d-flex justify-content-between align-items-center w-100">
        <h5 class="mb-0">
            All <span class="semi-bold">Generated Pay Slips</span>
        </h5>
        <div id="xportxlsx" class="xport ml-3">
            <input type="submit" class="btn btn-sm btn-primary" value="Export to XLSX!" onclick="doit('xlsx');">
        </div>
    </div>
</div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-hover nowrap" id="payslips-table">
                        <thead>
                            <tr>
                                <th>Student-Name</th>
                                <th>Serial No.</th>
                                <th>University Fee</th>
                                <th>Sub-Course Name</th>
                                <th>User Name</th>
                                 <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include '../../includes/db-config.php';
                            session_start();
                            $ids = isset($_GET['ids']) ? $_GET['ids'] : '';
                            $generation_id = isset($_GET['generation_id']) ? $_GET['generation_id'] : '';
                            $newStr = explode(",", $ids);
                            $student_id_query = "";
                            if (!empty($newStr)) {
                                $student_id_query = " and student_id in ($ids)";
                            }

                            $students = $conn->query("SELECT pay_slips.status,pay_slip_generation.status as gen_status,
                             pay_slips.student_id, pay_slips.id as slip_id, pay_slips.university_fee, pay_slip_generation.date_of_generation , CONCAT(Users.Name, ' (', Users.Code, ')') AS user_name,serial_no,CONCAT(TRIM(CONCAT(Students.First_Name, ' ', Students.Middle_Name, ' ', Students.Last_Name)), ' (', IF(Students.Unique_ID='' OR Students.Unique_ID IS NULL, RIGHT(CONCAT('000000', Students.ID), 6), Students.Unique_ID), ')') as Student_Name, Sub_Courses.Name as sub_course_name FROM pay_slips left join pay_slip_generation on pay_slip_id = pay_slip_generation.id left join Students on Students.ID = pay_slips.student_id left join Users on Users.ID = Students.Added_By left join Sub_Courses on Sub_Courses.ID = Students.Sub_Course_ID where 1=1 and pay_slips.university_id = '" . $_SESSION['university_id'] . "' $student_id_query group by student_id order by pay_slips.id desc");

                            if ($students->num_rows > 0) {
                                while ($student = mysqli_fetch_assoc($students)) {
                                    ?>
                                    <tr id="<?= $student['slip_id'] ?>">
                                        <td><?= $student['Student_Name'] ?></td>
                                        <td><?= $student['serial_no'] ?></td>
                                        <td><?= "&#8377; " . number_format($student['university_fee'], 2) ?></td>
                                        <td><?= $student['sub_course_name'] ?></td>
                                        <td><?= $student['user_name'] ?></td>
                                        <td >
                                            <?php if ($student['status'] == 0): ?>
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            <?php elseif ($student['status'] == 1): ?>
                                                <span class="badge bg-success">Approved</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger"></span>
                                            <?php endif; ?>
                                        </td>

                                        <td style="padding: 0px;">
                                            <?php if($student['gen_status']!=2){ ?>
                                            <a type="button"    title="Reject Pay Slip"
                                                onclick="deletePaySlip('<?= $student['slip_id'] ?>','<?= $student['student_id'] ?>' )"><i
                                                    style="color:red" class="uil uil-times-circle mr-2"></i></a>
                                            <a type="button" title="Approve Pay Slip"
                                                onclick="acceptPaySlip('<?= $student['slip_id'] ?>','<?= $student['student_id'] ?>' )"><i
                                                    style="color:green" class="uil uil-check-circle mr-1"></i></a>
                                                    <?php }else{ ?>
                                            <span class="badge bg-success">Fee Paid</span>
                                                    <?php } ?>
                                        </td>
                                    </tr>
                                <?php }
                            } else { ?>
                                <tr>
                                    <td>No data found</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="//unpkg.com/xlsx/dist/shim.min.js"></script>
    <script type="text/javascript" src="//unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
    <script type="text/javascript" src="//unpkg.com/blob.js@1.0.1/Blob.js"></script>
    <script type="text/javascript" src="//unpkg.com/file-saver@1.3.3/FileSaver.js"></script>

    <script type="text/javascript">

        function deletePaySlip(slip_id, student_id) {
            $.ajax({
                url: '/app/payslip/destroy?slip_id=' + slip_id + '&student_id=' + student_id,
                type: 'DELETE',
                dataType: 'json',
                success: function (data) {
                    if (data.status == 200) {
                        notification('success', data.message);
                          $("#payslips-table tbody tr#" + slip_id).remove(); 
                    } else {
                        notification('danger', data.message);
                          $("#payslips-table tbody tr#" + slip_id).remove(); 

                    }
                }
            })
        }

        function acceptPaySlip(slip_id, student_id) {
            $.ajax({
                url: '/app/payslip/approve?slip_id=' + slip_id + '&student_id=' + student_id,
                type: 'APPROVE',
                dataType: 'json',
                success: function (data) {
                    if (data.status == 200) {
                         $('#'+slip_id+' td:nth-child(6)').html('<span class="badge bg-success">Approved</span>');
                        notification('success', data.message);
                    } else {
                        notification('danger', data.message);

                    }
                }
            })
        }


        function doit(type, fn, dl) {
            var elt = document.getElementById('payslips-table');
            var wb = XLSX.utils.table_to_book(elt, {
                sheet: "Sheet JS"
            });
            return dl ?
                XLSX.write(wb, {
                    bookType: type,
                    bookSST: true,
                    type: 'base64'
                }) :
                XLSX.writeFile(wb, fn || ('Generated-Pay-Slips.' + (type || 'xlsx')));
        }


        function tableau(pid, iid, fmt, ofile) {
            if (typeof Downloadify !== 'undefined') Downloadify.create(pid, {
                swf: 'downloadify.swf',
                downloadImage: 'download.png',
                width: 100,
                height: 30,
                filename: ofile,
                data: function () {
                    return doit(fmt, ofile, true);
                },
                transparent: false,
                append: false,
                dataType: 'base64',
                onComplete: function () {
                    alert('Your File Has Been Saved!');
                },
                onCancel: function () {
                    alert('You have cancelled the saving of this file.');
                },
                onError: function () {
                    alert('You must put something in the File Contents or there will be nothing to save!');
                }
            });
        }
        tableau('xlsxbtn', 'xportxlsx', 'xlsx', 'Generated-Pay-Slips.xlsx');
    </script>
<?php } ?>