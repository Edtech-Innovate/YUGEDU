<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/autoload.php';

function sendCenterCredentialsMail($email, $name, $centerCode, $password)
{
    // ✅ Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log("MAIL ERROR: Invalid email - $email");
        return false;
    }

    if (empty($password)) {
        error_log("MAIL ERROR: Password missing");
        return false;
    }

    $loginUrl = "https://wilpvocmdu.edtechinnovate.in/";

    // 🔁 Retry logic (2 attempts)
    for ($attempt = 1; $attempt <= 2; $attempt++) {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'no-reply@edtechinnovate.com';
            $mail->Password   = 'qftsisgdjjafqsvi';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // ✅ IMPORTANT STABILITY SETTINGS
            $mail->Timeout       = 30;
            $mail->SMTPKeepAlive = true;
            $mail->CharSet       = 'UTF-8';
            $mail->Encoding      = 'base64';
            $mail->SMTPDebug     = 0;

            $mail->setFrom('no-reply@edtechinnovate.com', 'MDU Team');
            $mail->addAddress($email, $name ?: 'Center');
            $mail->addReplyTo('no-reply@edtechinnovate.com', 'MDU Support');

            $mail->isHTML(true);
            $mail->Subject = "Welcome to MDU – Login Credentials";

            $mail->Body = '
            <div style="font-family:Arial,sans-serif;padding:20px;background:#f9f9f9;">
                <div style="max-width:600px;margin:auto;background:#fff;padding:30px;border-radius:8px;">
                    <h2>Hi ' . htmlspecialchars($name) . ', 👋</h2>
                    <p>Your center has been <strong>approved</strong>.</p>
                    <ul>
                        <li><b>Center Code:</b> ' . htmlspecialchars($centerCode) . '</li>
                        <li><b>Email:</b> ' . htmlspecialchars($email) . '</li>
                        <li><b>Password:</b> ' . htmlspecialchars($password) . '</li>
                    </ul>
                    <a href="' . $loginUrl . '" style="background:#28a745;color:#fff;padding:12px 20px;border-radius:5px;text-decoration:none;">
                        Login Now
                    </a>
                    <p style="font-size:13px;color:#777;margin-top:15px;">
                        Please change your password after first login.
                    </p>
                    <p>Regards,<br><strong>MDU Team</strong></p>
                </div>
            </div>';

            $mail->AltBody = "Center Code: $centerCode | Password: $password | Login: $loginUrl";

            $mail->send();
            $mail->smtpClose();

            return true; // ✅ SUCCESS

        } catch (Exception $e) {
            error_log("MAIL ATTEMPT $attempt FAILED: " . $mail->ErrorInfo);
            sleep(1); // wait before retry
        }
    }

    return false; // ❌ Failed after retries
}
