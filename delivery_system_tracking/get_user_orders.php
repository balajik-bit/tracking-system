<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

if (!isset($_GET['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
    exit();
}

$user_id = intval($_GET['user_id']);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT o.order_id, 
                   u.fullname as customer_name,
                   p.name as product_name,
                   p.price,
                   o.status,
                   o.order_date
            FROM orders o 
            JOIN products p ON o.product_id = p.id 
            JOIN users u ON o.user_id = u.id
            WHERE o.user_id = ? 
            ORDER BY o.order_date DESC";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $orders = [];
    
    while ($row = $result->fetch_assoc()) {
        $orders[] = [
            'order_id' => (int)$row['order_id'],
            'customer_name' => htmlspecialchars($row['customer_name']),
            'product_name' => htmlspecialchars($row['product_name']),
            'price' => number_format((float)$row['price'], 2),
            'status' => htmlspecialchars($row['status']),
            'order_date' => date('M d, Y H:i', strtotime($row['order_date']))
        ];
    }

    $stmt->close();
    $conn->close();

    echo json_encode([
        'status' => 'success',
        'orders' => $orders
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} 