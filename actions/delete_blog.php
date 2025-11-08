<?php
// myblog/actions/delete_blog.php
require_once __DIR__ . '/../config/db_connect.php'; // Include db_connect to start session and get $pdo

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = 'You must be logged in to delete a blog post.';
    header("Location: /myblog/auth/login.php");
    exit();
}

// 2. Check if a blog ID was provided and request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['blog_id'])) {
    $blog_id = $_POST['blog_id'];
    $user_id = $_SESSION['user_id']; // The ID of the currently logged-in user

    try {
        // 3. Authorization: Check if the logged-in user owns this blog post
        $stmt = $pdo->prepare("DELETE FROM blogPosts WHERE id = ? AND user_id = ?");
        $stmt->execute([$blog_id, $user_id]);

        // Check if any rows were affected (meaning the blog was found and owned by the user)
        if ($stmt->rowCount() > 0) {
            $_SESSION['message'] = 'Blog post deleted successfully!';
        } else {
            $_SESSION['message'] = 'Blog post not found or you do not have permission to delete it.';
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = 'Error deleting blog post: ' . htmlspecialchars($e->getMessage());
    }
} else {
    $_SESSION['message'] = 'Invalid request to delete blog post.';
}

// Redirect back to the home page
header("Location: /index.php");
exit();
?>