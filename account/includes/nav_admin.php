<header class="header">
    <div class="container">
        <div class="header-content">
            <div class="brand">
                <span class="brand-icon">🍽️</span>
                <span class="brand-name"><?= SITE_NAME ?></span>
                <span class="role-badge role-badge--admin">Admin</span>
            </div>
            <nav class="nav">
                <a href="admin.php" <?= basename($_SERVER['PHP_SELF']) === 'admin.php' ? 'class="active"' : '' ?>>Dashboard</a>
                <a href="manage-products.php" <?= basename($_SERVER['PHP_SELF']) === 'manage-products.php' ? 'class="active"' : '' ?>>Produk</a>
                <a href="manage-users.php" <?= basename($_SERVER['PHP_SELF']) === 'manage-users.php' ? 'class="active"' : '' ?>>Pengguna</a>
                <a href="orders-admin.php" <?= basename($_SERVER['PHP_SELF']) === 'orders-admin.php' ? 'class="active"' : '' ?>>Pesanan</a>
                <a href="reports.php" <?= basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'class="active"' : '' ?>>Laporan</a>
                <a href="http://localhost/phpmyadmin" target="_blank" class="btn-db">DB Admin ↗</a>
            </nav>
            <div class="header-user">
                <span>👤 <?= htmlspecialchars($user['name']) ?></span>
                <a href="logout.php" class="btn-logout">Keluar</a>
            </div>
        </div>
    </div>
</header>
