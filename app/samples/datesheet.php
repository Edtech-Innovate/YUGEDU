<?php
  $delimiter = ","; 
  $filename = "Date-Sheet Sample.csv"; 
   
  // Create a file pointer 
  $f = fopen('php://memory', 'w'); 

  $fields = array('Course','Sub-Course','Exam Session', 'Subject Code', 'Exam Date (dd-mm-yyyy)', 'Start Time', 'End Time','Duration');
  fputcsv($f, $fields, $delimiter);

  fseek($f, 0); 
  
  // Set headers to download file rather than displayed 
  header('Content-Type: text/csv'); 
  header('Content-Disposition: attachment; filename="' . $filename . '";'); 
    
  //output all remaining data on a file pointer 
  fpassthru($f);