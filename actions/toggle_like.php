<?php
// myblog/actions/toggle_like.php

// Start session and get database connection and base path
//  included header.php to ensure $base_path is defined for redirects
require_once __DIR__ . '/../config/db_connect.php';

// Compute a robust base path for redirects.


$script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); 
if (strpos($script_dir, '/actions') !== false) {
    $base_path = substr($script_dir, 0, strpos($script_dir, '/actions')) ?: '/';
    $base_path = rtrim($base_path, '/') . '/';
} else {
    $base_path = rtrim($script_dir, '/') . '/';
}

$blog_id_for_redirect = null; // Initialize for safe redirect in all cases

// 1. Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = 'You must be logged in to like a post.';
    header("Location: " . $base_path . "auth/login.php");
    exit();
}

// 2. Ensure blog_id is provided via POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['blog_id'])) {
    $blog_id = (int) $_POST['blog_id'];
    $blog_id_for_redirect = $blog_id; // Set this for the final redirect
    $user_id = $_SESSION['user_id'];

    try {
        // Check if the current user has already liked this specific post
        $stmt = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND blog_id = ?");
        $stmt->execute([$user_id, $blog_id]);
        $like_exists = $stmt->fetch();

        if ($like_exists) {
            // If a like record exists, delete it (user is "unliking" the post)
            $stmt = $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND blog_id = ?");
            $stmt->execute([$user_id, $blog_id]);
            $_SESSION['message'] = 'Post unliked.';
        } else {
            // If no like record exists, insert a new one (user is "liking" the post)
            $stmt = $pdo->prepare("INSERT INTO likes (user_id, blog_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $blog_id]);
            $_SESSION['message'] = 'Post liked!';
        }
    } catch (PDOException $e) {
        // Catch and handle any database-related errors (e.g., table not found, constraint violations)
        
        $_SESSION['message'] = 'Error processing your like: ' . htmlspecialchars($e->getMessage());
    }
} else {
    // Handle cases where blog_id is not provided or request method is not POST
    $_SESSION['message'] = 'Invalid request.';
}

// Redirect the user back to the single blog post page they were just on
if ($blog_id_for_redirect) {
    header("Location: " . $base_path . "single_blog.php?id=" . htmlspecialchars($blog_id_for_redirect));
} else {
    // Fallback to the home page if for some reason blog_id was never set
    header("Location: " . $base_path . "index.php");
}
exit(); // Crucial to stop script execution after a header redirect
?>