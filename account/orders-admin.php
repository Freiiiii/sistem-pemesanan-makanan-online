<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireAdmin();
$user   = getCurrentUser();
$orders = getOrders();

// View single order detail
$view_id = isset($_GET['view']) ? (int)$_GET['view'] : 0;
$view_order       = null;
$view_order_items = [];
if ($view_id) {
    $view_order       = getOrder($view_id);
    $view_order_items = $view_order ? getOrderItems($view_id) : [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semua Pesanan — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include 'includes/nav_admin.php'; ?>

<main>
    <section class="section">
        <div class="container">
            <?php if ($view_order): ?>
                <div class="page-header">
                    <h2>Detail Pesanan #<?= $view_id ?></h2>
                    <a href="orders-admin.php" class="btn btn-secondary btn-sm">← Semua Pesanan</a>
                </div>
                <div class="card" style="margin-bottom:20px">
                    <div class="order-info-grid">
                        <div>
                            <p><strong>Pelanggan:</strong> <?= htmlspecialchars($view_order['customer_name']) ?></p>
                            <p><strong>Tanggal:</strong> <?= date('d M Y H:i', strtotime($view_order['order_date'])) ?></p>
                        </div>
                        <div>
                            <p><strong>Status:</strong> <span class="badge badge--<?= $view_order['status'] ?>"><?= ucfirst($view_order['status']) ?></span></p>
                            <p><strong>Pembayaran:</strong> <span class="badge badge--<?= $view_order['payment_status'] === 'paid' ? 'success' : 'warning' ?>"><?= $view_order['payment_status'] === 'paid' ? 'Lunas' : 'Belum Bayar' ?></span></p>
                        </div>
                        <div>
                            <p><strong>Alamat:</strong> <?= htmlspecialchars($view_order['shipping_address']) ?></p>
                            <p><strong>Total:</strong> Rp <?= number_format($view_order['total_amount'], 0, ',', '.') ?></p>
                        </div>
                    </div>
                </div>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead><tr><th>Produk</th><th>Qty</th><th>Harga</th><th>Subtotal</th><th>Status Item</th></tr></thead>
                        <tbody>
                            <?php foreach ($view_order_items as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['name']) ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td>Rp. <?= number_format($item['price'], 0, ',', '.') ?></td>
                                    <td>Rp. <?= number_format($item['quantity'] * $item['price'], 0, ',', '.') ?></td>
                                    <td><span class="badge badge--<?= $item['status'] ?>"><?= ucfirst($item['status']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td colspan="3"><strong>Total</strong></td>
                                <td colspan="2"><strong>Rp <?= number_format($view_order['total_amount'], 0, ',', '.') ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php else: ?>
                <h2 class="section-title">Semua Pesanan</h2>
                <?php if (empty($orders)): ?>
                    <div class="empty-state"><p>Belum ada pesanan.</p></div>
                <?php else: ?>
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Pelanggan</th>
                                    <th>Total</th>
                                    <th>Metode</th>
                                    <th>Status</th>
                                    <th>Pembayaran</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?= $order['id'] ?></td>
                                        <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                        <td>Rp. <?= number_format($order['total_amount'], 0, ',', '.') ?></td>
                                        <td><?= ucfirst(str_replace('_', ' ', $order['payment_method'])) ?></td>
                                        <td><span class="badge badge--<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></td>
                                        <td><span class="badge badge--<?= $order['payment_status'] === 'paid' ? 'success' : 'warning' ?>"><?= $order['payment_status'] === 'paid' ? 'Lunas' : 'Belum Bayar' ?></span></td>
                                        <td><?= date('d M Y', strtotime($order['order_date'])) ?></td>
                                        <td><a href="orders-admin.php?view=<?= $order['id'] ?>" class="btn btn-secondary btn-sm">Detail</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?> <!-- footer -->
</body>
</html>
