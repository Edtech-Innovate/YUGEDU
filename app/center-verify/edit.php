<?php
require '../../includes/db-config.php';

if (!isset($_GET['id'])) {
    die('Service ID missing');
}

$service_id = (int)$_GET['id'];

$q = mysqli_query($conn, "SELECT * FROM center_verfiy1 WHERE id = $service_id");
if (mysqli_num_rows($q) == 0) {
    die('Record not found');
}

$data = mysqli_fetch_assoc($q);

// Returns clickable link for modal
function fileView($file, $label) {
    if (!$file) return 'N/A';
    $fileUrl = "/app/center-verify/uploads/$file"; // Relative URL
    return "<button type='button' class='btn btn-sm btn-outline-primary' onclick=\"showModal('$fileUrl', '$label')\">View</button>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Service Partner</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
/* Read-only inputs */
input, textarea {
    background-color: #f8f9fa !important;
    pointer-events: none;
}

/* Labels style */
label {
    font-size: 0.85rem;
    font-weight: 500;
    color: #6c757d;
}

/* Print-friendly */
@media print {
  button, a {
    display: none !important;
  }
}

.f16 {
    font-size: 1rem;
}
</style>
</head>
<body>
<section class="container my-5">

  <div class="card shadow-sm border-0">
    <div class="card-body p-4">

      <h3 class="text-center mb-4 fw-bold">Service Partner Details (Read Only)</h3>

      <!-- ================= A. SERVICE PARTNER ================= -->
      <div class="mb-4">
        <h5 class="border-bottom pb-2 text-primary">A. Service Partner</h5>
        <div class="row g-3 mt-2">
          <div class="col-md-6">
            <label>Institution Name</label>
            <input class="form-control" value="<?= htmlspecialchars($data['institution_name']) ?>">
          </div>
          <div class="col-md-6">
            <label>Director Name</label>
            <input class="form-control" value="<?= htmlspecialchars($data['dir_name']) ?>">
          </div>
          <div class="col-md-4">
            <label>Pin Code</label>
            <input class="form-control" value="<?= htmlspecialchars($data['dir_pincode']) ?>">
          </div>
          <div class="col-md-4">
            <label>State</label>
            <input class="form-control" value="<?= htmlspecialchars($data['dir_state']) ?>">
          </div>
          <div class="col-md-4">
            <label>District</label>
            <input class="form-control" value="<?= htmlspecialchars($data['dir_district']) ?>">
          </div>
          <div class="col-md-6">
            <label>Mobile</label>
            <input class="form-control" value="<?= htmlspecialchars($data['dir_mob_number']) ?>">
          </div>
          <div class="col-md-6">
            <label>Email</label>
            <input class="form-control" value="<?= htmlspecialchars($data['dir_email']) ?>">
          </div>
          <div class="col-12">
            <label>Address</label>
            <textarea class="form-control"><?= htmlspecialchars($data['dir_address']) ?></textarea>
          </div>
        </div>
      </div>

      <!-- ================= FILES ================= -->
      <div class="mb-4">
        <h5 class="border-bottom pb-2 text-primary">Uploaded Files</h5>
        <div class="row g-2 mt-2">
          <?php
          $files = [
            'Approach Road' => 'approach_road',
            'Front View' => 'front_view',
            'Back View' => 'back_view',
            'Reception Area' => 'reception_area',
            'Domain Lab' => 'domain_lab',
            'Classroom' => 'classroom',
            'Washrooms' => 'washrooms',
            'IT Lab' => 'it_lab',
            'Signature' => 'signature',
            'Rubber Stamp' => 'rubber_stamp'
          ];

          foreach ($files as $label => $key) {
            echo '<div class="col-md-4 mb-2">
                    <div class="d-flex justify-content-between align-items-center border rounded p-2">
                      <span>'.$label.'</span>
                      '.fileView($data[$key], $label).'
                    </div>
                  </div>';
          }
          ?>
        </div>
      </div>

      <!-- ACTIONS -->
      <!--<div class="text-center mt-4">-->
      <!--  <button onclick="window.print()" type="button" class="btn btn-primary px-4">Print / Download</button>-->
      <!--  <a href="javascript:history.back()" class="btn btn-outline-secondary px-4">Back</a>-->
      <!--</div>-->
      <div class="text-center mt-4">
        <!--<button onclick="window.print()" type="button" class="btn btn-primary px-4">Print / Download</button>-->
         <a href="/app/center-verify/pdf.php?id=<?= $service_id ?>" class="btn btn-primary px-4" target="_blank">
        Print / Download
    </a>
        <a href="https://wilpvocmdu.edtechinnovate.in/users/appliedcenter" class="btn btn-outline-secondary px-4">Back</a>
      </div>

    </div>
  </div>

</section>

<!-- Modal -->
<div class="modal fade" id="fileModal" tabindex="-1" aria-labelledby="fileModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="fileModalLabel"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <img src="" id="modalImage" class="img-fluid" alt="">
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showModal(fileUrl, label) {
    const modalLabel = document.getElementById('fileModalLabel');
    const modalImage = document.getElementById('modalImage');
    modalLabel.textContent = label;
    modalImage.src = fileUrl;
    var modal = new bootstrap.Modal(document.getElementById('fileModal'));
    modal.show();
}
</script>
</body>
</html>
