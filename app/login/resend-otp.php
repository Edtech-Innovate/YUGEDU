<?php

session_start();

header('Content-Type: application/json');

require '../../includes/config.php'; 

require '../../vendor/autoload.php';   // IMPORTANT

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


try{

if(!isset($_SESSION['temp_user'])){

echo json_encode([
"status"=>401,
"message"=>"Session expired. Please login again"
]);
exit();

}

$user_details = $_SESSION['temp_user'];

/* GENERATE OTP */

$otp = rand(100000,999999);

$_SESSION['login_otp'] = $otp;
$_SESSION['otp_expire'] = time() + 300;


/* SEND EMAIL */

$mail = new PHPMailer(true);

$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'noreply@edtechinnovate.com';
$mail->Password = 'kaskkqzryhruwmyc';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;

$mail->setFrom('noreply@edtechinnovate.com','ERP Login');
$mail->addAddress($user_details['Email']);

$mail->isHTML(true);
$mail->Subject = "Resend Login OTP";

$mail->Body = "
<h2>ERP Login OTP</h2>
<p>Your new OTP is:</p>
<h1>$otp</h1>
<p>This OTP will expire in 5 minutes.</p>
";

$mail->send();

echo json_encode([
"status"=>200,
"message"=>"OTP resent successfully"
]);

}catch(Exception $e){

echo json_encode([
"status"=>500,
"message"=>$e->getMessage()
]);

}