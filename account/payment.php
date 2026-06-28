<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'classes/Payment.php';   // class Payment yang sudah implements PaymentInterface

requireCustomer();
$user     = getCurrentUser();
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if (!$order_id) {
    header('Location: orders.php');
    exit();
}

$order = getOrder($order_id);

// Customer can only see their own orders
if (!$order || $order['customer_id'] != $user['id']) {
    header('Location: orders.php');
    exit();
}

$order_items = getOrderItems($order_id);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $order['payment_status'] === 'unpaid') {

    $payment_method = sanitize($_POST['payment_method'] ?? '');

    // Membuat object Payment
    $payment = new Payment(
        $order_id,
        $order['total_amount'],
        $payment_method
    );

    if ($payment->processPayment()) {

        // Update status pembayaran
        $payment->completePayment();

        header('Location: orders.php?paid=1');
        exit();

    } else {

        $error = 'Pembayaran gagal. Coba lagi.';

    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran #<?= $order_id ?> — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include 'includes/nav_customer.php'; ?>

<main>
    <section class="section">
        <div class="container">
            <h2 class="section-title">Pembayaran Pesanan #<?= $order_id ?></h2>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>

            <div class="checkout-grid">
                <div class="card">
                    <h3 class="card-title">Detail Pesanan</h3>
                    <p><strong>Status Pembayaran:</strong>
                        <span class="badge badge--<?= $order['payment_status'] === 'paid' ? 'success' : 'warning' ?>">
                            <?= $order['payment_status'] === 'paid' ? 'Sudah Dibayar' : 'Belum Dibayar' ?>
                        </span>
                    </p>
                    <p><strong>Status Pesanan:</strong>
                        <span class="badge badge--<?= $order['status'] ?>">
                            <?= ucfirst($order['status']) ?>
                        </span>
                    </p>
                    <p><strong>Total:</strong> Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></p>
                    <table class="data-table" style="margin-top:16px">
                        <thead><tr><th>Produk</th><th>Qty</th><th>Harga</th></tr></thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['name']) ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td>Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td colspan="2" style="text-align:right"><strong>Total</strong></td>
                                <td><strong>Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <?php if ($order['payment_status'] === 'unpaid'): ?>
                    <div class="card">
                        <h3 class="card-title">Selesaikan Pembayaran</h3>
                        <div class="info-box">
                            <p><strong>Transfer Bank:</strong></p>
                            <p>Bank BCA — No. Rek: <strong>1234567890</strong></p>
                            <p>a/n <?= SITE_NAME ?></p>
                            <hr style="margin: 12px 0; border-color: var(--color-border);">
                            <p><small>Setelah melakukan transfer, klik tombol "Konfirmasi Bayar" untuk menyelesaikan pesanan.</small></p>
                        </div>
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="payment_method">Metode Pembayaran</label>
                                <select id="payment_method" name="payment_method" required>
                                    <option value="bank_transfer">Transfer Bank</option>
                                    <option value="e_wallet">E-Wallet</option>
                                </select>
                            </div>
                            <div class="action-bar">
                                <a href="orders.php" class="btn btn-secondary">Nanti</a>
                                <button type="submit" class="btn btn-primary">Konfirmasi Bayar</button>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="card card--success">
                        <h3>Pembayaran Berhasil!</h3>
                        <p>Pesanan Anda telah selesai diproses.</p>
                        <div style="margin-top: 12px; padding: 12px; background: #f0fdf4; border-radius: 8px;">
                            <p style="font-size: 0.9rem; color: #166534;">
                                <strong>Status Pesanan:</strong> 
                                <span class="badge badge--completed">Selesai</span>
                            </p>
                        </div>
                        <a href="orders.php" class="btn btn-primary" style="margin-top: 16px;">Lihat Pesanan</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
</body>
</html>