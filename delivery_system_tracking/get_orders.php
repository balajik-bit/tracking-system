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

    // Check if user_id is provided in the request
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
    
    // Build the SQL query based on whether user_id is provided
    if ($user_id) {
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
                WHERE o.user_id = ?
                ORDER BY o.order_date DESC";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        // Get all orders if no specific user is requested
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
                ORDER BY o.order_date DESC";
        
        $result = $conn->query($sql);
    }
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    $orders = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $orders[] = [
                'order_id' => (int)$row['order_id'],
                'user_id' => (int)$row['user_id'],
                'customer_name' => htmlspecialchars($row['customer_name']),
                'customer_email' => htmlspecialchars($row['customer_email']),
                'product_id' => (int)$row['product_id'],
                'product_name' => htmlspecialchars($row['product_name']),
                'price' => number_format((float)$row['price'], 2),
                'status' => $row['status'],
                'order_date' => date('M d, Y H:i', strtotime($row['order_date']))
            ];
        }
    }

    if (isset($stmt)) {
        $stmt->close();
    }
    $result->free();
    $conn->close();

    echo json_encode([
        'status' => 'success',
        'orders' => $orders,
        'message' => count($orders) > 0 ? 'Orders retrieved successfully' : 'No orders found'
    ]);

} catch (Exception $e) {
    error_log("Error in get_orders.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 