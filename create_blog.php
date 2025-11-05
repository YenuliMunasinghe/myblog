<?php
///create_blog.php
require_once __DIR__ . '/includes/header.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = 'You must be logged in to create or edit a blog post.';
    header("Location: /auth/login.php");
    exit();
}

$blog_id = $_GET['id'] ?? null;
$title = '';
$image_url = '';
$content = '';
$tags = 'Technology, Productivity'; // Placeholder tags for styling
$form_action = '/create_blog.php';
$page_title = 'Create New Post';
$message = '';

if ($blog_id) {
    $page_title = 'Edit Post';
    $form_action = '/create_blog.php?id=' . htmlspecialchars($blog_id);

    try {
        $stmt = $pdo->prepare("SELECT * FROM blogPosts WHERE id = ? AND user_id = ?");
        $stmt->execute([$blog_id, $_SESSION['user_id']]);
        $blog = $stmt->fetch();

        if (!$blog) {
            $_SESSION['message'] = 'Blog not found or you do not have permission to edit it.';
            header("Location: /index.php");
            exit();
        }

        $title = $blog['title'];
        $content = $blog['content'];
        $image_url = $blog['image_url'];
        // If you had a 'tags' column, you'd fetch it here
        // $tags = htmlspecialchars($blog['tags']);
    } catch (PDOException $e) {
        $message = '<div class="message error">Error loading blog for edit: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_title = trim($_POST['title']);
    $new_content = trim($_POST['content']);
    $new_image_url = trim($_POST['image_url']);
    $user_id = $_SESSION['user_id'];
    // $new_tags = trim($_POST['tags']); // If you add a tags field

    if (empty($new_title) || empty($new_content)) {
        $message = '<div class="message error">Title and Content cannot be empty.</div>';
    } else {
        try {
            if ($blog_id) {
                $stmt = $pdo->prepare("UPDATE blogPosts SET title = ?, content = ?, image_url = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
                $stmt->execute([$new_title, $new_content, $new_image_url, $blog_id, $user_id]);
                $_SESSION['message'] = 'Blog post updated successfully!';
            } else {
                $stmt = $pdo->prepare("INSERT INTO blogPosts (user_id, title, content,image_url) VALUES (?, ?, ?,?)");
                $stmt->execute([$user_id, $new_title, $new_content,$new_image_url]);
                $_SESSION['message'] = 'New blog post created successfully!';
            }
            header("Location: /index.php");
            exit();
        } catch (PDOException $e) {
            $message = '<div class="message error">Operation failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}
?>

<div class="editor-container">
    <div class="container">
        <div class="editor-header">
            <h1 class="editor-page-title"><?php echo $page_title; ?></h1>
            <div class="editor-actions">
                <span>Your changes are saved automatically.</span>
                
            </div>
        </div>

        <?php echo $message; ?>

        <form action="<?php echo htmlspecialchars($form_action); ?>" method="POST" class="blog-editor-form">
            <label for="title" style="display: none;">Title:</label> <!-- Hidden label for styling -->
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" placeholder="Enter your blog title here..." required>

            <label for="content" style="display: none;">Content:</label> <!-- Hidden label for styling -->
            
            <textarea id="content" name="content" placeholder="Start writing your amazing story..." required><?php echo htmlspecialchars($content); ?></textarea>
<label for="image_url">Image URL (Optional)</label>
        <input type="text" id="image_url" name="image_url" value="<?php echo htmlspecialchars($image_url); ?>" placeholder="Paste an image URL (e.g., from Unsplash, Imgur)" class="mb-4" style="font-family: var(--font-sans); font-size: 1rem; padding: 10px 15px; border-radius: 8px; background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color); width: 100%; box-sizing: border-box;">

            

            <div class="submit-btn-group">
                <button type="submit" class="btn"><?php echo ($blog_id ? 'Save Changes' : 'Create Post'); ?></button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>