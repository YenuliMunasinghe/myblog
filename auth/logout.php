<?php
// myblog/auth/logout.php
require_once __DIR__ . '/../config/db_connect.php'; // Just to ensure session is started

// Destroy all session variables
$_SESSION = array();

// If it's a cookie-based session, invalidate the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session
session_destroy();
// Also clear the 'Remember Me' cookie if it exists
if (isset($_COOKIE['remember_user_id'])) {
    setcookie('remember_user_id', '', time() - 3600, '/'); // Set expiration in the past to delete
}

$_SESSION['message'] = 'You have been logged out.';
header("Location: /index.php"); // Redirect to home page
exit();
?>