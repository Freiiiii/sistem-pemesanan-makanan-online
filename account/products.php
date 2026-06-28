<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireCustomer();
$user = getCurrentUser();

// Handle add to cart
if (isset($_GET['add'])) {
    $product_id = (int)$_GET['add'];
    $quantity   = max(1, (int)($_GET['qty'] ?? 1));
    addToCart($user['id'], $product_id, $quantity);
    
    // Preserve filter parameters when redirecting
    $redirect = 'products.php?added=1';
    if (isset($_GET['category']) && $_GET['category'] !== '') {
        $redirect .= '&category=' . (int)$_GET['category'];
    }
    if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
        $redirect .= '&search=' . urlencode($_GET['search']);
    }
    header('Location: ' . $redirect);
    exit();
}

// Get filter parameters
$category_id = isset($_GET['category']) && $_GET['category'] !== '' ? (int)$_GET['category'] : null;
$search      = isset($_GET['search']) && !empty(trim($_GET['search'])) ? trim($_GET['search']) : null;

// Get products with filters
$products = getProducts($category_id, $search);
$categories = getCategories();

// Get cart count for display
$cart_items = getCart($user['id']);
$cart_count = count($cart_items);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include 'includes/nav_customer.php'; ?>

<main>
    <section class="section">
        <div class="container">
            <h2 class="section-title">Produk</h2>

            <!-- Search and Filter Form -->
            <div class="search-filter">
                <form method="GET" action="products.php">
                    <div class="filter-row">
                        <!-- Search + Filter-->
                        <div class="search-filter">
                            <input type="text" name="search" placeholder="Cari produk..." value="<?= htmlspecialchars($search ?? '') ?>">
                                <select name="category" onchange="this.form.submit()">
                                <option value="">Semua</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= ($category_id == $cat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Search Button -->
                        <div class="filter-group button-group">
                            <button type="submit" class="btn btn-primary">Cari</button>
                        </div>

                    <!-- Keluar kalo produk ditambahkan ke Cart -->
                    <?php if (isset($_GET['added'])): ?>
                        <div class="alert alert-success">Produk ditambahkan ke keranjang!</div>
                    <?php endif; ?>

                        </div>
                    </div>
                </form>
            </div>

            <section class="section">
            <div class="container">
            <!-- Filter Info, keluar kalo ada filter aktif -->
            <?php if ($category_id): ?>
                <?php 
                $category_name = '';
                foreach ($categories as $cat) {
                    if ($cat['id'] == $category_id) {
                        $category_name = $cat['name'];
                        break;
                    }
                }
                ?>
                <div class="filter-info">
                    Menampilkan produk dari kategori: <strong><?= htmlspecialchars($category_name) ?></strong>
                </div>
            <?php endif; ?>

            <!-- Search info, keluar kalo ada text di search bar -->
            <?php if ($search): ?>
                <div class="filter-info">
                    Hasil pencarian untuk: <strong>"<?= htmlspecialchars($search) ?>"</strong>
                </div>
            <?php endif; ?>
            <?php if (empty($products)): ?> <!-- Kalo ga ada produk, show produk tidak ditemukan + reset button -->
                <div class="empty-state">
                    <p>Produk tidak ditemukan.</p>
                    <a href="products.php" class="btn btn-primary">Lihat Semua</a>
                </div>
            <?php else: ?> <!-- Kalo ada produk, show product -->
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <?php if (!empty($product['image'])): ?>
                                <?= displayProductImage($product['image'], $product['name'], 'product-image') ?>
                            <?php else: ?>
                                <div class="no-image-placeholder">Tidak ada gambar</div>
                            <?php endif; ?>
                            <div class="product-card-body">
                                <span class="product-category"><?= htmlspecialchars($product['category_name'] ?? 'Umum') ?></span>
                                <h3><?= htmlspecialchars($product['name']) ?></h3>
                                <?php if (!empty($product['description'])): ?>
                                    <p class="product-desc"><?= htmlspecialchars(mb_substr($product['description'], 0, 80)) ?></p>
                                <?php endif; ?>
                                <p class="product-price">Rp. <?= number_format($product['price'], 0, ',', '.') ?></p>
                                <p class="product-stock">Stok: <?= $product['stock'] ?></p>
                                <?php if ($product['stock'] > 0): ?>
                                    <form method="GET" action="" class="add-cart-form">
                                        <input type="hidden" name="add" value="<?= $product['id'] ?>">
                                        <?php if ($category_id): ?><input type="hidden" name="category" value="<?= $category_id ?>"><?php endif; ?>
                                        <input type="number" name="qty" value="1" min="1" max="<?= $product['stock'] ?>" class="qty-input">
                                        <button type="submit" class="btn btn-primary btn-sm">Tambahkan ke Keranjang</button>
                                    </form>
                                <?php else: ?>
                                    <p class="out-of-stock">Stok habis</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            </div>
            </section>
        </div>
    </section>
</main>

<!-- Footer -->
<?php include 'includes/footer.php'; ?>
</body>
</html>