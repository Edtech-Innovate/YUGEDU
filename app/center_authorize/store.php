<?php
require __DIR__ . "/../../includes/db-config.php";
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
mysqli_set_charset($conn, "utf8mb4");
// echo('hello');
// echo('<pre>');print_r($_FILES['file']);die;
header('Content-Type: application/json');

// ---------- helpers ----------
function respond($arr)
{
    echo json_encode($arr);
    exit;
}
function normalize_date($raw)
{
    $raw = trim((string)$raw);
    if ($raw === '') return null;

    // Try d-m-Y
    $dt = DateTime::createFromFormat('d-m-Y', $raw);
    if ($dt && $dt->format('d-m-Y') === $raw) return $dt->format('Y-m-d');

    // Try d/m/Y
    $dt = DateTime::createFromFormat('d/m/Y', $raw);
    if ($dt && $dt->format('d/m/Y') === $raw) return $dt->format('Y-m-d');

    // Try Y-m-d
    $dt = DateTime::createFromFormat('Y-m-d', $raw);
    if ($dt && $dt->format('Y-m-d') === $raw) return $dt->format('Y-m-d');

    // Try Y/m/d
    $dt = DateTime::createFromFormat('Y/m/d', $raw);
    if ($dt && $dt->format('Y/m/d') === $raw) return $dt->format('Y-m-d');

    return null;
}

/**
 * Check duplicates by phone or email.
 * Returns ['exists' => bool, 'by' => 'phone'|'email'|null, 'id' => int|null]
 */
function check_dupe(mysqli $conn, $phone, $email)
{
    // If both are empty, treat as duplicate invalid data
    if ($phone === '' && $email === '') {
        return ['exists' => true, 'by' => 'both_empty', 'id' => null];
    }

    if ($phone !== '' && $email !== '') {
        $sql = "SELECT id, phone, email FROM center_authorize WHERE phone = ? OR email = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $phone, $email);
    } elseif ($phone !== '') {
        $sql = "SELECT id, phone FROM center_authorize WHERE phone = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $phone);
    } else { // email only
        $sql = "SELECT id, email FROM center_authorize WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
    }

    if (!$stmt->execute()) {
        return ['exists' => true, 'by' => 'db_error', 'id' => null];
    }
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        if (isset($row['phone']) && $row['phone'] === $phone) {
            return ['exists' => true, 'by' => 'phone', 'id' => (int)$row['id']];
        }
        if (isset($row['email']) && $row['email'] === $email) {
            return ['exists' => true, 'by' => 'email', 'id' => (int)$row['id']];
        }
        // fallback
        return ['exists' => true, 'by' => 'unknown', 'id' => (int)$row['id']];
    }
    return ['exists' => false, 'by' => null, 'id' => null];
}

// Map "Bvoc"/"Skill" to ids
function map_type_id($v)
{
    $v = trim((string)$v);
    if (strcasecmp($v, 'Bvoc') === 0) return 47;
    if (strcasecmp($v, 'Skill') === 0) return 48;
    // If it's a number already
    if (ctype_digit($v)) return (int)$v;
    return $v; // leave as-is; DB may reject if invalid
}

// Build full address with city/district/state/pincode (single form case)
function build_full_address($conn, $address, $city, $district, $state, $pincode)
{
    $parts = [
        mysqli_real_escape_string($conn, trim($address)),
        mysqli_real_escape_string($conn, ucwords(trim($city))),
        mysqli_real_escape_string($conn, ucwords(trim($district))),
        mysqli_real_escape_string($conn, ucwords(trim($state))),
        mysqli_real_escape_string($conn, trim($pincode)),
    ];
    return implode(', ', array_filter($parts));
}

// ---------- SINGLE INSERT (form submit) ----------
// if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['center_name']) && !isset($_FILES['file'])) {
//     $center_name   = trim($_POST['center_name']);
//     $phone         = trim($_POST['phone'] ?? '');
//     $email         = trim($_POST['email'] ?? '');
//     $programs      = trim($_POST['programs'] ?? '');
//     $type_id_raw   = trim($_POST['type'] ?? '');
//     $date_of_issue = normalize_date($_POST['date_of_issue'] ?? '');
//     $payment_type =  $_POST['payment_type'] ?? '';
//     $amount =  $_POST['amount'] ?? '';
//     $payment_proof =  $_POST['payment_proof'] ?? '';
//     $address = build_full_address(
//         $conn,
//         trim($_POST['address'] ?? ''),
//         trim($_POST['city'] ?? ''),
//         trim($_POST['district'] ?? ''),
//         trim($_POST['state'] ?? ''),
//         trim($_POST['pincode'] ?? '')
//     );

//     // Duplicate check
//     $dupe = check_dupe($conn, $phone, $email);
//     if ($dupe['exists']) {
//         $by = $dupe['by'] === 'phone' ? 'phone' : ($dupe['by'] === 'email' ? 'email' : 'phone/email');
//         respond([
//             'status'  => 409,
//             'message' => "Duplicate $by found. Record not inserted."
//         ]);
//     }

//     $type_id = map_type_id($type_id_raw);

//     $sql = "INSERT INTO center_authorize 
//             (center_name, type_id, date_of_issue, programs, address, phone, email,payment_type,amount,payment_proof) 
//             VALUES (?, ?, ?, ?, ?, ?, ?)";
//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param(
//         "sisssss",
//         $center_name,
//         $type_id,
//         $date_of_issue,  // can be null
//         $programs,
//         $address,
//         $phone,
//         $email,
//         $payment_type,
//         $amount,
//         $payment_proof
//     );

//     if ($stmt->execute()) {
//         respond([
//             'status'  => 200,
//             'message' => $center_name . ' added successfully!'
//         ]);
//     } else {
//         // MySQL unique error? return nice message
//         if ($conn->errno == 1062) {
//             respond([
//                 'status'  => 409,
//                 'message' => 'Duplicate phone or email. Record not inserted.'
//             ]);
//         }
//         respond([
//             'status'  => 500,
//             'message' => 'Database Error: ' . $stmt->error
//         ]);
//     }
// }
//dublicate data insert
// if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['center_name']) && !isset($_FILES['file'])) {
//     $center_name   = trim($_POST['center_name']);
//     $phone         = trim($_POST['phone'] ?? '');
//     $email         = trim($_POST['email'] ?? '');
//     $programs      = trim($_POST['programs'] ?? '');
//     $type_id_raw   = trim($_POST['type'] ?? '');
//     $date_of_issue = normalize_date($_POST['date_of_issue'] ?? '');
//     $payment_type  = trim($_POST['payment_type'] ?? '');
//     $amount        = trim($_POST['amount'] ?? '');

//     $address = build_full_address(
//         $conn,
//         trim($_POST['address'] ?? ''),
//         trim($_POST['city'] ?? ''),
//         trim($_POST['district'] ?? ''),
//         trim($_POST['state'] ?? ''),
//         trim($_POST['pincode'] ?? '')
//     );
//     // echo('<pre>');print_r($_FILES['payment_proof']);die;
//     // Upload Payment Proof (image/pdf)
//     $payment_proof = '';
//     if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
//         $upload_dir = __DIR__ . "/../../uploads/payment_proof/";
//         if (!is_dir($upload_dir)) {
//             mkdir($upload_dir, 0777, true);
//         }

//         $file_tmp  = $_FILES['payment_proof']['tmp_name'];
//         $file_name = time() . "_" . basename($_FILES['payment_proof']['name']);
//         $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

//         // allow pdf and images
//         $allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
//         if (in_array($file_ext, $allowed)) {
//             $target = $upload_dir . $file_name;
//             if (move_uploaded_file($file_tmp, $target)) {
//                 $payment_proof = "/uploads/payment_proof/" . $file_name; // relative path to save in DB
//             }
//         }
//     }

//     // Duplicate check
//     $dupe = check_dupe($conn, $phone, $email);
//     if ($dupe['exists']) {
//         $by = $dupe['by'] === 'phone' ? 'phone' : ($dupe['by'] === 'email' ? 'email' : 'phone/email');
//         respond([
//             'status'  => 409,
//             'message' => "Duplicate $by found. Record not inserted."
//         ]);
//     }

//     $type_id = map_type_id($type_id_raw);

//     // ✅ Corrected query with 10 placeholders
//     $sql = "INSERT INTO center_authorize 
//             (center_name, type_id, date_of_issue, programs, address, phone, email, payment_type, amount, payment_proof) 
//             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param(
//         "sissssssss",  // 10 values: s=string, i=int
//         $center_name,
//         $type_id,
//         $date_of_issue,
//         $programs,
//         $address,
//         $phone,
//         $email,
//         $payment_type,
//         $amount,
//         $payment_proof
//     );

//     if ($stmt->execute()) {
//         respond([
//             'status'  => 200,
//             'message' => $center_name . ' added successfully!'
//         ]);
//     } else {
//         if ($conn->errno == 1062) {
//             respond([
//                 'status'  => 409,
//                 'message' => 'Duplicate phone or email. Record not inserted.'
//             ]);
//         }
//         respond([
//             'status'  => 500,
//             'message' => 'Database Error: ' . $stmt->error
//         ]);
//     }
// }
//end dublicate data
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['center_name']) && !isset($_FILES['file'])) {
    $center_name   = trim($_POST['center_name']);
    $phone         = trim($_POST['phone'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $programs      = trim($_POST['programs'] ?? '');
    $type_id_raw   = trim($_POST['type'] ?? '');
    $date_of_issue = normalize_date($_POST['date_of_issue'] ?? '');
    $payment_type  = trim($_POST['payment_type'] ?? '');
    $amount        = trim($_POST['amount'] ?? '');

    $address = build_full_address(
        $conn,
        trim($_POST['address'] ?? ''),
        trim($_POST['city'] ?? ''),
        trim($_POST['district'] ?? ''),
        trim($_POST['state'] ?? ''),
        trim($_POST['pincode'] ?? '')
    );

    // Upload Payment Proof (image/pdf)
    $payment_proof = '';
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . "/../../uploads/payment_proof/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_tmp  = $_FILES['payment_proof']['tmp_name'];
        $file_name = time() . "_" . basename($_FILES['payment_proof']['name']);
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
        if (in_array($file_ext, $allowed)) {
            $target = $upload_dir . $file_name;
            if (move_uploaded_file($file_tmp, $target)) {
                $payment_proof = "/uploads/payment_proof/" . $file_name;
            }
        }
    }

    $type_id = map_type_id($type_id_raw);

    // ✅ Duplicate check with type_id consideration
    $dupe = check_dupe($conn, $phone, $email);
    if ($dupe['exists']) {
        $sql_check_type = "SELECT type_id FROM center_authorize WHERE phone = ? OR email = ?";
        $stmt_type = $conn->prepare($sql_check_type);
        $stmt_type->bind_param("ss", $phone, $email);
        $stmt_type->execute();
        $result = $stmt_type->get_result();

        $insert_allowed = true;
        while ($row = $result->fetch_assoc()) {
            if ((int)$row['type_id'] === (int)$type_id) {
                $insert_allowed = false; // Same type_id exists
                break;
            }
        }

        if (!$insert_allowed) {
            respond([
                'status'  => 409,
                'message' => 'Duplicate phone/email with same type found. Record not inserted.'
            ]);
        }
    }

    // Insert query
    $sql = "INSERT INTO center_authorize 
            (center_name, type_id, date_of_issue, programs, address, phone, email, payment_type, amount, payment_proof) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sissssssss",
        $center_name,
        $type_id,
        $date_of_issue,
        $programs,
        $address,
        $phone,
        $email,
        $payment_type,
        $amount,
        $payment_proof
    );

    if ($stmt->execute()) {
        respond([
            'status'  => 200,
            'message' => $center_name . ' added successfully!'
        ]);
    } else {
        if ($conn->errno == 1062) {
            respond([
                'status'  => 409,
                'message' => 'Duplicate phone or email. Record not inserted.'
            ]);
        }
        respond([
            'status'  => 500,
            'message' => 'Database Error: ' . $stmt->error
        ]);
    }
}

// ---------- CSV BULK UPLOAD ----------
// ---------- CSV BULK UPLOAD ----------
// if (isset($_FILES['file']['name']) && $_FILES['file']['size'] > 0) {
//     $fileName = $_FILES['file']['tmp_name'];
//     $fh = fopen($fileName, "r");
//     if (!$fh) {
//         respond(['status' => 400, 'message' => 'Unable to read the uploaded file.']);
//     }

//     // Determine new batch number
//     $res = mysqli_query($conn, "SELECT MAX(batch) AS max_batch FROM center_authorize");
//     $row = $res ? mysqli_fetch_assoc($res) : null;
//     $batch = ($row && isset($row['max_batch']) && is_numeric($row['max_batch'])) ? ((int)$row['max_batch'] + 1) : 1;

//     $ins = $conn->prepare(
//         "INSERT INTO center_authorize (center_name, type_id, date_of_issue, address, programs, batch, phone, email)
//          VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
//     );

//     $seenPhones = [];
//     $seenEmails = [];

//     // Temp output file
//     $outputDir = __DIR__ . "/../../uploads/tmp";
//     if (!is_dir($outputDir)) {
//         @mkdir($outputDir, 0777, true);
//     }
//     $filename = "center_authorize_upload_result_" . date("Ymd_His") . ".csv";
//     $outPath = $outputDir . "/" . $filename;
//     $out = fopen($outPath, "w");

//     fputcsv($out, ['center_name', 'type', 'date_of_issue', 'address', 'programs', 'phone', 'email', 'payment type', 'amount', 'upload_status', 'error']);

//     $first = true;
//     $inserted = 0;
//     $skipped  = 0;

//     while (($col = fgetcsv($fh, 10000, ",")) !== false) {
//         if ($col === null || count($col) === 0) continue;

//         if ($first) {
//             $maybeHeader = strtolower(implode(' ', $col));
//             if (strpos($maybeHeader, 'center') !== false || strpos($maybeHeader, 'name') !== false) {
//                 $first = false;
//                 continue;
//             }
//             $first = false;
//         }

//         for ($i = count($col); $i < 7; $i++) $col[$i] = '';

//         $raw_center_name = trim($col[0] ?? '');
//         $raw_type        = trim($col[1] ?? '');
//         $raw_doi         = trim($col[2] ?? '');
//         $raw_address     = trim($col[3] ?? '');
//         $raw_programs    = trim($col[4] ?? '');
//         $raw_phone       = trim($col[5] ?? '');
//         $raw_email       = trim($col[6] ?? '');
//         $raw_payment_type = trim($col[7] ?? '');
//         $raw_amount       = trim($col[8] ?? '');


//         $resultRow = [$raw_center_name, $raw_type, $raw_doi, $raw_address, $raw_programs, $raw_phone, $raw_email, $raw_payment_type, $raw_amount];

//         if ($raw_center_name === '' || $raw_address === '' || $raw_programs === '') {
//             $skipped++;
//             fputcsv($out, array_merge($resultRow, ['skipped', 'Missing required fields']));
//             continue;
//         }

//         $center_name = $raw_center_name;
//         $type_id     = map_type_id($raw_type);
//         $doi         = normalize_date($raw_doi);
//         $address     = $raw_address;
//         $programs    = $raw_programs;
//         $phone       = $raw_phone;
//         $email       = $raw_email;
//         $payment_type = $raw_payment_type;
//         $amount = $raw_amount;

//         if ($phone !== '' && isset($seenPhones[$phone])) {
//             $skipped++;
//             fputcsv($out, array_merge($resultRow, ['skipped', 'Duplicate phone in CSV']));
//             continue;
//         }
//         if ($email !== '' && isset($seenEmails[$email])) {
//             $skipped++;
//             fputcsv($out, array_merge($resultRow, ['skipped', 'Duplicate email in CSV']));
//             continue;
//         }

//         $dupe = check_dupe($conn, $phone, $email);
//         if ($dupe['exists']) {
//             $skipped++;
//             $reason = ($dupe['by'] === 'phone') ? 'Duplicate phone' : (($dupe['by'] === 'email') ? 'Duplicate email' : 'Duplicate phone/email');
//             fputcsv($out, array_merge($resultRow, ['skipped', $reason]));
//             continue;
//         }

//         $ins->bind_param("sisssiss", $center_name, $type_id, $doi, $address, $programs, $batch, $phone, $email);

//         if ($ins->execute()) {
//             $inserted++;
//             if ($phone !== '') $seenPhones[$phone] = true;
//             if ($email !== '') $seenEmails[$email] = true;
//             fputcsv($out, array_merge($resultRow, ['uploaded', '']));
//         } else {
//             $skipped++;
//             $errMsg = ($conn->errno == 1062) ? 'Duplicate phone/email' : ($ins->error);
//             fputcsv($out, array_merge($resultRow, ['skipped', $errMsg]));
//         }
//     }

//     fclose($fh);
//     fclose($out);

//     // Stream back result CSV
//     header('Content-Type: text/csv');
//     header('Content-Disposition: attachment; filename="' . $filename . '";');
//     readfile($outPath);
//     exit;
// }

// ----------------- Google Drive downloader -----------------
// function downloadDriveFile($driveUrl, $saveDir) {
//     if (preg_match('/\/d\/(.*?)\//', $driveUrl, $matches)) {
//         $fileId = $matches[1];
//     } elseif (preg_match('/id=([^&]+)/', $driveUrl, $matches)) {
//         $fileId = $matches[1];
//     } else {
//         return false; // invalid
//     }

//     $downloadUrl = "https://drive.google.com/uc?export=download&id=" . $fileId;
//     $fileContent = @file_get_contents($downloadUrl);
//     if ($fileContent === false) return false;

//     // detect mime -> extension
//     $finfo = finfo_open(FILEINFO_MIME_TYPE);
//     $mimeType = finfo_buffer($finfo, $fileContent);
//     finfo_close($finfo);

//     $ext = match ($mimeType) {
//         'image/jpeg' => 'jpg',
//         'image/png'  => 'png',
//         'image/gif'  => 'gif',
//         'application/pdf' => 'pdf',
//         default => 'dat'
//     };

//     $newName = uniqid("proof_") . "." . $ext;
//     $destFile = rtrim($saveDir, "/") . "/" . $newName;
//     file_put_contents($destFile, $fileContent);

//     return "/uploads/payment_proof/" . $newName;
// }

// if (isset($_FILES['file']['name']) && $_FILES['file']['size'] > 0) {
//     $fileName = $_FILES['file']['tmp_name'];
//     $fh = fopen($fileName, "r");
//     if (!$fh) {
//         respond(['status' => 400, 'message' => 'Unable to read the uploaded file.']);
//     }

//     // Determine new batch number
//     $res = mysqli_query($conn, "SELECT MAX(batch) AS max_batch FROM center_authorize");
//     $row = $res ? mysqli_fetch_assoc($res) : null;
//     $batch = ($row && isset($row['max_batch']) && is_numeric($row['max_batch'])) ? ((int)$row['max_batch'] + 1) : 1;

//     // Prepare insert statement
//     // $ins = $conn->prepare(
//     //     "INSERT INTO center_authorize 
//     //     (center_name, type_id, date_of_issue, address, programs, batch, phone, email, payment_type, amount, payment_proof)
//     //     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
//     // );
//      $ins = $conn->prepare(
//         "INSERT INTO center_authorize 
//         (center_name, type_id,address, programs, batch, phone, email)
//         VALUES (?, ?, ?, ?, ?, ?, ?)"
//     );

//     $seenPhones = [];
//     $seenEmails = [];

//     // Temp output file
//     $outputDir = __DIR__ . "/../../uploads/tmp";
//     if (!is_dir($outputDir)) {
//         @mkdir($outputDir, 0777, true);
//     }
//     $filename = "center_authorize_upload_result_" . date("Ymd_His") . ".csv";
//     $outPath = $outputDir . "/" . $filename;
//     $out = fopen($outPath, "w");

//     fputcsv($out, [
//         'center_name',
//         'type',
//         // 'date_of_issue',
//         'address',
//         'phone',
//         'email',
//         // 'payment_type',
//         // 'amount',
//         // 'payment_proof_url',
//         'upload_status',
//         'error'
//     ]);

//     $first = true;
//     while (($col = fgetcsv($fh, 10000, ",")) !== false) {
//         if ($col === null || count($col) === 0) continue;

//         if ($first) {
//             $maybeHeader = strtolower(implode(' ', $col));
//             if (strpos($maybeHeader, 'center') !== false || strpos($maybeHeader, 'name') !== false) {
//                 $first = false;
//                 continue;
//             }
//             $first = false;
//         }

//         // Ensure all expected columns exist (10 including proof)
//         for ($i = count($col); $i < 10; $i++) $col[$i] = '';

//         $raw_center_name = trim($col[0] ?? '');
//         $raw_type        = trim($col[1] ?? '');
//         // $raw_doi         = trim($col[2] ?? '');
//         $raw_address     = trim($col[3] ?? '');
//         $raw_phone       = trim($col[5] ?? '');
//         $raw_email       = trim($col[6] ?? '');
//         // $raw_payment_type = trim($col[7] ?? '');
//         // $raw_amount      = trim($col[8] ?? '');
//         // $raw_proof_file  = trim($col[9] ?? '');  // filename or URL

//         $resultRow = [
//             $raw_center_name,
//             $raw_type,
//             // $raw_doi,
//             $raw_address,
//             $raw_phone,
//             $raw_email,
//             // $raw_payment_type,
//             // $raw_amount,
//             // $raw_proof_file
//         ];

//         if ($raw_center_name === '' || $raw_address === '' ) {
//             fputcsv($out, array_merge($resultRow, ['skipped', 'Missing required fields']));
//             continue;
//         }

//         $center_name  = $raw_center_name;
//         $type_id      = map_type_id($raw_type);
//         // $doi          = normalize_date($raw_doi);
//         $address      = $raw_address;
//         $phone        = $raw_phone;
//         $email        = $raw_email;
//         // $payment_type = $raw_payment_type;
//         // $amount       = $raw_amount;

//         // ---------------- Payment proof handler ----------------
//         // $payment_proof_url = '';
//         // if ($raw_proof_file !== '') {
//         //     $proofDir = __DIR__ . "/../../uploads/payment_proof";
//         //     if (!is_dir($proofDir)) {
//         //         @mkdir($proofDir, 0777, true);
//         //     }

//             // if (preg_match('/drive\.google\.com/', $raw_proof_file)) {
//             //     // Google Drive link
//             //     $payment_proof_url = downloadDriveFile($raw_proof_file, $proofDir);
//             // } elseif (filter_var($raw_proof_file, FILTER_VALIDATE_URL)) {
//             //     // Direct URL
//             //     $ext = pathinfo(parse_url($raw_proof_file, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'dat';
//             //     $newName = uniqid("proof_") . "." . $ext;
//             //     $destFile = $proofDir . "/" . $newName;
//             //     $fileData = @file_get_contents($raw_proof_file);
//             //     if ($fileData !== false) {
//             //         file_put_contents($destFile, $fileData);
//             //         $payment_proof_url = "/uploads/payment_proof/" . $newName;
//             //     }
//             // } else {
//             //     // Local file name
//             //     $srcFile = __DIR__ . "/../../uploads/tmp/" . basename($raw_proof_file);
//             //     $ext = pathinfo($srcFile, PATHINFO_EXTENSION) ?: 'dat';
//             //     $newName = uniqid("proof_") . "." . $ext;
//             //     $destFile = $proofDir . "/" . $newName;
//             //     if (file_exists($srcFile) && @copy($srcFile, $destFile)) {
//             //         $payment_proof_url = "/uploads/payment_proof/" . $newName;
//             //     }
//             // }
//         // }

//         // ---------------- Duplicate checks ----------------
//         // if ($phone !== '' && isset($seenPhones[$phone])) {
//         //     fputcsv($out, array_merge($resultRow, ['skipped', 'Duplicate phone in CSV']));
//         //     continue;
//         // }
//         // if ($email !== '' && isset($seenEmails[$email])) {
//         //     fputcsv($out, array_merge($resultRow, ['skipped', 'Duplicate email in CSV']));
//         //     continue;
//         // }

//         // $dupe = check_dupe($conn, $phone, $email);
//         // if ($dupe['exists']) {
//         //     $reason = ($dupe['by'] === 'phone') ? 'Duplicate phone' : (($dupe['by'] === 'email') ? 'Duplicate email' : 'Duplicate phone/email');
//         //     fputcsv($out, array_merge($resultRow, ['skipped', $reason]));
//         //     continue;
//         // }

//         // ---------------- Insert ----------------
//         // $ins->bind_param(
//         //     "sisssisssss",
//         //     $center_name,
//         //     $type_id,
//         //     // $doi,
//         //     $address,
//         //     $programs,
//         //     $batch,
//         //     $phone,
//         //     $email,
//         //     // $payment_type,
//         //     // $amount,
//         //     // $payment_proof_url
//         // );
//         $ins->bind_param(
//     "sisssss",   // 7 types → string, int, string, string, string, string, string
//     $center_name,
//     $type_id,
//     $address,
//     $programs,
//     $batch,
//     $phone,
//     $email
// );
// fputcsv($out, array_merge([$raw_center_name, $raw_type,  $raw_address, $raw_phone, $raw_email], ['uploaded', '']));

//         // if ($ins->execute()) {
//         //     if ($phone !== '') $seenPhones[$phone] = true;
//         //     if ($email !== '') $seenEmails[$email] = true;
//         //     // fputcsv($out, array_merge([$raw_center_name, $raw_type, $raw_doi, $raw_address, $raw_phone, $raw_email, $raw_payment_type, $raw_amount, $payment_proof_url], ['uploaded', '']));
//         //      fputcsv($out, array_merge([$raw_center_name, $raw_type,  $raw_address, $raw_phone, $raw_email], ['uploaded', '']));
//         // } else {
//         //     $errMsg = ($conn->errno == 1062) ? 'Duplicate phone/email' : ($ins->error);
//         //     fputcsv($out, array_merge($resultRow, ['skipped', $errMsg]));
//         // }
//     }

//     fclose($fh);
//     fclose($out);

//     // Stream back result CSV
//     header('Content-Type: text/csv');
//     header('Content-Disposition: attachment; filename="' . $filename . '";');
//     readfile($outPath);
//     exit;
// }
if (isset($_FILES['file']['name']) && $_FILES['file']['size'] > 0) {
    $fileName = $_FILES['file']['tmp_name'];
    $fh = fopen($fileName, "r");
    if (!$fh) {
        respond(['status' => 400, 'message' => 'Unable to read the uploaded file.']);
    }

    // Determine new batch number
    $res = mysqli_query($conn, "SELECT MAX(batch) AS max_batch FROM center_authorize");
    $row = $res ? mysqli_fetch_assoc($res) : null;
    $batch = ($row && isset($row['max_batch']) && is_numeric($row['max_batch']))
        ? ((int)$row['max_batch'] + 1)
        : 1;

    // Temp output file
    $outputDir = __DIR__ . "/../../uploads/tmp";
    if (!is_dir($outputDir)) {
        @mkdir($outputDir, 0777, true);
    }
    $filename = "center_authorize_upload_result_" . date("Ymd_His") . ".csv";
    $outPath = $outputDir . "/" . $filename;
    $out = fopen($outPath, "w");

    // CSV header
    fputcsv($out, [
        'center_name',
        'type',
        'address',
        'phone',
        'email',
        'upload_status',
        'error'
    ]);

    $first = true;
    while (($col = fgetcsv($fh, 10000, ",")) !== false) {
        if ($col === null || count($col) === 0) continue;

        // Skip header row
        if ($first) {
            $maybeHeader = strtolower(implode(' ', $col));
            if (strpos($maybeHeader, 'center') !== false || strpos($maybeHeader, 'name') !== false) {
                $first = false;
                continue;
            }
            $first = false;
        }

        // Ensure all expected columns exist
        for ($i = count($col); $i < 7; $i++) $col[$i] = '';

        $raw_center_name = trim($col[0] ?? '');
        $raw_type        = trim($col[1] ?? '');
        $raw_address     = trim($col[2] ?? '');
        $raw_phone       = trim($col[3] ?? '');
        $raw_email       = trim($col[4] ?? '');

        $resultRow = [
            $raw_center_name,
            $raw_type,
            $raw_address,
            $raw_phone,
            $raw_email
        ];

        if ($raw_center_name === '' || $raw_address === '') {
            fputcsv($out, array_merge($resultRow, ['skipped', 'Missing required fields']));
            continue;
        }

        // Prepare values
        $center_name = mysqli_real_escape_string($conn, $raw_center_name);
        $type_id     = (int) map_type_id($raw_type);
        $address     = mysqli_real_escape_string($conn, $raw_address);
        $programs    = ''; // CSV me column missing hai
        $phone       = mysqli_real_escape_string($conn, $raw_phone);
        $email       = mysqli_real_escape_string($conn, $raw_email);

        // Direct insert query
        $sql = "
            INSERT INTO center_authorize 
            (center_name, type_id, address, programs, batch, phone, email)
            VALUES (
                '$center_name',
                $type_id,
                '$address',
                '$programs',
                $batch,
                '$phone',
                '$email'
            )
        ";

        if (mysqli_query($conn, $sql)) {
            fputcsv($out, array_merge($resultRow, ['uploaded', '']));
        } else {
            fputcsv($out, array_merge($resultRow, ['skipped', mysqli_error($conn)]));
        }
    }

    fclose($fh);
    fclose($out);

    // Stream back result CSV
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '";');
    readfile($outPath);
    exit;
}








respond(['status' => 400, 'message' => 'Invalid request.']);
