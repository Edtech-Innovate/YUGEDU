<div class="card card-default m-b-0">
  <div class="card-header " role="tab" id="headingReasons">
    <div class="card-title">
      <a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseReasons" aria-expanded="false" aria-controls="collapseReasons">
          Reasons
        </a>
    </div>
  </div>
  <div id="collapseReasons" class="collapse" role="tabcard" aria-labelledby="headingReasons">
    <div class="card-body">

      <div class="row p-b-20">
        <div class="col-lg-12 text-end">
          <button type="button" class="btn border-0 shadow-none"  data-toggle="tooltip" data-placement="top" title="Add Reason" onclick="addComponents('reasons', 'md', '')"><i class="ti ti-copy-plus add_btn_form" style="font-size:24px !important;"></i></button>
        </div>
      </div>

      <div class="row p-b-20">
        <div class="col-lg-12">
          <div class=" table-bordered px-3 py-2">
            <table class="table table-hover nowrap table-responsive" id="tableReasons">
              <thead>
                <tr>
                  <th >Name</th>
                  <th class="text-center">Stage</th>
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
