<?php
// myblog/auth/logout.php
require_once __DIR__ . '/../config/db_connect.php'; // Ensures session and DB connection

// Clear Remember Me cookies if set
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, '/');
}
if (isset($_COOKIE['remember_user_id'])) {
    setcookie('remember_user_id', '', time() - 3600, '/');
}

// Destroy all session variables
$_SESSION = array();

// Invalidate session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Start a fresh session for flash message
session_start();
$_SESSION['message'] = 'You have been logged out.';
header("Location: /index.php"); // Redirect to home page
exit();
?>