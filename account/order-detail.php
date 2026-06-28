<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireCustomer();
$user     = getCurrentUser();
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$order_id) { header('Location: orders.php'); exit(); }

$order = getOrder($order_id);
if (!$order || $order['customer_id'] != $user['id']) {
    header('Location: orders.php'); exit();
}

// Cancel single item
if (isset($_GET['cancel_item'], $_GET['item_id'])) {
    $item_id     = (int)$_GET['item_id'];
    $order_items = getOrderItems($order_id);
    foreach ($order_items as $item) {
        if ($item['id'] == $item_id && canCancelOrderItem($item['status'])) {
            updateOrderItemStatus($item_id, 'cancelled');
            break;
        }
    }
    header('Location: order-detail.php?id=' . $order_id);
    exit();
}

// Complete single item
if (isset($_GET['complete_item'], $_GET['item_id'])) {
    $item_id     = (int)$_GET['item_id'];
    $order_items = getOrderItems($order_id);
    foreach ($order_items as $item) {
        if ($item['id'] == $item_id && canCompleteOrderItem($item['status'])) {
            updateOrderItemStatus($item_id, 'completed');
            break;
        }
    }
    header('Location: order-detail.php?id=' . $order_id);
    exit();
}

// Cancel all
if (isset($_GET['cancel_all'])) {
    $order_items  = getOrderItems($order_id);
    $can_cancel   = array_reduce($order_items, fn($c, $i) => $c && canCancelOrderItem($i['status']), true);
    if ($can_cancel && !empty($order_items)) {
        updateAllOrderItemsStatus($order_id, 'cancelled');
        updateOrderStatus($order_id, 'cancelled');
    }
    header('Location: order-detail.php?id=' . $order_id);
    exit();
}

// Complete all
if (isset($_GET['complete_all'])) {
    $order_items  = getOrderItems($order_id);
    $can_complete = array_reduce($order_items, fn($c, $i) => $c && canCompleteOrderItem($i['status']), true);
    if ($can_complete && !empty($order_items)) {
        updateAllOrderItemsStatus($order_id, 'completed');
        updateOrderStatus($order_id, 'completed');
    }
    header('Location: order-detail.php?id=' . $order_id);
    exit();
}

$order_items    = getOrderItems($order_id);
$can_cancel_any  = false;
$can_complete_any = false;
$all_pending     = true;
$all_processing  = true;

foreach ($order_items as $item) {
    if (canCancelOrderItem($item['status']))  $can_cancel_any  = true; else $all_pending    = false;
    if (canCompleteOrderItem($item['status'])) $can_complete_any = true; else $all_processing = false;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan #<?= $order_id ?> — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include 'includes/nav_customer.php'; ?>

<main>
    <section class="section">
        <div class="container">
            <div class="page-header">
                <h2>Detail Pesanan #<?= $order_id ?></h2>
                <a href="orders.php" class="btn btn-secondary btn-sm">← Kembali</a>
            </div>

            <div class="card" style="margin-bottom:20px">
                <div class="order-info-grid">
                    <div>
                        <p><strong>Tanggal:</strong> <?= date('d M Y H:i', strtotime($order['order_date'])) ?></p>
                        <p><strong>Status Pesanan:</strong>
                            <span class="badge badge--<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>
                        </p>
                    </div>
                    <div>
                        <p><strong>Metode Bayar:</strong> <?= ucfirst(str_replace('_', ' ', $order['payment_method'])) ?></p>
                        <p><strong>Status Bayar:</strong>
                            <span class="badge badge--<?= $order['payment_status'] === 'paid' ? 'success' : 'warning' ?>">
                                <?= $order['payment_status'] === 'paid' ? 'Lunas' : 'Belum Bayar' ?>
                            </span>
                        </p>
                    </div>
                    <div>
                        <p><strong>Alamat:</strong> <?= htmlspecialchars($order['shipping_address']) ?></p>
                    </div>
                </div>
            </div>

            <?php if ($can_cancel_any || $can_complete_any): ?>
                <div class="bulk-actions">
                    <?php if ($can_cancel_any && $all_pending): ?>
                        <a href="order-detail.php?id=<?= $order_id ?>&cancel_all=1" class="btn btn-danger"
                           onclick="return confirm('Batalkan semua item?')">Batalkan Semua</a>
                    <?php endif; ?>
                    <?php if ($can_complete_any && $all_processing): ?>
                        <a href="order-detail.php?id=<?= $order_id ?>&complete_all=1" class="btn btn-success"
                           onclick="return confirm('Selesaikan semua item?')">Selesaikan Semua</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Jumlah</th>
                            <th>Harga</th>
                            <th>Subtotal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td>Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                                <td>Rp <?= number_format($item['quantity'] * $item['price'], 0, ',', '.') ?></td>
                                <td><span class="badge badge--<?= $item['status'] ?>"><?= ucfirst($item['status']) ?></span></td>
                                <td>
                                    <?php if (canCancelOrderItem($item['status'])): ?>
                                        <a href="order-detail.php?id=<?= $order_id ?>&cancel_item=1&item_id=<?= $item['id'] ?>"
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Batalkan item ini?')">Batalkan</a>
                                    <?php endif; ?>
                                    <?php if (canCompleteOrderItem($item['status'])): ?>
                                        <a href="order-detail.php?id=<?= $order_id ?>&complete_item=1&item_id=<?= $item['id'] ?>"
                                           class="btn btn-success btn-sm"
                                           onclick="return confirm('Tandai selesai?')">Selesai</a>
                                    <?php endif; ?>
                                    <?php if (!canCancelOrderItem($item['status']) && !canCompleteOrderItem($item['status'])): ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="3"><strong>Total</strong></td>
                            <td colspan="3"><strong>Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="action-bar" style="margin-top:20px">
                <a href="orders.php" class="btn btn-secondary">← Semua Pesanan</a>
                <?php if ($order['payment_status'] === 'unpaid' && $order['status'] !== 'cancelled'): ?>
                    <a href="payment.php?order_id=<?= $order_id ?>" class="btn btn-primary">Bayar Sekarang</a>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
</body>
</html>
