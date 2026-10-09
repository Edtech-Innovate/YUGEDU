<?php
  if(isset($_POST['name']) && isset($_POST['email']) && isset($_POST['id'])){
    require '../../includes/db-config.php';
    session_start();

    $id = intval($_POST['id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $vertical = mysqli_real_escape_string($conn, $_POST['vertical'] ?? "");
    $role = $_SESSION['Role'];
    
    if($role != 'Administrator')
    {
        $vertical = $_SESSION['vertical'];
    }

    // Check if required fields are empty
    if(empty($name) || empty($email) || empty($id)){
      echo json_encode(['status' => 403, 'message' => 'All fields are mandatory!']);
      exit();
    }

    // Optional: Validate email format
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
      echo json_encode(['status' => 403, 'message' => 'Invalid email format!']);
      exit();
    }

    // Update query including the email field
    $update = $conn->query("UPDATE `Users` SET `vertical` = $vertical, `Name` = '$name', `Email` = '$email' WHERE ID = $id");
    
    if($update){
      echo json_encode(['status' => 200, 'message' => 'Sub-Center updated successfully!']);
    }else{
      echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
    }
  }
?>