<?php
if (isset($_POST['course_id']) && isset($_POST['semester']) && isset($_POST['start_date']) && isset($_POST['end_date'])) {
  require '../../includes/db-config.php';
  session_start();

  $condition = "";
  $addedFor = "";

  if (!empty($_POST['course_id'])) {
    $sub_course_id = intval($_POST['course_id']);
    $condition .= " AND Syllabi.Sub_Course_ID = $sub_course_id";
  }

  if (!empty($_POST['semester'])) {
    $semester = explode("|", $_POST['semester']);
    $scheme = $semester[0];
    $semester = $semester[1];

    $condition .= " AND Syllabi.Semester = $semester AND Syllabi.Scheme_ID = $scheme";
  }

  if (in_array($_SESSION['Role'], ['Center', 'Sub-Center'])) {
    $ids = array();
    $sub_course_ids = $conn->query("SELECT Sub_Course_ID FROM Center_Sub_Courses WHERE `User_ID` = " . $_SESSION['ID'] . "");
    while ($sub_course_id = $sub_course_ids->fetch_assoc()) {
      $ids[] = $sub_course_id['Sub_Course_ID'];
    }
    $condition .= " AND Sub_Courses.ID IN (" . implode(",", $ids) . ")";

    $users = array($_SESSION['ID']);
    if ($_SESSION['Role'] == 'Center') {
      $downlines = $conn->query("SELECT Sub_Center FROM Center_SubCenter WHERE Center = " . $_SESSION['ID']);
      while ($downline = $downlines->fetch_assoc()) {
        $users[] = $downline['Sub_Center'];
      }
    }

    $addedFor = " AND Students.Added_For IN (" . implode(",", $users) . ")";
  }



  $isRescheduled = 0;
  $checkForRescheduled = $conn->query("SELECT ID FROM Reschedule_Date_Sheets WHERE University_ID = " . $_SESSION['university_id']);
  if ($checkForRescheduled->num_rows > 0) {
    $isRescheduled = 1;
    if (!empty($_POST['start_date']) && !empty($_POST['end_date'])) {
      $condition .= " AND Reschedule_Date_Sheets.Exam_Date BETWEEN '" . $_POST['start_date'] . "' AND '" . $_POST['end_date'] . "'";
    }
    $date_sheets = $conn->query("SELECT Reschedule_Date_Sheets.Date_Sheet_ID as ID, Exam_Sessions.`Name` AS examSession,CONCAT(Courses.Short_Name,' (',Sub_Courses.`Name`,')')AS course,Syllabi.Code,Syllabi.`Name`,Syllabi.Semester,Reschedule_Date_Sheets.Exam_Date,Reschedule_Date_Sheets.Start_Time,Reschedule_Date_Sheets.End_Time,(SELECT COUNT(ID)FROM Reschedule_Date_Sheets as stCount WHERE Reschedule_Date_Sheets.Syllabus_ID=stCount.Syllabus_ID)AS studentCount,(SELECT COUNT(ID)FROM MCQs WHERE MCQs.Date_Sheet_ID=Reschedule_Date_Sheets.Date_Sheet_ID)AS questionCount FROM Reschedule_Date_Sheets LEFT JOIN Exam_Sessions ON Reschedule_Date_Sheets.Exam_Session_ID=Exam_Sessions.ID LEFT JOIN Syllabi ON Reschedule_Date_Sheets.Syllabus_ID=Syllabi.ID LEFT JOIN Sub_Courses ON Syllabi.Sub_Course_ID=Sub_Courses.ID LEFT JOIN Courses ON Sub_Courses.Course_ID=Courses.ID LEFT JOIN Students ON Reschedule_Date_Sheets.Student_ID = Students.ID WHERE Reschedule_Date_Sheets.University_ID= " . $_SESSION['university_id'] . " $condition GROUP BY Code,Semester ORDER BY Exam_Date ASC");
  } else {
    if (!empty($_POST['start_date']) && !empty($_POST['end_date'])) {
      $condition .= " AND Date_Sheets.Exam_Date BETWEEN '" . $_POST['start_date'] . "' AND '" . $_POST['end_date'] . "'";
    }
    $date_sheets = $conn->query("SELECT Date_Sheets.ID, Exam_Sessions.`Name` as examSession, CONCAT(Courses.Short_Name,' (',Sub_Courses.`Name`,')')as course, Syllabi.Code, Syllabi.`Name`, Syllabi.Semester, Date_Sheets.Exam_Date, Date_Sheets.Start_Time, Date_Sheets.End_Time, (SELECT COUNT(ID)FROM Students WHERE Exam=1 AND Students.Sub_Course_ID = Syllabi.Sub_Course_ID AND Students.Duration = Syllabi.Semester $addedFor) AS studentCount, (SELECT COUNT(ID) FROM MCQs WHERE Date_Sheet_ID = Date_Sheets.ID) as questionCount FROM Date_Sheets LEFT JOIN Exam_Sessions ON Date_Sheets.Exam_Session_ID = Exam_Sessions.ID LEFT JOIN Syllabi ON Date_Sheets.Syllabus_ID = Syllabi.ID LEFT JOIN Sub_Courses ON Syllabi.Sub_Course_ID = Sub_Courses.ID LEFT JOIN Courses ON Sub_Courses.Course_ID = Courses.ID WHERE Date_Sheets.University_ID = " . $_SESSION['university_id'] . " $condition ORDER BY Exam_Date ASC");
  }

  if ($date_sheets->num_rows == 0) {
    echo '<center><h1>Date Sheet Not Available</h1></center>';
  } else {
?>
    <div class="table-responsive">
      <table id="date-sheet-table" class="table table-striped nowrap">
        <thead>
          <tr>
            <th>#</th>
            <th>Exam Session</th>
            <th>Course</th>
            <th>Semester</th>
            <th>Paper Code</th>
            <th>Paper Name</th>
            <th>Date</th>
            <th>Time</th>
            <th>Student Count</th>
            <?php if (!in_array($_SESSION['Role'], ['Center', 'Sub-Center'])) { ?>
              <th>Question Bank</th>
            <?php } ?>
          </tr>
        </thead>
        <tbody>
          <?php
          $counter = 1;
          while ($date_sheet = $date_sheets->fetch_assoc()) {
          ?>
            <tr>
              <td><?= $counter++ ?></td>
              <td><?= $date_sheet['examSession'] ?></td>
              <td><?= $date_sheet['course'] ?></td>
              <td><?= $date_sheet['Semester'] ?></td>
              <td><?= $date_sheet['Code'] ?></td>
              <td><?= $date_sheet['Name'] ?></td>
              <td><?= date("d-m-Y", strtotime($date_sheet['Exam_Date'])) //l, dS M, Y 
                  ?></td>
              <td><?= date("h:i A", strtotime($date_sheet['Start_Time'])) . " to " . date("h:i A", strtotime($date_sheet['End_Time'])) ?></td>
              <td><?= $date_sheet['studentCount'] ?></td>
              <?php if (!in_array($_SESSION['Role'], ['Center', 'Sub-Center'])) { ?>
                <td>
                  <span class="cursor-pointer" onclick="uploadQuestionBank(<?= $date_sheet['ID'] ?>)">Upload</span>
                  <?php if ($date_sheet['questionCount'] > 0) { ?><span class="ml-2">(<?= $date_sheet['questionCount'] ?> Ques Uploaded)</span> <?php } ?>
                </td>
              <?php } ?>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>

    <script>
      function uploadQuestionBank(id) {
        $.ajax({
          url: '/app/question-banks/create',
          type: "POST",
          data: {
            id: id
          },
          success: function(data) {
            $("#lg-modal-content").html(data);
            $("#lgmodal").modal('show');
          }
        })
      }
    </script>

    <script>
      $(function() {
        $('#date-sheet-table').DataTable({
          paging: false,
          dom: 'Bfrtip',
          buttons: [
            'copyHtml5',
            {
              extend: 'excelHtml5',
              text: 'Save as Excel',
              title: 'Date Sheet'
            }
          ]
        });
      });
    </script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<?php }
} else {
  echo '<center><h1>Date Sheet Not Available</h1></center>';
}
?>