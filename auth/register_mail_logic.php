<?php
// auth/register_mail_logic.php
require __DIR__ . '/../vendor/PHPMailer/Exception.php';
require __DIR__ . '/../vendor/PHPMailer/PHPMailer.php';
require __DIR__ . '/../vendor/PHPMailer/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


function sendVerificationMail($email, $name, $otp) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'studentmanagementsystem04@gmail.com'; // Your Email
        $mail->Password = 'ljiawcqbthqwyuzp';              // Your App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('studentmanagementsystem04@gmail.com', 'SMS Portal');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Student Account';
        $mail->Body    = "<h3>Hello $name,</h3><p>Your OTP for account activation is: <b>$otp</b></p>";

        return $mail->send();
    } catch (Exception $e) { return false; }
}
?>