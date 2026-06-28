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

// Get categories
$categories = getCategories();

// Return response
echo json_encode($categories);
?>