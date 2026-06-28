<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// cek log in status, jika sudah login redirect ke dashboard.php (admin) atau customer.php (customer)
if (isLoggedIn()) {
    $user = getCurrentUser();
    header('Location: ' . ($user['role'] === 'admin' ? 'admin.php' : 'customer.php'));
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi.';
    } else {
        $conn = getDB();
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND deleted = 0");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user) {
            $verified = false;

            if (password_verify($password, $user['password'])) {
                $verified = true;
            } elseif ($password === $user['password']) {
                // Rehash plain-text legacy password
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $upd = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $upd->bind_param("si", $hashed, $user['id']);
                $upd->execute();
                $verified = true;
            }

            if ($verified) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role']    = $user['role'];

                header('Location: ' . ($user['role'] === 'admin' ? 'admin.php' : 'customer.php'));
                exit();
            } else {
                $error = 'Password salah.';
            }
        } else {
            $error = 'Username tidak ditemukan.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-page">
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-brand">
            <span class="brand-icon">🍽️</span>
            <h1><?= SITE_NAME ?></h1>
            <p>Masuk ke akun Anda</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text"
                       id="username"
                       name="username"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                       placeholder="Masukkan username"
                       required
                       autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password"
                       id="password"
                       name="password"
                       placeholder="Masukkan password"
                       required>
            </div>

            <button type="submit" class="btn btn-primary btn-full">
                Masuk
            </button>
        </form>

        <p class="auth-footer">
            Belum punya akun?
            <a href="register.php">Daftar di sini</a>
        </p>
    </div>
</div>
</body>
</html>