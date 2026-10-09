<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/autoload.php';

function sendCenterCredentialsMail($email, $name, $centerCode, $password)
{
    if (empty($email) || empty($password)) {
        error_log("Mail skipped: email or password missing");
        return false;
    }

    $loginUrl = "https://wilpvocmdu.edtechinnovate.in/";

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'no-reply@edtechinnovate.com';
        $mail->Password   = 'qftsisgdjjafqsvi';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->SMTPDebug = 0;

        $mail->setFrom('no-reply@edtechinnovate.com', 'MDU Team');
        $mail->addAddress($email, $name);
        $mail->addReplyTo('no-reply@edtechinnovate.com', 'MDU Support');

        $mail->isHTML(true);
        $mail->Subject = "Welcome to MDU  Login Credentials";

        /* ================================
           EMAIL UI (MATCHING YOUR SAMPLE)
        ================================ */
        $mail->Body = '
        <div style="font-family: Arial, sans-serif; padding:20px; background:#f9f9f9;">
            <div style="max-width:600px; margin:auto; background:#ffffff; border-radius:8px; padding:30px; box-shadow:0 2px 6px rgba(0,0,0,0.1);">
                
                <h2 style="color:#333;">Hi ' . htmlspecialchars($name) . ', 👋</h2>

                <p style="color:#555;">
                    Congratulations! Your center has been <strong>approved</strong>.
                    Below are your login credentials:
                </p>

                <ul style="line-height:1.8; color:#555;">
                    <li><strong>Center Code:</strong> ' . htmlspecialchars($centerCode) . '</li>
                    <li><strong>Email:</strong> ' . htmlspecialchars($email) . '</li>
                    <li><strong>Password:</strong> ' . htmlspecialchars($password) . '</li>
                </ul>

                <p style="margin-top:20px;">
                    Click the button below to access your dashboard:
                </p>

                <a href="' . $loginUrl . '" 
                   style="display:inline-block; background:#28a745; color:#fff; padding:12px 24px; 
                          border-radius:5px; text-decoration:none; font-weight:bold;">
                    Login Now
                </a>

                <p style="margin-top:20px; font-size:13px; color:#777;">
                    ⚠ Please change your password after first login.
                </p>

                <br>
                <p>Regards,<br><strong>MDU Team</strong></p>
            </div>
        </div>';

        $mail->AltBody = "Hi $name,
Your center has been approved.

Center Code: $centerCode
Email: $email
Password: $password

Login: $loginUrl
Please change your password after login.";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("MAIL ERROR: " . $mail->ErrorInfo);
        return false;
    }
}
