<?php
///index.php
require_once __DIR__ . '/includes/header.php'; // Includes db_connect.php as well
?>

<?php
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
if ($message): ?>
    <div class="container mt-4"> <!-- Add container for message -->
        <div class="message success"><?php echo htmlspecialchars($message); ?></div>
    </div>
<?php endif; ?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1>Your Words, Your World. Start Your Blog Today.</h1>
        <p>The ultimate platform for modern storytellers and thinkers.</p>
        <div class="hero-buttons">
            <a href="/create_blog.php" class="btn">Start Writing</a>
            <a href="#trending" class="btn btn-secondary">Explore Blogs</a>
        </div>
    </div>
</section>

<!-- Trending on BlogSphere Section -->
<section id="trending" class="trending-section">
    <div class="container">
        <h2>Trending on BlogSphere</h2>
        <div class="blog-grid">
            <?php
            try {
                // Prepare a SQL query to get all blog posts, ordered by creation date (newest first)
                // We also join with the 'users' table to get the author's username
                $stmt = $pdo->prepare("SELECT blogPosts.*, users.username FROM blogPosts JOIN users ON blogPosts.user_id = users.id ORDER BY blogPosts.created_at DESC LIMIT 6"); // Limit to 6 for trending
                $stmt->execute();
                $blogs = $stmt->fetchAll();

                if ($blogs) {
                    foreach ($blogs as $blog) {
                        echo '<div class="blog-card">';
                        echo '<a href="/single_blog.php?id=' . htmlspecialchars($blog['id']) . '">';
                        echo '<img src="' . (empty($blog['image_url']) ? 'https://via.placeholder.com/400x200/333333/FFFFFF?text=Blog+Image' : htmlspecialchars($blog['image_url'])) . '" alt="' . htmlspecialchars($blog['title']) . '">';
                        echo '</a>';
                        echo '<div class="card-content">';
                        echo '<h3><a href="/single_blog.php?id=' . htmlspecialchars($blog['id']) . '">' . htmlspecialchars($blog['title']) . '</a></h3>';
                        echo '<p class="card-meta">By: ' . htmlspecialchars($blog['username']) . ' on ' . date('F j, Y', strtotime($blog['created_at'])) . '</p>';
                        echo '<p class="card-snippet">' . htmlspecialchars(substr($blog['content'], 0, 100)) . '...</p>'; // Shorter snippet for cards
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p class="text-center">No blog posts found. Be the first to create one!</p>';
                }
            } catch (PDOException $e) {
                echo '<p class="message error text-center">Error fetching blogs: ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
            ?>
        </div>
    </div>
</section>

<!-- About Us Section -->
<section id="about" class="features-section"> <!-- Reusing features-section styling -->
    <div class="container">
        <h2>About BlogSphere</h2>
        <p class="form-subtitle" style="max-width: 800px; margin-left: auto; margin-right: auto;">
            BlogSphere is your go-to platform for sharing stories, ideas, and knowledge with the world. We believe in the power of words to connect, inspire, and educate. Our mission is to provide a seamless and engaging experience for both writers and readers. Join us and start your blogging journey today!
        </p>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>