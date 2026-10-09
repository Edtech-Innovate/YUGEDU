<div class="card card-default m-b-0">
  <div class="card-header " role="tab" id="headingSubSources">
    <div class="card-title">
      <a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseSubSources" aria-expanded="false" aria-controls="collapseSubSources">
          Sub-Channels
        </a>
    </div>
  </div>
  <div id="collapseSubSources" class="collapse" role="tabcard" aria-labelledby="headingSubSources">
    <div class="card-body">

      <div class="row p-b-20">
        <div class="col-lg-12 text-end">
          <button type="button" class="btn border-0 shadow-none" onclick="addComponents('sub-sources', 'md', '')"><i class="ti ti-copy-plus add_btn_form" style="font-size:24px !important;"></i></button>
        </div>
      </div>

      <div class="row p-b-20">
        <div class="col-lg-12">
          <div class="table-bordered px-3 py-2">
            <table class="table table-hover nowrap table-responsive" id="tableSubSources">
              <thead>
                <tr>
                  <th >Name</th>
                  <th>Channel</th>
                  <th data-orderable="false">Status</th>
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
