<?php
require_once 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "An account with that email already exists.";
        } else {
            // Hash the password securely
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user into database
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashed_password])) {
                $success = "Account created successfully! You can now <a href='login.php' style='color: var(--accent-green);'>log in</a>.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}

include 'header.php';
?>

<div style="max-width: 400px; margin: 4rem auto; background-color: #141414; border: 1px solid #222; padding: 2.5rem; border-radius: 6px;">
    <h2 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 1.5rem; text-align: center;">Join ZEN</h2>

    <?php if ($error): ?>
        <div style="background-color: rgba(255,0,0,0.1); border: 1px solid rgba(255,0,0,0.3); color: #ff6b6b; padding: 0.8rem; border-radius: 4px; margin-bottom: 1rem; font-size: 0.9rem;"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div style="background-color: rgba(0,255,102,0.1); border: 1px solid rgba(0,255,102,0.3); color: var(--accent-green); padding: 0.8rem; border-radius: 4px; margin-bottom: 1rem; font-size: 0.9rem;"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div style="margin-bottom: 1.2rem;">
            <label style="display: block; font-size: 0.85rem; color: #888; margin-bottom: 0.4rem;">Username</label>
            <input type="text" name="username" required style="width: 100%; padding: 0.8rem; background-color: #0b0b0b; border: 1px solid #222; color: #f4f4f4; border-radius: 4px;">
        </div>

        <div style="margin-bottom: 1.2rem;">
            <label style="display: block; font-size: 0.85rem; color: #888; margin-bottom: 0.4rem;">Email Address</label>
            <input type="email" name="email" required style="width: 100%; padding: 0.8rem; background-color: #0b0b0b; border: 1px solid #222; color: #f4f4f4; border-radius: 4px;">
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.85rem; color: #888; margin-bottom: 0.4rem;">Password</label>
            <input type="password" name="password" required style="width: 100%; padding: 0.8rem; background-color: #0b0b0b; border: 1px solid #222; color: #f4f4f4; border-radius: 4px;">
        </div>

        <button type="submit" class="btn-green" style="width: 100%; padding: 0.8rem; text-align: center;">Create Account</button>
    </form>
</div>

<?php include 'footer.php'; ?>