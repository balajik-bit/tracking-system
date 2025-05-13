<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please login to view orders'
    ]);
    exit();
}


header('Content-Type: application/json');


$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT o.order_id, o.order_date, p.name as product_name, p.price, o.status
            FROM orders o
            JOIN products p ON o.product_id = p.id
            WHERE o.user_id = ?
            ORDER BY o.order_date DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    $orders = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $orders[] = [
                'id' => (int)$row['order_id'],
                'order_date' => date('M d, Y H:i', strtotime($row['order_date'])),
                'product_name' => htmlspecialchars($row['product_name']),
                'price' => number_format((float)$row['price'], 2),
                'status' => strtolower($row['status'])
            ];
        }
    }

    echo json_encode([
        'status' => 'success',
        'orders' => $orders
    ]);

    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 