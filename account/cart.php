<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireCustomer(); // cek role akun, jika bukan customer redirect ke admin.php
$user = getCurrentUser();

// Handle remove from cart
if (isset($_GET['remove'])) {
    removeFromCart((int)$_GET['remove']);
    header('Location: cart.php');
    exit();
}

// Handle clear cart
if (isset($_GET['clear'])) {
    clearCart($user['id']);
    header('Location: cart.php');
    exit();
}
// Get cart items and total
$cart_items = getCart($user['id']);
$total      = getCartTotal($user['id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include 'includes/nav_customer.php'; ?> <!-- customer navbar -->

<main>
    <section class="section">
        <div class="container">
            <h2 class="section-title">Keranjang Belanja</h2>

            <!-- show kalo keranjang kosong -->
            <?php if (empty($cart_items)): ?>
                <div class="empty-state">
                    <p>Keranjang Anda kosong.</p>
                    <a href="products.php" class="btn btn-primary">Belanja Sekarang</a>
                </div>
            <!-- show kalo keranjang ada 1+ item -->
            <?php else: ?>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Jumlah</th>
                                <th>Subtotal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <!-- isi tabel -->
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <tr>
                                    <td class="td-product">
                                        <?php if ($item['image']): ?>
                                            <img src="data:image/jpeg;base64,<?= base64_encode($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="cart-thumb">
                                        <?php endif; ?>
                                        <span><?= htmlspecialchars($item['name']) ?></span>
                                    </td>
                                    <td>Rp. <?= number_format($item['price'], 0, ',', '.') ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td>Rp. <?= number_format($item['quantity'] * $item['price'], 0, ',', '.') ?></td>
                                    <td>
                                        <a href="cart.php?remove=<?= $item['id'] ?>" class="btn btn-danger btn-sm"
                                           onclick="return confirm('Hapus item ini?')">Hapus</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td colspan="3"><strong>Total</strong></td>
                                <td colspan="2"><strong>Rp. <?= number_format($total, 0, ',', '.') ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="action-bar">
                    <div class="action-bar-left">
                        <a href="products.php" class="btn btn-secondary">← Lanjut Belanja</a> <!-- back to products -->
                        <a href="cart.php?clear=1" class="btn btn-danger"
                           onclick="return confirm('Kosongkan keranjang?')">Kosongkan</a> <!-- clear cart -->
                    </div>
                    <a href="checkout.php" class="btn btn-primary">Checkout →</a> <!-- go to checkout -->
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?> <!-- footer -->
</body>
</html>
