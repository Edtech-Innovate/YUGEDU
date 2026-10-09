<?php

require '../../includes/db-config.php';

use setasign\Fpdi\Fpdi;

require_once('../../extras/vendor/setasign/fpdf/fpdf.php');
require_once('../../extras/vendor/setasign/fpdi/src/autoload.php');
$color_pdf = isset($_GET['colortype']) ? strtolower(trim($_GET['colortype'])) : '';
/** ---------------- Helpers ---------------- */
if (!function_exists('sanitize_filename')) {
    function sanitize_filename($name)
    {
        $safe = preg_replace('/[^A-Za-z0-9_\-]+/', '_', $name);
        return trim($safe, '_');
    }
}

if (!function_exists('wrapTextByChars')) {
    // Multibyte-safe fixed-character wrap (do NOT break words)
    function wrapTextByChars($text, $maxChars)
    {
        if ($maxChars <= 0) return [$text];

        $out = [];
        $len = function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
        $buf = '';

        for ($i = 0; $i < $len; $i++) {
            $ch = function_exists('mb_substr') ? mb_substr($text, $i, 1, 'UTF-8') : substr($text, $i, 1);
            $buf .= $ch;
            $cur = function_exists('mb_strlen') ? mb_strlen($buf, 'UTF-8') : strlen($buf);
            if ($cur >= $maxChars) {
                $out[] = $buf;
                $buf = '';
            }
        }
        if ($buf !== '') $out[] = $buf;
        // print_r($out);die;
        return $out;
    }
}
if (!function_exists('wrapTextByWords')){
    function wrapTextByWords($text, $maxCharsPerLine = 65) {
    $words = preg_split('/\s+/', trim($text));
    $lines = [];
    $currentLine = '';

    foreach ($words as $word) {
        if (strlen($currentLine . ' ' . $word) > $maxCharsPerLine) {
            $lines[] = trim($currentLine);
            $currentLine = $word;
        } else {
            $currentLine .= ' ' . $word;
        }
    }

    if (!empty($currentLine)) {
        $lines[] = trim($currentLine);
    }

    return $lines;
}

}
if (!function_exists('select_template_for_row')) {
    function select_template_for_row(array $row,  string $color_pdf): string
    {
        // Pick template based on type_id for each row
        // $color_pdf = isset($_GET['colortype']);

        $typeId = isset($row['type_id']) ? (int)$row['type_id'] : 0;
         $path = ($typeId === 47)
                ? __DIR__ . '/bvoc.pdf'
                : __DIR__ . '/skill.pdf';
        // if ($color_pdf === "color") {
        //     $path = ($typeId === 47)
        //         ? __DIR__ . '/dummy_bvoc.pdf'
        //         : __DIR__ . '/dummy_skill.pdf';
        // } else {
        //     $path = __DIR__ . '/dummy.pdf';
            
        // }

        if (!file_exists($path)) {
            // Hard fail with readable message if template missing
            die('Template not found: ' . htmlspecialchars($path));
        }
        return $path;
    }
}

if (!function_exists('generate_certificate_pdf')) {
    /**
     * Create ONE PDF file for ONE DB row.
     * $dest: 'I' inline, 'D' download, 'F' to file path.
     * If $dest='F', $filename must be a full filesystem path.
     */
    function generate_certificate_pdf(array $data, string $templatePath, string $dest, string $filename, string $color_pdf)
    {

        $pdf = new FPDI();
        if ($color_pdf == "color") {
            $pdf->setSourceFile($templatePath);
            $tplIdx = $pdf->importPage(1);
        }

        $pdf->AddPage();
        if ($color_pdf == "color") {
            $pdf->useTemplate($tplIdx); // fit whole page
        }
        $pdf->SetFont('helvetica', 'B', ($data['type_id'] == 47) ? 23.5 : 24);

        // Center Name
        // $pdf->SetXY(41.8, ($data['type_id'] == 47) ? 98 : 106.3);
        // $pdf->Cell(120, 10, ucwords($data['center_name'] ?? ''), 0, 1);
       if ($data['type_id'] == 47) {
    $pdf->SetTextColor(169, 120, 25); // custom color
} else {
    $pdf->SetTextColor(0, 0, 0); // black
}

// Coordinates
$name_x = ($data['type_id'] == 47) ? 64 : 41.8;
$name_y = ($data['type_id'] == 47) ? 87   : 106.3;

// Wrap center name into max 25 chars per line
// $centerLines = wrapTextByChars(strtoupper($data['center_name'] ?? ''), 25);
$center_name_w= ($data['type_id'] == 47) ? 30 : 40;
$centerLines = wrapTextByWords(strtoupper($data['center_name'] ?? ''), $center_name_w);
$pdf->SetFont('Arial', 'B', 22); // Bold

foreach ($centerLines as $line) {
    $pdf->SetXY($name_x, $name_y);
    $pdf->Cell(120, 10, $line, 0, 1, 'C'); // Center each line
    $name_y += 10; // move down for next line
}

$pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 11);
        // Address (fixed chars per line + stepped X/Y)
        // $addressLines = wrapTextByChars($data['address'] ?? '', 65);
        $addressLines = wrapTextByWords($data['address'] ?? '', 65);
        //   $ay = ($data['type_id'] == 47) ? 108.2 : 121.4;
         $ay = ($data['type_id'] == 47) ? 108.2 : 123.4;
          $ax = ($data['type_id'] == 47) ? 64 : 41.8;

        $firstLine = true;
             foreach ($addressLines as $line) {
                //  print_r($firstLine);die;
              $pdf->SetXY($ax, $ay);
              $pdf->Cell( 120, 10, $line, 0, 1,'C');
              $ay += 6;
              $ax -= 2;
              $firstLine = false;
          }
          
        // Programs (fixed chars per line)
        // $programLines = wrapTextByChars($data['programs'] ?? '', 55);
        // $y =  ($data['type_id'] == 47) ? 190.4 : 191.7;
        // $x =  ($data['type_id'] == 47) ? 47 : 46.5;
        // foreach ($programLines as $line) {
        //     $pdf->SetXY($x, $y);
        //     $pdf->Cell(120, 10, $line, 0, 1, 'L');
        //     $y += 9;
        // }
         $pdf->SetFont('helvetica', '', 11);
        // Date of Issue (bottom-left). Full month: 19 August 2025
        $bottomMargin =  ($data['type_id'] == 47) ? 16 : 16;
        $pageHeight   = ($data['type_id'] == 47) ? 250 : 257.2; // your design coordinates


        $pdf->SetXY(($data['type_id'] == 47) ? 75 : 53, $pageHeight - $bottomMargin);
        $dateFormatted = '';
        if (!empty($data['date_of_issue'])) {
            $ts = strtotime($data['date_of_issue']);
            if ($ts !== false) $dateFormatted = date('d F Y', $ts);
        }
        $pdf->Cell(0, 0, $dateFormatted, 0, 0, 'L');

        if ($dest === 'F') {
            $pdf->Output($filename, 'F');
        } elseif ($dest === 'D') {
            $pdf->Output($filename, 'D');
        } else {
            $pdf->Output($filename, 'I');
        }
    }
}

/** ---------------- Routing: single or batch ---------------- */

$batch = isset($_GET['batch']) ? trim($_GET['batch']) : '';

if ($batch === '' || $batch === '0' || $batch == 0) {
    /** ---------- Single mode (by id) ---------- */
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) die('Invalid ID');

    $sql = "SELECT * FROM center_authorize WHERE id = $id LIMIT 1";
    $res = $conn->query($sql);
    if (!$res || $res->num_rows === 0) die('No records found!');

    $row = $res->fetch_assoc();

    $templatePath = select_template_for_row($row, $color_pdf);

    // Download file name
    $safeName = sanitize_filename(($row['center_name'] ?? 'certificate') . '_' . $id);
    $downloadName = "authorize_center_certificate_{$safeName}.pdf";

    // Clean output buffers (avoid corrupt PDF)
    if (ob_get_length()) {
        while (ob_get_level()) ob_end_clean();
    }

    generate_certificate_pdf($row, $templatePath, 'I', $downloadName, $color_pdf);
    exit;
} else {
    /** ---------- Batch mode (batch or all) → one PDF per row  ZIP ---------- */

    if ($batch === 'all') {
        $sql = "SELECT * FROM center_authorize ORDER BY id ASC";
        $zipLabel = 'all';
    } else {
        $batchInt = (int)$batch;
        $sql = "SELECT * FROM center_authorize WHERE batch = $batchInt ORDER BY id ASC";
        $zipLabel = $batchInt;
    }

    $res = $conn->query($sql);
    if (!$res || $res->num_rows === 0) die('No records found for batch ' . htmlspecialchars((string)$batch));

    if (!class_exists('ZipArchive')) die('ZipArchive extension is not enabled on this server.');

    // temp directory for PDFs
    $tempDir = __DIR__ . '/temp_pdfs_' . time();
    if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);

    // Generate one PDF per row (template decided per row)
    while ($row = $res->fetch_assoc()) {
        $templatePath = select_template_for_row($row, $color_pdf);

        $idForName = isset($row['id']) ? (int)$row['id'] : time();
        $safeName  = sanitize_filename(($row['center_name'] ?? 'certificate') . '_' . $idForName);
        $pdfPath   = $tempDir . "/authorize_center_certificate_{$safeName}.pdf";

        generate_certificate_pdf($row, $templatePath, 'F', $pdfPath, $color_pdf);
    }

    // Zip them
    $zipFilename = "authorize_center_batch_{$zipLabel}.zip";
    $zipPath     = __DIR__ . "/{$zipFilename}";

    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        foreach (glob($tempDir . '/*.pdf') as $f) @unlink($f);
        @rmdir($tempDir);
        die('Could not create zip file.');
    }

    foreach (glob($tempDir . '/*.pdf') as $pdfFile) {
        $zip->addFile($pdfFile, basename($pdfFile));
    }
    $zip->close();

    // Send ZIP
    if (ob_get_length()) {
        while (ob_get_level()) ob_end_clean();
    }
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . basename($zipFilename) . '"');
    header('Content-Length: ' . filesize($zipPath));
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: public');

    readfile($zipPath);

    // Cleanup
    foreach (glob($tempDir . '/*.pdf') as $f) @unlink($f);
    @rmdir($tempDir);
    @unlink($zipPath);
    exit;
}
