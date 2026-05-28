<?php
// Start the session context to access it
session_start();

// Clear all session variables completely
$_SESSION = array();

// Destroy the cookie tracker from the browser session storage
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Vaporize the session on your local host server environment
session_destroy();

// Redirect back to your login portal instantly
header("Location: index.html");
exit();
?>