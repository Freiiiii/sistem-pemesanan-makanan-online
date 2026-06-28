<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireCustomer();
$user       = getCurrentUser();
$cart_items = getCart($user['id']);
$total      = getCartTotal($user['id']);

if (empty($cart_items)) {
    header('Location: products.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = sanitize($_POST['address'] ?? '');
    $payment_method   = sanitize($_POST['payment_method'] ?? '');

    if (empty($shipping_address)) {
        $error = 'Alamat pengiriman wajib diisi.';
    } else {
        $order_id = createOrder($user['id'], $shipping_address, $payment_method);
        if ($order_id) {
            header('Location: payment.php?order_id=' . $order_id);
            exit();
        } else {
            $error = 'Gagal membuat pesanan. Coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include 'includes/nav_customer.php'; ?>

<main>
    <section class="section">
        <div class="container">
            <h2 class="section-title">Checkout</h2>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>

            <div class="checkout-grid">
                <div class="card">
                    <h3 class="card-title">Ringkasan Pesanan</h3>
                    <table class="data-table">
                        <thead>
                            <tr><th>Produk</th><th>Qty</th><th>Harga</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['name']) ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td>Rp <?= number_format($item['quantity'] * $item['price'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td colspan="2"><strong>Total</strong></td>
                                <td><strong>Rp <?= number_format($total, 0, ',', '.') ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="card">
                    <h3 class="card-title">Pengiriman & Pembayaran</h3>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="address">Alamat Pengiriman</label>
                            <textarea id="address" name="address" rows="3" required
                                      placeholder="Masukkan alamat lengkap..."><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="payment_method">Metode Pembayaran</label>
                            <select id="payment_method" name="payment_method" required>
                                <option value="cash">Bayar di Tempat (COD)</option>
                                <option value="bank_transfer">Transfer Bank</option>
                                <option value="e_wallet">E-Wallet</option>
                            </select>
                        </div>
                        <div class="action-bar">
                            <a href="cart.php" class="btn btn-secondary">← Kembali</a>
                            <button type="submit" class="btn btn-primary">Buat Pesanan →</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
</body>
</html>
