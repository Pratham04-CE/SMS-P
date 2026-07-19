<?php
// --- UPDATED PATH (Without src folder) ---
$base_path = dirname(__DIR__) . '/vendor/PHPMailer/';

// Verify PHPMailer files location and load them correctly
if (file_exists($base_path . 'src/Exception.php')) {
    $final_path = $base_path . 'src/';
} else {
    $final_path = $base_path;
}

require_once $final_path . 'Exception.php';
require_once $final_path . 'PHPMailer.php';
require_once $final_path . 'SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// Send profile OTP verification email
function sendProfileOTP($email, $name, $otp, $field) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'studentmanagementsystem04@gmail.com'; 
        $mail->Password = 'ljiawcqbthqwyuzp'; 
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('studentmanagementsystem04@gmail.com', 'SMS Security');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = "Verification Code for $field Update";
        
        $mail->Body = "<h3>Security Verification</h3>
                       <p>Hello $name,</p>
                       <p>Your OTP to update your <b>$field</b> is: <b style='font-size:20px; color:#0ea5e9;'>$otp</b></p>";

        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
}
?>