<?php
///auth/login.php
require_once __DIR__ . '/../includes/header.php';

if (isset($_SESSION['user_id'])) {
    header("Location: /index.php");
    exit();
}

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_or_email = trim($_POST['username_or_email']);
    $password = $_POST['password'];

    if (empty($username_or_email) || empty($password)) {
        $message = '<div class="message error">Please enter both username/email and password.</div>';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username_or_email, $username_or_email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            // Check if 'Remember Me' was checked
            if (isset($_POST['remember_me'])) {
                // Set a persistent cookie with user ID for 30 days
                $cookie_name = 'remember_user_id';
                $cookie_value = $user['id']; // Simple for beginner, see security note above
                $expiration = time() + (30 * 24 * 60 * 60); // 30 days
                setcookie($cookie_name, $cookie_value, [
                    'expires' => $expiration,
                    'path' => '/', // Available across the entire site
                    'httponly' => true, // HttpOnly: Prevents JavaScript access, important for security
                    'samesite' => 'Lax' // CSRF protection
                    // 'secure' => true // Uncomment in production if your site uses HTTPS
                ]);
            }

            $_SESSION['message'] = 'Welcome back, ' . htmlspecialchars($user['username']) . '!';
            header("Location: /index.php");
            exit();
            } else {
                $message = '<div class="message error">Invalid username/email or password.</div>';
            }
        } catch (PDOException $e) {
            $message = '<div class="message error">Login failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}
?>

<div class="form-page-container">
    <div class="auth-form-card">
        <i class="fas fa-edit form-logo"></i> <!-- Blog Writer Logo Icon -->
        <h1 class="form-title">Welcome back</h1>
        <p class="form-subtitle">Log in to your account</p>

        <?php echo $message; ?>

        <form action="/auth/login.php" method="POST">
            <label for="username_or_email">Email or Username</label>
            <div class="input-group">
                <input type="text" id="username_or_email" name="username_or_email" placeholder="Enter your email or username" required>
            </div>

            <label for="password">Password</label>
            <div class="input-group">
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                
            </div>
            <div class="form-options">
            <div class="checkbox-group">
                <input type="checkbox" id="remember_me" name="remember_me">
                <label for="remember_me" style="display: inline;">Remember me</label>
            </div>
        </div>

        <button type="submit" class="btn form-submit-btn">Log In</button>

            

            <button type="submit" class="btn form-submit-btn">Log In</button>
        </form>

        <p class="form-footer-text">Don't have an account? <a href="/auth/register.php">Sign Up</a></p>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>