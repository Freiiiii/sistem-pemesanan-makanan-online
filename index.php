<?php
require_once 'account/includes/config.php';

// gateway, admin = dashboard.php, customer = customer.php, jika belum login = login.php
if (isLoggedIn()) {
    $user = getCurrentUser();
    if ($user && $user['role'] === 'admin') {
        header('Location: account/admin.php');
    } else {
        header('Location: account/customer.php');
    }
} else {
    header('Location: account/login.php');
}
exit();
