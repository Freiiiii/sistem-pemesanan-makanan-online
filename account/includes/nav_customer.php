<?php $cart_count = isset($user) ? count(getCart($user['id'])) : 0; ?>
<header class="header">
    <div class="container">
        <div class="header-content">
            <div class="brand">
                <span class="brand-icon">🍽️</span>
                <span class="brand-name"><?= SITE_NAME ?></span>
            </div>
            <nav class="nav">
                <a href="customer.php" <?= basename($_SERVER['PHP_SELF']) === 'customer.php' ? 'class="active"' : '' ?>>Beranda</a>
                <a href="products.php" <?= basename($_SERVER['PHP_SELF']) === 'products.php' ? 'class="active"' : '' ?>>Produk</a>
                <a href="cart.php" class="nav-cart <?= basename($_SERVER['PHP_SELF']) === 'cart.php' ? 'active' : '' ?>">
                    Keranjang <?php if ($cart_count > 0): ?><span class="cart-badge"><?= $cart_count ?></span><?php endif; ?>
                </a>
                <a href="orders.php" <?= basename($_SERVER['PHP_SELF']) === 'orders.php' ? 'class="active"' : '' ?>>Pesanan Saya</a>
            </nav>
            <div class="header-user">
                <span>👤 <?= htmlspecialchars($user['name']) ?></span>
                <a href="logout.php" class="btn-logout">Keluar</a>
            </div>
        </div>
    </div>
</header>
