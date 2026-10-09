<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//require '../../vendor/autoload.php';

## Database configuration
include '../../includes/db-config.php';
session_start();

$stepsLog = "";
$data_field = file_get_contents('php://input'); // by this we get raw data
$data_field = json_decode($data_field,true);
$stepsLog .= date(DATE_ATOM) . " :: input data => " . json_encode($data_field) . "\n\n"; 
$userList = $data_field['user_list'];
$userType = $data_field['user'];

$redis = new Predis\Client();

try {
    $stepsLog .= date(DATE_ATOM) . " :: Starting createQueueData() \n";
    createQueueData($userList, $userType);
    
    $stepsLog .= date(DATE_ATOM) . " :: Starting createWorker() \n";
    createWorker();
} catch (Exception $e) {
    $stepsLog .=  date(DATE_ATOM) . " :: error => " . $e->getMessage() . "\n\n";
} finally {
    saveLog();
}

function createQueueData($userList,$userType) {

    global $conn, $redis , $stepsLog;
    try {
        if (!$redis) {
            throw new Exception("Redis connection error.");
        }
        $table = ($userType == 'student') ? "Students" : "Users"; 
        $getUserEmailQuery = "SELECT Email FROM $table WHERE ID IN (". implode(',',$userList) .") AND Email IS NOT NULL";
        $getUserEmail = $conn->query($getUserEmailQuery);
        $stepsLog .= date(DATE_ATOM) . " :: userEmailQuery => $getUserEmailQuery  \n\n";
        $userEmailList = mysqli_fetch_all($getUserEmail,MYSQLI_ASSOC);
        $userEmailList = array_column($userEmailList,'Email');
        $userEmailList = array_filter($userEmailList);
        $stepsLog .= date(DATE_ATOM) . " :: userEmailList => " . json_encode($userEmailList) . "\n\n";
        if (!empty($userEmailList)) {
            $emailBatch = [];
            $batchCount = 0;
            foreach ($userEmailList as $email) {
                $emailBatch[] = json_encode(['email' => trim($email), 'message' => 'Your email content']);
                $batchCount++;
                if ($batchCount >= 100) {
                    pushDataInRadis($emailBatch); 
                    $emailBatch = [];       
                    $batchCount = 0;
                }
            }   
            if (!empty($emailBatch)) {
                pushDataInRadis($emailBatch);
            }
        }
        
    } catch (Exception $e) {
        $stepsLog .= date(DATE_ATOM) . " :: Exception  => " . $e->getMessage() . "  \n\n";
    }
}

function pushDataInRadis ($emailBatch) {
    global $redis;
    $redis->pipeline(function($pipe) use ($emailBatch) {
        foreach ($emailBatch as $emailData) {
            $pipe->rpush('email_queue', $emailData);
        }
    });
}

function createWorker() {
    
    global $redis , $stepsLog;
    $stepsLog .= date(DATE_ATOM) ." :: create worker method start to execute \n\n";
    if (!$redis) {
        $stepsLog .= date(DATE_ATOM) . " :: Redis connection error. Exiting worker. \n\n";
        return;
    }
    $emptyCount = 0;
    $maxEmptyChecks = 5;
    while (true) {
        $emailData = $redis->rpop('email_queue'); // Pop an email job from queue
        if ($emailData) {
            $stepsLog .= date(DATE_ATOM) ." :: Job run for => " . $emailData. "\n\n";
            $emailData = json_decode($emailData, true);
            //sendMail($emailData['message'],'EDTech',$emailData['email'],"Notification from Glocal University");
            $emptyCount = 0;
        } else {
            $stepsLog .= date(DATE_ATOM) . " :: Queue is empty.Stopping the worker. \n\n";
            $emptyCount++;
            if ($emptyCount >= $maxEmptyChecks) {
                $stepsLog .= date(DATE_ATOM) . " :: Stopping worker after multiple empty checks.\n\n";
                break;
            }
            sleep(5);
        }
    }
}

function sendMail($message,$sender_name,$receiver_email,$subject) {

    global $stepsLog;

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'no-reply@edtechinnovate.com';
        $mail->Password   = 'qftsisgdjjafqsvi';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('noreply@yourdomain.com',$sender_name);
        $mail->addAddress($receiver_email);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = strip_tags($message);

        $mail->send();

        $stepsLog .= date(DATE_ATOM) . " :: Mail send successfully \n\n";
    } catch (Exception $e) {
        $stepsLog .= date(DATE_ATOM) . " :: Failed to send email. Mailer Error: ". $e->getMessage() ." \n\n";
    }
}

function saveLog() {
    global $stepsLog;
    $stepsLog .= " ============ End Of Script ================== \n\n";
    $pdf_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/notification_log/';
    $fh = fopen($pdf_dir . 'mailQueueAndJob_' . date('y-m-d') . '.log' , 'a');
    fwrite($fh,$stepsLog);
    fclose($fh);
    echo json_encode(['status'=>200,'message'=>"execute this job and queue"]);
    exit;
}

?>
