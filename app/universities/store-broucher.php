<?php
  if(isset($_POST['id']) && isset($_FILES['broucher'])){
    require '../../includes/db-config.php';

    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $temp = explode(".", $_FILES["broucher"]["name"]);
      $filename = round(microtime(true)) . '.' . end($temp);
      $tempname = $_FILES["broucher"]["tmp_name"];
      $folder = "../../uploads/tmp/".$filename; 
      if(move_uploaded_file($tempname, $folder)){ 
        $filename = "/uploads/tmp/".$filename;
      }else{
        echo json_encode(['status'=>403, 'message'=>'Unable to save broucher!']);
        exit();
      }
      $update = $conn->query("UPDATE `Universities` SET broucher= '$filename' WHERE ID = '".$_POST['id']."'");
    if($update){
      echo json_encode(['status'=>200, 'message'=>'Broucher Uploaded successfully!']);
    }else{
      echo json_encode(['status'=>403, 'message'=>'Unable to Upload Broucher!']);
    }
  }
