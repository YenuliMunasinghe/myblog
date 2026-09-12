<?php
// myblog/actions/toggle_like.php

// Start session and get database connection and base path
//  included header.php to ensure $base_path is defined for redirects
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../includes/csrf.php';

// Compute a robust base path for redirects.


$script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); 
if (strpos($script_dir, '/actions') !== false) {
    $base_path = substr($script_dir, 0, strpos($script_dir, '/actions')) ?: '/';
    $base_path = rtrim($base_path, '/') . '/';
} else {
    $base_path = rtrim($script_dir, '/') . '/';
}

$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || 
           (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

$response_data = [
    'success' => false,
    'liked' => false,
    'total_likes' => 0,
    'message' => ''
];

// 1. Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'You must be logged in to like a post.']);
        exit();
    }
    $_SESSION['message'] = 'You must be logged in to like a post.';
    header("Location: " . $base_path . "auth/login.php");
    exit();
}

// 2. Ensure blog_id is provided via POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['blog_id'])) {
    if (!verify_csrf_token()) {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
            exit();
        }
        $_SESSION['message'] = 'Invalid CSRF token.';
        header("Location: " . $base_path . "single_blog.php?id=" . (int)$_POST['blog_id']);
        exit();
    }
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
            $response_data['liked'] = false;
            $response_data['message'] = 'Post unliked.';
        } else {
            // If no like record exists, insert a new one (user is "liking" the post)
            $stmt = $pdo->prepare("INSERT INTO likes (user_id, blog_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $blog_id]);
            $_SESSION['message'] = 'Post liked!';
            $response_data['liked'] = true;
            $response_data['message'] = 'Post liked!';
        }

        // Fetch updated total likes
        $stmt_count = $pdo->prepare("SELECT COUNT(*) AS total FROM likes WHERE blog_id = ?");
        $stmt_count->execute([$blog_id]);
        $total_likes = $stmt_count->fetch()['total'] ?? 0;

        $response_data['success'] = true;
        $response_data['total_likes'] = (int)$total_likes;

    } catch (PDOException $e) {
        error_log("Toggle like error: " . $e->getMessage());
        $_SESSION['message'] = 'Error processing your like. Please try again.';
        $response_data['message'] = 'Error processing your like.';
    }
} else {
    $_SESSION['message'] = 'Invalid request.';
    $response_data['message'] = 'Invalid request.';
}

if ($is_ajax) {
    header('Content-Type: application/json');
    echo json_encode($response_data);
    exit();
}

// Redirect the user back to the single blog post page they were just on
if ($blog_id_for_redirect) {
    header("Location: " . $base_path . "single_blog.php?id=" . htmlspecialchars($blog_id_for_redirect));
} else {
    header("Location: " . $base_path . "index.php");
}
exit(); // Crucial to stop script execution after a header redirect
?>