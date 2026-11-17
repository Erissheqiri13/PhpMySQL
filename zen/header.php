<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zen | Gaming Gear Marketplace</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <header>
        <a href="index.php" class="logo">ZEN<span>.</span></a>
        <nav>
            <a href="index.php">Marketplace</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="add-listing.php">Sell Gear</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="signup.php" class="signup-btn">Sign Up</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="container">