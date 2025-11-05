<?php
// myblog/config/db_connect.php

// Start a session to manage user login status
// This must be the very first thing in your script, before any output.
session_start();

// --- Database credentials ---
// IMPORTANT: These are your ONLINE HOSTING credentials.
// For LOCAL TESTING, you must change $host, $dbname, $username, $password to 'localhost', 'blog_app', 'root', '' respectively.
$host = 'sql100.infinityfree.com';      // Your specific InfinityFree database host
$dbname = 'if0_40230817_blogdb';        // Your specific online database name
$username = 'if0_40230817';             // Your specific online database username
$password = 'sYhfLPRghjmN';             // Your specific online database password (Removed stray '6')

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Report errors as exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch results as associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Disable emulation for better security/performance
];

try {
    // Create a new PDO instance (PHP Data Objects)
    // This is where the database connection is actually established.
    $pdo = new PDO($dsn, $username, $password, $options);
    // You can uncomment the line below for testing connection, then remove it.
    // echo "Connected to database successfully!"; 
} catch (PDOException $e) {
    // If connection fails, display an error message and stop the script.
    // In a production environment, you might log this error and display a generic message.
    die("Database connection failed: " . $e->getMessage());
}

// --- Remember Me Logic (Cookie-based auto-login) ---
// This logic runs only IF the user is NOT already logged in via session
// AND a 'remember me' cookie is present.
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_user_id'])) {
    $remembered_user_id = $_COOKIE['remember_user_id'];

    try {
        // Prepare a statement to find the user by their ID from the cookie
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE id = ?");
        $stmt->execute([$remembered_user_id]);
        $user = $stmt->fetch();

        if ($user) {
            // User found, re-establish their session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            // Optionally, you can set a session message here, e.g.:
            // $_SESSION['message'] = 'Welcome back via "Remember Me", ' . htmlspecialchars($user['username']) . '!';
        } else {
            // If the user ID in the cookie is invalid (e.g., user deleted), clear the cookie
            setcookie('remember_user_id', '', time() - 3600, '/'); // Set expiration in the past to delete
        }
    } catch (PDOException $e) {
        // Log database errors for debugging, but don't expose them to the user.
        error_log("Remember Me auto-login failed: " . $e->getMessage());
        // Clear the potentially problematic cookie if there's a DB error during lookup
        setcookie('remember_user_id', '', time() - 3600, '/');
    }
}
// --- End Remember Me Logic ---

// No closing PHP tag if the file contains only PHP code, to prevent accidental whitespace output.