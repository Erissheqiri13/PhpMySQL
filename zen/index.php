<?php
require_once 'db.php';
include 'header.php';

$selected_category = $_GET['category'] ?? '';
$search_query      = trim($_GET['search'] ?? '');

// Find highest price in database to set slider max dynamically
$max_db_price = (float)($pdo->query("SELECT COALESCE(MAX(price), 5000) FROM products")->fetchColumn() ?: 5000);
$slider_max   = max(100, (int)ceil($max_db_price / 10) * 10);

$min_price = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? max(0, (float)$_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? min((float)$slider_max, max(0, (float)$_GET['max_price'])) : (float)$slider_max;

if ($min_price > $max_price) {
    $temp = $min_price;
    $min_price = $max_price;
    $max_price = $temp;
}

$allowed_categories = [
    'pc'       => 'Gaming PCs',
    'gpu'      => 'GPUs',
    'monitor'  => 'Monitors',
    'keyboard' => 'Keyboards',
    'mouse'    => 'Mice',
];

$sql    = "SELECT products.*, users.username AS seller_name 
           FROM products 
           LEFT JOIN users ON products.userid = users.id 
           WHERE products.price BETWEEN ? AND ?";
$params = [$min_price, $max_price];

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

function build_index_url(array $overrides = []): string
{
    $query = array_merge([
        'category'  => $_GET['category'] ?? '',
        'search'    => $_GET['search'] ?? '',
        'min_price' => $_GET['min_price'] ?? '',
        'max_price' => $_GET['max_price'] ?? '',
    ], $overrides);

    $cleaned = array_filter($query, fn($v) => $v !== '' && $v !== null);
    return 'index.php' . (!empty($cleaned) ? '?' . http_build_query($cleaned) : '');
}
?>

<style>
    .filter-pill:hover {
        border-color: #00ff66 !important;
        color: #00ff66 !important;
    }

    .product-card:hover {
        border-color: #00ff66 !important;
    }

    input[type="range"].zen-slider {
        accent-color: #00ff66;
        cursor: pointer;
    }

    .buy-glow-btn:hover {
        box-shadow: 0 0 22px rgba(0, 255, 102, 0.65) !important;
        filter: brightness(1.06);
    }

    .contact-glow-btn:hover {
        box-shadow: 0 0 22px rgba(59, 130, 246, 0.7) !important;
        filter: brightness(1.08);
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
<div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
    <div style="display: flex; flex-wrap: wrap; gap: 0.6rem; align-items: center;">
        <a href="<?= htmlspecialchars(build_index_url(['category' => ''])) ?>"
            class="filter-pill"
            style="padding: 0.45rem 1rem; border-radius: 999px; text-decoration: none; font-size: 0.85rem; font-weight: 600; transition: 0.2s; <?= $selected_category === '' ? 'background-color: #00ff66; color: #000; border: 1px solid #00ff66;' : 'background-color: #141414; color: #f4f4f4; border: 1px solid #222;' ?>">
            All Gear
        </a>

        <?php foreach ($allowed_categories as $slug => $label):
            $is_active = ($selected_category === $slug);
        ?>
            <a href="<?= htmlspecialchars(build_index_url(['category' => $slug])) ?>"
                class="filter-pill"
                style="padding: 0.45rem 1rem; border-radius: 999px; text-decoration: none; font-size: 0.85rem; font-weight: 600; transition: 0.2s; <?= $is_active ? 'background-color: #00ff66; color: #000; border: 1px solid #00ff66;' : 'background-color: #141414; color: #f4f4f4; border: 1px solid #222;' ?>">
                <?= htmlspecialchars($label) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <form method="GET" action="index.php" style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center;">
        <?php if ($selected_category !== ''): ?>
            <input type="hidden" name="category" value="<?= htmlspecialchars($selected_category) ?>">
        <?php endif; ?>
        <input type="hidden" id="hiddenMinPrice" name="min_price" value="<?= htmlspecialchars((string)$min_price) ?>">
        <input type="hidden" id="hiddenMaxPrice" name="max_price" value="<?= htmlspecialchars((string)$max_price) ?>">

        <input type="text" name="search" value="<?= htmlspecialchars($search_query) ?>" placeholder="Search hardware..."
            style="padding: 0.5rem 0.85rem; background-color: #141414; border: 1px solid #222; color: #f4f4f4; border-radius: 4px; font-size: 0.9rem;">
        <button type="submit" class="btn-green" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Filter</button>
    </form>
</div>

<!-- Min to Max Price Slider Bar -->
<div style="background-color: #141414; border: 1px solid #222; border-radius: 6px; padding: 1.1rem 1.4rem; margin-bottom: 2rem;">
    <form method="GET" action="index.php" id="priceFilterForm" style="display: flex; flex-wrap: wrap; gap: 1.5rem; align-items: center; justify-content: space-between;">
        <?php if ($selected_category !== ''): ?>
            <input type="hidden" name="category" value="<?= htmlspecialchars($selected_category) ?>">
        <?php endif; ?>
        <?php if ($search_query !== ''): ?>
            <input type="hidden" name="search" value="<?= htmlspecialchars($search_query) ?>">
        <?php endif; ?>

        <div style="flex: 1; min-width: 200px;">
            <div style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 0.35rem;">
                <label for="minPriceSlider" style="color: #888;">Min Price</label>
                <span style="color: #00ff66; font-weight: 700;">$<span id="minPriceVal"><?= number_format($min_price, 0) ?></span></span>
            </div>
            <input type="range" id="minPriceSlider" name="min_price" class="zen-slider"
                min="0" max="<?= $slider_max ?>" step="10" value="<?= (int)$min_price ?>"
                style="width: 100%;">
        </div>

        <div style="flex: 1; min-width: 200px;">
            <div style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 0.35rem;">
                <label for="maxPriceSlider" style="color: #888;">Max Price</label>
                <span style="color: #00ff66; font-weight: 700;">$<span id="maxPriceVal"><?= number_format($max_price, 0) ?></span></span>
            </div>
            <input type="range" id="maxPriceSlider" name="max_price" class="zen-slider"
                min="0" max="<?= $slider_max ?>" step="10" value="<?= (int)$max_price ?>"
                style="width: 100%;">
        </div>

        <div style="display: flex; gap: 0.6rem; align-items: center;">
            <button type="submit" class="btn-green" style="padding: 0.5rem 1.1rem; font-size: 0.85rem;">Apply Price</button>
            <a href="index.php" style="color: #888; font-size: 0.85rem; text-decoration: none;">Reset</a>
        </div>
    </form>
</div>

<script>
    const minSlider = document.getElementById('minPriceSlider');
    const maxSlider = document.getElementById('maxPriceSlider');
    const minLabel = document.getElementById('minPriceVal');
    const maxLabel = document.getElementById('maxPriceVal');
    const hiddenMin = document.getElementById('hiddenMinPrice');
    const hiddenMax = document.getElementById('hiddenMaxPrice');

    function syncPriceSliders() {
        let minVal = parseInt(minSlider.value, 10);
        let maxVal = parseInt(maxSlider.value, 10);

        if (minVal > maxVal) {
            minSlider.value = maxVal;
            minVal = maxVal;
        }

        minLabel.textContent = minVal.toLocaleString();
        maxLabel.textContent = maxVal.toLocaleString();
        if (hiddenMin) hiddenMin.value = minVal;
        if (hiddenMax) hiddenMax.value = maxVal;
    }

    minSlider.addEventListener('input', syncPriceSliders);
    maxSlider.addEventListener('input', syncPriceSliders);
</script>

<!-- Listings Grid -->
<?php if (count($products) === 0): ?>
    <div style="text-align: center; padding: 4rem 1rem; background-color: #141414; border: 1px solid #222; border-radius: 6px;">
        <p style="color: #888; font-size: 1rem; margin-bottom: 1rem;">No gear matches your current filter.</p>
        <a href="index.php" style="color: #00ff66; text-decoration: none; font-weight: 600;">Reset filters</a>
    </div>
<?php else: ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
        <?php foreach ($products as $item):
            $cat_label   = $allowed_categories[$item['category']] ?? strtoupper($item['category']);
            $has_img     = (!empty($item['productimage']) && file_exists(__DIR__ . '/' . $item['productimage']));
            $img_src     = $has_img ? htmlspecialchars($item['productimage']) : '';
            $seller_name = $item['seller_name'] ?? 'Zen Member';
            $is_zen_user = (strtoupper(trim($seller_name)) === 'ZEN') || ((int)($item['userid'] ?? 0) === 1);
            $seller_color = $is_zen_user ? '#00ff66' : '#f4f4f4';
        ?>
            <div class="product-card"
                onclick="openProductModal(this)"
                data-title="<?= htmlspecialchars($item['title'], ENT_QUOTES) ?>"
                data-category="<?= htmlspecialchars($cat_label, ENT_QUOTES) ?>"
                data-price="$<?= number_format((float)$item['price'], 2) ?>"
                data-description="<?= htmlspecialchars($item['description'] ?? '', ENT_QUOTES) ?>"
                data-seller="<?= htmlspecialchars($seller_name, ENT_QUOTES) ?>"
                data-seller-color="<?= htmlspecialchars($seller_color, ENT_QUOTES) ?>"
                data-image="<?= $img_src ?>"
                style="background-color: #141414; border: 1px solid #222; border-radius: 6px; overflow: hidden; display: flex; flex-direction: column; transition: border-color 0.2s ease; cursor: pointer;">
                <div style="width: 100%; height: 200px; background-color: #0b0b0b; overflow: hidden; border-bottom: 1px solid #222;">
                    <?php if ($has_img): ?>
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

                    <div style="padding-top: 0.85rem; border-top: 1px solid #222; display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; color: #888;">
                        <span>Seller: <strong style="color: <?= $seller_color ?>;"><?= htmlspecialchars($seller_name) ?></strong></span>
                        <span><?= !empty($item['created_at']) ? date('M j', strtotime($item['created_at'])) : 'Listed' ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Marketplace Listing Detail Popup -->
<div id="productModal" onclick="if(event.target === this) closeProductModal();" style="display: none; position: fixed; inset: 0; background-color: rgba(0, 0, 0, 0.8); z-index: 1500; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background-color: #141414; border: 1px solid #222; border-radius: 8px; max-width: 520px; width: 100%; overflow: hidden;">
        <div style="width: 100%; height: 240px; background-color: #0b0b0b; border-bottom: 1px solid #222; position: relative; display: flex; align-items: center; justify-content: center;">
            <img id="modalProdImg" src="" alt="" style="display: none; width: 100%; height: 100%; object-fit: cover;">
            <span id="modalNoImg" style="color: #555; font-size: 0.9rem;">No Image Available</span>
            <button type="button" onclick="closeProductModal()" style="position: absolute; top: 0.75rem; right: 0.75rem; background-color: rgba(11,11,11,0.85); border: 1px solid #333; color: #f4f4f4; width: 32px; height: 32px; border-radius: 4px; font-size: 1.1rem; cursor: pointer;">&times;</button>
        </div>

        <div style="padding: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.65rem;">
                <span id="modalProdCategory" style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #00ff66; background-color: rgba(0, 255, 102, 0.1); padding: 0.25rem 0.6rem; border-radius: 4px;"></span>
                <span id="modalProdPrice" style="font-size: 1.35rem; font-weight: 800; color: #f4f4f4;"></span>
            </div>

            <h2 id="modalProdTitle" style="font-size: 1.45rem; font-weight: 800; color: #f4f4f4; margin-bottom: 0.65rem;"></h2>

            <p id="modalProdDesc" style="color: #aaa; font-size: 0.92rem; line-height: 1.55; margin-bottom: 1rem;"></p>

            <div style="font-size: 0.85rem; color: #888; padding-bottom: 1.25rem; border-bottom: 1px solid #222; margin-bottom: 1.25rem;">
                Listed by: <strong id="modalProdSeller"></strong>
            </div>

            <div style="display: flex; gap: 0.85rem;">
                <button type="button"
                    class="buy-glow-btn"
                    style="flex: 1; background-color: #00ff66; color: #000; border: none; padding: 0.75rem 1rem; border-radius: 5px; font-weight: 800; font-size: 0.92rem; cursor: pointer; transition: box-shadow 0.25s ease, filter 0.25s ease;">
                    Buy
                </button>
                <button type="button"
                    class="contact-glow-btn"
                    style="flex: 1; background-color: #3b82f6; color: #fff; border: none; padding: 0.75rem 1rem; border-radius: 5px; font-weight: 800; font-size: 0.92rem; cursor: pointer; transition: box-shadow 0.25s ease, filter 0.25s ease;">
                    Contact
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const productModal = document.getElementById('productModal');

    function openProductModal(card) {
        const imgSrc = card.dataset.image;
        const imgEl = document.getElementById('modalProdImg');
        const noImg = document.getElementById('modalNoImg');

        if (imgSrc) {
            imgEl.src = imgSrc;
            imgEl.alt = card.dataset.title;
            imgEl.style.display = 'block';
            noImg.style.display = 'none';
        } else {
            imgEl.style.display = 'none';
            noImg.style.display = 'inline';
        }

        document.getElementById('modalProdCategory').textContent = card.dataset.category;
        document.getElementById('modalProdPrice').textContent = card.dataset.price;
        document.getElementById('modalProdTitle').textContent = card.dataset.title;
        document.getElementById('modalProdDesc').textContent = card.dataset.description || 'No additional description provided.';

        const sellerEl = document.getElementById('modalProdSeller');
        sellerEl.textContent = card.dataset.seller;
        sellerEl.style.color = card.dataset.sellerColor || '#f4f4f4';

        productModal.style.display = 'flex';
    }

    function closeProductModal() {
        productModal.style.display = 'none';
    }
</script>

<?php include 'footer.php'; ?>