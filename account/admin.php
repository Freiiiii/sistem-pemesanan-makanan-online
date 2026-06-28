<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'classes/Admin.php';

requireAdmin(); // cek role akun, jika bukan admin redirect ke customer.php
$user = getCurrentUser();

// Membuat object Admin (Inheritance)
$admin = new Admin(
    $user['id'],
    $user['username']
);

$users = getUsers();
$orders = getOrders();
$products = getProducts();

$customers = array_filter($users, fn($u) => $u['role'] === 'customer');
$pending_orders = array_filter($orders, fn($o) => $o['status'] === 'pending');
$completed_orders = array_filter($orders, fn($o) => $o['status'] === 'completed');

$total_revenue = array_reduce(
    $orders,
    fn($carry, $order) => $carry + ($order['status'] === 'completed' ? $order['total_amount'] : 0),
    0
);

$recent_orders = array_slice($orders, 0, 5);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include 'includes/nav_admin.php'; ?>

<main>

<section class="section">
<div class="container">

    <h2 class="section-title">Dashboard Admin</h2>

    <!-- Menggunakan object Admin -->
    <p>
        Selamat Datang,
        <strong><?= htmlspecialchars($admin->getUsername()); ?></strong> 👋
    </p>

    <div class="stats-grid">

        <div class="stat-card stat-card--blue">
            <p class="stat-label">Total Pengguna</p>
            <p class="stat-value"><?= count($users) ?></p>
        </div>

        <div class="stat-card stat-card--teal">
            <p class="stat-label">Pelanggan</p>
            <p class="stat-value"><?= count($customers) ?></p>
        </div>

        <div class="stat-card stat-card--orange">
            <p class="stat-label">Total Produk</p>
            <p class="stat-value"><?= count($products) ?></p>
        </div>

        <div class="stat-card stat-card--yellow">
            <p class="stat-label">Pesanan Pending</p>
            <p class="stat-value"><?= count($pending_orders) ?></p>
        </div>

        <div class="stat-card stat-card--green">
            <p class="stat-label">Pesanan Selesai</p>
            <p class="stat-value"><?= count($completed_orders) ?></p>
        </div>

        <div class="stat-card stat-card--purple">
            <p class="stat-label">Total Pendapatan</p>
            <p class="stat-value stat-value--sm">
                Rp <?= number_format($total_revenue, 0, ',', '.') ?>
            </p>
        </div>

    </div>

    <!-- Pesanan Terbaru -->
    <div class="card" style="margin-top:30px">

        <div class="card-header">
            <h3>Pesanan Terbaru</h3>
            <a href="orders-admin.php" class="btn btn-secondary btn-sm">
                Lihat Semua
            </a>
        </div>

        <?php if (empty($orders)): ?>

            <p class="text-muted" style="padding:20px">
                Belum ada pesanan.
            </p>

        <?php else: ?>

            <table class="data-table">

                <thead>
                <tr>
                    <th>ID</th>
                    <th>Pelanggan</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                </thead>

                <tbody>

                <?php foreach ($recent_orders as $order): ?>

                    <tr>

                        <td>#<?= $order['id'] ?></td>

                        <td><?= htmlspecialchars($order['customer_name']) ?></td>

                        <td>
                            Rp <?= number_format($order['total_amount'],0,',','.') ?>
                        </td>

                        <td>
                            <span class="badge badge--<?= $order['status'] ?>">
                                <?= ucfirst($order['status']) ?>
                            </span>
                        </td>

                        <td>
                            <a href="orders-admin.php?view=<?= $order['id'] ?>"
                               class="btn btn-secondary btn-sm">
                                Detail
                            </a>
                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        <?php endif; ?>

    </div>

</div>
</section>

</main>

<?php include 'includes/footer.php'; ?>

</body>
</html>