<div class="card card-default m-b-0">
  <div class="card-header " role="tab" id="headingLateFees">
    <div class="card-title">
      <a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseLateFees" aria-expanded="false" aria-controls="collapseLateFees">
          Late Fee
        </a>
    </div>
  </div>
  <div id="collapseLateFees" class="collapse" role="tabcard" aria-labelledby="headingLateFees">
    <div class="card-body">

      <div class="row p-b-20">
        <div class="col-lg-12 text-end">
          <button type="button" class="btn border-0 shadow-none" onclick="addComponents('late-fees', 'md', <?=$university_id?>)"><i class="ti ti-copy-plus add_btn_form" style="font-size:24px"></i></button>
        </div>
      </div>

      <div class="row p-b-20">
        <div class="col-lg-12">
          <div class="table-bordered px-3 py-2">
            <table class="table table-hover nowrap table-responsive" style="margin-top:0px !important;" id="tableLateFees">
              <thead>
                <tr>
                  <th>Fee</th>
                  <th>Start Date</th>
                  <th>End Date</th>
                  <th data-orderable="false">Show Popup</th>
                  <th data-orderable="false">Status</th>
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
  var table = $('#tableLateFees');
  var settings = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
      'url':'/app/components/late-fees/server',
      type: 'POST',
      "data":function(data) {
        data.university_id = '<?=$university_id?>';
      },
    },
    'columns': [  
      { data: "Fee",width:"1200px"},
      { data: "Start_Date",width:"500px"},
      { data: "End_Date",width:"500px"},
      { data: "Show_Popup",width:"500px",
        "render": function(data, type, row){
          var active = data==1 ? 'Active' : 'Inactive';
          var checked = data==1 ? 'checked' : '';
          return '<div class="form-check form-check-inline switch switch-lg success">\
            <input onclick="changePopupStatus( \''+row.ID+'\');" type="checkbox" '+checked+' id="popup-status-switch-'+row.ID+'">\
            <label for="popup-status-switch-'+row.ID+'">'+active+'</label>\
          </div>';
        }
      },
      { data: "Status",width:"500px",
        "render": function(data, type, row){
          var active = data==1 ? 'Active' : 'Inactive';
          var checked = data==1 ? 'checked' : '';
          return '<div class="form-check form-check-inline switch switch-lg success">\
            <input onclick="changeComponentStatus(\'Late_Fees\', \'LateFees\', \''+row.ID+'\');" type="checkbox" '+checked+' id="late-fee-status-switch-'+row.ID+'">\
            <label for="late-fee-status-switch-'+row.ID+'">'+active+'</label>\
          </div>';
        }
      }
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

<script>
  function changePopupStatus(id){
    $.ajax({
      url:'/app/components/late-fees/popup',
      type:'POST',
      data:{id},
      dataType: 'json',
      success: function(data) {
        if(data.status){
          notification('success', data.message);
          $('#tableLateFees').DataTable().ajax.reload(null, false);
        }else{
          notification('danger', data.message);
          $('#tableLateFees').DataTable().ajax.reload(null, false);
        }
      }
    })
  }
</script>
