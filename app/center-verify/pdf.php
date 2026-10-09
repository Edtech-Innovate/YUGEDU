<?php
use setasign\Fpdi\Fpdi;

require '../../includes/db-config.php';
require_once('../../extras/vendor/setasign/fpdf/fpdf.php');
require_once('../../extras/vendor/setasign/fpdi/src/autoload.php');

if (!isset($_GET['id'])) {
    die('Service ID missing');
}

$service_id = (int)$_GET['id'];
$q = mysqli_query($conn, "SELECT * FROM center_verfiy1 WHERE id = $service_id");
if (mysqli_num_rows($q) == 0) {
    die('Record not found');
}

$data = mysqli_fetch_assoc($q);

// Initialize PDF
$pdf = new FPDI();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Service Partner Form',0,1,'C');
$pdf->Ln(5);

// Function to safely output text in PDF
function pdfCell($pdf, $label, $value){
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(50,8,$label,1);
    $pdf->SetFont('Arial','',12);
    $pdf->MultiCell(0,8,$value,1);
}

// ================= A. Service Partner =================
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'A. Service Partner Details',0,1,'L');

pdfCell($pdf,'Institution Name',$data['institution_name']);
pdfCell($pdf,'Director Name',$data['dir_name']);
pdfCell($pdf,'Pin Code',$data['dir_pincode']);
pdfCell($pdf,'State',$data['dir_state']);
pdfCell($pdf,'District',$data['dir_district']);
pdfCell($pdf,'Mobile',$data['dir_mob_number']);
pdfCell($pdf,'Email',$data['dir_email']);
pdfCell($pdf,'Address',$data['dir_address']);

// ================= B. Centre Manager =================
$pdf->Ln(5);
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'B. Centre Manager Details',0,1,'L');

pdfCell($pdf,'Name',$data['cmgr_name']);
pdfCell($pdf,'Mobile',$data['cmgr_mob_number']);
pdfCell($pdf,'Email',$data['cmgr_email']);
pdfCell($pdf,'Pin Code',$data['cmgr_pincode']);
pdfCell($pdf,'State',$data['cmgr_state']);
pdfCell($pdf,'District',$data['cmgr_district']);
pdfCell($pdf,'Address',$data['cmgr_address']);

// ================= C. Infrastructure =================
$pdf->Ln(5);
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'C. Infrastructure Details',0,1,'L');

pdfCell($pdf,'Training Floors',$data['infra_dtls_training']);
pdfCell($pdf,'Built-up Area',$data['infra_dtls_builtup']);
pdfCell($pdf,'Compound Area',$data['infra_dtls_compound']);
pdfCell($pdf,'Ownership Type',$data['infra_dtls_types_ownership']);
pdfCell($pdf,'Lease / Rent Tenure',$data['infra_dtls_leased_rented']);

// ================= Uploaded Files =================
$pdf->Ln(5);
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'Uploaded Files',0,1,'L');

$files = [
    'Approach Road'=>'approach_road',
    'Front View'=>'front_view',
    'Back View'=>'back_view',
    'Reception Area'=>'reception_area',
    'Domain Lab'=>'domain_lab',
    'Classroom'=>'classroom',
    'Washrooms'=>'washrooms',
    'IT Lab'=>'it_lab',
    'Signature'=>'signature',
    'Rubber Stamp'=>'rubber_stamp'
];

foreach($files as $label => $key){
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(50,8,$label,1);

    if($data[$key] && file_exists(__DIR__ . "/uploads/" . $data[$key])){
        // Create a clickable "View" link
        $fileUrl = "https://wilpvocmdu.edtechinnovate.in/app/center-verify/uploads/".$data[$key];
        $pdf->SetFont('Arial','U',12);
        $pdf->SetTextColor(0,0,255);
        $pdf->Cell(0,8,'View',1,1,'C', false, $fileUrl); // clickable button
        $pdf->SetTextColor(0,0,0); // reset color
    } else {
        $pdf->SetFont('Arial','',12);
        $pdf->Cell(0,8,'N/A',1,1);
    }
}

// ================= Declaration =================
$pdf->Ln(5);
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'Declaration',0,1,'L');

pdfCell($pdf,'Place',$data['place']);
pdfCell($pdf,'Date',$data['declaration_date']);

// Output PDF inline in browser
$pdf->Output('I','Service_Partner_Form_'.$service_id.'.pdf');
