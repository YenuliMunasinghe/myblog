<?php
// myblog/config/db_connect.php

// Start a session to manage user login status

// --- Temporarily enable maximum error reporting for debugging ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- End error reporting setup ---
session_start();

// --- Database credentials ---

// $host = 'localhost';//'sql100.infinityfree.com';      
// $dbname = 'blog_app';//'if0_40230817_blogdb';       
// $username = 'root';//'if0_40230817';             
// $password = '';//'sYhfLPRghjmN';            

$host = 'sql100.infinityfree.com';      //  specific InfinityFree database host
$dbname = 'if0_40230817_blogdb';        //  specific online database name
$username = 'if0_40230817';             //  specific online database username
$password = 'sYhfLPRghjmN';             //  specific online database password 

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Report errors as exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch results as associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Disable emulation for better security/performance
];

try {
    // Create a new PHP Data Objects instance
    //  where the database connection is actually established.
    $pdo = new PDO($dsn, $username, $password, $options);
    // 
    // echo "Connected to database successfully!"; 
} catch (PDOException $e) {
    // If connection fails, display an error message and stop the script.
    
    die("Database connection failed: " . $e->getMessage());
}

// --- Remember Me Logic (Cookie-based auto-login) ---
// the logic runs only IF the user is NOT already logged in via session
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
            
        } else {
            // If the user ID in the cookie is invalid , clear the cookie
            setcookie('remember_user_id', '', time() - 3600, '/'); // Set expiration in the past to delete
        }
    } catch (PDOException $e) {
        // Log database errors for debugging, but not exposing them to the user.
        error_log("Remember Me auto-login failed: " . $e->getMessage());
        // Clear the potentially problematic cookie if there's a DB error during lookup
        setcookie('remember_user_id', '', time() - 3600, '/');
    }
}
// --- End Remember Me Logic ---

