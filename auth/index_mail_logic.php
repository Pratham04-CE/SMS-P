<?php
// auth/index_mail_logic.php

require_once __DIR__ . '/../vendor/PHPMailer/Exception.php';
require_once __DIR__ . '/../vendor/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../vendor/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendVerificationMail($email, $name, $otp) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp-relay.brevo.com'; // Brevo SMTP Host
        $mail->SMTPAuth   = true;
        // API Key ko Password ki tarah use karein
        $mail->Username   = 'studentmanagementsystem04@gmail.com'; 
        $mail->Password = getenv('BREVO_API_KEY');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587; // Brevo ke liye standard port

        $mail->setFrom('studentmanagementsystem04@gmail.com', 'SMS Portal');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Login Verification Code';
        $mail->Body    = "<h3>Hello $name,</h3><p>Your verification code for login is: <b>$otp</b></p>";

        return $mail->send();
    } catch (Exception $e) {
        error_log("Brevo SMTP Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>