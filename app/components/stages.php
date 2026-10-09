<div class="card card-default m-b-0">
  <div class="card-header " role="tab" id="headingStages">
    <div class="card-title">
      <a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseStages" aria-expanded="false" aria-controls="collapseStages">
          Stages
        </a>
    </div>
  </div>
  <div id="collapseStages" class="collapse" role="tabcard" aria-labelledby="headingStages">
    <div class="card-body">

      <div class="row p-b-20">
        <div class="col-lg-12 text-end">
          <button type="button" class="btn border-0 shadow-none " data-toggle="tooltip" data-placement="top" title="Add Stages" onclick="addComponents('stages', 'md', '')"><i class="ti ti-copy-plus add_btn_form" style="font-size:24px;"></i></button>
        </div>
      </div>

      <div class="row p-b-20">
        <div class="col-lg-12">
          <div class="table-bordered">
            <table class="table table-hover nowrap table-responsive" style="margin-top:0px !important;width:100% !important;" id="tableStages">
              <thead>
                <tr>
                  <th >Name</th>
                  <th data-orderable="false">Initial Stage</th>
                  <th data-orderable="false" class="text-center">Final Stage</th>
                  <th data-orderable="false" class="text-center">Re-Enquired Stage</th>
                  <th data-orderable="false" class="text-center">Status</th>
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
