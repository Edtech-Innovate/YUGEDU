<?php
   ini_set('error_reporting', E_ALL );
   ini_set('display_errors', 1 );
   ini_set('max_execution_time', 0);
  session_start();
  require '../../includes/db-config.php';
  require ('../../extras/vendor/shuchkin/simplexlsxgen/src/SimpleXLSXGen.php');
include '../../includes/helpers.php';
  $searchValue = "";
  
//   print_r($_FILES);die;
 if (!empty($_FILES['center_file']['tmp_name'])) {

    $file = $_FILES['center_file']['tmp_name'];
    $csvData = [];

    if (($handle = fopen($file, "r")) !== FALSE) {

        while (($row = fgetcsv($handle, 10000, ",")) !== FALSE) {
            $csvData[] = $row;
        }

        fclose($handle);
    }
$url = "https://yugedu.edtechinnovate.in/app/center-master/store";
    $header = array_shift($csvData);

foreach ($csvData as $row) {

    // Row ko associative array me convert karna
    // $postData = array_combine($header, $row);

    // Agar kuch empty hai to "" replace kar den
    $postData['user_type'] = 1;
    $postData['name'] = $row[1];
    $postData['short_name'] = $row[2];
    $postData['contact_person_name'] = $row['3'];
    $postData['email'] = $row[4];
    $postData['contact'] = $row[5];
    $postData['address'] = $row[7]!=''?$row[7]:"Noida";
    $postData['pincode'] = $row[11]!=''?$row[11]:'201301';
    $postData['city'] = $row[8]!=''?$row[8]:"Noida sect 6";
    $postData['district'] = $row[9]!=''?$row[9]:"Noida";
    $postData['state'] = $row[10]!=''?$row[10]:"Uttar Pradesh";
    
    // print_r($postData);die;

    // CURL Init
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Run
    $response = curl_exec($ch);
    // print_r($response);die;
    // Error check
    if (curl_errno($ch)) {
        echo "CURL Error: " . curl_error($ch) . "\n";
    } else {
        echo "Response for " . $postData['Code'] . " => " . $response . "\n\n";
    }

    curl_close($ch);
}
    
} else {
    echo "No CSV file received";
}
