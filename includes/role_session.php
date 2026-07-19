<?php
function start_role_session($role = null) {
    $sessionName = 'sms_app';

    if (session_status() === PHP_SESSION_NONE) {
        session_name($sessionName);
        session_start();
    } elseif (session_name() !== $sessionName) {
        session_write_close();
        session_name($sessionName);
        session_start();
    }

    if (!empty($role)) {
        $_SESSION['role'] = $role;
    }

    return $sessionName;
}

function logout_role_session($role = null) {
    $sessionName = 'sms_app';

    if (session_status() !== PHP_SESSION_NONE) {
        session_write_close();
    }

    session_name($sessionName);
    session_start();
    session_unset();
    session_destroy();
    session_write_close();
}
