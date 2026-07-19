<?php
// register_mail_logic.php

// Function ko yahan define karein ya include kar lein
function sendRegistrationMail($email, $name, $otp) {
    $apiKey = getenv('BREVO_API_KEY');
    $url = 'https://api.brevo.com/v3/smtp/email';

    $data = [
        'sender' => ['name' => 'SMS Portal', 'email' => 'studentmanagementsystem04@gmail.com'],
        'to' => [['email' => $email, 'name' => $name]],
        'subject' => 'Verify your Account',
        'htmlContent' => "<h3>Welcome $name,</h3><p>Your verification code is: <b>$otp</b></p>"
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'api-key: ' . $apiKey
    ]);

    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($httpCode === 201);
}

// Apne existing registration logic mein ise call karein:
// if(sendRegistrationMail($user_email, $user_name, $otp)) { ... }
?>