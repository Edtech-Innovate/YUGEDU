<?php
  if(isset($_GET['university_id']) && isset($_GET['id'])){
    require '../../includes/db-config.php';
    $id = intval($_GET['id']);
    $university_id = intval($_GET['university_id']);

    // Mode
    $available_modes = array();
    $modes = $conn->query("SELECT Name FROM Modes WHERE University_ID = $university_id");
    while($mode = $modes->fetch_assoc()){
      $available_modes[] = $mode['Name'];
    }
    $mode = implode('/', $available_modes);

    // Duration
    $duration = $conn->query("SELECT MAX(Min_Duration) as Duration FROM Sub_Courses WHERE University_ID = $university_id");
    $duration = mysqli_fetch_assoc($duration);
    $durations = $duration['Duration'];
    
    if(empty($university_id) || empty($mode) || empty($durations)){
      exit();
    }

    // Check Has Course Allotment
    $course_allotment = 0;
    $has_course_allotment = $conn->query("SELECT ID FROM Universities WHERE ID = $university_id AND Course_Allotment = 1");
    if($has_course_allotment->num_rows > 0){
      $course_allotment = 1;
    }

    // Reporting
    $reportingQuery = "";
    $allotedReportingFee = array();
    if(isset($_GET['reporting'])){
      $reporting = intval($_GET['reporting']);
      // Department to Show
      $reportingDepartments = array();
      $allotedDepartments = $conn->query("SELECT Department_ID FROM User_Departments WHERE User_ID = $reporting AND University_ID = $university_id");
      if($allotedDepartments->num_rows > 0){
        while($allotedDepartment = $allotedDepartments->fetch_assoc()){
          $reportingDepartments[] = $allotedDepartment['Department_ID'];
        }
        $reportingQuery = " AND ID IN (".implode(",", $reportingDepartments).")";
      }else{
        $reportingQuery = "";
      }

      // Reporting Fee
      $reportingFees = $conn->query("SELECT Fee, Admission_Session_ID, Scheme_ID FROM Fee_Variables WHERE Code = $reporting AND University_ID = $university_id");
      if($reportingFees->num_rows > 0){
        while($reportingFee = $reportingFees->fetch_assoc()){
          $allotedReportingFee[$reportingFee['Admission_Session_ID']][$reportingFee['Scheme_ID']] = !empty($reportingFee['Fee']) ? json_decode($reportingFee['Fee'], true) : array();
        }
      }
    }

    $allotedFee = [];
    $alloted_fees = $conn->query("SELECT Fee, Admission_Session_ID, Scheme_ID FROM Fee_Variables WHERE Code = $id AND University_ID = $university_id");
    if($alloted_fees->num_rows > 0){
      while($alloted_fee = $alloted_fees->fetch_assoc()){
        $allotedFee[$alloted_fee['Admission_Session_ID']][$alloted_fee['Scheme_ID']] = !empty($alloted_fee['Fee']) ? json_decode($alloted_fee['Fee'], true) : array();
      }
    }

    // Admission Sessions
    $allotedSchemes = array();
    $sessions = array();
    $admission_sessions = $conn->query("SELECT ID, Name, Scheme FROM Admission_Sessions WHERE University_ID = $university_id");
    while($admission_session = $admission_sessions->fetch_assoc()){
      $sessions[$admission_session['ID']] = $admission_session['Name'];
      $sessionSchemes = json_decode($admission_session['Scheme'], true);
      $allotedSchemes[$admission_session['ID']] = $sessionSchemes['schemes'];
    }

    // Schemes
    $schemesNames = array();
    $schemeFeeStructures = array();
    $schemes = $conn->query("SELECT ID, Name, Fee_Structure FROM Schemes WHERE University_ID = $university_id");
    while($scheme = $schemes->fetch_assoc()){
      $schemesNames[$scheme['ID']] = $scheme['Name'];
      $schemeFeeStructures[$scheme['ID']] = json_decode($scheme['Fee_Structure'], true);
    }

    // Sharing Fee
    $structures = array();
    $variableStructureIds = array();
    $sharingFeeStructures = array();
    $fee_structures = $conn->query("SELECT ID, Name, Sharing FROM Fee_Structures WHERE (Sharing = 1 OR Is_Constant = 0) AND University_ID = $university_id");
    while($fee_structure = $fee_structures->fetch_assoc()){
      $structures[$fee_structure['ID']] = $fee_structure['Name'];
      $variableStructureIds[] = $fee_structure['ID'];
      if($fee_structure['Sharing']){
        $sharingFeeStructures[] = $fee_structure['ID'];
      }
    }

    if($course_allotment){ 
      $allotedDepartments = [];
      $alloted_departments = $conn->query("SELECT Department_ID FROM User_Departments WHERE `User_ID` = $id AND University_ID = $university_id");
      while($alloted_department = $alloted_departments->fetch_assoc()){
        $allotedDepartments[] = $alloted_department['Department_ID'];
      }
      ?>
      
      <!-- Department Input Section with Premium Left-Aligned Toggle Button -->
      <div class="row mb-3">
        <div class="col-md-12">
          <div class="form-group form-group-default required border rounded p-3 bg-white shadow-custom-sm">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
              <label class="text-uppercase tracking-wider font-weight-bold text-muted small m-0 mr-1">Department</label>
              <button type="button" class="btn btn-outline-primary btn-action-toggle dropdown-select-all d-flex align-items-center" data-target="#department">
                <i class="pg-icon mr-1 small font-weight-bold btn-toggle-icon">add</i>
                <span class="btn-toggle-text">Select All</span>
              </button>
            </div>
            <select class="full-width select2-modern" id="department" name="department[]" onchange="getCourses()" multiple>
              <?php
                $departments = $conn->query("SELECT ID, Name FROM Departments WHERE University_ID = $university_id $reportingQuery ORDER BY Name ASC");
                while ($department = $departments->fetch_assoc()){ ?>
                  <option value="<?=$department['ID']?>" <?php echo in_array($department['ID'], $allotedDepartments) ? 'selected' : '' ?>><?=$department['Name']?></option>
              <?php } ?>
            </select>
          </div>
        </div>
      </div>

      <!-- Courses Input Section with Premium Left-Aligned Toggle Button -->
      <div class="row mb-4">
        <div class="col-md-12">
          <div class="form-group form-group-default required border rounded p-3 bg-white shadow-custom-sm">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
              <label class="text-uppercase tracking-wider font-weight-bold text-muted small m-0 mr-1">Courses</label>
              <button type="button" class="btn btn-outline-primary btn-action-toggle dropdown-select-all d-flex align-items-center" data-target="#course">
                <i class="pg-icon mr-1 small font-weight-bold btn-toggle-icon">add</i>
                <span class="btn-toggle-text">Select All</span>
              </button>
            </div>
            <select class="full-width select2-modern" id="course" name="courses[]" multiple>
              <!-- Populated dynamically via AJAX -->
            </select>
          </div>
        </div>
      </div>
      
      <script>
        $(function(){
          if ($.fn.select2) {
            $("#department").select2({
              placeholder: 'Choose Departments',
              allowClear: true,
              width: '100%'
            });
    
            $("#course").select2({
              placeholder: 'Choose Courses',
              allowClear: true,
              width: '100%'
            });
          }

          $('.dropdown-select-all').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var targetSelect = $($btn.data('target'));
            var allOptions = targetSelect.find('option').map(function() { return $(this).val(); }).get();
            
            if (targetSelect.val() && targetSelect.val().length === allOptions.length) {
              targetSelect.val(null).trigger('change');
              $btn.removeClass('btn-active-clear').addClass('btn-outline-primary');
              $btn.find('.btn-toggle-text').text('Select All');
              $btn.find('.btn-toggle-icon').text('add');
            } else {
              targetSelect.val(allOptions).trigger('change');
              $btn.removeClass('btn-outline-primary').addClass('btn-active-clear');
              $btn.find('.btn-toggle-text').text('Clear All');
              $btn.find('.btn-toggle-icon').text('close');
            }
          });
        });

        function getCourses(){
          var department = $("#department").val();
          var reporting = '<?php echo isset($_GET['reporting']) ? '&reporting='.$reporting : '' ?>';
          $.ajax({
            url: '/app/university-allotment/department-courses?department='+department+'&university=<?=$university_id?>&user_id=<?=$id?>'+reporting,
            type: 'GET',
            success: function(data){
              $("#course").html(data);
              var $courseBtn = $('[data-target="#course"]');
              $courseBtn.removeClass('btn-active-clear').addClass('btn-outline-primary');
              $courseBtn.find('.btn-toggle-text').text('Select All');
              $courseBtn.find('.btn-toggle-icon').text('add');
            }
          })
        }
  
        getCourses();
      </script>
    <?php } ?>

  <!-- Session Matrix Canvas -->
  <div class="row m-t-10 p-b-2">
    <div class="col-md-12">
      <div class="table-responsive border rounded bg-white shadow-custom-sm">
        <table class="table table-hover align-middle m-0 custom-session-table">
          <thead>
            <tr class="bg-light">
              <th class="p-3 text-dark font-weight-bold small text-uppercase tracking-wider" style="background: #f8fafc; width: 35%;">Admission Sessions</th>
              <th class="p-3 text-dark font-weight-bold small text-uppercase tracking-wider" style="background: #f8fafc; width: 65%;">Fee Values Setup</th>
            </tr>
          </thead>
          <tbody>
            <?php
              foreach($sessions as $session_id=>$session){
                foreach($allotedSchemes[$session_id] as $allotedScheme){
                  $feeHeadIds = array_intersect($variableStructureIds, $schemeFeeStructures[$allotedScheme]);
                  ?>
                  <tr>
                    <td class="p-3 align-middle font-weight-bold text-dark small">
                      <?=$session?> <span class="text-primary font-weight-normal">(<?=$schemesNames[$allotedScheme]?>)</span>
                    </td>
                    <td class="p-3 align-middle">
                      <div class="d-flex justify-content-start gap-2">
                        <?php
                          $width = round(100/count($feeHeadIds));
                          foreach($feeHeadIds as $feeHeadId){ 
                            $placeholder = in_array($feeHeadId, $sharingFeeStructures) ? $structures[$feeHeadId]." Share (%)" : $structures[$feeHeadId];
                            $max = !empty($allotedReportingFee) && in_array($session_id, array_keys($allotedReportingFee)) && in_array($allotedScheme, array_keys($allotedReportingFee[$session_id])) && in_array($feeHeadId, array_keys($allotedReportingFee[$session_id][$allotedScheme])) ? 'max="'.$allotedReportingFee[$session_id][$allotedScheme][$feeHeadId].'"' : (in_array($feeHeadId, $sharingFeeStructures) ? 'max="100"' : '');
                            $value = !empty($allotedFee) && in_array($session_id, array_keys($allotedFee)) && in_array($allotedScheme, array_keys($allotedFee[$session_id])) && in_array($feeHeadId, array_keys($allotedFee[$session_id][$allotedScheme])) ? $allotedFee[$session_id][$allotedScheme][$feeHeadId] : "";
                            ?>
                            <div style="width: <?=$width?>%; min-width: 110px;">
                              <input type="number" class="form-control text-center session-fee-input" min="0" <?=$max?> value="<?=$value?>" placeholder="<?=$placeholder?>" name="fee[<?=$session_id?>][<?=$allotedScheme?>][<?=$feeHeadId?>]" required>
                            </div>
                          <?php }
                        ?>
                      </div>
                    </td>
                  </tr>
                <?php }
              }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- UI System Style RulesOverrides -->
  <style>
    .shadow-custom-sm { box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.06); }
    .bg-light { background-color: #f8fafc !important; }
    .gap-2 { gap: 0.5rem; }
    
    /* Responsive Premium Button Micro-Styles */
    .btn-action-toggle {
      font-size: 0.72rem !important;
      font-weight: 700 !important;
      letter-spacing: 0.03em;
      text-transform: uppercase;
      padding: 4px 10px !important;
      border-radius: 20px !important;
      line-height: 1.2 !important;
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
      background: transparent;
      height: auto !important;
    }
    
    .btn-outline-primary {
      border: 1.5px solid #e2e8f0 !important;
      color: #4f46e5 !important;
    }
    .btn-outline-primary:hover {
      background-color: #f8fafc !important;
      border-color: #4f46e5 !important;
    }

    .btn-active-clear {
      border: 1.5px solid #fee2e2 !important;
      background-color: #fef2f2 !important;
      color: #ef4444 !important;
    }
    .btn-active-clear:hover {
      background-color: #fee2e2 !important;
      border-color: #ef4444 !important;
    }

    .btn-toggle-icon {
      font-size: 0.9rem !important;
      vertical-align: middle;
    }

    .select2-container--default .select2-selection--multiple {
      border: 1px solid #cbd5e1 !important;
      border-radius: 6px !important;
      padding: 4px 6px !important;
    }

    .custom-session-table tbody tr {
      transition: background-color 0.15s ease;
    }
    .custom-session-table tbody tr:hover {
      background-color: #f8fafc !important;
    }

    .session-fee-input {
      font-size: 0.825rem !important;
      font-weight: 500;
      padding: 0.4rem 0.6rem !important;
      border-radius: 5px !important;
      border: 1px solid #cbd5e1 !important;
      height: auto !important;
    }
    .session-fee-input:focus {
      border-color: #4f46e5 !important;
      box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.15) !important;
    }
  </style>
<?php } ?>