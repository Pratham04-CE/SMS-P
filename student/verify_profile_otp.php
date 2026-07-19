<?php
session_start();
include('../config/db_connect.php');

if(isset($_POST['verify_otp'])) {
    if($_POST['otp_input'] == $_SESSION['profile_otp']) {
        $field = $_SESSION['pending_field'];
        $val = $_SESSION['pending_value'];
        $uid = $_SESSION['user_id'];

        $sql = "UPDATE users SET $field = '$val' WHERE id = '$uid'";
        if(mysqli_query($conn, $sql)) {
            unset($_SESSION['profile_otp']);
            echo "<script>alert('Verified & Updated!'); window.location='view_profile.php';</script>";
        }
    } else {
        echo "<script>alert('Invalid OTP! Try again.');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&family=Sora:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-1: #f4efe6;
            --bg-2: #dbe8ff;
            --ink: #1f2937;
            --muted: #5b6475;
            --accent: #0f766e;
            --accent-2: #134e4a;
            --card: rgba(255, 255, 255, 0.86);
            --ring: rgba(15, 118, 110, 0.25);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 20px;
            background:
                radial-gradient(circle at 20% 20%, #fff6cc 0%, transparent 40%),
                radial-gradient(circle at 80% 10%, #c7f9e7 0%, transparent 35%),
                linear-gradient(135deg, var(--bg-1), var(--bg-2));
            font-family: "Space Grotesk", sans-serif;
            color: var(--ink);
        }

        .otp-card {
            width: 100%;
            max-width: 420px;
            background: var(--card);
            border: 1px solid rgba(255, 255, 255, 0.7);
            border-radius: 22px;
            padding: 34px 28px;
            box-shadow: 0 18px 40px rgba(19, 78, 74, 0.18);
            backdrop-filter: blur(8px);
            animation: riseIn 0.7s ease;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            background: #e6fffa;
            color: var(--accent-2);
            margin-bottom: 14px;
        }

        h2 {
            margin: 0;
            font-family: "Sora", sans-serif;
            font-size: 28px;
            line-height: 1.2;
        }

        p {
            margin: 10px 0 24px;
            color: var(--muted);
            font-size: 14px;
        }

        .otp-input {
            width: 100%;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            border-radius: 14px;
            padding: 14px 16px;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 0.35em;
            text-align: center;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }

        .otp-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 4px var(--ring);
        }

        .otp-input::placeholder {
            color: #9ca3af;
            letter-spacing: 0.35em;
        }

        .btn-activate {
            width: 100%;
            margin-top: 16px;
            border: 0;
            border-radius: 14px;
            padding: 13px 16px;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            color: #ffffff;
            font-size: 15px;
            font-weight: 700;
            font-family: "Space Grotesk", sans-serif;
            cursor: pointer;
            transition: transform 0.18s ease, box-shadow 0.18s ease;
            box-shadow: 0 10px 22px rgba(19, 78, 74, 0.28);
        }

        .btn-activate:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 25px rgba(19, 78, 74, 0.32);
        }

        .cancel-link {
            display: block;
            text-align: center;
            margin-top: 14px;
            color: var(--accent-2);
            font-weight: 600;
            text-decoration: none;
        }

        .cancel-link:hover {
            text-decoration: underline;
        }

        @keyframes riseIn {
            from {
                opacity: 0;
                transform: translateY(18px) scale(0.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @media (max-width: 460px) {
            .otp-card {
                padding: 28px 20px;
            }

            h2 {
                font-size: 24px;
            }

            .otp-input {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <form method="POST" class="otp-card" autocomplete="one-time-code">
        <span class="badge">Secure Verification</span>
        <h2>Enter Your OTP</h2>
        <p>We sent a 6-digit verification code to your registered email address.</p>

        <input
            type="text"
            name="otp_input"
            maxlength="6"
            required
            placeholder="000000"
            class="otp-input"
            inputmode="numeric"
            pattern="[0-9]{6}"
            title="Please enter the 6-digit OTP"
        >

        <button type="submit" name="verify_otp" class="btn-activate">Verify & Update</button>
        <a href="view_profile.php" class="cancel-link">Cancel</a>
    </form>
</body>
</html>