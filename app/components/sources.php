<div class="card card-default m-b-0">
  <div class="card-header " role="tab" id="headingSources">
    <div class="card-title">
      <a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseSources" aria-expanded="false" aria-controls="collapseSources">
          Channels
        </a>
    </div>
  </div>
  <div id="collapseSources" class="collapse" role="tabcard" aria-labelledby="headingSources">
    <div class="card-body">

      <div class="row p-b-20">
        <div class="col-lg-12 text-end">
          <button type="button" class="btn border-0 shadow-none" onclick="addComponents('sources', 'md', '')"><i class="ti ti-copy-plus add_btn_form" style="font-size:24px !important;"></i></button>
        </div>
      </div>

      <div class="row p-b-20">
        <div class="col-lg-12">
          <div class="table-bordered px-3 py-2">
            <table class="table table-hover nowrap table-responsive" style="margin-top:0px !important;" id="tableSources">
              <thead>
                <tr>
                  <th >Name</th>
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
