<?php
require '../../includes/db-config.php';
session_start();
if (isset($_GET['course_id']) && isset($_GET['semester']) && $_GET['lms'] == "lms") {
  $sub_course_ids = intval($_GET['course_id']);
  $query = '';
  $semesters = $_GET['semester'];
  $syllabus_query = $conn->query("SELECT Name, ID FROM Syllabi WHERE Sub_Course_ID = " . intval($sub_course_ids) . "  AND Semester = '" . mysqli_real_escape_string($conn, $semesters) . "' $query ORDER BY ID DESC");
  $bg_colors = array("0" => "bg-yellow-gradient", "1" => "bg-purple-gradient", "2" => "bg-green-gradient", "3" => "bg-aqua-gradient", "4" => "bg-red-gradient", "5" => "bg-aqua-gradient", "6" => "bg-maroon-gradient", "7" => "bg-teal-gradient", "8" => "bg-blue-gradient");
  $colorIndex = 0;

  if ($syllabus_query->num_rows > 0) {
    while ($rows = $syllabus_query->fetch_assoc()) {
      $clr = $bg_colors[$colorIndex % count($bg_colors)];
      $colorIndex++;

      ?>
      <div class="col-md-3">
        <div class="card info-box p-0">
          <a href="/student/lms/subjects?id=<?= $rows['ID'] ?>&type=1&duration=<?= $semesters ?>">
            <div class="card-img-top <?= $clr ?>">
              <p class="subject-name"><?= $rows['Name']; ?> </p>
            </div>
          </a>
          <div class="card-footer">
            <div class="row justify-content-between align-items-center">
              <?php
              $ebook_query = $conn->query("SELECT id FROM e_books WHERE subject_id = '" . $rows['ID'] . "' AND Course_ID='" . $_SESSION['Course_ID'] . "'  AND Sub_Course_ID='" . $_SESSION['Sub_Course_ID'] . "'  and Status = 1");
              $ebook_count = ($ebook_query->num_rows > 0) ? $ebook_query->num_rows : 0;

              $video_query = $conn->query("SELECT id FROM video_lectures WHERE subject_id = '" . $rows['ID'] . "' AND Course_ID='" . $_SESSION['Course_ID'] . "'  AND Sub_Course_ID='" . $_SESSION['Sub_Course_ID'] . "' and Status = 1");
              $video_count = ($video_query->num_rows > 0) ? $video_query->num_rows : 0;
              ?>
              <div class="col-md-4 text-center">
                <a href="/student/lms/subjects?id=<?= $rows['ID'] ?>&type=1&duration=<?= $semesters ?>"><i
                    class="ti-book mr-2"></i><span><?= $ebook_count ?></span></a>
              </div>
              <div class="col-md-4 text-center">
                <a href="/student/lms/subjects?id=<?= $rows['ID'] ?>&type=2&duration=<?= $semesters ?>"><i
                    class="ti- ti-video-clapper mr-2"></i><span><?= $video_count ?></span></a>
              </div>
              <div class="col-md-4 text-center">
                <a href="/student/lms/subjects?id=<?= $rows['ID'] ?>&type=3&duration=<?= $semesters ?>"><i
                    class=" ti-clipboard mr-2"></i><span>0</span></a>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php }
  } else { ?>
    <div class="col-md-4"></div>
    <div class="col-md-4" style="font-weight:700;font-size:19px"> Subjects Not Uploaded!</div>
    <div class="col-md-4"></div>
  <?php } ?>
<?php } ?>
