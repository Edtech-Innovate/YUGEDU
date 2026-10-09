<!-- Modal -->
<style>
.show {
  display: block;
}
.hide {
  display: none;
}
</style>

<style>
  /* Select box height and content styling */
  .select2-selection--multiple {
    min-height: 40px; /* Optional: increase box height */
    white-space: normal;
  }

  /* Wrapping selected tags to multiple lines */
  .select2-selection__rendered {
    white-space: normal !important;
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
  }

  /* Optional: Style for tags */
  .select2-selection__choice {
    background-color: #e0e0e0;
    color: #333;
    padding: 4px 8px;
    border-radius: 4px;
  }
</style>

<div class="modal-header clearfix text-left">
  <button aria-label="" type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="pg-icon">close</i>
  </button>
  <h5 class="font-weight-bold text-black">Add <span class="semi-bold"></span>Notification's</h5>
</div>
<form role="form" id="form-add-notifications" action="/app/notifications/store" method="POST" enctype="multipart/form-data">
  <div class="modal-body">
    <div class = "row">
      <div class = "col-md-6">
        <div class = "form-group form-group-default required">
          <label>Send to</label>
          <select class="full-width" style="border: transparent;" id="send_to" name="send_to" onchange="checkSendTo(this.value)">
            <option value="">Choose</option>
            <option value="student">Student's</option>
            <option value="center">Center's</option>
            <option value="all">All</option>
          </select>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group form-group-default required">
          <label>Heading</label>
          <select class="full-width" style="border: transparent;" id="heading" name="heading">
          </select>
        </div>
      </div>
    </div>

    <div class="row hide" id="center_input">
      <div class="col-md-12">
        <div class="form-group form-group-default">
          <label>Center</label>
          <select class="full-width" style="border: transparent;" id="center" data-init-plugin="select2" name="center[]" multiple>
          </select>
        </div>
      </div>
    </div>

    <div class="hide" id="student_input">

      <div class="row">
        <div class="col-md-6">
          <div class="form-group form-group-default">
            <label>Scheme</label>
            <select class="full-width" style="border: transparent;" id="scheme" data-init-plugin="select2" name="scheme[]" multiple onchange="getFilterInputFieldOption(this.id)">
            </select>
          </div>
        </div>
        <div class = "col-md-6">
          <div class = "form-group form-group-default">
            <label>Admission Session</label>
            <select class="full-width" style="border: transparent;" id="admissionSession" data-init-plugin="select2" name="admissionSession[]" multiple onchange="getFilterInputFieldOption(this.id)">
            </select>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-12">
          <div class="form-group form-group-default">
            <label>Course</label>
            <select class="full-width" style="border: transparent;" id="course" data-init-plugin="select2" name="course[]" multiple onchange="getFilterInputFieldOption(this.id)">
            </select>
          </div>
        </div>
      </div>
      <div id="duration_box"></div>
      <div class="row">
        <div class="col-md-12">
          <div class="form-group form-group-default">
            <label>Student</label>
            <select class="full-width" style="border: transparent;" id="student" data-init-plugin="select2" name="student[]" multiple>
            </select>
          </div>
        </div>
      </div>

    </div>
    <div class="row">
        <div class="form-group form-group-default required">
          <label>Content</label>
          <textarea type="content" name="content" class="form-control" rows="50" cols="50" required>
          </textarea>
        </div>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="form-group form-group-default">
        <label>Attachment*</label>
        <input name="file" type="file" accept="application/pdf,image/*">
        </div>
      </div>
      <div class="col-md-6" id="logo-view"></div>
    </div>

  </div>
  <div class="modal-footer clearfix text-end">
    <div class="col-md-12  m-t-10 sm-m-t-10">
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

$(document).ready(function(){
  getFilterData();
});

function getFilterData() {
  var filter_data_field = ['heading'];
  $.ajax({
    url : "/app/notifications/notification-filter",
    type : "post",
    contentType: 'json',  // Set the content type to JSON 
    data: JSON.stringify(filter_data_field), 
    dataType: 'json', 
    success : function(data) {
      for (const key in data) {
        $("#"+key).html(data[key]);
      }
    }   
  })
}

function checkSendTo(add_input) {
  getInputFieldData(add_input);
  if (add_input == 'student' || add_input == 'center') {
    let remove_input = (add_input == 'student') ? 'center' : 'student';
    if($("#"+add_input+"_input").hasClass("hide")) {
      $("#"+add_input+"_input").removeClass("hide");
      $("#"+add_input+"_input").addClass("show");
    }  
    if ($("#"+remove_input+"_input").hasClass("show")) {
      $("#"+remove_input+"_input").removeClass("show");
      $("#"+remove_input+"_input").addClass("hide");
    } 
  }  
}

function getInputFieldData(input) { 
  let input_data_field = (input == 'student') ? ['scheme','admissionSession','course','student'] : ['center'];
  let selected_values = {};
  for (const value of input_data_field) {
    $("#"+value).select2({
      placeholder: 'Choose '+value+""
    });
    var selected = [...(document.getElementById(value).selectedOptions)].map(option => option.value);
    selected_values[value] = selected.toString();  
  }
  $.ajax({
    url : "/app/notifications/getInputFieldData",
    type : "POST",
    contentType: 'json', 
    data: JSON.stringify({input_data_field,selected_values}), 
    dataType: 'json', 
    success : function(data) {
      for (const key in data) {
        $("#"+key).html(data[key]);
      }
    }   
  });
}

function getFilterInputFieldOption(type) {
  let selected_values = {};
  let input_data_field = []; let depended_field = [];
  if(type == 'scheme') {
    input_data_field = ['admissionSession','course','student'];
    depended_field = ['scheme'];
  } else if (type == 'admissionSession') {
    input_data_field = ['course','student']; 
    depended_field = ['scheme','admissionSession'];  
  } else if (type == 'course') {
    checkForDeleteOrCreateDurationInputField();
    input_data_field = ['student']; 
    depended_field = ['scheme','admissionSession','course'];
  } 
  for (const field of depended_field) {
    var selected = [...(document.getElementById(field).selectedOptions)].map(option => option.value);
    selected_values[field] = selected.toString();
  }
  $.ajax({
    url : "/app/notifications/getInputFieldData",
    type : "POST",
    contentType: 'json', 
    data: JSON.stringify({input_data_field,selected_values}), 
    dataType: 'json', 
    success : function(data) {
      for (const key in data) {
        $("#"+key).html(data[key]);
      }
    }   
  });
}

function checkForDeleteOrCreateDurationInputField() {
  let courseSelectedId = [...document.getElementById('course').selectedOptions].map( option => option.value);
  let courseDurationId = [...document.getElementById('duration_box').children].map(child => child.id);
  courseDurationId = courseDurationId.map(function(part){
    return part.split("_").filter((value,index) => index === 2).join(" "); 
  });
  let createInput = courseSelectedId.filter( value => !courseDurationId.includes(value));
  if (createInput.length > 0) {
    return createDurationInputField(createInput[0]);
  }
  let removeInput = courseDurationId.filter( value => !courseSelectedId.includes(value));
  if(removeInput.length > 0) {
    document.getElementById("duration_center_"+removeInput[0]).remove();
  }
}

// for select 
function createDurationInputField(course_id) {
  $.ajax({
    url : "/app/notifications/getInputFieldTag",
    type : "POST", 
    data:  {course_id},
    success : function(data) {
      $("#duration_box").append(data);
      $("#duration_"+course_id).select2({
        placeholder: 'Select Duration'
      });
    }   
  });
}

function getDurationSelectedData(id) {
  const childIds = [...document.getElementById('duration_box').children].map(child => child.id);
  let selected_values = {};
  let input_data_field = ['student'];
  let depended_field = ['scheme','admissionSession','course'];
  for (const field of childIds) {
    var durationId = field.split("_").filter( part => part != 'center').join("_");
    var selected = [...(document.getElementById(durationId).selectedOptions)].map(option => option.value);
    selected_values[durationId] = selected.toString();
  }
  for (const field of depended_field) {
    var selected = [...(document.getElementById(field).selectedOptions)].map(option => option.value);
    selected_values[field] = selected.toString();
  }
  $.ajax({
    url : "/app/notifications/getInputFieldData",
    type : "POST",
    contentType: 'json', 
    data: JSON.stringify({input_data_field,selected_values}), 
    dataType: 'json', 
    success : function(data) {
      for (const key in data) {
        $("#"+key).html(data[key]);
      }
    }   
  });
}

jQuery.validator.addMethod('fileType',function(value,element){
  if(element.files.length === 0) {
    return false;
  }
  var file = element.files[0];
  var allowedTypes = ["application/pdf","image/jpeg","image/jpeg","image/png","image/gif"];
  return allowedTypes.includes(file.type);
},"Only PDF and image files are allowed.");

$(function(){
  $('#form-add-notifications').validate({
    rules: {
      content: {required:true},
      send_to: {required:true},
      heading: {required:true},
      date: {required:true},
      file : {required : true,fileType : true}
    },
    highlight: function (element) {
      $(element).addClass('error');
      $(element).closest('.form-control').addClass('has-error');
    },
    unhighlight: function (element) {
      $(element).removeClass('error');
      $(element).closest('.form-control').removeClass('has-error');
    }
  });
})

function checkStudentData() {
  let sendToValue = document.getElementById("send_to").value;
  if (sendToValue == 'student') {
    let studentOption = [...(document.getElementById("student").options)].filter( option => (option.value != ''));
    return (studentOption.length > 0) ? true : false;
  }
  return true;
}

$("#form-add-notifications").on("submit", function(e){
  e.preventDefault();
  if($('#form-add-notifications').valid()){
    //$(':input[type="submit"]').prop('disabled', true);
    if (checkStudentData()) {
      var formData = new FormData(this);
      $.ajax({
          url: this.action,
          type: 'post',
          data: formData,
          cache:false,
          contentType: false,
          processData: false,
          dataType: "json",
          success: function(data) {
            if(data.status==200){
              $('.modal').modal('hide');
              table.dataTable(settings);
              notification('success', data.message);
            }else{
              $(':input[type="submit"]').prop('disabled', false);
              notification('danger', data.message);
            }
          }
      });
    } else {
      Swal.fire({
        title: 'Sorry..Notification not submitted',
        text: "Student not present for this selected filter group",
        icon: 'error'
      });
    }
  }
});

</script>
