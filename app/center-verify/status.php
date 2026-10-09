<?php
header('Content-Type: application/json');

require '../../includes/db-config.php';
require_once 'send-welcome.php';

session_start();

/* Enable debug (turn OFF in production) */
ini_set('display_errors', 1);
error_reporting(E_ALL);

/* ================================
   METHOD CHECK
================================ */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 405, 'message' => 'Invalid request method']);
    exit;
}

/* ================================
   INPUT
================================ */
$id     = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$status = isset($_POST['status']) ? (int) $_POST['status'] : -1;

/* ================================
   VALIDATION
================================ */
if ($id <= 0 || !in_array($status, [0, 1, 2])) {
    echo json_encode(['status' => 400, 'message' => 'Invalid ID or status']);
    exit;
}

/* ================================
   CHECK RECORD EXISTS
================================ */
$checkCenter = mysqli_query($conn, "SELECT * FROM center_verfiy1 WHERE id = $id LIMIT 1");

if (!$checkCenter || mysqli_num_rows($checkCenter) === 0) {
    echo json_encode(['status' => 404, 'message' => 'Center record not found']);
    exit;
}

/* ================================
   UPDATE STATUS
================================ */
$updateCenter = "UPDATE center_verfiy1 SET status = $status WHERE id = $id";

if (!mysqli_query($conn, $updateCenter)) {
    echo json_encode(['status' => 500, 'message' => mysqli_error($conn)]);
    exit;
}

/* ================================
   IF APPROVED → CREATE USER
================================ */
if ($status === 1) {

    $row   = mysqli_fetch_assoc($checkCenter);

    $name  = mysqli_real_escape_string($conn, $row['institution_name']);
    $email = mysqli_real_escape_string($conn, $row['dir_email']);
    $contact_person_name = mysqli_real_escape_string($conn, $row['director_name'] ?? $row['institution_name']);
    $contact = mysqli_real_escape_string($conn, $row['dir_mob_number'] ?? '');
    $address = mysqli_real_escape_string($conn, $row['dir_address'] ?? '');
    $city = mysqli_real_escape_string($conn, $row['dir_district'] ?? '');
    $district = mysqli_real_escape_string($conn, $row['dir_district'] ?? '');
    $state = mysqli_real_escape_string($conn, $row['dir_state'] ?? '');
    $pincode = mysqli_real_escape_string($conn, $row['dir_pincode'] ?? '');

    $createdBy = isset($_SESSION['ID']) ? intval($_SESSION['ID']) : 0;
    // $vertical  = isset($_SESSION['vertical']) ? intval($_SESSION['vertical']) : 2;
$vertical  = 2;
$ISunique = 1;
$b2b = 1;

    // Generate unique center code
    function generateCenterCode($conn) {
        do {
            $code  = 'MDU' . rand(10000, 99999);
            $check = mysqli_query($conn, "SELECT ID FROM Users WHERE Code = '$code' LIMIT 1");
        } while (mysqli_num_rows($check) > 0);
        return $code;
    }

    // Check if user already exists
    $checkUser = mysqli_query($conn, "SELECT ID FROM Users WHERE Email = '$email' AND Role = 'Center' LIMIT 1");

    if (mysqli_num_rows($checkUser) === 0) {

        // $password   = password_hash('Center@123', PASSWORD_DEFAULT);
        $centerCode = generateCenterCode($conn);
        $photo      = '/assets/img/default-user.png';

        $insertUser = "
            INSERT INTO Users 
            (Code, Name, Short_Name, Contact_Name, Email, Mobile, Alternate_Mobile, Address, Pincode, City, District, State, Password, Photo, Role, Designation, Created_By, vertical, Status,Is_Unique,B2B_Partner, created_at)
            VALUES 
            ('$centerCode', '$name', '$contact_person_name', '$contact_person_name', '$email', '$contact', '', '$address', '$pincode', '$city', '$district', '$state', AES_ENCRYPT($contact, '60ZpqkOnqn0UQQ2MYTlJ'), '$photo', 'Center', 'Center', $createdBy, $vertical, 1,1,1, NOW())
        ";

        if (!mysqli_query($conn, $insertUser)) {
            echo json_encode(['status' => 500, 'message' => mysqli_error($conn)]);
            exit;
        }
        
        /* ================================
   SEND CREDENTIALS EMAIL
================================ */
sendCenterCredentialsMail(
    $email,
    $name,
    $centerCode,
    $contact // password = mobile number
);
    }
}

/* ================================
   RESPONSE
================================ */
$statusText = [0 => 'Pending', 1 => 'Approved', 2 => 'Rejected'][$status];

echo json_encode(['status' => 200, 'message' => "Status updated to $statusText successfully"]);
exit;
