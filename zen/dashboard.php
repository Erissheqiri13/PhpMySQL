<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$error   = '';
$success = '';

$allowed_categories = [
    'pc'       => 'Gaming PCs',
    'gpu'      => 'GPUs',
    'monitor'  => 'Monitors',
    'keyboard' => 'Keyboards',
    'mouse'    => 'Mice',
];

$is_zen_admin = ((int)$_SESSION['user_id'] === 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $product_id = (int)($_POST['product_id'] ?? 0);

        if ($is_zen_admin) {
            $stmt = $pdo->prepare("SELECT productimage FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
        } else {
            $stmt = $pdo->prepare("SELECT productimage FROM products WHERE id = ? AND userid = ?");
            $stmt->execute([$product_id, $user_id]);
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            if ($is_zen_admin) {
                $del = $pdo->prepare("DELETE FROM products WHERE id = ?");
                $ok = $del->execute([$product_id]);
            } else {
                $del = $pdo->prepare("DELETE FROM products WHERE id = ? AND userid = ?");
                $ok = $del->execute([$product_id, $user_id]);
            }

            if ($ok) {
                if (!empty($row['productimage'])) {
                    $img_file = __DIR__ . '/' . $row['productimage'];
                    if (file_exists($img_file) && strpos(realpath($img_file) ?: '', realpath(__DIR__ . '/uploads') ?: '') === 0) {
                        @unlink($img_file);
                    }
                }
                $success = "Listing deleted.";
            } else {
                $error = "Could not delete listing.";
            }
        } else {
            $error = "Listing not found or permission denied.";
        }
    } elseif ($action === 'edit') {
        $product_id  = (int)($_POST['product_id'] ?? 0);
        $title       = trim($_POST['title'] ?? '');
        $category    = trim($_POST['category'] ?? '');
        $price       = trim($_POST['price'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($title === '' || !array_key_exists($category, $allowed_categories) || !is_numeric($price) || (float)$price < 0) {
            $error = "Please enter a valid title, category, and price.";
        } else {
            if ($is_zen_admin) {
                $upd = $pdo->prepare(
                    "UPDATE products SET title = ?, category = ?, price = ?, description = ? WHERE id = ?"
                );
                $ok = $upd->execute([$title, $category, $price, $description, $product_id]);
            } else {
                $upd = $pdo->prepare(
                    "UPDATE products SET title = ?, category = ?, price = ?, description = ? WHERE id = ? AND userid = ?"
                );
                $ok = $upd->execute([$title, $category, $price, $description, $product_id, $user_id]);
            }

            if ($ok) {
                $success = "Listing updated.";
            } else {
                $error = "Could not update listing.";
            }
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE userid = ? ORDER BY id DESC");
$stmt->execute([$user_id]);
$my_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<style>
    .product-card:hover {
        border-color: #00ff66 !important;
    }

    .edit-btn:hover {
        border-color: #00ff66 !important;
        color: #00ff66 !important;
    }

    .del-btn:hover {
        background-color: #dc2626 !important;
    }
</style>

<div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem; padding-bottom: 1.5rem; border-bottom: 1px solid #222; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 2.2rem; font-weight: 800; margin-bottom: 0.25rem;">Your Dashboard</h1>
        <p style="color: #888; font-size: 0.95rem;">Manage your active hardware listings (<?= count($my_products) ?>).</p>
    </div>
    <a href="add-listing.php" class="btn-green">+ Post New Gear</a>
</div>

<?php if ($error): ?>
    <div style="background-color: rgba(255,0,0,0.1); border: 1px solid rgba(255,0,0,0.3); color: #ff6b6b; padding: 0.85rem 1rem; border-radius: 4px; margin-bottom: 1.5rem; font-size: 0.9rem;">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div style="background-color: rgba(0,255,102,0.1); border: 1px solid rgba(0,255,102,0.3); color: #00ff66; padding: 0.85rem 1rem; border-radius: 4px; margin-bottom: 1.5rem; font-size: 0.9rem;">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<?php if (count($my_products) === 0): ?>
    <div style="text-align: center; padding: 4rem 1rem; background-color: #141414; border: 1px solid #222; border-radius: 6px;">
        <p style="color: #888; font-size: 1rem; margin-bottom: 1.25rem;">You haven't posted any listings yet.</p>
        <a href="add-listing.php" class="btn-green">Create Your First Listing</a>
    </div>
<?php else: ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
        <?php foreach ($my_products as $item):
            $cat_label = $allowed_categories[$item['category']] ?? strtoupper($item['category']);
            $img_src   = htmlspecialchars($item['productimage'] ?? '');
        ?>
            <div class="product-card" style="background-color: #141414; border: 1px solid #222; border-radius: 6px; overflow: hidden; display: flex; flex-direction: column; transition: border-color 0.2s ease;">
                <div style="width: 100%; height: 200px; background-color: #0b0b0b; overflow: hidden; border-bottom: 1px solid #222;">
                    <?php if ($img_src !== '' && file_exists(__DIR__ . '/' . $item['productimage'])): ?>
                        <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($item['title']) ?>"
                            style="width: 100%; height: 100%; object-fit: cover; display: block;">
                    <?php else: ?>
                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #555; font-size: 0.85rem;">
                            No Image Available
                        </div>
                    <?php endif; ?>
                </div>

                <div style="padding: 1.25rem; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.6rem;">
                            <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #00ff66; background-color: rgba(0, 255, 102, 0.1); padding: 0.2rem 0.55rem; border-radius: 4px;">
                                <?= htmlspecialchars($cat_label) ?>
                            </span>
                            <span style="font-size: 1.15rem; font-weight: 800; color: #f4f4f4;">
                                $<?= number_format((float)$item['price'], 2) ?>
                            </span>
                        </div>

                        <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.5rem; color: #f4f4f4;">
                            <?= htmlspecialchars($item['title']) ?>
                            <?php if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === 1): ?>
                                <span style="color: #00ff66; font-weight: 800; font-size: 0.9rem; margin-left: 0.35rem;">ID <?= (int)$item['id'] ?></span>
                            <?php endif; ?>
                        </h3>

                        <?php if (!empty($item['description'])): ?>
                            <p style="color: #888; font-size: 0.88rem; margin-bottom: 1rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                <?= htmlspecialchars($item['description']) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div style="padding-top: 0.9rem; border-top: 1px solid #222; display: flex; gap: 0.6rem;">
                        <button type="button"
                            class="edit-btn"
                            onclick="openEditModal(this)"
                            data-id="<?= (int)$item['id'] ?>"
                            data-title="<?= htmlspecialchars($item['title'], ENT_QUOTES) ?>"
                            data-category="<?= htmlspecialchars($item['category'], ENT_QUOTES) ?>"
                            data-price="<?= htmlspecialchars((string)$item['price'], ENT_QUOTES) ?>"
                            data-description="<?= htmlspecialchars($item['description'] ?? '', ENT_QUOTES) ?>"
                            style="flex: 1; background-color: #0b0b0b; color: #f4f4f4; border: 1px solid #333; padding: 0.5rem; border-radius: 4px; font-weight: 600; font-size: 0.85rem; cursor: pointer; transition: 0.2s;">
                            Edit
                        </button>

                        <button type="button"
                            class="del-btn"
                            onclick="openDeleteModal(<?= (int)$item['id'] ?>, '<?= htmlspecialchars($item['title'], ENT_QUOTES) ?>')"
                            style="flex: 1; background-color: #ef4444; color: #fff; border: none; padding: 0.5rem; border-radius: 4px; font-weight: 600; font-size: 0.85rem; cursor: pointer; transition: 0.2s;">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Delete Confirmation Popup Modal -->
<div id="deleteModal" style="display: none; position: fixed; inset: 0; background-color: rgba(0, 0, 0, 0.75); z-index: 1000; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background-color: #141414; border: 1px solid #222; border-radius: 6px; max-width: 400px; width: 100%; padding: 1.75rem; text-align: center;">
        <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 0.6rem;">Are you sure you want to delete?</h3>
        <p id="deleteItemName" style="color: #888; font-size: 0.9rem; margin-bottom: 1.5rem;"></p>

        <form method="POST" action="dashboard.php" style="display: flex; gap: 0.8rem; justify-content: center;">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="product_id" id="deleteProductId" value="">
            <button type="submit" style="background-color: #ef4444; color: #fff; border: none; padding: 0.6rem 1.5rem; border-radius: 4px; font-weight: 700; cursor: pointer;">
                Yes
            </button>
            <button type="button" onclick="closeDeleteModal()" style="background-color: #0b0b0b; color: #f4f4f4; border: 1px solid #333; padding: 0.6rem 1.5rem; border-radius: 4px; font-weight: 600; cursor: pointer;">
                No
            </button>
        </form>
    </div>
</div>

<!-- Edit Listing Popup Modal -->
<div id="editModal" style="display: none; position: fixed; inset: 0; background-color: rgba(0, 0, 0, 0.75); z-index: 1000; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background-color: #141414; border: 1px solid #222; border-radius: 6px; max-width: 480px; width: 100%; padding: 1.75rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <h3 style="font-size: 1.25rem; font-weight: 800;">Edit Listing</h3>
            <button type="button" onclick="closeEditModal()" style="background: none; border: none; color: #888; font-size: 1.3rem; cursor: pointer;">&times;</button>
        </div>

        <form method="POST" action="dashboard.php">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="product_id" id="editProductId" value="">

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.85rem; color: #888; margin-bottom: 0.35rem;">Title</label>
                <input type="text" name="title" id="editTitle" required style="width: 100%; padding: 0.7rem; background-color: #0b0b0b; border: 1px solid #222; color: #f4f4f4; border-radius: 4px;">
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.85rem; color: #888; margin-bottom: 0.35rem;">Category</label>
                <select name="category" id="editCategory" required style="width: 100%; padding: 0.7rem; background-color: #0b0b0b; border: 1px solid #222; color: #f4f4f4; border-radius: 4px;">
                    <?php foreach ($allowed_categories as $slug => $label): ?>
                        <option value="<?= htmlspecialchars($slug) ?>"><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.85rem; color: #888; margin-bottom: 0.35rem;">Price ($)</label>
                <input type="number" name="price" id="editPrice" step="0.01" min="0" required style="width: 100%; padding: 0.7rem; background-color: #0b0b0b; border: 1px solid #222; color: #f4f4f4; border-radius: 4px;">
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.85rem; color: #888; margin-bottom: 0.35rem;">Description</label>
                <textarea name="description" id="editDescription" rows="3" style="width: 100%; padding: 0.7rem; background-color: #0b0b0b; border: 1px solid #222; color: #f4f4f4; border-radius: 4px; resize: vertical;"></textarea>
            </div>

            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" onclick="closeEditModal()" style="background-color: #0b0b0b; color: #f4f4f4; border: 1px solid #333; padding: 0.6rem 1.2rem; border-radius: 4px; font-weight: 600; cursor: pointer;">
                    Cancel
                </button>
                <button type="submit" class="btn-green" style="padding: 0.6rem 1.4rem;">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const deleteModal = document.getElementById('deleteModal');
    const deleteIdInput = document.getElementById('deleteProductId');
    const deleteNameEl = document.getElementById('deleteItemName');

    const editModal = document.getElementById('editModal');
    const editIdInput = document.getElementById('editProductId');
    const editTitle = document.getElementById('editTitle');
    const editCat = document.getElementById('editCategory');
    const editPrice = document.getElementById('editPrice');
    const editDesc = document.getElementById('editDescription');

    function openDeleteModal(id, title) {
        deleteIdInput.value = id;
        deleteNameEl.textContent = title;
        deleteModal.style.display = 'flex';
    }

    function closeDeleteModal() {
        deleteModal.style.display = 'none';
    }

    function openEditModal(btn) {
        editIdInput.value = btn.dataset.id;
        editTitle.value = btn.dataset.title;
        editCat.value = btn.dataset.category;
        editPrice.value = btn.dataset.price;
        editDesc.value = btn.dataset.description;
        editModal.style.display = 'flex';
    }

    function closeEditModal() {
        editModal.style.display = 'none';
    }
</script>

<?php include 'footer.php'; ?>