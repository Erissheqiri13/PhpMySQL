<?php 
require_once 'db.php';
include 'header.php'; 

// Get active category filter and search keyword from URL (GET)
$selected_category = $_GET['category'] ?? '';
$search_query      = trim($_GET['search'] ?? '');

$allowed_categories = [
    'pc'       => 'Gaming PCs',
    'gpu'      => 'GPUs',
    'monitor'  => 'Monitors',
    'keyboard' => 'Keyboards',
    'mouse'    => 'Mice',
];

// Build SQL query dynamically with prepared statements
$sql    = "SELECT products.*, users.username AS seller_name 
           FROM products 
           LEFT JOIN users ON products.userid = users.id 
           WHERE 1=1";
$params = [];

if ($selected_category !== '' && array_key_exists($selected_category, $allowed_categories)) {
    $sql .= " AND products.category = ?";
    $params[] = $selected_category;
} else {
    $selected_category = '';
}

if ($search_query !== '') {
    $sql .= " AND (products.title LIKE ? OR products.description LIKE ?)";
    $like     = '%' . $search_query . '%';
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY products.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .filter-pill:hover {
        border-color: #00ff66 !important;
        color: #00ff66 !important;
    }
    .product-card:hover {
        border-color: #00ff66 !important;
    }
</style>

<!-- Hero Banner -->
<div style="text-align: center; padding: 3rem 0 2.5rem 0; border-bottom: 1px solid #222; margin-bottom: 2.5rem;">
    <h1 style="font-size: 3rem; font-weight: 800; margin-bottom: 0.75rem;">Pure Hardware. Zero Clutter.</h1>
    <p style="color: #888; font-size: 1.05rem; margin-bottom: 1.75rem;">Discover, buy, and sell elite gaming PCs, GPUs, and peripherals.</p>

    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="add-listing.php" class="btn-green">Post a Listing</a>
    <?php else: ?>
        <a href="login.php" class="btn-green">Log In to Sell</a>
    <?php endif; ?>
</div>

<!-- Category Filter Pills & Search Bar -->
<div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 2rem;">
    <div style="display: flex; flex-wrap: wrap; gap: 0.6rem; align-items: center;">
        <a href="index.php<?= $search_query !== '' ? '?search=' . urlencode($search_query) : '' ?>"
           class="filter-pill"
           style="padding: 0.45rem 1rem; border-radius: 999px; text-decoration: none; font-size: 0.85rem; font-weight: 600; transition: 0.2s; <?= $selected_category === '' ? 'background-color: #00ff66; color: #000; border: 1px solid #00ff66;' : 'background-color: #141414; color: #f4f4f4; border: 1px solid #222;' ?>">
            All Gear
        </a>

        <?php foreach ($allowed_categories as $slug => $label): 
            $is_active = ($selected_category === $slug);
            $url = 'index.php?category=' . urlencode($slug) . ($search_query !== '' ? '&search=' . urlencode($search_query) : '');
        ?>
            <a href="<?= htmlspecialchars($url) ?>"
               class="filter-pill"
               style="padding: 0.45rem 1rem; border-radius: 999px; text-decoration: none; font-size: 0.85rem; font-weight: 600; transition: 0.2s; <?= $is_active ? 'background-color: #00ff66; color: #000; border: 1px solid #00ff66;' : 'background-color: #141414; color: #f4f4f4; border: 1px solid #222;' ?>">
                <?= htmlspecialchars($label) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <form method="GET" action="index.php" style="display: flex; gap: 0.5rem;">
        <?php if ($selected_category !== ''): ?>
            <input type="hidden" name="category" value="<?= htmlspecialchars($selected_category) ?>">
        <?php endif; ?>
        <input type="text" name="search" value="<?= htmlspecialchars($search_query) ?>" placeholder="Search hardware..."
               style="padding: 0.5rem 0.85rem; background-color: #141414; border: 1px solid #222; color: #f4f4f4; border-radius: 4px; font-size: 0.9rem;">
        <button type="submit" class="btn-green" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Filter</button>
    </form>
</div>

<!-- Listings Grid -->
<?php if (count($products) === 0): ?>
    <div style="text-align: center; padding: 4rem 1rem; background-color: #141414; border: 1px solid #222; border-radius: 6px;">
        <p style="color: #888; font-size: 1rem; margin-bottom: 1rem;">No gear matches your current filter.</p>
        <a href="index.php" style="color: #00ff66; text-decoration: none; font-weight: 600;">Reset filters</a>
    </div>
<?php else: ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
        <?php foreach ($products as $item): 
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
                        </h3>

                        <?php if (!empty($item['description'])): ?>
                            <p style="color: #888; font-size: 0.88rem; margin-bottom: 1rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                <?= htmlspecialchars($item['description']) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div style="padding-top: 0.85rem; border-top: 1px solid #222; display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; color: #888;">
                        <span>Seller: <strong style="color: #f4f4f4;"><?= htmlspecialchars($item['seller_name'] ?? 'Zen Member') ?></strong></span>
                        <span><?= !empty($item['created_at']) ? date('M j', strtotime($item['created_at'])) : 'Listed' ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include 'footer.php'; ?>