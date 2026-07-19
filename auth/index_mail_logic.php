<?php
// auth/index_mail_logic.php

/**
 * Brevo API ke zariye mail bhejne ka function.
 * SMTP ki jagah HTTPS API request ka use kiya hai jo Render free tier par chalti hai.
 */
function sendVerificationMail($email, $name, $otp) {
    // Render environment se API key fetch karein
    $apiKey = getenv('BREVO_API_KEY');
    
    // Agar API key set nahi hai, toh error log karein
    if (!$apiKey) {
        error_log("Brevo API Error: Missing API Key in environment variables.");
        return false;
    }

    $url = 'https://api.brevo.com/v3/smtp/email';

    // API ke liye JSON payload
    $data = [
        'sender' => [
            'name' => 'SMS Portal', 
            'email' => 'studentmanagementsystem04@gmail.com'
        ],
        'to' => [
            ['email' => $email, 'name' => $name]
        ],
        'subject' => 'Login Verification Code',
        'htmlContent' => "
            <div style='font-family: Arial, sans-serif;'>
                <h2>Hello $name,</h2>
                <p>Your verification code for the Student Management System is:</p>
                <h1 style='color: #4CAF50;'>$otp</h1>
                <p>Please use this code to complete your login.</p>
                <br>
                <p>Regards,<br>SMS Portal Team</p>
            </div>"
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'api-key: ' . $apiKey
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);

    // Agar httpCode 201 hai, toh mail successfully accept ho gaya
    if ($httpCode === 201) {
        return true;
    } else {
        error_log("Brevo API Error: HTTP $httpCode - Response: $response - cURL Error: $error");
        return false;
    }
}
?>