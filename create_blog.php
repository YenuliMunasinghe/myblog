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
$tags = '';
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
        $tags = $blog['tags'] ?? '';
    } catch (PDOException $e) {
        error_log("Load blog for edit failed: " . $e->getMessage());
        $message = '<div class="message error">Error loading blog for edit. Please try again.</div>';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        http_response_code(403);
        $message = '<div class="message error">Invalid or expired CSRF token. Please refresh and try again.</div>';
    } else {
        $new_title = trim($_POST['title']);
        $new_content = trim($_POST['content']);
        $new_tags = trim($_POST['tags'] ?? '');

        $new_image_url = $image_url; // Default to existing image_url if no new upload

        // Handle image upload
        if (isset($_FILES['blog_image']) && $_FILES['blog_image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp_name = $_FILES['blog_image']['tmp_name'];
            $file_name = $_FILES['blog_image']['name'];
            $file_size = $_FILES['blog_image']['size'];

            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $max_file_size = 5 * 1024 * 1024; // 5MB

            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $file_mime = function_exists('mime_content_type') ? mime_content_type($file_tmp_name) : '';

            // Strict file & MIME validation
            if (!in_array($file_ext, $allowed_extensions) || ($file_mime && !in_array($file_mime, $allowed_mime_types))) {
                $message = '<div class="message error">Invalid file type. Only JPG, JPEG, PNG, GIF, and WEBP images are allowed.</div>';
            } elseif ($file_size > $max_file_size) {
                $message = '<div class="message error">File size exceeds 5MB limit.</div>';
            } else {
                // Generate a unique filename to prevent conflicts
                $uniqid_part = str_replace('.', '_', uniqid('blog_img_', true));
                $unique_file_name = $uniqid_part . '.' . $file_ext;
                $upload_directory = __DIR__ . '/uploads/images/'; // Absolute path to uploads folder
                $destination_path = $upload_directory . $unique_file_name;

                // Ensure upload directory exists
                if (!is_dir($upload_directory)) {
                    mkdir($upload_directory, 0755, true); // Create directory if it doesn't exist
                }

                // Move the uploaded file
                if (move_uploaded_file($file_tmp_name, $destination_path)) {
                    // If an old image existed and it's a new upload, delete the old one
                    if (!empty($image_url) && $image_url !== $new_image_url) {
                        $old_image_path = __DIR__ . '/' . ltrim($image_url, '/'); // Construct full path to old image
                        if (file_exists($old_image_path) && is_file($old_image_path)) {
                            unlink($old_image_path); // Delete old image file
                        }
                    }
                    // Store the relative path to the image in the database
                    $new_image_url = '/uploads/images/' . $unique_file_name;
                } else {
                    $message = '<div class="message error">Failed to upload image. Check folder permissions.</div>';
                }
            }
        }
        // If there's an image upload error, we don't proceed with the blog post save
        if (!empty($message)) {
            // Keep the current title and content in case of image upload error
            $title = $new_title;
            $content = $new_content;
            $tags = $new_tags;
            // If there was an image previously, keep it in case of new upload error
            $image_url = $new_image_url;
        }

        
        $user_id = $_SESSION['user_id'];

        if (empty($new_title) || empty($new_content)) {
            $message = '<div class="message error">Title and Content cannot be empty.</div>';
        } else {
            try {
                if ($blog_id) {
                    $stmt = $pdo->prepare("UPDATE blogPosts SET title = ?, content = ?, image_url = ?, tags = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
                    $stmt->execute([$new_title, $new_content, $new_image_url, $new_tags, $blog_id, $user_id]);
                    $_SESSION['message'] = 'Blog post updated successfully!';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO blogPosts (user_id, title, content, image_url, tags) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$user_id, $new_title, $new_content, $new_image_url, $new_tags]);
                    $_SESSION['message'] = 'New blog post created successfully!';
                }
                header("Location: /index.php");
                exit();
            } catch (PDOException $e) {
                error_log("Blog post save operation failed: " . $e->getMessage());
                $message = '<div class="message error">Operation failed due to a system error. Please try again.</div>';
            }
        }
    }
}
?>

<div class="editor-container">
    <div class="container">
        <div class="editor-header">
            <h1 class="editor-page-title"><?php echo $page_title; ?></h1>
            <div class="editor-actions">
                <span>Markdown formatting is supported.</span>
            </div>
        </div>

        <?php echo $message; ?>

        <form action="<?php echo htmlspecialchars($form_action); ?>" method="POST" enctype="multipart/form-data" class="blog-editor-form">
            <?php echo csrf_field(); ?>
            <label for="title" style="display: none;">Title:</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" placeholder="Enter your blog title here..." required>

            <label for="tags" style="font-family: var(--font-sans); font-size: 0.9rem; color: var(--light-text); display: block; margin-top: 10px; margin-bottom: 5px;">Tags (comma-separated, e.g. Technology, Productivity, Design):</label>
            <input type="text" id="tags" name="tags" value="<?php echo htmlspecialchars($tags); ?>" placeholder="e.g. Technology, Productivity, Design" style="font-family: var(--font-sans); font-size: 1rem; padding: 10px 15px; border-radius: 8px; background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color); width: 100%; box-sizing: border-box; margin-bottom: 15px;">

            <label for="content" style="display: none;">Content:</label>
            <textarea id="content" name="content" placeholder="Start writing your story... (Supports Markdown: # Header, **bold**, *italics*, `code`, - list)" required><?php echo htmlspecialchars($content); ?></textarea>
            
            <label for="blog_image" style="font-family: var(--font-sans); font-size: 0.9rem; color: var(--light-text); display: block; margin-top: 10px; margin-bottom: 5px;">Upload Header Image (Optional)</label>
            <input type="file" id="blog_image" name="blog_image" accept="image/*" class="mb-4" style="font-family: var(--font-sans); font-size: 1rem; padding: 10px 15px; border-radius: 8px; background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color); width: 100%; box-sizing: border-box;">
            <?php if (!empty($image_url)): ?>
                <div style="margin-bottom: 20px;">
                    <p style="color: var(--light-text); font-size: 0.9em;">Current Image:</p>
                    <img src="<?php echo htmlspecialchars($image_url); ?>" alt="Current Blog Image" style="max-width: 200px; height: auto; border-radius: 8px;">
                </div>
            <?php endif; ?>

            <div class="submit-btn-group">
                <button type="submit" class="btn"><?php echo ($blog_id ? 'Save Changes' : 'Create Post'); ?></button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>