<div class="card card-default m-b-0">
  <div class="card-header " role="tab" id="headingFeeStructures">
    <div class="card-title">
      <a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseFeeStructures" aria-expanded="false" aria-controls="collapseFeeStructures">
        Fee Structures
      </a>
    </div>
  </div>
  <div id="collapseFeeStructures" class="collapse" role="tabcard" aria-labelledby="headingFeeStructures">
    <div class="card-body">

      <div class="row p-b-20">
        <div class="col-lg-12 text-end">
          <button type="button" class="btn border-0 shadow-none" onclick="addComponents('fee-structures', 'md', <?=$university_id?>)"><i class="ti ti-copy-plus add_btn_form" style="font-size:24px"></i></button>
        </div>
      </div>

      <div class="row p-b-20">
        <div class="col-lg-12">
          <div class="table-bordered px-3 py-2">
            <table class="table table-hover nowrap table-responsive" id="tableFeeStructures">
              <thead>
                <tr>
                  <th >Name</th>
                  <th data-orderable="false">Sharing</th>
                  <th data-orderable="false">Constant</th>
                  <th data-orderable="false">Applicable on</th>
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
  var table = $('#tableFeeStructures');
  var settings = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
      'url':'/app/components/fee-structures/server',
      type: 'POST',
      "data":function(data) {
        data.university_id = '<?=$university_id?>';
      },
    },
    'columns': [  
      { data: "Name",width:"900px",},
      { data: "Sharing",width:"400px",},
      { data: "Is_Conctant",width:"400px",},
      { data: "Applicable",width:"400px",},
      { data: "Status",width:"400px",
        "render": function(data, type, row){
          var active = data==1 ? 'Active' : 'Inactive';
          var checked = data==1 ? 'checked' : '';
          return '<div class="form-check form-check-inline switch switch-lg success">\
            <input onclick="changeComponentStatus(\'Fee-Structures\', \'FeeStructures\', \''+row.ID+'\');" type="checkbox" '+checked+' id="fee-structure-switch-'+row.ID+'">\
            <label for="fee-structure-switch-'+row.ID+'">'+active+'</label>\
          </div>';
        }
      },
      { data: "ID",width:"200px",
        "render": function(data, type, row){
          return '<div class="text-center">\
            <i class="ti ti-edit-circle add_btn_form h5 cursor-pointer" onclick="editComponents(\'fee-structures\', \''+data+'\', \'md\');"></i>\
            <i class="ti ti-trash-x cursor-pointer add_btn_form h5" onclick="destroyComponents(\'fee-structures\', \'FeeStructures\', \''+data+'\');"></i>\
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
