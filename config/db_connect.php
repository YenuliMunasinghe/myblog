<?php
// myblog/config/db_connect.php

// Application secret key for cookie signatures & security hashes
if (!defined('APP_SECRET_KEY')) {
    define('APP_SECRET_KEY', getenv('APP_SECRET_KEY') ?: 'myblog_secret_key_8f3a1d9c2e4b5a');
}

// Disable displaying errors on the UI for security; log errors instead
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Database credentials (from environment or defaults) ---
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'blog_app';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Report errors as exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch results as associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Disable emulation for better security/performance
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}

// --- Secure Remember Me Logic (HMAC Cookie Validation) ---
// Clean up legacy insecure cookie if present
if (isset($_COOKIE['remember_user_id'])) {
    setcookie('remember_user_id', '', time() - 3600, '/');
}

if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    $cookie_parts = explode(':', $_COOKIE['remember_me'], 2);
    if (count($cookie_parts) === 2) {
        list($remembered_user_id, $cookie_hash) = $cookie_parts;
        $expected_hash = hash_hmac('sha256', $remembered_user_id, APP_SECRET_KEY);

        if (hash_equals($expected_hash, $cookie_hash)) {
            try {
                $stmt = $pdo->prepare("SELECT id, username FROM users WHERE id = ?");
                $stmt->execute([$remembered_user_id]);
                $user = $stmt->fetch();

                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                } else {
                    setcookie('remember_me', '', time() - 3600, '/');
                }
            } catch (PDOException $e) {
                error_log("Remember Me auto-login DB error: " . $e->getMessage());
                setcookie('remember_me', '', time() - 3600, '/');
            }
        } else {
            // Invalid HMAC signature - potentially tampered cookie
            setcookie('remember_me', '', time() - 3600, '/');
        }
    } else {
        setcookie('remember_me', '', time() - 3600, '/');
    }
}
// --- End Remember Me Logic ---


