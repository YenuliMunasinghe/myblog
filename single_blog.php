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
                <!-- For Markdown, you would use a PHP Markdown parser library here -->
                <p style="white-space: pre-wrap;"><?php echo htmlspecialchars($blog['content']); ?></p>
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