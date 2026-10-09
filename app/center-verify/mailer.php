<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/autoload.php'; // Make sure PHPMailer is installed via Composer

function sendMails($to, $toName, $submissionDate) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'no-reply@edtechinnovate.com'; // Your SMTP email
        $mail->Password   = 'qftsisgdjjafqsvi';            // SMTP password or app password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('no-reply@edtechinnovate.com', 'EdTech Innovate');
        $mail->addAddress($to, $toName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Application Received Successfully';

        $mail->Body = "
        <div style='font-family: Arial, sans-serif; color: #333; line-height: 1.6; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;'>
            <h2 style='color: #0d6efd;'>Hello $toName,</h2>
            <p>We have received your application successfully. Our team will review your application and documents carefully.</p>
            <p>After review and approval, you will receive another email with your login credentials and further instructions.</p>
            <p><strong>Submission Date:</strong> $submissionDate</p>
            <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
            <p style='text-align: center; color: #555;'>Thank you,<br><strong>EdTech Innovate Team</strong></p>
        </div>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Mail error: " . $mail->ErrorInfo);
        return false;
    }
}
