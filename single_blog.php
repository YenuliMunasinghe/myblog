<?php
///single_blog.php
require_once __DIR__ . '/includes/header.php';

$blog = null;
$message = '';
$blog_id = $_GET['id'] ?? null;

if (!$blog_id) {
    $_SESSION['message'] = 'No blog post specified.';
    header("Location: /index.php");
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT blogPosts.*, users.username FROM blogPosts JOIN users ON blogPosts.user_id = users.id WHERE blogPosts.id = ?");
    $stmt->execute([$blog_id]);
    $blog = $stmt->fetch();

    if (!$blog) {
        $_SESSION['message'] = 'Blog post not found.';
        header("Location: /index.php");
        exit();
    }
} catch (PDOException $e) {
    $message = '<p class="message error">Error fetching blog: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

$is_author = (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $blog['user_id']);
?>

<div class="single-blog-post">
    <div class="container">
        <?php
        $session_message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);
        if ($session_message): ?>
            <div class="message success mb-4"><?php echo htmlspecialchars($session_message); ?></div>
        <?php endif; ?>
        <?php echo $message; ?>

        <?php if ($blog): ?>
            <h1 class="blog-title"><?php echo htmlspecialchars($blog['title']); ?></h1>
            <?php if (!empty($blog['image_url'])): ?>
                <img src="<?php echo htmlspecialchars($blog['image_url']); ?>" alt="<?php echo htmlspecialchars($blog['title']); ?>" class="blog-main-image">
            <?php endif; ?>
            <div class="blog-meta-full">
                <span><i class="fas fa-user"></i> By: <?php echo htmlspecialchars($blog['username']); ?></span>
                <span><i class="fas fa-calendar-alt"></i> On: <?php echo date('F j, Y', strtotime($blog['created_at'])); ?></span>
                <?php if ($blog['created_at'] != $blog['updated_at']): ?>
                    <span><i class="fas fa-clock"></i> Last updated: <?php echo date('F j, Y, H:i', strtotime($blog['updated_at'])); ?></span>
                <?php endif; ?>
            </div>
            <div class="blog-content-full">
                <!-- For Markdown, using a PHP Markdown parser library here -->
                <p style="white-space: pre-wrap;"><?php echo htmlspecialchars($blog['content']); ?></p>
            </div>
           <div class="blog-likes-section">
            <?php
            // Get current user's login status and ID
            $user_logged_in = isset($_SESSION['user_id']);
            $current_user_id = $_SESSION['user_id'] ?? null;
            $blog_id_for_like_feature = htmlspecialchars($blog['id']); // Ensuring blog_id is safe for HTML

            // 1. Get total likes for this specific blog post
            $stmt_likes = $pdo->prepare("SELECT COUNT(*) AS total_likes FROM likes WHERE blog_id = ?");
            $stmt_likes->execute([$blog_id_for_like_feature]);
            $total_likes = $stmt_likes->fetch()['total_likes'];

            // 2. Check if the current logged-in user has already liked this post
            $user_has_liked = false;
            if ($user_logged_in) {
                $stmt_user_like = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE blog_id = ? AND user_id = ?");
                $stmt_user_like->execute([$blog_id_for_like_feature, $current_user_id]);
                $user_has_liked = ($stmt_user_like->fetchColumn() > 0);
            }
            ?>
            <span class="like-count"><i class="fas fa-heart"></i> <?php echo $total_likes; ?> Likes</span>

            <?php if ($user_logged_in): ?>
                <!-- Form to submit like/unlike action -->
                <!-- Use a relative action so the URL resolves correctly in subfolder or root installs -->
                <form action="actions/toggle_like.php" method="POST" style="display: inline-block; margin-left: 15px;">
                    <input type="hidden" name="blog_id" value="<?php echo $blog_id_for_like_feature; ?>">
                    <button type="submit" class="btn btn-like <?php echo $user_has_liked ? 'liked' : ''; ?>" title="<?php echo $user_has_liked ? 'Unlike this post' : 'Like this post'; ?>">
                        <?php if ($user_has_liked): ?>
                            <i class="fas fa-heart"></i> Unlike
                        <?php else: ?>
                            <i class="far fa-heart"></i> Like
                        <?php endif; ?>
                    </button>
                </form>
            <?php else: ?>
                <!-- Message for non-logged-in users -->
                <span style="margin-left: 15px; color: var(--light-text); font-size: 0.9em;">
                    <!-- Use relative login URL so it works in subfolder installs as well -->
                    <a href="auth/login.php">Login</a> to like this post.
                </span>
            <?php endif; ?>
        </div>



            <?php if ($is_author): ?>
                <div class="blog-actions">
                    <a href="/create_blog.php?id=<?php echo htmlspecialchars($blog['id']); ?>" class="btn">Edit Blog</a>
                    <form action="/actions/delete_blog.php" method="POST" style="display: inline-block;">
                        <input type="hidden" name="blog_id" value="<?php echo htmlspecialchars($blog['id']); ?>">
                        <button type="submit" onclick="return confirm('Are you sure you want to delete this blog post?');" class="btn delete-btn">Delete Blog</button>
                    </form>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p class="message error">Blog post could not be loaded.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>