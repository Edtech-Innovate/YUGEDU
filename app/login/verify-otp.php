<?php

session_start();
 require '../../includes/db-config.php';
//  echo('<pre>');print_r($_POST);
if(!isset($_SESSION['login_otp'])){
    echo json_encode(['status'=>400,'message'=>'Session expired']);
    exit;
}

$otp = $_POST['otp'];
if(time() > $_SESSION['otp_expire']){

echo json_encode([
"status"=>400,
"message"=>"OTP expired"
]);

exit();

}
if(time() > $_SESSION['otp_expire']){
    echo json_encode(['status'=>400,'message'=>'OTP expired']);
    exit;
}

if($otp != $_SESSION['login_otp']){
    echo json_encode(['status'=>400,'message'=>'Invalid OTP']);
    exit;
}

/* LOGIN SUCCESS */

$user_details = $_SESSION['temp_user'];

foreach ($user_details as $key => $user_detail) {
    $_SESSION[$key] = $user_detail;
}

/* REMOVE TEMP DATA */

unset($_SESSION['login_otp']);
unset($_SESSION['otp_expire']);
unset($_SESSION['temp_user']);

/* REDIRECT */
$isAuthFeePaid = true; // default allow

$Code = $_SESSION['Code'] ?? ''; // ✅ IMPORTANT: no $

if ($_SESSION['Role'] === 'Center' && $Code !== 'MDUBVOC0003') {

	$isAuthFeePaid = false;

	$authCheck = $conn->query("
        SELECT ID 
        FROM Wallets 
        WHERE Added_By = {$_SESSION['ID']}
          AND Payment_For = 2
          AND Status = 1
        LIMIT 1
    ");

	if ($authCheck && $authCheck->num_rows > 0) {
		$isAuthFeePaid = true;
	}
}
	//end of center check for login
	if ($_SESSION['Role'] != 'Administrator') {
		$all_universities = array();
		$counter = 1;
		$universities = $conn->query("SELECT University_User.University_ID, Universities.Is_B2C, CONCAT(Universities.Short_Name, ' (', Universities.Vertical, ')') AS Name, Universities.Has_Unique_Center, Universities.Has_Unique_StudentID, Universities.Has_LMS, Universities.Logo FROM University_User LEFT JOIN Universities ON University_User.University_ID = Universities.ID WHERE `User_ID` = " . $_SESSION['ID']);
		while ($dt = $universities->fetch_assoc()) {
			$all_universities[] = $dt['University_ID'];
			if ($counter == 1) {
				$_SESSION['university_id'] = $dt['University_ID'];
				$_SESSION['university_name'] = $dt['Name'];
				$_SESSION['unique_center'] = $dt['Has_Unique_Center'];
				$_SESSION['student_id'] = $dt['Has_Unique_StudentID'];
				$_SESSION['university_logo'] = $dt['Logo'];
				$_SESSION['has_lms'] = $dt['Has_LMS'];
				$_SESSION['crm'] = $dt['Is_B2C'];
			}
			$counter++;
		}

		$_SESSION['Alloted_Universities'] = array_filter(array_unique($all_universities));
		$_SESSION['all_university_ids'] = implode(',', array_filter(array_unique($all_universities)));
	}
	// echo $_SESSION['Role']; die;

	if (!array_key_exists('university_id', $_SESSION) && !in_array($_SESSION['Role'], ['Administrator'])) {
		session_destroy();
		exit(json_encode(['status' => 400, 'message' => 'Please allot University!']));
	}

	// University Query
	$query = $_SESSION['Role'] != "Administrator" ? " AND University_ID = " . $_SESSION['university_id'] : "";
	$_SESSION['UniversityQuery'] = $query;

	$setting_names = $conn->query("SELECT * FROM Custom_User_Names");
	while ($sn = $setting_names->fetch_assoc()) {
		$_SESSION[$sn['Name']] = $sn['Rename_As_Singular'];
		$_SESSION[$sn['Name'] . '-Outer'] = $sn['Rename_As'];
	}


	// RolesQuery
	$role_query = " AND {{ table }}.{{ column }} = " . $_SESSION['ID'];

	if ($_SESSION['Role'] === 'Administrator' || $_SESSION['Role'] == 'Operations' || $_SESSION['Role'] == 'Accountant') {
		$role_query = " AND {{ table }}.{{ column }} IS NOT NULL";
	} elseif ($_SESSION['Role'] === 'University Head') { // Vertical Head //University Head
		$center_list = array($_SESSION['ID']);
		$counsellorIds = array();
		$subCounsellorIds = array();
		$centersIds = array();

		$counsellors = $conn->query("SELECT `User_ID` FROM University_User WHERE Reporting = " . $_SESSION['ID'] . " AND University_ID IN (" . $_SESSION['all_university_ids'] . ")");
		while ($counsellor = $counsellors->fetch_assoc()) {
			$center_list[] = $counsellor['User_ID'];
			$counsellorIds[] = $counsellor['User_ID'];
		}

		if (!empty($counsellorIds)) {
			$sub_counsellors = $conn->query("SELECT `User_ID` FROM University_User WHERE Reporting IN (" . implode(",", $counsellorIds) . ") AND University_ID IN (" . $_SESSION['all_university_ids'] . ")");
			while ($sub_counsellor = $sub_counsellors->fetch_assoc()) {
				$center_list[] = $sub_counsellor['User_ID'];
				$subCounsellorIds[] = $sub_counsellor['User_ID'];
			}

			$centers = $conn->query("SELECT `User_ID` FROM University_User WHERE Reporting IN (" . implode(",", $center_list) . ") AND University_ID IN (" . $_SESSION['all_university_ids'] . ")");
			while ($center = $centers->fetch_assoc()) {
				$center_list[] = $center['User_ID'];
				$centersIds[] = $center['User_ID'];
			}

			if (!empty($centersIds)) {
				$sub_centers = $conn->query("SELECT `User_ID` FROM University_User WHERE Reporting IN (" . implode(",", $center_list) . ") AND University_ID IN (" . $_SESSION['all_university_ids'] . ")");
				while ($sub_center = $sub_centers->fetch_assoc()) {
					$center_list[] = $sub_center['User_ID'];
				}
			}
		}

		// echo "<pre>";
		// print_r(implode(',', array_unique($center_list))); die;
		$role_query = " AND {{ table }}.{{ column }} IN (" . implode(",", array_unique($center_list)) . ")";
	} elseif ($_SESSION['Role'] == 'Counsellor') {
		$center_list = array($_SESSION['ID']);
		$counsellorIds = array($_SESSION['ID']);
		$subCounsellorIds = array();
		$centersIds = array();


		if (!empty($counsellorIds)) {
			$sub_counsellors = $conn->query("SELECT `User_ID` FROM University_User WHERE Reporting IN (" . implode(",", $counsellorIds) . ") AND University_ID = " . $_SESSION['university_id']);
			while ($sub_counsellor = $sub_counsellors->fetch_assoc()) {
				$center_list[] = $sub_counsellor['User_ID'];
				$subCounsellorIds[] = $sub_counsellor['User_ID'];
			}



			$centers = $conn->query("SELECT `User_ID` FROM University_User WHERE Reporting IN (" . implode(",", $center_list) . ") AND University_ID = " . $_SESSION['university_id']);
			while ($center = $centers->fetch_assoc()) {
				$center_list[] = $center['User_ID'];
				$centersIds[] = $center['User_ID'];
			}



			if (!empty($centersIds)) {
				$sub_centers = $conn->query("SELECT `User_ID` FROM University_User WHERE Reporting IN (" . implode(",", $center_list) . ") AND University_ID = " . $_SESSION['university_id']);
				while ($sub_center = $sub_centers->fetch_assoc()) {
					$center_list[] = $sub_center['User_ID'];
				}
			}
		}
		// echo "<pre>";
		// print_r(implode(',', $center_list)); die;
		$role_query = " AND {{ table }}.{{ column }} IN (" . implode(",", $center_list) . ")";
	} elseif ($_SESSION['Role'] === 'Sub-Counsellor') {
		$center_list = array($_SESSION['ID']);
		$subCounsellorIds = array($_SESSION['ID']);

		$centers = $conn->query("SELECT `User_ID` FROM University_User WHERE Reporting IN (" . implode(",", $subCounsellorIds) . ") AND University_ID = " . $_SESSION['university_id']);
		while ($center = $centers->fetch_assoc()) {
			$center_list[] = $center['User_ID'];
			$centersIds[] = $center['User_ID'];
		}

		if (!empty($centersIds)) {

			$sub_centers = $conn->query("SELECT `User_ID` FROM University_User WHERE Reporting IN (" . implode(",", $centersIds) . ") AND University_ID = " . $_SESSION['university_id']);
			while ($sub_center = $sub_centers->fetch_assoc()) {
				$center_list[] = $sub_center['User_ID'];
			}
		}
		// echo "<pre>"; print_r($center_list);die;


		$role_query = " AND {{ table }}.{{ column }} IN (" . implode(",", $center_list) . ")";
	} elseif ($_SESSION['Role'] === 'Center') {
		$center_list = array($_SESSION['ID']);
		$centersIds = array($_SESSION['ID']);

		$sub_centers = $conn->query("SELECT `User_ID` FROM University_User WHERE Reporting IN (" . implode(",", $centersIds) . ") AND University_ID = " . $_SESSION['university_id']);
		while ($sub_center = $sub_centers->fetch_assoc()) {
			$center_list[] = $sub_center['User_ID'];
		}

		$role_query = " AND {{ table }}.{{ column }} IN (" . implode(",", $center_list) . ")";
	} elseif ($_SESSION['Role'] === 'Sub-Center') {
		$role_query = " AND {{ table }}.{{ column }} = '" . $_SESSION['ID'] . "'";
	} else {
		$role_query = " AND {{ table }}.{{ column }} = '" . $_SESSION['ID'] . "'";
	}

	// Payment Gateway
	if ($_SESSION['Role'] != 'Administrator') {
		$gateway = $conn->query("SELECT * FROM Payment_Gateways WHERE University_ID = " . $_SESSION['university_id']);
		if ($gateway->num_rows > 0) {
			$gateway = $gateway->fetch_assoc();
			$_SESSION['gateway'] = $gateway['Type'];
			$_SESSION['access_key'] = $gateway['Access_Key'];
			$_SESSION['secret_key'] = $gateway['Secret_Key'];
		}
	}

	$_SESSION['RoleQuery'] = $role_query;



	if ($_SESSION['Role'] === 'Center') {

		if ($isAuthFeePaid) {
			echo json_encode([
				'status' => 200,
				'message' => 'Welcome ' . $user_details['Name'],
				'url' => '/admissions/applications'
			]);
		} else {
			echo json_encode([
				'status' => 200,
				'message' => 'Please pay Authorization Fee to continue',
				'url' => '/dashboard'
					// 'url' => '/dashboards/authorization'

			]);
		}
	} elseif ($_SESSION['Role'] === 'Academic Head') {

		echo json_encode([
			'status' => 200,
			'message' => 'Welcome ' . $user_details['Name'],
			'url' => '/academics/departments'
		]);
	} else {

		echo json_encode([
			'status' => 200,
			'message' => 'Welcome ' . $user_details['Name'],
			'url' => '/admissions/applications'
		]);
	}