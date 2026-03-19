<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

startSession();

// Destroy all session data
session_destroy();

// Delete session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirect to login page
redirect('../courses/index.php');
?>
