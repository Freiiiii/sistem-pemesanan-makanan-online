<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'food_ordering');

// Application configuration
define('SITE_NAME', 'Rumah Makan Sarwaguna');
define('BASE_URL', 'http://localhost/sistem-pemesanan-makanan-online/');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
function getDB() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die("Database connection error: " . $conn->connect_error);
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

// Get current user data
function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) return null;
    $conn = getDB();
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND deleted = 0");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    if (!isLoggedIn()) return false;
    $user = getCurrentUser();
    return $user && $user['role'] === 'admin';
}

function isCustomer() {
    if (!isLoggedIn()) return false;
    $user = getCurrentUser();
    return $user && $user['role'] === 'customer';
}

// Redirect to login if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Redirect if not admin — admin-only pages
function requireAdmin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
    $user = getCurrentUser();
    if (!$user || $user['role'] !== 'admin') {
        header('Location: customer.php');
        exit();
    }
}

// Redirect if not customer — customer-only pages
function requireCustomer() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
    $user = getCurrentUser();
    if (!$user || $user['role'] !== 'customer') {
        header('Location: admin.php');
        exit();
    }
}

function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}
