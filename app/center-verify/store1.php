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
require_once 'mailer.php'; // optional – only if you want mail like reference

// Escape helper
function esc($val, $conn) {
    return mysqli_real_escape_string($conn, trim($val ?? ''));
}

// If you later add file inputs for vocational form
function uploadFile($name, $dir = 'uploads/') {
    if (!isset($_FILES[$name]) || $_FILES[$name]['error'] !== 0) return null;
    if (!is_dir($dir)) mkdir($dir, 0777, true);

    $file = time() . '_' . basename($_FILES[$name]['name']);
    return move_uploaded_file($_FILES[$name]['tmp_name'], $dir.$file) ? $file : null;
}

/*
 |==========================================
 |  FORM FIELDS  (VOCATIONAL FORM)
 |==========================================
*/

// A. General Information About the Institute
$institution_name     = esc($_POST['institution_name'], $conn);
$postal_address       = esc($_POST['postal_address'], $conn);
$city_place           = esc($_POST['city_place'], $conn);
$block_tehsil         = esc($_POST['block_tehsil'], $conn);
$pin_code             = esc($_POST['pin_code'], $conn);
$state                = esc($_POST['state'], $conn);
$district             = esc($_POST['district'], $conn);
$mobile_no_stdcode    = esc($_POST['mobile_no_stdcode'], $conn);
$email                = esc($_POST['email'], $conn);
$principal_name       = esc($_POST['principal_name'], $conn);
$qualification_princ  = esc($_POST['qualification_principal'], $conn);
$administrative_exp   = esc($_POST['administrative_exp'], $conn);
$teaching_exp         = esc($_POST['teaching_exp'], $conn);

// B. Society / Trust / Management
$name_address             = esc($_POST['name_address'], $conn);
$registered               = esc($_POST['registered'] ?? '', $conn);
$registered_under_act     = esc($_POST['registered_under_act'], $conn);
$registration_year        = esc($_POST['registration_year'], $conn);
$registration_no          = esc($_POST['registration_no'], $conn);
$whether_non_proprietary  = esc($_POST['whether_non_proprietary'] ?? '', $conn);

$manager_name         = esc($_POST['name'], $conn);
$manager_designation  = esc($_POST['designation'], $conn);
$manager_address      = esc($_POST['address'], $conn);
$manager_phone        = esc($_POST['phone'], $conn);

// C. Infrastructural and Academic Facilities
$institution_located  = esc($_POST['institution_located'] ?? '', $conn);
$area_acres           = esc($_POST['area_acres'], $conn);
$area_sq              = esc($_POST['area_sq'], $conn);
$built_area           = esc($_POST['built_area'], $conn);

// Rooms / Labs
$classrooms_total_rooms       = esc($_POST['classrooms_total_rooms'], $conn);
$classrooms_size_sqft         = esc($_POST['classrooms_size_sqft'], $conn);
$classrooms_area_sqft         = esc($_POST['classrooms_area_sqft'], $conn);

$science_lab_total_rooms      = esc($_POST['science_lab_total_rooms'] ?? '', $conn);
$science_lab_size_sqft        = esc($_POST['science_lab_size_sqft'] ?? '', $conn);
$science_lab_area_sqft        = esc($_POST['science_lab_area_sqft'] ?? '', $conn);

$physics_lab_total_rooms      = esc($_POST['physics_lab_total_rooms'] ?? '', $conn);
$physics_lab_size_sqft        = esc($_POST['physics_lab_size_sqft'] ?? '', $conn);
$physics_lab_area_sqft        = esc($_POST['physics_lab_area_sqft'] ?? '', $conn);

$chemistry_lab_total_rooms    = esc($_POST['chemistry_lab_total_rooms'] ?? '', $conn);
$chemistry_lab_size_sqft      = esc($_POST['chemistry_lab_size_sqft'] ?? '', $conn);
$chemistry_lab_area_sqft      = esc($_POST['chemistry_lab_area_sqft'] ?? '', $conn);

$biology_lab_total_rooms      = esc($_POST['biology_lab_total_rooms'] ?? '', $conn);
$biology_lab_size_sqft        = esc($_POST['biology_lab_size_sqft'] ?? '', $conn);
$biology_lab_area_sqft        = esc($_POST['biology_lab_area_sqft'] ?? '', $conn);

$maths_lab_total_rooms        = esc($_POST['maths_lab_total_rooms'] ?? '', $conn);
$maths_lab_size_sqft          = esc($_POST['maths_lab_size_sqft'] ?? '', $conn);
$maths_lab_area_sqft          = esc($_POST['maths_lab_area_sqft'] ?? '', $conn);

$computer_lab_total_rooms     = esc($_POST['computer_lab_total_rooms'] ?? '', $conn);
$computer_lab_size_sqft       = esc($_POST['computer_lab_size_sqft'] ?? '', $conn);
$computer_lab_area_sqft       = esc($_POST['computer_lab_area_sqft'] ?? '', $conn);

$library_total_rooms          = esc($_POST['library_total_rooms'] ?? '', $conn);
$library_size_sqft            = esc($_POST['library_size_sqft'] ?? '', $conn);
$library_area_sqft            = esc($_POST['library_area_sqft'] ?? '', $conn);

$other_total_rooms            = esc($_POST['other_total_rooms'] ?? '', $conn);
$other_size_sqft              = esc($_POST['other_size_sqft'] ?? '', $conn);
$other_area_sqft              = esc($_POST['other_area_sqft'] ?? '', $conn);

$special_workshop_total_rooms = esc($_POST['special_workshop_total_rooms'] ?? '', $conn);
$special_workshop_size_sqft   = esc($_POST['special_workshop_size_sqft'] ?? '', $conn);
$special_workshop_area_sqft   = esc($_POST['special_workshop_area_sqft'] ?? '', $conn);

// Teaching staff
$tgts_permanent       = esc($_POST['tgts_permanent'], $conn);
$tgts_part_time       = esc($_POST['tgts_part_time'], $conn);
$total_tgts           = esc($_POST['total_tgts'], $conn);

$pgts_permanent       = esc($_POST['pgts_permanent'], $conn);
$pgts_part_time       = esc($_POST['pgts_part_time'], $conn);
$total_pgts           = esc($_POST['total_pgts'], $conn);

$librarian_permanent  = esc($_POST['librarian_permanent'], $conn);
$librarian_part_time  = esc($_POST['librarian_part_time'], $conn);
$total_librarian      = esc($_POST['total_librarian'], $conn);

$vice_principal_permanent  = esc($_POST['vice_principal_permanent'], $conn);
$vice_principal_part_time  = esc($_POST['vice_principal_part_time'], $conn);
$total_vice_principal      = esc($_POST['total_vice_principal'], $conn);

// Administrative support staff
$clerks_permanent          = esc($_POST['clerks_permanent'] ?? '', $conn);
$clerks_nt_permanent       = esc($_POST['clerks_nt_permanent'] ?? '', $conn);
$total_clerks              = esc($_POST['total_clerks'] ?? '', $conn);

$lab_attendants_permanent  = esc($_POST['lab_attendants_permanent'] ?? '', $conn);
$lab_attendants_nt_perm    = esc($_POST['lab_attendants_nt_permanent'] ?? '', $conn);
$total_lab_attendants      = esc($_POST['total_lab_attendants'] ?? '', $conn);

$accountants_permanent     = esc($_POST['accountants_permanent'] ?? '', $conn);
$accountants_nt_permanent  = esc($_POST['accountants_nt_permanent'] ?? '', $conn);
$total_accountants         = esc($_POST['total_accountants'] ?? '', $conn);

$peons_permanent           = esc($_POST['peons_permanent'] ?? '', $conn);
$peons_nt_permanent        = esc($_POST['peons_nt_permanent'] ?? '', $conn);
$total_peons               = esc($_POST['total_peons'] ?? '', $conn);

// Other facilities (radio / checkbox)
$other_facilities                = esc($_POST['other_facilities'] ?? '', $conn);                 // toilets
$other_facilities_drinking_water = esc($_POST['other_facilities_drinking_water'] ?? '', $conn);  // water

$library_facilities_ttl_books    = esc($_POST['library_facilities_ttl_books'] ?? '', $conn);
$library_facilities_newspapers   = esc($_POST['library_facilities_newspapers'] ?? '', $conn);
$library_facilities_magazines    = esc($_POST['library_facilities_magazines'] ?? '', $conn);

// checkboxes: store yes/no
$sports_games   = isset($_POST['sports_games']) ? 'yes' : 'no';
$dance_room     = isset($_POST['dance_room']) ? 'yes' : 'no';
$gymnasium      = isset($_POST['gymnasium']) ? 'yes' : 'no';
$music_room     = isset($_POST['music_room']) ? 'yes' : 'no';
$hostel         = isset($_POST['hostel']) ? 'yes' : 'no';
$medical_checkup= isset($_POST['medical_checkup']) ? 'yes' : 'no';

// Other relevant information
$relevant_info1 = esc($_POST['relevant_info1'], $conn);
$relevant_info2 = esc($_POST['relevant_info2'], $conn);
$place          = esc($_POST['place'], $conn);
$declaration_date = date('Y-m-d');

/*
 |==========================================
 |  INSERT QUERY
 |  (use your own table/column names)
 |==========================================
*/

// $sql = "INSERT INTO center_verfiy1 (
//     form_type,
//     institution_name, postal_address, city_place, block_tehsil, pin_code, state, district,
//     mobile_no_stdcode, email, principal_name, qualification_principal,
//     administrative_exp, teaching_exp,

//     name_address, registered, registered_under_act, registration_year,
//     registration_no, whether_non_proprietary,
//     manager_name, manager_designation, manager_address, manager_phone,

//     institution_located, area_acres, area_sq, built_area,

//     classrooms_total_rooms, classrooms_size_sqft, classrooms_area_sqft,
//     science_lab_total_rooms, science_lab_size_sqft, science_lab_area_sqft,
//     physics_lab_total_rooms, physics_lab_size_sqft, physics_lab_area_sqft,
//     chemistry_lab_total_rooms, chemistry_lab_size_sqft, chemistry_lab_area_sqft,
//     biology_lab_total_rooms, biology_lab_size_sqft, biology_lab_area_sqft,
//     maths_lab_total_rooms, maths_lab_size_sqft, maths_lab_area_sqft,
//     computer_lab_total_rooms, computer_lab_size_sqft, computer_lab_area_sqft,
//     library_total_rooms, library_size_sqft, library_area_sqft,
//     other_total_rooms, other_size_sqft, other_area_sqft,
//     special_workshop_total_rooms, special_workshop_size_sqft, special_workshop_area_sqft,

//     tgts_permanent, tgts_part_time, total_tgts,
//     pgts_permanent, pgts_part_time, total_pgts,
//     librarian_permanent, librarian_part_time, total_librarian,
//     vice_principal_permanent, vice_principal_part_time, total_vice_principal,

//     clerks_permanent, clerks_nt_permanent, total_clerks,
//     lab_attendants_permanent, lab_attendants_nt_permanent, total_lab_attendants,
//     accountants_permanent, accountants_nt_permanent, total_accountants,
//     peons_permanent, peons_nt_permanent, total_peons,

//     other_facilities, other_facilities_drinking_water,
//     library_facilities_ttl_books, library_facilities_newspapers, library_facilities_magazines,
//     sports_games, dance_room, gymnasium, music_room, hostel, medical_checkup,

//     relevant_info1, relevant_info2, place, declaration_date
// ) VALUES (
//     'vocational',
//     '$institution_name', '$postal_address', '$city_place', '$block_tehsil', '$pin_code', '$state', '$district',
//     '$mobile_no_stdcode', '$email', '$principal_name', '$qualification_princ',
//     '$administrative_exp', '$teaching_exp',

//     '$name_address', '$registered', '$registered_under_act', '$registration_year',
//     '$registration_no', '$whether_non_proprietary',
//     '$manager_name', '$manager_designation', '$manager_address', '$manager_phone',

//     '$institution_located', '$area_acres', '$area_sq', '$built_area',

//     '$classrooms_total_rooms', '$classrooms_size_sqft', '$classrooms_area_sqft',
//     '$science_lab_total_rooms', '$science_lab_size_sqft', '$science_lab_area_sqft',
//     '$physics_lab_total_rooms', '$physics_lab_size_sqft', '$physics_lab_area_sqft',
//     '$chemistry_lab_total_rooms', '$chemistry_lab_size_sqft', '$chemistry_lab_area_sqft',
//     '$biology_lab_total_rooms', '$biology_lab_size_sqft', '$biology_lab_area_sqft',
//     '$maths_lab_total_rooms', '$maths_lab_size_sqft', '$maths_lab_area_sqft',
//     '$computer_lab_total_rooms', '$computer_lab_size_sqft', '$computer_lab_area_sqft',
//     '$library_total_rooms', '$library_size_sqft', '$library_area_sqft',
//     '$other_total_rooms', '$other_size_sqft', '$other_area_sqft',
//     '$special_workshop_total_rooms', '$special_workshop_size_sqft', '$special_workshop_area_sqft',

//     '$tgts_permanent', '$tgts_part_time', '$total_tgts',
//     '$pgts_permanent', '$pgts_part_time', '$total_pgts',
//     '$librarian_permanent', '$librarian_part_time', '$total_librarian',
//     '$vice_principal_permanent', '$vice_principal_part_time', '$total_vice_principal',

//     '$clerks_permanent', '$clerks_nt_permanent', '$total_clerks',
//     '$lab_attendants_permanent', '$lab_attendants_nt_perm', '$total_lab_attendants',
//     '$accountants_permanent', '$accountants_nt_permanent', '$total_accountants',
//     '$peons_permanent', '$peons_nt_permanent', '$total_peons',

//     '$other_facilities', '$other_facilities_drinking_water',
//     '$library_facilities_ttl_books', '$library_facilities_newspapers', '$library_facilities_magazines',
//     '$sports_games', '$dance_room', '$gymnasium', '$music_room', '$hostel', '$medical_checkup',

//     '$relevant_info1', '$relevant_info2', '$place', '$declaration_date'
// )";

$sql = "INSERT INTO center_verfiy1 (
    form_type,
    institution_name,

    dir_name, dir_pincode, dir_state, dir_district,
    dir_contact_details, dir_email, dir_mob_number, dir_address,

    cmgr_name, cmgr_pincode, cmgr_state, cmgr_district,
    cmgr_contact_details, cmgr_email, cmgr_mob_number, cmgr_address,

    infra_dtls_training, infra_dtls_builtup, infra_dtls_compound,
    infra_dtls_types_ownership, infra_dtls_leased_rented,

    approach_road, front_view, back_view, reception_area,
    domain_lab, classroom, washrooms, it_lab,
    signature, rubber_stamp,

    place, declaration_date
) VALUES (
    'vocational',
    '$institution_name',

    '$principal_name', '$pin_code', '$state', '$district',
    '$mobile_no_stdcode', '$email', '$mobile_no_stdcode', '$postal_address',

    '$manager_name', '$pin_code', '$state', '$district',
    '$manager_phone', '$email', '$manager_phone', '$manager_address',

    '$institution_located', '$built_area', '$area_sq',
    '$area_acres', '$institution_located',

    'Available', 'Available', 'Available', 'Available',
    'Available', '$classrooms_total_rooms', '$other_facilities', '$computer_lab_total_rooms',
    'Uploaded', 'Uploaded',

    '$place', '$declaration_date'
)";


// ========================
// Execute Query & Response
// ========================
if (mysqli_query($conn, $sql)) {
    $voc_form_id = mysqli_insert_id($conn);

    // optional mail similar to service partner
    // $mailSent = sendMails($email, $institution_name, $voc_form_id, $declaration_date);

    echo json_encode([
        'status'      => 200,
        'voc_form_id' => $voc_form_id,
        'message'     => 'Information saved successfully'
    ]);
} else {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Database error: ' . mysqli_error($conn)
    ]);
}
?>
