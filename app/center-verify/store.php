<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400'); // cache preflight
// header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// print_r($_REQUEST);die;

// print_r($_FILES);die;
// Database connection
    // ==============================
// DB
// Database connection
require '../../includes/db-config.php';
require_once  'mailer.php';

// Escape helper
function esc($val, $conn) {
    return mysqli_real_escape_string($conn, trim($val ?? ''));
}

// File upload helper
function uploadFile($name, $dir = 'uploads/') {
    if (!isset($_FILES[$name]) || $_FILES[$name]['error'] !== 0) return null;
    if (!is_dir($dir)) mkdir($dir, 0777, true);

    $file = time() . '_' . basename($_FILES[$name]['name']);
    return move_uploaded_file($_FILES[$name]['tmp_name'], $dir.$file) ? $file : null;
}

// ========================
// Upload Files
// ========================
$approach_road  = uploadFile('approach_road');
$front_view     = uploadFile('front_view');
$back_view      = uploadFile('back_view');
$reception_area = uploadFile('reception_area');
$domain_lab     = uploadFile('domain_lab');
$classroom      = uploadFile('classroom');
$washrooms      = uploadFile('washrooms');
$it_lab         = uploadFile('it_lab');
$signature      = uploadFile('signature');
$rubber_stamp   = uploadFile('rubber_stamp');

// ========================
// Form Fields
// ========================

// Service Partner Details
$institution_name    = esc($_POST['skill_name_intstution'], $conn);
$dir_name            = esc($_POST['dir_name'], $conn);
$dir_pincode         = esc($_POST['dir_pincode'], $conn);
$dir_state           = esc($_POST['dir_state'], $conn);
$dir_district        = esc($_POST['dir_district'], $conn);
$dir_mob_number      = esc($_POST['dir_mob_number'], $conn);
$dir_address         = esc($_POST['dir_address'], $conn);
$dir_contact_details = esc($_POST['dir_contact_details'], $conn);
$dir_email           = esc($_POST['dir_email'], $conn);
$dir_aadhar          = esc($_POST['dir_aadhar'], $conn);

// Centre Manager Details
$cmgr_name            = esc($_POST['cmgr_name'], $conn);
$cmgr_pincode         = esc($_POST['cmgr_pincode'], $conn);
$cmgr_state           = esc($_POST['cmgr_state'], $conn);
$cmgr_district        = esc($_POST['cmgr_district'], $conn);
$cmgr_mob_number      = esc($_POST['cmgr_mob_number'], $conn);
$cmgr_address         = esc($_POST['cmgr_address'], $conn);
$cmgr_contact_details = esc($_POST['cmgr_contact_details'], $conn);
$cmgr_email           = esc($_POST['cmgr_email'], $conn);
$cmgr_aadhar          = esc($_POST['cmgr_aadhar'], $conn);

// Infrastructure Details
$infra_dtls_training        = esc($_POST['infra_dtls_training'], $conn);
$infra_dtls_builtup         = esc($_POST['infra_dtls_builtup'], $conn);
$infra_dtls_compound        = esc($_POST['infra_dtls_compound'], $conn);
$infra_dtls_types_ownership = esc($_POST['infra_dtls_types_ownership'], $conn);
$infra_dtls_leased_rented   = esc($_POST['infra_dtls_leased_rented'], $conn);

// Declaration Section
$place            = esc($_POST['place'], $conn);
// $declaration_date = esc($_POST['date'], $conn);
$declaration_date = date('Y-m-d');

// ========================
// Insert Query
// ========================
$sql = "INSERT INTO center_verfiy1 (
    form_type,
    institution_name,
    dir_name, dir_pincode, dir_state, dir_district, dir_contact_details, dir_email, dir_aadhar, dir_mob_number, dir_address,
    cmgr_name, cmgr_pincode, cmgr_state, cmgr_district, cmgr_contact_details, cmgr_email, cmgr_aadhar, cmgr_mob_number, cmgr_address,
    infra_dtls_training, infra_dtls_builtup, infra_dtls_compound, infra_dtls_types_ownership, infra_dtls_leased_rented,
    approach_road, front_view, back_view, reception_area, domain_lab, classroom, washrooms, it_lab, signature, rubber_stamp,
    place, declaration_date
) VALUES (
    'skill',
    '$institution_name',
    '$dir_name', '$dir_pincode', '$dir_state', '$dir_district', '$dir_contact_details', '$dir_email', '$dir_aadhar', '$dir_mob_number', '$dir_address',
    '$cmgr_name', '$cmgr_pincode', '$cmgr_state', '$cmgr_district', '$cmgr_contact_details', '$cmgr_email', '$cmgr_aadhar', '$cmgr_mob_number', '$cmgr_address',
    '$infra_dtls_training', '$infra_dtls_builtup', '$infra_dtls_compound', '$infra_dtls_types_ownership', '$infra_dtls_leased_rented',
    '$approach_road', '$front_view', '$back_view', '$reception_area', '$domain_lab', '$classroom', '$washrooms', '$it_lab', '$signature', '$rubber_stamp',
    '$place', '$declaration_date'
)";

// Execute Query
// if(mysqli_query($conn, $sql)){
//     $service_id = mysqli_insert_id($conn);
//     echo json_encode(['status' => 200, 'service_id' => $service_id, 'message' => 'Information saved successfully']);
// } else {
//     echo json_encode(['status' => 'error', 'message' => 'Database error: '.mysqli_error($conn)]);
// }

// ========================
// Execute Query
// ========================
if(mysqli_query($conn, $sql)){
    $service_id = mysqli_insert_id($conn);

    // Send email to director
    $mailSent = sendMails($dir_email, $dir_name, $service_id, $declaration_date);

    $message = 'Information saved successfully';
    if(!$mailSent){
        $message .= ' but email could not be sent';
    }

    echo json_encode([
        'status' => 200,
        'service_id' => $service_id,
        'message' => $message
    ]);

} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: '.mysqli_error($conn)
    ]);
}
?>
