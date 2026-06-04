<?php
require_once __DIR__ . '/helpers.php';
sessionStart();

// Unset all session variables
$_SESSION = [];

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// We need to start a new session just to set the flash message
sessionStart();
setFlash('info', 'You have been successfully logged out.');

redirect('login.php');
