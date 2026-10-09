<?php 
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-top.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-bottom.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/menu.php'); ?>

<link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet" />

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<style>
    body {
        background: #f4f6f9;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }

    .welcome-section {
        background: #ffffff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        margin-bottom: 25px;
        animation: fadeIn 0.8s ease-in-out;
    }

    .custom-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        background: #ffffff;
        margin-bottom: 25px;
    }

    .custom-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1) !important;
    }

    .metric-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }

    .bg-light-primary { background-color: rgba(0, 123, 255, 0.1); color: #007bff; }
    .bg-light-success { background-color: rgba(40, 167, 69, 0.1); color: #28a745; }

    .custom-table-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        background: #ffffff;
        overflow: hidden;
    }

    .custom-table th {
        background-color: #1e293b;
        color: #ffffff !important;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
        border: none;
    }

    .custom-table td {
        vertical-align: middle !important;
        padding: 1.1rem 1rem;
        color: #475569;
        border-color: #f1f5f9;
    }

    .custom-table tbody tr {
        transition: background-color 0.2s ease;
    }

    .custom-table tbody tr:hover {
        background-color: #f8fafc;
    }

    .custom_modal_bg {
        background-color: #1e293b;
    }

    .custom-footer {
        padding: 30px 0 10px;
        margin-top: 20px;
        border-top: 1px solid #e2e8f0;
    }

    @keyframes fadeIn {
        0% { opacity: 0; transform: translateY(15px); }
        100% { opacity: 1; transform: translateY(0); }
    }
</style>

<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/menu.php'); ?>
<div class="page-container ">
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/topbar.php'); ?>
    <div class="page-content-wrapper ">
        <div class="content ">
            <div class="jumbotron" data-pages="parallax">
                <div class=" container-fluid sm-p-l-0 sm-p-r-0">
                    <div class="inner d-flex flex-wrap justify-content-between">
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
                        </ol>
                        <?php
                        $center_id = mysqli_real_escape_string($conn, $_SESSION['ID']);
                        $searchQuery = "AND IF(Notifications_Generated.center_id != '',JSON_CONTAINS(Notifications_Generated.center_id,'[\"{$center_id}\"]'),true)";
                        $new_notification = $conn->query("SELECT * FROM Notifications_Generated WHERE Status = 1 AND Send_To = 'center' OR Send_To = '" . 'all' . "' $searchQuery  ORDER BY Notifications_Generated.ID DESC LIMIT 1");
                        $records = mysqli_fetch_assoc($new_notification);
                        $record_count = array();
                        $viewed_id = array();
                        ?>
                        <div class="justify-content-between align-self-end" id="show-notification">
                            <?php if ($record_count) { ?>
                                <a type="button" onclick="show_notification('<?= $records['ID'] ?>')"><iconify-icon
                                        icon="uil:bell"></iconify-icon>
                                    <?php echo "One New Notification regarding " . $records['Heading'];
                            } else {
                                echo '';
                            } ?></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container-fluid">
                
                <div class="welcome-section d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="m-0 font-weight-bold text-dark">Welcome Back, <?= isset($_SESSION['Contact_Name']) ? htmlspecialchars($_SESSION['Contact_Name']) : 'User' ?>!</h4>
                        
                    </div>
                    <div>
                        <span class="badge bg-light-primary p-2 px-3 text-uppercase font-weight-bold" style="font-size: 12px;"><?= date('l, F d, Y') ?></span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-4 col-xl-3 col-lg-4">
                        <div class="card custom-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-muted text-uppercase font-weight-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Total Students</p>
                                        <?php
                                        $all_count = $conn->query("SELECT COUNT(ID) as allcount FROM Students WHERE University_ID = " . $_SESSION['university_id'] . " AND Added_For =  " . $_SESSION['ID'] . " ");
                                        $records = mysqli_fetch_assoc($all_count);
                                        $totalRecords = $records['allcount'];
                                        ?>
                                        <h3 class="font-weight-bold text-dark m-0"><?= $totalRecords ?></h3>
                                    </div>
                                    <div class="metric-icon bg-light-primary">
                                        <i class="ri-user-follow-line"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-sm-4 col-xl-3 col-lg-4">
                        <div class="card custom-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-muted text-uppercase font-weight-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Total Programs</p>
                                        <?php
                                        $all_count = $conn->query("SELECT COUNT(Sub_Courses.ID) as allcount FROM Sub_Courses LEFT JOIN User_Sub_Courses ON User_Sub_Courses.Sub_Course_ID = Sub_Courses.ID WHERE Sub_Courses.University_ID = " . $_SESSION['university_id'] . " AND User_Sub_Courses.User_ID = " . $_SESSION['ID']);
                                        $records = mysqli_fetch_assoc($all_count);
                                        $totalRecords = $records['allcount'];
                                        ?>
                                        <h3 class="font-weight-bold text-dark m-0"><?= $totalRecords ?></h3>
                                    </div>
                                    <div class="metric-icon bg-light-success">
                                        <i class="ri-book-open-line"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-12">
                        <div class="card custom-table-card">
                            <div class="card-header bg-white border-0 pt-4 px-4 d-flex align-items-center">
                                <div class="metric-icon bg-light-primary mr-3" style="width:40px; height:40px; font-size: 20px;">
                                    <i class="ri-node-tree"></i>
                                </div>
                                <h5 class="m-0 font-weight-bold text-dark">Escalation Matrix</h5>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <?php
                                $counsellor = $conn->query("SELECT Reporting FROM University_User WHERE User_ID=".$_SESSION['ID']." AND University_ID=".$_SESSION['university_id']);

                                $records = mysqli_fetch_assoc($counsellor);
                                $totalRecords = $records['Reporting'];
                                $verticalheadQuery = $conn->query("SELECT Reporting from University_User where User_ID=" . $totalRecords." AND University_ID=".$_SESSION['university_id']);
                                $verticalhearreport = mysqli_fetch_assoc($verticalheadQuery);
                                
                                $verticalHeadOwner = $verticalhearreport['Reporting'];
                                  
                                $Counsellor = $conn->query("SELECT * FROM Users WHERE ID = " . $totalRecords . "");
                                $university_Counsellor = mysqli_fetch_assoc($Counsellor);
                                $accountantQuery = $conn->query("Select * from Users where Role='Accountant'");
                                $accountant =  mysqli_fetch_assoc($accountantQuery);
                                $verticalheaduser = $conn->query("select * from Users where ID=".$verticalHeadOwner."");
                                $verticalhead =  mysqli_fetch_assoc($verticalheaduser);
                                ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped custom-table m-0">
                                        <thead>
                                            <tr>
                                                <th>S. No.</th>
                                                <th>User</th>
                                                <th>Name</th>
                                                <th>Mobile Number</th>
                                                <th>Email ID</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>1</td>
                                                <td><?= $verticalhead['Role'] ?></td>
                                                <td><?= $verticalhead['Name'] ?></td>
                                                <td><?= $verticalhead['Mobile'] ?></td>
                                                <td><?= $verticalhead['Email'] ?></td>
                                            </tr>
                                            <tr>
                                                <td>2</td>
                                                <td><?= $university_Counsellor['Role'] ?></td>
                                                <td><?= $university_Counsellor['Name'] ?></td>
                                                <td><?= $university_Counsellor['Mobile'] ?></td>
                                                <td><?= $university_Counsellor['Email'] ?></td>
                                            </tr>
                                            <tr>
                                                <td>3</td>
                                                <td><?= $accountant['Role'] ?></td>
                                                <td><?= $accountant['Name'] ?></td>
                                                <td><?= $accountant['Mobile'] ?></td>
                                                <td><?= $accountant['Email'] ?></td>
                                            </tr>
                                            <tr>
                                                <td>4</td>
                                                <td>Director</td>
                                                <td>Krishna Gupta</td>
                                                <td>7303245553</td>
                                                <td>krishna@yugedu.in</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row custom-footer">
                    <div class="col-12 text-center">
                        <p class="m-0 mb-1 text-muted" style="font-size: 14px;">
                            <i class="ri-mail-line mr-1 text-primary"></i> Contact Email: <a href="mailto:info@yugedu.in" class="text-primary font-weight-bold">info@yugedu.in</a>
                        </p>
                        
                    </div>
                </div>

            </div>
            </div>
        
        <div class="modal fade" id="emailModal" tabindex="-1" role="dialog" aria-labelledby="emailModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered animate__animated animate__zoomIn" role="document">
                <div class="modal-content border-0 shadow " style="border-radius: 10px;">
                    <div class="modal-header custom_modal_bg text-white pt-1 py-3"
                        style="border-top-right-radius: 10px;border-top-left-radius:10px">
                        <h5 class="modal-title mt-0" id="emailModalLabel">
                            <i class="ri-mail-line mr-2"></i>Send Email
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="escalationForm" enctype="multipart/form-data" action="/app/escalation-matrix/sendMail"
                        method="POST">
                        <div class="modal-body mt-3">
                            <input type="hidden" id="email" name="email" value="">
                            <input type="hidden" class="form-control" id="subject" name="subject" value=""
                                placeholder="Enter subject" />

                            <div class="form-group">
                                <label class="text-black">Message</label>
                                <textarea class="form-control" name="msg" rows="4"
                                    placeholder="Write your message here..." required></textarea>
                            </div>
                            <div class="form-group">
                                <label class="text-black">Attachment</label>
                                <input type="file" name="attachment"
                                    class="form-control-file border border-1 rounded p-2" />
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" id="submitButton" class="btn btn-primary">Send</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                Close
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

        <script>
            // Fill contact person name in modal
            $('#emailModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var contactName = button.data('contact');
                var contactEmail = button.data('email');
                var contactSubject = button.data('subject');
                var modal = $(this);
                modal.find('#contactName').text(contactName);
                modal.find('#email').val(contactEmail);
                modal.find('#subject').val(contactSubject);
            });

            $('#escalationForm').on('submit', function (e) {
                $('#submitButton').text("Sending...");
                $('#submitButton').prop('disabled', true);
                e.preventDefault();
                var formData = new FormData($(this)[0]);
                $.ajax({
                    url: $(this).attr('action'),
                    type: $(this).attr('method'),
                    data: formData,
                    success: function (res) {
                        res = JSON.parse(res);
                        if (res['status'] == 'success') {
                            $('#emailModal').modal('hide');
                            notification('success', "Query sent to concern person.");
                            setTimeout(() => {
                                window.location.reload();
                            }, "1000");
                        } else {
                            notification('danger', res['message']);
                        }
                    },
                    cache: false,
                    contentType: false,
                    processData: false
                })
            })
        </script>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-top.php'); ?>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>
        <script type="text/javascript">
            function view_content(id) {
                $.ajax({
                    url: '/app/notifications/contents?id=' + id,
                    type: 'GET',
                    success: function (data) {
                        $("#md-modal-content").html(data);
                        $("#mdmodal").modal('show');
                    }
                })
            }

            function show_notification(id) {
                $.ajax({
                    url: '/app/notifications/current-notification?id=' + id,
                    type: 'GET',
                    success: function (data) {
                        $("#md-modal-content").html(data);
                        $("#mdmodal").modal('show');
                    }
                })
            }
        </script>