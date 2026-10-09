<div class="card card-default m-b-0">
  <div class="card-header " role="tab" id="headingSchemes">
    <div class="card-title">
      <a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseSchemes" aria-expanded="false" aria-controls="collapseSchemes">
          Schemes
        </a>
    </div>
  </div>
  <div id="collapseSchemes" class="collapse" role="tabcard" aria-labelledby="headingSchemes">
    <div class="card-body">

      <div class="row p-b-20">
        <div class="col-lg-12 text-end">
          <button type="button" class="btn border-0 shadow-none" onclick="addComponents('schemes', 'md', <?=$university_id?>)"><i class="ti ti-copy-plus add_btn_form" style="font-size:24px"></i></button>
        </div>
      </div>

      <div class="row p-b-20">
        <div class="col-lg-12">
          <div class="table-bordered px-3 py-2">
            <table class="table table-hover nowrap table-responsive" id="tableSchemes">
              <thead>
                <tr>
                  <th >Name</th>
                  <th >Fee Structures</th>
                  <th data-orderable="false">Status</th>
                  <th data-orderable="false" class="text-center">Action</th>
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
  var table = $('#tableSchemes');
  var settings = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
      'url':'/app/components/schemes/server',
      type: 'POST',
      "data":function(data) {
        data.university_id = '<?=$university_id?>';
      },
    },
    'columns': [  
      { data: "Name",width:"800px"},
      { data: "Fee_Structure",width:"400px"},
      { data: "Status",width:"400px",
        "render": function(data, type, row){
          var active = data==1 ? 'Active' : 'Inactive';
          var checked = data==1 ? 'checked' : '';
          return '<div class="form-check form-check-inline switch switch-lg success">\
            <input onclick="changeComponentStatus(\'Schemes\', \'Schemes\', \''+row.ID+'\');" type="checkbox" '+checked+' id="scheme-status-switch-'+row.ID+'">\
            <label for="scheme-status-switch-'+row.ID+'">'+active+'</label>\
          </div>';
        }
      },
      { data: "ID",width:"400px",
        "render": function(data, type, row){
          return '<div class="text-center">\
            <i class="ti ti-edit-circle add_btn_form h5 cursor-pointer" onclick="editComponents(\'schemes\', \''+data+'\', \'md\');"></i>\
            <i class="ti ti-trash-x cursor-pointer add_btn_form h5" onclick="destroyComponents(\'schemes\', \'Schemes\', \''+data+'\');"></i>\
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
