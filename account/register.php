<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
// redirect jika sudah login
if (isLoggedIn()) {
    $user = getCurrentUser();
    header('Location: ' . ($user['role'] === 'admin' ? 'admin.php' : 'customer.php'));
    exit();
}

$error = '';
$success = '';
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name             = sanitize($_POST['name'] ?? '');
    $username         = sanitize($_POST['username'] ?? '');
    $email            = sanitize($_POST['email'] ?? '');
    $phone            = sanitize($_POST['phone'] ?? '');
    $address          = sanitize($_POST['address'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    // Validasi input
    if (strlen($name) < 3) {
        $error = 'Nama minimal 3 karakter.';
    } elseif (strlen($username) < 5) {
        $error = 'Username minimal 5 karakter.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $confirm_password) {
        $error = 'Password tidak cocok.';
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } else {
        $conn = getDB();
        $chk = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $chk->bind_param("s", $username);
        $chk->execute();
        // Cek apakah username sudah ada
        if ($chk->get_result()->num_rows > 0) {
            $error = 'Username sudah digunakan.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, username, password, email, phone, address, role, verified) VALUES (?, ?, ?, ?, ?, ?, 'customer', 1)");
            $stmt->bind_param("ssssss", $name, $username, $hashed, $email, $phone, $address);
            if ($stmt->execute()) {
                $success = 'Registrasi berhasil! Silakan login.';
            } else {
                $error = 'Registrasi gagal. Coba lagi.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-wrapper">
        <div class="auth-card auth-card--wide">
            <div class="auth-brand">
                <span class="brand-icon">🍽️</span>
                <h1><?= SITE_NAME ?></h1>
                <p>Buat akun baru</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= $success ?> <a href="login.php">Login sekarang →</a>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Nama Lengkap</label>
                        <input type="text" id="name" name="name"
                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                               placeholder="Nama lengkap" required>
                    </div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username"
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                               placeholder="Minimal 5 karakter" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email <span class="optional">(opsional)</span></label>
                        <input type="email" id="email" name="email"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               placeholder="email@contoh.com">
                    </div>
                    <div class="form-group">
                        <label for="phone">Nomor HP</label>
                        <input type="text" id="phone" name="phone"
                               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                               placeholder="08xxxxxxxxxx" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="address">Alamat</label>
                    <textarea id="address" name="address" rows="2"
                              placeholder="Alamat lengkap Anda"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password"
                               placeholder="Minimal 6 karakter" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Konfirmasi Password</label>
                        <input type="password" id="confirm_password" name="confirm_password"
                               placeholder="Ulangi password" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-full">Daftar</button>
            </form>
            <?php endif; ?>
            <!-- footer hint + login page -->
            <p class="auth-footer">Sudah punya akun? <a href="login.php">Login di sini</a></p>
        </div>
    </div>
</body>
</html>
