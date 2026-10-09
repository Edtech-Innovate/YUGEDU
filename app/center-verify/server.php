<?php
require '../../includes/db-config.php';
session_start();
header('Content-Type: application/json');

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Read POST parameters
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$searchValue = isset($_POST['search']['value']) ? mysqli_real_escape_string($conn, $_POST['search']['value']) : '';

// Base query
$sql = "SELECT id, institution_name, dir_email, form_type, status, created_at FROM center_verfiy1 WHERE 1";

// Search filter
if (!empty($searchValue)) {
    $sql .= " AND (institution_name LIKE '%$searchValue%' OR dir_email LIKE '%$searchValue%' OR form_type LIKE '%$searchValue%')";
}

// Total records without filtering
$totalRecordsQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM center_verfiy1");
if (!$totalRecordsQuery) {
    die(json_encode(['error' => mysqli_error($conn)]));
}
$totalRecords = mysqli_fetch_assoc($totalRecordsQuery)['total'];

// Total records with filtering
$filteredRecordsQuery = mysqli_query($conn, $sql);
if (!$filteredRecordsQuery) {
    die(json_encode(['error' => mysqli_error($conn), 'sql' => $sql]));
}
$totalFiltered = mysqli_num_rows($filteredRecordsQuery);

// Ordering
$orderColumnIndex = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
$orderDir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'ASC';
$orderColumns = ['id', 'institution_name', 'dir_email', 'form_type', 'status', 'created_at'];
$orderColumn = $orderColumns[$orderColumnIndex] ?? 'id';
$sql .= " ORDER BY $orderColumn $orderDir LIMIT $start, $length";

// Execute final query
$result = mysqli_query($conn, $sql);
if (!$result) {
    die(json_encode(['error' => mysqli_error($conn), 'sql' => $sql]));
}

// Fetch data
$data = [];
$no = $start + 1;
while ($row = mysqli_fetch_assoc($result)) {
    $statusMap = [0 => 'Pending', 1 => 'Approved', 2 => 'Rejected'];
    $statusLabel = $statusMap[$row['status']] ?? 'Unknown';

    $data[] = [
        "No" => $no++,
        "ID" => $row['id'],
        "Name" => $row['institution_name'],
        "Email" => $row['dir_email'],
        "FormType" => ucfirst($row['form_type']),
        "Status" => $statusLabel,
        // "CreatedAt" => date('d-m-Y H:i', strtotime($row['created_at']))
        "CreatedAt" => (!empty($row['created_at']) && strtotime($row['created_at']))
    ? date('d-m-Y H:i', strtotime($row['created_at']))
    : '-'
    ];
}

// echo "<pre>";
// print_r($data);
// echo "</pre>";
// exit; // Stop script here to see the output

// Response
echo json_encode([
    "draw" => $draw,
    "recordsTotal" => intval($totalRecords),
    "recordsFiltered" => intval($totalFiltered),
    "data" => $data
]);
