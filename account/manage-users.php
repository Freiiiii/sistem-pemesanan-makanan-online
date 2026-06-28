<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireAdmin(); // Pastikan hanya admin yang bisa mengakses halaman ini
$user    = getCurrentUser();
$success = '';
$error   = '';
// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id     = (int)$_POST['id'];
    // Update user
    if ($action === 'update') {
        $data = [
            'name'     => sanitize($_POST['name']     ?? ''),
            'email'    => sanitize($_POST['email']    ?? ''),
            'phone'    => sanitize($_POST['phone']    ?? ''),
            'address'  => sanitize($_POST['address']  ?? ''),
            'role'     => sanitize($_POST['role']     ?? 'customer'),
            'verified' => (int)($_POST['verified']    ?? 0),
        ];
        if (!empty($_POST['password'])) {
            $data['password'] = $_POST['password'];
        }
        if (updateUser($id, $data)) {
            $success = 'Pengguna berhasil diperbarui.';
        } else {
            $error = 'Gagal memperbarui pengguna.';
        }
    // Delete user
    } elseif ($action === 'delete') {
        if ($id === $user['id']) {
            $error = 'Tidak dapat menghapus akun sendiri.';
        } else {
            $conn = getDB();
            $stmt = $conn->prepare("UPDATE users SET deleted = 1 WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $success = 'Pengguna dihapus.';
            } else {
                $error = 'Gagal menghapus pengguna.';
            }
        }
    }
}

$users = getUsers();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include 'includes/nav_admin.php'; ?>

<main>
    <section class="section">
        <div class="container">
            <h2 class="section-title">Kelola Pengguna</h2>

            <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
            <?php if ($error):   ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>

            <div class="card">
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>HP</th>
                                <th>Role</th>
                                <th>Verified</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><?= $u['id'] ?></td>
                                    <td><?= htmlspecialchars($u['name']) ?></td>
                                    <td><?= htmlspecialchars($u['username']) ?></td>
                                    <td><?= htmlspecialchars($u['email'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
                                    <td><span class="badge badge--<?= $u['role'] === 'admin' ? 'processing' : 'pending' ?>"><?= ucfirst($u['role']) ?></span></td>
                                    <td><?= $u['verified'] ? '<span class="text-success">✔</span>' : '<span class="text-muted">—</span>' ?></td>
                                    <td class="td-actions">
                                        <button class="btn btn-secondary btn-sm"
                                                onclick="editUser(<?= htmlspecialchars(json_encode($u)) ?>)">Edit</button>
                                        <?php if ($u['id'] != $user['id']): ?>
                                            <form method="POST" style="display:inline">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Hapus pengguna ini?')">Hapus</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Edit User Modal -->
<div id="edit-modal" class="modal-overlay" style="display:none">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Edit Pengguna</h3>
            <button class="modal-close" onclick="closeModal()">✕</button>
        </div>
        <form method="POST" action="" id="edit-form">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit-id">
            <div class="form-row">
                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" name="name" id="edit-name" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="edit-email">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Nomor HP</label>
                    <input type="text" name="phone" id="edit-phone">
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" id="edit-role">
                        <option value="customer">Customer</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Alamat</label>
                <textarea name="address" id="edit-address" rows="2"></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Verified</label>
                    <select name="verified" id="edit-verified">
                        <option value="1">Ya</option>
                        <option value="0">Tidak</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Password Baru <span class="optional">(kosongkan jika tidak diubah)</span></label>
                    <input type="password" name="password" placeholder="Password baru...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// Fungsi untuk mengisi form modal dengan data pengguna yang dipilih
function editUser(u) {
    document.getElementById('edit-id').value       = u.id;
    document.getElementById('edit-name').value     = u.name;
    document.getElementById('edit-email').value    = u.email    || '';
    document.getElementById('edit-phone').value    = u.phone    || '';
    document.getElementById('edit-address').value  = u.address  || '';
    document.getElementById('edit-role').value     = u.role;
    document.getElementById('edit-verified').value = u.verified ? '1' : '0';
    document.getElementById('edit-modal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('edit-modal').style.display = 'none';
}
document.getElementById('edit-modal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
</body>
</html>
