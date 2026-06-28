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

// Get POST data
$cart_id = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

// Validate cart ID
if ($cart_id <= 0) {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid cart item'
    ]);
    exit();
}

// Validate quantity
if ($quantity < 0) {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid quantity'
    ]);
    exit();
}

// Connect to database
$conn = getDB();

// Update cart
if ($quantity == 0) {
    // Remove item if quantity is 0
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ?");
    $stmt->bind_param("i", $cart_id);
} else {
    // Update quantity
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $stmt->bind_param("ii", $quantity, $cart_id);
}

$result = $stmt->execute();

// Get updated cart count
$user = getCurrentUser();
$cart_items = getCart($user['id']);
$cart_count = count($cart_items);

// Return response
echo json_encode([
    'success' => $result,
    'cart_count' => $cart_count,
    'message' => $result ? 'Cart updated successfully!' : 'Failed to update cart'
]);
?>