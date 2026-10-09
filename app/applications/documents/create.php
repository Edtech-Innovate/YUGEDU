<?php
$search = "";
if (isset($_GET['search'])) {
  $search = "?searchValue=" . $_GET['search'];
}
?>

<!-- Modal -->
<div class="modal-header clearfix text-left mb-4">
  <h5 class="font-weight-bold">Export Documents</h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
</div>
<form role="form" action="/app/applications/documents/export<?= $search ?>" method="POST" enctype="multipart/form-data">
  <div class="modal-body">
    <div class="row">
      <div class="col-md-12">
        <?php
        $documents = array('Photo', 'Student Signature', 'Parent Signature', 'Aadhar', 'Affidavit', 'Migration', 'Other Certificate', 'High School', 'Intermediate', 'UG', 'PG', 'Other');
        foreach ($documents as $document) { ?>
          <div class="row">
            <div class="col-md-12 form-check complete">
              <input type="checkbox" id="document_<?= str_replace(" ", "_", $document) ?>" name="download[]" value="<?= $document ?>">
              <label for="document_<?= str_replace(" ", "_", $document) ?>" class="font-weight-bold">
                <?= $document ?>
              </label>
            </div>
          </div>
        <?php }
        ?>
      </div>
    </div>
  </div>
  <div class="modal-footer d-flex justify-content-between">
    <div class="m-t-10 sm-m-t-10">
      <button aria-label="" type="submit" name="pdf" class="btn btn-primary custom_primary from-left">
        <i class="fa fa-file-photo-o mr-1"></i>
        Export as PDF
      </button>
    </div>
    <div class="m-t-10 sm-m-t-10">
      <button aria-label="" type="submit" name="zip" class="btn btn-primary custom_primary from-left">
        <i class="fa fa-file-photo-o mr-1"></i>
        Export as Image
      </button>
    </div>
  </div>
</form>