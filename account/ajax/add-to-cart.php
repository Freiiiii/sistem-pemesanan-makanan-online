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

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false, 
        'message' => 'Please login first'
    ]);
    exit();
}

// Get current user
$user = getCurrentUser();

// Get POST data
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

// Validate product ID
if ($product_id <= 0) {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid product'
    ]);
    exit();
}

// Check if product exists and has stock
$product = getProduct($product_id);
if (!$product) {
    echo json_encode([
        'success' => false, 
        'message' => 'Product not found'
    ]);
    exit();
}

if ($product['stock'] <= 0) {
    echo json_encode([
        'success' => false, 
        'message' => 'Product is out of stock'
    ]);
    exit();
}

// Add to cart
$result = addToCart($user['id'], $product_id, $quantity);

// Get updated cart count
$cart_items = getCart($user['id']);
$cart_count = count($cart_items);

// Return response
echo json_encode([
    'success' => $result,
    'cart_count' => $cart_count,
    'message' => $result ? 'Added to cart successfully!' : 'Failed to add to cart'
]);
?>