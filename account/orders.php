<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireCustomer();   // Customer only — admin punya orders-admin.php
$user   = getCurrentUser();
$orders = getOrders($user['id']);

// Auto-complete paid orders that are not yet completed
foreach ($orders as &$order) {
    if (($order['payment_status'] === 'paid' || $order['payment_status'] === 'completed') && $order['status'] !== 'completed') {
        // Update order status to completed
        updateOrderStatus($order['id'], 'completed');
        $order['status'] = 'completed'; // Update the local copy
    }
}
unset($order); // Break the reference

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include 'includes/nav_customer.php'; ?>

<main>
    <section class="section">
        <div class="container">
            <h2 class="section-title">Pesanan Saya</h2>

            <?php if (isset($_GET['paid'])): ?>
                <div class="alert alert-success">Pembayaran berhasil! Pesanan Anda sedang diproses.</div>
            <?php endif; ?>

            <?php if (isset($_GET['auto_completed'])): ?>
                <div class="alert alert-success">Pesanan #<?= htmlspecialchars($_GET['auto_completed']) ?> telah otomatis diselesaikan.</div>
            <?php endif; ?>

            <?php if (empty($orders)): ?>
                <div class="empty-state">
                    <p>Belum ada pesanan.</p>
                    <a href="products.php" class="btn btn-primary">Mulai Belanja</a>
                </div>
            <?php else: ?>
                <div class="orders-list">
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <div class="order-card-header">
                                <div>
                                    <span class="order-id">Pesanan #<?= $order['id'] ?></span>
                                    <span class="order-date"><?= date('d M Y H:i', strtotime($order['order_date'])) ?></span>
                                </div>
                                <div class="order-badges">
                                    <span class="badge badge--<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>
                                    <span class="badge badge--<?= $order['payment_status'] === 'paid' ? 'success' : 'warning' ?>">
                                        <?= $order['payment_status'] === 'paid' ? 'Lunas' : 'Belum Bayar' ?>
                                    </span>
                                </div>
                            </div>
                            <div class="order-card-body">
                                <p><strong>Total:</strong> Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></p>
                                <p><strong>Metode:</strong> <?= ucfirst(str_replace('_', ' ', $order['payment_method'])) ?></p>
                                <p><strong>Alamat:</strong> <?= htmlspecialchars($order['shipping_address']) ?></p>
                            </div>
                            <div class="order-card-footer">
                                <a href="order-detail.php?id=<?= $order['id'] ?>" class="btn btn-secondary btn-sm">Lihat Detail</a>
                                <?php if ($order['payment_status'] === 'unpaid' && $order['status'] !== 'cancelled'): ?>
                                    <a href="payment.php?order_id=<?= $order['id'] ?>" class="btn btn-primary btn-sm">Bayar Sekarang</a>
                                <?php endif; ?>
                                <?php if ($order['status'] === 'completed'): ?>
                                    <span class="badge badge--success" style="padding: 5px 12px;">✓ Selesai</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
</body>
</html>