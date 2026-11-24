<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$is_zen_admin = isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === 1;
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
        .admin-nav-btn:hover {
            border-color: #00ff66 !important;
            color: #00ff66 !important;
            box-shadow: 0 0 14px rgba(0, 255, 102, 0.25);
        }
        @keyframes zenFadeIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .zen-fade-in {
            animation: zenFadeIn 0.35s ease forwards;
        }
    </style>
</head>

<body>

    <header>
        <a href="index.php" class="logo">ZEN<span>.</span></a>
        <nav style="display: flex; align-items: center;">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($is_zen_admin): ?>
                    <button type="button"
                            class="admin-nav-btn"
                            onclick="openAdminModal()"
                            style="margin-left: 1.5rem; background-color: #0b0b0b; color: #00ff66; border: 1px solid #00ff66; padding: 0.42rem 0.9rem; border-radius: 4px; font-weight: 700; font-size: 0.85rem; cursor: pointer; transition: 0.2s;">
                        Admin Panel
                    </button>
                <?php endif; ?>
                <a href="index.php">Marketplace</a>
                <a href="dashboard.php">Dashboard</a>
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

    <?php if ($is_zen_admin): ?>
    <!-- Admin Product Lookup & Moderation Modal -->
    <div id="adminModal" style="display: none; position: fixed; inset: 0; background-color: rgba(0, 0, 0, 0.8); z-index: 2000; align-items: center; justify-content: center; padding: 1rem;">
        <div style="background-color: #141414; border: 1px solid #222; border-radius: 6px; max-width: 540px; width: 100%; padding: 1.75rem; max-height: 90vh; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.2rem;">
                <h3 style="font-size: 1.25rem; font-weight: 800; color: #00ff66;">ZEN Admin Panel</h3>
                <button type="button" onclick="closeAdminModal()" style="background: none; border: none; color: #888; font-size: 1.4rem; cursor: pointer;">&times;</button>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <input type="number"
                       id="adminProductIdInput"
                       placeholder="Enter Product ID"
                       min="1"
                       style="width: 100%; padding: 0.75rem 0.9rem; background-color: #0b0b0b; border: 1px solid #333; color: #f4f4f4; border-radius: 4px; font-size: 0.95rem;">
            </div>

            <div id="adminStatusMsg" style="color: #888; font-size: 0.88rem; margin-bottom: 0.75rem;"></div>

            <!-- Fades in once a product ID is found -->
            <div id="adminProductResult" style="display: none; background-color: #0b0b0b; border: 1px solid #222; border-radius: 6px; padding: 1.15rem;">
                <div style="display: flex; gap: 1rem; align-items: flex-start; margin-bottom: 1rem;">
                    <img id="adminProdImg" src="" alt="" style="width: 84px; height: 84px; object-fit: cover; border-radius: 4px; border: 1px solid #222; background-color: #141414; flex-shrink: 0;">
                    <div style="flex: 1;">
                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; margin-bottom: 0.3rem;">
                            <h4 id="adminProdTitle" style="font-size: 1.1rem; font-weight: 800; color: #f4f4f4;"></h4>
                            <span id="adminProdPrice" style="color: #00ff66; font-weight: 800; font-size: 1rem;"></span>
                        </div>
                        <p id="adminProdDesc" style="color: #888; font-size: 0.88rem; margin-bottom: 0.45rem;"></p>
                        <div style="font-size: 0.82rem; color: #aaa;">
                            Listed by: <span id="adminProdSeller" style="font-weight: 700;"></span>
                            &nbsp;|&nbsp;
                            Category: <span id="adminProdCategory" style="color: #f4f4f4;"></span>
                        </div>
                    </div>
                </div>

                <!-- Edit Form inside Admin Modal -->
                <form method="POST" action="dashboard.php" id="adminEditForm" style="border-top: 1px solid #222; padding-top: 1rem; margin-bottom: 0.85rem;">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="product_id" id="adminEditId" value="">

                    <div style="margin-bottom: 0.75rem;">
                        <label style="display: block; font-size: 0.8rem; color: #888; margin-bottom: 0.25rem;">Title</label>
                        <input type="text" name="title" id="adminEditTitle" required style="width: 100%; padding: 0.55rem; background-color: #141414; border: 1px solid #222; color: #f4f4f4; border-radius: 4px;">
                    </div>

                    <div style="display: flex; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div style="flex: 1;">
                            <label style="display: block; font-size: 0.8rem; color: #888; margin-bottom: 0.25rem;">Category</label>
                            <select name="category" id="adminEditCategory" required style="width: 100%; padding: 0.55rem; background-color: #141414; border: 1px solid #222; color: #f4f4f4; border-radius: 4px;">
                                <option value="pc">Gaming PCs</option>
                                <option value="gpu">GPUs</option>
                                <option value="monitor">Monitors</option>
                                <option value="keyboard">Keyboards</option>
                                <option value="mouse">Mice</option>
                            </select>
                        </div>
                        <div style="flex: 1;">
                            <label style="display: block; font-size: 0.8rem; color: #888; margin-bottom: 0.25rem;">Price ($)</label>
                            <input type="number" name="price" id="adminEditPrice" step="0.01" min="0" required style="width: 100%; padding: 0.55rem; background-color: #141414; border: 1px solid #222; color: #f4f4f4; border-radius: 4px;">
                        </div>
                    </div>

                    <div style="margin-bottom: 0.85rem;">
                        <label style="display: block; font-size: 0.8rem; color: #888; margin-bottom: 0.25rem;">Description</label>
                        <textarea name="description" id="adminEditDesc" rows="2" style="width: 100%; padding: 0.55rem; background-color: #141414; border: 1px solid #222; color: #f4f4f4; border-radius: 4px; resize: vertical;"></textarea>
                    </div>

                    <div style="display: flex; gap: 0.65rem;">
                        <button type="submit" class="btn-green" style="flex: 1; padding: 0.55rem; font-size: 0.85rem;">Save Edit</button>
                    </div>
                </form>

                <!-- Take Down (Delete) Form inside Admin Modal -->
                <form method="POST" action="dashboard.php" onsubmit="return confirm('Take down this listing permanently?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="product_id" id="adminDeleteId" value="">
                    <button type="submit" style="width: 100%; background-color: #ef4444; color: #fff; border: none; padding: 0.55rem; border-radius: 4px; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                        Take Down Listing
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const adminModal = document.getElementById('adminModal');
        const adminIdInput = document.getElementById('adminProductIdInput');
        const adminStatus = document.getElementById('adminStatusMsg');
        const adminResult = document.getElementById('adminProductResult');

        function openAdminModal() {
            adminModal.style.display = 'flex';
            adminIdInput.value = '';
            adminStatus.textContent = '';
            adminResult.style.display = 'none';
            adminResult.classList.remove('zen-fade-in');
            setTimeout(() => adminIdInput.focus(), 50);
        }

        function closeAdminModal() {
            adminModal.style.display = 'none';
        }

        if (adminIdInput) {
            adminIdInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const id = parseInt(adminIdInput.value.trim(), 10);
                    if (!id || id < 1) {
                        adminStatus.style.color = '#ff6b6b';
                        adminStatus.textContent = 'Enter a valid numeric Product ID.';
                        return;
                    }

                    adminStatus.style.color = '#888';
                    adminStatus.textContent = 'Loading listing #' + id + '...';
                    adminResult.style.display = 'none';
                    adminResult.classList.remove('zen-fade-in');

                    fetch('admin-lookup.php?id=' + encodeURIComponent(id))
                        .then(res => res.json())
                        .then(data => {
                            if (!data.ok || !data.product) {
                                adminStatus.style.color = '#ff6b6b';
                                adminStatus.textContent = data.message || 'Listing not found.';
                                return;
                            }

                            const p = data.product;
                            adminStatus.textContent = '';

                            const imgEl = document.getElementById('adminProdImg');
                            imgEl.src = p.productimage || '';
                            imgEl.alt = p.title;

                            document.getElementById('adminProdTitle').textContent = p.title;
                            document.getElementById('adminProdPrice').textContent = '$' + parseFloat(p.price).toFixed(2);
                            document.getElementById('adminProdDesc').textContent = p.description || 'No description provided.';

                            const sellerEl = document.getElementById('adminProdSeller');
                            const sellerName = p.seller_name || 'Zen Member';
                            sellerEl.textContent = sellerName;
                            sellerEl.style.color = (sellerName.toUpperCase() === 'ZEN' || parseInt(p.userid, 10) === 1) ? '#00ff66' : '#f4f4f4';

                            document.getElementById('adminProdCategory').textContent = p.category_label || p.category;

                            document.getElementById('adminEditId').value = p.id;
                            document.getElementById('adminEditTitle').value = p.title;
                            document.getElementById('adminEditCategory').value = p.category;
                            document.getElementById('adminEditPrice').value = p.price;
                            document.getElementById('adminEditDesc').value = p.description || '';
                            document.getElementById('adminDeleteId').value = p.id;

                            adminResult.style.display = 'block';
                            void adminResult.offsetWidth;
                            adminResult.classList.add('zen-fade-in');
                        })
                        .catch(() => {
                            adminStatus.style.color = '#ff6b6b';
                            adminStatus.textContent = 'Error looking up product.';
                        });
                }
            });
        }
    </script>
    <?php endif; ?>

    <main class="container">