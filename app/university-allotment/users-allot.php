<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
  require '../../includes/db-config.php';
  session_start();

    $allUsers = $conn->query("select * from Users");
    echo "<pre>";
   while($users = $allUsers->fetch_assoc()){
       
       print_r($users);
   }
    

  

