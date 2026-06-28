<?php
// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include configuration
require_once '../account/includes/config.php';
require_once '../account/includes/functions.php';

// Set JSON response header
header('Content-Type: application/json');

// Check if user has admin permission
$user = getCurrentUser();
if (!$user || $user['role'] !== 'admin') {
    echo json_encode([
        'error' => 'Unauthorized access'
    ]);
    exit();
}

// Get product ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo json_encode([
        'error' => 'Invalid product ID'
    ]);
    exit();
}

// Get product
$product = getProduct($id);

if (!$product) {
    echo json_encode([
        'error' => 'Product not found'
    ]);
    exit();
}

// Don't send image data in JSON (too large)
// Instead, we'll handle image display separately
unset($product['image']);

// Return response
echo json_encode($product);
?>