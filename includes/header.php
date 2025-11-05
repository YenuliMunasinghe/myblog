
<?php
// We start session_start() in db_connect.php, which will be included here.
// Ensure db_connect.php is included before outputting any HTML
require_once __DIR__ . '/../config/db_connect.php'; // Correct path to db_connect.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BlogSphere</title> <!-- Changed title to match design -->

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link rel="stylesheet" href="/css/style.css"> <!-- Link to your CSS file -->
</head>
<body>
<body>
    <!-- ... (rest of header.php, after <head> and before <body>) ... -->
<body>
    <header>
        <div class="container">
            <div id="branding">
                <h1><a href="/index.php">
                    <i class="fas fa-cube"></i> BlogSphere <!-- Changed logo icon and text -->
                </a></h1>
            </div>
            <nav>
                <ul>
                    <li><a href="/index.php">Explore Blogs</a></li> <!-- Changed text -->
                    <li><a href="/index.php#about">About Us</a></li> <!-- Link to new about section -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Show these links if user is logged in -->
                        <li><a href="/create_blog.php">Create New Blog</a></li>
                        <li><a href="/auth/logout.php" class="btn btn-secondary">Logout</a></li> <!-- Styled as secondary button -->
                    <?php else: ?>
                        <!-- Show these links if user is NOT logged in -->
                        <li><a href="/auth/login.php" class="btn">Login</a></li> <!-- Styled as primary button -->
                        <li><a href="/auth/register.php" class="btn btn-primary">Sign Up</a></li> <!-- Styled as primary button, add class for clarity -->
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <!-- The main-content container no longer wraps the entire page,
         it will be applied to specific sections below the header -->
    <div class="container"> <!-- Main content container starts here -->