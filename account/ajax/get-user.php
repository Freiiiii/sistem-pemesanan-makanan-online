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

// Get user ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo json_encode([
        'error' => 'Invalid user ID'
    ]);
    exit();
}

// Connect to database
$conn = getDB();

// Get user data
$stmt = $conn->prepare("SELECT id, name, username, email, phone, address, role, verified FROM users WHERE id = ? AND deleted = 0");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

if (!$user_data) {
    echo json_encode([
        'error' => 'User not found'
    ]);
    exit();
}

// Return response
echo json_encode($user_data);
?>