<?php
$root = $_SERVER['DOCUMENT_ROOT'] . '/Pratham/sms/'; 
require $root . 'vendor/PHPMailer/Exception.php';
require $root . 'vendor/PHPMailer/PHPMailer.php';
require $root . 'vendor/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendFacultyOTP($email, $name, $otp) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'studentmanagementsystem04@gmail.com'; 
        $mail->Password = 'ljiawcqbthqwyuzp'; // Aapka App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('studentmanagementsystem04@gmail.com', 'SMS Faculty Portal');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Faculty Login Verification Code';
        $mail->Body    = "<h3>Hello $name,</h3><p>Your OTP for Faculty Login is: <b>$otp</b></p>";

        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
}
?>