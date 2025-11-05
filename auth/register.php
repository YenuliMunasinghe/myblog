<?php
///auth/register.php
require_once __DIR__ . '/../includes/header.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $message = '<div class="message error">Please fill in all fields.</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<div class="message error">Invalid email format.</div>';
    } elseif ($password !== $confirm_password) {
        $message = '<div class="message error">Passwords do not match.</div>';
    } elseif (strlen($password) < 6) {
        $message = '<div class="message error">Password must be at least 6 characters long.</div>';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetchColumn() > 0) {
                $message = '<div class="message error">Username or email already exists.</div>';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
                $stmt->execute([$username, $email, $hashed_password]);

                $_SESSION['message'] = 'Registration successful! You can now log in.';
                header("Location: /auth/login.php");
                exit();
            }
        } catch (PDOException $e) {
            $message = '<div class="message error">Registration failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}
?>

<div class="form-page-container">
    <div class="auth-form-card">
        <h1 class="form-title">Create Your Account</h1>
        <p class="form-subtitle">Start sharing your stories with the world.</p>

        <?php echo $message; ?>

        <form action="/auth/register.php" method="POST">
            <label for="username">Full Name</label> <!-- Changed label for username -->
            <div class="input-group">
                <input type="text" id="username" name="username" placeholder="Enter your full name" required>
            </div>

            <label for="email">Email</label>
            <div class="input-group">
                <input type="email" id="email" name="email" placeholder="Enter your email address" required>
            </div>

            <label for="password">Password</label>
            <div class="input-group">
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
               
            </div>

            <label for="confirm_password">Confirm Password</label>
            <div class="input-group">
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
               
            </div>

            <button type="submit" class="btn form-submit-btn">Sign Up</button>
        </form>

        

        <p class="form-footer-text mt-4">Already have an account? <a href="/auth/login.php">Log In</a></p>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>