<?php
if (isset($_GET['id'])) {
    require '../../includes/db-config.php';
    
    // 1. Decode and sanitize ID
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $id = base64_decode($id);
    $id = intval(str_replace('W1Ebt1IhGN3ZOLplom9I', '', $id));

    $unique_id = $id;
    $student = $conn->query("SELECT Unique_ID FROM Students WHERE ID = $id");
    if ($student && $student->num_rows > 0) {
        $student_data = $student->fetch_assoc();
        $unique_id = $student_data['Unique_ID'];
    }

    $documents = $conn->query("SELECT Location FROM Student_Documents WHERE Student_ID = $id");

    // 2. Define a secure, absolute base path for your files
    // __DIR__ ensures it tracks from this file's actual location
    $base_path = realpath(__DIR__ . '/../../'); 

    $archive_file_name = $unique_id . ".zip";
    $zip_obj = new ZipArchive;

    if ($zip_obj->open($archive_file_name, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        $file_count = 0;

        while ($document = $documents->fetch_assoc()) {
            $files = explode("|", $document['Location']);
            foreach ($files as $file) {
                // Trim slashes to avoid "///" errors
                $relative_file = ltrim($file, '/');
                $full_file_path = $base_path . '/' . $relative_file;

                if (file_exists($full_file_path) && !is_dir($full_file_path)) {
                    $file_name = basename($full_file_path);
                    $file_name = str_replace($id, $unique_id, $file_name);
                    
                    $zip_obj->addFile($full_file_path, $file_name);
                    $file_count++;
                }
            }
        }
        $zip_obj->close();

        // 3. Prevent downloading an empty file if no documents were found
        if ($file_count === 0 || !file_exists($archive_file_name) || filesize($archive_file_name) === 0) {
            die("Error: No valid documents found to compress.");
        }

        // 4. Clear output buffer to prevent corrupted zip headers
        if (ob_get_level()) {
            ob_end_clean();
        }

        // 5. Send Headers
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-type: application/zip");
        header("Content-Disposition: attachment; filename=\"" . basename($archive_file_name) . "\"");
        header("Content-Transfer-Encoding: binary");
        header("Content-length: " . filesize($archive_file_name));
        
        // 6. Safely stream and delete
        readfile($archive_file_name);
        
        // Use ignore_user_abort so the script completes the deletion even if the user cancels
        ignore_user_abort(true);
        if (connection_aborted()) {
            unlink($archive_file_name);
        } else {
            unlink($archive_file_name);
        }
        exit;
    } else {
        die("Error: Could not create zip archive.");
    }
}