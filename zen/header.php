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
    <style>
        .signout-btn:hover {
            background-color: #dc2626 !important;
        }
    </style>
</head>
<body>

    <header>
        <a href="index.php" class="logo">ZEN<span>.</span></a>
        <nav style="display: flex; align-items: center;">
            <a href="index.php">Marketplace</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="add-listing.php">Sell Gear</a>
                <span style="margin-left: 2rem; color: #f4f4f4; font-weight: 600; font-size: 0.9rem;">
                    <?= htmlspecialchars($_SESSION['username'] ?? 'Account') ?>
                </span>
                <a href="logout.php" class="signout-btn" style="margin-left: 1rem; background-color: #ef4444; color: #fff; padding: 0.45rem 0.9rem; border-radius: 4px; font-weight: 600; font-size: 0.85rem; text-decoration: none; transition: background-color 0.2s ease;">
                    Sign Out
                </a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="signup.php" class="btn-green" style="margin-left: 1.5rem;">Sign Up</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="container">