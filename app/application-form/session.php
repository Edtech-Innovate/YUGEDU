<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

  if(isset($_GET['university_id'])){
    require '../../includes/db-config.php';
    session_start();

    $university_id = intval($_GET['university_id']);
    $center = intval($_GET['center']);
    if(!empty($_GET['form'])){
      $status_query = "";
    }else{
      $status_query = " AND Status = 1";
    }

    $sessions = $conn->query("SELECT ID, Name, Current_Status FROM Admission_Sessions WHERE University_ID = $university_id $status_query");
    if($sessions->num_rows==0){
      echo '<option value="">Please add admission session</option>';
      exit();
    }
    // print_r("SELECT Admission_Session_ID FROM wilp_mdu.User_Sub_Courses where University_ID=$university_id and User_ID=$center");die;
    $allotedSessions = $conn->query("SELECT Admission_Session_ID FROM User_Sub_Courses where University_ID=$university_id and User_ID=$center");
    while($session = $allotedSessions->fetch_assoc()){
        
        $allsessions[] = $session['Admission_Session_ID'];
    }
    // print_r(array_unique($allsessions));die;
    while($session = $sessions->fetch_assoc()){ 
        if(!in_array($session['ID'],$allsessions) && $university_id!=21){
           
        }
    ?>
      <option value="<?php echo $session['ID'] ?>" <?php print $session['Current_Status']==1 ? 'selected' : '' ?>><?php echo $session['Name'] ?></option>
    <?php }
  }
