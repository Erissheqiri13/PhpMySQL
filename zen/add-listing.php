<?php
session_start();
require_once 'db.php';

// Protect page: only logged-in users can post a listing
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $category    = trim($_POST['category'] ?? '');
    $price       = trim($_POST['price'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $user_id     = $_SESSION['user_id'];

    $allowed_categories = ['pc', 'gpu', 'monitor', 'keyboard', 'mouse'];

    if (empty($title) || empty($category) || empty($price)) {
        $error = "Title, category, and price are required.";
    } elseif (!in_array($category, $allowed_categories, true)) {
        $error = "Please select a valid category.";
    } elseif (!is_numeric($price) || (float)$price < 0) {
        $error = "Please enter a valid price.";
    } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $error = "Please upload a product image.";
    } else {
        $file_tmp  = $_FILES['image']['tmp_name'];
        $file_name = $_FILES['image']['name'];
        $file_size = $_FILES['image']['size'];
        $ext       = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

        $image_info = @getimagesize($file_tmp);

        if ($image_info === false || !in_array($ext, $allowed_ext, true)) {
            $error = "Only JPG, JPEG, PNG, and WEBP image files are allowed.";
        } elseif ($file_size > 5 * 1024 * 1024) { // 5 MB limit
            $error = "Image must be 5MB or smaller.";
        } else {
            // Create unique filename to avoid collisions
            $new_filename = uniqid('zen_', true) . '.' . $ext;
            $upload_dir   = __DIR__ . '/uploads/';
            $target_path  = $upload_dir . $new_filename;
            $db_path      = 'uploads/' . $new_filename;

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            if (move_uploaded_file($file_tmp, $target_path)) {
                $stmt = $pdo->prepare(
                    "INSERT INTO products (userid, title, description, price, category, productimage) VALUES (?, ?, ?, ?, ?, ?)"
                );
                if ($stmt->execute([$user_id, $title, $description, $price, $category, $db_path])) {
                    $success = "Listing published! <a href='index.php' style='color: #00ff66;'>View in marketplace</a>";
                } else {
                    $error = "Database error while saving the listing.";
                }
            } else {
                $error = "Failed to save uploaded image.";
            }
        }
    }
}

include 'header.php';
?>

<div style="max-width: 550px; margin: 2rem auto; background-color: #141414; border: 1px solid #222; padding: 2.5rem; border-radius: 6px;">
    <h2 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 1.5rem;">Post Gaming Gear</h2>

    <?php if ($error): ?>
        <div style="background-color: rgba(255,0,0,0.1); border: 1px solid rgba(255,0,0,0.3); color: #ff6b6b; padding: 0.8rem; border-radius: 4px; margin-bottom: 1.2rem; font-size: 0.9rem;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div style="background-color: rgba(0,255,102,0.1); border: 1px solid rgba(0,255,102,0.3); color: #00ff66; padding: 0.8rem; border-radius: 4px; margin-bottom: 1.2rem; font-size: 0.9rem;">
            <?= $success ?>
        </div>
    <?php endif; ?>

    <!-- enctype="multipart/form-data" is REQUIRED for file uploads -->
    <form method="POST" action="" enctype="multipart/form-data">
        <div style="margin-bottom: 1.2rem;">
            <label style="display: block; font-size: 0.85rem; color: #888; margin-bottom: 0.4rem;">Item Title</label>
            <input type="text" name="title" placeholder="e.g., ASUS TUF RTX 4070 Ti Super" required style="width: 100%; padding: 0.8rem; background-color: #0b0b0b; border: 1px solid #222; color: #f4f4f4; border-radius: 4px;">
        </div>

        <div style="margin-bottom: 1.2rem;">
            <label style="display: block; font-size: 0.85rem; color: #888; margin-bottom: 0.4rem;">Category</label>
            <select name="category" required style="width: 100%; padding: 0.8rem; background-color: #0b0b0b; border: 1px solid #222; color: #f4f4f4; border-radius: 4px;">
                <option value="">Select a category...</option>
                <option value="pc">Gaming PCs</option>
                <option value="gpu">GPUs</option>
                <option value="monitor">Monitors</option>
                <option value="keyboard">Keyboards</option>
                <option value="mouse">Mice</option>
            </select>
        </div>

        <div style="margin-bottom: 1.2rem;">
            <label style="display: block; font-size: 0.85rem; color: #888; margin-bottom: 0.4rem;">Price ($)</label>
            <input type="number" name="price" step="0.01" min="0" placeholder="799.00" required style="width: 100%; padding: 0.8rem; background-color: #0b0b0b; border: 1px solid #222; color: #f4f4f4; border-radius: 4px;">
        </div>

        <div style="margin-bottom: 1.2rem;">
            <label style="display: block; font-size: 0.85rem; color: #888; margin-bottom: 0.4rem;">Description & Specs</label>
            <textarea name="description" rows="4" placeholder="Condition, warranty, specs, pickup/shipping details..." style="width: 100%; padding: 0.8rem; background-color: #0b0b0b; border: 1px solid #222; color: #f4f4f4; border-radius: 4px; resize: vertical;"></textarea>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.85rem; color: #888; margin-bottom: 0.4rem;">Product Image (JPG, PNG, WEBP - Max 5MB)</label>
            <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/*" required style="width: 100%; padding: 0.6rem; background-color: #0b0b0b; border: 1px solid #222; color: #f4f4f4; border-radius: 4px;">
        </div>

        <button type="submit" class="btn-green" style="width: 100%; padding: 0.85rem; text-align: center;">Publish Listing</button>
    </form>
</div>

<?php include 'footer.php'; ?>