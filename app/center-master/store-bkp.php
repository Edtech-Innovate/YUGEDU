<?php
ini_set('error_reporting', E_ALL );
   ini_set('display_errors', 1 );
  if(isset($_POST['name']) && isset($_POST['email'])){
    require '../../includes/db-config.php';
    session_start();
    
    $user_type =  isset($_POST['user_type']) ? intval($_POST['user_type']) : 1;
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $short_name = mysqli_real_escape_string($conn, $_POST['short_name']);
    $contact_person_name = mysqli_real_escape_string($conn, $_POST['contact_person_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $alternate_contact = mysqli_real_escape_string($conn, $_POST['alternate_contact']??"");
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $pincode = mysqli_real_escape_string($conn, $_POST['pincode']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $district = mysqli_real_escape_string($conn, $_POST['district']);
    $state = mysqli_real_escape_string($conn, $_POST['state']);
    $role = $_SESSION['Role']??"Administrator";
    $_SESSION['Role'] = $role;
  
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
      echo json_encode(['status'=>400, "message"=>"Invalid email!"]);
      exit();
    }

    if(empty($name) || empty($email) || empty($contact) || empty($short_name) || empty($contact_person_name) || empty($address)){
      echo json_encode(['status'=>403, 'message'=>'All fields are mandatory!']);
      exit();
    }

    $_SESSION['university_id'] = 76;
    $_SESSION['ID'] = 1;

    // Check across the whole Users table globally
    $check_has_unique_center_code = $conn->query("SELECT Center_Suffix FROM Universities WHERE ID = ".$_SESSION['university_id']." AND Has_Unique_Center = 1");
    
    if($check_has_unique_center_code->num_rows > 0){
      $center_suffix = mysqli_fetch_assoc($check_has_unique_center_code);
      $center_suffix = $center_suffix['Center_Suffix'];
      
      // Globally check whole Users table for this suffix pattern
      $last_inserted_center_code = $conn->query("SELECT Code FROM Users WHERE Code LIKE '$center_suffix%' AND Role = 'Center' AND Is_Unique = 1 ORDER BY Code DESC LIMIT 1");
      
      
      if($last_inserted_center_code->num_rows == 0){
        $code = $center_suffix . sprintf("%'.05d\n", 1);
      } else {
        //print_r($code);die;
        $last_inserted_center_code = mysqli_fetch_assoc($last_inserted_center_code);
        $last_inserted_center_code = $last_inserted_center_code['Code'];
        $last_inserted_center_code = str_replace($center_suffix, '', $last_inserted_center_code);
        $last_inserted_center_code = (int)$last_inserted_center_code + 1;
        $code = $center_suffix . sprintf("%'.05d\n", $last_inserted_center_code);
      }
      $is_unique = 1;
    } else {
      // Globally check whole Users table for non-unique / standard format
      $last_inserted_center_code = $conn->query("SELECT Code FROM Users WHERE Role = 'Center' AND Is_Unique = 1 AND B2B_Partner = 0 ORDER BY Code DESC LIMIT 1");
      
      if($last_inserted_center_code->num_rows == 0){
        $code = 4000;
      } else {
        $default_center_code_suffix = 'CC';
        $last_inserted_center_code = mysqli_fetch_assoc($last_inserted_center_code);        
        $last_inserted_center_code = str_replace($default_center_code_suffix ?? '', '', $last_inserted_center_code['Code']);
        $last_inserted_center_code = intval($last_inserted_center_code) + 1;
        $code = ($default_center_code_suffix ?? '') . sprintf("%'.05d\n", $last_inserted_center_code);
       
        
      }
      $is_unique = 0;
    }
    
    $code = trim($code);
   // print_r($code);die;
  
    if(isset($_FILES["photo"]["name"]) && $_FILES["photo"]["name"] != ''){
      $temp = explode(".", $_FILES["photo"]["name"]);
      $filename = round(microtime(true)) . '.' . end($temp);
      $tempname = $_FILES["photo"]["tmp_name"];
      $folder = "../../assets/img/centers/" . $filename; 
      if(move_uploaded_file($tempname, $folder)){ 
        $filename = "/assets/img/centers/" . $filename;
      } else {
        echo json_encode(['status'=>400, 'message'=>'Unable to save photo!']);
        exit();
      }
    } else {
      $filename = "/assets/img/default-user.png";
    }

    $add = $conn->query("INSERT INTO `Users`(`Name`, `Short_Name`, `Contact_Name`, `Code`, `Email`, `Mobile`, `Alternate_Mobile`, `Address`, `Pincode`, `City`, `District`, `State`, `Password`, `Photo`, `Role`, `Designation`, `Created_By`, `B2B_Partner`, `vertical`, `Is_Unique`) VALUES ('$name', '$short_name', '$contact_person_name', '$code', '$email', '$contact', '$alternate_contact', '$address', '$pincode', '$city', '$district', '$state', AES_ENCRYPT('$contact', '60ZpqkOnqn0UQQ2MYTlJ'), '$filename', 'Center', 'Center', " . $_SESSION['ID'] . ", '$user_type', 1, $is_unique)");
    
    if ($add) {
      if (!empty($_POST['university_id'])) {
        $centerId = $conn->insert_id;
        addUniversityUser($centerId, $_POST['university_id']);
      }
      echo json_encode(['status' => 200, 'message' => 'Center added successfully!']);
    } else {
      echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
    }
  }

  function addUniversityUser($centerId, $universityId) {
    global $conn;
    $sql = "INSERT INTO University_User (University_ID, User_ID) VALUES ('$universityId', '$centerId')";
    return $conn->query($sql);
  }
?>