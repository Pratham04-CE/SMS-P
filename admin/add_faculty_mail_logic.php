
<?php
// Sahi path set kiya hai aapke folder structure ke hisaab se
require '../vendor/PHPMailer/Exception.php';
require '../vendor/PHPMailer/PHPMailer.php';
require '../vendor/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendFacultyWelcomeMail($email, $name, $id, $pass) {
    // ... baaki ka purana code ...
    $mail = new PHPMailer(true);
    try {
        // SMTP Settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'studentmanagementsystem04@gmail.com'; // Aapka Gmail
        $mail->Password = 'ljiawcqbthqwyuzp';              // Aapka App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('studentmanagementsystem04@gmail.com', 'SMS Admin');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Faculty Registration - Login Credentials';
        $mail->Body    = "<h3>Welcome Prof. $name,</h3>
                          <p>Your account has been successfully created on the Student Management System.</p>
                          <p><b>Login ID:</b> $id <br>
                             <b>Temporary Password:</b> $pass</p>
                          <p>Please login and change your password from your dashboard.</p>";

        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
}
?>