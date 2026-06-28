<?php
require_once 'config.php';

// Product functions
function getProducts($category_id = null, $search = null) {
    $conn = getDB();
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.deleted = 0";
    
    $params = [];
    $types = "";
    
    // Fix: Properly handle category_id (0 or null should mean "all")
    if ($category_id !== null && $category_id > 0) {
        $sql .= " AND p.category_id = ?";
        $params[] = $category_id;
        $types .= "i";
    }
    
    if ($search !== null && !empty(trim($search))) {
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $searchTerm = "%" . trim($search) . "%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "ss";
    }
    
    $sql .= " ORDER BY p.name";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getProduct($id) {
    $conn = getDB();
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name 
                            FROM products p 
                            LEFT JOIN categories c ON p.category_id = c.id 
                            WHERE p.id = ? AND p.deleted = 0");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function addProduct($category_id, $name, $description, $price, $stock, $image = null) {
    $conn = getDB();
    
    if ($image !== null) {
        $stmt = $conn->prepare("INSERT INTO products (category_id, name, description, price, stock, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issdis", $category_id, $name, $description, $price, $stock, $image);
    } else {
        $stmt = $conn->prepare("INSERT INTO products (category_id, name, description, price, stock) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issdi", $category_id, $name, $description, $price, $stock);
    }
    
    return $stmt->execute();
}

function updateProduct($id, $category_id, $name, $description, $price, $stock, $image = null) {
    $conn = getDB();
    
    if ($image !== null) {
        $stmt = $conn->prepare("UPDATE products SET category_id = ?, name = ?, description = ?, price = ?, stock = ?, image = ? WHERE id = ?");
        $stmt->bind_param("issdisi", $category_id, $name, $description, $price, $stock, $image, $id);
    } else {
        $stmt = $conn->prepare("UPDATE products SET category_id = ?, name = ?, description = ?, price = ?, stock = ? WHERE id = ?");
        $stmt->bind_param("issdii", $category_id, $name, $description, $price, $stock, $id);
    }
    return $stmt->execute();
}

function deleteProduct($id) {
    $conn = getDB();
    $stmt = $conn->prepare("UPDATE products SET deleted = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// Cart functions
function getCart($customer_id) {
    $conn = getDB();
    $stmt = $conn->prepare("SELECT c.*, p.name, p.price, p.image 
                            FROM cart c 
                            JOIN products p ON c.product_id = p.id 
                            WHERE c.customer_id = ? AND p.deleted = 0");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function addToCart($customer_id, $product_id, $quantity = 1) {
    $conn = getDB();
    
    // Check if product already in cart
    $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE customer_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $customer_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $new_quantity = $row['quantity'] + $quantity;
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_quantity, $row['id']);
        return $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO cart (customer_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $customer_id, $product_id, $quantity);
        return $stmt->execute();
    }
}

function removeFromCart($id) {
    $conn = getDB();
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

function clearCart($customer_id) {
    $conn = getDB();
    $stmt = $conn->prepare("DELETE FROM cart WHERE customer_id = ?");
    $stmt->bind_param("i", $customer_id);
    return $stmt->execute();
}

function getCartTotal($customer_id) {
    $conn = getDB();
    $stmt = $conn->prepare("SELECT SUM(c.quantity * p.price) as total 
                            FROM cart c 
                            JOIN products p ON c.product_id = p.id 
                            WHERE c.customer_id = ?");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['total'] ?? 0;
}

// Order functions
function createOrder($customer_id, $shipping_address, $payment_method) {
    $conn = getDB();
    
    $cart_items = getCart($customer_id);
    if (empty($cart_items)) {
        return false;
    }
    
    $total = getCartTotal($customer_id);
    
    $conn->begin_transaction();
    
    try {
        $stmt = $conn->prepare("INSERT INTO orders (customer_id, total_amount, shipping_address, payment_method) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("idss", $customer_id, $total, $shipping_address, $payment_method);
        $stmt->execute();
        $order_id = $conn->insert_id;
        
        foreach ($cart_items as $item) {
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
            $stmt->execute();
            
            $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $stmt->bind_param("ii", $item['quantity'], $item['product_id']);
            $stmt->execute();
        }
        
        clearCart($customer_id);
        $conn->commit();
        return $order_id;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

function getOrders($customer_id = null) {
    $conn = getDB();
    $sql = "SELECT o.*, u.name as customer_name 
            FROM orders o 
            JOIN users u ON o.customer_id = u.id 
            WHERE o.deleted = 0";
    
    if ($customer_id) {
        $sql .= " AND o.customer_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $customer_id);
    } else {
        $stmt = $conn->prepare($sql);
    }
    
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getOrder($id) {
    $conn = getDB();
    $stmt = $conn->prepare("SELECT o.*, u.name as customer_name, u.email, u.phone 
                            FROM orders o 
                            JOIN users u ON o.customer_id = u.id 
                            WHERE o.id = ? AND o.deleted = 0");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function updateOrderItemStatus($order_item_id, $status) {
    $conn = getDB();
    $stmt = $conn->prepare("UPDATE order_items SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_item_id);
    return $stmt->execute();
}

function updateAllOrderItemsStatus($order_id, $status) {
    $conn = getDB();
    $stmt = $conn->prepare("UPDATE order_items SET status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $status, $order_id);
    return $stmt->execute();
}

function getOrderItemsWithStatus($order_id) {
    $conn = getDB();
    $stmt = $conn->prepare("SELECT oi.*, p.name, p.image 
                            FROM order_items oi 
                            JOIN products p ON oi.product_id = p.id 
                            WHERE oi.order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function canCancelOrderItem($status) {
    return $status === 'pending';
}

function canCompleteOrderItem($status) {
    return $status === 'processing';
}

// ============================================
// MODIFIED: getOrderItems to include status
// ============================================
function getOrderItems($order_id) {
    $conn = getDB();
    $stmt = $conn->prepare("SELECT oi.*, p.name, p.image
                            FROM order_items oi 
                            JOIN products p ON oi.product_id = p.id 
                            WHERE oi.order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function updateOrderStatus($order_id, $status) {
    $conn = getDB();
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    return $stmt->execute();
}

// Category functions
function getCategories() {
    $conn = getDB();
    $result = $conn->query("SELECT * FROM categories WHERE deleted = 0 ORDER BY name");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// User functions
function getUsers($role = null) {
    $conn = getDB();
    $sql = "SELECT id, role, name, username, email, phone, address, verified, deleted, created_at FROM users WHERE deleted = 0";
    if ($role) {
        $sql .= " AND role = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $role);
    } else {
        $stmt = $conn->prepare($sql);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function updateUser($id, $data) {
    $conn = getDB();
    $fields = [];
    $params = [];
    $types = "";
    
    foreach ($data as $key => $value) {
        if ($key !== 'id' && $key !== 'password') {
            $fields[] = "$key = ?";
            $params[] = $value;
            $types .= "s";
        }
    }
    
    if (isset($data['password']) && !empty($data['password'])) {
        $fields[] = "password = ?";
        $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        $types .= "s";
    }
    
    $params[] = $id;
    $types .= "i";
    
    $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    return $stmt->execute();
}

// Payment functions
function createPayment($order_id, $amount, $payment_method) {
    $conn = getDB();
    $stmt = $conn->prepare("INSERT INTO payments (order_id, amount, payment_method) VALUES (?, ?, ?)");
    $stmt->bind_param("ids", $order_id, $amount, $payment_method);
    return $stmt->execute();
}

function updatePaymentStatus($order_id, $status) {
    $conn = getDB();
    
    // Update payment status
    $stmt = $conn->prepare("UPDATE payments SET payment_status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();
    
    // Update order payment status
    $stmt = $conn->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();
    
    // If payment is completed, automatically update order status to 'completed'
    if ($status === 'paid' || $status === 'completed') {
        $order_status = 'completed';
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $order_status, $order_id);
        $stmt->execute();
    }
    
    return true;
}

// Report functions (Laporan)
function generateSalesReport($start_date, $end_date) {
    $conn = getDB();
    
    $stmt = $conn->prepare("SELECT 
                            DATE(o.order_date) as date,
                            COUNT(DISTINCT o.id) as order_count,
                            SUM(o.total_amount) as total_sales,
                            SUM(oi.quantity) as items_sold
                            FROM orders o
                            JOIN order_items oi ON o.id = oi.order_id
                            WHERE o.status IN ('completed', 'processing')
                            AND o.deleted = 0
                            AND DATE(o.order_date) BETWEEN ? AND ?
                            GROUP BY DATE(o.order_date)
                            ORDER BY date DESC");
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function generateProductReport($start_date, $end_date) {
    $conn = getDB();
    $stmt = $conn->prepare("SELECT 
                            p.id,
                            p.name,
                            COUNT(DISTINCT oi.order_id) as order_count,
                            SUM(oi.quantity) as total_quantity,
                            SUM(oi.quantity * oi.price) as total_revenue
                            FROM products p
                            JOIN order_items oi ON p.id = oi.product_id
                            JOIN orders o ON oi.order_id = o.id
                            WHERE o.status IN ('completed', 'processing')
                            AND o.deleted = 0
                            AND DATE(o.order_date) BETWEEN ? AND ?
                            GROUP BY p.id
                            HAVING total_quantity > 0
                            ORDER BY total_revenue DESC");
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Order Status Count
function getOrderStatusCounts($start_date = null, $end_date = null) {
    $conn = getDB();
    $sql = "SELECT status, COUNT(*) as count FROM orders WHERE deleted = 0";
    $params = [];
    $types = "";
    
    if ($start_date && $end_date) {
        $sql .= " AND DATE(order_date) BETWEEN ? AND ?";
        $params[] = $start_date;
        $params[] = $end_date;
        $types .= "ss";
    }
    
    $sql .= " GROUP BY status";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $counts = [];
    while ($row = $result->fetch_assoc()) {
        $counts[$row['status']] = $row['count'];
    }
    return $counts;
}

// Helper function to display product image
function displayProductImage($image_data, $alt = 'Product Image', $class = 'product-thumb') {
    if (empty($image_data)) {
        return '<span class="no-image">No Image</span>';
    }
    
    // Try to detect image type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_buffer($finfo, $image_data);
    finfo_close($finfo);
    
    // If mime type detection fails, try common image types
    if (!$mime_type || $mime_type === 'application/octet-stream') {
        $hex = bin2hex(substr($image_data, 0, 4));
        if (substr($hex, 0, 4) === 'ffd8') {
            $mime_type = 'image/jpeg';
        } elseif (substr($hex, 0, 6) === '89504e') {
            $mime_type = 'image/png';
        } elseif (substr($hex, 0, 6) === '474946') {
            $mime_type = 'image/gif';
        } elseif (substr($hex, 0, 8) === '424d') {
            $mime_type = 'image/bmp';
        } else {
            $mime_type = 'image/jpeg';
        }
    }
    
    $base64 = base64_encode($image_data);
    return '<img src="data:' . $mime_type . ';base64,' . $base64 . '" alt="' . htmlspecialchars($alt) . '" class="' . $class . '" loading="lazy">';
}
?>