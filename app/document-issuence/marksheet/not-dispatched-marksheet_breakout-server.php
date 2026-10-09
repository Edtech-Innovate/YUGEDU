<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../../includes/db-config.php';
session_start();

/* ----------------------------------------------------
   SAFE FETCH POST VALUES (Prevents Undefined Index Warning)
---------------------------------------------------- */
$draw        = $_POST['draw']        ?? 0;
$start       = $_POST['start']       ?? 0;
$length      = $_POST['length']      ?? 10;

$orderColumn = $_POST['order'][0]['column'] ?? null;
$orderDir    = $_POST['order'][0]['dir']    ?? "asc";
$searchValue = $_POST['search']['value']    ?? "";

/* ----------------------------------------------------
   IF ORDER NOT SET → DEFAULT
---------------------------------------------------- */
if ($orderColumn !== null) {
    $columnName = $_POST['columns'][$orderColumn]['data'] ?? "MarkSheet_Entry.ID";
    $orderby = "ORDER BY $columnName $orderDir";
} else {
    $orderby = "ORDER BY MarkSheet_Entry.ID ASC";
}

/* ----------------------------------------------------
   ESCAPE SEARCH VALUE SAFELY
---------------------------------------------------- */
$searchValue = mysqli_real_escape_string($conn, $searchValue);

/* ----------------------------------------------------
   SEARCH QUERY
---------------------------------------------------- */
$searchQuery = "";
if (!empty($searchValue)) {
    $searchQuery = " AND (
        MarkSheet_Entry.Enrollment_No LIKE '%$searchValue%' OR 
        MarkSheet_Entry.Marksheet_No LIKE '%$searchValue%' OR 
        MarkSheet_Entry.Duration LIKE '%$searchValue%'
    )";
}

/* ----------------------------------------------------
   TOTAL RECORDS WITHOUT FILTER
---------------------------------------------------- */
$totalQuery = "
    SELECT COUNT(*) AS total 
    FROM MarkSheet_Entry 
    WHERE Dispatch_status = '1'
";
$totalRecords = $conn->query($totalQuery)->fetch_assoc()['total'];

/* ----------------------------------------------------
   TOTAL WITH FILTER
---------------------------------------------------- */
$filterQuery = "
    SELECT COUNT(*) AS total
    FROM MarkSheet_Entry
    WHERE Dispatch_status = '1' $searchQuery
";
$totalRecordwithFilter = $conn->query($filterQuery)->fetch_assoc()['total'];

/* ----------------------------------------------------
   FETCH RECORDS (MAIN QUERY)
---------------------------------------------------- */
$query = "
    SELECT 
        MarkSheet_Entry.*, 
        Users.Name AS Center_Name,
        COUNT(Students.Enrollment_No) AS Marksheet_count,
        MarkSheet_Entry.Created_At AS insert_date
    FROM MarkSheet_Entry
    LEFT JOIN Students ON Students.Enrollment_No = MarkSheet_Entry.Enrollment_No
    LEFT JOIN Users ON Users.ID = MarkSheet_Entry.Added_For
    WHERE MarkSheet_Entry.Dispatch_status = '1'
    $searchQuery
    GROUP BY Users.Name, MarkSheet_Entry.Created_At, MarkSheet_Entry.ID
    $orderby
    LIMIT $start, $length
";

$result = $conn->query($query);

$data = [];
$sl = 1;

/* ----------------------------------------------------
   LOOP THROUGH RECORDS
---------------------------------------------------- */
while ($r = $result->fetch_assoc()) {

    $upload_file = '';
    $dispatch_date = '';

    if (!empty($r['Docket_Id'])) {
        $checkUpload = $conn->query("
            SELECT upload_file, dispatch_date 
            FROM dispatch_marksheet 
            WHERE dockect_id = '".$r['Docket_Id']."'
        ");

        if ($checkUpload->num_rows > 0) {
            $du = $checkUpload->fetch_assoc();
            $upload_file   = $du['upload_file'] ?? '';
            $dispatch_date = !empty($du['dispatch_date']) ? date('d-m-Y', strtotime($du['dispatch_date'])) : '';
        }
    }

    $insert_date = !empty($r['insert_date']) ? date('d-M-Y', strtotime($r['insert_date'])) : '';

    $data[] = [
        'Slno'            => $sl,
        'Center_Name'     => $r['Center_Name'],
        'Marksheet_count' => $r['Marksheet_count'],
        'Docket_Id'       => $r['Docket_Id'],
        'Center_id'       => $r['Added_For'],
        'upload_file'     => $upload_file,
        'Dispatch_status' => $r['Dispatch_status'],
        'insert_date'     => $insert_date,
        'dispatch_date'   => $dispatch_date
    ];

    $sl++;
}

/* ----------------------------------------------------
   DATATABLES RESPONSE
---------------------------------------------------- */
$response = [
    "draw"                 => intval($draw),
    "iTotalRecords"        => $totalRecords,
    "iTotalDisplayRecords" => $totalRecordwithFilter,
    "aaData"               => $data
];

echo json_encode($response);
