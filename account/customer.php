<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'classes/Customer.php';

requireCustomer(); // cek role akun, jika bukan customer redirect ke admin.php

$user = getCurrentUser();

// Membuat object Customer (Inheritance)
$customer = new Customer(
    $user['id'],
    $user['username']
);

$products = getProducts();
$categories = getCategories();
$featured = array_slice($products, 0, 8); // ambil 8 produk pertama sebagai produk unggulan
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include 'includes/nav_customer.php'; ?>

<main>

    <!-- Hero -->
    <section class="hero">
        <div class="container">

            <h2>
                Selamat Datang,
                <?= htmlspecialchars($customer->getUsername()); ?>! 👋
            </h2>

            <!-- Polymorphism (Method Override) -->
            <p><?= $customer->getDashboard(); ?></p>

            <p>Temukan menu favorit Anda dan pesan sekarang.</p>

            <a href="products.php" class="btn btn-primary">
                Lihat Semua Produk
            </a>

        </div>
    </section>

    <!-- Kategori -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">Kategori</h2>

            <div class="categories-grid">
                <?php foreach ($categories as $cat): ?>
                    <a href="products.php?category=<?= $cat['id'] ?>" class="category-card">
                        <h3><?= htmlspecialchars($cat['name']) ?></h3>
                        <p><?= htmlspecialchars($cat['description'] ?? '') ?></p>
                    </a>
                <?php endforeach; ?>
            </div>

        </div>
    </section>

    <!-- Produk Unggulan -->
    <section class="section">
        <div class="container">

            <h2 class="section-title">Produk Unggulan</h2>

            <div class="products-grid">

                <?php foreach ($featured as $product): ?>

                    <div class="product-card">

                        <?php if (!empty($product['image'])): ?>

                            <?= displayProductImage($product['image'], $product['name'], 'product-image') ?>

                        <?php else: ?>

                            <div class="no-image-placeholder">
                                Tidak ada gambar
                            </div>

                        <?php endif; ?>

                        <div class="product-card-body">

                            <h3><?= htmlspecialchars($product['name']) ?></h3>

                            <p class="product-price">
                                Rp <?= number_format($product['price'], 0, ',', '.') ?>
                            </p>

                            <?php if ($product['stock'] == 0): ?>
                                <span class="out-of-stock">
                                    Stok Habis
                                </span>
                            <?php endif; ?>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        </div>
    </section>

</main>

<?php include 'includes/footer.php'; ?>

</body>
</html>