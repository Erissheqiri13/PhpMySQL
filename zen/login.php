<?php
session_start();
require_once 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];

            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    }
}

include 'header.php';
?>

<div style="max-width: 400px; margin: 4rem auto; background-color: #141414; border: 1px solid #222; padding: 2.5rem; border-radius: 6px;">
    <h2 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 1.5rem; text-align: center;">Welcome Back</h2>

    <?php if ($error): ?>
        <div style="background-color: rgba(255,0,0,0.1); border: 1px solid rgba(255,0,0,0.3); color: #ff6b6b; padding: 0.8rem; border-radius: 4px; margin-bottom: 1rem; font-size: 0.9rem;"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div style="margin-bottom: 1.2rem;">
            <label style="display: block; font-size: 0.85rem; color: #888; margin-bottom: 0.4rem;">Email Address</label>
            <input type="email" name="email" required style="width: 100%; padding: 0.8rem; background-color: #0b0b0b; border: 1px solid #222; color: #f4f4f4; border-radius: 4px;">
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.85rem; color: #888; margin-bottom: 0.4rem;">Password</label>
            <input type="password" name="password" required style="width: 100%; padding: 0.8rem; background-color: #0b0b0b; border: 1px solid #222; color: #f4f4f4; border-radius: 4px;">
        </div>

        <button type="submit" class="btn-green" style="width: 100%; padding: 0.8rem; text-align: center;">Log In</button>
    </form>
</div>

<?php include 'footer.php'; ?>