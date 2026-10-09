<div class="card card-default m-b-0">
  <div class="card-header " role="tab" id="headingModes">
    <div class="card-title">
      <a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapsePaySlips"
        aria-expanded="false" aria-controls="collapsePaySlips">
        Pay Slip Serial No.
      </a>
    </div>
  </div>
  <div id="collapsePaySlips" class="collapse" role="tabcard" aria-labelledby="headingModes">
    <div class="card-body">

      <div class="row p-b-20">
        <div class="col-lg-12 text-end">
          <?php $check = $conn->query("SELECT id FROM pay_slip_suffix where university_id =$university_id ");
          if ($check->num_rows == 0) {
            ?>
            <button type="button" class="btn border-0"
              onclick="addComponents('pay-slips', 'md', <?= $university_id ?>)"> <i class="ti ti-copy-plus add_btn_form "></i></button>
          <?php } ?>
        </div>
      </div>

      <div class="row p-b-20">
        <div class="col-lg-12">
          <div class="">
            <table class="table table-hover nowrap table-responsive table-responsive" id="tablePaySlips">
              <thead>
                <tr>
                  <th width="30%">Suffix</th>
                  <th width="30%">Character</th>
                   <th width="30%">Serial No. Sample</th>
                  <th data-orderable="false" class="text-end">Action</th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


<script type="text/javascript">
  var table = $('#tablePaySlips');
  var settings = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
      'url': '/app/components/pay-slips/server',
      type: 'POST',
      "data": function (data) {
        data.university_id = '<?= $university_id ?>';
      },
    },
    'columns': [
      { data: "suffix", width: "700px", },
      { data: "character", width: "700px", },
      { data: "sample", width: "700px", },
      // {
      //   data: "Status",
      //   width: "700px",
      //   "render": function (data, type, row) {
      //     var active = data == 1 ? 'Active' : 'Inactive';
      //     var checked = data == 1 ? 'checked' : '';
      //     var badgeClass = data == 1 ? 'badge-success' : 'badge-danger';
      //     return '<div class="form-check form-check-inline switch switch-lg success">\
      //             <input onclick="changeComponentStatus(\'Modes\', \'Modes\', \''+ row.ID + '\');" type="checkbox" ' + checked + ' id="mode-status-switch-' + row.ID + '">\
      //            <label for="mode-status-switch-'+ row.ID + '">\
      //           <span class="badge '+ badgeClass + ' p-2">' + active + '</span>\
      //          </label>\
      //         </div> ';
      //   }
      // },
      {
        data: "ID", width: "200px",
        "render": function (data, type, row) {
            //  <i class="uil uil-trash icon-xs cursor-pointer custom_edit_button" onclick="destroyComponents(\'modes\', \'Modes\', \''+ data + '\');"></i>\
          return '<div class="text-end">\
            <i class="uil uil-edit icon-xs cursor-pointer add_btn_form" onclick="editComponents(\'pay-slips\', \''+ data + '\', \'md\');"></i>\
          </div>'
        }
      },
    ],
    "sDom": "<t><'row'<p i>>",
    "destroy": true,
    "scrollCollapse": true,
    "oLanguage": {
      "sLengthMenu": "_MENU_ ",
      "sInfo": "Showing <b>_START_ to _END_</b> of _TOTAL_ entries"
    },
    "aaSorting": [],
    "iDisplayLength": 5
  };

  table.dataTable(settings);
</script>