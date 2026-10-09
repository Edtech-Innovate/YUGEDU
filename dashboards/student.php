<style>
  .profile_img {
    width: 150px;
    height: 150px;
    object-fit: fill;
    margin: 10px auto;
    border: 5px solid #3c4e76;
    border-radius: 50%;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    /* normal shadow */
    transition: all 0.3s ease-in-out;
  }

  .profile_img:hover {
    box-shadow: 0 8px 20px rgba(60, 78, 118, 0.6);
    /* deeper blue shadow */
    transform: scale(1.05);
    /* optional slight zoom effect */
  }




  .profile_table td {
    padding: .5rem !important;
    border: none !important;

  }

  .tile-progress .tile-footer {
    padding: 9px 20px !important;
  }

  .bg-info {
    background-color: #17a2b8 !important
  }

  .table>thead>tr>th {
    border-bottom: 2px solid #f4f4f4;
    font-weight: bold;
    color: black;
  }

  .bg-success {
    background-color: #28a745 !important;
  }

  .bg-primary {
    background-color: #007bff !important;
  }

  h5 {

    line-height: unset;
    margin-bottom: .5rem;
  }

  h4 {
    font-size: 18px !important;
  }

  .card .card-header {
    padding: 5px 16px 5px 16px !important;
  }

  .card.border.border-danger.shadow {
    height: 585px;
  }

  .progress {
    background-color: #e9ecef;
  }

  .progress-bar {
    transition: width 0.6s ease;
  }

  .custom-bg-primary {
    background-color: #2e3f67 !important;
  }

  .custom_border_r {
    border-top-left-radius: 10px !important;
    border-top-right-radius: 10px !important;
  }

  .btn-custom {
    background-color: #2e3f6761;
    border-color: #2e3f676b;
    color: #2e3f67;
  }

  .custom_h {
    min-height: 400px;
    max-height: 100%;
  }

  .custom_hs {
    min-height: 535px;
    max-height: 100%;
  }
.custom-bg-primary {
    background-color: #587467 !important;
}
.btn-custom {
    background-color: #c5cfcacf;
    border-color: #2e3f676b;
    color: #1c3127;
}
.tile-custom {
    background-color: #c5cfca !important;
    color: #3f3737 !important;
}
.text-black {
    color: #3f3737 !important;
}
</style>
<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

?>

<link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.10.2/lottie.min.js"></script>
<link rel="stylesheet" href="/assets/css/new-style.css" />
<link rel="stylesheet" href="/assets/css/themify-icons/themify-icons.css" />
<div class="row mb-3">
  <div class="col-lg-6 col-md-12 col-sm-12">
    <div class="card border-primary shadow custom_h" style="border-radius:10px;">
      <div class="card-header custom-bg-primary text-white separator custom_border_r">
        <h5 class="fw-bold"><i class="ti-user mr-2"></i> Profile</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-5">

            <img class="profile_img" src="<?= $_SESSION['Photo'] ?>" alt="">

            <h6 class=""><span class="fw-semibold">Name:</span> <?= $_SESSION['Name'] ?></h6>
            <h6 class=""><span class="fw-semibold">Student ID:</span> <?= $_SESSION['Unique_ID'] ?></h6>
            <h6 class=""><span class="fw-semibold">Phone:</span> <?= $_SESSION['Contact'] ?></h6>
            <h6 class=""><span class="fw-semibold">Email:</span> <?= $_SESSION['Email'] ?></h6>
          </div>
          <div class="col-md-7 border-left">
            <h6 class="fw-semibold">Academic Details:</h6>
            <div class="table-responsive">
              <table class="table mb-0 profile_table">
                <tr>
                  <td class="fw-bold" width="37%">Admission Session </td>
                  <td class="fw-bold" width="2%">:</td>
                  <td class="fw-normal"><?= $_SESSION['Admission_Session'] ?></td>
                </tr>
                <tr>
                    <?php
                        if($_SESSION['university_id']==41){
                            ?>  <td class="fw-bold" width="30%">Duration</td>
                  <td class="fw-bold" width="2%">:</td>
                  <td class="fw-normal"><?php
                        if (strpos($_SESSION['Sub_Course'], "3 Months") !== false) {
                            echo "3 Months";
                        } elseif(strpos($_SESSION['Sub_Course'], "6 Months") !== false) {
                            echo "6 Months"; // fallback safe output
                        }else{
                            echo "11 Months";
                        }
                  
                  ?></td> <?php
                        }else{
                            ?> <td class="fw-bold" width="30%">Current Semester</td>
                  <td class="fw-bold" width="2%">:</td>
                  <td class="fw-normal"><?= $_SESSION['Duration'] ?></td> <?php
                        }
                    ?>
                  
                </tr>
                <tr>
                  <td class="fw-bold" width="30%">Enrollment No</td>
                  <td class="fw-bold" width="2%">:</td>
                  <td class="fw-normal">
                    <?php echo empty($_SESSION['Enrollment_No']) ? 'Document under verification' : $_SESSION['Enrollment_No'] ?>
                  </td>
                </tr>
                <tr>
                  <td class="fw-bold" width="30%">Course</td>
                  <td class="fw-bold" width="2%">:</td>
                  <td class="fw-normal"><?= $_SESSION['Course'] ?></td>
                </tr>
                <tr>
                  <td class="fw-bold" width="30%">Specialization</td>
                  <td class="fw-bold" width="2%">:</td>
                  <td class="fw-normal"><?= $_SESSION['Sub_Course'] ?></td>
                </tr>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-6 col-md-12 col-sm-12">
    <div class="card shadow border-success custom_h" style="border-radius:10px;">
      <div class="card-header custom-bg-primary text-white separator custom_border_r">
        <h5 class="fw-bold"> <i class=" ti-ruler-pencil mr-2"></i> Academic Details</h5>
      </div>
      <div class="card-body ">
        <div class="row">

          <!-- SUBJECTS BOX -->
          <div class="col-lg-6 col-md-6 col-sm-12">
            <a href="/student/lms/lms" class="">
              <div class="tile-progress tile-custom dashboards_card cd1" style="border-radius:10px;">
                <div class="tile-header d-flex flex-row justify-content-between">
                  <i class="ri-book-marked-fill custom_icons_d text-black" ></i>
                  <div class="w-75">
                    <p class="mb-0 font-weight-bold custom_h2">Subjects</p>
                     <?php
                      $duration = $_SESSION['Duration'];
                      $sub_count = $conn->query("SELECT Syllabi.Name FROM Syllabi WHERE Course_ID='" . $_SESSION['Course_ID'] . "' AND Sub_Course_ID='" . $_SESSION['Sub_Course_ID'] . "' AND Semester = '" . $duration . "' AND University_ID ='" . $_SESSION['university_id'] . "'");
                     
                    ?>
                    <p class="mb-0 h6 font-weight-bold"><?= $sub_count->num_rows ?></p>
                  </div>
                </div>
              </div>
            </a>
          </div>

          <!-- ASSIGNMENTS BOX -->
          <div class="col-lg-6 col-md-6 col-sm-12">
            <a href="/student/lms/assignments" class="">
              <div class="tile-progress tile-custom dashboards_card cd2" style="border-radius:10px;">
                <div class="tile-header d-flex flex-row justify-content-between">
                  <?php
              
                  $assignment_count = $conn->query("SELECT * FROM student_assignment WHERE sub_course_id = '" . $_SESSION['Sub_Course_ID'] . "'  ");
                  ?>
                  <i class="ri-file-edit-line custom_icons_d text-black"></i>
                  <div class="w-75">
                    <p class="mb-0 font-weight-bold custom_h2">Assignments</p>
                    <p class="mb-0 h6 font-weight-bold"><?= $assignment_count->num_rows; ?></p>
                  </div>
                </div>
              </div>
            </a>
          </div>

          <!-- EXAM SESSION BOX -->
          <div class="col-lg-6 col-md-6 col-sm-12">
            <a href="/student/examination/date-sheets" class="">
              <div class="tile-progress tile-custom dashboards_card cd3" style="border-radius:10px;">
                <div class="tile-header d-flex flex-row justify-content-between">
                  <i class="ri-contract-line custom_icons_d text-black"></i>
                  <div class="w-75">
                    <p class="mb-0 font-weight-bold custom_h2">Exam Session</p>
                    <p class="mb-0 h6 font-weight-bold"><?= $_SESSION['Admission_Session'] ?></p>
                  </div>
                </div>
              </div>
            </a>
          </div>

          <!-- RESULTS BOX -->
          <div class="col-lg-6 col-md-6 col-sm-12">
            <a href="/student/examination/results" class="">
            <!--<a href="#" class="">-->
              <div class="tile-progress tile-custom dashboards_card cd4" style="border-radius:10px;">
                <div class="tile-header d-flex flex-row justify-content-between">
                  <i class="ri-article-line custom_icons_d text-black"></i>
                  <div class="w-75">
                    <p class="mb-0 font-weight-bold custom_h2">Results</p>
                    <?php  $getDataSQL = $conn->query("SELECT * From marksheets AS m LEFT JOIN Syllabi AS s ON m.subject_id = s.ID WHERE m.enrollment_no = '" . $_SESSION['Enrollment_No'] . "' AND s.Course_ID = " . $_SESSION['Course_ID'] . " AND  s.Sub_Course_ID = " . $_SESSION['Sub_Course_ID'] . " ORDER BY s.Code");
                     if($getDataSQL->num_rows > 0){
                       $result_message = "Published";
                     }else{
                       $result_message = "Coming Soon";
                     }
                       
                    ?>
                    <p class="mb-0 h6 font-weight-bold"><?= $result_message ?></p>
                  </div>
                </div>
              </div>
            </a>
          </div>

        </div>
      </div>

    </div>
  </div>
</div>
<div class="row mb-2">
  <div class="col-lg-6 col-md-12 col-sm-12">
    <div class="card border-info shadow custom_hs" style="border-radius:10px;">
      <div class="card-header custom_border_r custom-bg-primary text-white separator">
        <h5 class="fw-bold"><i class="ti-agenda mr-2"></i> Subject Overview</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <?php
     
           $subjectArr = $conn->query("SELECT Syllabi.ID as subject_id,Syllabi.Semester,Syllabi.Name,Sub_Course_ID,Syllabi.Course_ID, Syllabi.Credit FROM Syllabi  LEFT JOIN Sub_Courses ON Syllabi.Sub_Course_ID = Sub_Courses.ID LEFT JOIN Modes ON Sub_Courses.Mode_Id = Modes.ID  WHERE Syllabi.Sub_Course_ID ='" . $_SESSION['Sub_Course_ID'] . "' AND Syllabi.Semester='" . $_SESSION['Duration'] . "'");

          if ($subjectArr->num_rows > 0) { ?>
            <table class="table table-striped table-bordered">
              <thead>
                <tr>
                  <th>Subject Name</th>
                  <th>Credits</th>
                  <th>Ebooks</th>
                  <th>Notes</th>
                  <th>Video</th>
                  <th>Assessments</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $rowArr = array();
                
                
                    while ($rowArr = mysqli_fetch_assoc($subjectArr)) {
                
                  $duration = $rowArr['Semester'];

                  $progressAverage = 0;
                  $query = $conn->query("SELECT count(e_books.id) as total_ebook FROM e_books WHERE e_books.subject_id = '" . $rowArr['subject_id'] . "' AND e_books.status =1 AND e_books.sub_course_id='" . $rowArr['Sub_Course_ID'] . "'");
                  $e_bookArr = $query->fetch_assoc();
                  $noteQuery = $conn->query("SELECT count(notes.id) as total_note FROM notes WHERE notes.subject_id = '" . $rowArr['subject_id'] . "' AND notes.status =1 AND notes.course_id='" . $rowArr['Sub_Course_ID'] . "'");
                  $notesArr = $noteQuery->fetch_assoc();
                  $video_query = $conn->query("SELECT count(video_lectures.id) as total_vedio FROM video_lectures WHERE video_lectures.subject_id = '" . $rowArr['subject_id'] . "' AND video_lectures.status =1 AND video_lectures.sub_course_id='" . $rowArr['Sub_Course_ID'] . "'");
                  $videoArr = $video_query->fetch_assoc();
                  $assesmentArr['total_assesment'] = 0;
                  $progress = rand(50, 100);
                  ?>
                  <tr>
                    <td>
                      <?= $rowArr['Name'] ?>
                      <div class="d-flex align-items-center mt-1" style="gap: 10px;">
                        <?php
                        $video_query = $conn->query("SELECT * FROM video_lectures WHERE video_lectures.subject_id = '" . $rowArr['subject_id'] . "' AND video_lectures.status =1 AND video_lectures.course_id='" . $rowArr['Sub_Course_ID'] . "'");
                        $videoID = [];
                        while ($row = $video_query->fetch_assoc()) {
                          $videoID[] = $row['id'];
                        }
                        if (count($videoID) > 0) {
                          $videosIds = implode(",", $videoID);
                          $studentId = $_SESSION['ID'];
                          $progressQuery = "SELECT SUM(progress) AS average_progress FROM student_progress WHERE students_id = $studentId AND videos_id IN ($videosIds)";
                          $progress = $conn->query($progressQuery);
                          if ($progress->num_rows > 0) {
                            $progress = $progress->fetch_assoc();
                            $progress = $progress['average_progress'] / count($videoID);
                            $progressAverage = round($progress) ?? 0;
                          }
                        }
                        ?>
                        <div class="progress flex-grow-1" style="height: 6px; border-radius: 4px;">
                          <div class="progress-bar custom-bg-primary" role="progressbar"
                            style="width: <?= $progressAverage ?? 0 ?>%;" aria-valuenow="<?= $progress ?>" aria-valuemin="0"
                            aria-valuemax="100"></div>
                        </div>
                        <small><strong><?= $progressAverage ?? 0 ?>%</strong></small>
                      </div>
                    </td>
                    <td><?= $rowArr['Credit'] ?></td>
                    <td><a
                        href="/student/lms/subjects?id=<?= $rowArr['subject_id'] ?>&type=1&duration=<?= $duration ?>"><?= $e_bookArr['total_ebook'] ?></a>
                    </td>
                    <td><a
                        href="/student/lms/subjects?id=<?= $rowArr['subject_id'] ?>&type=4&duration=<?= $duration ?>"><?= $notesArr['total_note'] ?></a>
                    </td>
                    <td><a
                        href="/student/lms/study-material?subject=<?= $rowArr['subject_id'] ?>&type=2&duration=<?= $duration ?>"><?= $videoArr['total_vedio'] ?></a>
                    </td>
                    <td><a
                        href="/student/lms/subjects?id=<?= $rowArr['subject_id'] ?>&type=3&duration=<?= $duration ?>"><?= $assesmentArr['total_assesment'] ?></a>
                    </td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          <?php } else { ?>
            <tr>
              <h1 class="text-center" style="font-size: 20px;font-weight: 600;">No Record Found!</h1>
            </tr>
          <?php } ?>
        </div>

      </div>
      <div class="card-footer">
        <a href="/student/syllabus" class="btn btn-custom float-right">See All Subject</a>
      </div>
    </div>
  </div>
  <div class="col-lg-6 col-md-12 col-sm-12">
    <div class="card border  shadow custom_hs" style="border-radius:10px;">
      <div class="card-header custom-bg-primary custom_border_r text-white separator">
        <h5 class="fw-bold"><i class="ti-bell mr-2"></i> Notifications</h5>
      </div>
      <div class="card-body m-t-10">
        <div class="table-responsive">
          <?php
          $current_notification_id = 0;
          $session = $_SESSION['Admission_Session'];
          $filterQuery = studentSearchQuery();
          $result_record = $conn->query("SELECT Notifications_Generated.ID , Notification_Heading.Name as `heading` , JSON_UNQUOTE(JSON_EXTRACT(Notifications_Generated.published_on,'$[0].published')) AS `send_on` ,Notifications_Generated.Send_To as `send_to` , Notifications_Generated.Attachment as `document` FROM `Notifications_Generated` LEFT JOIN Notification_Heading ON Notification_Heading.ID = Notifications_Generated.Heading WHERE Notifications_Generated.Status = '1' AND (Notifications_Generated.Send_To = 'student' OR Notifications_Generated.Send_To = 'all') AND Notifications_Generated.university_id = '" . $_SESSION['university_id'] . "' $filterQuery ORDER BY Notifications_Generated.ID DESC LIMIT 4");
          $data = array();
          if ($result_record->num_rows > 0) { ?>
            <table class="table table-striped table-bordered">
              <thead>
                <tr>
                  <th>Regarding</th>
                  <th>Sent To</th>
                  <th>Sent On</th>
                  <th>Content</th>
                  <th>Attachment</th>
                </tr>
              </thead>
              <tbody>
                <?php

                while ($row = $result_record->fetch_assoc()) { ?>
                  <tr>
                    <td><?= ucfirst($row['heading']) ?></td>
                    <td><?= ucfirst($row['send_to']) ?></td>
                    <td><?= date_format(date_create($row['send_on']), "d-m-Y") ?></td>
                    <td class="text-center"><a type="btn btn-link" class="text-primary"
                        onclick="view_content('<?= $row['ID'] ?>');"><i class="fa fa-eye">View</i></a></td>
                    <td>
                      <?php if (!empty($row['document'])) { ?>
                        <a href="<?= $row['document'] ?>" target="_blank" download="<?= $row['heading'] ?>">Download</a>
                      <?php } else { ?>
                        <p>No Attachment</p>
                      <?php } ?>
                    </td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          <?php } else { ?>
            <div class="d-flex justify-content-center align-items-center w-100 h-100">
              <p class="h2 font-weight-bold">No Notification Found</p>
            </div>
          <?php } ?>
        </div>
      </div>
      <div class="card-footer">
        <a href="/student/notifications" class="btn btn-custom float-right">See All Notifications</a>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="welcomeModal" tabindex="-1" role="dialog" aria-labelledby="welcomeModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content shadow-lg" style="border-radius:10px;">
      <div class="modal-header text-center border-bottom-0 d-flex justify-content-center">
        <h5 class="modal-title h2 font-weight-bold w-100 text-center" id="welcomeModalLabel"> Welcome!</h5>
      </div>
      <div class="modal-body text-center pb-1">
        <div id="lottie-welcome" style="width: 200px; height: 200px; margin: 0 auto;"></div>
        <h4>Hello, <?= $_SESSION['Name'] ?> 👋</h4>
        <p class="mb-0">Welcome to ARNI University </p>
      </div>
      <div class="modal-footer justify-content-center border-top-0 pt-0">
        <button type="button" class="btn btn-success" data-dismiss="modal">Let's Go!</button>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
  function changeNotificationStatus(id) {
    $.ajax({
      url: '/app/notifications/current-notification?id=' + id,
      type: 'GET',
      success: function (data) {
        $("#md-modal-content").html(data);
        $("#mdmodal").modal('show');
      }
    })
  }

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
</script>

<?php

function studentSearchQuery(): string
{

  global $conn;
  $university_id = mysqli_real_escape_string($conn, $_SESSION['university_id']);
  $student_id = mysqli_real_escape_string($conn, $_SESSION['ID']);
  $sub_course_id = mysqli_real_escape_string($conn, $_SESSION['Sub_Course_ID']);
  $admission_session = mysqli_real_escape_string($conn, $_SESSION['Admission_Session_ID']);

  //$scheme = $conn->query("SELECT Scheme_ID FROM `Admission_Sessions` WHERE ID = '$admission_session'");
  //$scheme_id = mysqli_fetch_column($scheme);
  $searchQuery = "";

  $searchQuery .= "AND IF(Notifications_Generated.student_id != '',JSON_CONTAINS(Notifications_Generated.student_id,'[\"{$student_id}\"]'),true)";
  //$searchQuery .= "AND IF(Notifications_Generated.scheme_id != '',JSON_CONTAINS(Notifications_Generated.scheme_id,'[\"{$scheme_id}\"]'),true)";
  $searchQuery .= "AND IF(Notifications_Generated.admissionSession_id != '',JSON_CONTAINS(Notifications_Generated.admissionSession_id,'[\"{$admission_session}\"]'),true)";
  $searchQuery .= "AND IF(Notifications_Generated.course_id != '',JSON_CONTAINS(Notifications_Generated.course_id,'[\"{$sub_course_id}\"]'),true)";

  $combine_duration = '';

    $duration = mysqli_real_escape_string($conn, $_SESSION['Duration']);
    $combine_duration = $duration;
  

  $searchQuery .= "AND IF(Notifications_Generated.duration != '',JSON_UNQUOTE(JSON_EXTRACT(Notifications_Generated.duration,'$.{$sub_course_id}')) LIKE '%{$combine_duration}%' OR JSON_UNQUOTE(JSON_EXTRACT(Notifications_Generated.duration, '$.{$sub_course_id}')) = 'All',true)";

  return $searchQuery;
}

?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.10.2/lottie.min.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    lottie.loadAnimation({
      container: document.getElementById('lottie-welcome'), // ID of your container
      renderer: 'svg',
      loop: true,
      autoplay: true,
      path: '/assets/Animation - 1744950037628.json' //  CHANGE THIS PATH
    });
  });
</script>
<script>
  $(document).ready(function () {
    <?php if ($showWelcomeModal): ?>
      $('#welcomeModal').modal('show');
    <?php endif; ?>
  });

</script>