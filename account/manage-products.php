<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireAdmin();
$user       = getCurrentUser();
$categories = getCategories();
$error      = '';
$success    = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    // add product
    if ($action === 'add') {
        $category_id = (int)$_POST['category_id'];
        $name        = sanitize($_POST['name'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $price       = (float)$_POST['price'];
        $stock       = (int)$_POST['stock'];
        $image       = null;

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image = file_get_contents($_FILES['image']['tmp_name']);
        }

        if (empty($name) || $price <= 0 || $stock < 0) {
            $error = 'Nama, harga, dan stok wajib diisi dengan benar.';
        } elseif (addProduct($category_id, $name, $description, $price, $stock, $image)) {
            $success = 'Produk berhasil ditambahkan.';
        } else {
            $error = 'Gagal menambahkan produk.';
        }

    // edit product
    } elseif ($action === 'edit') {
        $id          = (int)$_POST['id'];
        $category_id = (int)$_POST['category_id'];
        $name        = sanitize($_POST['name'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $price       = (float)$_POST['price'];
        $stock       = (int)$_POST['stock'];
        $image       = null;

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image = file_get_contents($_FILES['image']['tmp_name']);
        }

        if (empty($name) || $price <= 0) {
            $error = 'Nama dan harga wajib diisi.';
        } elseif (updateProduct($id, $category_id, $name, $description, $price, $stock, $image)) {
            $success = 'Produk berhasil diperbarui.';
        } else {
            $error = 'Gagal memperbarui produk.';
        }

    // delete product
    } elseif ($action === 'delete') {
        $id   = (int)$_POST['id'];
        $conn = getDB();
        $stmt = $conn->prepare("UPDATE products SET deleted = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $success = 'Produk dihapus.';
        } else {
            $error = 'Gagal menghapus produk.';
        }
    }
}

$products = getProducts();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include 'includes/nav_admin.php'; ?> <!-- admin navbar -->

<main>
    <section class="section">
        <div class="container">
            <h2 class="section-title">Kelola Produk</h2>

            <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
            <?php if ($error):   ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>

            <!-- Add Product Form -->
            <div class="card" style="margin-bottom:30px">
                <div class="card-header">
                    <h3>Tambah Produk Baru</h3>
                    <button class="btn btn-secondary btn-sm" onclick="toggleForm('add-form')">Tampilkan / Sembunyikan</button>
                </div>
                <div id="add-form" class="card-body" style="display:none">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Nama Produk</label>
                                <input type="text" name="name" required placeholder="Nama produk">
                            </div>
                            <div class="form-group">
                                <label>Kategori</label>
                                <select name="category_id" required>
                                    <option value="">— Pilih Kategori —</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea name="description" rows="2" placeholder="Deskripsi produk (opsional)"></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Harga (Rp)</label>
                                <input type="number" name="price" step="0.01" min="0" required placeholder="0">
                            </div>
                            <div class="form-group">
                                <label>Stok</label>
                                <input type="number" name="stock" min="0" required placeholder="0">
                            </div>
                            <div class="form-group">
                                <label>Gambar Produk</label>
                                <input type="file" name="image" accept="image/*">
                            </div>
                        </div>
                        <div class="action-bar">
                            <button type="submit" class="btn btn-primary">Tambah Produk</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Product List -->
            <div class="card">
                <div class="card-header">
                    <h3>Daftar Produk (<?= count($products) ?>)</h3>
                </div>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Gambar</th>
                                <th>Nama</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($products)): ?>
                                <tr><td colspan="6" class="text-center text-muted">Belum ada produk.</td></tr>
                            <?php else: ?>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($product['image'])): ?>
                                                <img src="data:image/jpeg;base64,<?= base64_encode($product['image']) ?>"
                                                     alt="<?= htmlspecialchars($product['name']) ?>" class="table-thumb">
                                            <?php else: ?>
                                                <div class="table-thumb-placeholder">—</div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($product['name']) ?></td>
                                        <td><?= htmlspecialchars($product['category_name'] ?? '—') ?></td>
                                        <td>Rp <?= number_format($product['price'], 0, ',', '.') ?></td>
                                        <td>
                                            <span class="<?= $product['stock'] == 0 ? 'text-danger' : '' ?>">
                                                <?= $product['stock'] ?>
                                            </span>
                                        </td>
                                        <td class="td-actions">
                                            <button class="btn btn-secondary btn-sm edit-btn"
                                                    data-id="<?= $product['id'] ?>"
                                                    data-name="<?= htmlspecialchars($product['name']) ?>"
                                                    data-category="<?= $product['category_id'] ?>"
                                                    data-description="<?= htmlspecialchars($product['description'] ?? '') ?>"
                                                    data-price="<?= $product['price'] ?>"
                                                    data-stock="<?= $product['stock'] ?>"
                                                    data-has-image="<?= !empty($product['image']) ? '1' : '0' ?>"
                                                    onclick="editProduct(this)">Edit</button>
                                            <form method="POST" action="" style="display:inline">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Hapus produk ini?')">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Edit Product Modal -->
<div id="edit-modal" class="modal-overlay" style="display:none">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Edit Produk</h3>
            <button class="modal-close" onclick="closeModal()">✕</button>
        </div>
        <form method="POST" action="" enctype="multipart/form-data" id="edit-form">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit-id">
            <div class="form-row">
                <div class="form-group">
                    <label>Nama Produk</label>
                    <input type="text" name="name" id="edit-name" required>
                </div>
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="category_id" id="edit-category" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="description" id="edit-description" rows="2"></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Harga (Rp)</label>
                    <input type="number" name="price" id="edit-price" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label>Stok</label>
                    <input type="number" name="stock" id="edit-stock" min="0" required>
                </div>
            </div>
            <div class="form-group">
                <label>Ganti Gambar <span class="optional">(kosongkan untuk tidak mengubah)</span></label>
                <input type="file" name="image" accept="image/*">
                <p id="edit-img-note" class="form-note"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function toggleForm(id) {
    const el = document.getElementById(id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

function editProduct(button) {
    // Get data from data attributes
    const id = button.dataset.id;
    const name = button.dataset.name;
    const category = button.dataset.category;
    const description = button.dataset.description;
    const price = button.dataset.price;
    const stock = button.dataset.stock;
    const hasImage = button.dataset.hasImage === '1';
    
    // Populate modal fields
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-name').value = name;
    document.getElementById('edit-description').value = description || '';
    document.getElementById('edit-price').value = price;
    document.getElementById('edit-stock').value = stock;
    document.getElementById('edit-category').value = category;
    
    // Update image note
    const note = document.getElementById('edit-img-note');
    if (hasImage) {
        note.textContent = 'Gambar (kosongkan jika tidak ingin mengganti gambar)';
        note.style.color = 'var(--color-success)';
    } else {
        note.textContent = 'Tidak ada gambar saat ini';
        note.style.color = 'var(--color-text-muted)';
    }
    
    // Show modal
    document.getElementById('edit-modal').style.display = 'flex';
}
// Close modal
function closeModal() {
    document.getElementById('edit-modal').style.display = 'none';
}

// Close modal jika klik diluar modal
document.getElementById('edit-modal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

// Close modal pake Esc
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});
</script>
</body>
</html>