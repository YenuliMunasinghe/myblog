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

<!-- Trending on MyBlog Section -->
<section id="trending" class="trending-section">
    <div class="container">
        <?php
        $search_query = trim($_GET['search'] ?? '');
        $selected_tag = trim($_GET['tag'] ?? '');
        ?>

        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 20px; gap: 15px;">
            <h2>
                <?php 
                if (!empty($selected_tag)) {
                    echo 'Posts tagged with "' . htmlspecialchars($selected_tag) . '"';
                } elseif (!empty($search_query)) {
                    echo 'Search results for "' . htmlspecialchars($search_query) . '"';
                } else {
                    echo 'Trending on MyBlog';
                }
                ?>
            </h2>

            <!-- Search Form -->
            <form action="/index.php#trending" method="GET" style="display: flex; gap: 10px; max-width: 400px; width: 100%;">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Search posts or tags..." style="flex: 1; padding: 10px 15px; border-radius: 8px; background-color: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-color); font-family: var(--font-sans);">
                <button type="submit" class="btn" style="padding: 10px 20px;"><i class="fas fa-search"></i> Search</button>
                <?php if (!empty($search_query) || !empty($selected_tag)): ?>
                    <a href="/index.php#trending" class="btn btn-secondary" style="padding: 10px 15px;" title="Clear filter"><i class="fas fa-times"></i></a>
                <?php endif; ?>
            </form>
        </div>

        <div class="blog-grid">
            <?php
            try {
                $sql = "SELECT blogPosts.*, users.username FROM blogPosts JOIN users ON blogPosts.user_id = users.id";
                $params = [];

                if (!empty($selected_tag)) {
                    $sql .= " WHERE blogPosts.tags LIKE ?";
                    $params[] = '%' . $selected_tag . '%';
                } elseif (!empty($search_query)) {
                    $sql .= " WHERE (blogPosts.title LIKE ? OR blogPosts.content LIKE ? OR blogPosts.tags LIKE ?)";
                    $searchTerm = '%' . $search_query . '%';
                    $params = [$searchTerm, $searchTerm, $searchTerm];
                }

                $sql .= " ORDER BY blogPosts.created_at DESC LIMIT 12";

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $blogs = $stmt->fetchAll();

                if ($blogs) {
                    foreach ($blogs as $blog) {
                        echo '<div class="blog-card">';
                        echo '<a href="/single_blog.php?id=' . htmlspecialchars($blog['id']) . '">';
                        echo '<img src="' . (empty($blog['image_url']) ? 'https://icon-library.com/images/no-picture-available-icon/no-picture-available-icon-20.jpg' : htmlspecialchars($blog['image_url'])) . '" alt="' . htmlspecialchars($blog['title']) . '">';
                        echo '</a>';
                        echo '<div class="card-content">';
                        echo '<h3><a href="/single_blog.php?id=' . htmlspecialchars($blog['id']) . '">' . htmlspecialchars($blog['title']) . '</a></h3>';
                        echo '<p class="card-meta">By: ' . htmlspecialchars($blog['username']) . ' on ' . date('F j, Y', strtotime($blog['created_at'])) . '</p>';
                        echo '<p class="card-snippet">' . htmlspecialchars(substr(strip_tags($blog['content']), 0, 100)) . '...</p>';
                        
                        if (!empty($blog['tags'])) {
                            $tag_list = array_map('trim', explode(',', $blog['tags']));
                            echo '<div class="card-tags" style="margin-top: 10px; display: flex; flex-wrap: wrap; gap: 5px;">';
                            foreach ($tag_list as $t) {
                                if (!empty($t)) {
                                    echo '<a href="/index.php?tag=' . urlencode($t) . '#trending" class="tag-pill" style="font-size: 0.75rem; background: var(--input-bg); color: var(--accent-blue); padding: 3px 8px; border-radius: 12px; border: 1px solid var(--border-color);">' . htmlspecialchars($t) . '</a>';
                                }
                            }
                            echo '</div>';
                        }
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p class="text-center" style="grid-column: 1 / -1; padding: 40px 0;">No blog posts found matching your criteria.</p>';
                }
            } catch (PDOException $e) {
                error_log("Fetch blogs index error: " . $e->getMessage());
                echo '<p class="message error text-center">Error fetching blogs. Please try again later.</p>';
            }
            ?>
        </div>
    </div>
</section>

<!-- About Us Section -->
<section id="about" class="features-section"> <!-- Reusing features-section styling -->
    <div class="container">
        <h2>About MyBlog</h2>
        <p class="form-subtitle" style="max-width: 800px; margin-left: auto; margin-right: auto;">
            MyBlog is your go-to platform for sharing stories, ideas, and knowledge with the world. We believe in the power of words to connect, inspire, and educate. Our mission is to provide a seamless and engaging experience for both writers and readers. Join us and start your blogging journey today!
        </p>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>