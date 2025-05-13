<?php
session_start();
header('Content-Type: application/json');

// Debug logging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access - Admin not logged in'
    ]);
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Get order_id from request
    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : null;
    
    if (!$order_id) {
        throw new Exception("Order ID is required");
    }

    // Get order details
    $sql = "SELECT o.order_id, 
                   o.user_id,
                   u.fullname as customer_name,
                   u.email as customer_email,
                   p.id as product_id,
                   p.name as product_name,
                   p.price,
                   o.status,
                   o.order_date
            FROM orders o
            JOIN users u ON o.user_id = u.id
            JOIN products p ON o.product_id = p.id
            WHERE o.order_id = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    if ($result->num_rows === 0) {
        throw new Exception("Order not found");
    }

    $order = $result->fetch_assoc();
    
    // Format the data
    $formattedOrder = [
        'order_id' => (int)$order['order_id'],
        'user_id' => (int)$order['user_id'],
        'customer_name' => htmlspecialchars($order['customer_name']),
        'customer_email' => htmlspecialchars($order['customer_email']),
        'product_id' => (int)$order['product_id'],
        'product_name' => htmlspecialchars($order['product_name']),
        'price' => number_format((float)$order['price'], 2),
        'status' => $order['status'],
        'order_date' => date('M d, Y H:i', strtotime($order['order_date']))
    ];

    $stmt->close();
    $result->free();
    $conn->close();

    echo json_encode([
        'status' => 'success',
        'order' => $formattedOrder
    ]);

} catch (Exception $e) {
    error_log("Error in get_order_details.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 