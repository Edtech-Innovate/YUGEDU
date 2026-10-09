<?php
require '../../includes/db-config.php';
session_start();
//if($_SESSION['university_id'] == 48){}
?>

<!-- Modal -->
<div class="modal-header clearfix text-left">
  <button aria-label="" type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="pg-icon">close</i>
  </button>
  <h5 class="font-weight-bold text-black ">Add <span class="semi-bold">Note</span></h5>
</div>

<form role="form" id="form-add-e-book" action="/app/notes/store" method="POST" enctype="multipart/form-data">
  <div class="modal-body">
    <!-- University & Course -->
    <div class="row">

      <div class="col-md-6">
        <div class="form-group form-group-default required">
          <label>Specialization/Course</label>
          <select class="full-width select2" style="border: transparent;" onchange="getDuration(this.value);" id="course_id" name="course_id" onchange="getSubjects(this.value);">
            <option value="">Select</option>
            <?php
            $programs = $conn->query("SELECT Sub_Courses.ID,CONCAT(Sub_Courses.Name,'(',Courses.Short_Name,')') AS Short_Name  FROM Sub_Courses LEFT JOIN Courses on Courses.ID = Sub_Courses.Course_ID WHERE Sub_Courses.Status = 1 AND Sub_Courses.University_ID = '".$_SESSION['university_id']."' ORDER BY Sub_Courses.Name ASC");
            while ($program = $programs->fetch_assoc()) { ?>
              <option value="<?= $program['ID'] ?>">
                <?= $program['Name'] . ' (' . $program['Short_Name'] . ')' ?>
              </option>
            <?php } ?>
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
          <select class="full-width" style="border: transparent;" onchange="getChapter(this.value)" id="subject_id" name="subject_id">
            <option value="">Select</option>
          </select>
        </div>
      </div>
      
    <div class="col-md-6">
        <div class="form-group form-group-default required">
          <!--<label>Unit</label>-->
                    <label>Chapter</label>

          <select class="full-width" style="border: transparent;"  onchange="getModule(this.value)" id="unit_id" name="unit_id">
            <option value="">Select</option>
          </select>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group form-group-default required">
          <label>Unit</label>
          <select class="full-width" style="border: transparent;"  id="module_id" name="module_id">
            <option value="">Select</option>
          </select>
        </div>
      </div>
    <div class="col-md-6">
        <div class="form-group form-group-default required">
          <label>Note file *</label>
          <input type="file" name="file" class="form-control" accept="image/png, image/jpg, image/jpeg, image/svg">
        </div>
      </div>
    <div class="col-md-6">
        <div class="form-group form-group-default required">
          <label>Note Name</label>
          <input type="text" name="ebook_name" class="form-control" placeholder="Enter Note Name" required>
        </div>
      </div>
  </div>


  <div class="modal-footer clearfix justify-content-end">
    <div class="col-md-12 text-end m-t-10 sm-m-t-10">
      <!--<button aria-label="" type="submit" class="btn btn-primary btn-cons btn-animated from-left">-->
      <!--  <span>Save</span>-->
      <!--  <span class="hidden-block">-->
      <!--    <i class="pg-icon">tick</i>-->
      <!--  </span>-->
      <!--</button>-->
        <button aria-label="" type="button" data-dismiss="modal" class=" btn btn-secondary mr-2">
        <i class="ti ti-circle-dashed-x mr-2"></i> Close</button>
        <button aria-label="" type="submit" class="btn btn-primary ">
        <i class="ti ti-circle-check mr-2"></i> Save</button>
    </div>
  </div>
</form>


<script>
  $(function() {
      $("#course_id").select2({
      searchable: true,
      dropdownParent: $('#lg-modal-content')
    });
    
  })

  function getSubjects(duration) {
      var sub_course_id = $("#course_id").val();
    $.ajax({
      url: '/app/videos/subjects',
      type: 'POST',
      dataType: 'text',
      data: {
          'duration': duration,
        'sub_course_id': sub_course_id
        
      },
      success: function(result) {

        $('#subject_id').html(result);
              $("#subject_id").select2({
      searchable: true,
      dropdownParent: $('#lg-modal-content')
    });

      }
    })
  }
  function getDuration(subCourseId) {
    $.ajax({
      url: '/app/subjects/semester?id='+subCourseId,
      type: 'get',
      success: function (data) {
        $("#duration").html(data);
        $("#duration").select2({
          searchable: true,
          dropdownParent: $('#lg-modal-content')
        });
      }
    })
  }
    function getChapter(subject_id){
    var sub_course_id = $("#course_id").val();
    var duration = $("#duration").val();

    $.ajax({
      url: '/app/videos/get-chapter',
      type: 'POST',
      dataType: 'text',
      data: {
        type:'chapter',
        duration: duration,
        sub_course_id: sub_course_id,
        subject_id:subject_id,
      },
      success: function (result) {
        $('#unit_id').html(result);
        $("#unit_id").select2({
          searchable: true,
          dropdownParent: $('#lg-modal-content')
        });
      }
    })
  }
  function getModule(chapter_id){
    $.ajax({
      url: '/app/videos/get-chapter',
      type: 'POST',
      dataType: 'text',
      data: {
        type:'unit',
        chapter_id:chapter_id,
      },
      success: function (result) {
        $('#module_id').html(result);
        $("#module_id").select2({
          searchable: true,
          dropdownParent: $('#lg-modal-content')
        });
      }
    })
  }
  $(function() {
    $('#form-add-e-book').validate({
      rules: {
        course_id: {
          required: true
        },
        subject_id: {
          required: true
        },
        file: {
          required: true
        },
      },
      highlight: function(element) {
        //$(element).addClass('error');
        $(element).closest('.form-control').addClass('has-error');
      },
      unhighlight: function(element) {
        //$(element).removeClass('error');
        $(element).closest('.form-control').removeClass('has-error');
      }
    });
  })

  $("#form-add-e-book").on("submit", function(e) {
    if ($('#form-add-e-book').valid()) {
    $(':input[type="submit"]').prop('disabled', true);
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
        success: function(data) {
          if (data.status == 200) {
            notification('success', data.message);
            $('.modal').modal('hide');
            $('#e_books-table').DataTable().ajax.reload(null, false);
          } else {
            notification('danger', data.message);
          }


        },
        error: function(data) {
          notification('danger', 'Server is not responding. Please try again later');
        }
      });
    } else {
      //notification('danger', 'Invalid form information.');
    }
  });
</script>