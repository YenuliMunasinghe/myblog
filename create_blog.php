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
       
        // $tags = htmlspecialchars($blog['tags']);
    } catch (PDOException $e) {
        $message = '<div class="message error">Error loading blog for edit: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_title = trim($_POST['title']);
    $new_content = trim($_POST['content']);

    $new_image_url = $image_url; // Default to existing image_url if no new upload

    // Handle image upload
    if (isset($_FILES['blog_image']) && $_FILES['blog_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_name = $_FILES['blog_image']['tmp_name'];
        $file_name = $_FILES['blog_image']['name'];
        $file_size = $_FILES['blog_image']['size'];
        $file_type = $_FILES['blog_image']['type'];

        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif','webp'];
        $max_file_size = 5 * 1024 * 1024; // 5MB

        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Basic file validation
        if (!in_array($file_ext, $allowed_extensions)) {
            $message = '<div class="message error">Invalid file type. Only JPG, JPEG, PNG, GIF are allowed.</div>';
        } elseif ($file_size > $max_file_size) {
            $message = '<div class="message error">File size exceeds 5MB limit.</div>';
        } else {
            // Generate a unique filename to prevent conflicts
            // Sanitize the uniqid part to remove extra periods, replacing with underscore
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
                    $old_image_path = __DIR__ . '/../' . $image_url; // Construct full path to old image
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
        // If there was an image previously, keep it in case of new upload error
        $image_url = $new_image_url;
    }

    
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

        <form action="<?php echo htmlspecialchars($form_action); ?>" method="POST" enctype="multipart/form-data" class="blog-editor-form">
            <label for="title" style="display: none;">Title:</label> <!-- Hidden label for styling -->
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" placeholder="Enter your blog title here..." required>

            <label for="content" style="display: none;">Content:</label> <!-- Hidden label for styling -->
            
            <textarea id="content" name="content" placeholder="Start writing your amazing story..." required><?php echo htmlspecialchars($content); ?></textarea>
<label for="blog_image">Upload Image (Optional)</label>
        <input type="file" id="blog_image" name="blog_image" accept="image/*" class="mb-4" style="font-family: var(--font-sans); font-size: 1rem; padding: 10px 15px; border-radius: 8px; background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color); width: 100%; box-sizing: border-box;">
        <?php if (!empty($image_url)): // Show current image if editing and one exists ?>
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