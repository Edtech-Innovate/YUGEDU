<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>LMS Layout with Dynamic Progress</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">

  <style>
    body {
      background-color: #f8f9fa;
      overflow-x: hidden;
    }

    .sidebar {
      background-color: #fff;
      padding: 1rem;
      height: 100vh;
      overflow-y: auto;
      position: sticky;
      top: 0;
      border-right: 1px solid #dee2e6;
      transition: all 0.3s ease;
    }

    .sidebar.collapsed {
      width: 0 !important;
      padding: 0 !important;
      overflow: hidden !important;
      border: none !important;
      display: none;
    }

    .main-expanded {
      width: 100% !important;
      flex: 0 0 100% !important;
      max-width: 100% !important;
    }

    .video-container {
      background-color: #fff;
      padding: 1.5rem;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      margin-bottom: 2rem;
    }

    .progress {
      height: 6px;
      background-color: #e9ecef;
    }

    .progress-bar {
      background-color: #34ab63;
      border: solid 1px #1ecf1e;
      border-radius: 7px;
    }

    .custom_accordion_item {
      border: none !important;
    }

    .accordion-button:not(.collapsed) {
      background-color: white;
    }

    .accordion-body ul li a {
      font-size: 0.9rem;
      cursor: pointer;
    }

    .w-autos {
      width: 200px !important;
    }

    .unit-video-list {
      list-style: none;
      padding-left: 0;
      margin: 0;
    }

    .unit-video-list li {
      display: flex;
      align-items: center;
      margin-bottom: 10px;
      font-size: 16px;
      color: #333;
      position: relative;
      padding-left: 30px;
      /* space for icon */
    }

    .unit-video-list li::before {
      content: "\f282";
      /* Remix Icon unicode */
      font-family: 'remixicon';
      /* make sure RemixIcon is loaded */
      font-size: 18px;
      color: #3c4e76;
      position: absolute;
      left: 0;
      top: 50%;
      transform: translateY(-50%);
      transition: color 0.3s ease;
    }

    .unit-video-list li a {
      text-decoration: none;
      color: #333;
      transition: color 0.3s ease;
    }

    .unit-video-list li:hover::before,
    .unit-video-list li:hover a {}

    .accordion-button::after {
      flex-shrink: 0 !important;
      width: var(--bs-accordion-btn-icon-width) !important;
      height: var(--bs-accordion-btn-icon-width) !important;
      margin-left: auto !important;
      content: "" !important;
      background-image: var(--bs-accordion-btn-icon) !important;
      background-repeat: no-repeat !important;
      background-size: var(--bs-accordion-btn-icon-width) !important;
      transition: var(--bs-accordion-btn-icon-transition) !important;
    }
  </style>
</head>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/db-config.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<?php
session_start();
$subect = [];
if (isset($_GET['subject']) && $_GET['subject'] != '') {
  $subjectId = $_GET['subject'];
  $query = "select * from Syllabi where ID=$subjectId";
  $result = $conn->query($query);
  $subect = $result->fetch_assoc();

  if ($result->num_rows > 0) {
    $subCourseId = $_SESSION['Sub_Course_ID'];
    $chapterQuery = "select * from Chapter where Sub_Course_ID=$subCourseId AND Subject_ID=$subjectId";
    $chapterResult = $conn->query($chapterQuery);
    $chapters = [];
    while ($row = $chapterResult->fetch_assoc()) {
      $chapters[] = $row;
    }
  }
} else {
  header('Location:/student/lms/videos');
}
?>

<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm sticky-top">
    <div class="container-fluid">
      <button class="btn btn-outline-primary ms-4 me-2" id="toggleSidebarBtn">☰</button>
      <a class="navbar-brand fw-bold" href="#"><?php echo $subect['Name']; ?> </a>
      <div class="d-flex ms-auto gap-2 flex-row flex-wrap">
        <div class="w-autos">
          <select class="form-select form-select-sm" id="chapters">
            <option value="">Select Chapter</option>
            <?php
            foreach ($chapters as $chapter) { ?>
              <option value="<?= $chapter['ID'] ?>" <?php echo ($_SESSION['selectedChapter'] ?? 0) == $chapter['ID'] ? 'selected' : '' ?>><?= $chapter['Name'] ?></option>
            <?php }
            ?>
          </select>
        </div>
        <div class="w-autos">
          <a class="btn btn-primary" href="/student/lms/lms">Back To Subjects</a>
        </div>
        <!--<div class="w-autos">-->
        <!-- <select class="form-select form-select-sm" id="units">-->
        <!-- <option>Unit1</option>-->
        <!-- </select>-->
        <!--</div>-->
        <!--<div class="w-autos">-->
        <!-- <select class="form-select form-select-sm" id="topic">-->
        <!-- <option>Topic1</option>-->
        <!-- </select>-->
        <!--</div>-->
        <!--<div class="w-autos">-->
        <!-- <select class="form-select form-select-sm" id="sub-topic">-->
        <!-- <option>Sub-topic1</option>-->
        <!-- </select>-->
        <!--</div>-->
      </div>
    </div>
  </nav>

  <!-- Layout -->
  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <div class="col-md-3 sidebar " id="sidebar">
        <?php
        if (isset($_SESSION['selectedChapter']) && $_SESSION['selectedChapter'] != '') {
          $chapterQuery = "SELECT * FROM Chapter WHERE ID=" . $_SESSION['selectedChapter'];
          $chapterResult = $conn->query($chapterQuery);

          $chapterIndex = 1;
          $student_id = $_SESSION['ID'];

          function getVideoProgress($conn, $student_id, $video_id)
          {
            $q = "SELECT progress FROM student_progress WHERE students_id = $student_id AND videos_id = $video_id";
            $res = $conn->query($q);
            if ($res->num_rows > 0) {
              $data = $res->fetch_assoc();
              return round($data['progress']);
            }
            return 0;
          }

          while ($chapter = $chapterResult->fetch_assoc()) {
            $chapterId = $chapter['ID'];

            // Calculate average chapter progress
            $chapterVideoQuery = "SELECT id FROM video_lectures WHERE chapter_id = $chapterId";
            $chapterVideos = $conn->query($chapterVideoQuery);
            $videoIds = [];
            while ($row = $chapterVideos->fetch_assoc()) {
              $videoIds[] = $row['id'];
            }
            $chapterAvg = 0;
            if (!empty($videoIds)) {
              $videoStr = implode(",", $videoIds);
              $progQuery = "SELECT AVG(progress) as avg_progress FROM student_progress WHERE students_id = $student_id AND videos_id IN ($videoStr)";
              $progRes = $conn->query($progQuery);
              $chapterAvg = round($progRes->fetch_assoc()['avg_progress'] ?? 0);
            }
            ?>
            <div class="accordion">
              <div class="accordion-item">
                <h2 class="accordion-header" id="headingChapter<?= $chapterIndex ?>">
                  <button class="accordion-button" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseChapter<?= $chapterIndex ?>">
                    <div class="w-100 me-2">
                      <div class="d-flex justify-content-between mb-1">
                        <small><?= htmlspecialchars($chapter['Name']) ?></small>
                        <small><?= $chapterAvg ?>%</small>
                      </div>
                      <div class="progress">
                        <div class="progress-bar" style="width: <?= $chapterAvg ?>%"></div>
                      </div>
                    </div>
                  </button>
                </h2>
                <div id="collapseChapter<?= $chapterIndex ?>" class="accordion-collapse collapse">
                  <div class="accordion-body p-2">

                    <!-- Chapter-level Videos -->
                    <ul>
                      <?php
                      $chapterVidQ = "SELECT * FROM video_lectures WHERE chapter_id=$chapterId AND unit_id=0";
                      // $chapterVidQ = "SELECT * FROM video_lectures WHERE chapter_id=$chapterId ";

                      $chapterVidRes = $conn->query($chapterVidQ);
                      while ($video = $chapterVidRes->fetch_assoc()) {
                        // echo "<pre>";
                        // print_r($video);
                        
                        $vidProgress = getVideoProgress($conn, $student_id, $video['id']);
                        if($video['videos_categories']==1){
                              echo "<li class='d-flex justify-content-between align-items-center'>
                        <a href='javascript:void(0)' onclick='setVideo(\"{$video['video_url']}\",\"{$video['unit']}\",\"{$video['id']}\",\"{$video['videos_categories']}\")'>{$video['unit']}</a>
                        <small>{$vidProgress}%</small> </li>";
                        }else{
                        echo "<li class='d-flex justify-content-between align-items-center'>
                        <a href='javascript:void(0)' onclick='setVideo(\"/{$video['video_url']}\",\"{$video['unit']}\",\"{$video['id']}\",\"{$video['videos_categories']}\")'>{$video['unit']}</a>
                        <small>{$vidProgress}%</small> </li>";
                        }
                
                     
                      }
                      ?>
                    </ul>

                    <!-- Units Inside Chapter -->
                    <div class="accordion" id="unitAccordion<?= $chapterIndex ?>">
                      <?php
                      $unitQuery = "SELECT * FROM Chapter_Units WHERE Chapter_ID = $chapterId";
                      $unitResult = $conn->query($unitQuery);
                      $unitIndex = 1;
                      while ($unit = $unitResult->fetch_assoc()) {
                        $unitId = $unit['ID'];

                        // Unit-level video progress
                        $unitVidQ = "SELECT id FROM video_lectures WHERE chapter_id=$chapterId AND unit_id=$unitId";
                        $unitVidRes = $conn->query($unitVidQ);
                        $unitVidIds = [];
                        while ($row = $unitVidRes->fetch_assoc()) {
                          $unitVidIds[] = $row['id'];
                        }
                        $unitAvg = 0;
                        if (!empty($unitVidIds)) {
                          $unitVidStr = implode(",", $unitVidIds);
                          $unitProgQ = "SELECT AVG(progress) as avg_progress FROM student_progress WHERE students_id = $student_id AND videos_id IN ($unitVidStr)";
                          $unitProgRes = $conn->query($unitProgQ);
                          $unitAvg = round($unitProgRes->fetch_assoc()['avg_progress'] ?? 0);
                        }
                        ?>
                        <div class="accordion-item custom_accordion_item">
                          <h2 class="accordion-header" id="unit<?= $chapterIndex . '_' . $unitIndex ?>">
                            <button class="accordion-button collapsed p-1" type="button" data-bs-toggle="collapse"
                              data-bs-target="#unitCollapse<?= $chapterIndex . '_' . $unitIndex ?>">
                              <div class="w-100 me-2">
                                <div class="d-flex justify-content-between mb-1">
                                  <small><?= htmlspecialchars($unit['Name']) ?></small>
                                  <small><?= $unitAvg ?>%</small>
                                </div>
                                <div class="progress mb-2">
                                  <div class="progress-bar" style="width: <?= $unitAvg ?>%"></div>
                                </div>
                              </div>
                            </button>
                          </h2>
                          <div id="unitCollapse<?= $chapterIndex . '_' . $unitIndex ?>" class="accordion-collapse collapse">
                            <div class="accordion-body p-2">
                              <!-- Unit-level Videos -->
                              <ul class="unit-video-list">
                                <?php
                                $unitVideosQ = "SELECT * FROM video_lectures WHERE chapter_id=$chapterId AND unit_id=$unitId";

                                $unitVideosRes = $conn->query($unitVideosQ);
                                while ($video = $unitVideosRes->fetch_assoc()) {
// echo "<pre>";
// print_r($video);
                                  $vidProgress = getVideoProgress($conn, $student_id, $video['id']);
                                  if($video['videos_categories']==1){
                                           echo "<li class='d-flex justify-content-between align-items-center'>
                                  <a href='javascript:void(0)' onclick='setVideo(\"{$video['video_url']}\",\"{$video['unit']}\",\"{$video['id']}\",\"{$video['videos_categories']}\")'>{$video['unit']}</a>
                                  <small>{$vidProgress}%</small>
                                </li>";
                                  }else{
                                  echo "<li class='d-flex justify-content-between align-items-center'>
                                  <a href='javascript:void(0)' onclick='setVideo(\"/{$video['video_url']}\",\"{$video['unit']}\",\"{$video['id']}\",\"{$video['videos_categories']}\")'>{$video['unit']}</a>
                                  <small>{$vidProgress}%</small>
                                </li>";
                                  }
                                }
                                ?>
                              </ul>
                            </div>
                          </div>
                        </div>
                        <?php
                        $unitIndex++;
                      }
                      ?>
                    </div> <!-- unitAccordion -->
                  </div>
                </div>
              </div>
            </div>
            <?php
            $chapterIndex++;
          }
        }
        ?>

      </div>

      <!-- Main Content -->
      <div class="col-md-9 p-4" id="mainContent">
        <div class="video-container">
          <h3 class="mb-3" id="videoTitle"></h3>
          <div class="ratio ratio-16x9 mb-3">
            <video controls id="mainVideo">
              <source src="assets/sample-video.mp4" type="video/mp4" id="videocontainer" data-video_id="" />
            </video>
            <iframe  id="mainIframe" width="560" height="315" src="" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            <!--<iframe id="mainIframe" src="" frameborder="0"></iframe>-->
            <!--<iframe  id="mainIframe" width="760" height="350" src="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen=""></iframe>-->
          </div>
          <!--<p>This is a detailed explanation of the selected sub-topic.</p>-->
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="footer py-3 border-top shadow-sm">
    <div class="row">
      <div class="col-md-3 " id="footer_col"></div>
      <div class="col-md-9" id="footer_col1">
        <div class="container d-flex justify-content-between align-items-center">
          <!--<button class="btn btn-outline-primary">Previous</button>-->
          <div>
            <!-- <input type="checkbox" id="completeCheckbox" class="form-check-input">
        <label for="completeCheckbox" class="form-check-label ms-1">Mark as Done</label> -->
          </div>
          <!--<button class="btn btn-primary">Next</button>-->
        </div>
      </div>
    </div>
  </footer>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <!-- Custom Script -->
  <script>
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleSidebarBtn');
    const mainContent = document.getElementById('mainContent');

    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('collapsed');
      mainContent.classList.toggle('main-expanded');
      const footerCol = document.getElementById('footer_col');
      const footerCol1 = document.getElementById('footer_col1');
      footerCol.classList.add('d-none');
      footerCol1.classList.add('w-100');
    });

    const video = document.getElementById('mainVideo');
    const videoTitle = document.getElementById('videoTitle');

    document.querySelectorAll('.video-link').forEach(link => {
      link.addEventListener('click', () => {
        const videoSrc = link.dataset.video;
        const videoId = link.dataset.id;
        const level = link.dataset.level;

        video.querySelector('source').src = videoSrc;
        video.load();
        videoTitle.innerText = link.textContent;

        // Mark progress complete for clicked video
        updateProgress(`${videoId}ProgressBar`, `${videoId}ProgressText`, 100);

        // Recalculate parents
        recalculateParentProgress();
      });
    });

    function updateProgress(barId, textId, percent) {
      document.getElementById(barId).style.width = percent + '%';
      document.getElementById(textId).textContent = percent + '%';
    }

    function recalculateParentProgress() {
      const subTopics = ['sub1', 'sub2', 'sub3'];
      const subTotal = subTopics.reduce((sum, id) => {
        return sum + parseInt(document.getElementById(`${id}ProgressBar`).style.width);
      }, 0);
      const subProgress = Math.round(subTotal / subTopics.length);
      updateProgress('topicProgressBar', 'topicProgressText', subProgress);

      const topicProgress = subProgress;
      updateProgress('unitProgressBar', 'unitProgressText', topicProgress);

      const unitProgress = topicProgress;
      updateProgress('chapterProgressBar', 'chapterProgressText', unitProgress);
    }

    //get units
    $('#chapters').on('change', function () {
      var chapterId = $(this).val();
      if (chapterId != "") {
        $.ajax({
          url: "/app/subjects/units?chapterId=" + chapterId,
          type: 'get',
          success: function (res) {
            // $('#units').html(res);
            window.location.reload()
          }
        })
      }
    });

    //get Topics
    $('#units').on('change', function () {
      var unitId = $(this).val();
      if (unitId != "") {
        $.ajax({
          url: "/app/subjects/topic?unitId=" + unitId,
          type: 'get',
          success: function (res) {
            $('#topic').html(res);
          }
        })
      }
    });
    //get Sub Topics
    $('#topic').on('change', function () {
      var topicId = $(this).val();
      if (topicId != "") {
        $.ajax({
          url: "/app/subjects/sub_topic?topicId=" + topicId,
          type: 'get',
          success: function (res) {
            $('#sub-topic').html(res);
          }
        })
      }
    });

    function setVideo(url, title, id, category) {
      $('#videocontainer').attr('src', url);
      $('#videoTitle').text(title);
      $('#videocontainer').attr('data-video_id', id);
      
      if (category == 1) {
        //     let videoId = id;
        //  if (!videoId && url.includes("youtube.com/watch")) {
        //     const urlParams = new URL(url).searchParams;
        //     videoId = urlParams.get("v");
        //  }
       
         

        document.getElementById("mainVideo").style.display = 'none';
        document.getElementById("mainIframe").style.display = 'block';
        var site = url + '?toolbar=0&navpanes=0&scrollbar=0';
        document.getElementById('mainIframe').src = url;

      } else {
        document.getElementById("mainIframe").style.display = 'none';
        document.getElementById("mainVideo").style.display = 'block';
        document.getElementById('mainVideo').load();
      }
    }
  </script>
  <script>
    let videos = document.getElementById('mainVideo');

    videos.addEventListener('pause', function () {
      const pauseTime = videos.currentTime;
      const total = videos.duration;

      // Calculate percentage
      const progressPercent = ((pauseTime / total) * 100).toFixed(2);

      // AJAX POST
      fetch('/app/videos/save-progress', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          video_id: $('#videocontainer').attr('data-video_id'), // Replace dynamically
          total_duration: total,
          pause_time: pauseTime,
          progress: progressPercent
        })
      })
        .then(res => res.json())
        .then(data => console.log("Saved:", data))
        .catch(err => console.error("Save error:", err));
    });
  </script>

</body>

</html>