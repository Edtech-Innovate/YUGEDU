<?php
require '../../includes/db-config.php';
session_start();
$id = $_GET['id'];
$videos = $conn->query("SELECT * FROM video_lectures WHERE ID = '" . $_GET['id'] . "'");
$video = mysqli_fetch_assoc($videos);
//echo "<pre>";
//print_r($video);
?>

<!-- Modal -->
<div class="modal-header clearfix text-left">
  <button aria-label="" type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="pg-icon">close</i>
  </button>
  <h5 class="font-weight-bold text-black">Edit <span class="semi-bold">Videos</span></h5>
</div>

<form role="form" id="form-add-videos" action="/app/videos/update" method="POST" enctype="multipart/form-data">
  <div class="modal-body">
    <!-- University & Course -->
    <input type="hidden" name="id" value="<?= $video['id'] ?>">
    <div class="row">
      <div class="col-md-6">
        <div class="form-group form-group-default required">
          <label>Course</label>
          <select class="full-width select2" style="border: transparent;" id="course_id" name="course_id"
            onchange="getSubCourse(this.value);">
            <option value="">Select Course</option>
            <?php
            $programs = $conn->query("SELECT ID,Name,Short_Name FROM Courses WHERE Status=1 ORDER BY Name ASC");
            while ($program = $programs->fetch_assoc()) { ?>
              <option value="<?= $program['ID'] ?>" <?= ($program['ID'] == $video['course_id']) ? "selected" : "" ?>>
                <?= $program['Name'] . ' (' . $program['Short_Name'] . ')' ?>
              </option>
            <?php } ?>
          </select>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group form-group-default required">
          <label>Specialization/Course</label>
          <select class="full-width select2" style="border: transparent;" id="sub_course_id" name="sub_course_id"
            onchange="getDuration(this.value)">
            <option value="">Select</option>

          </select>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group form-group-default required">
          <label>Duration </label>
          <select class="full-width" style="border: transparent;" name="duration" id="duration"
            data-placeholder="Choose Duration" onchange="getSubjects(this.value)">
            <option value="">Select Duration</option>
          </select>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group form-group-default required">
          <label>Subject</label>
          <select class="full-width" style="border: transparent;" onchange="getChapter(this.value)" id="subject_id"
            name="subject_id">
            <option value="">Select Subject</option>
          </select>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group form-group-default required">
          <label>Unit</label>
          <select class="full-width" style="border: transparent;" onchange="getModule(this.value)" id="unit_id"
            name="unit_id">
            <option value="">Select</option>
          </select>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group form-group-default required">
          <label>Module</label>
          <select class="full-width" style="border: transparent;" id="module_id" name="module_id">
            <option value="">Select</option>
          </select>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group form-group-default required">
          <label>Unit/Title</label>
          <input type="text" name="unit" class="form-control" value="<?php print $video['unit'] ?>" required>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group form-group-default ">
          <label>Desciption </label>
          <textarea name="description" class="form-control" rows="6"><?php print $video['description'] ?></textarea>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group form-group-default required">
          <label>Video Type</label>
          <select class="full-width" style="border: transparent;" onchange="showVideoType(this.value)" id="video_type" name="video_type">
              <option value="">Video Category Type</option>
              <option value="2" <?php echo ($video['videos_categories'] == 2) ? "selected" : ""; ?>>Recorded Video</option>
              <option value="1" <?php echo ($video['videos_categories'] == 1) ? "selected" : ""; ?>>Link Type</option>
          </select>
        </div>
      </div>
      <div class="col-md-6 video_link_box">
        <div class="form-group form-group-default">
          <label>Video Link(Paste Here)</label>
          <input type="text" name="uniform" id="uniform" value="<?= $video['video_url'] ?>" class="form-control">
        </div>
      </div>
    </div>
 
    <div class="row videoSec">
      <div class="col-md-6">
        <div class="form-group form-group-default ">
          <label>Thumbnail *</label>
          <input type="file" name="thumbnail" class="dropify" accept="image/png, image/jpg, image/jpeg, image/svg">
          <?php if (!empty($id) && !empty($video['thumbnail_url'])) { ?>
            <a href="/../<?php print !empty($id) ? $video['thumbnail_url'] : '' ?>" download=""><i
                class="uil uil-down-arrow"></i>
            </a>
            <input type="hidden" value="<?= $video['thumbnail_url'] ?>" name="updated_thumbnail">
          <?php } ?>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group form-group-default ">
          <label>Video *</label>
          <input type="file" name="video" class="dropify" accept="video">
          <?php if (!empty($id) && !empty($video['video_url'])) { ?>
            <a href="/../<?php print !empty($id) ? $video['video_url'] : '' ?>" download=""><i
                class="uil uil-down-arrow"></i>
            </a>
            <input type="hidden" value="<?= $video['video_url'] ?>" name="updated_video_url">
          <?php } ?>
        </div>
      </div>
    </div>



  <div class="modal-footer clearfix justify-content-center">
    <div class="col-md-12 m-t-10 sm-m-t-10 text-end">
      <!--<button aria-label="" type="submit" class="btn btn-primary btn-cons btn-animated from-left">-->
      <!--  <span>Update</span>-->
      <!--  <span class="hidden-block">-->
      <!--    <i class="pg-icon">tick</i>-->
      <!--  </span>-->
      <!--</button>-->
        <button aria-label="" type="button" data-dismiss="modal" class=" btn btn-secondary mr-2">
        <i class="ti ti-circle-dashed-x mr-2"></i> Close</button>
        <button aria-label="" type="submit" class="btn btn-primary ">
        <i class="ti ti-circle-check mr-2"></i> Update</button>
    </div>
  </div>
</form>


<script>
  $(function () {
    $("#course_id, #sub_course_id, #duration, #subject_id, #video_type").select2({
      searchable: true,
      dropdownParent: $('#lg-modal-content')
    });
    showVideoType('<?= $video['videos_categories'] ?>');
  })

  function showVideoType(video_type) {
    if (video_type == 2) {
      $(".video_link_box").hide();
      $(".videoSec").show();
    } else if (video_type == 1) {
      $(".video_link_box").show();
      $(".videoSec").hide();
    } else {
      $(".video_link_box").hide();
      $(".videoSec").hide();
    }

  }
  $(function () {
    getSubCourse('<?= $video['course_id'] ?>');
  })

  function getDuration(id) {
    $.ajax({
      url: '/app/subjects/get-duration',
      data: { id: id },
      type: 'POST',
      success: function (data) {
        $("#duration").html(data);
        $("#duration").val('<?= $video['semester'] ?>');
        getSubjects('<?= $video['semester'] ?>');
      }
    })
  }

  function getSubjects(duration) {
    var sub_course_id = $("#sub_course_id").val();
    $.ajax({
      url: '/app/videos/subjects',
      type: 'POST',
      dataType: 'text',
      data: {
        'duration': duration,
        'sub_course_id': sub_course_id,
      },
      success: function (result) {
        $('#subject_id').html(result);
        $("#subject_id").val('<?= $video['subject_id'] ?>');
        getChapter('<?= $video['subject_id'] ?>');
      }
    })
  }

  function getChapter(subject_id) {
    var sub_course_id = $("#sub_course_id").val();
    var duration = $("#duration").val();

    $.ajax({
      url: '/app/videos/get-chapter',
      type: 'POST',
      dataType: 'text',
      data: {
        type: 'chapter',
        duration: duration,
        sub_course_id: sub_course_id,
        subject_id: subject_id,
      },
      success: function (result) {
        $('#unit_id').html(result);
        $("#unit_id").val('<?= $video['chapter_id'] ?>');

        getModule('<?= $video['chapter_id'] ?>');

        $("#unit_id").select2({
          searchable: true,
          dropdownParent: $('#lg-modal-content')
        });
      }
    })
  }

  function getModule(chapter_id) {
    $.ajax({
      url: '/app/videos/get-chapter',
      type: 'POST',
      dataType: 'text',
      data: {
        type: 'unit',
        chapter_id: chapter_id,
      },
      success: function (result) {
        $('#module_id').html(result);
        $("#module_id").val('<?= $video['unit_id'] ?>');
        $("#module_id").select2({
          searchable: true,
          dropdownParent: $('#lg-modal-content')
        });
      }
    })
  }

  function getSubCourse(course_id) {

    $.ajax({
      url: '/app/e-books/sub_course',
      type: 'POST',
      dataType: 'text',
      data: {
        'course_id': course_id
      },
      success: function (result) {
        $('#sub_course_id').html(result);

        var sub_course = $("#sub_course_id").val();

        getDuration(sub_course);

      }
    })
  }


  $(function () {
    $('#form-add-videos').validate({
      rules: {
        course_id: {
          required: true
        },
        subject_id: {
          required: true
        },
        // thumbnail: {
        // required: true
        // },
        // video: {
        // required: true
        // },
        unit: {
          required: true
        },
        semester: {
          required: true
        },
      },
      highlight: function (element) {
        //$(element).addClass('error');
        $(element).closest('.form-control').addClass('has-error');
      },
      unhighlight: function (element) {
        //$(element).removeClass('error');
        $(element).closest('.form-control').removeClass('has-error');
      }
    });
  })

  //form-add-videos


  $('#form-add-videos').submit(function (e) {
    if ($('#form-add-videos').valid()) {
      var formData = new FormData(this);
      e.preventDefault();
      $.ajax({
        url: $(this).attr('action'),
        type: "POST",
        data: formData,
        contentType: false,
        cache: false,
        processData: false,
        dataType: 'json',
        success: function (data) {
          if (data.status == 200) {
            notification('success', data.message);
            $('.modal').modal('hide');
            $('#video_lectures-table').DataTable().ajax.reload(null, false);
          } else {
            notification('danger', data.message);
          }
        },
        error: function (data) {
          notification('danger', 'Server is not responding. Please try again later');
        }
      });
    } else {
      //notification('danger', 'Invalid form information.');
    }
  });



</script>